<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\Database;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use PDOException;

final class UserAdminController extends BaseAdminController
{
    public function index(Request $request): void
    {
        $this->requireAdmin();

        $search = trim((string) $request->query('q', ''));
        $page = max(1, (int) $request->query('page', 1));
        $perPage = 10;
        $users = [];
        $roles = [];
        $pagination = $this->buildPagination(0, $page, $perPage);

        try {
            $pdo = Database::connection();

            $roleStmt = $pdo->query('SELECT id, name, slug FROM roles ORDER BY id ASC');
            $roles = $roleStmt->fetchAll() ?: [];

            $params = [];
            $where = '';
            if ($search !== '') {
                $where = ' WHERE u.full_name LIKE :search_full_name OR u.email LIKE :search_email OR r.name LIKE :search_role';
                $term = '%' . $search . '%';
                $params['search_full_name'] = $term;
                $params['search_email'] = $term;
                $params['search_role'] = $term;
            }

            $countStmt = $pdo->prepare(
                'SELECT COUNT(*)
                 FROM users u
                 INNER JOIN roles r ON r.id = u.role_id' . $where
            );
            $countStmt->execute($params);
            $total = (int) ($countStmt->fetchColumn() ?: 0);
            $pagination = $this->buildPagination($total, $page, $perPage);

            $userStmt = $pdo->prepare(
                'SELECT u.id, u.full_name, u.email, u.is_active, u.created_at, u.last_login_at, r.name AS role_name, r.slug AS role_slug, r.id AS role_id
                 FROM users u
                 INNER JOIN roles r ON r.id = u.role_id
                 ' . $where . '
                 ORDER BY u.id ASC
                 LIMIT :limit OFFSET :offset'
            );
            foreach ($params as $key => $value) {
                $userStmt->bindValue(':' . $key, $value);
            }
            $userStmt->bindValue(':limit', $pagination['per_page'], \PDO::PARAM_INT);
            $userStmt->bindValue(':offset', $pagination['offset'], \PDO::PARAM_INT);
            $userStmt->execute();
            $users = $userStmt->fetchAll() ?: [];
        } catch (PDOException $exception) {
            Session::flash('error', 'Unable to load users: ' . $exception->getMessage());
        }

        $this->render('admin/users/index', $request, [
            'meta' => [
                'title' => 'User Management | Nuteck Admin',
                'description' => 'Manage admin and content editor accounts.',
            ],
            'users' => $users,
            'roles' => $roles,
            'search' => $search,
            'pagination' => $pagination,
        ]);
    }

    public function store(Request $request): void
    {
        $this->requireAdmin();
        $this->validateCsrfOrRedirect($request, '/admin/users');

        $fullName = trim((string) $request->input('full_name', ''));
        $email = trim((string) $request->input('email', ''));
        $password = (string) $request->input('password', '');
        $roleId = (int) $request->input('role_id', 0);
        $isActive = $request->input('is_active') === '1' ? 1 : 0;

        if ($fullName === '' || $email === '' || $password === '' || $roleId <= 0) {
            Session::flash('error', 'Full name, email, password, and role are required.');
            Response::redirect('/admin/users');
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            Session::flash('error', 'Invalid email format.');
            Response::redirect('/admin/users');
        }

        try {
            $pdo = Database::connection();
            $stmt = $pdo->prepare(
                'INSERT INTO users (role_id, full_name, email, password_hash, is_active, created_at, updated_at)
                 VALUES (:role_id, :full_name, :email, :password_hash, :is_active, NOW(), NOW())'
            );
            $stmt->execute([
                'role_id' => $roleId,
                'full_name' => $fullName,
                'email' => $email,
                'password_hash' => password_hash($password, PASSWORD_DEFAULT),
                'is_active' => $isActive,
            ]);
            $userId = (int) $pdo->lastInsertId();
            $this->logActivity($request, 'user.create', 'user', $userId, null, [
                'email' => $email,
                'role_id' => $roleId,
                'is_active' => $isActive,
            ]);
            Session::flash('success', 'User created successfully.');
        } catch (PDOException $exception) {
            Session::flash('error', 'Unable to create user: ' . $exception->getMessage());
        }

        Response::redirect('/admin/users');
    }

