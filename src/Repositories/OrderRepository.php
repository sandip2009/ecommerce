<?php

namespace App\Repositories;

use PDO;

class OrderRepository {
    public function __construct(private PDO $pdo) {
    }

    public function create(int $userId, float $totalAmount, string $status = 'pending'): int {
        $sql = "
            INSERT INTO orders (
                user_id,
                total_amount,
                status
            )
            VALUES (
                :user_id,
                :total_amount,
                :status
            )
        ";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'user_id' => $userId,
            'total_amount' => $totalAmount,
            'status' => $status,
        ]);
        return (int) $this->pdo->lastInsertId();
    }
    

    public function findById(int $id): ?array {
        $sql = "
            SELECT
                orders.*,
                users.name AS customer_name,
                users.email AS customer_email
            FROM orders
            INNER JOIN users
                ON users.id = orders.user_id
            WHERE orders.id = :id
            LIMIT 1
        ";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'id' => $id,
        ]);
        $order = $stmt->fetch(PDO::FETCH_ASSOC);
        return $order ?: null;
    }

    public function findByUserId(int $userId): array {
        $sql = "
            SELECT *
            FROM orders
            WHERE user_id = :user_id
            ORDER BY id DESC
        ";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'user_id' => $userId
        ]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findByIdAndUserId(int $orderId, int $userId): ?array {
        $sql = "
            SELECT *
            FROM orders
            WHERE id = :order_id
            AND user_id = :user_id
            LIMIT 1
        ";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'order_id' => $orderId,
            'user_id' => $userId,
        ]);
        $order = $stmt->fetch(PDO::FETCH_ASSOC);
        return $order ?: null;
    }
    public function findAll(): array {
        $sql = "
            SELECT
                orders.*,
                users.name AS customer_name,
                users.email AS customer_email
            FROM orders
            INNER JOIN users
                ON users.id = orders.user_id
            ORDER BY orders.id DESC
        ";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(
            PDO::FETCH_ASSOC
        );
    }

    public function paginate(int $page = 1, int $perPage = 10): array {
        $page = max(1, $page);
        $perPage = max(1, min($perPage, 100));
        $offset = ($page - 1) * $perPage;

        $countSql = "
            SELECT COUNT(*)
            FROM orders
        ";

        $countStmt = $this->pdo->prepare($countSql);
        $countStmt->execute();
        $total = (int) $countStmt->fetchColumn();
        $sql = "
            SELECT
                orders.*,
                users.name AS customer_name,
                users.email AS customer_email
            FROM orders
            INNER JOIN users
                ON users.id = orders.user_id
            ORDER BY orders.id DESC
            LIMIT :limit OFFSET :offset
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return [
            'items' => $stmt->fetchAll(PDO::FETCH_ASSOC),
            'total' => $total,
            'current_page' => $page,
            'per_page' => $perPage,
            'total_pages' => $total > 0 ? (int) ceil($total / $perPage) : 0,
        ];
    }

    public function updateStatus(int $orderId, string $status): bool {
        $sql = "
            UPDATE orders
            SET
                status = :status,
                updated_at = NOW()
            WHERE id = :id
        ";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'status' => $status,
            'id' => $orderId,
        ]);

        return $stmt->rowCount() > 0;
    }
}