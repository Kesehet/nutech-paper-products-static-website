<?php
declare(strict_types=1);

namespace App\Core;

final class Response
{
    public static function redirect(string $path): never
    {
        $target = $path;
        if (str_starts_with($path, '/')) {
            $target = path_url($path);
        }

        header('Location: ' . $target);
        exit;
    }

    public static function json(array $payload, int $status = 200): never
    {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }
}
