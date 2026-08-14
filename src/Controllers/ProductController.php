<?php

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Services\ProductService;
use App\Core\Validator;

class ProductController
{
    // private $productService;
    public function __construct(
        private ProductService $productService
    ) {
    }

    public function index(Request $request): never
    {
        $filters = [
            'page' => $request->query('page', 1),
            'limit' => $request->query('limit', 10),
            'search' => $request->query('search'),
            'min_price' => $request->query('min_price'),
            'max_price' => $request->query('max_price'),
            'sort' => $request->query('sort', 'created_at'),
            'order' => $request->query('order', 'desc'),
        ];

        $result = $this->productService
            ->getProducts($filters);
        // Response::success() is declared as never and terminates execution.
        Response::success(
            $result,
            'Products retrieved successfully.'
        );
    }

    public function store(Request $request): never
    {
        $data = $request->input();

        $validator = new Validator();

        $validator
            ->required('name', $data['name'] ?? null)
            ->string('name', $data['name'] ?? null)
            ->maxLength(
                'name',
                $data['name'] ?? null,
                150
            )

            ->string(
                'description',
                $data['description'] ?? null
            )

            ->required(
                'price',
                $data['price'] ?? null
            )
            ->numeric(
                'price',
                $data['price'] ?? null
            )
            ->min(
                'price',
                $data['price'] ?? null,
                0
            )

            ->required(
                'stock',
                $data['stock'] ?? null
            )
            ->integer(
                'stock',
                $data['stock'] ?? null
            )
            ->min(
                'stock',
                $data['stock'] ?? null,
                0
            )

            ->boolean(
                'status',
                $data['status'] ?? null
            );

        if ($validator->fails()) {
            Response::error(
                'Validation failed.',
                422,
                $validator->errors()
            );
        }

        $product = $this->productService
            ->createProduct($data);

        Response::success(
            $product,
            'Product created successfully.',
            201
        );
    }
}
