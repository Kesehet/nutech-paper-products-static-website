<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\Database;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use PDOException;

final class ProductAdminController extends BaseAdminController
{
    public function index(Request $request): void
    {
        $this->requireAuth();

        $search = trim((string) $request->query('q', ''));
        $products = [];

        try {
            $pdo = Database::connection();
            $sql = 'SELECT p.id, p.title, p.slug, p.status, p.sort_order, p.updated_at,
                           c.name AS category_name
                    FROM products p
                    LEFT JOIN product_categories c ON c.id = p.category_id';

            $params = [];
            if ($search !== '') {
                $sql .= ' WHERE p.title LIKE :search OR p.slug LIKE :search';
                $params['search'] = '%' . $search . '%';
            }

            $sql .= ' ORDER BY p.sort_order ASC, p.id DESC';
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $products = $stmt->fetchAll() ?: [];
        } catch (PDOException $exception) {
            Session::flash('error', 'Unable to load products: ' . $exception->getMessage());
        }

        $this->render('admin/products/index', $request, [
            'meta' => [
                'title' => 'Products | Nutech Admin',
                'description' => 'Manage product catalog entries.',
            ],
            'products' => $products,
            'search' => $search,
        ]);
    }

    public function create(Request $request): void
    {
        $this->requireAuth();
        $this->render('admin/products/form', $request, [
            'meta' => [
                'title' => 'Create Product | Nutech Admin',
                'description' => 'Add a new catalog product.',
            ],
            'product' => $this->blankProduct(),
            'categories' => $this->categories(),
            'mediaLibrary' => $this->mediaLibrary(),
            'gallerySelections' => [],
            'formAction' => '/admin/products/store',
            'submitLabel' => 'Create Product',
        ]);
    }

    public function store(Request $request): void
    {
        $this->requireAuth();
        $this->validateCsrfOrRedirect($request, '/admin/products/create');

        [$payload, $error] = $this->buildProductPayload($request, null);
        if ($error !== null) {
            Session::flash('error', $error);
            Response::redirect('/admin/products/create');
        }

        try {
            $pdo = Database::connection();
            $pdo->beginTransaction();
            $stmt = $pdo->prepare(
                'INSERT INTO products
                    (category_id, title, slug, short_description, long_description, specifications_json, features_json, applications_json, featured_image_id, sort_order, status, published_at, created_by, updated_by, created_at, updated_at)
                 VALUES
                    (:category_id, :title, :slug, :short_description, :long_description, :specifications_json, :features_json, :applications_json, :featured_image_id, :sort_order, :status, :published_at, :created_by, :updated_by, NOW(), NOW())'
            );
            $stmt->execute($payload);
            $productId = (int) $pdo->lastInsertId();
            $this->syncProductGallery($pdo, $productId, $request);
            $pdo->commit();
            $this->logActivity($request, 'product.create', 'product', $productId, null, [
                'title' => $payload['title'] ?? '',
                'slug' => $payload['slug'] ?? '',
                'status' => $payload['status'] ?? '',
            ]);
            Session::flash('success', 'Product created successfully.');
        } catch (PDOException $exception) {
            if (isset($pdo) && $pdo->inTransaction()) {
                $pdo->rollBack();
            }
            Session::flash('error', 'Unable to create product: ' . $exception->getMessage());
            Response::redirect('/admin/products/create');
        }

        Response::redirect('/admin/products');
    }

    public function edit(Request $request, array $params): void
    {
        $this->requireAuth();
        $id = (int) ($params['id'] ?? 0);
        if ($id <= 0) {
            Response::redirect('/admin/products');
        }

        $product = $this->findProduct($id);
        if ($product === null) {
            Session::flash('error', 'Product not found.');
            Response::redirect('/admin/products');
        }

        $this->render('admin/products/form', $request, [
            'meta' => [
                'title' => 'Edit Product | Nutech Admin',
                'description' => 'Update catalog product.',
            ],
            'product' => $product,
            'categories' => $this->categories(),
            'mediaLibrary' => $this->mediaLibrary(),
            'gallerySelections' => $this->gallerySelections($id),
            'formAction' => '/admin/products/' . $id . '/update',
            'submitLabel' => 'Update Product',
        ]);
    }

