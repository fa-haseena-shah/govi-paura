<?php

namespace App\Domains\Notification\Services;

use Illuminate\Support\Facades\Log;

class LogSmsGateway implements SmsGatewayInterface
{
    public function send(string $phoneNumber, string $message): bool
    {
        Log::info("[MOCK SMS] To: {$phoneNumber} | Message: {$message}");
        return true;
    }
}