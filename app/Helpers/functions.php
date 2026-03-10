<?php
declare(strict_types=1);

if (!function_exists('env')) {
    function env(string $key, mixed $default = null): mixed
    {
        return App\Core\Env::get($key, $default);
    }
}

if (!function_exists('config_bool')) {
    function config_bool(string $key, bool $default = false): bool
    {
        $value = env($key, $default);
        if (is_bool($value)) {
            return $value;
        }

        return filter_var((string) $value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? $default;
    }
}

if (!function_exists('e')) {
    function e(?string $value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('asset')) {
    function asset(string $path): string
    {
        $trimmed = ltrim($path, '/');
        $basePath = app_base_path();
        return ($basePath === '' ? '' : $basePath) . '/' . $trimmed;
    }
}

if (!function_exists('url')) {
    function url(string $path = ''): string
    {
        $base = rtrim((string) env('APP_URL', ''), '/');
        $suffix = ltrim($path, '/');
        return $suffix === '' ? $base : $base . '/' . $suffix;
    }
}

if (!function_exists('app_base_path')) {
    function app_base_path(): string
    {
        $path = parse_url((string) env('APP_URL', ''), PHP_URL_PATH);
        if (!is_string($path) || $path === '' || $path === '/') {
            return '';
        }
        return rtrim($path, '/');
    }
}

if (!function_exists('path_url')) {
    function path_url(string $path = '/'): string
    {
        if ($path === '' || $path === '#') {
            return $path === '' ? '/' : '#';
        }

        if (preg_match('#^(https?:)?//#i', $path) === 1) {
            return $path;
        }

        $basePath = app_base_path();
        $normalized = '/' . ltrim($path, '/');
        return ($basePath === '' ? '' : $basePath) . ($normalized === '//' ? '/' : $normalized);
    }
}
