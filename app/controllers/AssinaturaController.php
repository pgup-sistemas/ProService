<?php
/**
 * Controller para gerenciamento de assinaturas e pagamentos EfiPay
 */
namespace App\Controllers;

use App\Middlewares\AuthMiddleware;
use App\Models\Empresa;
use App\Models\OrdemServico;
use App\Models\Usuario;
use App\Services\EfiPayService;

class AssinaturaController extends Controller
{
    private Empresa $empresaModel;
    private OrdemServico $osModel;
    private Usuario $usuarioModel;
    private EfiPayService $efiPay;

    // Planos disponíveis - ajustados conforme funcionalidades reais do sistema
    private array $planos = [
        'starter' => [
            'id' => 'starter',
            'nome' => 'Starter',
            'descricao' => 'Ideal para começar',
            'preco' => 2900, // R$ 29,00 em centavos
            'intervalo' => 30, // dias
            'limite_os' => 50,
            'limite_tecnicos' => 1,
            'limite_armazenamento' => 100, // MB
            'recursos' => [
                '50 OS/mês',
                '1 técnico',
                '100 MB armazenamento',
                'Relatórios básicos',
                'Suporte por email',
                'Clientes ilimitados'
            ],
            'destaque' => false
        ],
        'pro' => [
            'id' => 'pro',
            'nome' => 'Pro',
            'descricao' => 'Para empresas em crescimento',
            'preco' => 5900, // R$ 59,00 em centavos
            'intervalo' => 30,
            'limite_os' => -1, // ilimitado
            'limite_tecnicos' => 5,
            'limite_armazenamento' => 1024, // 1 GB
            'recursos' => [
                'OS ilimitadas',
                'Até 5 técnicos',
                '1 GB armazenamento',
                'Relatórios avançados',
                'Suporte prioritário',
                'Clientes ilimitados',
                'Logs do sistema'
            ],
            'destaque' => true
        ],
        'business' => [
            'id' => 'business',
            'nome' => 'Business',
            'descricao' => 'Solução completa',
            'preco' => 9900, // R$ 99,00 em centavos
            'intervalo' => 30,
            'limite_os' => -1, // ilimitado
            'limite_tecnicos' => -1, // ilimitado
            'limite_armazenamento' => 5120, // 5 GB
            'recursos' => [
                'OS ilimitadas',
                'Técnicos ilimitados',
                '5 GB armazenamento',
                'Todos os relatórios',
                'Suporte prioritário',
                'Clientes ilimitados',
                'Logs do sistema',
                'Backup automático'
            ],
            'destaque' => false
        ]
    ];

    public function __construct()
    {
        AuthMiddleware::check();
        $this->empresaModel = new Empresa();
        $this->osModel = new OrdemServico();
        $this->usuarioModel = new Usuario();
        $this->efiPay = new EfiPayService();
    }

    /**
     * Página de seleção de planos
     */
    public function index(): void
    {
        $empresaId = getEmpresaId();
        $empresa = $this->empresaModel->findById($empresaId);

        $usoOS = $this->osModel->countMesAtual();
        $limiteOS = $empresa['limite_os_mes'] ?? 20;

        $totalTecnicos = $this->usuarioModel->count(['empresa_id' => $empresaId, 'perfil' => 'tecnico', 'ativo' => 1]);
        $limiteTecnicos = $empresa['limite_tecnicos'] ?? 1;

        $limiteArmazenamento = $empresa['limite_armazenamento_mb'] ?? 100;
        
        // Calcular dias restantes do trial baseado na data de criação da conta
        $diasTrial = 0;
        if ($empresa['plano'] === 'trial') {
            // Usar data_fim_trial se existir, senão calcular baseado na data de criação
            if (!empty($empresa['data_fim_trial'])) {
                $dataFimTrial = new \DateTime($empresa['data_fim_trial']);
            } elseif (!empty($empresa['created_at'])) {
                // Calcular: data de criação + TRIAL_DAYS
                $dataCriacao = new \DateTime($empresa['created_at']);
                $dataFimTrial = $dataCriacao->modify('+' . TRIAL_DAYS . ' days');
            } else {
                // Fallback: hoje + 14 dias
                $dataFimTrial = new \DateTime();
                $dataFimTrial->modify('+14 days');
            }
            
            $agora = new \DateTime();
            $intervalo = $agora->diff($dataFimTrial);
            
            // Se a data final do trial é no futuro, mostrar dias restantes
            if ($dataFimTrial > $agora) {
                $diasTrial = $intervalo->days;
            } else {
                $diasTrial = 0;
            }
        }

        // Capturar conteúdo da view
        ob_start();
        $this->view('assinaturas/planos', [
            'titulo' => 'Escolha seu Plano - ' . APP_NAME,
            'planos' => $this->planos,
            'planoAtual' => $empresa['plano'] ?? 'trial',
            'diasTrial' => $diasTrial,
            'empresa' => $empresa,
            'usoOS' => $usoOS,
            'limiteOS' => $limiteOS,
            'totalTecnicos' => $totalTecnicos,
            'limiteTecnicos' => $limiteTecnicos,
            'limiteArmazenamento' => $limiteArmazenamento
        ]);
        $content = ob_get_clean();
        
        // Renderizar com layout
        $this->layout('main', ['titulo' => 'Escolha seu Plano - ' . APP_NAME, 'content' => $content]);
    }

