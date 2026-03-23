<?php
declare(strict_types=1);

use App\Controllers\Admin\AuthController;
use App\Controllers\Admin\BlogAdminController;
use App\Controllers\Admin\CategoryAdminController;
use App\Controllers\Admin\DashboardController;
use App\Controllers\Admin\MediaAdminController;
use App\Controllers\Admin\PageAdminController;
use App\Controllers\Admin\ProductAdminController;
use App\Controllers\Admin\SeoAdminController;
use App\Controllers\Admin\SettingsAdminController;
use App\Controllers\Admin\UserAdminController;

$router->get('/admin/login', [AuthController::class, 'showLogin']);
$router->post('/admin/login', [AuthController::class, 'login']);
$router->post('/admin/logout', [AuthController::class, 'logout']);

$router->get('/admin/dashboard', [DashboardController::class, 'index']);

$router->get('/admin/pages', [PageAdminController::class, 'index']);
$router->get('/admin/pages/{id}/edit', [PageAdminController::class, 'edit']);
$router->post('/admin/pages/{id}/update', [PageAdminController::class, 'update']);

$router->get('/admin/products', [ProductAdminController::class, 'index']);
$router->get('/admin/blogs', [BlogAdminController::class, 'index']);
$router->get('/admin/blogs/create', [BlogAdminController::class, 'create']);
$router->post('/admin/blogs/store', [BlogAdminController::class, 'store']);
$router->get('/admin/blogs/{id}/edit', [BlogAdminController::class, 'edit']);
$router->post('/admin/blogs/{id}/update', [BlogAdminController::class, 'update']);
$router->post('/admin/blogs/{id}/delete', [BlogAdminController::class, 'delete']);

$router->get('/admin/products/create', [ProductAdminController::class, 'create']);
$router->post('/admin/products/store', [ProductAdminController::class, 'store']);
$router->get('/admin/products/{id}/edit', [ProductAdminController::class, 'edit']);
$router->post('/admin/products/{id}/update', [ProductAdminController::class, 'update']);
$router->post('/admin/products/{id}/delete', [ProductAdminController::class, 'delete']);

$router->get('/admin/categories', [CategoryAdminController::class, 'index']);
$router->post('/admin/categories/store', [CategoryAdminController::class, 'store']);
$router->post('/admin/categories/{id}/update', [CategoryAdminController::class, 'update']);
$router->post('/admin/categories/{id}/delete', [CategoryAdminController::class, 'delete']);

$router->get('/admin/media', [MediaAdminController::class, 'index']);
$router->post('/admin/media/upload', [MediaAdminController::class, 'upload']);
$router->post('/admin/media/upload-async', [MediaAdminController::class, 'uploadAsync']);
$router->post('/admin/media/ckeditor-upload', [MediaAdminController::class, 'ckeditorUpload']);
$router->post('/admin/media/{id}/delete', [MediaAdminController::class, 'delete']);

$router->get('/admin/seo', [SeoAdminController::class, 'index']);
$router->get('/admin/seo/edit/{entity_type}/{entity_id}', [SeoAdminController::class, 'edit']);
$router->post('/admin/seo/save', [SeoAdminController::class, 'save']);
$router->post('/admin/seo/scripts/save', [SeoAdminController::class, 'saveScripts']);

$router->get('/admin/settings', [SettingsAdminController::class, 'index']);
$router->post('/admin/settings/save', [SettingsAdminController::class, 'save']);

$router->get('/admin/users', [UserAdminController::class, 'index']);
$router->post('/admin/users/store', [UserAdminController::class, 'store']);
$router->post('/admin/users/{id}/update', [UserAdminController::class, 'update']);
$router->post('/admin/users/{id}/delete', [UserAdminController::class, 'delete']);
