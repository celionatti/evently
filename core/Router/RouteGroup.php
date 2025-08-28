<?php

declare(strict_types=1);

namespace Trees\Router;

class RouteGroup
{
    public function __construct(
        private string $prefix = '',
        private array $middleware = [],
        private array $whereConstraints = []
    ) {}

    public function getPrefix(): string
    {
        return $this->prefix;
    }

    public function getMiddleware(): array
    {
        return $this->middleware;
    }

    public function getWhereConstraints(): array
    {
        return $this->whereConstraints;
    }
}