    /**
     * Página de gerenciamento da assinatura atual
     */
    public function gerenciar(): void
    {
        $empresaId = getEmpresaId();
        $empresa = $this->empresaModel->findById($empresaId);

        $assinatura = null;
        $historico = [];

        if (!empty($empresa['assinatura_id'])) {
            $assinatura = $this->efiPay->buscarAssinatura((int)$empresa['assinatura_id']);
            $historico = $this->efiPay->listarPagamentosAssinatura((int)$empresa['assinatura_id']);
        }

        // Capturar conteúdo da view
        ob_start();
        $this->view('assinaturas/gerenciar', [
            'titulo' => 'Minha Assinatura - ' . APP_NAME,
            'empresa' => $empresa,
            'assinatura' => $assinatura['data'] ?? null,
            'historico' => $historico['data'] ?? [],
            'plano' => $this->planos[$empresa['plano']] ?? null
        ]);
        $content = ob_get_clean();
        
        // Renderizar com layout
        $this->layout('main', ['titulo' => 'Minha Assinatura - ' . APP_NAME, 'content' => $content]);
    }

    /**
     * Cancela a assinatura atual
     */
    public function cancelar(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('assinaturas/gerenciar');
        }

        if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
            setFlash('error', 'Token de segurança inválido.');
            $this->redirect('assinaturas/gerenciar');
        }

        $empresaId = getEmpresaId();
        $empresa = $this->empresaModel->findById($empresaId);

        if (empty($empresa['assinatura_id'])) {
            setFlash('error', 'Nenhuma assinatura ativa.');
            $this->redirect('assinaturas/gerenciar');
        }

        $resultado = $this->efiPay->cancelarAssinatura((int)$empresa['assinatura_id']);

        if (empty($resultado['error'])) {
            $this->empresaModel->update($empresaId, [
                'assinatura_status' => 'cancelled',
                'plano' => 'inactive'
            ]);

            if (function_exists('logSystem')) {
                logSystem('assinatura_cancelada', 'assinaturas', $empresa['assinatura_id'], 'Assinatura cancelada');
            }

            setFlash('success', 'Assinatura cancelada com sucesso.');
        } else {
            setFlash('error', 'Erro ao cancelar assinatura.');
        }

        $this->redirect('assinaturas/gerenciar');
    }

    /**
     * Webhook para notificações do EfiPay
     */
    public function webhook(): void
    {
        // Não requer autenticação - vem da EfiPay
        $input = file_get_contents('php://input');
        $data = json_decode($input, true);

        if (empty($data)) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid data']);
            return;
        }

        // Log do webhook
        error_log('EfiPay Webhook: ' . $input);

        // Processar notificação
        if (!empty($data['event'])) {
            switch ($data['event']) {
                case 'subscription.payment':
                    $this->processarPagamentoAssinatura($data);
                    break;
                case 'subscription.canceled':
                    $this->processarCancelamento($data);
                    break;
                case 'subscription.reactivated':
                    $this->processarReativacao($data);
                    break;
            }
        }

        http_response_code(200);
        echo json_encode(['status' => 'received']);
    }

    /**
     * Processa notificação de pagamento
     */
    private function processarPagamentoAssinatura(array $data): void
    {
        $assinaturaId = $data['data']['subscription_id'] ?? null;
        $status = $data['data']['status'] ?? null;

        if (!$assinaturaId) return;

        // Buscar empresa pela assinatura
        $db = \App\Config\Database::getInstance();
        $stmt = $db->prepare("SELECT id FROM empresas WHERE assinatura_id = ?");
        $stmt->execute([$assinaturaId]);
        $empresa = $stmt->fetch(\PDO::FETCH_ASSOC);

        if ($empresa) {
            $this->empresaModel->update($empresa['id'], [
                'assinatura_status' => $status === 'paid' ? 'active' : 'pending'
            ]);

            if (function_exists('logSystem')) {
                logSystem('assinatura_pagamento', 'assinaturas', $assinaturaId, "Pagamento: {$status}");
            }
        }
    }

    /**
     * Processa notificação de cancelamento
     */
    private function processarCancelamento(array $data): void
    {
        $assinaturaId = $data['data']['subscription_id'] ?? null;
        if (!$assinaturaId) return;

        $db = \App\Config\Database::getInstance();
        $stmt = $db->prepare("UPDATE empresas SET assinatura_status = 'cancelled', plano = 'inactive' WHERE assinatura_id = ?");
        $stmt->execute([$assinaturaId]);

        if (function_exists('logSystem')) {
            logSystem('assinatura_cancelada_webhook', 'assinaturas', $assinaturaId, 'Cancelado via webhook');
        }
    }

    /**
     * Processa notificação de reativação
     */
    private function processarReativacao(array $data): void
    {
        $assinaturaId = $data['data']['subscription_id'] ?? null;
        if (!$assinaturaId) return;

        $db = \App\Config\Database::getInstance();
        $stmt = $db->prepare("UPDATE empresas SET assinatura_status = 'active' WHERE assinatura_id = ?");
        $stmt->execute([$assinaturaId]);

        if (function_exists('logSystem')) {
            logSystem('assinatura_reativada', 'assinaturas', $assinaturaId, 'Reativado via webhook');
        }
    }

    /**
     * Redireciona para checkout EfiPay (Link de Pagamento)
     */
    public function efipayCheckout(string $planoId): void
    {
        if (!isset($this->planos[$planoId])) {
            setFlash('error', 'Plano não encontrado.');
            $this->redirect('assinaturas');
        }

        $empresaId = getEmpresaId();
        $empresa = $this->empresaModel->findById($empresaId);
        $plano = $this->planos[$planoId];

        // Criar link de pagamento via EfiPay
        $resultado = $this->efiPay->criarLinkPagamento(
            $plano['nome'] . ' - ' . APP_NAME,
            $plano['preco'],
            [
                'name' => $empresa['responsavel_nome'] ?? $empresa['nome_fantasia'],
                'email' => $empresa['email'],
                'cpf' => $empresa['cpf_responsavel'] ?? ''
            ],
            APP_URL . '/assinaturas/retorno',
            'EMP_' . $empresaId . '_PLANO_' . $planoId
        );

        if (!empty($resultado['data']['payment_url'])) {
            // Salvar dados temporários para uso após retorno
            $_SESSION['efipay_pending'] = [
                'plano_id' => $planoId,
                'empresa_id' => $empresaId,
                'custom_id' => $resultado['data']['custom_id'] ?? null
            ];
            
            // Redirecionar para checkout EfiPay
            header('Location: ' . $resultado['data']['payment_url']);
            exit;
        } else {
            $erro = $resultado['error_description']
                ?? $resultado['error']
                ?? $resultado['message']
                ?? ($resultado['raw_response'] ?? null)
                ?? 'Erro ao criar link de pagamento';

            $httpCode = $resultado['http_code'] ?? null;
            if (!empty($httpCode)) {
                $erro .= ' (HTTP ' . $httpCode . ')';
            }

            error_log('EfiPay criarLinkPagamento falhou: ' . json_encode($resultado));

            setFlash('error', $erro);
            $this->redirect('assinaturas');
        }
    }

    /**
     * Processa retorno do checkout EfiPay
     */
    public function retorno(): void
    {
        $status = $_GET['status'] ?? '';
        $paymentId = $_GET['payment_id'] ?? '';
        
        if ($status === 'paid' && !empty($_SESSION['efipay_pending'])) {
            $pending = $_SESSION['efipay_pending'];
            $planoId = $pending['plano_id'];
            $empresaId = $pending['empresa_id'];
            
            if (isset($this->planos[$planoId])) {
                $plano = $this->planos[$planoId];
                
                // Atualizar empresa
                $this->empresaModel->update($empresaId, [
                    'plano' => $planoId,
                    'plano_nome' => $plano['nome'],
                    'assinatura_status' => 'active',
                    'assinatura_inicio' => date('Y-m-d H:i:s')
                ]);
                
                setFlash('success', 'Pagamento confirmado! Bem-vindo ao plano ' . $plano['nome']);
            }
            
            unset($_SESSION['efipay_pending']);
        } else {
            setFlash('info', 'Pagamento pendente ou cancelado.');
        }
        
        $this->redirect('assinaturas/gerenciar');
    }

    /**
     * Retorna configurações para o JavaScript do EfiPay
     */
    public function getConfig(): void
    {
        header('Content-Type: application/json');
        
        echo json_encode([
            'identificador_conta' => 'EFIPAY_' . getEmpresaId(),
            'sandbox' => true
        ]);
    }
}
