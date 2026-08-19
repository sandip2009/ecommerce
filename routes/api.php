<?php

use App\Controllers\ProductController;
use App\Controllers\AuthController;
use App\Controllers\CartController;
use App\Core\Response;
use App\Controllers\OrderController;

$productController = $container->make(
    ProductController::class
);

$authController = $container->make(
    AuthController::class
);

$cartController = $container->make(
    CartController::class
);

$orderController = $container->make(
    OrderController::class
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
    '/api/cart',
    [$cartController, 'index'],
    ['optional_auth']
);

$router->post(
    '/api/cart/add',
    [$cartController, 'add'],
    ['optional_auth']
);

$router->put(
    '/api/cart/{id}',
    [$cartController, 'update'],
    ['optional_auth']
);

$router->post(
    '/api/cart/{id}',
    [$cartController, 'update'],
    ['optional_auth']
);

$router->get(
    '/api/test-error',
    function () {
        throw new RuntimeException(
            'This is a test exception.'
        );
    }
);

$router->delete(
    '/api/cart/{id}',
    [$cartController, 'remove'],
    ['optional_auth']
);

$router->post(
    '/api/cart/{id}/remove',
    [$cartController, 'remove'],
    ['optional_auth']
);

$router->post(
    '/api/checkout',
    [$orderController, 'checkout'],
    ['auth']
);

$router->get(
    '/api/orders',
    [$orderController, 'index'],
    ['auth']
);

$router->get(
    '/api/orders/{id}',
    [$orderController, 'show'],
    ['auth']
);

$router->get(
    '/api/admin/orders',
    [$orderController, 'adminIndex'],
    ['auth', 'admin']
);

$router->put(
    '/api/admin/orders/{id}/status',
    [$orderController, 'updateStatus'],
    ['auth', 'admin']
);

$router->put(
    '/api/admin/orders/{id}/cancel',
    [$orderController, 'cancel'],
    ['auth', 'admin']
);