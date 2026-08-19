<?php

namespace App\Repositories;

use PDO;

class CartRepository {
    public function __construct(private PDO $pdo) {
    }

    public function findByUserId(int $userId): ?array {
        $sql = "
            SELECT
                id,
                user_id,
                created_at,
                updated_at
            FROM carts
            WHERE user_id = :user_id
            LIMIT 1
        ";

        $stmt = $this->pdo->prepare($sql);

        $stmt->execute([
            'user_id' => $userId
        ]);

        $cart = $stmt->fetch(PDO::FETCH_ASSOC);

        return $cart ?: null;
    }

    public function create(int $userId): int {
        $sql = "
            INSERT INTO carts (
                user_id,
                created_at,
                updated_at
            )
            VALUES (
                :user_id,
                NOW(),
                NOW()
            )
        ";

        $stmt = $this->pdo->prepare($sql);

        $stmt->execute([
            'user_id' => $userId
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    public function findOrCreate(int $userId): array {
        $cart = $this->findByUserId($userId);

        if ($cart !== null) {
            return $cart;
        }

        $cartId = $this->create($userId);

        return [
            'id' => $cartId,
            'user_id' => $userId
        ];
    }

    public function updateTimestamp(int $cartId): bool {
        $sql = "
            UPDATE carts
            SET updated_at = NOW()
            WHERE id = :id
        ";

        $stmt = $this->pdo->prepare($sql);

        return $stmt->execute([
            'id' => $cartId
        ]);
    }

    public function delete(int $cartId): bool {
        $sql = "
            DELETE FROM carts
            WHERE id = :id
        ";

        $stmt = $this->pdo->prepare($sql);

        return $stmt->execute([
            'id' => $cartId
        ]);
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