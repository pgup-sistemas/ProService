<?php
/**
 * proService - FinanceiroController
 * Arquivo: /app/controllers/FinanceiroController.php
 */

namespace App\Controllers;

use App\Models\Receita;
use App\Models\Despesa;
use App\Models\OrdemServico;
use App\Models\Produto;
use App\Models\Parcela;

class FinanceiroController extends Controller
{
    private Receita $receitaModel;
    private Despesa $despesaModel;
    private OrdemServico $osModel;
    private Produto $produtoModel;
    private Parcela $parcelaModel;

    public function __construct()
    {
        $this->receitaModel = new Receita();
        $this->despesaModel = new Despesa();
        $this->osModel = new OrdemServico();
        $this->produtoModel = new Produto();
        $this->parcelaModel = new Parcela();
    }

    /**
     * Dashboard financeiro
     */
    public function index(): void
    {
        $dataInicio = $_GET['data_inicio'] ?? date('Y-m-01');
        $dataFim = $_GET['data_fim'] ?? date('Y-m-t');
        $mes = date('Y-m', strtotime($dataInicio));
        
        // Receitas do período
        $totalReceitas = $this->receitaModel->getTotalPorPeriodo($dataInicio, $dataFim, 'recebido');
        $receitasPendentes = $this->receitaModel->getTotalPendente();
        $receitasPorForma = $this->receitaModel->getPorFormaPagamento($mes);
        
        // Despesas do período
        $totalDespesas = $this->despesaModel->getTotalPorPeriodo($dataInicio, $dataFim);
        $despesasPendentes = $this->despesaModel->getTotalPendente();
        $despesasPorCategoria = $this->despesaModel->getPorCategoria($mes);
        
        // Lucro
        $lucro = $totalReceitas - $totalDespesas;

        // Resultado previsto (caixa): realizado + pendências
        $resultadoPrevisto = ($totalReceitas + $receitasPendentes) - ($totalDespesas + $despesasPendentes);
        
        // Estatísticas
        $statsDespesas = $this->despesaModel->getEstatisticas();
        
        $this->layout('main', [
            'titulo' => 'Financeiro',
            'content' => $this->renderView('financeiro/index', [
                'dataInicio' => $dataInicio,
                'dataFim' => $dataFim,
                'totalReceitas' => $totalReceitas,
                'totalDespesas' => $totalDespesas,
                'lucro' => $lucro,
                'receitasPendentes' => $receitasPendentes,
                'despesasPendentes' => $despesasPendentes,
                'resultadoPrevisto' => $resultadoPrevisto,
                'receitasPorForma' => $receitasPorForma,
                'despesasPorCategoria' => $despesasPorCategoria,
                'statsDespesas' => $statsDespesas
            ])
        ]);
    }

    /**
     * Lista de receitas
     */
    public function receitas(): void
    {
        $filtros = [];
        
        if (!empty($_GET['status'])) $filtros['status'] = $_GET['status'];
        if (!empty($_GET['data_inicio'])) $filtros['data_inicio'] = $_GET['data_inicio'];
        if (!empty($_GET['data_fim'])) $filtros['data_fim'] = $_GET['data_fim'];
        if (!empty($_GET['busca'])) $filtros['busca'] = sanitizeInput($_GET['busca']);
        
        $page = (int) ($_GET['page'] ?? 1);
        
        $resultado = $this->receitaModel->listar($filtros, $page, 20);
        $pendentes = $this->receitaModel->getPendentes();
        
        $this->layout('main', [
            'titulo' => 'Receitas',
            'content' => $this->renderView('financeiro/receitas', [
                'receitas' => $resultado['items'],
                'paginacao' => $resultado,
                'pendentes' => $pendentes,
                'filtros' => $filtros
            ])
        ]);
    }

    /**
     * Marcar receita como paga
     */
    public function receber(int $id): void
    {
        if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
            setFlash('error', 'Token de segurança inválido.');
            $this->redirect('financeiro/receitas');
        }

