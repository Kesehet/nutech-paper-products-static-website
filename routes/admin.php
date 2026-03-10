<?php
declare(strict_types=1);

use App\Controllers\Admin\AuthController;
use App\Controllers\Admin\DashboardController;
use App\Controllers\Admin\ModuleController;

$router->get('/admin/login', [AuthController::class, 'showLogin']);
$router->post('/admin/login', [AuthController::class, 'login']);
$router->post('/admin/logout', [AuthController::class, 'logout']);

$router->get('/admin/dashboard', [DashboardController::class, 'index']);
$router->get('/admin/pages', [ModuleController::class, 'pages']);
$router->get('/admin/products', [ModuleController::class, 'products']);
$router->get('/admin/media', [ModuleController::class, 'media']);
$router->get('/admin/seo', [ModuleController::class, 'seo']);
$router->get('/admin/settings', [ModuleController::class, 'settings']);
$router->get('/admin/users', [ModuleController::class, 'users']);

