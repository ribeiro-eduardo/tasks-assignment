<?php

namespace App\Core;

class Request
{
    protected string $method;
    protected string $path;
    protected ?array $body;
    protected array $query;
    protected array $routeParams = [];

    public function __construct(?Request $source = null)
    {
        if ($source) {
            $this->method = $source->method;
            $this->path = $source->path;
            $this->body = $source->body;
            $this->query = $source->query;
            $this->routeParams = $source->routeParams;
            return;
        }

        $this->method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
        $this->path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
        $this->query = $_GET;

        $raw = file_get_contents('php://input');
        $this->body = $raw ? json_decode($raw, true) : null;
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getBody(): ?array
    {
        return $this->body;
    }

    public function getQuery(string $key = null, mixed $default = null): mixed
    {
        if ($key === null) {
            return $this->query;
        }

        return $this->query[$key] ?? $default;
    }

    public function getRouteParam(string $name): ?string
    {
        return $this->routeParams[$name] ?? null;
    }

    public function setRouteParams(array $params): void
    {
        $this->routeParams = $params;
    }
}
