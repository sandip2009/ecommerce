<?php

namespace App\Services;

use App\Core\Session;
use App\Repositories\CartRepository;
use App\Repositories\CartItemRepository;
use App\Repositories\ProductRepository;

class CartService {
    public function __construct(
        private CartRepository $cartRepository,
        private CartItemRepository $cartItemRepository,
        private ProductRepository $productRepository,
        private Session $session
    ) {
    }

    public function add(int $productId, int $quantity,?int $userId = null): array {
        $product = $this->productRepository->findById(
            $productId
        );

        if ($product === null) {
            throw new \RuntimeException('Product not found.');
        }

        if ((int) $product['status'] !== 1) {
            throw new \RuntimeException('Product is not available.');
        }

        if ($quantity < 1) {
            throw new \RuntimeException('Quantity must be at least 1.');
        }

        if ($quantity > (int) $product['stock']) {
            throw new \RuntimeException('Requested quantity is not available.');
        }

        if ($userId !== null) {
            return $this->addToCustomerCart($userId, $productId, $quantity);
        }

        return $this->addToGuestCart($productId,$quantity);
    }

    private function addToCustomerCart(int $userId, int $productId, int $quantity): array {
        $cart = $this->cartRepository->findOrCreate($userId);
        $item = $this->cartItemRepository->findByCartAndProduct((int) $cart['id'], $productId);
        if ($item !== null) {
            $newQuantity = (int) $item['quantity'] + $quantity;

            $this->cartItemRepository->updateQuantity((int) $item['id'],$newQuantity);
        } else {
            $this->cartItemRepository->create((int) $cart['id'],$productId,$quantity);
        }
        return $this->getCustomerCart((int) $cart['id']);
    }

    private function addToGuestCart(int $productId,int $quantity): array {
        $cart = $this->session->get('cart',[]);
        $cart[$productId] = ($cart[$productId] ?? 0) + $quantity;
        $this->session->put('cart',$cart);
        return $cart;
    }

    private function getCustomerCart(int $cartId): array {
        return $this->cartItemRepository->findByCartId($cartId);
    }
    
    public function mergeGuestCart(int $userId): void {
        $guestCart = $this->session->get('cart', []);

        if (empty($guestCart)) {
            return;
        }

        $cart = $this->cartRepository->findOrCreate($userId);

        foreach ($guestCart as $productId => $quantity) {
            $product = $this->productRepository->findById((int) $productId);

            if ($product === null) {
                continue;
            }

            if ((int) $product['status'] !== 1) {
                continue;
            }

            $availableStock = (int) $product['stock'];
            $quantity = (int) $quantity;

            if ($quantity <= 0 || $availableStock <= 0) {
                continue;
            }
            $quantity = min($quantity,$availableStock);
            $existingItem = $this->cartItemRepository->findByCartAndProduct((int) $cart['id'],(int) $productId);

            if ($existingItem !== null) {
                $newQuantity = (int) $existingItem['quantity'] + $quantity;
                $newQuantity = min($newQuantity,$availableStock);
                $this->cartItemRepository->updateQuantity((int) $existingItem['id'],$newQuantity);
                continue;
            }
            $this->cartItemRepository->create((int) $cart['id'],(int) $productId,$quantity);
        }
        $this->session->forget('cart');
    }

    public function getCart(?int $userId = null): array {
        if ($userId !== null) {
            $cart = $this->cartRepository->findByUserId($userId);
            if ($cart === null) {
                return [
                    'items' => [],
                    'total' => 0
                ];
            }
            $items = $this->cartItemRepository->findByCartId((int) $cart['id']);
            return $this->formatCart($items);
        }

        return $this->getGuestCart();
    }
    private function getGuestCart(): array {
        $guestCart = $this->session->get('cart', []);

        if (empty($guestCart)) {
            return [
                'items' => [],
                'total' => 0
            ];
        }

        $items = [];
        $total = 0;

        foreach ($guestCart as $productId => $quantity) {
            $product = $this->productRepository->findById((int) $productId);

            if ($product === null) {
                continue;
            }

            if ((int) $product['status'] !== 1) {
                continue;
            }

            $quantity = (int) $quantity;
            $price = (float) $product['price'];
            $subtotal = $price * $quantity;

            $items[] = [
                'product_id' => (int) $product['id'],
                'name' => $product['name'],
                'price' => $product['price'],
                'quantity' => $quantity,
                'subtotal' => number_format($subtotal, 2, '.', '')
            ];

            $total += $subtotal;
        }
        return [
            'items' => $items,
            'total' => number_format($total, 2, '.', '')
        ];
    }

    private function formatCart(array $items): array {
        $total = 0;
        foreach ($items as &$item) {
            $subtotal =(float) $item['price'] * (int) $item['quantity'];
            $item['subtotal'] = number_format($subtotal, 2, '.', '');
            $total += $subtotal;
        }
        unset($item);
        return [
            'items' => $items,
            'total' => number_format($total, 2, '.', '')
        ];
    }

    public function updateQuantity(int $productId, int $quantity, ?int $userId = null): array {
        if ($quantity < 1) {
            throw new \RuntimeException('Quantity must be at least 1.');
        }

        $product = $this->productRepository->findById($productId);

        if ($product === null) {
            throw new \RuntimeException('Product not found.');
        }

        if ((int) $product['status'] !== 1) {
            throw new \RuntimeException('Product is not available.');
        }

        if ($quantity > (int) $product['stock']) {
            throw new \RuntimeException('Requested quantity is not available.');
        }

        if ($userId !== null) {
            return $this->updateCustomerCart($userId, $productId, $quantity);
        }

        return $this->updateGuestCart($productId, $quantity);
    }

    private function updateCustomerCart(int $userId, int $productId, int $quantity): array {
        $cart = $this->cartRepository->findByUserId($userId);
        if ($cart === null) {
            throw new \RuntimeException('Cart not found.');
        }
        $item = $this->cartItemRepository->findByCartAndProduct((int) $cart['id'],$productId);
        if ($item === null) {
            throw new \RuntimeException('Product is not in the cart.');
        }
        $this->cartItemRepository->updateQuantity((int) $item['id'],$quantity);
        return $this->getCustomerCart((int) $cart['id']);
    }

    private function updateGuestCart(int $productId, int $quantity): array {
        $cart = $this->session->get('cart', []);
        if (!isset($cart[$productId])) {
            throw new \RuntimeException('Product is not in the cart.');
        }
        $cart[$productId] = $quantity;
        $this->session->put('cart',$cart);
        return $this->getGuestCart();
    }

    public function remove(int $productId, ?int $userId = null): array {
        if ($userId !== null) {
            return $this->removeFromCustomerCart($userId,$productId);
        }
        return $this->removeFromGuestCart($productId);
    }

    private function removeFromCustomerCart(int $userId, int $productId): array {
        $cart = $this->cartRepository->findByUserId($userId);
        if ($cart === null) {
            throw new \RuntimeException('Cart not found.');
        }
        $item = $this->cartItemRepository->findByCartAndProduct((int) $cart['id'],$productId);
        if ($item === null) {
            throw new \RuntimeException('Product is not in the cart.');
        }
        $this->cartItemRepository->delete((int) $item['id']);
        return $this->getCustomerCart((int) $cart['id']);
    }

    private function removeFromGuestCart(int $productId): array {
        $cart = $this->session->get('cart', []);
        if (!isset($cart[$productId])) {
            throw new \RuntimeException('Product is not in the cart.');
        }
        unset($cart[$productId]);
        $this->session->put('cart',$cart);
        return $this->getGuestCart();
    }
}