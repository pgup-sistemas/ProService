<?php
/**
 * proService - OrdemServicoController
 * Arquivo: /app/controllers/OrdemServicoController.php
 */

namespace App\Controllers;

use App\Models\OrdemServico;
use App\Models\Cliente;
use App\Models\Servico;
use App\Models\Produto;
use App\Models\Usuario;
use App\Models\Receita;
use App\Models\OsLog;
use App\Models\Recibo;
use App\Models\Comunicacao;

class OrdemServicoController extends Controller
{
    private OrdemServico $osModel;
    private Cliente $clienteModel;
    private Servico $servicoModel;
    private Produto $produtoModel;
    private Usuario $usuarioModel;
    private Receita $receitaModel;
    private OsLog $osLogModel;
    private Recibo $reciboModel;
    private Comunicacao $comunicacaoModel;

    public function __construct()
    {
        $this->osModel = new OrdemServico();
        $this->clienteModel = new Cliente();
        $this->servicoModel = new Servico();
        $this->produtoModel = new Produto();
        $this->usuarioModel = new Usuario();
        $this->receitaModel = new Receita();
        $this->osLogModel = new OsLog();
        $this->reciboModel = new Recibo();
        $this->comunicacaoModel = new Comunicacao();
    }

    /**
     * Lista de ordens de serviço
     */
    public function index(): void
    {
        $filtros = [];
        
        if (!empty($_GET['status'])) $filtros['status'] = $_GET['status'];
        if (!empty($_GET['cliente_id'])) $filtros['cliente_id'] = (int) $_GET['cliente_id'];
        if (!empty($_GET['tecnico_id'])) $filtros['tecnico_id'] = (int) $_GET['tecnico_id'];
        if (!empty($_GET['prioridade'])) $filtros['prioridade'] = $_GET['prioridade'];
        if (!empty($_GET['busca'])) $filtros['busca'] = sanitizeInput($_GET['busca']);
        if (!empty($_GET['data_inicio'])) $filtros['data_inicio'] = $_GET['data_inicio'];
        if (!empty($_GET['data_fim'])) $filtros['data_fim'] = $_GET['data_fim'];
        
        $page = (int) ($_GET['page'] ?? 1);
        
        $resultado = $this->osModel->listar($filtros, 'os.created_at DESC', $page, 20);
        
        $clientes = $this->clienteModel->findAll(['ativo' => 1], 'nome ASC');
        $tecnicos = $this->usuarioModel->listarTecnicos();
        
        $this->layout('main', [
            'titulo' => 'Ordens de Serviço',
            'content' => $this->renderView('ordens/index', [
                'ordens' => $resultado['items'],
                'paginacao' => $resultado,
                'clientes' => $clientes,
                'tecnicos' => $tecnicos,
                'filtros' => $filtros
            ])
        ]);
    }

    /**
     * Formulário de nova OS
     */
    public function create(): void
    {
        // Verificar limite de OS
        $limiteOS = verificarLimiteOS();
        if (!$limiteOS['permitido']) {
            setFlash('error', $limiteOS['mensagem']);
            $this->redirect('ordens');
        }
        
        $clientes = $this->clienteModel->findAll(['ativo' => 1], 'nome ASC');
        $servicos = $this->servicoModel->findAll(['ativo' => 1], 'nome ASC');
        $tecnicos = [];
        if (isAdmin()) {
            $tecnicos = $this->usuarioModel->listarTecnicos();
        } else {
            $usuarioId = getUsuarioId();
            if ($usuarioId) {
                $u = $this->usuarioModel->findById($usuarioId);
                if ($u) {
                    $tecnicos = [$u];
                }
            }
        }
        $produtos = $this->produtoModel->findAll(['ativo' => 1], 'nome ASC');
        
        $this->layout('main', [
            'titulo' => 'Nova Ordem de Serviço',
            'content' => $this->renderView('ordens/create', [
                'clientes' => $clientes,
                'servicos' => $servicos,
                'tecnicos' => $tecnicos,
                'produtos' => $produtos,
                'limiteOS' => $limiteOS,
                'usuarioAtualId' => getUsuarioId(),
                'perfilAtual' => getPerfil()
            ])
        ]);
    }

