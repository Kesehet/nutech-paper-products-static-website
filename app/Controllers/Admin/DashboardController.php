<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\Database;
use App\Core\Request;
use App\Core\Response;
use PDOException;

final class DashboardController extends BaseAdminController
{
    public function index(Request $request): void
    {
        if (!Auth::check()) {
            Response::redirect('/admin/login');
        }

        $stats = [
            'pages' => 0,
            'products' => 0,
            'media' => 0,
            'users' => 0,
        ];
        $recentActivity = [];

        try {
            $pdo = Database::connection();
            $stats['pages'] = (int) ($pdo->query('SELECT COUNT(*) FROM pages')->fetchColumn() ?: 0);
            $stats['products'] = (int) ($pdo->query('SELECT COUNT(*) FROM products')->fetchColumn() ?: 0);
            $stats['media'] = (int) ($pdo->query('SELECT COUNT(*) FROM media')->fetchColumn() ?: 0);
            $stats['users'] = (int) ($pdo->query('SELECT COUNT(*) FROM users')->fetchColumn() ?: 0);
            $activityStmt = $pdo->query(
                'SELECT a.id, a.action, a.entity_type, a.entity_id, a.created_at, u.full_name
                 FROM activity_logs a
                 LEFT JOIN users u ON u.id = a.user_id
                 ORDER BY a.id DESC
                 LIMIT 12'
            );
            $recentActivity = $activityStmt->fetchAll() ?: [];
        } catch (PDOException) {
        }

        $this->render('admin/dashboard/index', $request, [
            'meta' => [
                'title' => 'Admin Dashboard | Nutech Paper Products',
                'description' => 'CMS Dashboard',
            ],
            'stats' => $stats,
            'recentActivity' => $recentActivity,
        ]);
    }
}
