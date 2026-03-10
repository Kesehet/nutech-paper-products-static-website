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
        return path_url('/' . $trimmed);
    }
}

if (!function_exists('url')) {
    function url(string $path = ''): string
    {
        if ($path !== '' && preg_match('#^(https?:)?//#i', $path) === 1) {
            return $path;
        }

        $baseUrl = app_base_url();
        $basePath = app_base_path();
        $prefix = rtrim($baseUrl . $basePath, '/');
        $suffix = '/' . ltrim($path, '/');

        if ($path === '') {
            return $prefix === '' ? '/' : $prefix;
        }

        if ($prefix === '') {
            return $suffix;
        }

        return $prefix . $suffix;
    }
}

if (!function_exists('normalize_path_prefix')) {
    function normalize_path_prefix(string $value): string
    {
        $trimmed = trim($value);
        if ($trimmed === '' || $trimmed === '/' || $trimmed === '.') {
            return '';
        }

        return '/' . trim($trimmed, '/');
    }
}

if (!function_exists('app_base_path')) {
    function app_base_path(): string
    {
        $configured = env('APP_BASE_PATH', null);
        if ($configured !== null) {
            return normalize_path_prefix((string) $configured);
        }

        $path = parse_url((string) env('APP_URL', ''), PHP_URL_PATH);
        if (!is_string($path)) {
            return '';
        }

        return normalize_path_prefix($path);
    }
}

if (!function_exists('app_base_url')) {
    function app_base_url(): string
    {
        $raw = trim((string) env('APP_URL', ''));
        if ($raw === '') {
            return '';
        }

        $parts = parse_url($raw);
        if (!is_array($parts)) {
            return '';
        }

        $scheme = (string) ($parts['scheme'] ?? '');
        $host = (string) ($parts['host'] ?? '');
        if ($scheme === '' || $host === '') {
            return '';
        }

        $port = isset($parts['port']) ? ':' . (int) $parts['port'] : '';
        return $scheme . '://' . $host . $port;
    }
}

if (!function_exists('app_use_absolute_urls')) {
    function app_use_absolute_urls(): bool
    {
        return config_bool('APP_USE_ABSOLUTE_URLS', false);
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
        $relative = ($basePath === '' ? '' : $basePath) . ($normalized === '//' ? '/' : $normalized);

        if (!app_use_absolute_urls()) {
            return $relative;
        }

        $baseUrl = app_base_url();
        if ($baseUrl === '') {
            return $relative;
        }

        return rtrim($baseUrl, '/') . $relative;
    }
}

if (!function_exists('slugify')) {
    function slugify(string $value): string
    {
        $value = strtolower(trim($value));
        $value = preg_replace('/[^a-z0-9]+/i', '-', $value) ?? '';
        $value = trim($value, '-');
        return $value === '' ? 'item' : $value;
    }
}

if (!function_exists('query_url')) {
    function query_url(string $path, array $query = []): string
    {
        $url = path_url($path);
        $query = array_filter($query, static fn (mixed $value): bool => $value !== null && $value !== '');
        if ($query === []) {
            return $url;
        }
        return $url . '?' . http_build_query($query);
    }
}
