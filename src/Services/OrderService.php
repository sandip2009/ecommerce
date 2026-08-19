<?php

namespace App\Services;

use App\Repositories\CartRepository;
use App\Repositories\CartItemRepository;
use App\Repositories\OrderRepository;
use App\Repositories\OrderItemRepository;
use App\Repositories\ProductRepository;
use PDO;
use InvalidArgumentException;
use RuntimeException;


class OrderService {
    public function __construct(
        private PDO $pdo,
        private CartRepository $cartRepository,
        private CartItemRepository $cartItemRepository,
        private OrderRepository $orderRepository,
        private OrderItemRepository $orderItemRepository,
        private ProductRepository $productRepository
    ) {
    }

    public function checkout(int $userId): array {
        $this->pdo->beginTransaction();

        try {
            $cart = $this->cartRepository->findByUserId($userId);
            if ($cart === null) {
                throw new RuntimeException('Cart not found.');
            }

            $cartItems = $this->cartItemRepository->findByCartId((int) $cart['id']);

            if (empty($cartItems)) {
                throw new RuntimeException('Cart is empty.');
            }
            $total = 0;
            $products = [];
            foreach ($cartItems as $item) {
                $product = $this->productRepository->findById((int) $item['product_id']);
                if ($product === null) {
                    throw new RuntimeException('Product not found.');
                }
                if ((int) $product['status'] !== 1) {
                    throw new RuntimeException("Product {$product['name']} is not available.");
                }
                if ((int) $item['quantity'] > (int) $product['stock']) {
                    throw new RuntimeException("Insufficient stock for {$product['name']}.");
                }
                $quantity = (int) $item['quantity'];
                $price = (float) $product['price'];
                $itemTotal = $price * $quantity;
                $total += $itemTotal;
                $products[] = [
                    'product_id' => (int) $item['product_id'],
                    'quantity' => $quantity,
                    'price' => $price,
                    'total' => $itemTotal,
                    'name' => $product['name'],
                ];
            }

            $orderId = $this->orderRepository->create(
                $userId,
                $total,
                'pending'
            );
            foreach ($products as $item) {
                $this->orderItemRepository->create(
                    $orderId,
                    $userId,
                    $item['product_id'],
                    $item['quantity'],
                    $item['price'],
                    $item['total'],
                    'pending'
                );
                $updated = $this->productRepository->decreaseStock(
                    $item['product_id'],
                    $item['quantity']
                );
                if (!$updated) {
                    throw new RuntimeException("Unable to update stock for {$item['name']}.");
                }
            }

            $this->cartItemRepository->deleteByCartId((int) $cart['id']);
            $this->pdo->commit();
            return $this->orderRepository->findById($orderId);
        } catch (\Throwable $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $e;
        }
    }

    public function getOrders(int $userId): array {
        $orders = $this->orderRepository->findByUserId(
            $userId
        );

        foreach ($orders as &$order) {
            $order['items'] = $this->orderItemRepository->findByOrderId(
                (int) $order['id']
            );
        }

        return $orders;
    }

    public function getOrder(int $orderId, int $userId): ?array {
        $order = $this->orderRepository->findByIdAndUserId($orderId,$userId);
        if ($order === null) {
            return null;
        }
        $order['items'] = $this->orderItemRepository->findByOrderId($orderId);
        return $order;
    }

    public function getAllOrders(int $page = 1, int $perPage = 10): array {
        $result = $this->orderRepository->paginate($page,$perPage);
        $orders = $result['items'];
        if (empty($orders)) {
            $result['items'] = [];
            return $result;
        }
        $orderIds = array_map(fn ($order) => (int) $order['id'],$orders);
        $items = $this->orderItemRepository->findByOrderIds($orderIds);
        $itemsByOrder = [];
        foreach ($items as $item) {
            $itemsByOrder[(int) $item['order_id']][] = [
                'id' => $item['id'],
                'product_id' => $item['product_id'],
                'product_name' => $item['product_name'],
                'quantity' => $item['quantity'],
                'price' => $item['price'],
                'total' => $item['total'],
            ];
        }

        foreach ($orders as &$order) {
            $orderId = (int) $order['id'];
            $order['items'] = $itemsByOrder[$orderId] ?? [];
        }
        $result['items'] = $orders;
        return $result;
    }

    public function updateStatus(int $orderId, string $status): ?array {
        $allowedStatuses = [
            'pending',
            'confirmed',
            'processing',
            'shipped',
            'delivered',
            'cancelled',
        ];
        if (!in_array($status, $allowedStatuses, true)) {
            throw new InvalidArgumentException('Invalid order status.');
        }
        $order = $this->orderRepository->findById($orderId);
        if ($order === null) {
            return null;
        }
        $updated = $this->orderRepository->updateStatus($orderId,$status);
        if (!$updated) {
            throw new RuntimeException('Unable to update order status.');
        }
        return $this->orderRepository->findById($orderId);
    }

    public function cancelOrder(int $orderId): ?array {
        $this->pdo->beginTransaction();
        try {
            $order = $this->orderRepository->findById($orderId);
            if ($order === null) {
                $this->pdo->rollBack();
                return null;
            }
            if ($order['status'] !== 'pending') {
                throw new InvalidArgumentException('Only pending orders can be cancelled.');
            }
            $orderItems = $this->orderItemRepository->findByOrderId($orderId);
            foreach ($orderItems as $item) {
                $updated = $this->productRepository->increaseStock((int) $item['product_id'],(int) $item['quantity']);
                if (!$updated) {
                    throw new RuntimeException('Unable to restore product stock.');
                }
            }
            $updated = $this->orderRepository->updateStatus($orderId,'cancelled');
            if (!$updated) {
                throw new RuntimeException('Unable to cancel order.');
            }
            $this->pdo->commit();
            return $this->orderRepository->findById($orderId);
        } catch (\Throwable $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            throw $e;
        }
    }
}