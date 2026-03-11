<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\Database;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use PDOException;

final class SeoAdminController extends BaseAdminController
{
    public function index(Request $request): void
    {
        $this->requireAuth();

        $pageSearch = trim((string) $request->query('page_q', ''));
        $productSearch = trim((string) $request->query('product_q', ''));
        $pageListPage = max(1, (int) $request->query('page_page', 1));
        $productListPage = max(1, (int) $request->query('product_page', 1));
        $perPage = 12;

        $pages = [];
        $products = [];
        $globalSeo = $this->emptySeo('global', 0);
        $scripts = [];
        $pagePagination = $this->buildPagination(0, $pageListPage, $perPage);
        $productPagination = $this->buildPagination(0, $productListPage, $perPage);

        try {
            $pdo = Database::connection();

            $global = $pdo->prepare('SELECT * FROM seo_meta WHERE entity_type = :entity_type AND entity_id = 0 LIMIT 1');
            $global->execute(['entity_type' => 'global']);
            $globalRow = $global->fetch();
            if (is_array($globalRow)) {
                $globalSeo = $globalRow;
            }

            $pageParams = [];
            $pageWhere = '';
            if ($pageSearch !== '') {
                $pageWhere = ' WHERE p.title LIKE :page_title_search OR p.slug LIKE :page_slug_search';
                $pageTerm = '%' . $pageSearch . '%';
                $pageParams['page_title_search'] = $pageTerm;
                $pageParams['page_slug_search'] = $pageTerm;
            }
            $pageCountStmt = $pdo->prepare('SELECT COUNT(*) FROM pages p' . $pageWhere);
            $pageCountStmt->execute($pageParams);
            $pageTotal = (int) ($pageCountStmt->fetchColumn() ?: 0);
            $pagePagination = $this->buildPagination($pageTotal, $pageListPage, $perPage);

            $pagesStmt = $pdo->prepare(
                'SELECT p.id, p.title, p.slug,
                        CASE WHEN sm.id IS NULL THEN 0 ELSE 1 END AS has_seo
                 FROM pages p
                 LEFT JOIN seo_meta sm ON sm.entity_type = "page" AND sm.entity_id = p.id
                 ' . $pageWhere . '
                 ORDER BY p.id ASC
                 LIMIT :limit OFFSET :offset'
            );
            foreach ($pageParams as $key => $value) {
                $pagesStmt->bindValue(':' . $key, $value);
            }
            $pagesStmt->bindValue(':limit', $pagePagination['per_page'], \PDO::PARAM_INT);
            $pagesStmt->bindValue(':offset', $pagePagination['offset'], \PDO::PARAM_INT);
            $pagesStmt->execute();
            $pages = $pagesStmt->fetchAll() ?: [];

            $productParams = [];
            $productWhere = '';
            if ($productSearch !== '') {
                $productWhere = ' WHERE p.title LIKE :product_title_search OR p.slug LIKE :product_slug_search';
                $productTerm = '%' . $productSearch . '%';
                $productParams['product_title_search'] = $productTerm;
                $productParams['product_slug_search'] = $productTerm;
            }
            $productCountStmt = $pdo->prepare('SELECT COUNT(*) FROM products p' . $productWhere);
            $productCountStmt->execute($productParams);
            $productTotal = (int) ($productCountStmt->fetchColumn() ?: 0);
            $productPagination = $this->buildPagination($productTotal, $productListPage, $perPage);

            $productsStmt = $pdo->prepare(
                'SELECT p.id, p.title, p.slug,
                        CASE WHEN sm.id IS NULL THEN 0 ELSE 1 END AS has_seo
                 FROM products p
                 LEFT JOIN seo_meta sm ON sm.entity_type = "product" AND sm.entity_id = p.id
                 ' . $productWhere . '
                 ORDER BY p.id DESC
                 LIMIT :limit OFFSET :offset'
            );
            foreach ($productParams as $key => $value) {
                $productsStmt->bindValue(':' . $key, $value);
            }
            $productsStmt->bindValue(':limit', $productPagination['per_page'], \PDO::PARAM_INT);
            $productsStmt->bindValue(':offset', $productPagination['offset'], \PDO::PARAM_INT);
            $productsStmt->execute();
            $products = $productsStmt->fetchAll() ?: [];

            $scriptsStmt = $pdo->query(
                'SELECT id, location, label, script_content, is_active
                 FROM script_injections
                 ORDER BY location ASC, id ASC'
            );
            $scripts = $scriptsStmt->fetchAll() ?: [];
        } catch (PDOException $exception) {
            Session::flash('error', 'Unable to load SEO data: ' . $exception->getMessage());
        }

        $this->render('admin/seo/index', $request, [
            'meta' => [
                'title' => 'SEO Manager | Nutech Admin',
                'description' => 'Manage global and entity-level metadata.',
            ],
            'pages' => $pages,
            'products' => $products,
            'globalSeo' => $globalSeo,
            'scripts' => $scripts,
            'isAdmin' => Auth::hasRole('admin'),
            'pageSearch' => $pageSearch,
            'productSearch' => $productSearch,
            'pagePagination' => $pagePagination,
            'productPagination' => $productPagination,
        ]);
    }

