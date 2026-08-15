<?php

use App\Controllers\ProductController;
use App\Controllers\AuthController;
use App\Core\Response;

$productController = $container->make(
    ProductController::class
);

$authController = $container->make(
    AuthController::class
);

// List
$router->get(
    '/api/products',
    [$productController, 'index'],
    ['auth']
);

// Create
$router->post(
    '/api/products',
    [$productController, 'store'],
    ['auth', 'admin']
);

// Show
$router->get(
    '/api/products/{id}',
    [$productController, 'show'],
    ['auth']
);

// Update - REST
$router->put(
    '/api/products/{id}',
    [$productController, 'update'],
    ['auth', 'admin']
);

// Update - POST fallback
$router->post(
    '/api/products/{id}',
    [$productController, 'update'],
    ['auth', 'admin']
);

// Delete - REST
$router->delete(
    '/api/products/{id}',
    [$productController, 'destroy'],
    ['auth', 'admin']
);

// Delete - POST fallback
$router->post(
    '/api/products/{id}/delete',
    [$productController, 'destroy'],
    ['auth', 'admin']
);

$router->post(
    '/api/register',
    [$authController, 'register']
);

$router->post(
    '/api/login',
    [$authController, 'login']
);

$router->post(
    '/api/logout',
    [$authController, 'logout']
);

$router->get(
    '/api/test-error',
    function () {
        throw new RuntimeException(
            'This is a test exception.'
        );
    }
);