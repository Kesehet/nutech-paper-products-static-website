<?php
declare(strict_types=1);

require dirname(__DIR__) . '/app/bootstrap.php';

use App\Controllers\PublicSite\PageController;
use App\Core\Request;
use App\Core\Router;

$request = Request::capture();
$router = new Router();

require BASE_PATH . '/routes/web.php';
require BASE_PATH . '/routes/admin.php';

$router->setNotFoundHandler([PageController::class, 'notFound']);
$router->dispatch($request);

