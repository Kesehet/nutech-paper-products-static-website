<?php
declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use PDOException;

final class BlogService
{
    public function getPublished(?int $limit = null): array
    {
        try {
            $pdo = Database::connection();
            $sql = 'SELECT b.id, b.title, b.slug, b.excerpt, b.content_html, b.published_at, b.created_at,
                           m.storage_path AS featured_image_path, m.alt_text AS featured_image_alt
                    FROM blogs b
                    LEFT JOIN media m ON m.id = b.featured_image_id
                    WHERE b.status = "published"
                    ORDER BY COALESCE(b.published_at, b.created_at) DESC, b.id DESC';
            if ($limit !== null && $limit > 0) {
                $sql .= ' LIMIT :limit';
            }

            $stmt = $pdo->prepare($sql);
            if ($limit !== null && $limit > 0) {
                $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
            }
            $stmt->execute();
            return $stmt->fetchAll() ?: [];
        } catch (PDOException) {
            return [];
        }
    }

    public function findPublishedBySlug(string $slug): ?array
    {
        try {
            $pdo = Database::connection();
            $stmt = $pdo->prepare(
                'SELECT b.*,
                        m.storage_path AS featured_image_path, m.alt_text AS featured_image_alt,
                        og.storage_path AS og_image_path
                 FROM blogs b
                 LEFT JOIN media m ON m.id = b.featured_image_id
                 LEFT JOIN media og ON og.id = b.og_image_id
                 WHERE b.slug = :slug AND b.status = "published"
                 LIMIT 1'
            );
            $stmt->execute(['slug' => $slug]);
            $row = $stmt->fetch();
            return is_array($row) ? $row : null;
        } catch (PDOException) {
            return null;
        }
    }
}
