<?php

namespace Tests\Unit;

use App\Models\AiConversation;
use App\Models\Employee;
use App\Models\User;
use App\Services\AiAccessPolicy;
use App\Services\AiConversationStore;
use App\Services\ChartRenderer;
use App\Services\ReportPdfService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Where a conversation is kept, and what survives being kept there.
 *
 * The chatheads used to store their thread in the Laravel session, so it was
 * destroyed by logout and by the session lifetime, and it held prose only. The
 * properties pinned here are the ones that made moving it to the database worth
 * doing:
 *
 *  1. a thread outlives the session it was typed in, and a chathead question
 *     continues the user's newest conversation rather than opening one nobody
 *     can find;
 *  2. a replayed turn carries what the answer actually showed;
 *  3. it is re-authorised on read — a user whose access has narrowed does not
 *     keep a readable copy of a wider answer.
 */
class AiConversationStoreTest extends TestCase
{
    private AiConversationStore $store;

    protected function setUp(): void
    {
        parent::setUp();

        $this->createSchema();

        $this->store = new AiConversationStore(
            new AiAccessPolicy(),
            new ChartRenderer(),
            new ReportPdfService(new ChartRenderer()),
        );
    }

    /**
     * Only the tables these assertions touch, built by hand on the in-memory
     * SQLite connection — the project's migrations cannot run here, so
     * RefreshDatabase is not an option. See AiFileResolverTest.
     */
    private function createSchema(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('email')->nullable();
            $table->string('password')->nullable();
            $table->text('roles')->nullable();
            $table->unsignedBigInteger('employee_id')->nullable();
            $table->timestamps();
        });

        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->string('employee_id')->nullable();
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('ai_conversations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('title')->nullable();
            $table->timestamps();
        });

        Schema::create('ai_messages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('conversation_id');
            $table->string('role');
            $table->text('content');
            $table->json('attachments')->nullable();
            $table->timestamps();
        });
    }

    private function admin(): User
    {
        $user = new User();
        $user->name = 'HR Admin';
        $user->email = 'admin@example.test';
        $user->password = 'x';
        $user->roles = ['admin'];
        $user->save();

        return $user->refresh();
    }

    private function employee(): User
    {
        $employee = Employee::create([
            'employee_id' => 'EMP-1',
            'first_name' => 'Juan',
            'last_name' => 'Dela Cruz',
        ]);

        $user = new User();
        $user->name = 'Juan Dela Cruz';
        $user->email = 'juan@example.test';
        $user->password = 'x';
        $user->roles = ['employee'];
        $user->employee_id = $employee->id;
        $user->save();

        return $user->refresh();
    }

    #[Test]
    public function a_chathead_question_continues_the_users_newest_conversation(): void
    {
        $user = $this->admin();

        $older = $this->store->start($user, 'first thread');
        $older->update(['updated_at' => now()->subDay()]);

        $newest = $this->store->start($user, 'second thread');

        $this->assertSame(
            $newest->id,
            $this->store->continueLatestOrStart($user, 'and another question')->id,
            'the chathead should pick up the thread the user last used, not open a new one'
        );
        $this->assertNotSame($older->id, $newest->id);
    }

    #[Test]
    public function a_user_with_no_conversation_yet_gets_one_titled_from_their_question(): void
    {
        $user = $this->admin();

        $conversation = $this->store->continueLatestOrStart($user, 'How many employees are on leave today?');

        $this->assertSame($user->id, $conversation->user_id);
        $this->assertStringStartsWith('How many employees are on leave', $conversation->title);
    }

    #[Test]
    public function another_users_conversation_is_never_continued(): void
    {
        $mine = $this->admin();
        $theirs = $this->employee();

        $this->store->start($theirs, 'their thread');
        $conversation = $this->store->continueLatestOrStart($mine, 'my question');

        $this->assertSame($mine->id, $conversation->user_id);
    }

    #[Test]
    public function history_replays_the_question_before_its_answer(): void
    {
        $user = $this->admin();
        $conversation = $this->store->start($user, 'Who is on leave?');

        // Both rows land in the same second, which is exactly the case
        // created_at alone cannot order.
        $this->store->recordQuestion($conversation, 'Who is on leave?');
        $this->store->recordAnswer($conversation, 'Two employees are on leave.', [], $user);
        $this->store->recordQuestion($conversation, 'And tomorrow?');
        $this->store->recordAnswer($conversation, 'One employee.', [], $user);

        $this->assertSame([
            ['role' => 'user', 'content' => 'Who is on leave?'],
            ['role' => 'assistant', 'content' => 'Two employees are on leave.'],
            ['role' => 'user', 'content' => 'And tomorrow?'],
            ['role' => 'assistant', 'content' => 'One employee.'],
        ], $this->store->history($conversation));
    }

    #[Test]
    public function history_survives_being_read_by_a_new_request(): void
    {
        $user = $this->admin();
        $conversation = $this->store->start($user, 'Who is on leave?');
        $this->store->recordQuestion($conversation, 'Who is on leave?');
        $this->store->recordAnswer($conversation, 'Two employees.', [], $user);

        // Nothing about the read depends on session state: a fresh store, given
        // only the user, finds the same thread. This is what logging out and
        // back in now does, and what the session-backed version could not.
        $fresh = new AiConversationStore(
            new AiAccessPolicy(),
            new ChartRenderer(),
            new ReportPdfService(new ChartRenderer()),
        );

        $this->assertCount(2, $fresh->history($fresh->latestFor($user)));
    }

    #[Test]
    public function history_is_capped_to_the_most_recent_turns(): void
    {
        $user = $this->admin();
        $conversation = $this->store->start($user, 'q1');

        for ($i = 1; $i <= 15; $i++) {
            $this->store->recordQuestion($conversation, "q{$i}");
            $this->store->recordAnswer($conversation, "a{$i}", [], $user);
        }

        $history = $this->store->history($conversation, 4);

        $this->assertSame([
            ['role' => 'user', 'content' => 'q14'],
            ['role' => 'assistant', 'content' => 'a14'],
            ['role' => 'user', 'content' => 'q15'],
            ['role' => 'assistant', 'content' => 'a15'],
        ], $history);
    }

    #[Test]
    public function a_replayed_answer_still_carries_its_file_cards_and_table(): void
    {
        $user = $this->admin();
        $conversation = $this->store->start($user, 'Show me Juan\'s files');

        $message = $this->store->recordAnswer($conversation, 'Found 1 file.', [
            'files' => [['name' => 'appointment.pdf', 'url' => '/ai-assistant/file/documents/41']],
            'table' => ['title' => 'Documents', 'columns' => [['key' => 'name', 'label' => 'Name']]],
            'data' => [['name' => 'appointment.pdf']],
        ], $user);

        $replay = $this->store->replayAttachments($message->refresh(), $user);

        $this->assertSame('appointment.pdf', $replay['files'][0]['name']);
        $this->assertSame('Documents', $replay['table']['title']);
        $this->assertArrayNotHasKey('withheld', $replay);
    }

    #[Test]
    public function an_answer_is_withheld_from_a_reader_whose_scope_no_longer_matches(): void
    {
        $admin = $this->admin();
        $conversation = $this->store->start($admin, 'Everyone in the Mayor\'s Office');

        $message = $this->store->recordAnswer($conversation, '12 employees.', [
            'data' => [['name' => 'Juan Dela Cruz']],
        ], $admin);

        // The same row, read back after the account lost org-wide access.
        $admin->roles = ['employee'];
        $admin->save();

        $replay = $this->store->replayAttachments($message->refresh(), $admin->refresh());

        $this->assertTrue($replay['withheld']);
        $this->assertArrayNotHasKey('data', $replay);
    }

    #[Test]
    public function recording_an_answer_moves_the_thread_to_the_top(): void
    {
        $user = $this->admin();

        $first = $this->store->start($user, 'first');
        $second = $this->store->start($user, 'second');

        AiConversation::whereKey($first->id)->update(['updated_at' => now()->subHour()]);
        AiConversation::whereKey($second->id)->update(['updated_at' => now()->subMinute()]);

        $this->store->recordAnswer($first->refresh(), 'an answer', [], $user);

        $this->assertSame(
            $first->id,
            $this->store->latestFor($user)->id,
            'answering in a thread should make it the one the chathead continues'
        );
    }
}