    public function edit(Request $request, array $params): void
    {
        $this->requireAuth();

        $entityType = (string) ($params['entity_type'] ?? '');
        $entityId = (int) ($params['entity_id'] ?? 0);
        if (!in_array($entityType, ['page', 'product'], true) || $entityId <= 0) {
            Response::redirect('/admin/seo');
        }

        $entity = $this->resolveEntity($entityType, $entityId);
        if ($entity === null) {
            Session::flash('error', 'Entity not found for SEO editing.');
            Response::redirect('/admin/seo');
        }

        $seo = $this->fetchSeo($entityType, $entityId) ?? $this->emptySeo($entityType, $entityId);
        $media = $this->fetchMedia();

        $this->render('admin/seo/edit', $request, [
            'meta' => [
                'title' => 'Edit SEO | Nutech Admin',
                'description' => 'Update SEO metadata.',
            ],
            'entityType' => $entityType,
            'entity' => $entity,
            'seo' => $seo,
            'media' => $media,
        ]);
    }

    public function save(Request $request): void
    {
        $this->requireAuth();
        $this->validateCsrfOrRedirect($request, '/admin/seo');

        $entityType = trim((string) $request->input('entity_type', ''));
        $entityId = (int) $request->input('entity_id', 0);

        if (!in_array($entityType, ['global', 'page', 'product'], true)) {
            Session::flash('error', 'Invalid SEO entity type.');
            Response::redirect('/admin/seo');
        }

        if ($entityType !== 'global' && $entityId <= 0) {
            Session::flash('error', 'Invalid SEO entity id.');
            Response::redirect('/admin/seo');
        }

        if ($entityType === 'global' && !Auth::hasRole('admin')) {
            Session::flash('error', 'Only admins can update global SEO defaults.');
            Response::redirect('/admin/seo');
        }

        $entityId = $entityType === 'global' ? 0 : $entityId;
        $rawSchema = (string) $request->input('schema_json', '');
        $normalizedSchema = $this->normalizeSchema($rawSchema);
        if (trim($rawSchema) !== '' && $normalizedSchema === null) {
            Session::flash('error', 'Schema JSON-LD is invalid JSON.');
            if ($entityType === 'global') {
                Response::redirect('/admin/seo');
            }
            Response::redirect('/admin/seo/edit/' . $entityType . '/' . $entityId);
        }

        $payload = [
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'meta_title' => trim((string) $request->input('meta_title', '')),
            'meta_description' => trim((string) $request->input('meta_description', '')),
            'meta_keywords' => trim((string) $request->input('meta_keywords', '')),
            'canonical_url' => trim((string) $request->input('canonical_url', '')),
            'robots' => trim((string) $request->input('robots', 'index,follow')),
            'og_title' => trim((string) $request->input('og_title', '')),
            'og_description' => trim((string) $request->input('og_description', '')),
            'og_image_id' => ((int) $request->input('og_image_id', 0)) ?: null,
            'schema_json' => $normalizedSchema,
        ];

        try {
            $pdo = Database::connection();
            $stmt = $pdo->prepare(
                'INSERT INTO seo_meta
                    (entity_type, entity_id, meta_title, meta_description, meta_keywords, canonical_url, robots, og_title, og_description, og_image_id, schema_json, created_at, updated_at)
                 VALUES
                    (:entity_type, :entity_id, :meta_title, :meta_description, :meta_keywords, :canonical_url, :robots, :og_title, :og_description, :og_image_id, :schema_json, NOW(), NOW())
                 ON DUPLICATE KEY UPDATE
                    meta_title = VALUES(meta_title),
                    meta_description = VALUES(meta_description),
                    meta_keywords = VALUES(meta_keywords),
                    canonical_url = VALUES(canonical_url),
                    robots = VALUES(robots),
                    og_title = VALUES(og_title),
                    og_description = VALUES(og_description),
                    og_image_id = VALUES(og_image_id),
                    schema_json = VALUES(schema_json),
                    updated_at = NOW()'
            );
            $stmt->execute($payload);
            $this->logActivity($request, 'seo.save', 'seo_meta', null, null, [
                'entity_type' => $entityType,
                'entity_id' => $entityId,
                'meta_title' => $payload['meta_title'],
            ]);
            Session::flash('success', 'SEO metadata saved successfully.');
        } catch (PDOException $exception) {
            Session::flash('error', 'Unable to save SEO metadata: ' . $exception->getMessage());
        }

        if ($entityType === 'global') {
            Response::redirect('/admin/seo');
        }
        Response::redirect('/admin/seo/edit/' . $entityType . '/' . $entityId);
    }

