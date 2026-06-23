<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\SendNotificationsRequest;
use App\Models\User;
use App\Services\DeduplicationService;
use App\Services\Notifications\NotificationService;
use App\Services\RabbitMQ\RabbitMQService;
use Illuminate\Http\JsonResponse;

class NotificationController extends Controller {
    public function sendNotifications(
        SendNotificationsRequest $request,
        NotificationService $notificationService,
        RabbitMQService $rabbitMQService,
        DeduplicationService $deduplicationService,
    ): JsonResponse {
        $validated = $request->validated();

        $hash = $deduplicationService->generateHash($validated);
        if ($deduplicationService->isDuplicate($hash)) {
            return response()->json([
                'success' => true,
                'cached' => true,
                'message' => 'Уведомления уже были отправлены ранее (дубликат)',
            ]);
        }

        $notifications = $notificationService->createNotifications($validated['user_ids'], $validated['notification_type'], $validated['channel'], $validated['message']);
        $rabbitMQService->publishNotifications($notifications);
        $deduplicationService->saveHash($hash, 3600);

        $notificationIds = $notifications->pluck('id');

        return response()->json([
            'success' => true,
            'cached' => false,
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

        $notifications = $user->getUserNotifications();

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
