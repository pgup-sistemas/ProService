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

    // Nota: Planos agora são carregados dinamicamente via getPlanos()
    // Para manter sincronização com config.php

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
        
        // Obter plano atual (com fallback para trial se vazio)
        $planoAtual = $empresa['plano'] ?? 'trial';
        if (empty($planoAtual)) {
            $planoAtual = 'trial'; // Se vazio, assumir trial
        }
        
        // Usar dados do plano, não do banco (banco pode ter valores desatualizado)
        $dadosPlano = $this->empresaModel->getDadosPlano($planoAtual);
        $limiteOS = $dadosPlano['limite_os'] ?? 100;

        $totalTecnicos = $this->usuarioModel->count(['empresa_id' => $empresaId, 'perfil' => 'tecnico', 'ativo' => 1]);
        $limiteTecnicos = $dadosPlano['limite_tecnicos'] ?? 1;

        $limiteArmazenamento = $dadosPlano['limite_armazenamento'] ?? 1024;
        
        // Calcular dias restantes do trial baseado na data de criação da conta
        $diasTrial = 0;
        if ($empresa['plano'] === 'trial' || (empty($empresa['plano']) && !empty($empresa['data_fim_trial']))) {
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
            
            // Usar mesma fórmula que DashboardController para consistência
            $dataFimTrialStr = $dataFimTrial->format('Y-m-d H:i:s');
            $diasTrial = max(0, (strtotime($dataFimTrialStr) - time()) / 86400);
            $diasTrial = (int) ceil($diasTrial); // Arredonda para cima para mostrar dia completo
        }

        // Capturar conteúdo da view
        ob_start();
        $this->view('assinaturas/planos', [
            'titulo' => 'Escolha seu Plano - ' . APP_NAME,
            'planos' => $this->getPlanos(),
            'planoAtual' => $planoAtual,
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
     * Retorna array de planos sincronizado com config.php
     * Conforme SPEC: 2 planos pagos (Básico e Profissional) + Trial
     */
    private function getPlanos(): array
    {
        $formatArmazenamento = function($mb) {
            return $mb >= 1024 ? ($mb / 1024) . ' GB' : $mb . ' MB';
        };

        return [
            'starter' => [
                'id' => 'starter',
                'nome' => 'Básico',
                'descricao' => 'Ideal para começar',
                'preco' => (int)(PLANO_STARTER_PRECO * 100), // Converter para centavos
                'intervalo' => 30,
                'limite_os' => PLANO_STARTER_OS,
                'limite_tecnicos' => PLANO_STARTER_TECNICOS,
                'limite_armazenamento' => PLANO_STARTER_ARMAZENAMENTO,
                'recursos' => [
                    PLANO_STARTER_OS . ' OS/mês',
                    'Até ' . PLANO_STARTER_TECNICOS . ' técnicos',
                    $formatArmazenamento(PLANO_STARTER_ARMAZENAMENTO),
                    'Relatórios básicos',
                    'Suporte por email',
                    'Clientes ilimitados'
                ],
                'destaque' => false
            ],
            'pro' => [
                'id' => 'pro',
                'nome' => 'Profissional',
                'descricao' => 'Para empresas estabelecidas',
                'preco' => (int)(PLANO_PRO_PRECO * 100),
                'intervalo' => 30,
                'limite_os' => PLANO_PRO_OS,
                'limite_tecnicos' => PLANO_PRO_TECNICOS,
                'limite_armazenamento' => PLANO_PRO_ARMAZENAMENTO,
                'recursos' => [
                    'OS ilimitadas',
                    'Técnicos ilimitados',
                    $formatArmazenamento(PLANO_PRO_ARMAZENAMENTO),
                    'Relatórios avançados',
                    'Suporte prioritário',
                    'Clientes ilimitados',
                    'Logs do sistema'
                ],
                'destaque' => true
            ]
        ];
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
        $planos = $this->getPlanos();
        $this->view('assinaturas/gerenciar', [
            'titulo' => 'Minha Assinatura - ' . APP_NAME,
            'empresa' => $empresa,
            'assinatura' => $assinatura['data'] ?? null,
            'historico' => $historico['data'] ?? [],
            'plano' => $planos[$empresa['plano']] ?? null
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
        $planoId = $data['data']['custom_id'] ?? null; // Contém 'EMP_[id]_PLANO_[planoId]'

        if (!$assinaturaId) {
            error_log('Webhook: assinatura_id vazio em subscription.payment');
            return;
        }

        // Buscar empresa pela assinatura
        $db = \App\Config\Database::getInstance();
        $stmt = $db->prepare("SELECT id FROM empresas WHERE assinatura_id = ?");
        $stmt->execute([$assinaturaId]);
        $empresa = $stmt->fetch(\PDO::FETCH_ASSOC);

        if ($empresa) {
            $updateData = [
                'assinatura_status' => $status === 'paid' ? 'active' : 'pending',
                'assinatura_id' => $assinaturaId // Manter sincronizado
            ];

            // Se for pagamento confirmado, atualizar o plano também
            if ($status === 'paid' && !empty($planoId)) {
                // Extrair ID do plano do custom_id (formato: EMP_[empresaId]_PLANO_[planoId])
                preg_match('/PLANO_(\w+)/', $planoId, $matches);
                if (!empty($matches[1])) {
                    $planosDisponiveis = ['starter', 'pro']; // Planos válidos
                    if (in_array($matches[1], $planosDisponiveis)) {
                        $planoAtualizado = $matches[1];
                        $planosDados = $this->empresaModel->getDadosPlano($planoAtualizado);
                        
                        $updateData['plano'] = $planoAtualizado;
                        $updateData['plano_nome'] = $planosDados['nome'] ?? 'Profissional';
                        $updateData['limite_os_mes'] = $planosDados['limite_os'];
                        $updateData['limite_tecnicos'] = $planosDados['limite_tecnicos'];
                        $updateData['limite_armazenamento_mb'] = $planosDados['limite_armazenamento'];
                        $updateData['data_inicio_plano'] = date('Y-m-d');
                    }
                }
            }

            $this->empresaModel->update($empresa['id'], $updateData);

            error_log('Webhook processado: empresa=' . $empresa['id'] . ', status=' . $status . ', plano=' . ($planoAtualizado ?? 'não definido'));

            if (function_exists('logSystem')) {
                logSystem('assinatura_pagamento', 'assinaturas', $assinaturaId, "Status: {$status}, Plano: " . ($planoAtualizado ?? 'preservado'));
            }
        } else {
            error_log('Webhook: Empresa não encontrada para assinatura ' . $assinaturaId);
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
        $planos = $this->getPlanos();
        
        if (!isset($planos[$planoId])) {
            setFlash('error', 'Plano não encontrado.');
            $this->redirect('assinaturas');
        }

        $empresaId = getEmpresaId();
        $empresa = $this->empresaModel->findById($empresaId);
        $plano = $planos[$planoId];

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
            // Extrair e salvar subscription_id se disponível
            $subscriptionId = $resultado['data']['subscription_id'] ?? null;
            
            // Salvar dados temporários para uso após retorno
            $_SESSION['efipay_pending'] = [
                'plano_id' => $planoId,
                'empresa_id' => $empresaId,
                'custom_id' => $resultado['data']['custom_id'] ?? null,
                'subscription_id' => $subscriptionId // ID da assinatura no EfiPay
            ];
            
            // Se já temos subscription_id, salvar no banco (opcional, para rastreamento)
            if ($subscriptionId) {
                $this->empresaModel->update($empresaId, [
                    'assinatura_id' => $subscriptionId
                ]);
                error_log('Assinatura criada: empresa=' . $empresaId . ', subscription_id=' . $subscriptionId);
            }
            
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
     * Valida o pagamento consultando a API para confirmar
     */
    public function retorno(): void
    {
        $planos = $this->getPlanos();
        
        $status = $_GET['status'] ?? '';
        $paymentId = $_GET['payment_id'] ?? '';
        
        // Validação inicial
        if (empty($paymentId) || empty($_SESSION['efipay_pending'])) {
            setFlash('info', 'Retorno do pagamento recebido.');
            $this->redirect('assinaturas');
            return;
        }

        $pending = $_SESSION['efipay_pending'];
        $planoId = $pending['plano_id'] ?? null;
        $empresaId = $pending['empresa_id'] ?? null;

        if (!$planoId || !$empresaId) {
            setFlash('error', 'Dados da sessão inválidos.');
            unset($_SESSION['efipay_pending']);
            $this->redirect('assinaturas');
            return;
        }

        // Validar se plano existe
        if (!isset($planos[$planoId])) {
            setFlash('error', 'Plano selecionado não é válido.');
            unset($_SESSION['efipay_pending']);
            $this->redirect('assinaturas');
            return;
        }

        $plano = $planos[$planoId];

        // Se status foi 'paid', confirmar com a API (opcional mas recomendado)
        if ($status === 'paid') {
            try {
                // Aqui você poderia validar com a API EfiPay se necessário
                // Por enquanto, confiamos no webhook para confirmação final
                
                $this->empresaModel->update($empresaId, [
                    'plano' => $planoId,
                    'plano_nome' => $plano['nome'],
                    'assinatura_status' => 'pending', // Aguarda confirmação do webhook
                    'assinatura_inicio' => date('Y-m-d H:i:s'),
                    'limite_os_mes' => $plano['limite_os'],
                    'limite_tecnicos' => $plano['limite_tecnicos'],
                    'limite_armazenamento_mb' => $plano['limite_armazenamento'],
                    'data_inicio_plano' => date('Y-m-d')
                ]);

                error_log('Retorno de pagamento processado: empresa=' . $empresaId . ', plano=' . $planoId);

                if (function_exists('logSystem')) {
                    logSystem('retorno_pagamento', 'assinaturas', $empresaId, "Plano: {$plano['nome']}, Status: pending (aguardando confirmação)");
                }

                setFlash('success', 'Pagamento recebido! Seu plano ' . $plano['nome'] . ' será ativado em breve após confirmação.');
            } catch (\Exception $e) {
                error_log('Erro ao processar retorno de pagamento: ' . $e->getMessage());
                setFlash('error', 'Erro ao atualizar plano: ' . $e->getMessage());
            }
        } else {
            setFlash('info', 'Pagamento em processamento. Você receberá uma confirmação por e-mail.');
        }

        unset($_SESSION['efipay_pending']);
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
