<?php

namespace App\Domains\Notification\DTOs;

use App\Domains\Notification\Models\UserNotification;

final readonly class NotificationData
{
    public function __construct(
        public int $id,
        public string $type,
        public string $title,
        public string $message,
        public ?array $data, // ids to establish a link i.e. order_id, listing_id, bid_id
        public bool $isRead,
        public string $createdAt,
        public ?string $readAt,
    ) {}

    public static function fromModel(UserNotification $n): self
    {
        return new self(
            id: $n->id,
            type: $n->type->value,
            title: $n->title,
            message: $n->message,
            data: $n->data,
            isRead: $n->status === 'read',
            createdAt: $n->created_at->toIso8601String(),
            readAt: $n->read_at?->toIso8601String(),
        );
    }
}
