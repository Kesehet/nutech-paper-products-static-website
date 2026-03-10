<?php
declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use PDOException;

final class SeoService
{
    public function resolve(string $entityType, int $entityId, array $fallback = []): array
    {
        $global = $this->getSeo('global', 0);
        $entity = $this->getSeo($entityType, $entityId);

        return [
            'title' => (string) ($entity['meta_title'] ?? $global['meta_title'] ?? $fallback['title'] ?? env('APP_NAME', 'Nutech Paper Products')),
            'description' => (string) ($entity['meta_description'] ?? $global['meta_description'] ?? $fallback['description'] ?? ''),
            'keywords' => (string) ($entity['meta_keywords'] ?? $global['meta_keywords'] ?? ''),
            'canonical' => (string) ($entity['canonical_url'] ?? ''),
            'robots' => (string) ($entity['robots'] ?? 'index,follow'),
            'og_title' => (string) ($entity['og_title'] ?? $entity['meta_title'] ?? ''),
            'og_description' => (string) ($entity['og_description'] ?? $entity['meta_description'] ?? ''),
            'og_image' => (string) ($entity['og_image_path'] ?? ''),
            'schema_json' => (string) ($entity['schema_json'] ?? ''),
        ];
    }

    private function getSeo(string $entityType, int $entityId): ?array
    {
        try {
            $pdo = Database::connection();
            $stmt = $pdo->prepare(
                'SELECT s.meta_title, s.meta_description, s.meta_keywords, s.canonical_url, s.robots,
                        s.og_title, s.og_description, s.schema_json, m.storage_path AS og_image_path
                 FROM seo_meta s
                 LEFT JOIN media m ON m.id = s.og_image_id
                 WHERE s.entity_type = :entity_type AND s.entity_id = :entity_id
                 LIMIT 1'
            );
            $stmt->execute([
                'entity_type' => $entityType,
                'entity_id' => $entityId,
            ]);
            $row = $stmt->fetch();
            return is_array($row) ? $row : null;
        } catch (PDOException) {
            return null;
        }
    }
}

