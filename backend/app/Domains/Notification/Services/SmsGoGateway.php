<?php

namespace App\Domains\Notification\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class SmsGoGateway implements SmsGatewayInterface
{
    private const ENDPOINT = 'https://api.smsgo.lk/api/v1/sms/send';

    public function send(string $phoneNumber, string $message): bool
    {
        try {
            $response = Http::withHeaders([
                'X-API-Key' => config('services.smsgo.api_key'),
                'Content-Type' => 'application/json',
            ])->post(self::ENDPOINT, [
                'to' => $this->normalizePhone($phoneNumber),
                'message' => $message,
                'mask' => config('services.smsgo.mask'),
            ]);

            if (!$response->successful()) {
                Log::warning('SMSGo send failed', ['status' => $response->status(), 'body' => $response->body()]);
                return false;
            }

            return (bool) ($response->json('success') ?? false);
        } catch (Throwable $e) {
            Log::warning('SMSGo send threw an exception', ['error' => $e->getMessage()]);
            return false;
        }
    }

    private function normalizePhone(string $phone): string
    {
        $digits = preg_replace('/\D/', '', $phone);

        if (str_starts_with($digits, '94')) {
            return $digits;
        }
        if (str_starts_with($digits, '0')) {
            return '94' . substr($digits, 1);
        }

        return '94' . $digits;
    }
}