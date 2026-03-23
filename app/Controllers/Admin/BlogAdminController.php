<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\Database;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use PDOException;

final class BlogAdminController extends BaseAdminController
{
    public function index(Request $request): void
    {
        $this->requireAuth();

        $search = trim((string) $request->query('q', ''));
        $page = max(1, (int) $request->query('page', 1));
        $perPage = 15;
        $blogs = [];
        $pagination = $this->buildPagination(0, $page, $perPage);

        try {
            $pdo = Database::connection();
            $countSql = 'SELECT COUNT(*) FROM blogs b';
            $sql = 'SELECT b.id, b.title, b.slug, b.status, b.updated_at, b.published_at, m.storage_path AS featured_image_path
                    FROM blogs b
                    LEFT JOIN media m ON m.id = b.featured_image_id';
            $params = [];
            if ($search !== '') {
                $where = ' WHERE b.title LIKE :title_search OR b.slug LIKE :slug_search';
                $countSql .= $where;
                $sql .= $where;
                $term = '%' . $search . '%';
                $params['title_search'] = $term;
                $params['slug_search'] = $term;
            }

            $countStmt = $pdo->prepare($countSql);
            $countStmt->execute($params);
            $total = (int) ($countStmt->fetchColumn() ?: 0);
            $pagination = $this->buildPagination($total, $page, $perPage);

            $sql .= ' ORDER BY COALESCE(b.published_at, b.created_at) DESC, b.id DESC LIMIT :limit OFFSET :offset';
            $stmt = $pdo->prepare($sql);
            foreach ($params as $key => $value) {
                $stmt->bindValue(':' . $key, $value);
            }
            $stmt->bindValue(':limit', $pagination['per_page'], \PDO::PARAM_INT);
            $stmt->bindValue(':offset', $pagination['offset'], \PDO::PARAM_INT);
            $stmt->execute();
            $blogs = $stmt->fetchAll() ?: [];
        } catch (PDOException $exception) {
            Session::flash('error', 'Unable to load blogs: ' . $exception->getMessage());
        }

        $this->render('admin/blogs/index', $request, [
            'meta' => [
                'title' => 'Blogs | Nuteck Admin',
                'description' => 'Manage blog posts and publishing.',
            ],
            'blogs' => $blogs,
            'search' => $search,
            'pagination' => $pagination,
        ]);
    }

    public function create(Request $request): void
    {
        $this->requireAuth();
        $this->render('admin/blogs/form', $request, [
            'meta' => [
                'title' => 'Create Blog | Nuteck Admin',
                'description' => 'Create a new blog post.',
            ],
            'blog' => $this->blankBlog(),
            'mediaLibrary' => $this->mediaLibrary(),
            'formAction' => '/admin/blogs/store',
            'submitLabel' => 'Create Blog',
        ]);
    }

    public function store(Request $request): void
    {
        $this->requireAuth();
        $this->validateCsrfOrRedirect($request, '/admin/blogs/create');

        [$payload, $error] = $this->buildBlogPayload($request, null);
        if ($error !== null) {
            Session::flash('error', $error);
            Response::redirect('/admin/blogs/create');
        }

        try {
            $pdo = Database::connection();
            $stmt = $pdo->prepare(
                'INSERT INTO blogs
                    (title, slug, excerpt, content_html, featured_image_id, status, published_at,
                     seo_title, seo_description, seo_keywords, canonical_url, robots, og_title, og_description, og_image_id, schema_json,
                     created_by, updated_by, created_at, updated_at)
                 VALUES
                    (:title, :slug, :excerpt, :content_html, :featured_image_id, :status, :published_at,
                     :seo_title, :seo_description, :seo_keywords, :canonical_url, :robots, :og_title, :og_description, :og_image_id, :schema_json,
                     :created_by, :updated_by, NOW(), NOW())'
            );
            $stmt->execute($payload);
            $blogId = (int) $pdo->lastInsertId();
            $this->logActivity($request, 'blog.create', 'blog', $blogId, null, [
                'title' => $payload['title'] ?? '',
                'slug' => $payload['slug'] ?? '',
                'status' => $payload['status'] ?? '',
            ]);
            Session::flash('success', 'Blog created successfully.');
        } catch (PDOException $exception) {
            Session::flash('error', 'Unable to create blog: ' . $exception->getMessage());
            Response::redirect('/admin/blogs/create');
        }

        Response::redirect('/admin/blogs');
    }

