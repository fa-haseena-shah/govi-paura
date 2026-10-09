<?php

namespace App\Domains\Notification\Services;

interface SmsGatewayInterface
{
    public function send(string $phoneNumber, string $message): bool;
}