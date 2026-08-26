<?php

namespace App\Services;

use App\Models\User;
use App\Notifications\PasswordResetCodeNotification;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * The forgot-password flow: email address -> six-digit code -> new password.
 *
 * All three steps live here rather than in the controller because they are one
 * rule set, not three endpoints: the code the first step mails is the code the
 * second step spends, and the ticket the second step issues is the only thing
 * the third step will accept. Splitting them across controller methods is how
 * one of those links quietly stops being checked.
 *
 * Two properties this is built to hold:
 *
 * - **The screen never says whether an address is registered.** sendCode()
 *   returns nothing on every path, so an unknown address, a deactivated one and
 *   a real one are indistinguishable from the browser. A public form that
 *   answers "no such account" is an account-enumeration oracle aimed at a
 *   municipality's staff directory.
 * - **Nothing is trusted from the client between steps.** The browser carries a
 *   ticket, not a "verified" flag, and the ticket is checked against a hash on
 *   the row before any password is written.
 */
class PasswordResetCodeService
{
    /** How long a mailed code stays spendable. */
    public const CODE_TTL_MINUTES = 10;

    /** How long the post-verification ticket lasts - enough to choose a password. */
    public const TICKET_TTL_MINUTES = 15;

    /** Wrong guesses a single code tolerates before it is burned. */
    public const MAX_ATTEMPTS = 5;

    /** Minimum gap between two "send code" requests for one address. */
    public const RESEND_COOLDOWN_SECONDS = 60;

    private const TABLE = 'password_reset_codes';

    /**
     * Issue a code and mail it, if the address belongs to an account that could
     * sign in at all.
     *
     * Returns void on purpose - see the enumeration note above. The caller has
     * nothing to branch on and so cannot leak what this found.
     */
    public function sendCode(string $email): void
    {
        $email = $this->normalise($email);
        $user  = $this->resettableUser($email);

        if (!$user) {
            return;
        }

        // Rate-limited per address as well as per IP (the route throttles the
        // IP). Otherwise "Resend Code" held down is a mail-bomb aimed at one
        // employee's inbox, from a form that needs no login to reach.
        $pending = $this->row($email);
        if ($pending && $pending->created_at
            && Carbon::parse($pending->created_at)->addSeconds(self::RESEND_COOLDOWN_SECONDS)->isFuture()) {
            return;
        }

        $code = $this->generateCode();

        DB::table(self::TABLE)->updateOrInsert(
            ['email' => $email],
            [
                'code_hash'   => Hash::make($code),
                'attempts'    => 0,
                'ticket_hash' => null,
                'created_at'  => now(),
                'verified_at' => null,
            ]
        );

        try {
            $user->notify(new PasswordResetCodeNotification($code, self::CODE_TTL_MINUTES));
        } catch (\Throwable $e) {
            // A dead SMTP host must not leave a live code on the row with
            // nobody able to read it - and must not tell the browser whether
            // the address existed either. Drop the code, log, stay silent.
            $this->forget($email);
            Log::error('Password reset code could not be sent', [
                'email' => $email,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Spend a code. Returns the plaintext ticket for step 3, or null.
     *
     * A wrong guess costs an attempt; the last one burns the code outright, so
     * a six-digit secret cannot be walked through.
     */
    public function verifyCode(string $email, string $code): ?string
    {
        $email = $this->normalise($email);
        $row   = $this->row($email);

        if (!$row || !$this->resettableUser($email)) {
            return null;
        }

        if ($this->codeExpired($row)) {
            $this->forget($email);
            return null;
        }

        if ($row->attempts >= self::MAX_ATTEMPTS) {
            $this->forget($email);
            return null;
        }

        if (!Hash::check($code, $row->code_hash)) {
            DB::table(self::TABLE)->where('email', $email)->increment('attempts');
            return null;
        }

        $ticket = Str::random(64);

        // The code is cleared as it is spent. Leaving it live would let the
        // same digits be replayed to mint a second ticket after the first was
        // used, which is the window this flow exists to close.
        DB::table(self::TABLE)->where('email', $email)->update([
            'code_hash'   => Hash::make(Str::random(40)),
            'ticket_hash' => Hash::make($ticket),
            'attempts'    => 0,
            'verified_at' => now(),
        ]);

        return $ticket;
    }

    /**
     * Write the new password, if the ticket is the one this address was issued.
     */
    public function resetPassword(string $email, string $ticket, string $password): bool
    {
        $email = $this->normalise($email);
        $row   = $this->row($email);
        $user  = $this->resettableUser($email);

        if (!$row || !$user || !$row->ticket_hash || !$row->verified_at) {
            return false;
        }

        if (Carbon::parse($row->verified_at)->addMinutes(self::TICKET_TTL_MINUTES)->isPast()) {
            $this->forget($email);
            return false;
        }

        if (!Hash::check($ticket, $row->ticket_hash)) {
            $this->forget($email);
            return false;
        }

        $user->password = $password;              // hashed by the model's cast
        $user->setRememberToken(Str::random(60)); // a stolen "remember me" cookie dies with the old password
        $user->save();

        $this->forget($email);

        return true;
    }

    /**
     * The account a reset may be performed on.
     *
     * Inactive accounts are excluded: AuthController::login() refuses them a
     * session, so mailing one a code offers a recovery that ends at the same
     * refusal. The exclusion is invisible from the browser either way.
     */
    private function resettableUser(string $email): ?User
    {
        $user = User::where('email', $email)->first();

        return $user && $user->isActive() ? $user : null;
    }

    private function codeExpired(object $row): bool
    {
        return !$row->created_at
            || Carbon::parse($row->created_at)->addMinutes(self::CODE_TTL_MINUTES)->isPast();
    }

    private function row(string $email): ?object
    {
        return DB::table(self::TABLE)->where('email', $email)->first();
    }

    private function forget(string $email): void
    {
        DB::table(self::TABLE)->where('email', $email)->delete();
    }

    private function normalise(string $email): string
    {
        return Str::lower(trim($email));
    }

    /**
     * random_int rather than rand: this is a credential, and a predictable one
     * is a bypass for the mailbox check the code stands for.
     */
    private function generateCode(): string
    {
        return str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    }
}
