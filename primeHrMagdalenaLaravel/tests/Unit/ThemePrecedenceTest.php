<?php

namespace Tests\Unit;

use App\Models\SystemAiSetting;
use App\Models\User;
use App\Models\UserThemeSetting;
use App\Services\SystemTheme;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Theme precedence: personal → global → application default.
 *
 * The rule that matters most is the one an administrator has to trust:
 * changing the organisation's palette must never overwrite what somebody
 * chose for themselves.
 *
 * Tables are built by hand here because RefreshDatabase does not work in
 * this project — see CLAUDE.md.
 */
class ThemePrecedenceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('email')->nullable();
            $table->string('username')->nullable();
            $table->text('roles')->nullable();
            $table->timestamps();
        });

        Schema::create('system_ai_settings', function (Blueprint $table) {
            $table->id();
            $table->string('theme')->default('default');
            $table->string('custom_theme_primary', 7)->nullable();
            $table->string('theme_secondary', 7)->nullable();
            $table->string('theme_accent', 7)->nullable();
            $table->string('theme_muted', 7)->nullable();
            $table->timestamps();
        });

        Schema::create('user_theme_settings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->unique();
            $table->string('theme')->default('default');
            $table->string('custom_theme_primary', 7)->nullable();
            $table->string('theme_secondary', 7)->nullable();
            $table->string('theme_accent', 7)->nullable();
            $table->string('theme_muted', 7)->nullable();
            $table->timestamps();
        });

        SystemAiSetting::create(['theme' => 'emerald']);
        SystemTheme::flushCache();
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('user_theme_settings');
        Schema::dropIfExists('system_ai_settings');
        Schema::dropIfExists('users');
        parent::tearDown();
    }

    private function signIn(): User
    {
        $user = User::create(['email' => 'someone@example.test', 'username' => 'someone', 'roles' => ['employee']]);
        Auth::login($user);

        // The service memoises per request; a test acts as several requests
        // in one process, so the memo is cleared exactly as a new request
        // would clear it.
        SystemTheme::flushCache();

        return $user;
    }

    #[Test]
    public function a_guest_gets_the_organisation_palette(): void
    {
        $this->assertSame(
            SystemTheme::resolve('emerald')['--theme-primary'],
            SystemTheme::globalVars()['--theme-primary'],
        );
        $this->assertNull(SystemTheme::personalVars());
    }

    #[Test]
    public function a_user_without_a_row_inherits_the_global_palette(): void
    {
        $this->signIn();

        $this->assertNull(SystemTheme::personalVars(), 'No row must read as "no personal theme".');
        $this->assertFalse(SystemTheme::hasPersonalTheme());
        $this->assertSame(
            SystemTheme::resolve('emerald')['--theme-primary'],
            SystemTheme::globalVars()['--theme-primary'],
        );
    }

    #[Test]
    public function a_personal_palette_wins_over_the_global_one(): void
    {
        $user = $this->signIn();
        UserThemeSetting::create(['user_id' => $user->id, 'theme' => 'violet']);
        SystemTheme::flushCache();

        $this->assertTrue(SystemTheme::hasPersonalTheme());
        $this->assertSame(
            SystemTheme::resolve('violet')['--theme-primary'],
            SystemTheme::personalVars()['--theme-primary'],
        );
        $this->assertNotSame(
            SystemTheme::globalVars()['--theme-primary'],
            SystemTheme::personalVars()['--theme-primary'],
        );
    }

    #[Test]
    public function changing_the_global_palette_leaves_personal_ones_alone(): void
    {
        $user = $this->signIn();
        UserThemeSetting::create(['user_id' => $user->id, 'theme' => 'violet']);
        SystemTheme::flushCache();

        SystemAiSetting::current()->update(['theme' => 'crimson']);
        SystemTheme::flushCache();

        $this->assertSame(
            SystemTheme::resolve('violet')['--theme-primary'],
            SystemTheme::personalVars()['--theme-primary'],
            'An admin changing the organisation palette must not reach into a personal one.',
        );
        $this->assertSame(
            SystemTheme::resolve('crimson')['--theme-primary'],
            SystemTheme::globalVars()['--theme-primary'],
        );
    }

    #[Test]
    public function resetting_the_global_palette_does_not_delete_personal_ones(): void
    {
        $user = $this->signIn();
        UserThemeSetting::create(['user_id' => $user->id, 'theme' => 'violet']);

        // What AppearanceController::resetGlobal() writes.
        SystemAiSetting::current()->update([
            'theme'                => 'default',
            'custom_theme_primary' => null,
            'theme_secondary'      => null,
            'theme_accent'         => null,
            'theme_muted'          => null,
        ]);

        $this->assertDatabaseHas('user_theme_settings', ['user_id' => $user->id, 'theme' => 'violet']);
        $this->assertSame('default', SystemAiSetting::current()->theme);
    }

    #[Test]
    public function resetting_a_personal_palette_is_a_delete_so_the_global_takes_over(): void
    {
        $user = $this->signIn();
        UserThemeSetting::create(['user_id' => $user->id, 'theme' => 'violet']);

        // What AppearanceController::resetPersonal() writes.
        UserThemeSetting::where('user_id', $user->id)->delete();

        $this->assertDatabaseMissing('user_theme_settings', ['user_id' => $user->id]);
    }

    #[Test]
    public function one_row_per_user(): void
    {
        $user = $this->signIn();

        UserThemeSetting::updateOrCreate(['user_id' => $user->id], ['theme' => 'violet']);
        UserThemeSetting::updateOrCreate(['user_id' => $user->id], ['theme' => 'amber']);

        $this->assertSame(1, UserThemeSetting::where('user_id', $user->id)->count());
        $this->assertSame('amber', UserThemeSetting::where('user_id', $user->id)->value('theme'));
    }

    #[Test]
    public function the_picker_offers_only_palettes_the_backend_accepts(): void
    {
        // The <select>-equivalent and the validator must not drift: a card
        // the UI renders but the endpoint rejects is a dead button.
        $offered = array_column(SystemTheme::all(), 'key');

        $this->assertSame(array_keys(SystemTheme::PALETTES), $offered);
    }
}
