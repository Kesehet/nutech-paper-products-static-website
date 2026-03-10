<?php
declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use PDOException;

final class SettingService
{
    public function getGrouped(): array
    {
        $defaults = $this->defaults();

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
                'title' => 'Nutech Paper Products',
                'contact_email' => 'info@nutechpaper.com',
                'contact_phone' => '+91 11 5555 4444',
                'address' => 'Plot No. 45, Okhla Industrial Estate, Phase III, New Delhi - 110020, India',
                'logo_path' => '/assets/img/nutech_square_logo.png',
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
