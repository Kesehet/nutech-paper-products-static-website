<?php
declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use PDOException;

final class SettingService
{
    public function getDefaults(): array
    {
        return $this->defaults();
    }

    public function getSiteTitle(): string
    {
        $settings = $this->getGrouped();

        return (string) ($settings['site']['title'] ?? $this->defaults()['site']['title']);
    }

    public function getGrouped(): array
    {
        $defaults = $this->getDefaults();

        try {
            $pdo = Database::connection();
            $stmt = $pdo->query('SELECT setting_group, setting_key, setting_value FROM settings');
            $rows = $stmt->fetchAll();

            $grouped = [];
            foreach ($rows as $row) {
                $group = (string) ($row['setting_group'] ?? 'site');
                $key = (string) ($row['setting_key'] ?? '');
                if ($key === '') {
                    continue;
                }
                $grouped[$group][$key] = $row['setting_value'];
            }

            return array_replace_recursive($defaults, $grouped);
        } catch (PDOException) {
        }

        return $defaults;
    }

    private function defaults(): array
    {
        return [
            'site' => [
                'title' => 'Nuteck Paper Products',
                'footer_tagline' => 'Trusted partner for high-performance self-adhesive and release paper solutions.',
                'contact_email' => 'info@nuteckpaper.com',
                'contact_phone' => '+91 11 5555 4444',
                'address' => 'Plot No. 45, Okhla Industrial Estate, Phase III, New Delhi - 110020, India',
                'logo_path' => '/assets/img/nuteck_square_logo.png',
                'social_linkedin' => '',
                'social_facebook' => '',
                'social_instagram' => '',
                'home_hero_image' => 'https://images.unsplash.com/photo-1603484477859-abe6a73f9366?q=80&w=687&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D',
                'home_hero_image_alt' => 'Nuteck manufacturing',
            ],
            'theme' => [
                'primary_color' => '#67C6D0',
                'primary_hover_color' => '#2F8FA1',
                'dark_navy_color' => '#0F1B2A',
                'background_light_color' => '#F8FAFC',
            ],
        ];
    }
}
