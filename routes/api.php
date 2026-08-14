<?php

use App\Controllers\ProductController;
use App\Core\Response;

$productController = $container->make(
    ProductController::class
);

// List
$router->get(
    '/api/products',
    [$productController, 'index']
);

// Create
$router->post(
    '/api/products',
    [$productController, 'store']
);

// Show
$router->get(
    '/api/products/{id}',
    [$productController, 'show']
);

// Update - REST
$router->put(
    '/api/products/{id}',
    [$productController, 'update']
);

// Update - POST fallback
$router->post(
    '/api/products/{id}',
    [$productController, 'update']
);

// Delete - REST
$router->delete(
    '/api/products/{id}',
    [$productController, 'destroy']
);

// Delete - POST fallback
$router->post(
    '/api/products/{id}/delete',
    [$productController, 'destroy']
);

$router->post(
    '/api/login',
    [$authController, 'login']
);

$router->post(
    '/api/logout',
    [$authController, 'logout']
);