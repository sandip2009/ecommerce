<?php

namespace App\Core;

use ReflectionClass;
use ReflectionException;
use RuntimeException;

class Container
{
    private array $bindings = [];

    private array $instances = [];

    public function bind(
        string $abstract,
        callable $factory
    ): void {
        $this->bindings[$abstract] = $factory;
    }

    public function singleton(
        string $abstract,
        callable $factory
    ): void {
        $this->bindings[$abstract] = $factory;
    }

    public function make(string $abstract): mixed
    {
        /*
         * 1. If we already have a singleton instance,
         *    return it.
         */
        if (isset($this->instances[$abstract])) {
            return $this->instances[$abstract];
        }

        /*
         * 2. If this class has a binding,
         *    use the binding instead of Reflection.
         */
        if (isset($this->bindings[$abstract])) {

            $object = ($this->bindings[$abstract])($this);

            $this->instances[$abstract] = $object;

            return $object;
        }

        /*
         * 3. Otherwise, try automatic dependency resolution.
         */
        return $this->build($abstract);
    }

    private function build(string $class): mixed
    {
        try {
            $reflection = new ReflectionClass($class);
        } catch (ReflectionException $e) {
            throw new RuntimeException(
                "Class {$class} does not exist.",
                0,
                $e
            );
        }

        if (!$reflection->isInstantiable()) {
            throw new RuntimeException(
                "Class {$class} cannot be instantiated."
            );
        }

        $constructor = $reflection->getConstructor();

        /*
         * Class has no constructor.
         */
        if ($constructor === null) {
            return $reflection->newInstance();
        }

        $dependencies = [];

        foreach ($constructor->getParameters() as $parameter) {

            $type = $parameter->getType();

            /*
             * We cannot automatically resolve:
             *
             * string
             * int
             * float
             * bool
             *
             * or parameters without a type.
             */
            if (
                $type === null ||
                $type->isBuiltin()
            ) {
                throw new RuntimeException(
                    "Unable to resolve dependency: "
                    . $parameter->getName()
                );
            }

            $dependencies[] = $this->make(
                $type->getName()
            );
        }

        return $reflection->newInstanceArgs(
            $dependencies
        );
    }
}