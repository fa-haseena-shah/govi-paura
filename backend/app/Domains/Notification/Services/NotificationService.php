<?php

namespace App\Domains\Notification\Services;

use App\Domains\Notification\DTOs\NotificationData;
use App\Domains\Notification\Models\UserNotification;
use App\Enums\NotificationType;
use App\Models\User;
use InvalidArgumentException;

class NotificationService implements NotificationServiceInterface
{
    public function notify(int $userId, NotificationType $type, string $title, string $message, array $data = []): void
    {
        UserNotification::create([
            'user_id' => $userId,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'data' => $data === [] ? null : $data,
            'status' => 'unread',
        ]);
    }

    public function listFor(User $user, string $filter, int $page, int $perPage): array
    {
        $query = UserNotification::where('user_id', $user->id);

        if ($filter === 'unread') {
            $query->where('status', 'unread');
        }

        $total = (clone $query)->count();

        $items = $query
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->forPage($page, $perPage)
            ->get()
            ->map(fn (UserNotification $n) => NotificationData::fromModel($n))
            ->all();

        return [
            'data' => $items,
            'total' => $total,
            'page' => $page,
            'perPage' => $perPage,
            'unread' => $this->unreadCount($user),
        ];
    }

    public function unreadCount(User $user): int
    {
        return UserNotification::where('user_id', $user->id)->where('status', 'unread')->count();
    }

    public function markRead(User $user, int $notificationId): NotificationData
    {
        // only owner can read or change their notifications only
        $notification = UserNotification::where('user_id', $user->id)->find($notificationId);

        if ($notification === null) {
            throw new InvalidArgumentException('Notification not found.');
        }

        if ($notification->status !== 'read') {
            $notification->update(['status' => 'read', 'read_at' => now()]);
        }

        return NotificationData::fromModel($notification);
    }

    public function markAllRead(User $user): int
    {
        return UserNotification::where('user_id', $user->id)
            ->where('status', 'unread')
            ->update(['status' => 'read', 'read_at' => now()]);
    }
}
