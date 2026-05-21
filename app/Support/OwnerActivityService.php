<?php

namespace App\Support;

use App\Models\AuditLog;
use App\Models\BoardingHouse;
use App\Models\OwnerNotification;

class OwnerActivityService
{
    public static function audit(?int $userId, string $action, string $description, array $context = []): void
    {
        if (! class_exists(AuditLog::class)) {
            return;
        }

        AuditLog::query()->create([
            'user_id' => $userId,
            'action' => $action,
            'description' => $description,
            'context' => $context !== [] ? $context : null,
        ]);
    }

    public static function notify(?int $userId, string $title, string $message): void
    {
        if (! $userId) {
            return;
        }

        OwnerNotification::query()->create([
            'user_id' => $userId,
            'title' => $title,
            'message' => $message,
            'is_read' => false,
        ]);
    }

    public static function notifyOwner(BoardingHouse $boardingHouse, string $title, string $message): void
    {
        self::notify((int) $boardingHouse->owner_id, $title, $message);
    }
}
