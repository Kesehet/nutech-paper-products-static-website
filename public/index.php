<?php
declare(strict_types=1);

require dirname(__DIR__) . '/app/bootstrap.php';

use App\Controllers\PublicSite\PageController;
use App\Core\Request;
use App\Core\Router;

if (config_bool('APP_REDIRECT_TO_BASE_PATH', false)) {
    $requestPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
    if (!is_string($requestPath) || $requestPath === '') {
        $requestPath = '/';
    }

    $configuredBasePath = app_base_path();

    $scriptName = (string) ($_SERVER['SCRIPT_NAME'] ?? '');
    $scriptDir = normalize_path_prefix(str_replace('\\', '/', dirname($scriptName)));
    if (str_ends_with($scriptDir, '/public')) {
        $scriptDir = substr($scriptDir, 0, -7) ?: '';
    }

    $detectedBasePath = $scriptDir === '/public' ? '' : $scriptDir;

    if ($detectedBasePath !== $configuredBasePath && $detectedBasePath !== '' && str_starts_with($requestPath, $detectedBasePath)) {
        $suffix = substr($requestPath, strlen($detectedBasePath));
        $normalizedSuffix = '/' . ltrim((string) $suffix, '/');
        $normalizedSuffix = rtrim($normalizedSuffix, '/') === '' ? '/' : rtrim($normalizedSuffix, '/');

        $targetPath = ($configuredBasePath === '' ? '' : $configuredBasePath) . ($normalizedSuffix === '/' ? '/' : $normalizedSuffix);
        $query = (string) parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_QUERY);
        $location = $targetPath . ($query !== '' ? '?' . $query : '');

        if ($location !== ($_SERVER['REQUEST_URI'] ?? '')) {
            header('Location: ' . ($location === '' ? '/' : $location), true, 301);
            exit;
        }
    }
}

$request = Request::capture();
$router = new Router();

require BASE_PATH . '/routes/web.php';
require BASE_PATH . '/routes/admin.php';

$router->setNotFoundHandler([PageController::class, 'notFound']);
$router->dispatch($request);
