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