    public function update(Request $request, array $params): void
    {
        $this->requireAuth();
        $id = (int) ($params['id'] ?? 0);
        if ($id <= 0) {
            Response::redirect('/admin/products');
        }

        $this->validateCsrfOrRedirect($request, '/admin/products/' . $id . '/edit');

        $existing = $this->findProduct($id);
        if ($existing === null) {
            Session::flash('error', 'Product not found.');
            Response::redirect('/admin/products');
        }

        [$payload, $error] = $this->buildProductPayload($request, $id);
        if ($error !== null) {
            Session::flash('error', $error);
            Response::redirect('/admin/products/' . $id . '/edit');
        }

        $payload['id'] = $id;
        unset($payload['created_by']);

        try {
            $pdo = Database::connection();
            $pdo->beginTransaction();
            $stmt = $pdo->prepare(
                'UPDATE products
                 SET category_id = :category_id,
                     title = :title,
                     slug = :slug,
                     short_description = :short_description,
                     long_description = :long_description,
                     specifications_json = :specifications_json,
                     features_json = :features_json,
                     applications_json = :applications_json,
                     featured_image_id = :featured_image_id,
                     sort_order = :sort_order,
                     status = :status,
                     published_at = :published_at,
                     updated_by = :updated_by,
                     updated_at = NOW()
                 WHERE id = :id'
            );
            $stmt->execute($payload);
            $this->syncProductGallery($pdo, $id, $request);
            $pdo->commit();
            $this->logActivity($request, 'product.update', 'product', $id, [
                'title' => $existing['title'] ?? '',
                'slug' => $existing['slug'] ?? '',
                'status' => $existing['status'] ?? '',
            ], [
                'title' => $payload['title'] ?? '',
                'slug' => $payload['slug'] ?? '',
                'status' => $payload['status'] ?? '',
            ]);
            Session::flash('success', 'Product updated successfully.');
        } catch (PDOException $exception) {
            if (isset($pdo) && $pdo->inTransaction()) {
                $pdo->rollBack();
            }
            Session::flash('error', 'Unable to update product: ' . $exception->getMessage());
        }

        Response::redirect('/admin/products/' . $id . '/edit');
    }

    public function delete(Request $request, array $params): void
    {
        $this->requireAuth();
        $id = (int) ($params['id'] ?? 0);
        if ($id <= 0) {
            Response::redirect('/admin/products');
        }

        $this->validateCsrfOrRedirect($request, '/admin/products');

        try {
            $pdo = Database::connection();
            $before = $this->findProduct($id);
            $stmt = $pdo->prepare('DELETE FROM products WHERE id = :id');
            $stmt->execute(['id' => $id]);
            $this->logActivity($request, 'product.delete', 'product', $id, [
                'title' => $before['title'] ?? '',
                'slug' => $before['slug'] ?? '',
            ], null);
            Session::flash('success', 'Product deleted.');
        } catch (PDOException $exception) {
            Session::flash('error', 'Unable to delete product: ' . $exception->getMessage());
        }

        Response::redirect('/admin/products');
    }

    private function categories(): array
    {
        try {
            $pdo = Database::connection();
            $stmt = $pdo->query('SELECT id, name FROM product_categories WHERE is_active = 1 ORDER BY sort_order ASC, id ASC');
            return $stmt->fetchAll() ?: [];
        } catch (PDOException) {
            return [];
        }
    }

    private function findProduct(int $id): ?array
    {
        try {
            $pdo = Database::connection();
            $stmt = $pdo->prepare('SELECT * FROM products WHERE id = :id LIMIT 1');
            $stmt->execute(['id' => $id]);
            $row = $stmt->fetch();
            if (!is_array($row)) {
                return null;
            }
            return $row;
        } catch (PDOException) {
            return null;
        }
    }

    private function blankProduct(): array
    {
        return [
            'title' => '',
            'slug' => '',
            'category_id' => '',
            'featured_image_id' => '',
            'short_description' => '',
            'long_description' => '',
            'specifications_json' => "{}",
            'features_json' => "[]",
            'applications_json' => "[]",
            'sort_order' => 0,
            'status' => 'draft',
        ];
    }

    private function buildProductPayload(Request $request, ?int $currentId): array
    {
        $title = trim((string) $request->input('title', ''));
        $slugInput = trim((string) $request->input('slug', ''));
        $slug = slugify($slugInput !== '' ? $slugInput : $title);
        $categoryId = (int) $request->input('category_id', 0);
        $shortDescription = trim((string) $request->input('short_description', ''));
        $longDescription = trim((string) $request->input('long_description', ''));
        $sortOrder = (int) $request->input('sort_order', 0);
        $status = (string) $request->input('status', 'draft');
        $featuredImageId = (int) $request->input('featured_image_id', 0);

        if ($title === '') {
            return [[], 'Product title is required.'];
        }

        if (!in_array($status, ['draft', 'published', 'archived'], true)) {
            return [[], 'Invalid product status.'];
        }

        if ($this->slugExists($slug, $currentId)) {
            return [[], 'Slug already exists. Choose a different slug.'];
        }

        $specifications = $this->normalizeJsonString((string) $request->input('specifications_json', '{}'));
        $features = $this->normalizeJsonString((string) $request->input('features_json', '[]'));
        $applications = $this->normalizeJsonString((string) $request->input('applications_json', '[]'));

        $user = Auth::user();
        $userId = (int) ($user['id'] ?? 0);
        $publishedAt = $status === 'published' ? date('Y-m-d H:i:s') : null;

        return [[
            'category_id' => $categoryId > 0 ? $categoryId : null,
            'title' => $title,
            'slug' => $slug,
            'short_description' => $shortDescription,
            'long_description' => $longDescription,
            'specifications_json' => $specifications,
            'features_json' => $features,
            'applications_json' => $applications,
            'featured_image_id' => $featuredImageId > 0 ? $featuredImageId : null,
            'sort_order' => $sortOrder,
            'status' => $status,
            'published_at' => $publishedAt,
            'created_by' => $userId > 0 ? $userId : null,
            'updated_by' => $userId > 0 ? $userId : null,
        ], null];
    }

