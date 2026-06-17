<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\SendNotificationsRequest;
use App\Models\Notification;
use App\Models\NotificationType;
use App\Models\User;
use Illuminate\Http\JsonResponse;

class NotificationController extends Controller {
    public function sendNotifications(SendNotificationsRequest $request): JsonResponse {
        $validated = $request->validated();

        $notificationType = NotificationType::where('type', $validated['notification_type'])->first();

        $users = User::whereIn('id', $validated['user_ids'])->get();

        $notificationIds = [];
        foreach ($users as $user) {
            if ($validated['channel'] === 'sms' && !$user->phone) {
                continue;
            }

            if ($validated['channel'] === 'email' && !$user->email) {
                continue;
            }

            $notification = new Notification();
            $notification->user_id = $user->id;
            $notification->notification_type_id = $notificationType->id;
            $notification->channel = $validated['channel'];
            $notification->recipient = $validated['channel'] === 'sms' ? $user->phone : $user->email;
            $notification->message = $validated['message'];
            $notification->status = 'queued';
            $notification->save();

            $notificationIds[] = $notification->id;
        }

        return response()->json([
            'success' => true,
            'notification_ids' => $notificationIds,
            'count' => count($notificationIds),
        ]);
    }

    public function getUserNotifications(int $userId): JsonResponse {
        $user = User::find($userId);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Пользователь не найден',
            ], 404);
        }

        $notifications = Notification::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->get();

        $notificationData = $notifications->map(function ($notification) {
            return [
                'id' => $notification->id,
                'type' => $notification->notificationType->type ?? null,
                'channel' => $notification->channel,
                'recipient' => $notification->recipient,
                'message' => $notification->message,
                'status' => $notification->status,
                'sent_at' => $notification->sent_at?->format('d.m.Y H:i:s'),
                'delivered_at' => $notification->delivered_at?->format('d.m.Y H:i:s'),
            ];
        });

        return response()->json([
            'user_id' => $userId,
            'notifications' => $notificationData,
        ]);
    }
}
