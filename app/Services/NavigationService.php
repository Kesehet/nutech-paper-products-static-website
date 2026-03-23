<?php
declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use PDOException;

final class NavigationService
{
    public function getMenu(string $menuKey = 'primary'): array
    {
        try {
            $pdo = Database::connection();
            $stmt = $pdo->prepare(
                'SELECT label, href
                 FROM navigation_items
                 WHERE menu_key = :menu_key AND is_active = 1
                 ORDER BY sort_order ASC, id ASC'
            );
            $stmt->execute(['menu_key' => $menuKey]);
            $rows = $stmt->fetchAll();
            if (is_array($rows) && count($rows) > 0) {
                return $rows;
            }
        } catch (PDOException) {
        }

        return [
            ['label' => 'Home', 'href' => '/'],
            ['label' => 'Products', 'href' => '/product-catalog'],
            ['label' => 'About', 'href' => '/about-us'],
            ['label' => 'Blogs', 'href' => '/blogs'],
            ['label' => 'Contact', 'href' => '/contact-us'],
        ];
    }
}

