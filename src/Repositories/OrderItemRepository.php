<?php

namespace App\Repositories;

use PDO;

class OrderItemRepository
{
    public function __construct(private PDO $pdo) {
    }

    public function create(
        int $orderId,
        int $userId,
        int $productId,
        int $quantity,
        float $price,
        float $total,
        string $status = 'pending'
    ): int {
        $sql = "
            INSERT INTO order_items (
                order_id,
                user_id,
                product_id,
                quantity,
                price,
                total,
                status
            )
            VALUES (
                :order_id,
                :user_id,
                :product_id,
                :quantity,
                :price,
                :total,
                :status
            )
        ";

        $stmt = $this->pdo->prepare($sql);

        $stmt->execute([
            'order_id' => $orderId,
            'user_id' => $userId,
            'product_id' => $productId,
            'quantity' => $quantity,
            'price' => $price,
            'total' => $total,
            'status' => $status,
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    public function findById(int $id): ?array {
        $sql = "
            SELECT
                order_items.*,
                products.name AS product_name
            FROM order_items
            INNER JOIN products
                ON products.id = order_items.product_id
            WHERE order_items.id = :id
            LIMIT 1
        ";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'id' => $id,
        ]);
        $item = $stmt->fetch(PDO::FETCH_ASSOC);
        return $item ?: null;
    }

    public function updateStatus(int $id, string $status): bool {
        $sql = "
            UPDATE order_items
            SET
                status = :status,
                updated_at = NOW()
            WHERE id = :id
        ";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'status' => $status,
            'id' => $id,
        ]);
        return $stmt->rowCount() > 0;
    }

    public function cancel(int $id, string $reason): bool {
        $sql = "
            UPDATE order_items
            SET
                status = 'cancelled',
                cancel_reason = :reason,
                cancelled_at = NOW(),
                updated_at = NOW()
            WHERE id = :id
              AND status != 'cancelled'
        ";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'reason' => $reason,
            'id' => $id,
        ]);
        return $stmt->rowCount() > 0;
    }
    public function findByOrderId(int $orderId): array {
        $sql = "
            SELECT
                order_items.*,
                products.name AS product_name
            FROM order_items
            INNER JOIN products
                ON products.id = order_items.product_id
            WHERE order_items.order_id = :order_id
            ORDER BY order_items.id ASC
        ";

        $stmt = $this->pdo->prepare($sql);

        $stmt->execute([
            'order_id' => $orderId,
        ]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}