    private function mediaLibrary(): array
    {
        try {
            $pdo = Database::connection();
            $stmt = $pdo->query(
                'SELECT id, original_name, storage_path, mime_type
                 FROM media
                 ORDER BY id DESC
                 LIMIT 300'
            );
            return $stmt->fetchAll() ?: [];
        } catch (PDOException) {
            return [];
        }
    }

    private function gallerySelections(int $productId): array
    {
        try {
            $pdo = Database::connection();
            $stmt = $pdo->prepare(
                'SELECT media_id, alt_text, sort_order, is_primary
                 FROM product_images
                 WHERE product_id = :product_id
                 ORDER BY sort_order ASC, id ASC'
            );
            $stmt->execute(['product_id' => $productId]);
            $rows = $stmt->fetchAll() ?: [];

            $map = [];
            foreach ($rows as $row) {
                $mediaId = (int) ($row['media_id'] ?? 0);
                if ($mediaId <= 0) {
                    continue;
                }
                $map[$mediaId] = [
                    'alt_text' => (string) ($row['alt_text'] ?? ''),
                    'sort_order' => (int) ($row['sort_order'] ?? 0),
                    'is_primary' => (int) ($row['is_primary'] ?? 0) === 1,
                ];
            }
            return $map;
        } catch (PDOException) {
            return [];
        }
    }

    private function syncProductGallery(\PDO $pdo, int $productId, Request $request): void
    {
        $selectedRaw = $request->input('gallery_media_id', []);
        $selectedIds = [];
        if (is_array($selectedRaw)) {
            foreach ($selectedRaw as $value) {
                $id = (int) $value;
                if ($id > 0) {
                    $selectedIds[$id] = $id;
                }
            }
        }
        $selectedIds = array_values($selectedIds);

        $altMap = $request->input('gallery_alt_text', []);
        $sortMap = $request->input('gallery_sort_order', []);
        $primaryMediaId = (int) $request->input('gallery_primary_media_id', 0);

        $deleteStmt = $pdo->prepare('DELETE FROM product_images WHERE product_id = :product_id');
        $deleteStmt->execute(['product_id' => $productId]);

        if ($selectedIds === []) {
            return;
        }

        $insertStmt = $pdo->prepare(
            'INSERT INTO product_images
                (product_id, media_id, alt_text, is_primary, sort_order, created_at)
             VALUES
                (:product_id, :media_id, :alt_text, :is_primary, :sort_order, NOW())'
        );

        foreach ($selectedIds as $index => $mediaId) {
            $alt = is_array($altMap) ? trim((string) ($altMap[(string) $mediaId] ?? $altMap[$mediaId] ?? '')) : '';
            $sort = is_array($sortMap) ? (int) ($sortMap[(string) $mediaId] ?? $sortMap[$mediaId] ?? ($index + 1)) : ($index + 1);
            $isPrimary = $primaryMediaId > 0 && $primaryMediaId === $mediaId ? 1 : 0;

            $insertStmt->execute([
                'product_id' => $productId,
                'media_id' => $mediaId,
                'alt_text' => $alt,
                'is_primary' => $isPrimary,
                'sort_order' => $sort,
            ]);
        }
    }

    private function normalizeJsonString(string $value): string
    {
        $decoded = json_decode($value, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            return json_encode($decoded, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '{}';
        }
        return json_encode(['raw_text' => $value], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '{}';
    }

    private function slugExists(string $slug, ?int $ignoreId = null): bool
    {
        try {
            $pdo = Database::connection();
            if ($ignoreId !== null) {
                $stmt = $pdo->prepare('SELECT COUNT(*) FROM products WHERE slug = :slug AND id != :id');
                $stmt->execute(['slug' => $slug, 'id' => $ignoreId]);
            } else {
                $stmt = $pdo->prepare('SELECT COUNT(*) FROM products WHERE slug = :slug');
                $stmt->execute(['slug' => $slug]);
            }
            return (int) $stmt->fetchColumn() > 0;
        } catch (PDOException) {
            return true;
        }
    }
}
