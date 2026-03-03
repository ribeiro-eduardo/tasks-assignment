<?php

namespace App\Core;

class Router
{
    private array $routes = [];

    public function add(string $method, string $pattern, string $controller, string $action, ?string $requestClass = null): void
    {
        $this->routes[] = [
            'method'       => strtoupper($method),
            'pattern'      => $pattern,
            'controller'   => $controller,
            'action'       => $action,
            'requestClass' => $requestClass,
        ];
    }

    public function get(string $pattern, string $controller, string $action, ?string $requestClass = null): void
    {
        $this->add('GET', $pattern, $controller, $action, $requestClass);
    }

    public function post(string $pattern, string $controller, string $action, ?string $requestClass = null): void
    {
        $this->add('POST', $pattern, $controller, $action, $requestClass);
    }

    public function put(string $pattern, string $controller, string $action, ?string $requestClass = null): void
    {
        $this->add('PUT', $pattern, $controller, $action, $requestClass);
    }

    public function delete(string $pattern, string $controller, string $action, ?string $requestClass = null): void
    {
        $this->add('DELETE', $pattern, $controller, $action, $requestClass);
    }

    public function dispatch(Request $request, Container $container): void
    {
        $path = $request->getPath();
        $method = $request->getMethod();

        $methodMatchFound = false;

        foreach ($this->routes as $route) {
            $regex = $this->patternToRegex($route['pattern']);

            if (!preg_match($regex, $path, $matches)) {
                continue;
            }

            $methodMatchFound = true;

            if ($route['method'] !== $method) {
                continue;
            }

            $params = array_filter($matches, fn($key) => is_string($key), ARRAY_FILTER_USE_KEY);
            $request->setRouteParams($params);

            if ($route['requestClass']) {
                $request = new $route['requestClass']($request);
            }

            $controller = $container->get($route['controller']);
            $response = $controller->{$route['action']}($request);
            $response->send();
            return;
        }

        if ($methodMatchFound) {
            (new Response(['error' => 'Method not allowed'], 405))->send();
            return;
        }

        (new Response(['error' => 'Not found'], 404))->send();
    }

    private function patternToRegex(string $pattern): string
    {
        $regex = preg_replace('/\{([a-zA-Z_]+)\}/', '(?P<$1>[^/]+)', $pattern);
        return '#^' . $regex . '$#';
    }
}
