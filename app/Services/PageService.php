<?php
declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use PDOException;

final class PageService
{
    public function getBySlug(string $slug): ?array
    {
        try {
            $pdo = Database::connection();
            $stmt = $pdo->prepare('SELECT id, title, slug, template_key FROM pages WHERE slug = :slug AND is_published = 1 LIMIT 1');
            $stmt->execute(['slug' => $slug]);
            $page = $stmt->fetch();

            if (!is_array($page)) {
                return null;
            }

            $sectionsStmt = $pdo->prepare(
                'SELECT section_key, section_label, content_json, is_visible, sort_order
                 FROM page_sections
                 WHERE page_id = :page_id
                 ORDER BY sort_order ASC, id ASC'
            );
            $sectionsStmt->execute(['page_id' => $page['id']]);
            $sections = $sectionsStmt->fetchAll();

            $sectionMap = [];
            foreach ($sections as $section) {
                $json = json_decode((string) ($section['content_json'] ?? '{}'), true);
                if (!is_array($json)) {
                    $json = [];
                }

                $sectionMap[$section['section_key']] = [
                    'label' => (string) ($section['section_label'] ?? ''),
                    'is_visible' => (int) ($section['is_visible'] ?? 1) === 1,
                    'content' => $json,
                ];
            }

            return [
                'id' => (int) $page['id'],
                'title' => (string) $page['title'],
                'slug' => (string) $page['slug'],
                'template_key' => (string) $page['template_key'],
                'sections' => $sectionMap,
            ];
        } catch (PDOException) {
            return $this->fallbackPage($slug);
        }
    }

    private function fallbackPage(string $slug): ?array
    {
        $defaults = [
            'home' => [
                'title' => 'Nutech Paper Products | Self Adhesive & Release Paper Manufacturer',
                'sections' => [
                    'home.hero' => [
                        'content' => [
                            'eyebrow' => 'Industrial Excellence Since 1995',
                            'heading' => 'Trusted Manufacturer of Self Adhesive and Release Papers',
                            'description' => 'Premium paper and coating solutions for packaging, labeling, and high-volume industrial use.',
                            'primary_cta_label' => 'View Products',
                            'primary_cta_link' => '/product-catalog',
                        ],
                    ],
                ],
            ],
            'about-us' => [
                'title' => 'About Us | Nutech Paper Products',
                'sections' => [
                    'about.hero' => [
                        'content' => [
                            'heading' => 'Pioneering Paper Excellence Since 1995',
                            'description' => 'Nutech Paper Products delivers dependable self-adhesive and release solutions for B2B industries.',
                        ],
                    ],
                ],
            ],
            'contact-us' => [
                'title' => 'Contact Us | Nutech Paper Products',
                'sections' => [
                    'contact.intro' => [
                        'content' => [
                            'heading' => 'Let us start a conversation',
                            'description' => 'Have questions about our premium paper products? Our team is ready to help.',
                        ],
                    ],
                ],
            ],
            'product-catalog' => [
                'title' => 'Nutech Paper Products | B2B Product Catalog',
                'sections' => [
                    'catalog.hero' => [
                        'content' => [
                            'heading' => 'Premium B2B Paper Solutions',
                            'description' => 'Discover our full range of industrial-grade paper products.',
                        ],
                    ],
                ],
            ],
        ];

        if (!array_key_exists($slug, $defaults)) {
            return null;
        }

        return [
            'id' => 0,
            'title' => $defaults[$slug]['title'],
            'slug' => $slug,
            'template_key' => str_replace('-', '_', $slug),
            'sections' => $defaults[$slug]['sections'],
        ];
    }
}

