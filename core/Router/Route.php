<?php

declare(strict_types=1);

namespace Trees\Router;

class Route
{
    private string $method;
    private string $pattern;
    private $handler;
    private array $middleware = [];
    private array $wheres = [];
    private array $matchedParams = [];
    private ?string $compiledRegex = null;

    public function __construct(string $method, string $pattern, $handler)
    {
        $this->method = $method;
        $this->pattern = $this->normalizeUri($pattern);
        $this->handler = $handler;
    }

    public function matches(string $method, string $uri): bool
    {
        if ($this->method !== $method) {
            return false;
        }

        $uri = $this->normalizeUri($uri);
        $regex = $this->getCompiledRegex();

        if (!preg_match($regex, $uri, $matches)) {
            return false;
        }

        $this->matchedParams = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
        return true;
    }

    private function getCompiledRegex(): string
    {
        if ($this->compiledRegex !== null) {
            return $this->compiledRegex;
        }

        $pattern = $this->pattern;
        foreach ($this->wheres as $param => $regex) {
            $pattern = str_replace("{{$param}}", "(?<{$param}>{$regex})", $pattern);
        }

        $pattern = preg_replace('/\{([a-zA-Z_]+)\}/', '(?<$1>[^/]+)', $pattern);
        return $this->compiledRegex = "#^{$pattern}$#";
    }

    private function normalizeUri(string $uri): string
    {
        return rtrim($uri, '/') ?: '/';
    }

    public function getHandler()
    {
        return $this->handler;
    }

    public function getParams(): array
    {
        return $this->matchedParams;
    }

    public function getMiddleware(): array
    {
        return $this->middleware;
    }

    public function middleware(string|array $middleware): self
    {
        $this->middleware = array_merge($this->middleware, (array)$middleware);
        return $this;
    }

    public function where(string $param, string $regex): self
    {
        $this->wheres[$param] = $regex;
        $this->compiledRegex = null;
        return $this;
    }

    public function whereMultiple(array $constraints): self
    {
        foreach ($constraints as $param => $regex) {
            $this->where($param, $regex);
        }
        return $this;
    }
}