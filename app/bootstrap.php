<?php
declare(strict_types=1);

define('BASE_PATH', dirname(__DIR__));

require BASE_PATH . '/app/Helpers/functions.php';

spl_autoload_register(static function (string $class): void {
    $prefix = 'App\\';
    if (strncmp($class, $prefix, strlen($prefix)) !== 0) {
        return;
    }

    $relativeClass = substr($class, strlen($prefix));
    $path = BASE_PATH . '/app/' . str_replace('\\', '/', $relativeClass) . '.php';

    if (is_file($path)) {
        require $path;
    }
});

App\Core\Env::load(BASE_PATH . '/.env');

// First-time installer: if users table is missing or empty, auto-apply schema + seed.
App\Core\AutoSync::runIfRequired();

App\Core\Session::start();

$timezone = env('APP_TIMEZONE', 'UTC');
if (is_string($timezone) && $timezone !== '') {
    date_default_timezone_set($timezone);
}
