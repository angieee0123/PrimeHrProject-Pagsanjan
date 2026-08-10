<?php
namespace Tests\Feature;

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class TmpRenderTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Schema::create('users', function (Blueprint $t) {
            $t->id(); $t->string('email')->nullable(); $t->string('username')->nullable();
            $t->string('password')->nullable(); $t->text('roles')->nullable();
            $t->string('status')->default('Active'); $t->timestamps();
        });
        Schema::create('notifications', function (Blueprint $t) {
            $t->id(); $t->string('type')->nullable(); $t->string('title')->nullable();
            $t->text('message')->nullable(); $t->unsignedBigInteger('user_id')->nullable();
            $t->timestamp('read_at')->nullable(); $t->string('link')->nullable(); $t->timestamps();
        });
        Schema::create('site_contents', function (Blueprint $t) {
            $t->id(); $t->string('key')->unique(); $t->text('value');
            $t->unsignedBigInteger('updated_by')->nullable(); $t->timestamps();
        });
    }

    #[Test]
    public function dump_editor_html(): void
    {
        $u = new User();
        $u->email='a@x.test'; $u->username='admin'; $u->roles=['admin']; $u->status='Active'; $u->save();
        $html = $this->actingAs($u)->get('/admin/website')->assertOk()->getContent();
        file_put_contents(public_path('_editor.html'), $html);
        $this->assertStringContainsString('wc-container', $html);
    }
}
