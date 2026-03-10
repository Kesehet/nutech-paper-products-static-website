<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\Csrf;
use App\Core\Database;
use App\Core\Request;
use App\Core\Response;
use App\Core\View;
use PDOException;

final class DashboardController
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

        try {
            $pdo = Database::connection();
            $stats['pages'] = (int) ($pdo->query('SELECT COUNT(*) FROM pages')->fetchColumn() ?: 0);
            $stats['products'] = (int) ($pdo->query('SELECT COUNT(*) FROM products')->fetchColumn() ?: 0);
            $stats['media'] = (int) ($pdo->query('SELECT COUNT(*) FROM media')->fetchColumn() ?: 0);
            $stats['users'] = (int) ($pdo->query('SELECT COUNT(*) FROM users')->fetchColumn() ?: 0);
        } catch (PDOException) {
        }

        View::render('admin/dashboard/index', [
            'meta' => [
                'title' => 'Admin Dashboard | Nutech Paper Products',
                'description' => 'CMS Dashboard',
            ],
            'currentPath' => $request->path(),
            'authUser' => Auth::user(),
            'csrfToken' => Csrf::token(),
            'stats' => $stats,
        ], 'layouts/admin');
    }
}

