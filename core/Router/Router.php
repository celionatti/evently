<?php

declare(strict_types=1);

namespace Trees\Router;

use Trees\Exception\TreesException;
use Trees\Http\Request;

class Router
{
    private array $routesByMethod = [];
    private array $groupStack = [];
    private array $patterns = [
        'id' => '[0-9]+',
        'slug' => '[a-z0-9-]+',
        'alpha' => '[a-zA-Z]+',
        'alphanumeric' => '[a-zA-Z0-9]+'
    ];

    public function addRoute(string $method, string $pattern, $handler): Route
    {
        $pattern = $this->applyGroupPrefix($pattern);
        $route = new Route($method, $pattern, $handler);

        if ($middleware = $this->getGroupMiddleware()) {
            $route->middleware($middleware);
        }

        if ($constraints = $this->getGroupWhereConstraints()) {
            $route->whereMultiple($constraints);
        }

        $this->routesByMethod[strtoupper($method)][] = $route;
        return $route;
    }

    public function resolve(Request $request): Route
    {
        $method = $request->getMethod();
        $path = $request->getPath();

        if (!isset($this->routesByMethod[$method])) {
            throw new TreesException("No routes registered for method: $method", 404);
        }

        foreach ($this->routesByMethod[$method] as $route) {
            if ($route->matches($method, $path)) {
                return $route;
            }
        }

        throw new TreesException("Route not found for path: {$path}", 404);
    }

    // HTTP method shortcuts (get, post, put, patch, delete)
    public function get(string $pattern, $handler): Route
    {
        return $this->addRoute('GET', $pattern, $handler);
    }

    public function post(string $pattern, $handler): Route
    {
        return $this->addRoute('POST', $pattern, $handler);
    }

    public function put(string $pattern, $handler): Route
    {
        return $this->addRoute('PUT', $pattern, $handler);
    }

    public function patch(string $pattern, $handler): Route
    {
        return $this->addRoute('PATCH', $pattern, $handler);
    }

    public function delete(string $pattern, $handler): Route
    {
        return $this->addRoute('DELETE', $pattern, $handler);
    }

    public function group(array $attributes, \Closure $callback): void
    {
        $this->groupStack[] = new RouteGroup(
            $attributes['prefix'] ?? '',
            $attributes['middleware'] ?? [],
            $attributes['where'] ?? []
        );

        $callback($this);
        array_pop($this->groupStack);
    }

    private function applyGroupPrefix(string $pattern): string
    {
        if (empty($this->groupStack)) {
            return $pattern;
        }

        $prefix = '';
        foreach ($this->groupStack as $group) {
            $prefix .= $group->getPrefix();
        }

        return $prefix ? rtrim($prefix, '/') . '/' . ltrim($pattern, '/') : $pattern;
    }

    private function getGroupMiddleware(): array
    {
        return array_merge([], ...array_map(
            fn($group) => $group->getMiddleware(),
            $this->groupStack
        ));
    }

    private function getGroupWhereConstraints(): array
    {
        return array_merge([], ...array_map(
            fn($group) => $group->getWhereConstraints(),
            $this->groupStack
        ));
    }
}