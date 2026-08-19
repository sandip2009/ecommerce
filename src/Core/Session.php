<?php

namespace App\Core;

class Session {
    public function start(): void {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public function get(string $key, mixed $default = null): mixed {
        return $_SESSION[$key] ?? $default;
    }

    public function put(string $key, mixed $value): void {
        $_SESSION[$key] = $value;
    }

    public function forget(string $key): void {
        unset($_SESSION[$key]);
    }

    public function has(string $key): bool {
        return isset($_SESSION[$key]);
    }

    public function all(): array {
        return $_SESSION;
    }
}