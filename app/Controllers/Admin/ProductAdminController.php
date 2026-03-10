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
            $stmt = $pdo->prepare(
                'INSERT INTO products
                    (category_id, title, slug, short_description, long_description, specifications_json, features_json, applications_json, sort_order, status, published_at, created_by, updated_by, created_at, updated_at)
                 VALUES
                    (:category_id, :title, :slug, :short_description, :long_description, :specifications_json, :features_json, :applications_json, :sort_order, :status, :published_at, :created_by, :updated_by, NOW(), NOW())'
            );
            $stmt->execute($payload);
            Session::flash('success', 'Product created successfully.');
        } catch (PDOException $exception) {
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

        if ($this->findProduct($id) === null) {
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
                     sort_order = :sort_order,
                     status = :status,
                     published_at = :published_at,
                     updated_by = :updated_by,
                     updated_at = NOW()
                 WHERE id = :id'
            );
            $stmt->execute($payload);
            Session::flash('success', 'Product updated successfully.');
        } catch (PDOException $exception) {
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
            $stmt = $pdo->prepare('DELETE FROM products WHERE id = :id');
            $stmt->execute(['id' => $id]);
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
            'sort_order' => $sortOrder,
            'status' => $status,
            'published_at' => $publishedAt,
            'created_by' => $userId > 0 ? $userId : null,
            'updated_by' => $userId > 0 ? $userId : null,
        ], null];
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
