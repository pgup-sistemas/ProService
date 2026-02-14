<?php
/**
 * proService - Controller Base
 * Arquivo: /app/controllers/Controller.php
 */

namespace App\Controllers;

abstract class Controller
{
    protected function jsonResponse(array $data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    protected function view(string $view, array $data = []): void
    {
        // Extrair dados para a view
        extract($data);
        
        // Caminho da view
        $viewPath = __DIR__ . '/../views/' . $view . '.php';
        
        if (!file_exists($viewPath)) {
            throw new \Exception("View não encontrada: {$view}");
        }
        
        // Carregar a view
        require $viewPath;
    }

    /**
     * Renderiza uma view e retorna como string (para usar em layouts)
     */
    protected function render(string $view, array $data = []): string
    {
        ob_start();
        $this->view($view, $data);
        return ob_get_clean();
    }

    protected function layout(string $layout, array $data = []): void
    {
        extract($data);
        
        $layoutPath = __DIR__ . '/../views/layouts/' . $layout . '.php';
        
        if (!file_exists($layoutPath)) {
            throw new \Exception("Layout não encontrado: {$layout}");
        }
        
        require $layoutPath;
    }

    protected function redirect(string $path): void
    {
        header('Location: ' . url($path));
        exit;
    }

    protected function back(): void
    {
        $referer = $_SERVER['HTTP_REFERER'] ?? '/';
        header('Location: ' . $referer);
        exit;
    }

    protected function validate(array $data, array $rules): array
    {
        $errors = [];
        
        foreach ($rules as $field => $rule) {
            $ruleParts = explode('|', $rule);
            
            foreach ($ruleParts as $part) {
                if ($part === 'required' && empty($data[$field])) {
                    $errors[$field] = "O campo {$field} é obrigatório.";
                    break;
                }
                
                if (strpos($part, 'min:') === 0 && isset($data[$field])) {
                    $min = (int) substr($part, 4);
                    if (strlen($data[$field]) < $min) {
                        $errors[$field] = "O campo {$field} deve ter no mínimo {$min} caracteres.";
                    }
                }
                
                if (strpos($part, 'max:') === 0 && isset($data[$field])) {
                    $max = (int) substr($part, 4);
                    if (strlen($data[$field]) > $max) {
                        $errors[$field] = "O campo {$field} deve ter no máximo {$max} caracteres.";
                    }
                }
                
                if ($part === 'email' && isset($data[$field]) && !filter_var($data[$field], FILTER_VALIDATE_EMAIL)) {
                    $errors[$field] = "O campo {$field} deve ser um e-mail válido.";
                }
                
                if ($part === 'numeric' && isset($data[$field]) && !is_numeric($data[$field])) {
                    $errors[$field] = "O campo {$field} deve ser numérico.";
                }
            }
        }
        
        return $errors;
    }

    protected function old(string $key, string $default = ''): string
    {
        return $_SESSION['old'][$key] ?? $default;
    }

    protected function setOld(array $data): void
    {
        $_SESSION['old'] = $data;
    }

    protected function clearOld(): void
    {
        unset($_SESSION['old']);
    }
}
