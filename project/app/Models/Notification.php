<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notification extends Model {
    protected $fillable = [
        'channel',
        'recipient',
        'message',
        'status',
        'sent_at',
        'delivered_at',
        'user_id',
        'notification_type_id',
    ];

    public function user(): BelongsTo {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function notificationType(): BelongsTo {
        return $this->belongsTo(NotificationType::class, 'notification_type_id');
    }

    public function markAsSent(): void {
        $this->status = 'sent';
        $this->sent_at = now();
        $this->save();
    }

    public function markAsDelivered(): void {
        $this->status = 'delivered';
        $this->delivered_at = now();
        $this->save();
    }

    public function markAsFailed(): void {
        $this->status = 'failed';
        $this->save();
    }

    protected function casts(): array {
        return [
            'channel' => 'string',
            'status' => 'string',
            'sent_at' => 'datetime',
            'delivered_at' => 'datetime',
        ];
    }
}
