<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Database;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use PDOException;

final class CategoryAdminController extends BaseAdminController
{
    public function index(Request $request): void
    {
        $this->requireAuth();

        $categories = [];
        try {
            $pdo = Database::connection();
            $stmt = $pdo->query(
                'SELECT c.id, c.name, c.slug, c.description, c.sort_order, c.is_active,
                        COUNT(p.id) AS product_count
                 FROM product_categories c
                 LEFT JOIN products p ON p.category_id = c.id
                 GROUP BY c.id
                 ORDER BY c.sort_order ASC, c.id ASC'
            );
            $categories = $stmt->fetchAll() ?: [];
        } catch (PDOException $exception) {
            Session::flash('error', 'Unable to load categories: ' . $exception->getMessage());
        }

        $this->render('admin/categories/index', $request, [
            'meta' => [
                'title' => 'Product Categories | Nutech Admin',
                'description' => 'Manage product categories and ordering.',
            ],
            'categories' => $categories,
        ]);
    }

    public function store(Request $request): void
    {
        $this->requireAuth();
        $this->validateCsrfOrRedirect($request, '/admin/categories');

        $name = trim((string) $request->input('name', ''));
        $slugInput = trim((string) $request->input('slug', ''));
        $slug = slugify($slugInput !== '' ? $slugInput : $name);
        $description = trim((string) $request->input('description', ''));
        $sortOrder = (int) $request->input('sort_order', 0);
        $isActive = $request->input('is_active') === '1' ? 1 : 0;

        if ($name === '') {
            Session::flash('error', 'Category name is required.');
            Response::redirect('/admin/categories');
        }

        try {
            $pdo = Database::connection();
            $stmt = $pdo->prepare(
                'INSERT INTO product_categories
                    (name, slug, description, sort_order, is_active, created_at, updated_at)
                 VALUES
                    (:name, :slug, :description, :sort_order, :is_active, NOW(), NOW())'
            );
            $stmt->execute([
                'name' => $name,
                'slug' => $slug,
                'description' => $description,
                'sort_order' => $sortOrder,
                'is_active' => $isActive,
            ]);
            Session::flash('success', 'Category created successfully.');
        } catch (PDOException $exception) {
            Session::flash('error', 'Unable to create category: ' . $exception->getMessage());
        }

        Response::redirect('/admin/categories');
    }

    public function update(Request $request, array $params): void
    {
        $this->requireAuth();
        $this->validateCsrfOrRedirect($request, '/admin/categories');

        $id = (int) ($params['id'] ?? 0);
        if ($id <= 0) {
            Response::redirect('/admin/categories');
        }

        $name = trim((string) $request->input('name', ''));
        $slugInput = trim((string) $request->input('slug', ''));
        $slug = slugify($slugInput !== '' ? $slugInput : $name);
        $description = trim((string) $request->input('description', ''));
        $sortOrder = (int) $request->input('sort_order', 0);
        $isActive = $request->input('is_active') === '1' ? 1 : 0;

        if ($name === '') {
            Session::flash('error', 'Category name is required.');
            Response::redirect('/admin/categories');
        }

        try {
            $pdo = Database::connection();
            $stmt = $pdo->prepare(
                'UPDATE product_categories
                 SET name = :name,
                     slug = :slug,
                     description = :description,
                     sort_order = :sort_order,
                     is_active = :is_active,
                     updated_at = NOW()
                 WHERE id = :id'
            );
            $stmt->execute([
                'id' => $id,
                'name' => $name,
                'slug' => $slug,
                'description' => $description,
                'sort_order' => $sortOrder,
                'is_active' => $isActive,
            ]);
            Session::flash('success', 'Category updated successfully.');
        } catch (PDOException $exception) {
            Session::flash('error', 'Unable to update category: ' . $exception->getMessage());
        }

        Response::redirect('/admin/categories');
    }

    public function delete(Request $request, array $params): void
    {
        $this->requireAuth();
        $this->validateCsrfOrRedirect($request, '/admin/categories');

        $id = (int) ($params['id'] ?? 0);
        if ($id <= 0) {
            Response::redirect('/admin/categories');
        }

        try {
            $pdo = Database::connection();
            $usageStmt = $pdo->prepare('SELECT COUNT(*) FROM products WHERE category_id = :id');
            $usageStmt->execute(['id' => $id]);
            if ((int) $usageStmt->fetchColumn() > 0) {
                Session::flash('error', 'Category is assigned to products. Reassign products before deleting.');
                Response::redirect('/admin/categories');
            }

            $stmt = $pdo->prepare('DELETE FROM product_categories WHERE id = :id');
            $stmt->execute(['id' => $id]);
            Session::flash('success', 'Category deleted.');
        } catch (PDOException $exception) {
            Session::flash('error', 'Unable to delete category: ' . $exception->getMessage());
        }

        Response::redirect('/admin/categories');
    }
}

