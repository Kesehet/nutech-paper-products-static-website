<?php
declare(strict_types=1);

namespace App\Core;

final class Csrf
{
    private const KEY = '_csrf_token';

    public static function token(): string
    {
        $token = Session::get(self::KEY);
        if (is_string($token) && $token !== '') {
            return $token;
        }

        $token = bin2hex(random_bytes(32));
        Session::put(self::KEY, $token);
        return $token;
    }

    public static function validate(?string $token): bool
    {
        $stored = Session::get(self::KEY);
        if (!is_string($stored) || $stored === '' || $token === null) {
            return false;
        }

        return hash_equals($stored, $token);
    }
}

