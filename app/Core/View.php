<?php
declare(strict_types=1);

namespace App\Core;

final class View
{
    public static function render(string $template, array $data = [], string $layout = 'layouts/public', int $status = 200): void
    {
        http_response_code($status);
        $templatePath = BASE_PATH . '/templates/' . $template . '.php';
        $layoutPath = BASE_PATH . '/templates/' . $layout . '.php';

        if (!is_file($templatePath) || !is_file($layoutPath)) {
            http_response_code(500);
            echo 'Template not found.';
            return;
        }

        extract($data, EXTR_SKIP);

        ob_start();
        require $templatePath;
        $content = (string) ob_get_clean();

        require $layoutPath;
    }
}

