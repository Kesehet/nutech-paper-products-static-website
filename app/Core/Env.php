<?php
declare(strict_types=1);

namespace App\Core;

final class Env
{
    private static array $data = [];

    public static function load(string $path): void
    {
        if (!is_file($path)) {
            return;
        }

        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if ($lines === false) {
            return;
        }

        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '#')) {
                continue;
            }

            $parts = explode('=', $line, 2);
            if (count($parts) !== 2) {
                continue;
            }

            [$key, $value] = $parts;
            $key = trim($key);
            $value = trim($value);
            $value = self::normalizeValue($value);

            self::$data[$key] = $value;
            $_ENV[$key] = $value;
            $_SERVER[$key] = $value;
        }
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        if (array_key_exists($key, self::$data)) {
            return self::$data[$key];
        }

        if (array_key_exists($key, $_ENV)) {
            return $_ENV[$key];
        }

        if (array_key_exists($key, $_SERVER)) {
            return $_SERVER[$key];
        }

        return $default;
    }

    private static function normalizeValue(string $value): string|bool|null
    {
        if ($value === 'true' || $value === '(true)') {
            return true;
        }

        if ($value === 'false' || $value === '(false)') {
            return false;
        }

        if ($value === 'null' || $value === '(null)') {
            return null;
        }

        return trim($value, "\"'");
    }
}

