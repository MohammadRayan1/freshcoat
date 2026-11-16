<?php

namespace App\Support;

use Illuminate\Support\Facades\Log;

/**
 * Writes texts to the log. Swap for a Twilio (or similar) sender before launch.
 */
class LogSmsSender implements SmsSender
{
    public function send(string $phone, string $message): void
    {
        Log::info('SMS', ['to' => $phone, 'message' => $message]);
    }
}
