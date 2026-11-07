<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Sends SMS through the configured provider (see config/sms.php).
 * With SMS_DRIVER=log the message is written to the Laravel log instead –
 * convenient for local development and testing.
 */
class SmsService
{
    public function send(string $to, string $message): bool
    {
        $driver = config('sms.driver');

        if ($driver !== 'http' || ! config('sms.api_url')) {
            Log::channel(config('logging.default'))->info('[SMS] to '.$to.': '.$message);

            return true;
        }

        $p = config('sms.params');
        $payload = [
            $p['api_key'] => config('sms.api_key'),
            $p['sender'] => config('sms.sender_id'),
            $p['to'] => $this->normalize($to),
            $p['message'] => $message,
        ];

        try {
            $response = strtoupper(config('sms.method')) === 'GET'
                ? Http::timeout(15)->get(config('sms.api_url'), $payload)
                : Http::timeout(15)->asForm()->post(config('sms.api_url'), $payload);

            if (! $response->successful()) {
                Log::warning('SMS gateway error', ['status' => $response->status(), 'body' => $response->body()]);
            }

            return $response->successful();
        } catch (Throwable $e) {
            Log::error('SMS sending failed: '.$e->getMessage());

            return false;
        }
    }

    /** 01XXXXXXXXX -> 8801XXXXXXXXX */
    public function normalize(string $phone): string
    {
        $digits = preg_replace('/\D/', '', $phone);

        return str_starts_with($digits, '880') ? $digits : '88'.$digits;
    }
}
