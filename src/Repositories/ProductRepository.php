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
        $allowedSortColumns = [
            'id',
            'name',
            'price',
            'stock',
            'created_at'
        ];

        if (!in_array($sort, $allowedSortColumns, true)) {
            $sort = 'created_at';
        }
        $order = strtolower($order);
        if (!in_array($order, ['asc', 'desc'], true)) {
            $order = 'desc';
        }

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
            WHERE 1 = 1
        ";

        $params = [];

        if ($search !== null && $search !== '') {
            $sql .= "
                AND (
                    name LIKE :search_name
                    OR description LIKE :search_description
                )
            ";

            $params['search_name'] = '%' . $search . '%';
            $params['search_description'] = '%' . $search . '%';
        }

        if ($minPrice !== null) {
            $sql .= " AND price >= :min_price";
            $params['min_price'] = $minPrice;
        }

        if ($maxPrice !== null) {
            $sql .= " AND price <= :max_price";
            $params['max_price'] = $maxPrice;
        }

        $sql .= " ORDER BY {$sort} {$order}";

        $sql .= " LIMIT :limit OFFSET :offset";

        $stmt = $this->pdo->prepare($sql);

        foreach ($params as $key => $value) {
            $stmt->bindValue(':' . $key, $value);
        }

        $stmt->bindValue(
            ':limit',
            $limit,
            PDO::PARAM_INT
        );

        $stmt->bindValue(
            ':offset',
            $offset,
            PDO::PARAM_INT
        );

        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function count(
        ?string $search = null,
        ?float $minPrice = null,
        ?float $maxPrice = null
    ): int {
        $sql = "
            SELECT COUNT(*)
            FROM products
            WHERE 1 = 1
        ";

        $params = [];

        if ($search !== null && $search !== '') {
            $sql .= "
                AND (
                    name LIKE :search_name
                    OR description LIKE :search_description
                )
            ";

            $params['search_name'] = '%' . $search . '%';
            $params['search_description'] = '%' . $search . '%';
        }

        if ($minPrice !== null) {
            $sql .= " AND price >= :min_price";
            $params['min_price'] = $minPrice;
        }

        if ($maxPrice !== null) {
            $sql .= " AND price <= :max_price";
            $params['max_price'] = $maxPrice;
        }


        $stmt = $this->pdo->prepare($sql);

        $stmt->execute($params);

        return (int) $stmt->fetchColumn();
    }

    public function create(array $data): array
{
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

    return [
        'id' => $id,
        'name' => $data['name'],
        'description' => $data['description'],
        'price' => $data['price'],
        'stock' => $data['stock'],
        'status' => $data['status'],
    ];
}
}
