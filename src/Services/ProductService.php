<?php

namespace App\Services;

use App\Interfaces\ProductRepositoryInterface;
use Throwable;

class ProductService
{
    public function __construct(
        private ProductRepositoryInterface $productRepository
    ) {
    }

    public function getProducts(array $filters): array
    {
        $page = max(
            1,
            (int) ($filters['page'] ?? 1)
        );

        $limit = (int) ($filters['limit'] ?? 10);

        if ($limit < 1) {
            $limit = 10;
        }

        if ($limit > 100) {
            $limit = 100;
        }

        $offset = ($page - 1) * $limit;

        $search = $filters['search'] ?? null;

        $minPrice = isset($filters['min_price'])
            ? (float) $filters['min_price']
            : null;

        $maxPrice = isset($filters['max_price'])
            ? (float) $filters['max_price']
            : null;

        $sort = $filters['sort'] ?? 'created_at';

        $order = $filters['order'] ?? 'desc';

        $products = $this->productRepository->getAll(
            $limit,
            $offset,
            $search,
            $minPrice,
            $maxPrice,
            $sort,
            $order
        );

        $total = $this->productRepository->count(
            $search,
            $minPrice,
            $maxPrice
        );

        return [
            'items' => $products,

            'pagination' => [
                'current_page' => $page,
                'per_page' => $limit,
                'total' => $total,
                'total_pages' => $total > 0
                    ? (int) ceil($total / $limit)
                    : 0,
            ],
        ];
    }

    public function createProduct(array $data): array
    {
        try {
            $productData = [
                'name' => trim($data['name']),
                'description' => isset($data['description']) ? trim($data['description']) : null,
                'price' => round((float) $data['price'], 2),
                'stock' => (int) $data['stock'],
                'status' => isset($data['status'])  ? (int) $data['status'] : 1,
            ];
            $this->database->beginTransaction();
            $product = $this->productRepository->create($productData);
            // return $this->productRepository->create($productData);
            $this->database->commit();
             return $product;
        } catch (Throwable $exception) {
            if ($this->database->inTransaction()) {
                $this->database->rollBack();
            }
            throw $exception;
        }
    }
    public function getProduct(int $id): ?array {
        return $this->productRepository->findById($id);
    }
    
    public function updateProduct(int $id,array $data): ?array {
        try {
            
            $productData = [
                'name' => trim($data['name']),
                'description' =>isset($data['description']) ? trim($data['description']) : null,
                'price' => round((float) $data['price'],2),
                'stock' => (int) $data['stock'],
                'status' => isset($data['status']) ? (int) $data['status'] : 1,
            ];
            $this->database->beginTransaction();
            $product = $this->productRepository->update($id,$productData);
            if ($product === null) {
                $this->database->rollBack();
                return null;
            }
            $this->database->commit();
            return $product;
        } catch (Throwable $exception) {
            if ($this->database->inTransaction()) {
                $this->database->rollBack();
            }
            throw $exception;
        }
    }

    public function deleteProduct(int $id): bool {
        return $this->productRepository->softDelete($id);
    }

}