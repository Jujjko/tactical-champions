<?php
// app/Core/Router.php
declare(strict_types=1);

namespace Core;

class Router {
    private array $routes = [];
    private array $middleware = [];
    
    public function get(string $path, string $controller, string $method, array $middleware = []): void {
        $this->addRoute('GET', $path, $controller, $method, $middleware);
    }
    
    public function post(string $path, string $controller, string $method, array $middleware = []): void {
        $this->addRoute('POST', $path, $controller, $method, $middleware);
    }
    
    private function addRoute(string $httpMethod, string $path, string $controller, string $method, array $middleware): void {
        $this->routes[] = [
            'method' => $httpMethod,
            'path' => $path,
            'controller' => $controller,
            'action' => $method,
            'middleware' => $middleware
        ];
    }
    
    public function dispatch(string $uri): void {
        $uri = parse_url($uri, PHP_URL_PATH);
        $requestMethod = $_SERVER['REQUEST_METHOD'];
        
        foreach ($this->routes as $route) {
            $pattern = preg_replace('/\{[a-zA-Z0-9_]+\}/', '([a-zA-Z0-9_-]+)', $route['path']);
            $pattern = '#^' . $pattern . '$#';
            
            if ($route['method'] === $requestMethod && preg_match($pattern, $uri, $matches)) {
                array_shift($matches);
                
                // Run middleware
                foreach ($route['middleware'] as $middlewareClass) {
                    $middleware = new $middlewareClass();
                    if (!$middleware->handle()) {
                        return;
                    }
                }
                
                $controllerName = "App\\Controllers\\" . $route['controller'];
                $controller = new $controllerName();
                $action = $route['action'];
                
                call_user_func_array([$controller, $action], $matches);
                return;
            }
        }
        
        http_response_code(404);
        echo "404 - Page Not Found";
    }
}