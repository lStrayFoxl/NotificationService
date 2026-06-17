<?php

namespace App\Services;

use Illuminate\Support\Facades\Redis;

class DeduplicationService {
    public function generateHash(array $payload): string {
        if (isset($payload['user_ids'])) {
            sort($payload['user_ids']);
        }

        ksort($payload);

        return hash('sha256', json_encode($payload));
    }

    public function isDuplicate(string $hash): bool {
        return Redis::exists("dedup:{$hash}") === 1;
    }

    public function saveHash(string $hash, int $ttl = 3600): void {
        Redis::setex("dedup:{$hash}", $ttl, '1');
    }
}
