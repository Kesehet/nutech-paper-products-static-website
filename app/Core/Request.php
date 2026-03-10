<?php
declare(strict_types=1);

namespace App\Core;

final class Request
{
    public function __construct(
        private readonly string $method,
        private readonly string $uri,
        private readonly array $query,
        private readonly array $post,
        private readonly array $files,
        private readonly array $server
    ) {
    }

    public static function capture(): self
    {
        $method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        return new self($method, $uri, $_GET, $_POST, $_FILES, $_SERVER);
    }

    public function method(): string
    {
        return $this->method;
    }

    public function uri(): string
    {
        return $this->uri;
    }

    public function path(): string
    {
        $path = parse_url($this->uri, PHP_URL_PATH);
        if (!is_string($path) || $path === '') {
            return '/';
        }

        $basePath = app_base_path();
        if ($basePath !== '' && str_starts_with($path, $basePath)) {
            $path = substr($path, strlen($basePath));
        } elseif ($basePath === '') {
            $detectedPrefix = $this->detectedInstallPrefix();
            if ($detectedPrefix !== '' && str_starts_with($path, $detectedPrefix)) {
                $path = substr($path, strlen($detectedPrefix));
            }
        }

        $normalized = '/' . ltrim($path, '/');
        return rtrim($normalized, '/') === '' ? '/' : rtrim($normalized, '/');
    }

    public function query(string $key, mixed $default = null): mixed
    {
        return $this->query[$key] ?? $default;
    }

    public function input(string $key, mixed $default = null): mixed
    {
        return $this->post[$key] ?? $default;
    }

    public function all(): array
    {
        return $this->post;
    }

    public function files(): array
    {
        return $this->files;
    }

    public function server(string $key, mixed $default = null): mixed
    {
        return $this->server[$key] ?? $default;
    }

    private function detectedInstallPrefix(): string
    {
        $scriptName = (string) ($this->server['SCRIPT_NAME'] ?? '');
        if ($scriptName === '') {
            return '';
        }

        $scriptDir = str_replace('\\', '/', dirname($scriptName));
        $scriptDir = normalize_path_prefix($scriptDir);
        if ($scriptDir === '' || $scriptDir === '/public') {
            return '';
        }

        if (str_ends_with($scriptDir, '/public')) {
            return substr($scriptDir, 0, -7) ?: '';
        }

        return $scriptDir;
    }
}
