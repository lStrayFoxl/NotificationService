<?php

namespace App\Services\Notifications;

use App\Models\Notification;
use App\Models\NotificationType;
use App\Models\User;
use Illuminate\Support\Collection;

class NotificationService {
    public function createNotifications(
        array $userIds,
        string $notificationType,
        string $channel,
        string $message
    ): Collection {
        $users = User::whereIn('id', $userIds)->get();
        $notificationType = NotificationType::where('type', $notificationType)->first();

        $notifications = new Collection();
        foreach ($users as $user) {
            if ($channel === 'sms' && !$user->phone) {
                continue;
            }

            if ($channel === 'email' && !$user->email) {
                continue;
            }

            $notification = $this->createNotification($user, $notificationType, $channel, $message);

            $notifications->push($notification);
        }

        return $notifications;
    }

    private function createNotification(
        User $user,
        NotificationType $notificationType,
        string $channel,
        string $message,
    ): Notification {
        $notification = new Notification();
        $notification->user_id = $user->id;
        $notification->notification_type_id = $notificationType->id;
        $notification->channel = $channel;
        $notification->recipient = $channel === 'sms' ? $user->phone : $user->email;
        $notification->message = $message;
        $notification->save();

        return $notification;
    }
}
