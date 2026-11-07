<?php

namespace App\Services;

use App\Models\OtpCode;
use Illuminate\Support\Facades\Hash;

/**
 * One-time password generation & verification.
 * - 6 digits, valid for 5 minutes, max 5 wrong attempts.
 * - Only a hash is stored in the database.
 */
class OtpService
{
    public const TTL_MINUTES = 5;
    public const MAX_ATTEMPTS = 5;

    public function __construct(private SmsService $sms) {}

    public function send(string $phone, string $purpose): void
    {
        // Invalidate earlier codes for the same purpose
        OtpCode::where('identifier', $phone)->where('purpose', $purpose)->whereNull('used_at')->update(['used_at' => now()]);

        $code = (string) random_int(100000, 999999);

        OtpCode::create([
            'identifier' => $phone,
            'purpose' => $purpose,
            'code_hash' => Hash::make($code),
            'expires_at' => now()->addMinutes(self::TTL_MINUTES),
        ]);

        $this->sms->send($phone, __(':site verification code: :code. Valid for :min minutes. Do not share this code with anyone.', [
            'site' => site_name(), 'code' => $code, 'min' => self::TTL_MINUTES,
        ]));
    }

    public function verify(string $phone, string $purpose, string $code): bool
    {
        $otp = OtpCode::where('identifier', $phone)->where('purpose', $purpose)
            ->whereNull('used_at')->where('expires_at', '>', now())
            ->latest('id')->first();

        if (! $otp || $otp->attempts >= self::MAX_ATTEMPTS) {
            return false;
        }

        if (! Hash::check($code, $otp->code_hash)) {
            $otp->increment('attempts');

            return false;
        }

        $otp->update(['used_at' => now()]);

        return true;
    }
}
