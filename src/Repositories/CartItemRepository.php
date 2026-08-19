<?php

namespace App\Repositories;

use PDO;

class CartItemRepository {
    public function __construct(private PDO $pdo) {
    }

    public function findByCartId(int $cartId): array {
        $sql = "
            SELECT
                cart_items.id,
                cart_items.cart_id,
                cart_items.product_id,
                cart_items.quantity,
                products.name,
                products.price,
                products.stock
            FROM cart_items
            INNER JOIN products
                ON products.id = cart_items.product_id
            WHERE cart_items.cart_id = :cart_id
            ORDER BY cart_items.id DESC
        ";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'cart_id' => $cartId
        ]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findByCartAndProduct(int $cartId, int $productId): ?array {
        $sql = "
            SELECT
                id,
                cart_id,
                product_id,
                quantity
            FROM cart_items
            WHERE cart_id = :cart_id
              AND product_id = :product_id
            LIMIT 1
        ";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'cart_id' => $cartId,
            'product_id' => $productId
        ]);
        $item = $stmt->fetch(PDO::FETCH_ASSOC);
        return $item ?: null;
    }

    public function create(int $cartId, int $productId, int $quantity): int {
        $sql = "
            INSERT INTO cart_items (
                cart_id,
                product_id,
                quantity
            )
            VALUES (
                :cart_id,
                :product_id,
                :quantity
            )
        ";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'cart_id' => $cartId,
            'product_id' => $productId,
            'quantity' => $quantity
        ]);
        return (int) $this->pdo->lastInsertId();
    }

    public function updateQuantity(int $itemId, int $quantity): bool {
        $sql = "
            UPDATE cart_items
            SET quantity = :quantity
            WHERE id = :id
        ";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            'id' => $itemId,
            'quantity' => $quantity
        ]);
    }

    public function delete(int $itemId): bool {
        $sql = "
            DELETE FROM cart_items
            WHERE id = :id
        ";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            'id' => $itemId
        ]);
    }

    public function deleteByCartId(int $cartId): bool {
        $sql = "
            DELETE FROM cart_items
            WHERE cart_id = :cart_id
        ";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'cart_id' => $cartId
        ]);
        return $stmt->rowCount() > 0;
    }
    
}