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

        $result = $this->productService->getProducts($filters);
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
            ->maxLength('name', $data['name'] ?? null, 150)

            ->string('description', $data['description'] ?? null)

            ->required('price', $data['price'] ?? null)
            ->numeric('price', $data['price'] ?? null)
            ->min('price', $data['price'] ?? null,0)

            ->required('stock', $data['stock'] ?? null)
            ->integer('stock', $data['stock'] ?? null)
            ->min('stock', $data['stock'] ?? null,0)

            ->boolean('status', $data['status'] ?? null);

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

    public function show(int $id): never
    {
        $product = $this->productService->getProduct($id);
        if ($product === null) {
            Response::error(
                'Product not found.',
                404
            );
        }
        Response::success(
            $product,
            'Product retrieved successfully.'
        );
    }

    // public function update(Request $request,int $id): never {
    //     $existingProduct = $this->productService->getProduct($id);
    //     if ($existingProduct === null) {
    //         Response::error(
    //             'Product not found.',
    //             404
    //         );
    //     }
    //     $data = $request->input();
    //     $validator = new Validator();

    //     $validator
    //         ->required('name',$data['name'] ?? null)
    //         ->string('name',$data['name'] ?? null)
    //         ->maxLength('name',$data['name'] ?? null,150)
    //         ->required('price',$data['price'] ?? null)
    //         ->numeric('price',$data['price'] ?? null)
    //         ->min('price',$data['price'] ?? null,0)
    //         ->required('stock',$data['stock'] ?? null)
    //         ->integer('stock',$data['stock'] ?? null)
    //         ->min('stock',$data['stock'] ?? null,0)
    //         ->boolean('status',$data['status'] ?? null);

    //     if ($validator->fails()) {
    //         Response::error(
    //             'Validation failed.',
    //             422,
    //             $validator->errors()
    //         );
    //     }

    //     $product = $this->productService->updateProduct($id, $data);

    //     if ($product === null) {
    //         Response::error(
    //             'Unable to update product.',
    //             409
    //         );
    //     }
    //     Response::success(
    //         $product,
    //         'Product updated successfully.'
    //     );
    // }
    public function update(Request $request, int $id): never {
        $existingProduct = $this->productService->getProduct($id);

        if ($existingProduct === null) {
            Response::error(
                'Product not found.',
                404
            );
        }

        $data = $request->input();
        $validator = new Validator();

        if (array_key_exists('name', $data)) {
            $validator->string('name', $data['name'])->maxLength('name',$data['name'],150);
        }

        if (array_key_exists('description', $data)) {
            $validator->string('description',$data['description']);
        }

        if (array_key_exists('price', $data)) {
            $validator->numeric('price',$data['price'])->min('price',$data['price'],0);
        }

        if (array_key_exists('stock', $data)) {
            $validator->integer('stock',$data['stock'])->min('stock',$data['stock'],0);
        }

        if (array_key_exists('status', $data)) {
            $validator->boolean('status',$data['status']);
        }

        if ($validator->fails()) {
            Response::error('Validation failed.',422,$validator->errors());
        }

        $product = $this->productService->updateProduct($id, $data);

        if ($product === null) {
            Response::error(
                'Unable to update product.',
                409
            );
        }

        Response::success(
            $product,
            'Product updated successfully.'
        );
    }

    public function destroy(int $id): never
    {
        $deleted = $this->productService->deleteProduct($id);

        if (!$deleted) {
            Response::error(
                'Product not found.',
                404
            );
        }

        Response::success(
            null,
            'Product deleted successfully.'
        );
    }
}
