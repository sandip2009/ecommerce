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
        $method = $request->method();
        $uri = $request->uri();

        foreach ($this->routes as $route) {

            if ($route['method'] !== $method) {
                continue;
            }

            $parameters = $this->matchRoute(
                $route['uri'],
                $uri
            );

            if ($parameters !== null) {

                return $this->callHandler(
                    $route['handler'],
                    $request,
                    $parameters
                );
            }
        }

        return Response::error(
            'Route not found.',
            404
        );
    }

    private function callHandler(callable $handler, Request $request, array $parameters = []): mixed {
        $reflection = new \ReflectionFunction(
            \Closure::fromCallable($handler)
        );

        $arguments = [];
        $routeParameters = array_values($parameters);
        $routeIndex = 0;
        foreach ($reflection->getParameters() as $parameter) {
            $type = $parameter->getType();

            // Inject Request object
            if (
                $type instanceof \ReflectionNamedType &&
                $type->getName() === Request::class
            ) {
                $arguments[] = $request;
                continue;
            }

            // Inject route parameter
            if ($routeIndex < count($routeParameters)) {
                $value = $routeParameters[$routeIndex];

                // Convert route parameter to expected type
                if ($type instanceof \ReflectionNamedType) {
                    switch ($type->getName()) {

                        case 'int':
                            $value = (int) $value;
                            break;

                        case 'float':
                            $value = (float) $value;
                            break;

                        case 'bool':
                            $value = filter_var($value,FILTER_VALIDATE_BOOLEAN);
                            break;
                    }
                }

                $arguments[] = $value;
                $routeIndex++;
            }
        }

        return call_user_func_array(
            $handler,
            $arguments
        );
    }

    private function matchRoute(string $route,string $uri): ?array {
        $parameterNames = [];

        $pattern = preg_replace_callback(
            '/\{([a-zA-Z_][a-zA-Z0-9_]*)\}/',
            function ($matches) use (&$parameterNames) {
                $parameterNames[] = $matches[1];
                return '([^/]+)';
            },
            $route
        );

        $pattern = '#^' . $pattern . '$#';

        if (!preg_match($pattern, $uri, $matches)) {
            return null;
        }

        array_shift($matches);

        $parameters = [];

        foreach ($parameterNames as $index => $name) {
            $parameters[$name] =
                $matches[$index];
        }
        return $parameters;
    }
}
