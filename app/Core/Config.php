<?php
declare(strict_types=1);

namespace App\Core;

final class Config
{
    public static function appName(): string
    {
        return (string) env('APP_NAME', 'Nuteck Paper Products');
    }

    public static function isDebug(): bool
    {
        return config_bool('APP_DEBUG', false);
    }
}