    public function update(Request $request, array $params): void
    {
        $this->requireAdmin();
        $this->validateCsrfOrRedirect($request, '/admin/users');

        $id = (int) ($params['id'] ?? 0);
        if ($id <= 0) {
            Response::redirect('/admin/users');
        }

        $fullName = trim((string) $request->input('full_name', ''));
        $email = trim((string) $request->input('email', ''));
        $roleId = (int) $request->input('role_id', 0);
        $isActive = $request->input('is_active') === '1' ? 1 : 0;
        $newPassword = (string) $request->input('password', '');

        if ($fullName === '' || $email === '' || $roleId <= 0) {
            Session::flash('error', 'Full name, email, and role are required.');
            Response::redirect('/admin/users');
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            Session::flash('error', 'Invalid email format.');
            Response::redirect('/admin/users');
        }

        $authUser = Auth::user();
        $authUserId = (int) ($authUser['id'] ?? 0);
        if ($authUserId === $id && $isActive === 0) {
            Session::flash('error', 'You cannot deactivate your own account.');
            Response::redirect('/admin/users');
        }

        try {
            $pdo = Database::connection();
            $beforeStmt = $pdo->prepare('SELECT email, role_id, is_active FROM users WHERE id = :id LIMIT 1');
            $beforeStmt->execute(['id' => $id]);
            $before = $beforeStmt->fetch() ?: null;
            $sql = 'UPDATE users
                    SET full_name = :full_name,
                        email = :email,
                        role_id = :role_id,
                        is_active = :is_active,
                        updated_at = NOW()';
            $paramsBind = [
                'full_name' => $fullName,
                'email' => $email,
                'role_id' => $roleId,
                'is_active' => $isActive,
                'id' => $id,
            ];

            if ($newPassword !== '') {
                $sql .= ', password_hash = :password_hash';
                $paramsBind['password_hash'] = password_hash($newPassword, PASSWORD_DEFAULT);
            }

            $sql .= ' WHERE id = :id';
            $stmt = $pdo->prepare($sql);
            $stmt->execute($paramsBind);

            $this->logActivity($request, 'user.update', 'user', $id, is_array($before) ? $before : null, [
                'email' => $email,
                'role_id' => $roleId,
                'is_active' => $isActive,
            ]);
            Session::flash('success', 'User updated successfully.');
        } catch (PDOException $exception) {
            Session::flash('error', 'Unable to update user: ' . $exception->getMessage());
        }

        Response::redirect('/admin/users');
    }

    public function delete(Request $request, array $params): void
    {
        $this->requireAdmin();
        $this->validateCsrfOrRedirect($request, '/admin/users');

        $id = (int) ($params['id'] ?? 0);
        if ($id <= 0) {
            Response::redirect('/admin/users');
        }

        $authUser = Auth::user();
        $authUserId = (int) ($authUser['id'] ?? 0);
        if ($authUserId === $id) {
            Session::flash('error', 'You cannot delete your own account.');
            Response::redirect('/admin/users');
        }

        try {
            $pdo = Database::connection();
            $beforeStmt = $pdo->prepare('SELECT email, role_id FROM users WHERE id = :id LIMIT 1');
            $beforeStmt->execute(['id' => $id]);
            $before = $beforeStmt->fetch() ?: null;
            $stmt = $pdo->prepare('DELETE FROM users WHERE id = :id');
            $stmt->execute(['id' => $id]);
            $this->logActivity($request, 'user.delete', 'user', $id, is_array($before) ? $before : null, null);
            Session::flash('success', 'User deleted successfully.');
        } catch (PDOException $exception) {
            Session::flash('error', 'Unable to delete user: ' . $exception->getMessage());
        }

        Response::redirect('/admin/users');
    }
}