        $formaPagamento = sanitizeInput($_POST['forma_pagamento'] ?? 'dinheiro');
        
        if ($this->receitaModel->marcarComoPago($id, $formaPagamento)) {
            setFlash('success', 'Receita marcada como recebida!');
        } else {
            setFlash('error', 'Erro ao atualizar receita.');
        }
        
        $this->redirect('financeiro/receitas');
    }

    /**
     * Lista de despesas
     */
    public function despesas(): void
    {
        $filtros = [];
        
        if (!empty($_GET['categoria'])) $filtros['categoria'] = $_GET['categoria'];
        if (!empty($_GET['status'])) $filtros['status'] = $_GET['status'];
        if (!empty($_GET['data_inicio'])) $filtros['data_inicio'] = $_GET['data_inicio'];
        if (!empty($_GET['data_fim'])) $filtros['data_fim'] = $_GET['data_fim'];
        if (!empty($_GET['busca'])) $filtros['busca'] = sanitizeInput($_GET['busca']);
        
        $page = (int) ($_GET['page'] ?? 1);
        
        $resultado = $this->despesaModel->listar($filtros, $page, 20);
        $categorias = $this->despesaModel->getCategorias();
        $pendentes = $this->despesaModel->getPendentes();
        
        $this->layout('main', [
            'titulo' => 'Despesas',
            'content' => $this->renderView('financeiro/despesas', [
                'despesas' => $resultado['items'],
                'paginacao' => $resultado,
                'categorias' => $categorias,
                'pendentes' => $pendentes,
                'filtros' => $filtros
            ])
        ]);
    }

    /**
     * Marcar despesa como paga
     */
    public function pagar(int $id): void
    {
        if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
            setFlash('error', 'Token de segurança inválido.');
            $this->redirect('financeiro/despesas');
        }

        $formaPagamento = sanitizeInput($_POST['forma_pagamento'] ?? 'dinheiro');
        
        if ($this->despesaModel->marcarComoPago($id, $formaPagamento)) {
            setFlash('success', 'Despesa marcada como paga!');
        } else {
            setFlash('error', 'Erro ao atualizar despesa.');
        }
        
        $this->redirect('financeiro/despesas');
    }

    /**
     * Formulário de nova despesa
     */
    public function createDespesa(): void
    {
        $categorias = $this->despesaModel->getCategorias();
        $produtosPorCategoria = $this->produtoModel->listarPorCategoria();
        
        $this->layout('main', [
            'titulo' => 'Nova Despesa',
            'content' => $this->renderView('financeiro/create_despesa', [
                'categorias' => $categorias,
                'produtosPorCategoria' => $produtosPorCategoria
            ])
        ]);
    }

    /**
     * Salva despesa
     */
    public function storeDespesa(): void
    {
        if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
            setFlash('error', 'Token de segurança inválido.');
            $this->redirect('financeiro/despesas/create');
        }

        $comprovantePath = null;
        if (isset($_FILES['comprovante']) && is_array($_FILES['comprovante']) && ($_FILES['comprovante']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
            if (($_FILES['comprovante']['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
                setFlash('error', 'Ocorreu um erro no upload do comprovante.');
                $this->redirect('financeiro/despesas/create');
            }

            $file = $_FILES['comprovante'];
            $maxSize = defined('UPLOAD_MAX_SIZE') ? UPLOAD_MAX_SIZE : (5 * 1024 * 1024);
            if (($file['size'] ?? 0) > $maxSize) {
                setFlash('error', 'Comprovante excede o tamanho máximo permitido.');
                $this->redirect('financeiro/despesas/create');
            }

            $allowedTypes = array_merge(defined('UPLOAD_ALLOWED_TYPES') ? UPLOAD_ALLOWED_TYPES : [], ['application/pdf']);
            $mime = $file['type'] ?? '';
            if (!in_array($mime, $allowedTypes, true)) {
                setFlash('error', 'Tipo de comprovante inválido. Envie imagem ou PDF.');
                $this->redirect('financeiro/despesas/create');
            }

            $uploadBase = defined('UPLOAD_PATH') ? UPLOAD_PATH : (PROSERVICE_ROOT . '/public/uploads/');
            $pastaUploads = rtrim($uploadBase, '/\\') . '/comprovantes_despesas';
            if (!is_dir($pastaUploads)) {
                mkdir($pastaUploads, 0755, true);
            }

            $ext = strtolower(pathinfo($file['name'] ?? 'file', PATHINFO_EXTENSION));
            if ($ext === '') {
                $ext = $mime === 'application/pdf' ? 'pdf' : 'jpg';
            }

            $nomeArquivo = 'despesa_' . (getEmpresaId() ?? 'empresa') . '_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
            $caminhoArquivo = rtrim($pastaUploads, '/\\') . '/' . $nomeArquivo;
            $caminhoRelativo = 'comprovantes_despesas/' . $nomeArquivo;

            if (!move_uploaded_file($file['tmp_name'], $caminhoArquivo)) {
                setFlash('error', 'Erro ao salvar o comprovante enviado.');
                $this->redirect('financeiro/despesas/create');
            }

            $comprovantePath = $caminhoRelativo;
        }

        $tipo = sanitizeInput($_POST['tipo_despesa'] ?? 'operacional');

        if ($tipo === 'estoque') {
            $produtoId = (int) ($_POST['produto_id'] ?? 0);
            $quantidade = parseMoney($_POST['quantidade'] ?? '0');
            $custoUnitario = parseMoney($_POST['custo_unitario'] ?? '0');
            $valorTotalCompra = parseMoney($_POST['valor_total_compra'] ?? '0');
            $fornecedor = sanitizeInput($_POST['fornecedor'] ?? '');

            if ($produtoId <= 0 || $quantidade <= 0) {
                setFlash('error', 'Selecione um produto e informe a quantidade.');
                $this->redirect('financeiro/despesas/create');
            }

            if ($custoUnitario <= 0 && $valorTotalCompra <= 0) {
                setFlash('error', 'Informe o custo unitário ou o valor total da compra.');
                $this->redirect('financeiro/despesas/create');
            }

            $valorTotal = $valorTotalCompra > 0 ? $valorTotalCompra : ($custoUnitario * $quantidade);
            $custoUnitarioCalculado = $valorTotalCompra > 0
                ? ($valorTotalCompra / $quantidade)
                : ($custoUnitario > 0 ? $custoUnitario : ($valorTotal / $quantidade));

            $produto = $this->produtoModel->findById($produtoId);
            if (!$produto) {
                setFlash('error', 'Produto não encontrado.');
                $this->redirect('financeiro/despesas/create');
            }

            $motivo = 'Compra via Financeiro';
            if (!$this->produtoModel->entradaEstoque($produtoId, $quantidade, $custoUnitarioCalculado, $motivo)) {
                setFlash('error', 'Erro ao dar entrada no estoque.');
                $this->redirect('financeiro/despesas/create');
            }

            $descricao = 'Compra de estoque: ' . ($produto['nome'] ?? 'Produto') . ' (' . $quantidade . ' ' . ($produto['unidade'] ?? 'UN') . ')';
            $observacoes = 'Compra de estoque registrada via Financeiro. Cálculo: ' . number_format($quantidade, 2, ',', '.') . ' x R$ ' . number_format($custoUnitarioCalculado, 2, ',', '.') . ' = R$ ' . number_format($valorTotal, 2, ',', '.');
            if ($fornecedor !== '') {
                $observacoes .= ' | Fornecedor: ' . $fornecedor;
            }

            $data = [
                'descricao' => $descricao,
                'categoria' => 'material',
                'valor' => $valorTotal,
                'data_despesa' => $_POST['data_despesa'] ?? date('Y-m-d'),
                'forma_pagamento' => sanitizeInput($_POST['forma_pagamento'] ?? 'dinheiro'),
                'status' => sanitizeInput($_POST['status'] ?? 'pago'),
                'comprovante' => $comprovantePath,
                'observacoes' => $observacoes
            ];

            $id = $this->despesaModel->create($data);
            if ($id) {
                setFlash('success', 'Compra registrada: estoque atualizado e despesa criada com sucesso!');
                $this->redirect('financeiro/despesas');
            }

            setFlash('error', 'Entrada no estoque feita, mas houve erro ao registrar a despesa.');
            $this->redirect('financeiro/despesas');
        }

        $data = [
            'descricao' => sanitizeInput($_POST['descricao'] ?? ''),
            'categoria' => sanitizeInput($_POST['categoria'] ?? 'outros'),
            'valor' => parseMoney($_POST['valor'] ?? '0'),
            'data_despesa' => $_POST['data_despesa'] ?? date('Y-m-d'),
            'forma_pagamento' => sanitizeInput($_POST['forma_pagamento'] ?? 'dinheiro'),
            'status' => sanitizeInput($_POST['status'] ?? 'pago'),
            'comprovante' => $comprovantePath,
            'observacoes' => sanitizeInput($_POST['observacoes'] ?? '')
        ];

        if (empty($data['descricao']) || $data['valor'] <= 0) {
            setFlash('error', 'Descrição e valor são obrigatórios.');
            $this->redirect('financeiro/despesas/create');
        }

        $id = $this->despesaModel->create($data);
        
        if ($id) {
            setFlash('success', 'Despesa registrada com sucesso!');
            $this->redirect('financeiro/despesas');
        } else {
            setFlash('error', 'Erro ao registrar despesa.');
            $this->redirect('financeiro/despesas/create');
        }
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

    /**
     * Lista de parcelas pendentes
     */
    public function parcelas(): void
    {
        $page = (int) ($_GET['page'] ?? 1);
        $perPage = 20;
        
        $result = $this->parcelaModel->getPorPeriodoPaginado(
            date('Y-m-01'),
            date('Y-m-t', strtotime('+3 months')),
            $page,
            $perPage
        );
        
        $this->layout('main', [
            'titulo' => 'Parcelas',
            'content' => $this->renderView('financeiro/parcelas', [
                'parcelas' => $result['data'],
                'paginacao' => $result
            ])
        ]);
    }

    /**
     * Marca parcela como paga
     */
    public function pagarParcela(int $parcelaId): void
    {
        $formaPagamento = sanitizeInput($_POST['forma_pagamento'] ?? 'dinheiro');
        $dataPagamento = $_POST['data_pagamento'] ?? date('Y-m-d');
        
        if ($this->parcelaModel->marcarComoPaga($parcelaId, $formaPagamento, $dataPagamento)) {
            setFlash('success', 'Parcela marcada como paga!');
        } else {
            setFlash('error', 'Erro ao pagar parcela.');
        }
        
        $this->redirect('financeiro/parcelas');
    }

    /**
     * Gera parcelas para uma receita
     */
    public function gerarParcelas(int $receitaId): void
    {
        $numeroParcelas = (int) ($_POST['numero_parcelas'] ?? 1);
        $dataPrimeiroVencimento = $_POST['data_primeiro_vencimento'] ?? date('Y-m-d');
        
        $receita = $this->receitaModel->findById($receitaId);
        if (!$receita) {
            setFlash('error', 'Receita não encontrada.');
            $this->redirect('financeiro/receitas');
        }
        
        if ($this->parcelaModel->criarParcelas($receitaId, $numeroParcelas, $receita['valor'], $dataPrimeiroVencimento)) {
            setFlash('success', "{$numeroParcelas} parcelas geradas com sucesso!");
        } else {
            setFlash('error', 'Erro ao gerar parcelas.');
        }
        
        $this->redirect('financeiro/receitas');
    }
}
