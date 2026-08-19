<?php

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Services\CartService;

class CartController {
    public function __construct(private CartService $cartService) {
    }

    public function index(Request $request): never {
        $user = $request->getAttribute('user');
        $userId = $user ? (int) $user['id'] : null;
        $cart = $this->cartService->getCart($userId);
        Response::success(
            $cart,
            'Cart retrieved successfully.'
        );
    }

    public function add(Request $request): never {
        $data = $request->input();
        $productId = (int) ($data['product_id'] ?? 0);
        $quantity = (int) ($data['quantity'] ?? 1);
        $user = $request->getAttribute('user');
        $userId = $user ? (int) $user['id'] : null;

        try {
            $cart = $this->cartService->add($productId,$quantity,$userId);
        } catch (\RuntimeException $e) {
            Response::error(
                $e->getMessage(),
                422
            );
        }
        Response::success(
            $cart,
            'Product added to cart.'
        );
    }

    public function update(Request $request, int $id): never {
        $data = $request->input();
        $quantity = (int) ($data['quantity'] ?? 0);
        $user = $request->getAttribute('user');
        $userId = $user? (int) $user['id'] : null;

        try {
            $cart = $this->cartService->updateQuantity($id, $quantity, $userId);
        } catch (\RuntimeException $e) {
            Response::error(
                $e->getMessage(),
                422
            );
        }
        Response::success(
            $cart,
            'Cart quantity updated successfully.'
        );
    }

    public function remove(Request $request, int $id): never {
        $user = $request->getAttribute('user');
        $userId = $user ? (int) $user['id'] : null;
        try {
            $cart = $this->cartService->remove($id, $userId);
        } catch (\RuntimeException $e) {
            Response::error(
                $e->getMessage(),
                422
            );
        }
        Response::success(
            $cart,
            'Product removed from cart.'
        );
    }
}