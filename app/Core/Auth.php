<?php
declare(strict_types=1);

namespace App\Core;

use PDOException;

final class Auth
{
    private const SESSION_KEY = 'auth_user';

    public static function attempt(string $email, string $password): bool
    {
        try {
            $pdo = Database::connection();
            $stmt = $pdo->prepare(
                'SELECT u.id, u.full_name, u.email, u.password_hash, r.slug AS role_slug
                 FROM users u
                 INNER JOIN roles r ON r.id = u.role_id
                 WHERE u.email = :email AND u.is_active = 1
                 LIMIT 1'
            );
            $stmt->execute(['email' => $email]);
            $user = $stmt->fetch();
        } catch (PDOException) {
            return false;
        }

        if (!is_array($user) || !password_verify($password, (string) $user['password_hash'])) {
            return false;
        }

        Session::regenerate();
        Session::put(self::SESSION_KEY, [
            'id' => (int) $user['id'],
            'name' => (string) $user['full_name'],
            'email' => (string) $user['email'],
            'role' => (string) $user['role_slug'],
        ]);

        return true;
    }

    public static function check(): bool
    {
        return is_array(Session::get(self::SESSION_KEY));
    }

    public static function user(): ?array
    {
        $user = Session::get(self::SESSION_KEY);
        return is_array($user) ? $user : null;
    }

    public static function role(): ?string
    {
        $user = self::user();
        return $user['role'] ?? null;
    }

    public static function hasRole(string ...$allowed): bool
    {
        $role = self::role();
        if ($role === null) {
            return false;
        }

        return in_array($role, $allowed, true);
    }

    public static function logout(): void
    {
        Session::forget(self::SESSION_KEY);
        Session::regenerate();
    }
}