    public function saveScripts(Request $request): void
    {
        $this->requireAdmin();
        $this->validateCsrfOrRedirect($request, '/admin/seo');

        $locations = ['head_start', 'head_end', 'body_end'];

        try {
            $pdo = Database::connection();
            $findStmt = $pdo->prepare('SELECT id FROM script_injections WHERE location = :location LIMIT 1');
            $insertStmt = $pdo->prepare(
                'INSERT INTO script_injections (location, label, script_content, is_active, updated_by, created_at, updated_at)
                 VALUES (:location, :label, :script_content, :is_active, :updated_by, NOW(), NOW())'
            );
            $updateStmt = $pdo->prepare(
                'UPDATE script_injections
                 SET label = :label, script_content = :script_content, is_active = :is_active, updated_by = :updated_by, updated_at = NOW()
                 WHERE id = :id'
            );

            $user = Auth::user();
            $userId = (int) ($user['id'] ?? 0);

            foreach ($locations as $location) {
                $content = (string) $request->input('script_' . $location, '');
                $isActive = $request->input('script_active_' . $location) === '1' ? 1 : 0;
                $label = strtoupper(str_replace('_', ' ', $location));

                $findStmt->execute(['location' => $location]);
                $existingId = (int) ($findStmt->fetchColumn() ?: 0);
                if ($existingId > 0) {
                    $updateStmt->execute([
                        'id' => $existingId,
                        'label' => $label,
                        'script_content' => $content,
                        'is_active' => $isActive,
                        'updated_by' => $userId > 0 ? $userId : null,
                    ]);
                } else {
                    $insertStmt->execute([
                        'location' => $location,
                        'label' => $label,
                        'script_content' => $content,
                        'is_active' => $isActive,
                        'updated_by' => $userId > 0 ? $userId : null,
                    ]);
                }
            }

            $this->logActivity($request, 'scripts.save', 'script_injections', null, null, [
                'locations' => $locations,
            ]);
            Session::flash('success', 'Script injections updated.');
        } catch (PDOException $exception) {
            Session::flash('error', 'Unable to save script injections: ' . $exception->getMessage());
        }

        Response::redirect('/admin/seo');
    }

    private function fetchSeo(string $entityType, int $entityId): ?array
    {
        try {
            $pdo = Database::connection();
            $stmt = $pdo->prepare('SELECT * FROM seo_meta WHERE entity_type = :entity_type AND entity_id = :entity_id LIMIT 1');
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

    private function resolveEntity(string $entityType, int $entityId): ?array
    {
        try {
            $pdo = Database::connection();
            if ($entityType === 'page') {
                $stmt = $pdo->prepare('SELECT id, title, slug FROM pages WHERE id = :id LIMIT 1');
            } else {
                $stmt = $pdo->prepare('SELECT id, title, slug FROM products WHERE id = :id LIMIT 1');
            }
            $stmt->execute(['id' => $entityId]);
            $row = $stmt->fetch();
            return is_array($row) ? $row : null;
        } catch (PDOException) {
            return null;
        }
    }

    private function fetchMedia(): array
    {
        try {
            $pdo = Database::connection();
            $stmt = $pdo->query('SELECT id, original_name, storage_path FROM media ORDER BY id DESC LIMIT 200');
            return $stmt->fetchAll() ?: [];
        } catch (PDOException) {
            return [];
        }
    }

    private function normalizeSchema(string $raw): ?string
    {
        $trimmed = trim($raw);
        if ($trimmed === '') {
            return null;
        }
        $decoded = json_decode($trimmed, true);
        if (json_last_error() !== JSON_ERROR_NONE || !is_array($decoded)) {
            return null;
        }
        return json_encode($decoded, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: null;
    }

    private function emptySeo(string $entityType, int $entityId): array
    {
        return [
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'meta_title' => '',
            'meta_description' => '',
            'meta_keywords' => '',
            'canonical_url' => '',
            'robots' => 'index,follow',
            'og_title' => '',
            'og_description' => '',
            'og_image_id' => null,
            'schema_json' => '',
        ];
    }
}
