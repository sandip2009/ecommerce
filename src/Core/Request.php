<?php

namespace App\Core;

class Request
{
    private array $attributes = [];
    public function method(): string
    {
        return strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
    }

    public function uri(): string
    {
        $uri = parse_url(
            $_SERVER['REQUEST_URI'] ?? '/',
            PHP_URL_PATH
        );

        return rtrim($uri, '/') ?: '/';
    }

    public function input(): array
    {
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';

        if (str_contains($contentType, 'application/json')) {
            $data = json_decode(
                file_get_contents('php://input'),
                true
            );

            return is_array($data) ? $data : [];
        }

        return $_POST;
    }

    public function query(string $key, mixed $default = null): mixed
    {
        return $_GET[$key] ?? $default;
    }

    public function header(string $key): ?string
    {
        $header = 'HTTP_' . strtoupper(
            str_replace('-', '_', $key)
        );

        return $_SERVER[$header] ?? null;
    }

    public function bearerToken(): ?string
    {
        $header = $_SERVER['HTTP_AUTHORIZATION'] ?? '';

        if (!preg_match('/Bearer\s+(.+)/i', $header, $matches)) {
            return null;
        }
        return trim($matches[1]);
    }

    public function setAttribute(string $key, mixed $value): void {
        $this->attributes[$key] = $value;
    }

    public function getAttribute(string $key, mixed $default = null): mixed {
        return $this->attributes[$key] ?? $default;
    }
}