    public function edit(Request $request, array $params): void
    {
        $this->requireAuth();
        $id = (int) ($params['id'] ?? 0);
        if ($id <= 0) {
            Response::redirect('/admin/blogs');
        }

        $blog = $this->findBlog($id);
        if ($blog === null) {
            Session::flash('error', 'Blog not found.');
            Response::redirect('/admin/blogs');
        }

        $this->render('admin/blogs/form', $request, [
            'meta' => [
                'title' => 'Edit Blog | Nuteck Admin',
                'description' => 'Update blog content and SEO.',
            ],
            'blog' => $blog,
            'mediaLibrary' => $this->mediaLibrary(),
            'formAction' => '/admin/blogs/' . $id . '/update',
            'submitLabel' => 'Update Blog',
        ]);
    }

    public function update(Request $request, array $params): void
    {
        $this->requireAuth();
        $id = (int) ($params['id'] ?? 0);
        if ($id <= 0) {
            Response::redirect('/admin/blogs');
        }

        $this->validateCsrfOrRedirect($request, '/admin/blogs/' . $id . '/edit');
        $existing = $this->findBlog($id);
        if ($existing === null) {
            Session::flash('error', 'Blog not found.');
            Response::redirect('/admin/blogs');
        }

        [$payload, $error] = $this->buildBlogPayload($request, $id);
        if ($error !== null) {
            Session::flash('error', $error);
            Response::redirect('/admin/blogs/' . $id . '/edit');
        }

        $payload['id'] = $id;
        unset($payload['created_by']);

        try {
            $pdo = Database::connection();
            $stmt = $pdo->prepare(
                'UPDATE blogs
                 SET title = :title,
                     slug = :slug,
                     excerpt = :excerpt,
                     content_html = :content_html,
                     featured_image_id = :featured_image_id,
                     status = :status,
                     published_at = :published_at,
                     seo_title = :seo_title,
                     seo_description = :seo_description,
                     seo_keywords = :seo_keywords,
                     canonical_url = :canonical_url,
                     robots = :robots,
                     og_title = :og_title,
                     og_description = :og_description,
                     og_image_id = :og_image_id,
                     schema_json = :schema_json,
                     updated_by = :updated_by,
                     updated_at = NOW()
                 WHERE id = :id'
            );
            $stmt->execute($payload);
            $this->logActivity($request, 'blog.update', 'blog', $id, [
                'title' => $existing['title'] ?? '',
                'slug' => $existing['slug'] ?? '',
                'status' => $existing['status'] ?? '',
            ], [
                'title' => $payload['title'] ?? '',
                'slug' => $payload['slug'] ?? '',
                'status' => $payload['status'] ?? '',
            ]);
            Session::flash('success', 'Blog updated successfully.');
        } catch (PDOException $exception) {
            Session::flash('error', 'Unable to update blog: ' . $exception->getMessage());
        }

        Response::redirect('/admin/blogs/' . $id . '/edit');
    }

    public function delete(Request $request, array $params): void
    {
        $this->requireAuth();
        $this->validateCsrfOrRedirect($request, '/admin/blogs');

        $id = (int) ($params['id'] ?? 0);
        if ($id <= 0) {
            Response::redirect('/admin/blogs');
        }

        try {
            $pdo = Database::connection();
            $before = $this->findBlog($id);
            $stmt = $pdo->prepare('DELETE FROM blogs WHERE id = :id');
            $stmt->execute(['id' => $id]);
            $this->logActivity($request, 'blog.delete', 'blog', $id, [
                'title' => $before['title'] ?? '',
                'slug' => $before['slug'] ?? '',
            ], null);
            Session::flash('success', 'Blog deleted successfully.');
        } catch (PDOException $exception) {
            Session::flash('error', 'Unable to delete blog: ' . $exception->getMessage());
        }

        Response::redirect('/admin/blogs');
    }

