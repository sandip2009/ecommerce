<?php

namespace App\Repositories;

use PDO;
use App\Interfaces\ProductRepositoryInterface;

class ProductRepository implements ProductRepositoryInterface
{
    public function __construct(
        private PDO $pdo
    ) {
    }

    public function getAll(
        int $limit,
        int $offset,
        ?string $search = null,
        ?float $minPrice = null,
        ?float $maxPrice = null,
        string $sort = 'created_at',
        string $order = 'desc'
    ): array {
        $allowedSorts = [
            'id',
            'name',
            'price',
            'stock',
            'created_at',
            'updated_at',
        ];

        if (!in_array($sort, $allowedSorts, true)) {
            $sort = 'created_at';
        }

        $order = strtolower($order) === 'asc' ? 'ASC' : 'DESC';

        $conditions = [
            'deleted_at IS NULL'
        ];

        $params = [];

        if ($search !== null && $search !== '') {
            $conditions[] = '
                (
                    name LIKE :search
                    OR description LIKE :search
                )
            ';

            $params['search'] = '%' . $search . '%';
        }

        if ($minPrice !== null) {
            $conditions[] = 'price >= :min_price';

            $params['min_price'] = $minPrice;
        }

        if ($maxPrice !== null) {
            $conditions[] = 'price <= :max_price';

            $params['max_price'] = $maxPrice;
        }

        $where = implode( ' AND ', $conditions );

        $sql = "
            SELECT
                id,
                name,
                description,
                price,
                stock,
                status,
                created_at,
                updated_at
            FROM products
            WHERE {$where}
            ORDER BY {$sort} {$order}
            LIMIT :limit
            OFFSET :offset
        ";

        $stmt = $this->pdo->prepare($sql);

        foreach ($params as $key => $value) {
            $stmt->bindValue(
                ':' . $key,
                $value
            );
        }

        $stmt->bindValue(
            ':limit',
            $limit,
            \PDO::PARAM_INT
        );

        $stmt->bindValue(
            ':offset',
            $offset,
            \PDO::PARAM_INT
        );

        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function count(?string $search = null, ?float $minPrice = null, ?float $maxPrice = null): int {
        $conditions = [
            'deleted_at IS NULL'
        ];
        $params = [];
        if ($search !== null && $search !== '') {
            $conditions[] = '
                (
                    name LIKE :search
                    OR description LIKE :search
                )
            ';

            $params['search'] = '%' . $search . '%';
        }

        if ($minPrice !== null) {
            $conditions[] = 'price >= :min_price';

            $params['min_price'] = $minPrice;
        }

        if ($maxPrice !== null) {
            $conditions[] = 'price <= :max_price';

            $params['max_price'] = $maxPrice;
        }

        $where = implode(' AND ',$conditions);

        $sql = "
            SELECT COUNT(*)
            FROM products
            WHERE {$where}
        ";

        $stmt = $this->pdo->prepare($sql);

        $stmt->execute($params);

        return (int) $stmt->fetchColumn();
    }

    public function create(array $data): array
    {
        try {
            $this->pdo->beginTransaction();

            $sql = "
                INSERT INTO products
                (
                    name,
                    description,
                    price,
                    stock,
                    status,
                    created_at,
                    updated_at
                )
                VALUES
                (
                    :name,
                    :description,
                    :price,
                    :stock,
                    :status,
                    NOW(),
                    NOW()
                )
            ";

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                'name' => $data['name'],
                'description' => $data['description'],
                'price' => $data['price'],
                'stock' => $data['stock'],
                'status' => $data['status'],
            ]);
            $id = (int) $this->pdo->lastInsertId();
            $this->pdo->commit();
            return [
                'id' => $id,
                'name' => $data['name'],
                'description' => $data['description'],
                'price' => $data['price'],
                'stock' => $data['stock'],
                'status' => $data['status'],
            ];

        } catch (\Throwable $e) {

            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $e;
        }
    }
    public function findById(int $id): ?array
    {
        $sql = "
            SELECT
                id,
                name,
                description,
                price,
                stock,
                status,
                created_at,
                updated_at
            FROM products
            WHERE id = :id
            AND deleted_at IS NULL
            LIMIT 1
        ";

        $stmt = $this->pdo->prepare($sql);

        $stmt->execute([
            'id' => $id
        ]);

        $product = $stmt->fetch();

        return $product ?: null;
    }

    public function update(int $id, array $data): ?array {
        // First check whether product exists
        $existingProduct = $this->findById($id);

        if ($existingProduct === null) {
            return null;
        }
        $sql = "
            UPDATE products
            SET
                name = :name,
                description = :description,
                price = :price,
                stock = :stock,
                status = :status,
                updated_at = NOW()
            WHERE id = :id
            AND deleted_at IS NULL
        ";

        $stmt = $this->pdo->prepare($sql);

        $stmt->execute([
            'id' => $id,
            'name' => $data['name'],
            'description' => $data['description'],
            'price' => $data['price'],
            'stock' => $data['stock'],
            'status' => $data['status'],
        ]);

        if ($stmt->rowCount() === 0) {
            return null;
        }
        return $this->findById($id);
    }

    public function softDelete(int $id): bool
    {
        $sql = "
            UPDATE products
            SET deleted_at = NOW()
            WHERE id = :id
            AND deleted_at IS NULL
        ";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'id' => $id
        ]);

        return $stmt->rowCount() > 0;
    }
    
}
