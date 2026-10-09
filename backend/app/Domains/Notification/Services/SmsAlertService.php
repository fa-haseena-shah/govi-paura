<?php

namespace App\Domains\Notification\Services;

use App\Domains\Listing\Models\Listing;
use App\Domains\Notification\Models\SmsLog;
use App\Domains\Order\Models\Order;
use App\Enums\SmsAlertType;
use App\Models\User;
use Illuminate\Support\Str;
use Throwable;

class SmsAlertService implements SmsAlertServiceInterface
{
    public function __construct(
        private readonly SmsGatewayInterface $gateway,
    ) {}

    public function sendNewOrderAlert(User $farmer, Order $order): void
    {
        $order->loadMissing('items.listing');

        $summary = $order->items
            ->map(fn ($item) => "{$item->listing->crop_type} x{$item->qty}")
            ->implode(', ');

        $message = "Govi Paura: New order #{$order->id} - " . Str::limit($summary, 80) . ". Check your dashboard.";
        $this->dispatch($farmer, SmsAlertType::NewOrder, $message);
    }

    public function sendLowStockAlert(User $farmer, Listing $listing): void
    {
        $message = "Govi Paura: Low stock alert — \"{$listing->crop_type}\" has only {$listing->qty} units left.";
        $this->dispatch($farmer, SmsAlertType::LowStock, $message);
    }

    public function sendNewBidAlert(User $farmer, string $buyerName, float $bidAmount, string $cropType): void
    {
        $message = "Govi Paura: New bid of LKR {$bidAmount} from {$buyerName} on your \"{$cropType}\" listing.";
        $this->dispatch($farmer, SmsAlertType::NewBid, $message);
    }

    // if this sms functionality fails, it should not disturb the order process so the 
    // errors are caught and handled here instead of throwing them
    private function dispatch(User $recipient, SmsAlertType $type, string $message): void
    {
        $status = 'sent';

        try {
            $sent = $this->gateway->send($recipient->phone, $message);
            $status = $sent ? 'sent' : 'failed';
        } catch (Throwable) {
            $status = 'failed';
        }

        SmsLog::create([
            'user_id' => $recipient->id,
            'phone' => $recipient->phone,
            'type' => $type,
            'message' => $message,
            'status' => $status,
        ]);
    }
}