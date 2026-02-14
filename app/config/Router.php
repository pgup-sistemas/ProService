<?php
/**
 * proService - Router Simples
 * Arquivo: /app/config/Router.php
 */

namespace App\Config;

class Router
{
    private array $routes = [];

    /**
     * Registra uma rota GET
     */
    public function get(string $path, array $callback, ?string $middleware = null): void
    {
        $this->addRoute('GET', $path, $callback, $middleware);
    }

    /**
     * Registra uma rota POST
     */
    public function post(string $path, array $callback, ?string $middleware = null): void
    {
        $this->addRoute('POST', $path, $callback, $middleware);
    }

    /**
     * Registra uma rota DELETE
     */
    public function delete(string $path, array $callback, ?string $middleware = null): void
    {
        $this->addRoute('DELETE', $path, $callback, $middleware);
    }

    /**
     * Adiciona rota ao registro
     */
    private function addRoute(string $method, string $path, array $callback, ?string $middleware): void
    {
        // Converter parâmetros de rota {id} para regex
        $pattern = preg_replace('/\{(\w+)\}/', '(?P<$1>[^/]+)', $path);
        $pattern = '#^' . $pattern . '$#';

        $this->routes[] = [
            'method' => $method,
            'path' => $path,
            'pattern' => $pattern,
            'callback' => $callback,
            'middleware' => $middleware
        ];
    }

    /**
     * Executa o roteamento
     */
    public function dispatch(): void
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        
        // Remover base path se houver
        $basePath = '/proService';
        if (strpos($uri, $basePath) === 0) {
            $uri = substr($uri, strlen($basePath));
        }
        
        $uri = trim($uri, '/');
        
        // Verificar cada rota
        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }
            
            if (!preg_match($route['pattern'], $uri, $matches)) {
                continue;
            }
            
            // Extrair parâmetros nomeados
            $params = [];
            foreach ($matches as $key => $value) {
                if (is_string($key)) {
                    $params[$key] = $value;
                }
            }
            
            // Executar middleware se houver
            if ($route['middleware']) {
                $this->executeMiddleware($route['middleware']);
            }
            
            // Executar controller
            $this->executeCallback($route['callback'], $params);
            return;
        }
        
        // Rota não encontrada
        http_response_code(404);
        echo '<h1>404 - Página não encontrada</h1>';
        echo '<p>A página que você está procurando não existe.</p>';
        echo '<a href="' . url() . '">Voltar para o início</a>';
    }

    /**
     * Executa middleware
     */
    private function executeMiddleware(string $middleware): void
    {
        $middlewareClass = "App\\Middlewares\\{$middleware}";
        
        if (class_exists($middlewareClass)) {
            $middlewareClass::check();
        }
    }

    /**
     * Executa callback do controller
     */
    private function executeCallback(array $callback, array $params): void
    {
        [$controllerClass, $method] = $callback;
        
        $fullClass = "App\\Controllers\\{$controllerClass}";
        
        if (!class_exists($fullClass)) {
            throw new \Exception("Controller não encontrado: {$controllerClass}");
        }
        
        $controller = new $fullClass();
        
        if (!method_exists($controller, $method)) {
            throw new \Exception("Método não encontrado: {$method}");
        }
        
        // Chamar método com ou sem parâmetros
        if (empty($params)) {
            $controller->$method();
        } else {
            $controller->$method(...array_values($params));
        }
    }
}
