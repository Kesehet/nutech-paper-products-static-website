<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\Csrf;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;

final class AuthController extends BaseAdminController
{
    public function showLogin(Request $request): void
    {
        if (Auth::check()) {
            Response::redirect('/admin/dashboard');
        }

        $this->render('admin/auth/login', $request, [
            'meta' => [
                'title' => 'Admin Login | Nuteck Paper Products',
                'description' => 'CMS Admin Login',
            ],
        ]);
    }

    public function login(Request $request): void
    {
        if (!Csrf::validate((string) $request->input('_csrf'))) {
            Session::flash('error', 'Security token validation failed.');
            Response::redirect('/admin/login');
        }

        $email = trim((string) $request->input('email', ''));
        $password = (string) $request->input('password', '');

        if ($email === '' || $password === '') {
            Session::flash('error', 'Email and password are required.');
            Response::redirect('/admin/login');
        }

        if (!Auth::attempt($email, $password)) {
            Session::flash('error', 'Invalid credentials or user not found.');
            Response::redirect('/admin/login');
        }

        $user = Auth::user();
        $this->logActivity($request, 'auth.login', 'user', (int) ($user['id'] ?? 0) ?: null, null, [
            'email' => (string) ($user['email'] ?? ''),
        ]);

        Response::redirect('/admin/dashboard');
    }

    public function logout(Request $request): void
    {
        if (!Csrf::validate((string) $request->input('_csrf'))) {
            Response::redirect('/admin/dashboard');
        }

        $user = Auth::user();
        $this->logActivity($request, 'auth.logout', 'user', (int) ($user['id'] ?? 0) ?: null, null, [
            'email' => (string) ($user['email'] ?? ''),
        ]);

        Auth::logout();
        Session::flash('success', 'You are logged out.');
        Response::redirect('/admin/login');
    }
}
