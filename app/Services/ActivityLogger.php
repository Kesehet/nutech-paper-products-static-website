<?php
declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use PDOException;

final class ActivityLogger
{
    public static function log(
        ?int $userId,
        string $action,
        string $entityType,
        ?int $entityId = null,
        ?array $before = null,
        ?array $after = null,
        ?string $ipAddress = null,
        ?string $userAgent = null
    ): void {
        try {
            $pdo = Database::connection();
            $stmt = $pdo->prepare(
                'INSERT INTO activity_logs
                    (user_id, action, entity_type, entity_id, before_json, after_json, ip_address, user_agent, created_at)
                 VALUES
                    (:user_id, :action, :entity_type, :entity_id, :before_json, :after_json, :ip_address, :user_agent, NOW())'
            );
            $stmt->execute([
                'user_id' => $userId ?: null,
                'action' => $action,
                'entity_type' => $entityType,
                'entity_id' => $entityId,
                'before_json' => $before !== null ? json_encode($before, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : null,
                'after_json' => $after !== null ? json_encode($after, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : null,
                'ip_address' => $ipAddress,
                'user_agent' => $userAgent !== null ? substr($userAgent, 0, 255) : null,
            ]);
        } catch (PDOException) {
            // Ignore audit log write errors so primary workflow is not blocked.
        }
    }
}

