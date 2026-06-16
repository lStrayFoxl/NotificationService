<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class NotificationType extends Model {
    protected $fillable = [
        'type',
        'name',
        'priority',
        'description',
    ];

    public function notifications(): HasMany {
        return $this->hasMany(Notification::class, 'notification_type_id');
    }

    public function isHighPriority(): bool {
        return $this->priority === 'high';
    }

    public function getPriority(): string {
        return $this->priority;
    }

    protected function casts(): array {
        return [
            'priority' => 'string',
        ];
    }
}
