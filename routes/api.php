<?php

use App\Controllers\ProductController;
use App\Core\Response;

$productController = $container->make(
    ProductController::class
);

$router->get(
    '/api/products',
    [$productController, 'index']
);

$router->post(
    '/api/products',
    [$productController, 'store']
);

$router->get(
    '/api/products/{id}',
    [$productController, 'show']
);

$router->put(
    '/api/products/{id}',
    [$productController, 'update']
);

$router->delete(
    '/api/products/{id}',
    [$productController, 'destroy']
);