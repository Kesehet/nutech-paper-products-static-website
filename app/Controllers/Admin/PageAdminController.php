<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Database;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use PDOException;

final class PageAdminController extends BaseAdminController
{
    public function index(Request $request): void
    {
        $this->requireAuth();

        $pages = [];
        try {
            $pdo = Database::connection();
            $stmt = $pdo->query(
                'SELECT p.id, p.title, p.slug, p.template_key, p.is_published, p.updated_at,
                        COUNT(ps.id) AS section_count
                 FROM pages p
                 LEFT JOIN page_sections ps ON ps.page_id = p.id
                 GROUP BY p.id
                 ORDER BY p.id ASC'
            );
            $pages = $stmt->fetchAll() ?: [];
        } catch (PDOException $exception) {
            Session::flash('error', 'Unable to load pages: ' . $exception->getMessage());
        }

        $this->render('admin/pages/index', $request, [
            'meta' => [
                'title' => 'Pages / Content | Nutech Admin',
                'description' => 'Manage editable content blocks per page.',
            ],
            'pages' => $pages,
        ]);
    }

    public function edit(Request $request, array $params): void
    {
        $this->requireAuth();
        $pageId = (int) ($params['id'] ?? 0);
        if ($pageId <= 0) {
            Response::redirect('/admin/pages');
        }

        $page = null;
        $sections = [];

        try {
            $pdo = Database::connection();
            $pageStmt = $pdo->prepare(
                'SELECT id, title, slug, template_key, is_published
                 FROM pages
                 WHERE id = :id
                 LIMIT 1'
            );
            $pageStmt->execute(['id' => $pageId]);
            $page = $pageStmt->fetch();

            if (!is_array($page)) {
                Session::flash('error', 'Page not found.');
                Response::redirect('/admin/pages');
            }

            $sectionStmt = $pdo->prepare(
                'SELECT id, section_key, section_label, content_json, is_visible, sort_order
                 FROM page_sections
                 WHERE page_id = :page_id
                 ORDER BY sort_order ASC, id ASC'
            );
            $sectionStmt->execute(['page_id' => $pageId]);
            $sections = $sectionStmt->fetchAll() ?: [];
        } catch (PDOException $exception) {
            Session::flash('error', 'Unable to load page details: ' . $exception->getMessage());
            Response::redirect('/admin/pages');
        }

        $this->render('admin/pages/edit', $request, [
            'meta' => [
                'title' => 'Edit Page | Nutech Admin',
                'description' => 'Update page content and sections.',
            ],
            'page' => $page,
            'sections' => $sections,
        ]);
    }

    public function update(Request $request, array $params): void
    {
        $this->requireAuth();
        $pageId = (int) ($params['id'] ?? 0);
        if ($pageId <= 0) {
            Response::redirect('/admin/pages');
        }

        $this->validateCsrfOrRedirect($request, '/admin/pages/' . $pageId . '/edit');

        $title = trim((string) $request->input('title', ''));
        $isPublished = $request->input('is_published') === '1' ? 1 : 0;

        if ($title === '') {
            Session::flash('error', 'Page title is required.');
            Response::redirect('/admin/pages/' . $pageId . '/edit');
        }

        $sectionIds = $request->input('section_id', []);
        $sectionLabels = $request->input('section_label', []);
        $sectionVisibles = $request->input('section_visible', []);
        $sectionJsons = $request->input('section_content', []);
        $sectionSortOrders = $request->input('section_sort_order', []);

        if (!is_array($sectionIds) || !is_array($sectionLabels) || !is_array($sectionJsons)) {
            Session::flash('error', 'Invalid section payload.');
            Response::redirect('/admin/pages/' . $pageId . '/edit');
        }

        try {
            $pdo = Database::connection();
            $pdo->beginTransaction();
            $user = $this->renderUser();
            $userId = (int) ($user['id'] ?? 0);

            $updatePageStmt = $pdo->prepare(
                'UPDATE pages
                 SET title = :title, is_published = :is_published, updated_by = :updated_by, updated_at = NOW()
                 WHERE id = :id'
            );
            $updatePageStmt->execute([
                'title' => $title,
                'is_published' => $isPublished,
                'updated_by' => $userId > 0 ? $userId : null,
                'id' => $pageId,
            ]);

            $updateSectionStmt = $pdo->prepare(
                'UPDATE page_sections
                 SET section_label = :section_label,
                     content_json = :content_json,
                     is_visible = :is_visible,
                     sort_order = :sort_order,
                     updated_at = NOW()
                 WHERE id = :id AND page_id = :page_id'
            );

            $count = count($sectionIds);
            for ($i = 0; $i < $count; $i++) {
                $sectionId = (int) ($sectionIds[$i] ?? 0);
                if ($sectionId <= 0) {
                    continue;
                }

                $rawJson = (string) ($sectionJsons[$i] ?? '{}');
                $decoded = json_decode($rawJson, true);
                if (!is_array($decoded)) {
                    $decoded = ['raw_text' => $rawJson];
                }

                $updateSectionStmt->execute([
                    'section_label' => trim((string) ($sectionLabels[$i] ?? '')),
                    'content_json' => json_encode($decoded, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                    'is_visible' => ((string) ($sectionVisibles[$i] ?? '0') === '1') ? 1 : 0,
                    'sort_order' => (int) ($sectionSortOrders[$i] ?? 0),
                    'id' => $sectionId,
                    'page_id' => $pageId,
                ]);
            }

            $newKey = trim((string) $request->input('new_section_key', ''));
            $newLabel = trim((string) $request->input('new_section_label', ''));
            $newContent = trim((string) $request->input('new_section_content', ''));
            $newVisible = $request->input('new_section_visible') === '1' ? 1 : 0;
            $newSort = (int) $request->input('new_section_sort_order', 0);

            if ($newKey !== '' && $newContent !== '') {
                $decoded = json_decode($newContent, true);
                if (!is_array($decoded)) {
                    $decoded = ['raw_text' => $newContent];
                }

                $insertSectionStmt = $pdo->prepare(
                    'INSERT INTO page_sections (page_id, section_key, section_label, content_json, is_visible, sort_order, created_at, updated_at)
                     VALUES (:page_id, :section_key, :section_label, :content_json, :is_visible, :sort_order, NOW(), NOW())'
                );
                $insertSectionStmt->execute([
                    'page_id' => $pageId,
                    'section_key' => $newKey,
                    'section_label' => $newLabel,
                    'content_json' => json_encode($decoded, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                    'is_visible' => $newVisible,
                    'sort_order' => $newSort,
                ]);
            }

            $pdo->commit();
            Session::flash('success', 'Page content updated successfully.');
        } catch (PDOException $exception) {
            if (isset($pdo) && $pdo->inTransaction()) {
                $pdo->rollBack();
            }
            Session::flash('error', 'Unable to update page: ' . $exception->getMessage());
        }

        Response::redirect('/admin/pages/' . $pageId . '/edit');
    }

    private function renderUser(): array
    {
        /** @var array<string, mixed>|null $user */
        $user = \App\Core\Auth::user();
        return is_array($user) ? $user : [];
    }
}
