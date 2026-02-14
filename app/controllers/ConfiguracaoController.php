<?php
/**
 * proService - ConfiguracaoController
 * Arquivo: /app/controllers/ConfiguracaoController.php
 * 
 * Implementa Configurações da Empresa conforme especificação (Seção 17):
 * - Dados da empresa e upload de logo
 * - Cores personalizadas
 * - Editor de template de contrato
 * - Templates de mensagens WhatsApp
 * - Configurações SMTP
 * - Gestão de plano/upgrade
 */

namespace App\Controllers;

use App\Models\Empresa;
use App\Models\Cliente;
use App\Models\Configuracao;
use App\Models\OrdemServico;
use App\Models\Servico;
use App\Models\Usuario;
use App\Services\EmailService;

class ConfiguracaoController extends Controller
{
    private Empresa $empresaModel;
    private Configuracao $configuracaoModel;
    private Usuario $usuarioModel;

    public function __construct()
    {
        $this->empresaModel = new Empresa();
        $this->configuracaoModel = new Configuracao();
        $this->usuarioModel = new Usuario();
    }

    /**
     * Dashboard de configurações - redireciona para dados da empresa
     */
    public function index(): void
    {
        $this->redirect('configuracoes/empresa');
    }

    /**
     * Configurações de dados da empresa (Seção 17.1 da SPEC)
     * Upload de logo, dados cadastrais, informações bancárias
     */
    public function empresa(): void
    {
        $empresaId = getEmpresaId();
        $empresa = $this->empresaModel->findById($empresaId);
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->salvarDadosEmpresa($empresaId);
            return;
        }
        
