<?php
declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use PDOException;

final class ProductService
{
    public function getPublished(array $filters = []): array
    {
        try {
            $pdo = Database::connection();
            $sql = 'SELECT p.id, p.title, p.slug, p.short_description, p.status, c.name AS category_name,
                           m.storage_path AS featured_image_path
                    FROM products p
                    LEFT JOIN product_categories c ON c.id = p.category_id
                    LEFT JOIN media m ON m.id = p.featured_image_id
                    WHERE p.status = "published"';
            $params = [];

            if (!empty($filters['category'])) {
                $sql .= ' AND c.slug = :category';
                $params['category'] = $filters['category'];
            }

            if (!empty($filters['search'])) {
                $sql .= ' AND (p.title LIKE :search_title OR p.slug LIKE :search_slug OR p.short_description LIKE :search_description)';
                $term = '%' . trim((string) $filters['search']) . '%';
                $params['search_title'] = $term;
                $params['search_slug'] = $term;
                $params['search_description'] = $term;
            }

            $sql .= ' ORDER BY p.sort_order ASC, p.id DESC';
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $rows = $stmt->fetchAll();
            if (is_array($rows) && count($rows) > 0) {
                return $rows;
            }
        } catch (PDOException) {
        }

        $products = $this->fallbackProducts();
        $category = trim((string) ($filters['category'] ?? ''));
        $search = trim((string) ($filters['search'] ?? ''));
        if ($category !== '') {
            $products = array_values(array_filter($products, static fn (array $item): bool => slugify((string) ($item['category_name'] ?? '')) === $category));
        }
        if ($search !== '') {
            $needle = mb_strtolower($search);
            $products = array_values(array_filter($products, static function (array $item) use ($needle): bool {
                $haystack = mb_strtolower(
                    (string) ($item['title'] ?? '') . ' ' .
                    (string) ($item['slug'] ?? '') . ' ' .
                    (string) ($item['short_description'] ?? '')
                );
                return str_contains($haystack, $needle);
            }));
        }
        return $products;
    }

    public function findBySlug(string $slug): ?array
    {
        try {
            $pdo = Database::connection();
            $stmt = $pdo->prepare(
                'SELECT p.*, c.name AS category_name, c.slug AS category_slug,
                        m.storage_path AS featured_image_path
                 FROM products p
                 LEFT JOIN product_categories c ON c.id = p.category_id
                 LEFT JOIN media m ON m.id = p.featured_image_id
                 WHERE p.slug = :slug AND p.status = "published"
                 LIMIT 1'
            );
            $stmt->execute(['slug' => $slug]);
            $product = $stmt->fetch();
            if (!is_array($product)) {
                return null;
            }

            $imagesStmt = $pdo->prepare(
                'SELECT pi.id, pi.alt_text, pi.is_primary, m.storage_path
                 FROM product_images pi
                 INNER JOIN media m ON m.id = pi.media_id
                 WHERE pi.product_id = :product_id
                 ORDER BY pi.sort_order ASC, pi.id ASC'
            );
            $imagesStmt->execute(['product_id' => $product['id']]);
            $images = $imagesStmt->fetchAll();

            $product['gallery'] = is_array($images) ? $images : [];
            $product['specifications'] = json_decode((string) ($product['specifications_json'] ?? '[]'), true) ?: [];
            $product['features'] = json_decode((string) ($product['features_json'] ?? '[]'), true) ?: [];
            $product['applications'] = json_decode((string) ($product['applications_json'] ?? '[]'), true) ?: [];
            return $product;
        } catch (PDOException) {
            foreach ($this->fallbackProducts() as $item) {
                if ($item['slug'] === $slug) {
                    $item['long_description'] = 'Engineered release paper for precision converting and industrial labeling workflows.';
                    $item['specifications'] = [
                        'Base paper' => '60-120 GSM',
                        'Coating type' => 'Silicone Release',
                        'Roll width' => 'Custom',
                    ];
                    $item['features'] = [
                        'Stable release values',
                        'Moisture-resistant coating',
                        'Uniform caliper for high-speed machines',
                    ];
                    $item['applications'] = [
                        'Label manufacturing',
                        'Adhesive tape converting',
                        'Industrial laminate release liners',
                    ];
                    $item['gallery'] = [];
                    return $item;
                }
            }
        }

        return null;
    }

    public function getRelated(string $slug, int $limit = 4): array
    {
        $products = array_filter(
            $this->getPublished(),
            static fn (array $product): bool => ($product['slug'] ?? '') !== $slug
        );
        return array_slice(array_values($products), 0, $limit);
    }

    public function getCatalogCategories(): array
    {
        try {
            $pdo = Database::connection();
            $stmt = $pdo->query(
                'SELECT c.slug, c.name, COUNT(p.id) AS product_count
                 FROM product_categories c
                 LEFT JOIN products p ON p.category_id = c.id AND p.status = "published"
                 WHERE c.is_active = 1
                 GROUP BY c.id, c.slug, c.name
                 ORDER BY c.sort_order ASC, c.id ASC'
            );
            $rows = $stmt->fetchAll();
            if (is_array($rows) && $rows !== []) {
                return array_values(array_filter($rows, static fn (array $row): bool => (int) ($row['product_count'] ?? 0) > 0));
            }
        } catch (PDOException) {
        }

        $map = [];
        foreach ($this->fallbackProducts() as $product) {
            $name = trim((string) ($product['category_name'] ?? 'General'));
            if ($name === '') {
                $name = 'General';
            }
            $slug = slugify($name);
            if (!isset($map[$slug])) {
                $map[$slug] = [
                    'slug' => $slug,
                    'name' => $name,
                    'product_count' => 0,
                ];
            }
            $map[$slug]['product_count']++;
        }

        return array_values($map);
    }

    private function fallbackProducts(): array
    {
        return [
            [
                'id' => 1,
                'title' => 'Pre Gummed Paper',
                'slug' => 'pre-gummed-paper',
                'short_description' => 'Reliable pre-gummed stock for high-volume converting.',
                'category_name' => 'Adhesive Papers',
                'featured_image_path' => '',
                'status' => 'published',
            ],
            [
                'id' => 2,
                'title' => 'Holographic Cold Foil',
                'slug' => 'holographic-cold-foil',
                'short_description' => 'High-impact foil substrate for premium print applications.',
                'category_name' => 'Specialty Foils',
                'featured_image_path' => '',
                'status' => 'published',
            ],
            [
                'id' => 3,
                'title' => 'Pressure Sensitive Paper',
                'slug' => 'pressure-sensitive-paper',
                'short_description' => 'Pressure sensitive paper for durable label performance.',
                'category_name' => 'Label Stocks',
                'featured_image_path' => '',
                'status' => 'published',
            ],
            [
                'id' => 4,
                'title' => 'CCK Release Paper',
                'slug' => 'cck-release-paper',
                'short_description' => 'Clay-coated kraft release liner for industrial adhesive use.',
                'category_name' => 'Release Papers',
                'featured_image_path' => '',
                'status' => 'published',
            ],
            [
                'id' => 5,
                'title' => 'Glassine Release Paper',
                'slug' => 'glassine-release-paper',
                'short_description' => 'Smooth translucent release paper with consistent peel properties.',
                'category_name' => 'Release Papers',
                'featured_image_path' => '',
                'status' => 'published',
            ],
        ];
    }
}
