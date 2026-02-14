<?php
/**
 * proService - Middleware de Autenticação
 * Arquivo: /app/middlewares/AuthMiddleware.php
 */

namespace App\Middlewares;

class AuthMiddleware
{
    /**
     * Verifica se usuário está autenticado
     */
    public static function check(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['usuario_id']) || empty($_SESSION['usuario_id'])) {
            setFlash('error', 'Faça login para acessar esta página.');
            header('Location: ' . url('login'));
            exit;
        }

        // Verificar timeout de sessão (2 horas)
        if (isset($_SESSION['last_activity'])) {
            $inactivity = time() - $_SESSION['last_activity'];
            if ($inactivity > 7200) { // 2 horas
                session_destroy();
                setFlash('error', 'Sessão expirada. Faça login novamente.');
                header('Location: ' . url('login'));
                exit;
            }
        }
        
        $_SESSION['last_activity'] = time();
    }

    /**
     * Verifica se usuário é admin
     */
    public static function adminOnly(): void
    {
        self::check();

        if ($_SESSION['perfil'] !== 'admin') {
            setFlash('error', 'Acesso restrito a administradores.');
            header('Location: ' . url('dashboard'));
            exit;
        }
    }

    /**
     * Redireciona usuário logado
     */
    public static function guest(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (isset($_SESSION['usuario_id']) && !empty($_SESSION['usuario_id'])) {
            header('Location: ' . url('dashboard'));
            exit;
        }
    }
}
