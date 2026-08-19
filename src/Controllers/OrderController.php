<?php

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Services\OrderService;
use RuntimeException;
use App\Core\Validator;
use InvalidArgumentException;

class OrderController {
    public function __construct(private OrderService $orderService) {
    }

    public function index(Request $request): never {
        $user = $request->getAttribute('user');
        if (!$user) {
            Response::error(
                'Unauthorized.',
                401
            );
        }
        $orders = $this->orderService->getOrders((int) $user['id']);
        Response::success(
            $orders,
            'Orders retrieved successfully.'
        );
    }

    public function show(Request $request, int $id): never {
        $user = $request->getAttribute('user');
        if (!$user) {
            Response::error(
                'Unauthorized.',
                401
            );
        }
        $order = $this->orderService->getOrder($id,(int) $user['id']);
        if ($order === null) {
            Response::error(
                'Order not found.',
                404
            );
        }
        Response::success(
            $order,
            'Order retrieved successfully.'
        );
    }

    public function checkout(Request $request): never {
        $user = $request->getAttribute('user');
        if (!$user) {
            Response::error(
                'Unauthorized.',
                401
            );
        }
        try {
            $order = $this->orderService->checkout((int) $user['id']);
        } catch (RuntimeException $e) {
            Response::error(
                $e->getMessage(),
                422
            );
        }
        Response::success(
            $order,
            'Order created successfully.',
            201
        );
    }

    public function adminIndex(Request $request): never {
        $orders = $this->orderService->getAllOrders();

        Response::success(
            $orders,
            'Orders retrieved successfully.'
        );
    }

    public function updateStatus(Request $request, int $id): never {
        $data = $request->input();
        $validator = new Validator();
        $validator->required('status', $data['status'] ?? null)->string('status', $data['status'] ?? null);

        if ($validator->fails()) {
            Response::error(
                'Validation failed.',
                422,
                $validator->errors()
            );
        }
        try {
            $order = $this->orderService->updateStatus($id,$data['status']);
        } catch (InvalidArgumentException $e) {
            Response::error(
                $e->getMessage(),
                422
            );
        }
        if ($order === null) {
            Response::error(
                'Order not found.',
                404
            );
        }
        Response::success(
            $order,
            'Order status updated successfully.'
        );
    }
    public function cancel(Request $request, int $id): never {
        try {
            $order = $this->orderService->cancelOrder($id);
        } catch (InvalidArgumentException $e) {
            Response::error(
                $e->getMessage(),
                422
            );
        }
        if ($order === null) {
            Response::error(
                'Order not found.',
                404
            );
        }
        Response::success(
            $order,
            'Order cancelled successfully.'
        );
    }
}