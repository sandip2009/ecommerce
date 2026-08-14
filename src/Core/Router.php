<?php

namespace App\Core;

use Closure;

class Router
{
    private array $routes = [];

    public function get(
        string $uri,
        callable|array $handler
    ): void {
        $this->add('GET', $uri, $handler);
    }

    public function post(
        string $uri,
        callable|array $handler
    ): void {
        $this->add('POST', $uri, $handler);
    }

    public function put(
        string $uri,
        callable|array $handler
    ): void {
        $this->add('PUT', $uri, $handler);
    }

    public function delete(
        string $uri,
        callable|array $handler
    ): void {
        $this->add('DELETE', $uri, $handler);
    }

    private function add(
        string $method,
        string $uri,
        callable|array $handler
    ): void {
        $this->routes[] = [
            'method' => $method,
            'uri' => $uri,
            'handler' => $handler,
        ];
    }

    public function dispatch(Request $request): mixed
    {
        foreach ($this->routes as $route) {

            if ($route['method'] === $request->method() && $route['uri'] === $request->uri()) {
                return $this->callHandler(
                    $route['handler'],
                    $request
                );
            }
        }
        Response::error(
            'Route not found.',
            404
        );
    }

    private function callHandler(
        callable|array $handler,
        Request $request
    ): mixed {

        if (is_array($handler)) {
            [$controller, $method] = $handler;
            $controllerInstance = is_object($controller)
                ? $controller
                : new $controller();
            return $controllerInstance->$method($request);
        }

        return $handler($request);
    }
}