    /**
     * Salva nova OS
     */
    public function store(): void
    {
        $requestId = 'os_create_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4));
        error_log("=== DEBUG OS CREATE [{$requestId}] ===");
        error_log('Method: ' . ($_SERVER['REQUEST_METHOD'] ?? ''));
        error_log('URI: ' . ($_SERVER['REQUEST_URI'] ?? ''));
        error_log('Usuario: ' . (getUsuarioId() ?? 'NULL') . ' | Empresa: ' . (getEmpresaId() ?? 'NULL') . ' | Perfil: ' . (getPerfil() ?? 'NULL'));
        error_log('POST data: ' . print_r($_POST, true));

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION['debug_request_id'] = $requestId;
        
        if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
            error_log('CSRF validation failed');
            if (defined('ENVIRONMENT') && ENVIRONMENT === 'development') {
                $_SESSION['debug_error'] = [
                    'request_id' => $requestId,
                    'step' => 'csrf',
                    'message' => 'Token CSRF inválido ou expirado.',
                    'csrf_in_post' => isset($_POST['csrf_token']),
                    'csrf_in_session' => isset($_SESSION['csrf_token']),
                    'csrf_time' => $_SESSION['csrf_token_time'] ?? null,
                ];
            }
            setFlash('error', 'Token de segurança inválido.');
            $this->redirect('ordens/create');
        }
        error_log('CSRF validated successfully');

        // Verificar limite de OS
        $limiteOS = verificarLimiteOS();
        if (!$limiteOS['permitido']) {
            error_log('OS limit exceeded: ' . $limiteOS['mensagem']);
            if (defined('ENVIRONMENT') && ENVIRONMENT === 'development') {
                $_SESSION['debug_error'] = [
                    'request_id' => $requestId,
                    'step' => 'limite_os',
                    'message' => $limiteOS['mensagem'],
                    'limite' => $limiteOS,
                ];
            }
            setFlash('error', $limiteOS['mensagem']);
            $this->redirect('ordens');
        }

        $tecnicoId = (int) ($_POST['tecnico_id'] ?? 0) ?: null;
        if (!isAdmin()) {
            $tecnicoId = getUsuarioId();
        }

        $data = [
            'cliente_id' => (int) ($_POST['cliente_id'] ?? 0),
            'servico_id' => (int) ($_POST['servico_id'] ?? 0) ?: null,
            'tecnico_id' => $tecnicoId,
            'descricao' => sanitizeInput($_POST['descricao'] ?? ''),
            'prioridade' => sanitizeInput($_POST['prioridade'] ?? 'normal'),
            'previsao_entrega' => !empty($_POST['previsao_entrega']) ? $_POST['previsao_entrega'] : null,
            'valor_servico' => parseMoney($_POST['valor_servico'] ?? '0'),
            'taxas_adicionais' => parseMoney($_POST['taxas_adicionais'] ?? '0'),
            'desconto' => parseMoney($_POST['desconto'] ?? '0'),
            'forma_pagamento_acordada' => sanitizeInput($_POST['forma_pagamento'] ?? ''),
            'garantia_dias' => (int) ($_POST['garantia_dias'] ?? 0),
            'observacoes_internas' => sanitizeInput($_POST['observacoes_internas'] ?? ''),
            'observacoes_cliente' => sanitizeInput($_POST['observacoes_cliente'] ?? '')
        ];
        
        error_log('Parsed data: ' . print_r($data, true));

        if (empty(getEmpresaId())) {
            error_log('Empresa não identificada na sessão (empresa_id ausente).');
            if (defined('ENVIRONMENT') && ENVIRONMENT === 'development') {
                $_SESSION['debug_error'] = [
                    'request_id' => $requestId,
                    'step' => 'empresa',
                    'message' => 'empresa_id ausente na sessão. Faça logout/login novamente ou verifique o fluxo de autenticação.',
                    'session_keys' => array_keys($_SESSION ?? []),
                ];
            }
            setFlash('error', 'Empresa não identificada. Faça login novamente.');
            $this->redirect('logout');
        }

        // Validações
        $errors = [];
        if (empty($data['cliente_id'])) {
            $errors[] = 'Selecione um cliente.';
        }

        if (isAdmin()) {
            $tecnicos = $this->usuarioModel->listarTecnicos();
            if (!empty($tecnicos) && empty($data['tecnico_id'])) {
                $errors[] = 'Selecione um técnico responsável.';
            }
            if (empty($tecnicos) && empty($data['tecnico_id'])) {
                $errors[] = 'Nenhum técnico cadastrado. Cadastre um técnico antes de criar a OS.';
            }
        }

        if (!empty($errors)) {
            error_log('Validation errors: ' . print_r($errors, true));
            $_SESSION['errors'] = $errors;
            if (defined('ENVIRONMENT') && ENVIRONMENT === 'development') {
                $_SESSION['debug_error'] = [
                    'request_id' => $requestId,
                    'step' => 'validation',
                    'message' => 'Falha de validação do formulário.',
                    'errors' => $errors,
                    'data' => $data,
                ];
            }
            $this->redirect('ordens/create');
        }

        // Processar produtos
        $produtos = [];
        if (!empty($_POST['produtos'])) {
            foreach ($_POST['produtos'] as $produto) {
                if (!empty($produto['id']) && !empty($produto['quantidade']) && $produto['quantidade'] > 0) {
                    $produtos[] = [
                        'produto_id' => (int) $produto['id'],
                        'quantidade' => (float) $produto['quantidade']
                    ];
                }
            }
        }
        
