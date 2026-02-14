<?php
/**
 * proService - Middleware de Verificação de Plano
 * Arquivo: /app/middlewares/PlanoMiddleware.php
 */

namespace App\Middlewares;

use App\Models\Empresa;

class PlanoMiddleware
{
    /**
     * Verifica se empresa tem plano ativo
     */
    public static function check(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $empresaId = $_SESSION['empresa_id'] ?? null;
        
        if (!$empresaId) {
            return;
        }

        $empresaModel = new Empresa();
        $empresa = $empresaModel->findById($empresaId);
        
        if (!$empresa) {
            session_destroy();
            setFlash('error', 'Empresa não encontrada.');
            header('Location: ' . url('login'));
            exit;
        }

        // Verificar se trial expirou
        if ($empresa['plano'] === 'trial' && $empresa['data_fim_trial'] < date('Y-m-d')) {
            // Bloquear apenas ações de criação, permitir visualização
            if (self::isCreateAction()) {
                setFlash('error', 'Seu período de trial expirou. Atualize seu plano para continuar.');
                header('Location: ' . url('dashboard'));
                exit;
            }
        }

        // Verificar status bloqueado
        if ($empresa['status'] === 'bloqueado' || $empresa['status'] === 'cancelado') {
            session_destroy();
            setFlash('error', 'Conta bloqueada. Entre em contato com o suporte.');
            header('Location: ' . url('login'));
            exit;
        }

        // Atualizar dados da sessão
        $_SESSION['empresa'] = $empresa;
    }

    /**
     * Verifica se a ação atual é de criação
     */
    private static function isCreateAction(): bool
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $uri = $_SERVER['REQUEST_URI'] ?? '';
        
        // POST geralmente é criação
        if ($method === 'POST') {
            // Exceções: login, logout
            if (str_contains($uri, 'login') || str_contains($uri, 'logout')) {
                return false;
            }
            return true;
        }
        
        return false;
    }

    /**
     * Verifica se tem acesso a recurso (planos pagos = tudo liberado)
     * Apenas trial/gratuito tem restrições
     */
    public static function hasRecurso(string $recurso): bool
    {
        $empresaId = getEmpresaId();
        if (!$empresaId) {
            return false;
        }

        $model = self::getEmpresaModel();
        
        // Se for plano pago, libera tudo
        if ($model->isPlanoPago($empresaId)) {
            return true;
        }
        
        // Trial - bloqueia recursos avançados
        return match($recurso) {
            'relatorios_avancados', 'logs_sistema', 'backup_automatico' => false,
            default => true
        };
    }

    /**
     * Verifica limite de OS (apenas para trial)
     * Planos pagos não têm limite
     */
    public static function checkLimiteOS(): array
    {
        $empresaId = getEmpresaId();
        if (!$empresaId) {
            return ['permitido' => false, 'motivo' => 'Não autenticado'];
        }

        $model = self::getEmpresaModel();
        
        // Se for plano pago, sempre permite
        if ($model->isPlanoPago($empresaId)) {
            return ['permitido' => true, 'restante' => -1];
        }

        // Trial - verifica limite
        $result = $model->podeCriarOS($empresaId);

        if (!$result['permitido']) {
            setFlash('error', $result['motivo'] . ' <a href="' . url('assinaturas/planos') . '">Ver planos</a>');
        }

        return $result;
    }

    /**
     * Verifica limite de técnicos (apenas para trial)
     * Planos pagos não têm limite
     */
    public static function checkLimiteTecnicos(): bool
    {
        $empresaId = getEmpresaId();
        if (!$empresaId) {
            return false;
        }

        $model = self::getEmpresaModel();
        
        // Se for plano pago, sempre permite
        if ($model->isPlanoPago($empresaId)) {
            return true;
        }

        // Trial - verifica limite
        $pode = $model->verificarLimiteTecnicos($empresaId);

        if (!$pode) {
            setFlash('error', 'Limite de técnicos atingido. <a href="' . url('assinaturas/planos') . '">Ver planos</a>');
        }

        return $pode;
    }

    /**
     * Retorna instância do modelo Empresa
     */
    private static function getEmpresaModel(): Empresa
    {
        return new Empresa();
    }
}
