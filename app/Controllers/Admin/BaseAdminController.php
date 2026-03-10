<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\Csrf;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\View;
use App\Services\ActivityLogger;

abstract class BaseAdminController
{
    protected function requireAuth(): void
    {
        if (!Auth::check()) {
            Response::redirect('/admin/login');
        }
    }

    protected function requireAdmin(): void
    {
        $this->requireAuth();
        if (!Auth::hasRole('admin')) {
            Session::flash('error', 'You do not have permission to access that module.');
            Response::redirect('/admin/dashboard');
        }
    }

    protected function validateCsrfOrRedirect(Request $request, string $redirectPath): void
    {
        if (!Csrf::validate((string) $request->input('_csrf'))) {
            Session::flash('error', 'Security token validation failed. Please try again.');
            Response::redirect($redirectPath);
        }
    }

    protected function render(string $template, Request $request, array $payload = []): void
    {
        $flashSuccess = array_key_exists('flashSuccess', $payload) ? $payload['flashSuccess'] : Session::pullFlash('success');
        $flashError = array_key_exists('flashError', $payload) ? $payload['flashError'] : Session::pullFlash('error');

        View::render($template, array_merge([
            'meta' => $payload['meta'] ?? [
                'title' => 'Admin | Nutech Paper Products',
                'description' => 'CMS Admin',
            ],
            'currentPath' => $request->path(),
            'authUser' => Auth::user(),
            'csrfToken' => Csrf::token(),
            'flashSuccess' => $flashSuccess,
            'flashError' => $flashError,
        ], $payload), 'layouts/admin');
    }

    protected function logActivity(Request $request, string $action, string $entityType, ?int $entityId = null, ?array $before = null, ?array $after = null): void
    {
        $user = Auth::user();
        ActivityLogger::log(
            (int) ($user['id'] ?? 0) ?: null,
            $action,
            $entityType,
            $entityId,
            $before,
            $after,
            (string) ($request->server('REMOTE_ADDR', '') ?: ''),
            (string) ($request->server('HTTP_USER_AGENT', '') ?: '')
        );
    }
}
