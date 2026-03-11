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

        $search = trim((string) $request->query('q', ''));
        $page = max(1, (int) $request->query('page', 1));
        $perPage = 12;
        $pages = [];
        $pagination = $this->buildPagination(0, $page, $perPage);
        try {
            $pdo = Database::connection();
            $params = [];
            $where = '';
            if ($search !== '') {
                $where = ' WHERE p.title LIKE :search_title OR p.slug LIKE :search_slug OR p.template_key LIKE :search_template';
                $term = '%' . $search . '%';
                $params['search_title'] = $term;
                $params['search_slug'] = $term;
                $params['search_template'] = $term;
            }

            $countStmt = $pdo->prepare('SELECT COUNT(*) FROM pages p' . $where);
            $countStmt->execute($params);
            $total = (int) ($countStmt->fetchColumn() ?: 0);
            $pagination = $this->buildPagination($total, $page, $perPage);

            $stmt = $pdo->prepare(
                'SELECT p.id, p.title, p.slug, p.template_key, p.is_published, p.updated_at,
                        COUNT(ps.id) AS section_count
                 FROM pages p
                 LEFT JOIN page_sections ps ON ps.page_id = p.id
                 ' . $where . '
                 GROUP BY p.id
                 ORDER BY p.id ASC
                 LIMIT :limit OFFSET :offset'
            );
            foreach ($params as $key => $value) {
                $stmt->bindValue(':' . $key, $value);
            }
            $stmt->bindValue(':limit', $pagination['per_page'], \PDO::PARAM_INT);
            $stmt->bindValue(':offset', $pagination['offset'], \PDO::PARAM_INT);
            $stmt->execute();
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
            'search' => $search,
            'pagination' => $pagination,
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

            $this->ensureDefaultSections($pdo, $pageId, (string) ($page['slug'] ?? ''));

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
            $this->logActivity($request, 'page.update', 'page', $pageId, null, [
                'title' => $title,
                'is_published' => $isPublished,
            ]);
            Session::flash('success', 'Page content updated successfully.');
        } catch (PDOException $exception) {
            if (isset($pdo) && $pdo->inTransaction()) {
                $pdo->rollBack();
            }
            Session::flash('error', 'Unable to update page: ' . $exception->getMessage());
        }

        Response::redirect('/admin/pages/' . $pageId . '/edit');
    }

    private function ensureDefaultSections(\PDO $pdo, int $pageId, string $slug): void
    {
        $defaults = $this->defaultSectionsBySlug($slug);
        if ($defaults === []) {
            return;
        }

        $existingStmt = $pdo->prepare(
            'SELECT id, section_key, section_label, content_json
             FROM page_sections
             WHERE page_id = :page_id'
        );
        $existingStmt->execute(['page_id' => $pageId]);
        $rows = $existingStmt->fetchAll() ?: [];

        $existingByKey = [];
        foreach ($rows as $row) {
            $key = (string) ($row['section_key'] ?? '');
            if ($key === '') {
                continue;
            }
            $existingByKey[$key] = $row;
        }

        $insertStmt = $pdo->prepare(
            'INSERT INTO page_sections
                (page_id, section_key, section_label, content_json, is_visible, sort_order, created_at, updated_at)
             VALUES
                (:page_id, :section_key, :section_label, :content_json, :is_visible, :sort_order, NOW(), NOW())'
        );
        $updateStmt = $pdo->prepare(
            'UPDATE page_sections
             SET section_label = :section_label, content_json = :content_json, updated_at = NOW()
             WHERE id = :id'
        );

        foreach ($defaults as $definition) {
            $sectionKey = (string) ($definition['section_key'] ?? '');
            if ($sectionKey === '') {
                continue;
            }

            $defaultLabel = (string) ($definition['section_label'] ?? $sectionKey);
            $defaultContent = $definition['content'] ?? [];
            if (!is_array($defaultContent)) {
                $defaultContent = [];
            }

            if (!isset($existingByKey[$sectionKey])) {
                $insertStmt->execute([
                    'page_id' => $pageId,
                    'section_key' => $sectionKey,
                    'section_label' => $defaultLabel,
                    'content_json' => json_encode($defaultContent, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                    'is_visible' => (int) ($definition['is_visible'] ?? 1),
                    'sort_order' => (int) ($definition['sort_order'] ?? 0),
                ]);
                continue;
            }

            $existing = $existingByKey[$sectionKey];
            $existingContent = json_decode((string) ($existing['content_json'] ?? '{}'), true);
            if (!is_array($existingContent)) {
                $existingContent = [];
            }

            // Keep user-entered values while adding missing defaults for new template fields.
            $mergedContent = array_replace($defaultContent, $existingContent);
            $mergedJson = json_encode($mergedContent, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '{}';
            $existingJson = json_encode($existingContent, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '{}';
            $existingLabel = trim((string) ($existing['section_label'] ?? ''));
            $nextLabel = $existingLabel === '' ? $defaultLabel : $existingLabel;

            if ($mergedJson !== $existingJson || $nextLabel !== (string) ($existing['section_label'] ?? '')) {
                $updateStmt->execute([
                    'id' => (int) ($existing['id'] ?? 0),
                    'section_label' => $nextLabel,
                    'content_json' => $mergedJson,
                ]);
            }
        }
    }

    private function defaultSectionsBySlug(string $slug): array
    {
        if ($slug === 'contact-us') {
            return [
                [
                    'section_key' => 'contact.intro',
                    'section_label' => 'Contact Intro',
                    'sort_order' => 1,
                    'is_visible' => 1,
                    'content' => [
                        'heading' => "Let us start a conversation",
                        'description' => 'Have questions about our premium paper products? Our team is ready to help.',
                    ],
                ],
                [
                    'section_key' => 'contact.form',
                    'section_label' => 'Contact Form Labels',
                    'sort_order' => 2,
                    'is_visible' => 1,
                    'content' => [
                        'heading' => 'Get in Touch',
                        'submit_label' => 'Submit Inquiry',
                        'inquiry_label' => 'Inquiry Type',
                        'option_1' => 'Product Inquiry',
                        'option_2' => 'Bulk Order',
                        'option_3' => 'Technical Support',
                        'option_4' => 'General Inquiry',
                    ],
                ],
                [
                    'section_key' => 'contact.sidebar',
                    'section_label' => 'Contact Sidebar',
                    'sort_order' => 3,
                    'is_visible' => 1,
                    'content' => [
                        'details_heading' => 'Contact Details',
                        'urgent_heading' => 'Need urgent assistance?',
                        'urgent_description' => 'Our team usually responds within one business day.',
                        'urgent_button_label' => 'Call Now',
                    ],
                ],
            ];
        }

        if ($slug === 'product-catalog') {
            return [
                [
                    'section_key' => 'catalog.hero',
                    'section_label' => 'Catalog Hero',
                    'sort_order' => 1,
                    'is_visible' => 1,
                    'content' => [
                        'badge' => 'Industrial Excellence',
                        'heading' => 'Premium B2B Paper Solutions',
                        'description' => 'Specializing in high-performance release papers, specialty foils, and adhesive stocks for global manufacturing.',
                        'image_path' => 'https://images.unsplash.com/photo-1581092160562-40aa08e78837?auto=format&fit=crop&w=1800&q=80',
                        'image_alt' => 'Industrial paper manufacturing facility with rolls of paper',
                        'primary_cta_label' => 'Download Brochure',
                        'primary_cta_link' => '/contact-us',
                        'secondary_cta_label' => 'Inquire Now',
                        'secondary_cta_link' => '/contact-us',
                    ],
                ],
                [
                    'section_key' => 'catalog.listing',
                    'section_label' => 'Catalog Listing',
                    'sort_order' => 2,
                    'is_visible' => 1,
                    'content' => [
                        'heading' => 'Product Catalog',
                        'description' => 'Showing all industrial grade materials',
                        'all_label' => 'All Products',
                        'search_placeholder' => 'Search catalog...',
                        'stock_label' => 'In Stock',
                    ],
                ],
                [
                    'section_key' => 'catalog.custom_cta',
                    'section_label' => 'Catalog Custom CTA',
                    'sort_order' => 3,
                    'is_visible' => 1,
                    'content' => [
                        'heading' => 'Custom Requirement?',
                        'description' => "Can't find what you need? We offer custom coating and sizing solutions for unique industrial needs.",
                        'button_label' => 'Contact Sales',
                        'button_link' => '/contact-us',
                    ],
                ],
            ];
        }

        if ($slug !== 'about-us') {
            return [];
        }

        return [
            [
                'section_key' => 'about.hero',
                'section_label' => 'About Hero',
                'sort_order' => 1,
                'is_visible' => 1,
                'content' => [
                    'badge' => 'Precision & Quality',
                    'heading' => 'Pioneering Paper Excellence Since 1995.',
                    'description' => 'Nutech Paper Products is a leader in self-adhesive paper manufacturing, delivering innovative B2B solutions across global industries.',
                    'image_path' => 'https://images.unsplash.com/photo-1581091226033-d5c48150dbaa?auto=format&fit=crop&w=1600&q=80',
                    'image_alt' => 'Industrial paper manufacturing facility with large machinery',
                ],
            ],
            [
                'section_key' => 'about.story',
                'section_label' => 'Our Story',
                'sort_order' => 2,
                'is_visible' => 1,
                'content' => [
                    'heading' => 'Our Story',
                    'description_1' => 'Established in New Delhi in 1995, Nutech has been at the forefront of the paper industry for over two decades. What began as a local vision has grown into a national powerhouse in paper processing.',
                    'description_2' => 'Nutech Paper Products began its journey with a vision to provide high-quality paper solutions. Over the years, we have grown into a leading manufacturer, known for our reliability and innovation in the B2B sector.',
                    'years_value' => '28+',
                    'years_label' => 'Years of Experience',
                    'clients_value' => '1500+',
                    'clients_label' => 'Clients Served',
                    'image_path' => 'https://images.unsplash.com/photo-1567427017947-545c5f8d16ad?auto=format&fit=crop&w=1200&q=80',
                    'image_alt' => 'Close up of high quality adhesive paper rolls',
                ],
            ],
            [
                'section_key' => 'about.expertise',
                'section_label' => 'Our Expertise',
                'sort_order' => 3,
                'is_visible' => 1,
                'content' => [
                    'heading' => 'Our Expertise',
                    'description' => 'Specialized manufacturing of self-adhesive papers and specialized films tailored for industrial requirements.',
                    'item_1_icon' => 'precision_manufacturing',
                    'item_1_title' => 'Self-Adhesive Solutions',
                    'item_1_description' => 'Premium quality Chromo, Mirror Coat, and Woodfree self-adhesive papers for various surfaces.',
                    'item_2_icon' => 'layers',
                    'item_2_title' => 'Specialized Films',
                    'item_2_description' => 'BOPP, PE, and PET films designed for durability and high-performance labeling applications.',
                    'item_3_icon' => 'architecture',
                    'item_3_title' => 'Custom Coating',
                    'item_3_description' => 'Advanced siliconizing and adhesive coating techniques tailored to specific B2B needs.',
                ],
            ],
            [
                'section_key' => 'about.industries',
                'section_label' => 'Industries Served',
                'sort_order' => 4,
                'is_visible' => 1,
                'content' => [
                    'heading' => 'Industries Served',
                    'description' => 'Our products power critical operations across diverse industrial landscapes.',
                    'cta_label' => 'Explore Applications',
                    'cta_link' => '/contact-us',
                    'item_1_title' => 'Packaging',
                    'item_1_image' => 'https://images.unsplash.com/photo-1586528116311-ad8dd3c8310d?auto=format&fit=crop&w=900&q=80',
                    'item_1_alt' => 'Modern logistics and packaging warehouse',
                    'item_2_title' => 'Printing',
                    'item_2_image' => 'https://images.unsplash.com/photo-1589365278144-c9e705f843ba?auto=format&fit=crop&w=900&q=80',
                    'item_2_alt' => 'Commercial printing machine',
                    'item_3_title' => 'Labeling',
                    'item_3_image' => 'https://images.unsplash.com/photo-1609833975787-5c143f373a97?auto=format&fit=crop&w=900&q=80',
                    'item_3_alt' => 'Product containers with adhesive labels',
                    'item_4_title' => 'Pharma & FMCG',
                    'item_4_image' => 'https://images.unsplash.com/photo-1581091215367-59ab6dcef782?auto=format&fit=crop&w=900&q=80',
                    'item_4_alt' => 'High-tech manufacturing facility interior',
                ],
            ],
            [
                'section_key' => 'about.quality',
                'section_label' => 'Quality Commitment',
                'sort_order' => 5,
                'is_visible' => 1,
                'content' => [
                    'heading' => 'Quality Commitment',
                    'description' => "At Nutech, quality is not a department; it's our core philosophy. Every roll of paper that leaves our facility undergoes rigorous testing to ensure it meets international B2B standards. We are committed to sustainable practices and continuous innovation.",
                    'bullet_1' => 'ISO Certified Production Processes',
                    'bullet_2' => '100% In-house Quality Inspection',
                    'bullet_3' => 'Sustainable Material Sourcing',
                    'image_path' => 'https://images.unsplash.com/photo-1582719478250-c89cae4dc85b?auto=format&fit=crop&w=1200&q=80',
                    'image_alt' => 'Engineer inspecting material quality in a laboratory',
                ],
            ],
        ];
    }

    private function renderUser(): array
    {
        /** @var array<string, mixed>|null $user */
        $user = \App\Core\Auth::user();
        return is_array($user) ? $user : [];
    }
}