        $this->layout('main', [
            'titulo' => 'Dados da Empresa',
            'content' => $this->renderView('configuracoes/empresa', [
                'empresa' => $empresa
            ])
        ]);
    }

    /**
     * Salva dados da empresa
     */
    private function salvarDadosEmpresa(int $empresaId): void
    {
        $data = [
            'nome_fantasia' => $_POST['nome_fantasia'] ?? '',
            'razao_social' => $_POST['razao_social'] ?? '',
            'cnpj_cpf' => $_POST['cnpj_cpf'] ?? '',
            'email' => $_POST['email'] ?? '',
            'telefone' => $_POST['telefone'] ?? '',
            'whatsapp' => $_POST['whatsapp'] ?? '',
            'cep' => $_POST['cep'] ?? '',
            'endereco' => $_POST['endereco'] ?? '',
            'numero' => $_POST['numero'] ?? '',
            'complemento' => $_POST['complemento'] ?? '',
            'bairro' => $_POST['bairro'] ?? '',
            'cidade' => $_POST['cidade'] ?? '',
            'estado' => $_POST['estado'] ?? '',
            'banco_nome' => $_POST['banco_nome'] ?? '',
            'banco_agencia' => $_POST['banco_agencia'] ?? '',
            'banco_conta' => $_POST['banco_conta'] ?? '',
            'banco_tipo' => $_POST['banco_tipo'] ?? 'corrente',
            'chave_pix' => $_POST['chave_pix'] ?? ''
        ];
        
        // Validação
        $errors = $this->validate($data, [
            'nome_fantasia' => 'required|max:255',
            'email' => 'required|email|max:255',
            'cnpj_cpf' => 'required|max:20'
        ]);
        
        if (!empty($errors)) {
            setFlash('error', 'Corrija os erros no formulário.');
            $this->setOld($data);
            $this->back();
            return;
        }
        
        // Upload do logo
        if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
            $logoPath = $this->uploadLogo($_FILES['logo'], $empresaId);
            if ($logoPath) {
                $data['logo'] = $logoPath;
            }
        }
        
        if ($this->empresaModel->update($empresaId, $data)) {
            setFlash('success', 'Dados da empresa atualizados com sucesso!');
        } else {
            setFlash('error', 'Erro ao atualizar dados da empresa.');
        }
        
        $this->redirect('configuracoes/empresa');
    }

    /**
     * Realiza upload do logo da empresa
     */
    private function uploadLogo(array $file, int $empresaId): ?string
    {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $maxSize = 2 * 1024 * 1024; // 2MB
        
        if (!in_array($file['type'], $allowedTypes)) {
            setFlash('error', 'Tipo de arquivo não permitido. Use JPG, PNG, GIF ou WEBP.');
            return null;
        }
        
        if ($file['size'] > $maxSize) {
            setFlash('error', 'Arquivo muito grande. Tamanho máximo: 2MB.');
            return null;
        }
        
        // Cria diretório se não existir
        $uploadDir = __DIR__ . '/../../public/uploads/logos/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        // Gera nome único
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = 'logo_empresa_' . $empresaId . '_' . time() . '.' . $extension;
        $filepath = $uploadDir . $filename;
        
        if (move_uploaded_file($file['tmp_name'], $filepath)) {
            return 'uploads/logos/' . $filename;
        }
        
        return null;
    }

    /**
     * Configurações de aparência - cores personalizadas (Seção 17.2 da SPEC)
     */
    public function aparencia(): void
    {
        $empresaId = getEmpresaId();
        $config = $this->configuracaoModel->getByEmpresaId($empresaId);
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $cores = [
                'cor_primaria' => $_POST['cor_primaria'] ?? '#1e40af',
                'cor_sucesso' => $_POST['cor_sucesso'] ?? '#059669',
                'cor_alerta' => $_POST['cor_alerta'] ?? '#ea580c'
            ];
            
            // Valida formato hex
            foreach ($cores as $key => $cor) {
                if (!preg_match('/^#[a-fA-F0-9]{6}$/', $cor)) {
                    setFlash('error', 'Formato de cor inválido. Use formato HEX (#RRGGBB).');
                    $this->back();
                    return;
                }
            }
            
            if ($this->configuracaoModel->atualizarCores($empresaId, $cores)) {
                setFlash('success', 'Cores atualizadas com sucesso!');
            } else {
                setFlash('error', 'Erro ao atualizar cores.');
            }
            
            $this->redirect('configuracoes/aparencia');
            return;
        }
        
        $this->layout('main', [
            'titulo' => 'Aparência',
            'content' => $this->renderView('configuracoes/aparencia', [
                'cores' => $config
            ])
        ]);
    }

    /**
     * Configurações de comunicação (Seção 17.3 da SPEC)
     * Templates de WhatsApp e SMTP
     */
    public function comunicacao(): void
    {
        $empresaId = getEmpresaId();
        $empresa = $this->empresaModel->findById($empresaId);
        $config = $this->configuracaoModel->getByEmpresaId($empresaId);
        $mergeTags = $this->configuracaoModel->getMergeTags();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = $_POST['action'] ?? '';
            
            if ($action === 'whatsapp') {
                $this->salvarTemplatesWhatsApp($empresaId);
            } elseif ($action === 'smtp') {
                $this->salvarSMTP($empresaId);
            }
            
            return;
        }
        
        $this->layout('main', [
            'titulo' => 'Comunicação',
            'content' => $this->renderView('configuracoes/comunicacao', [
                'empresa' => $empresa,
                'config' => $config,
                'mergeTags' => $mergeTags
            ])
        ]);
    }

    /**
     * Salva templates de WhatsApp
     */
    private function salvarTemplatesWhatsApp(int $empresaId): void
    {
        $templates = [
            'os_criada' => $_POST['template_os_criada'] ?? '',
            'os_finalizada' => $_POST['template_os_finalizada'] ?? '',
            'recibo' => $_POST['template_recibo'] ?? ''
        ];
        
        if ($this->configuracaoModel->atualizarTemplatesWhatsApp($empresaId, $templates)) {
            setFlash('success', 'Templates de WhatsApp atualizados!');
        } else {
            setFlash('error', 'Erro ao atualizar templates.');
        }
        
        $this->redirect('configuracoes/comunicacao');
    }

    /**
     * Salva configurações SMTP
     */
    private function salvarSMTP(int $empresaId): void
    {
        if (!$this->smtpColumnsExist()) {
            setFlash('error', "Seu banco ainda não possui as colunas de SMTP na tabela 'empresas'.\n\nRode este SQL no MySQL:\n\nALTER TABLE empresas\n  ADD COLUMN smtp_host VARCHAR(255) NULL,\n  ADD COLUMN smtp_port INT NULL,\n  ADD COLUMN smtp_user VARCHAR(255) NULL,\n  ADD COLUMN smtp_pass VARCHAR(255) NULL,\n  ADD COLUMN smtp_encryption ENUM('tls','ssl','none') DEFAULT 'tls' NULL;\n");
            $this->redirect('configuracoes/comunicacao');
            return;
        }

        $smtp = [
            'host' => $_POST['smtp_host'] ?? '',
            'port' => $_POST['smtp_port'] ?? '',
            'user' => $_POST['smtp_user'] ?? '',
            'pass' => $_POST['smtp_pass'] ?? '',
            'encryption' => $_POST['smtp_encryption'] ?? 'tls'
        ];

        $smtp['host'] = trim((string) $smtp['host']);
        $smtp['user'] = trim((string) $smtp['user']);
        $smtp['encryption'] = in_array($smtp['encryption'], ['tls', 'ssl', 'none'], true) ? $smtp['encryption'] : 'tls';
        $smtp['port'] = is_numeric($smtp['port']) ? (int) $smtp['port'] : null;

        if ($smtp['host'] === '' || $smtp['user'] === '' || ($smtp['pass'] ?? '') === '' || empty($smtp['port'])) {
            setFlash('error', 'Preencha Host, Porta, Usuário e Senha do SMTP.');
            $this->redirect('configuracoes/comunicacao');
            return;
        }
        
        if ($this->configuracaoModel->atualizarSMTP($empresaId, $smtp)) {
            setFlash('success', 'Configurações SMTP salvas!');
        } else {
            setFlash('error', 'Erro ao salvar configurações SMTP.');
        }
        
        $this->redirect('configuracoes/comunicacao');
    }

    private function smtpColumnsExist(): bool
    {
        try {
            $db = \App\Config\Database::getInstance();
            $stmt = $db->prepare("SHOW COLUMNS FROM empresas LIKE 'smtp_host'");
            $stmt->execute();
            $row = $stmt->fetch();
            return !empty($row);
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Envia e-mail de teste usando SMTP configurado
     */
    public function testarEmail(): void
    {
        if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
            $this->jsonResponse(['success' => false, 'message' => 'Token de segurança inválido.'], 403);
        }

        $empresaId = getEmpresaId();
        if (empty($empresaId)) {
            $this->jsonResponse(['success' => false, 'message' => 'Empresa não identificada.'], 400);
        }

        if (!$this->smtpColumnsExist()) {
            $this->jsonResponse([
                'success' => false,
                'message' => 'Seu banco ainda não possui as colunas de SMTP na tabela empresas. Execute o ALTER TABLE para adicionar smtp_host/smtp_port/smtp_user/smtp_pass/smtp_encryption.'
            ], 400);
        }

        $to = trim((string) ($_POST['email_teste'] ?? ''));
        if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
            $this->jsonResponse(['success' => false, 'message' => 'Informe um e-mail válido para teste.'], 422);
        }

        $emailService = new EmailService((int) $empresaId);
        if (!$emailService->isConfigured()) {
            $this->jsonResponse(['success' => false, 'message' => 'SMTP não configurado. Preencha Host, Porta, Usuário e Senha e salve antes de testar.'], 400);
        }

        $empresa = $this->empresaModel->findById((int) $empresaId);
        $nomeEmpresa = $empresa['nome_fantasia'] ?? 'Sua Empresa';

        $subject = 'Teste de SMTP - proService';
        $body = '<p>Olá!</p>' .
            '<p>Este é um e-mail de teste do <strong>proService</strong>.</p>' .
            '<p>Empresa: <strong>' . e($nomeEmpresa) . '</strong></p>' .
            '<p>Data/Hora: ' . date('d/m/Y H:i:s') . '</p>';

        $ok = $emailService->send($to, $subject, $body, true);

        if ($ok) {
            $this->jsonResponse(['success' => true, 'message' => 'E-mail de teste enviado para ' . $to]);
        }

        $this->jsonResponse(['success' => false, 'message' => 'Falha ao enviar e-mail de teste. Verifique host/porta/criptografia e credenciais.'], 500);
    }

    /**
     * Editor de template de contrato (Seção 17.4 da SPEC)
     */
    public function contrato(): void
    {
        $empresaId = getEmpresaId();
        $config = $this->configuracaoModel->getByEmpresaId($empresaId);
        $mergeTags = $this->configuracaoModel->getMergeTags()['contrato'];
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $template = $_POST['template_contrato'] ?? '';
            
            if ($this->configuracaoModel->atualizarTemplateContrato($empresaId, $template)) {
                setFlash('success', 'Template de contrato atualizado!');
            } else {
                setFlash('error', 'Erro ao atualizar template.');
            }
            
            $this->redirect('configuracoes/contrato');
            return;
        }
        
        $this->layout('main', [
            'titulo' => 'Template de Contrato',
            'content' => $this->renderView('configuracoes/contrato', [
                'template' => $config['template_contrato'] ?? '',
                'mergeTags' => $mergeTags
            ])
        ]);
    }

    /**
     * Preview do contrato preenchido
     */
    public function previewContrato(): void
    {
        $empresaId = getEmpresaId();
        
        // Dados de exemplo para preview
        $dadosExemplo = [
            'empresa_nome' => 'Minha Empresa LTDA',
            'empresa_cnpj' => '00.000.000/0001-00',
            'empresa_endereco' => 'Rua Exemplo, 123 - Centro',
            'empresa_telefone' => '(11) 99999-9999',
            'cliente_nome' => 'Cliente Exemplo',
            'cliente_cpf_cnpj' => '123.456.789-00',
            'cliente_endereco' => 'Av. Teste, 456 - Bairro',
            'cliente_telefone' => '(11) 98888-8888',
            'os_numero' => '1234',
            'os_data' => date('d/m/Y'),
            'os_servico' => 'Formatação de Computador',
            'os_descricao' => 'Formatação completa com backup de arquivos',
            'os_valor' => 250.00,
            'os_garantia' => '30'
        ];
        
        $contrato = $this->configuracaoModel->gerarContrato($empresaId, $dadosExemplo);
        
        $this->layout('main', [
            'titulo' => 'Preview do Contrato',
            'content' => $this->renderView('configuracoes/preview_contrato', [
                'contrato' => $contrato
            ])
        ]);
    }

    /**
     * Gestão de plano e upgrade (Seção 17.5 da SPEC)
     */
    public function plano(): void
    {
        $empresaId = getEmpresaId();
        $empresa = $this->empresaModel->findById($empresaId);

        // Compatibilidade: versões antigas usavam basico/profissional
        $planoKey = $empresa['plano'] ?? 'trial';
        $mapPlano = [
            'basico' => 'starter',
            'profissional' => 'pro'
        ];
        if (isset($mapPlano[$planoKey])) {
            $planoKey = $mapPlano[$planoKey];
        }
        
        // Verifica se é admin
        if (!isAdmin()) {
            setFlash('error', 'Acesso restrito a administradores.');
            $this->redirect('dashboard');
            return;
        }
        
        $planoAtual = $this->empresaModel->getDadosPlano($planoKey);
        $planosDisponiveis = [
            'starter' => $this->empresaModel->getDadosPlano('starter'),
            'pro' => $this->empresaModel->getDadosPlano('pro'),
            'business' => $this->empresaModel->getDadosPlano('business')
        ];
        
        // Uso atual (dados reais)
        $osModel = new OrdemServico();
        $usoOS = $osModel->countMesAtual();
        $limiteOS = $empresa['limite_os_mes'] ?? 20;
        $percentualOS = $limiteOS > 0 ? ($usoOS / $limiteOS) * 100 : 0;

        $totalTecnicos = $this->usuarioModel->count(['empresa_id' => $empresaId, 'perfil' => 'tecnico', 'ativo' => 1]);
        $limiteTecnicos = $empresa['limite_tecnicos'] ?? 1;

        $diasTrial = 0;
        if (($empresa['plano'] ?? '') === 'trial' && !empty($empresa['data_fim_trial'])) {
            $diasTrial = max(0, (strtotime($empresa['data_fim_trial']) - time()) / 86400);
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $novoPlano = $_POST['plano'] ?? '';

            if (isset($mapPlano[$novoPlano])) {
                $novoPlano = $mapPlano[$novoPlano];
            }
            
            if (in_array($novoPlano, ['trial', 'starter', 'pro', 'business'], true)) {
                if ($this->empresaModel->atualizarPlano($empresaId, $novoPlano)) {
                    setFlash('success', 'Plano atualizado com sucesso!');
                } else {
                    setFlash('error', 'Erro ao atualizar plano.');
                }
            }
            
            $this->redirect('configuracoes/plano');
            return;
        }
        
        $this->layout('main', [
            'titulo' => 'Gerenciamento de Plano',
            'content' => $this->renderView('configuracoes/plano', [
                'empresa' => $empresa,
                'planoAtual' => $planoAtual,
                'planoAtualKey' => $planoKey,
                'planosDisponiveis' => $planosDisponiveis,
                'usoOS' => $usoOS,
                'limiteOS' => $limiteOS,
                'percentualOS' => $percentualOS,
                'totalTecnicos' => $totalTecnicos,
                'limiteTecnicos' => $limiteTecnicos,
                'diasTrial' => $diasTrial,
                'trialExpirado' => $this->empresaModel->trialExpirado($empresaId),
                'dataFimTrial' => $empresa['data_fim_trial'] ?? null
            ])
        ]);
    }

    /**
     * API: Gera link de WhatsApp com template preenchido
     */
    public function gerarLinkWhatsApp(): void
    {
        $empresaId = getEmpresaId();
        
        $tipo = $_GET['tipo'] ?? 'os_criada';
        $dados = [
            'cliente_nome' => $_GET['cliente_nome'] ?? '',
            'os_numero' => $_GET['os_numero'] ?? '',
            'os_link' => $_GET['os_link'] ?? '',
            'empresa_nome' => $_GET['empresa_nome'] ?? '',
            'empresa_telefone' => $_GET['empresa_telefone'] ?? '',
            'valor_total' => $_GET['valor_total'] ?? 0,
            'servico_nome' => $_GET['servico_nome'] ?? ''
        ];
        
        $mensagem = $this->configuracaoModel->gerarMensagemWhatsApp($empresaId, $tipo, $dados);
        $telefone = preg_replace('/\D/', '', $_GET['telefone'] ?? '');
        
        $link = "https://wa.me/{$telefone}?text=" . urlencode($mensagem);
        
        $this->jsonResponse([
            'success' => true,
            'link' => $link,
            'mensagem' => $mensagem
        ]);
    }

    /**
     * Gera contrato com dados reais de uma OS específica
     */
    public function gerarContratoOS(int $osId): void
    {
        $empresaId = getEmpresaId();
        
        // Buscar OS com todos os dados
        $osModel = new OrdemServico();
        $os = $osModel->findById($osId);
        
        if (!$os || $os['empresa_id'] != $empresaId) {
            setFlash('error', 'Ordem de serviço não encontrada.');
            $this->redirect('ordens');
            return;
        }
        
        // Buscar dados do cliente
        $clienteModel = new Cliente();
        $cliente = $clienteModel->findById((int) ($os['cliente_id'] ?? 0));
        if (!$cliente) {
            setFlash('error', 'Cliente não encontrado.');
            $this->redirect('ordens/show/' . $os['id']);
            return;
        }
        
        // Buscar dados da empresa
        $empresa = $this->empresaModel->findById($empresaId);
        
        // Buscar serviço
        $servicoModel = new Servico();
        $servico = $servicoModel->findById((int) ($os['servico_id'] ?? 0));
        if (!$servico) {
            setFlash('error', 'Serviço não encontrado.');
            $this->redirect('ordens/show/' . $os['id']);
            return;
        }
        
        // Preparar dados para o contrato
        $dados = [
            'empresa_nome' => $empresa['nome_fantasia'] ?? $empresa['razao_social'] ?? '',
            'empresa_cnpj' => $empresa['cnpj'] ?? '',
            'empresa_endereco' => ($empresa['rua'] ?? '') . ', ' . ($empresa['numero'] ?? '') . ' - ' . ($empresa['bairro'] ?? ''),
            'empresa_telefone' => $empresa['telefone'] ?? '',
            'cliente_nome' => $cliente['nome'] ?? '',
            'cliente_cpf_cnpj' => $cliente['cpf_cnpj'] ?? '',
            'cliente_endereco' => ($cliente['rua'] ?? '') . ', ' . ($cliente['numero'] ?? '') . ' - ' . ($cliente['bairro'] ?? ''),
            'cliente_telefone' => $cliente['telefone'] ?? '',
            'os_numero' => $os['numero_os'] ?? '',
            'os_data' => !empty($os['data_entrada']) ? date('d/m/Y', strtotime($os['data_entrada'])) : date('d/m/Y'),
            'os_servico' => $servico['nome'] ?? '',
            'os_descricao' => $os['descricao'] ?? '',
            'os_valor' => (float) ($os['valor_total'] ?? 0),
            'os_garantia' => $os['garantia_dias'] ?? '0'
        ];
        
        $contrato = $this->configuracaoModel->gerarContrato($empresaId, $dados);
        
        $this->layout('main', [
            'titulo' => 'Contrato da OS #' . $os['numero_os'],
            'content' => $this->renderView('configuracoes/contrato_gerado', [
                'contrato' => $contrato,
                'os' => $os,
                'cliente' => $cliente
            ])
        ]);
    }

    /**
     * Remove logo da empresa
     */
    public function removerLogo(): void
    {
        $empresaId = getEmpresaId();
        $empresa = $this->empresaModel->findById($empresaId);
        
        if ($empresa && $empresa['logo']) {
            // Remove arquivo físico
            $filepath = __DIR__ . '/../../public/' . $empresa['logo'];
            if (file_exists($filepath)) {
                unlink($filepath);
            }
            
            // Atualiza banco
            $this->empresaModel->update($empresaId, ['logo' => null]);
            setFlash('success', 'Logo removido com sucesso!');
        }
        
        $this->redirect('configuracoes/empresa');
    }

    /**
     * Backup manual do banco de dados
     */
    public function backup(): void
    {
        if (!isAdmin()) {
            setFlash('error', 'Acesso restrito a administradores.');
            $this->redirect('configuracoes');
        }
        
        $empresaId = getEmpresaId();
        $empresa = $this->empresaModel->findById($empresaId);
        
        // Gerar nome do arquivo
        $nomeArquivo = 'backup_proservice_' . 
                       sanitizeFileName($empresa['nome_fantasia'] ?? 'empresa') . '_' .
                       date('Y-m-d_H-i-s') . '.sql';
        
        // Cabeçalhos para download
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $nomeArquivo . '"');
        header('Pragma: no-cache');
        header('Expires: 0');
        
        // Gerar SQL
        echo "-- ============================================\n";
        echo "-- Backup proService\n";
        echo "-- Empresa: " . ($empresa['nome_fantasia'] ?? 'N/A') . "\n";
        echo "-- Data: " . date('d/m/Y H:i:s') . "\n";
        echo "-- ============================================\n\n";
        
        // Backup das tabelas principais da empresa
        $tabelas = [
            'empresas' => "SELECT * FROM empresas WHERE id = {$empresaId}",
            'usuarios' => "SELECT * FROM usuarios WHERE empresa_id = {$empresaId}",
            'clientes' => "SELECT * FROM clientes WHERE empresa_id = {$empresaId}",
            'servicos' => "SELECT * FROM servicos WHERE empresa_id = {$empresaId}",
            'produtos' => "SELECT * FROM produtos WHERE empresa_id = {$empresaId}",
            'ordens_servico' => "SELECT * FROM ordens_servico WHERE empresa_id = {$empresaId}",
            'receitas' => "SELECT * FROM receitas WHERE empresa_id = {$empresaId}",
            'despesas' => "SELECT * FROM despesas WHERE empresa_id = {$empresaId}",
            'configuracoes_empresa' => "SELECT * FROM configuracoes_empresa WHERE empresa_id = {$empresaId}",
        ];
        
        foreach ($tabelas as $tabela => $sql) {
            echo "-- ----------------------------------------\n";
            echo "-- Tabela: {$tabela}\n";
            echo "-- ----------------------------------------\n";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $registros = $stmt->fetchAll();
            
            if (empty($registros)) {
                echo "-- Sem registros\n\n";
                continue;
            }
            
            // Gerar INSERTs
            foreach ($registros as $registro) {
                $colunas = implode(', ', array_keys($registro));
                $valores = [];
                foreach ($registro as $valor) {
                    if ($valor === null) {
                        $valores[] = 'NULL';
                    } else {
                        $valores[] = "'" . addslashes($valor) . "'";
                    }
                }
                $valoresStr = implode(', ', $valores);
                
                echo "INSERT INTO {$tabela} ({$colunas}) VALUES ({$valoresStr});\n";
            }
            echo "\n";
        }
        
        // Registrar log
        logSistema('Backup manual gerado', 'configuracoes', $empresaId, ['arquivo' => $nomeArquivo]);
        
        exit;
    }

    /**
     * Renderiza view e retorna como string
     */
    private function renderView(string $view, array $data = []): string
    {
        extract($data);
        ob_start();
        $viewPath = __DIR__ . '/../views/' . $view . '.php';
        if (file_exists($viewPath)) {
            include $viewPath;
        }
        return ob_get_clean();
    }
}
