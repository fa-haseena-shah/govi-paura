<?php

namespace App\Domains\Notification\Services;

use App\Domains\Notification\DTOs\NotificationData;
use App\Enums\NotificationType;
use App\Models\User;

interface NotificationServiceInterface
{
    /** Store an in-app notification for one user. */
    public function notify(int $userId, NotificationType $type, string $title, string $message, array $data = []): void;

    /**
     * @param 'all'|'unread' $filter
     * @return array{data: NotificationData[], total: int, page: int, perPage: int, unread: int}
     */
    public function listFor(User $user, string $filter, int $page, int $perPage): array;

    public function unreadCount(User $user): int;

    /** Mark one of the user's own notifications as read. */
    public function markRead(User $user, int $notificationId): NotificationData;

    /** @return int how many notifications were marked as read */
    public function markAllRead(User $user): int;
}
