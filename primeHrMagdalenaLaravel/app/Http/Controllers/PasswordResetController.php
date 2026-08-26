<?php

namespace App\Http\Controllers;

use App\Services\PasswordResetCodeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * The three POST endpoints behind `user/forgot-password.blade.php`.
 *
 * Thin by design: every rule about codes, tickets and expiry lives in
 * {@see PasswordResetCodeService}, so the wizard cannot be re-implemented a
 * second way by a future caller (the mobile client, most likely).
 *
 * The screens this serves used to be a mockup - the code was the literal
 * `123456`, compiled into the page every visitor can read, and step 3 announced
 * "Password reset successfully!" without touching the database. That is worse
 * than a broken button: it told people their password had changed when it had
 * not, and then sent them to a sign-in form their old password still opened.
 */
class PasswordResetController extends Controller
{
    public function __construct(private PasswordResetCodeService $codes)
    {
    }

    public function show()
    {
        return view('user.forgot-password');
    }

    /**
     * Step 1. Always the same answer, whatever was found.
     *
     * "If that address is registered..." rather than "no such account": this
     * form is public, so a distinguishing reply turns it into a checker for
     * which of the municipality's addresses hold accounts.
     */
    public function send(Request $request): JsonResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email', 'max:255'],
        ]);

        $this->codes->sendCode($data['email']);

        return response()->json([
            'ok'      => true,
            'message' => 'If that address is registered, a verification code is on its way.',
        ]);
    }

    /**
     * Step 2. Returns the ticket step 3 must present.
     */
    public function verify(Request $request): JsonResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email', 'max:255'],
            'code'  => ['required', 'digits:6'],
        ]);

        $ticket = $this->codes->verifyCode($data['email'], $data['code']);

        if (!$ticket) {
            return response()->json([
                'ok'      => false,
                'message' => 'That code is not valid or has expired. Request a new one.',
            ], 422);
        }

        return response()->json(['ok' => true, 'ticket' => $ticket]);
    }

    /**
     * Step 3. `min:8` matches registration and Settings > Change Password - a
     * password this system already accepts must not be one it refuses to
     * restore.
     */
    public function reset(Request $request): JsonResponse
    {
        $data = $request->validate([
            'email'    => ['required', 'email', 'max:255'],
            'ticket'   => ['required', 'string'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $done = $this->codes->resetPassword($data['email'], $data['ticket'], $data['password']);

        if (!$done) {
            return response()->json([
                'ok'      => false,
                'message' => 'This reset session has expired. Start again from your email address.',
            ], 422);
        }

        return response()->json([
            'ok'      => true,
            'message' => 'Password reset successfully. You can now sign in.',
        ]);
    }
}