        error_log('Produtos to add: ' . print_r($produtos, true));

        try {
            $osId = $this->osModel->criar($data, $produtos);
        } catch (\Throwable $e) {
            error_log('Exception no model criar(): ' . $e->getMessage());
            $osId = null;
            if (defined('ENVIRONMENT') && ENVIRONMENT === 'development') {
                $_SESSION['debug_error'] = [
                    'request_id' => $requestId,
                    'step' => 'model_exception',
                    'message' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                ];
            }
        }
        
        error_log('OS criada ID: ' . ($osId ?? 'NULL'));
        
        if ($osId) {
            incrementarContadorOS();
            logAuditoria('OS criada', $osId);
            // Registrar log de criação
            $this->osLogModel->registrarCriacao($osId);
            setFlash('success', 'Ordem de Serviço criada com sucesso!');
            $this->redirect('ordens/show/' . $osId);
        } else {
            error_log('Falha ao criar OS no model');
            if (defined('ENVIRONMENT') && ENVIRONMENT === 'development') {
                $_SESSION['debug_error'] = $_SESSION['debug_error'] ?? [
                    'request_id' => $requestId,
                    'step' => 'model_null',
                    'message' => 'Model retornou NULL ao criar OS (verifique logs do PHP / MySQL).',
                ];
            }
            setFlash('error', 'Erro ao criar Ordem de Serviço.');
            $this->redirect('ordens/create');
        }
    }

    /**
     * Visualiza OS
     */
    public function show(int $id): void
    {
        $os = $this->osModel->findComplete($id);
        
        if (!$os) {
            setFlash('error', 'Ordem de Serviço não encontrada.');
            $this->redirect('ordens');
        }
        
        $produtos = $this->osModel->getProdutos($id);
        $fotos = $this->osModel->getFotos($id);
        
        // Buscar receita vinculada
        $receita = $this->receitaModel->findBy('os_id', $id);
        
        // Buscar recibo vinculado
        $recibo = $this->reciboModel->findByOS($id);
        
        // Buscar logs da OS para exibir na timeline
        $logs = $this->osLogModel->listarPorOS($id);
        
        $this->layout('main', [
            'titulo' => 'OS #' . str_pad($os['numero_os'], 4, '0', STR_PAD_LEFT),
            'content' => $this->renderView('ordens/show', [
                'os' => $os,
                'produtos' => $produtos,
                'fotos' => $fotos,
                'receita' => $receita,
                'recibo' => $recibo,
                'logs' => $logs
            ])
        ]);
    }

    /**
     * Atualiza status da OS
     */
    public function status(int $id): void
    {
        if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
            setFlash('error', 'Token de segurança inválido.');
            $this->redirect('ordens/show/' . $id);
        }

        $novoStatus = sanitizeInput($_POST['status'] ?? '');
        $statusValidos = ['aberta', 'em_orcamento', 'aprovada', 'em_execucao', 'pausada', 'finalizada', 'paga', 'cancelada'];
        
        if (!in_array($novoStatus, $statusValidos)) {
            setFlash('error', 'Status inválido.');
            $this->redirect('ordens/show/' . $id);
        }

        $os = $this->osModel->findById($id);
        $statusAnterior = $os['status'] ?? 'aberta';
        
        if ($this->osModel->atualizarStatus($id, $novoStatus, getUsuarioId())) {
            // Registrar log de mudança de status
            $this->osLogModel->registrarMudancaStatus($id, $statusAnterior, $novoStatus);
            
            // Se marcou como paga, gerar recibo automaticamente e atualizar receita
            if ($novoStatus === 'paga' && $statusAnterior !== 'paga') {
                $osCompleto = $this->osModel->findComplete($id);
                if ($osCompleto) {
                    // Atualizar forma de pagamento na receita vinculada
                    $receita = $this->receitaModel->findBy('os_id', $id);
                    if ($receita && !empty($osCompleto['forma_pagamento_acordada'])) {
                        $this->receitaModel->update($receita['id'], [
                            'forma_pagamento' => $osCompleto['forma_pagamento_acordada'],
                            'status' => 'recebido',
                            'data_recebimento' => date('Y-m-d')
                        ]);
                    }
                    
                    $reciboId = $this->reciboModel->gerarDoOS($osCompleto);
                    if ($reciboId) {
                        $this->osLogModel->registrar($id, 'Recibo #' . $reciboId . ' gerado automaticamente', 'outro');
                        setFlash('success', 'Status atualizado para: ' . getStatusLabel($novoStatus) . ' | Recibo #' . $reciboId . ' gerado!');
                    } else {
                        setFlash('success', 'Status atualizado para: ' . getStatusLabel($novoStatus));
                    }
                }
            } else {
                setFlash('success', 'Status atualizado para: ' . getStatusLabel($novoStatus));
            }
        } else {
            setFlash('error', 'Erro ao atualizar status.');
        }
        
        $this->redirect('ordens/show/' . $id);
    }

    /**
     * Adiciona produto à OS
     */
    public function adicionarProduto(int $id): void
    {
        if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
            setFlash('error', 'Token de segurança inválido.');
            $this->redirect('ordens/show/' . $id);
        }

        $produtoId = (int) ($_POST['produto_id'] ?? 0);
        $quantidade = (float) ($_POST['quantidade'] ?? 0);
        
        if ($produtoId <= 0 || $quantidade <= 0) {
            setFlash('error', 'Selecione um produto e informe a quantidade.');
            $this->redirect('ordens/show/' . $id);
        }

        // Verificar estoque
        if (!$this->produtoModel->verificarEstoque($produtoId, $quantidade)) {
            setFlash('error', 'Estoque insuficiente para este produto.');
            $this->redirect('ordens/show/' . $id);
        }

        if ($this->osModel->adicionarProduto($id, $produtoId, $quantidade)) {
            // Buscar dados do produto para o log
            $produto = $this->produtoModel->findById($produtoId);
            if ($produto) {
                $valorProduto = ($produto['preco_venda'] ?? $produto['custo_unitario'] ?? 0) * $quantidade;
                $this->osLogModel->registrarAdicaoProduto($id, $produto['nome'], $quantidade, $valorProduto);
            }
            setFlash('success', 'Produto adicionado à OS.');
        } else {
            setFlash('error', 'Erro ao adicionar produto.');
        }
        
        $this->redirect('ordens/show/' . $id);
    }

    /**
     * Remove produto da OS
     */
    public function removerProduto(int $osId, int $produtoOsId): void
    {
        if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
            setFlash('error', 'Token de segurança inválido.');
            $this->redirect('ordens/show/' . $osId);
        }

        // Buscar dados do produto antes de remover para o log
        $osProduto = $this->osModel->getProdutos($osId);
        $produtoRemovido = null;
        foreach ($osProduto as $p) {
            if ($p['id'] == $produtoOsId) {
                $produtoRemovido = $p;
                break;
            }
        }
        
        if ($this->osModel->removerProduto($osId, $produtoOsId)) {
            // Registrar log de remoção
            if ($produtoRemovido) {
                $this->osLogModel->registrarRemocaoProduto($osId, $produtoRemovido['produto_nome'], $produtoRemovido['quantidade']);
            }
            setFlash('success', 'Produto removido da OS.');
        } else {
            setFlash('error', 'Erro ao remover produto.');
        }
        
        $this->redirect('ordens/show/' . $osId);
    }

    /**
     * Formulário de edição de OS
     */
    public function edit(int $id): void
    {
        $os = $this->osModel->findComplete($id);
        
        if (!$os) {
            setFlash('error', 'Ordem de Serviço não encontrada.');
            $this->redirect('ordens');
        }
        
        // Não permitir editar OS cancelada ou paga
        if (in_array($os['status'], ['cancelada', 'paga'])) {
            setFlash('error', 'OS cancelada ou paga não pode ser editada.');
            $this->redirect('ordens/show/' . $id);
        }
        
        $clientes = $this->clienteModel->findAll(['ativo' => 1], 'nome ASC');
        $servicos = $this->servicoModel->findAll(['ativo' => 1], 'nome ASC');
        $tecnicos = [];
        if (isAdmin()) {
            $tecnicos = $this->usuarioModel->listarTecnicos();
        } else {
            $usuarioId = getUsuarioId();
            if ($usuarioId) {
                $u = $this->usuarioModel->findById($usuarioId);
                if ($u) {
                    $tecnicos = [$u];
                }
            }
        }
        $produtos = $this->produtoModel->findAll(['ativo' => 1], 'nome ASC');
        $produtosOS = $this->osModel->getProdutos($id);
        
        $this->layout('main', [
            'titulo' => 'Editar OS #' . str_pad($os['numero_os'], 4, '0', STR_PAD_LEFT),
            'content' => $this->renderView('ordens/edit', [
                'os' => $os,
                'clientes' => $clientes,
                'servicos' => $servicos,
                'tecnicos' => $tecnicos,
                'produtos' => $produtos,
                'produtosOS' => $produtosOS,
                'usuarioAtualId' => getUsuarioId(),
                'perfilAtual' => getPerfil()
            ])
        ]);
    }

    /**
     * Atualiza OS existente
     */
    public function update(int $id): void
    {
        if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
            setFlash('error', 'Token de segurança inválido.');
            $this->redirect('ordens/edit/' . $id);
        }

        $os = $this->osModel->findById($id);
        if (!$os) {
            setFlash('error', 'Ordem de Serviço não encontrada.');
            $this->redirect('ordens');
        }
        
        // Não permitir editar OS cancelada ou paga
        if (in_array($os['status'], ['cancelada', 'paga'])) {
            setFlash('error', 'OS cancelada ou paga não pode ser editada.');
            $this->redirect('ordens/show/' . $id);
        }

        $tecnicoId = (int) ($_POST['tecnico_id'] ?? 0) ?: null;
        if (!isAdmin()) {
            $tecnicoId = getUsuarioId();
        }

        $data = [
            'cliente_id' => (int) ($_POST['cliente_id'] ?? 0),
            'servico_id' => (int) ($_POST['servico_id'] ?? 0) ?: null,
            'tecnico_id' => $tecnicoId,
            'descricao' => sanitizeInput($_POST['descricao'] ?? ''),
            'prioridade' => sanitizeInput($_POST['prioridade'] ?? 'normal'),
            'previsao_entrega' => !empty($_POST['previsao_entrega']) ? $_POST['previsao_entrega'] : null,
            'valor_servico' => parseMoney($_POST['valor_servico'] ?? '0'),
            'taxas_adicionais' => parseMoney($_POST['taxas_adicionais'] ?? '0'),
            'desconto' => parseMoney($_POST['desconto'] ?? '0'),
            'forma_pagamento_acordada' => sanitizeInput($_POST['forma_pagamento'] ?? ''),
            'garantia_dias' => (int) ($_POST['garantia_dias'] ?? 0),
            'observacoes_internas' => sanitizeInput($_POST['observacoes_internas'] ?? ''),
            'observacoes_cliente' => sanitizeInput($_POST['observacoes_cliente'] ?? '')
        ];

        // Validações
        $errors = [];
        if (empty($data['cliente_id'])) {
            $errors[] = 'Selecione um cliente.';
        }

        if (isAdmin()) {
            $tecnicos = $this->usuarioModel->listarTecnicos();
            if (!empty($tecnicos) && empty($data['tecnico_id'])) {
                $errors[] = 'Selecione um técnico responsável.';
            }
        }

        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            $this->redirect('ordens/edit/' . $id);
        }

        // Processar produtos
        $produtos = [];
        if (!empty($_POST['produtos'])) {
            foreach ($_POST['produtos'] as $produto) {
                if (!empty($produto['id']) && !empty($produto['quantidade']) && $produto['quantidade'] > 0) {
                    $produtos[] = [
                        'produto_id' => (int) $produto['id'],
                        'quantidade' => (float) $produto['quantidade']
                    ];
                }
            }
        }

        if ($this->osModel->atualizar($id, $data, $produtos)) {
            logAuditoria('OS atualizada', $id);
            // Registrar log de edição
            $this->osLogModel->registrarEdicao($id, array_keys($data));
            setFlash('success', 'Ordem de Serviço atualizada com sucesso!');
            $this->redirect('ordens/show/' . $id);
        } else {
            setFlash('error', 'Erro ao atualizar Ordem de Serviço.');
            $this->redirect('ordens/edit/' . $id);
        }
    }

    /**
     * Exclui OS
     */
    public function destroy(int $id): void
    {
        if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
            setFlash('error', 'Token de segurança inválido.');
            $this->redirect('ordens');
        }

        $os = $this->osModel->findById($id);
        if (!$os) {
            setFlash('error', 'Ordem de Serviço não encontrada.');
            $this->redirect('ordens');
        }
        
        // Apenas admin pode excluir OS
        if (!isAdmin()) {
            setFlash('error', 'Apenas administradores podem excluir OS.');
            $this->redirect('ordens');
        }
        
        // Não permitir excluir OS paga (apenas cancelar)
        if ($os['status'] === 'paga') {
            setFlash('error', 'OS paga não pode ser excluída. Cancele primeiro.');
            $this->redirect('ordens/show/' . $id);
        }

        if ($this->osModel->excluir($id)) {
            logAuditoria('OS excluída', $id);
            setFlash('success', 'Ordem de Serviço excluída com sucesso!');
        } else {
            setFlash('error', 'Erro ao excluir Ordem de Serviço.');
        }
        
        $this->redirect('ordens');
    }

    /**
     * Upload de foto para a OS
     */
    public function uploadFoto(int $id): void
    {
        if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
            setFlash('error', 'Token de segurança inválido.');
            $this->redirect('ordens/show/' . $id);
        }

        $os = $this->osModel->findById($id);
        if (!$os) {
            setFlash('error', 'Ordem de Serviço não encontrada.');
            $this->redirect('ordens');
        }

        // Verificar se arquivo foi enviado
        if (!isset($_FILES['foto']) || $_FILES['foto']['error'] !== UPLOAD_ERR_OK) {
            setFlash('error', 'Nenhuma foto foi enviada ou ocorreu um erro no upload.');
            $this->redirect('ordens/show/' . $id);
        }

        $arquivo = $_FILES['foto'];
        $tipo = sanitizeInput($_POST['tipo_foto'] ?? 'antes'); // antes, durante, depois

        // Validar tipo de arquivo
        $tiposPermitidos = ['image/jpeg', 'image/png', 'image/jpg'];
        if (!in_array($arquivo['type'], $tiposPermitidos)) {
            setFlash('error', 'Tipo de arquivo não permitido. Use JPG ou PNG.');
            $this->redirect('ordens/show/' . $id);
        }

        // Validar tamanho (máximo 5MB)
        if ($arquivo['size'] > 5 * 1024 * 1024) {
            setFlash('error', 'Arquivo muito grande. Máximo 5MB.');
            $this->redirect('ordens/show/' . $id);
        }

        // Criar pasta de uploads se não existir
        $pastaUploads = PROSERVICE_ROOT . '/public/uploads/fotos';
        if (!is_dir($pastaUploads)) {
            mkdir($pastaUploads, 0755, true);
        }

        // Gerar nome único
        $extensao = pathinfo($arquivo['name'], PATHINFO_EXTENSION);
        $nomeArquivo = 'os_' . $id . '_' . $tipo . '_' . date('Ymd_His') . '.' . $extensao;
        $caminhoArquivo = $pastaUploads . '/' . $nomeArquivo;
        $caminhoRelativo = 'uploads/fotos/' . $nomeArquivo;

        // Mover arquivo
        if (move_uploaded_file($arquivo['tmp_name'], $caminhoArquivo)) {
            // Redimensionar imagem se necessário (máximo 1200px)
            $this->redimensionarImagem($caminhoArquivo, 1200);

            // Salvar no banco
            if ($this->osModel->adicionarFoto($id, $caminhoRelativo, $tipo)) {
                // Registrar log
                $this->osLogModel->registrar($id, "Foto adicionada: {$tipo}", 'dados', null, ['tipo' => $tipo, 'arquivo' => $caminhoRelativo]);
                
                setFlash('success', 'Foto adicionada com sucesso!');
            } else {
                setFlash('error', 'Erro ao salvar foto no banco de dados.');
            }
        } else {
            setFlash('error', 'Erro ao mover arquivo de foto.');
        }

        $this->redirect('ordens/show/' . $id);
    }

    /**
     * Redimensiona imagem mantendo proporção
     */
    private function redimensionarImagem(string $caminho, int $larguraMaxima): void
    {
        // Verificar se GD está disponível
        if (!extension_loaded('gd')) {
            return;
        }

        list($larguraOriginal, $alturaOriginal, $tipo) = getimagesize($caminho);

        if ($larguraOriginal <= $larguraMaxima) {
            return; // Não precisa redimensionar
        }

        $ratio = $larguraOriginal / $alturaOriginal;
        $novaLargura = $larguraMaxima;
        $novaAltura = $larguraMaxima / $ratio;

        // Criar imagem redimensionada
        $imagemRedimensionada = imagecreatetruecolor($novaLargura, $novaAltura);

        // Carregar imagem original
        switch ($tipo) {
            case IMAGETYPE_JPEG:
                $imagemOriginal = imagecreatefromjpeg($caminho);
                break;
            case IMAGETYPE_PNG:
                $imagemOriginal = imagecreatefrompng($caminho);
                imagealphablending($imagemRedimensionada, false);
                imagesavealpha($imagemRedimensionada, true);
                break;
            default:
                return;
        }

        // Redimensionar
        imagecopyresampled($imagemRedimensionada, $imagemOriginal, 0, 0, 0, 0, $novaLargura, $novaAltura, $larguraOriginal, $alturaOriginal);

        // Salvar
        switch ($tipo) {
            case IMAGETYPE_JPEG:
                imagejpeg($imagemRedimensionada, $caminho, 85);
                break;
            case IMAGETYPE_PNG:
                imagepng($imagemRedimensionada, $caminho, 6);
                break;
        }

        // Liberar memória
        imagedestroy($imagemOriginal);
        imagedestroy($imagemRedimensionada);
    }

    /**
     * Remove foto da OS
     */
    public function removerFoto(int $osId, int $fotoId): void
    {
        if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
            setFlash('error', 'Token de segurança inválido.');
            $this->redirect('ordens/show/' . $osId);
        }

        // Buscar foto
        $foto = $this->osModel->getFotoById($fotoId);
        if (!$foto || $foto['os_id'] != $osId) {
            setFlash('error', 'Foto não encontrada.');
            $this->redirect('ordens/show/' . $osId);
        }

        // Remover arquivo físico
        $caminhoArquivo = PROSERVICE_ROOT . '/public/' . $foto['arquivo'];
        if (file_exists($caminhoArquivo)) {
            unlink($caminhoArquivo);
        }

        // Remover do banco
        if ($this->osModel->removerFoto($fotoId)) {
            $this->osLogModel->registrar($osId, 'Foto removida', 'dados');
            setFlash('success', 'Foto removida com sucesso.');
        } else {
            setFlash('error', 'Erro ao remover foto.');
        }

        $this->redirect('ordens/show/' . $osId);
    }

    /**
     * Exibe página de assinatura digital
     */
    public function assinatura(int $id): void
    {
        $os = $this->osModel->findComplete($id);
        
        if (!$os) {
            setFlash('error', 'Ordem de Serviço não encontrada.');
            $this->redirect('ordens');
        }
        
        // Não permitir assinatura se OS não estiver finalizada ou paga
        if (!in_array($os['status'], ['finalizada', 'paga'])) {
            setFlash('error', 'Apenas OS finalizada pode receber assinatura.');
            $this->redirect('ordens/show/' . $id);
        }
        
        $this->layout('main', [
            'titulo' => 'Assinatura Digital - OS #' . str_pad($os['numero_os'], 4, '0', STR_PAD_LEFT),
            'content' => $this->renderView('ordens/assinatura', [
                'os' => $os
            ])
        ]);
    }

    /**
     * Salva assinatura digital (base64)
     */
    public function salvarAssinatura(int $id): void
    {
        if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
            setFlash('error', 'Token de segurança inválido.');
            $this->redirect('ordens/assinatura/' . $id);
        }

        $os = $this->osModel->findById($id);
        if (!$os) {
            setFlash('error', 'Ordem de Serviço não encontrada.');
            $this->redirect('ordens');
        }

        $assinaturaBase64 = $_POST['assinatura'] ?? '';
        $tipoAssinatura = sanitizeInput($_POST['tipo'] ?? 'conformidade'); // autorizacao ou conformidade
        $assinanteNome = sanitizeInput($_POST['assinante_nome'] ?? '');
        
        if (empty($assinanteNome)) {
            setFlash('error', 'Nome do assinante é obrigatório.');
            $this->redirect('ordens/assinatura/' . $id);
        }
        
        if (empty($assinaturaBase64)) {
            setFlash('error', 'Nenhuma assinatura foi fornecida.');
            $this->redirect('ordens/assinatura/' . $id);
        }

        // Remover o prefixo data:image/png;base64,
        $assinaturaBase64 = str_replace('data:image/png;base64,', '', $assinaturaBase64);
        
        // Decodificar base64
        $imagem = base64_decode($assinaturaBase64);
        if (!$imagem) {
            setFlash('error', 'Erro ao processar a assinatura.');
            $this->redirect('ordens/assinatura/' . $id);
        }

        // Criar pasta de assinaturas se não existir
        $pastaAssinaturas = PROSERVICE_ROOT . '/public/uploads/assinaturas';
        if (!is_dir($pastaAssinaturas)) {
            mkdir($pastaAssinaturas, 0755, true);
        }

        // Gerar nome único para o arquivo
        $nomeArquivo = 'os_' . $id . '_' . $tipoAssinatura . '_' . date('Ymd_His') . '.png';
        $caminhoArquivo = $pastaAssinaturas . '/' . $nomeArquivo;
        $caminhoRelativo = 'uploads/assinaturas/' . $nomeArquivo;

        // Salvar arquivo
        if (file_put_contents($caminhoArquivo, $imagem)) {
            // Registrar na nova tabela de assinaturas
            $assinaturaModel = new \App\Models\Assinatura();
            $assinaturaModel->registrar([
                'empresa_id' => getEmpresaId(),
                'os_id' => $id,
                'tipo' => $tipoAssinatura,
                'assinante_nome' => $assinanteNome,
                'assinante_documento' => $os['cliente_cpf_cnpj'] ?? null,
                'arquivo' => $caminhoRelativo,
                'observacoes' => 'Assinatura ' . ($tipoAssinatura === 'autorizacao' ? 'de Autorização' : 'de Conformidade')
            ]);

            // Atualizar OS (compatibilidade com sistema antigo)
            $this->osModel->registrarAssinaturaCliente($id, $caminhoRelativo);

            // Registrar log
            $this->osLogModel->registrarAssinatura($id, $tipoAssinatura);

            setFlash('success', 'Assinatura de ' . ($tipoAssinatura === 'autorizacao' ? 'Autorização' : 'Conformidade') . ' registrada com sucesso!');
            $this->redirect('ordens/show/' . $id);
        } else {
            setFlash('error', 'Erro ao salvar a assinatura.');
            $this->redirect('ordens/assinatura/' . $id);
        }
    }

    /**
     * Visualiza recibo da OS
     */
    public function verRecibo(int $osId): void
    {
        $recibo = $this->reciboModel->findByOS($osId);
        
        if (!$recibo) {
            setFlash('error', 'Recibo não encontrado para esta OS.');
            $this->redirect('ordens/show/' . $osId);
        }
        
        // Buscar dados completos do recibo
        $reciboCompleto = $this->reciboModel->findComplete($recibo['id']);
        
        // Renderizar view sem layout (para impressão) e exibir
        echo $this->renderView('recibos/view', [
            'recibo' => $reciboCompleto
        ]);
    }

    /**
     * Registra envio de WhatsApp
     */
    public function registrarWhatsApp(int $id): void
    {
        $template = sanitizeInput($_GET['template'] ?? '');
        
        $os = $this->osModel->findById($id);
        if (!$os) {
            http_response_code(404);
            echo json_encode(['error' => 'OS não encontrada']);
            return;
        }
        
        // Processar template
        $variaveis = [
            'cliente_nome' => $os['cliente_nome'],
            'numero_os' => str_pad($os['numero_os'], 4, '0', STR_PAD_LEFT),
            'servico' => $os['servico_nome'] ?? $os['descricao'] ?? 'Serviço',
            'valor' => formatMoney($os['valor_total']),
            'previsao' => $os['previsao_entrega'] ? date('d/m/Y', strtotime($os['previsao_entrega'])) : 'A definir',
            'link_acompanhamento' => url('acompanhar/' . $os['token_publico'])
        ];
        
        $mensagem = Comunicacao::processarTemplate($template, $variaveis);
        
        // Registrar comunicação
        $this->comunicacaoModel->registrar(
            $id,
            $os['cliente_id'],
            'whatsapp',
            $template,
            $mensagem,
            'enviado'
        );
        
        http_response_code(200);
        echo json_encode(['success' => true]);
    }

    /**
     * Visualiza calendário/agenda de OS
     */
    public function calendario(): void
    {
        $mes = (int) ($_GET['mes'] ?? date('m'));
        $ano = (int) ($_GET['ano'] ?? date('Y'));
        
        // Filtros
        $filtros = [
            'status' => $_GET['status'] ?? '',
            'cliente_id' => $_GET['cliente_id'] ?? '',
            'tecnico_id' => $_GET['tecnico_id'] ?? '',
            'tipo_data' => $_GET['tipo_data'] ?? 'previsao_entrega',
            'busca' => $_GET['busca'] ?? ''
        ];
        
        // Calcular período do mês
        $primeiroDia = date('Y-m-01', strtotime("{$ano}-{$mes}-01"));
        $ultimoDia = date('Y-m-t', strtotime("{$ano}-{$mes}-01"));
        
        // Buscar OS do período com filtros
        $ordens = $this->osModel->listarPorDataComFiltros($primeiroDia, $ultimoDia, $filtros);
        
        // Organizar OS por data
        $ordensPorDia = [];
        foreach ($ordens as $os) {
            $data = match($filtros['tipo_data']) {
                'data_entrada' => $os['data_entrada'] ?? $os['created_at'],
                default => $os['previsao_entrega'] ?? $os['created_at']
            };
            $dia = date('Y-m-d', strtotime($data));
            $ordensPorDia[$dia][] = $os;
        }
        
        // Dados para navegação
        $mesAnterior = date('m', strtotime("{$ano}-{$mes}-01 -1 month"));
        $anoAnterior = date('Y', strtotime("{$ano}-{$mes}-01 -1 month"));
        $mesProximo = date('m', strtotime("{$ano}-{$mes}-01 +1 month"));
        $anoProximo = date('Y', strtotime("{$ano}-{$mes}-01 +1 month"));
        
        // Nome do mês em português
        $meses = [
            1 => 'Janeiro', 2 => 'Fevereiro', 3 => 'Março', 4 => 'Abril',
            5 => 'Maio', 6 => 'Junho', 7 => 'Julho', 8 => 'Agosto',
            9 => 'Setembro', 10 => 'Outubro', 11 => 'Novembro', 12 => 'Dezembro'
        ];
        $nomeMes = $meses[(int)$mes] . ' ' . $ano;
        
        $this->layout('main', [
            'titulo' => 'Calendário de OS',
            'content' => $this->renderView('ordens/calendario', [
                'ordensPorDia' => $ordensPorDia,
                'mes' => $mes,
                'ano' => $ano,
                'mesAnterior' => $mesAnterior,
                'anoAnterior' => $anoAnterior,
                'mesProximo' => $mesProximo,
                'anoProximo' => $anoProximo,
                'nomeMes' => $nomeMes,
                'filtros' => $filtros,
                'clientes' => $this->clienteModel->findAll(['ativo' => 1], 'nome ASC'),
                'tecnicos' => $this->usuarioModel->listarTecnicos()
            ])
        ]);
    }

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