    private function findBlog(int $id): ?array
    {
        try {
            $pdo = Database::connection();
            $stmt = $pdo->prepare('SELECT * FROM blogs WHERE id = :id LIMIT 1');
            $stmt->execute(['id' => $id]);
            $row = $stmt->fetch();
            return is_array($row) ? $row : null;
        } catch (PDOException) {
            return null;
        }
    }

    private function blankBlog(): array
    {
        return [
            'title' => '',
            'slug' => '',
            'excerpt' => '',
            'content_html' => '',
            'featured_image_id' => '',
            'status' => 'draft',
            'seo_title' => '',
            'seo_description' => '',
            'seo_keywords' => '',
            'canonical_url' => '',
            'robots' => 'index,follow',
            'og_title' => '',
            'og_description' => '',
            'og_image_id' => '',
            'schema_json' => '',
        ];
    }

    private function buildBlogPayload(Request $request, ?int $currentId): array
    {
        $title = trim((string) $request->input('title', ''));
        $slugInput = trim((string) $request->input('slug', ''));
        $slug = slugify($slugInput !== '' ? $slugInput : $title);
        $excerpt = trim((string) $request->input('excerpt', ''));
        $contentHtml = trim((string) $request->input('content_html', ''));
        $status = (string) $request->input('status', 'draft');
        $featuredImageId = (int) $request->input('featured_image_id', 0);
        $ogImageId = (int) $request->input('og_image_id', 0);
        $schemaJson = $this->normalizeSchema((string) $request->input('schema_json', ''));

        if ($title === '') {
            return [[], 'Blog title is required.'];
        }

        if (!in_array($status, ['draft', 'published', 'archived'], true)) {
            return [[], 'Invalid blog status.'];
        }

        if ($this->slugExists($slug, $currentId)) {
            return [[], 'Slug already exists. Choose a different slug.'];
        }

        if (trim((string) $request->input('schema_json', '')) !== '' && $schemaJson === null) {
            return [[], 'Schema JSON-LD must be valid JSON.'];
        }

        $user = Auth::user();
        $userId = (int) ($user['id'] ?? 0) ?: null;
        $publishedAt = $status === 'published'
            ? trim((string) ($request->input('published_at', ''))) ?: date('Y-m-d H:i:s')
            : null;

        return [[
            'title' => $title,
            'slug' => $slug,
            'excerpt' => $excerpt,
            'content_html' => $contentHtml,
            'featured_image_id' => $featuredImageId > 0 ? $featuredImageId : null,
            'status' => $status,
            'published_at' => $publishedAt,
            'seo_title' => trim((string) $request->input('seo_title', '')),
            'seo_description' => trim((string) $request->input('seo_description', '')),
            'seo_keywords' => trim((string) $request->input('seo_keywords', '')),
            'canonical_url' => trim((string) $request->input('canonical_url', '')),
            'robots' => trim((string) $request->input('robots', 'index,follow')) ?: 'index,follow',
            'og_title' => trim((string) $request->input('og_title', '')),
            'og_description' => trim((string) $request->input('og_description', '')),
            'og_image_id' => $ogImageId > 0 ? $ogImageId : null,
            'schema_json' => $schemaJson,
            'created_by' => $userId,
            'updated_by' => $userId,
        ], null];
    }

    private function normalizeSchema(string $raw): ?string
    {
        $trimmed = trim($raw);
        if ($trimmed === '') {
            return null;
        }

        $decoded = json_decode($trimmed, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return null;
        }

        return json_encode($decoded, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) ?: null;
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

    private function slugExists(string $slug, ?int $ignoreId = null): bool
    {
        try {
            $pdo = Database::connection();
            if ($ignoreId !== null) {
                $stmt = $pdo->prepare('SELECT COUNT(*) FROM blogs WHERE slug = :slug AND id != :id');
                $stmt->execute(['slug' => $slug, 'id' => $ignoreId]);
            } else {
                $stmt = $pdo->prepare('SELECT COUNT(*) FROM blogs WHERE slug = :slug');
                $stmt->execute(['slug' => $slug]);
            }

            return (int) $stmt->fetchColumn() > 0;
        } catch (PDOException) {
            return true;
        }
    }
}
