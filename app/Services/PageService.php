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
                            'badge' => 'Precision & Quality',
                            'heading' => 'Pioneering Paper Excellence Since 1995.',
                            'description' => 'Nutech Paper Products delivers dependable self-adhesive and release solutions for B2B industries.',
                            'image_path' => 'https://images.unsplash.com/photo-1581091226033-d5c48150dbaa?auto=format&fit=crop&w=1600&q=80',
                            'image_alt' => 'Industrial paper manufacturing facility with large machinery',
                        ],
                    ],
                    'about.story' => [
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
                    'about.expertise' => [
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
                    'about.industries' => [
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
                    'about.quality' => [
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
