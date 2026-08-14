<?php

namespace App\Core;

class Response
{
    public static function json(
        array $data,
        int $statusCode = 200
    ): never {
        http_response_code($statusCode);

        header('Content-Type: application/json');

        echo json_encode(
            $data,
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
        );

        exit;
    }

    public static function success(
        mixed $data = null,
        string $message = 'Success',
        int $statusCode = 200
    ): never {
        self::json(
            [
                'success' => true,
                'message' => $message,
                'data' => $data,
            ],
            $statusCode
        );
    }

    public static function error(
        string $message,
        int $statusCode = 400,
        array $errors = []
    ): never {
        self::json(
            [
                'success' => false,
                'message' => $message,
                'errors' => $errors,
            ],
            $statusCode
        );
    }
}