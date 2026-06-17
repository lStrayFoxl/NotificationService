<?php

use App\Http\Controllers\Api\NotificationController;
use Illuminate\Support\Facades\Route;

Route::prefix('notifications')->group(function () {
    Route::post('/send-notifications', [NotificationController::class, 'sendNotifications']);
});

Route::prefix('user')->group(function () {
    Route::get('/{userId}/notifications', [NotificationController::class, 'getUserNotifications']);
});
