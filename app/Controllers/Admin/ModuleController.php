<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\Csrf;
use App\Core\Request;
use App\Core\Response;
use App\Core\View;

final class ModuleController
{
    public function pages(Request $request): void
    {
        $this->renderModule($request, 'Pages / Content', 'Manage editable sections for each public page.');
    }

    public function products(Request $request): void
    {
        $this->renderModule($request, 'Products', 'Create, edit, publish, and manage product catalog entries.');
    }

    public function media(Request $request): void
    {
        $this->renderModule($request, 'Media Library', 'Upload, reuse, and organize images/files used across pages and products.');
    }

    public function seo(Request $request): void
    {
        $this->renderModule($request, 'SEO Manager', 'Configure global and entity-level metadata and social previews.');
    }

    public function settings(Request $request): void
    {
        if (!Auth::hasRole('admin')) {
            Response::redirect('/admin/dashboard');
        }

        $this->renderModule($request, 'Site Settings', 'Update branding, contact details, and theme variables.');
    }

    public function users(Request $request): void
    {
        if (!Auth::hasRole('admin')) {
            Response::redirect('/admin/dashboard');
        }

        $this->renderModule($request, 'User Management', 'Manage Admin and Content Editor accounts and permissions.');
    }

    private function renderModule(Request $request, string $moduleTitle, string $summary): void
    {
        if (!Auth::check()) {
            Response::redirect('/admin/login');
        }

        View::render('admin/module-index', [
            'meta' => [
                'title' => $moduleTitle . ' | Nutech Admin',
                'description' => $summary,
            ],
            'currentPath' => $request->path(),
            'authUser' => Auth::user(),
            'csrfToken' => Csrf::token(),
            'moduleTitle' => $moduleTitle,
            'moduleSummary' => $summary,
        ], 'layouts/admin');
    }
}

