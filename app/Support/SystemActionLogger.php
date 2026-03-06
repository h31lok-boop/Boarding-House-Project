<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class SystemActionLogger
{
    public static function log(
        ?int $actorId,
        string $action,
        string $entityType,
        ?int $entityId = null,
        array $context = []
    ): void {
        $payload = [
            'actor_id' => $actorId,
            'action' => $action,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'context_json' => ! empty($context) ? json_encode($context, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : null,
            'created_at' => now(),
            'updated_at' => now(),
        ];

        if (Schema::hasTable('system_action_logs')) {
            DB::table('system_action_logs')->insert($payload);
            return;
        }

        Log::info('system_action', $payload);
    }
}
