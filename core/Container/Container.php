<?php

declare(strict_types=1);

namespace Trees\Container;

use Closure;
use Psr\Container\ContainerInterface;
use ReflectionException;
use Trees\Database\ErrorHandler\ErrorHandler;
use Trees\Exception\TreesException;
use Trees\Exception\ContainerException;
use Trees\Http\Request;
use Trees\Http\Response;
use Trees\Router\Router;

/**
 * =======================================
 * ***************************************
 * ======== Trees Container Class ========
 * ***************************************
 * =======================================
 */

class Container implements ContainerInterface
{
    private array $bindings = [];
    private array $instances = [];
    private array $aliases = [];

    public function bind(string $abstract, $concrete = null): void
    {
        if ($concrete === null) {
            $concrete = $abstract;
        }

        $this->bindings[$abstract] = $concrete;
    }

    public function singleton(string $abstract, $concrete = null): void
    {
        $this->bind($abstract, function () use ($concrete, $abstract) {
            static $instance;

            if ($instance === null) {
                $instance = $this->build($concrete ?? $abstract, []);
            }

            return $instance;
        });
    }

    public function alias(string $abstract, string $alias): void
    {
        $this->aliases[$alias] = $abstract;
    }

    /**
     * @throws ContainerException
     * @throws TreesException
     */
    public function make(string $abstract, array $parameters = [])
    {
        $abstract = $this->getAlias($abstract);

        if (isset($this->instances[$abstract])) {
            return $this->instances[$abstract];
        }

        if (isset($this->bindings[$abstract])) {
            $concrete = $this->bindings[$abstract];

//            if ($concrete instanceof Closure) {
//                $instance = $this->build($concrete, $parameters);
//            } else {
//                $instance = $this->build($concrete, $parameters);
//            }

            $instance = $this->build($concrete, $parameters);

            $this->instances[$abstract] = $instance;

            return $instance;
        }

        return $this->resolve($abstract, $parameters);
    }

    /**
     * @throws TreesException
     */
    private function build($concrete, array $parameters)
    {
        if ($concrete instanceof Closure) {
            return $concrete($this, ...$parameters);
        }

        try {
            $reflector = new \ReflectionClass($concrete);

            if (!$reflector->isInstantiable()) {
                throw new TreesException("Class '{$concrete}' is not instantiable.", 424);
            }

            $constructor = $reflector->getConstructor();

            if ($constructor === null) {
                return new $concrete;
            }

            $dependencies = $this->resolveDependencies($constructor, $parameters);

            return $reflector->newInstanceArgs($dependencies);
        } catch (ReflectionException $e) {
            throw new TreesException("Error resolving '{$concrete}': " . $e->getMessage(), $e->getCode());
        }
    }

    /**
     * @throws TreesException
     */
    private function resolveDependencies(\ReflectionFunctionAbstract $function, array $parameters): array
    {
        $dependencies = [];

        foreach ($function->getParameters() as $parameter) {
            $paramName = $parameter->getName();
            $paramType = $parameter->getType();

            if (array_key_exists($paramName, $parameters)) {
                $dependencies[] = $parameters[$paramName];
            } elseif ($paramType && !$paramType->isBuiltin()) {
                $dependencies[] = $this->make($paramType->getName());
            } elseif ($parameter->isDefaultValueAvailable()) {
                $dependencies[] = $parameter->getDefaultValue();
            } else {
                throw new TreesException("Unable to resolve dependency: {$paramName}", 424);
            }
        }

        return $dependencies;
    }

    private function getAlias(string $abstract): string
    {
        return $this->aliases[$abstract] ?? $abstract;
    }

    /**
     * @throws ContainerException
     * @throws TreesException
     */
    private function resolve(string $abstract, array $parameters = [])
    {
        if (!class_exists($abstract)) {
            throw new ContainerException("No entry was found for '{$abstract}'.");
        }

        return $this->build($abstract, $parameters);
    }

    /**
     * Check if an item exists in the container.
     *
     * @param string $id The identifier to check for.
     * @return bool
     */
    public function has(string $id): bool
    {
        $id = $this->getAlias($id);
        return isset($this->bindings[$id]) || isset($this->instances[$id]);
    }

    /**
     * Get an item from the container.
     *
     * @param string $id The identifier to get.
     * @throws ContainerException
     */
    public function get(string $id)
    {
        try {
            return $this->make($id);
        } catch (TreesException $e) {
            throw new ContainerException("No entry was found for '{$id}'.");
        }
    }

    /**
     * @throws TreesException
     * @throws ReflectionException
     */
    public function call($callback, array $parameters = [])
    {
        if (is_array($callback)) {
            [$class, $method] = $callback;

            $instance = is_object($class) ? $class : $this->make($class);

            $reflector = new \ReflectionMethod($instance, $method);

            $dependencies = $this->resolveDependencies($reflector, $parameters);

            return $reflector->invokeArgs($instance, $dependencies);
        } elseif ($callback instanceof Closure) {
            $reflector = new \ReflectionFunction($callback);

            $dependencies = $this->resolveDependencies($reflector, $parameters);

            return $callback(...$dependencies);
        }

        throw new TreesException("Invalid callback provided for method injection.", 424);
    }

    /**
     * @throws ContainerException
     * @throws TreesException
     */
    public function __get($name)
    {
        return $this->make($name);
    }

    public function productionOptimizations(): void
    {
        // Cache resolved instances
        $this->singleton(RouteBinding::class);
        $this->singleton(MiddlewarePipeline::class);

        // Preload frequently used classes
        $this->preload([
            Request::class,
            Response::class,
            ErrorHandler::class
        ]);
    }

    /**
     * @throws ContainerException
     * @throws TreesException
     */
    public function preload(array $classes): void
    {
        foreach ($classes as $class) {
            if (class_exists($class)) {
                $this->make($class);
            }
        }
    }

    public function cacheResolvedInstances(): void
    {
        $this->instances = array_merge($this->instances, [
            'router' => $this->make(Router::class),
            'middleware.pipeline' => $this->make(MiddlewarePipeline::class)
        ]);
    }
}