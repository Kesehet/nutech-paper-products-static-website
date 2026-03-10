<?php
declare(strict_types=1);

use App\Controllers\PublicSite\PageController;

$router->get('/', [PageController::class, 'home']);
$router->get('/about-us', [PageController::class, 'about']);
$router->get('/contact-us', [PageController::class, 'contact']);
$router->post('/contact-us', [PageController::class, 'submitContact']);
$router->get('/product-catalog', [PageController::class, 'catalog']);
$router->get('/product/{slug}', [PageController::class, 'productDetail']);

