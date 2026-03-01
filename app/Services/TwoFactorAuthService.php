<?php

namespace App\Services;

use App\Mail\TwoFactorCodeMail;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class TwoFactorAuthService
{
    /**
     * Generate and send 2FA code to the user.
     *
     * @param User $user
     * @return void
     */
    public function generateAndSendCode(User $user): void
    {
        // Generate 2FA Code
        $code = (string) random_int(100000, 999999);

        // Save hashed code and expiration
        $user->two_factor_code = Hash::make($code);
        $user->two_factor_expires_at = now()->addMinutes(10);
        $user->save();

        // Send Email
        Mail::to($user)->send(new TwoFactorCodeMail($code));
    }
}
