<?php
declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use PDOException;

final class ScriptService
{
    public function getByLocation(string $location): array
    {
        try {
            $pdo = Database::connection();
            $stmt = $pdo->prepare(
                'SELECT label, script_content
                 FROM script_injections
                 WHERE location = :location AND is_active = 1
                 ORDER BY id ASC'
            );
            $stmt->execute(['location' => $location]);
            $rows = $stmt->fetchAll();
            return is_array($rows) ? $rows : [];
        } catch (PDOException) {
            return [];
        }
    }
}

