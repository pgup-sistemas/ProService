<?php
/**
 * proService - ProdutoController
 * Arquivo: /app/controllers/ProdutoController.php
 */

namespace App\Controllers;

use App\Models\Produto;
use App\Models\Despesa;

class ProdutoController extends Controller
{
    private Produto $produtoModel;
    private Despesa $despesaModel;

    public function __construct()
    {
        $this->produtoModel = new Produto();
        $this->despesaModel = new Despesa();
    }

    /**
     * Lista de produtos
     */
    public function index(): void
    {
        $page = (int) ($_GET['page'] ?? 1);
        $busca = sanitizeInput($_GET['busca'] ?? '');
        
        if (!empty($busca)) {
            $produtos = $this->produtoModel->buscar($busca);
            $paginacao = [
                'items' => $produtos,
                'total' => count($produtos),
                'page' => 1,
                'per_page' => count($produtos),
                'last_page' => 1
            ];
        } else {
            $paginacao = $this->produtoModel->paginate($page, 20, ['ativo' => 1], 'nome ASC');
        }
        
        $estatisticas = $this->produtoModel->getEstatisticas();
        $produtosEmFalta = $this->produtoModel->listarEmFalta();
        
        $this->layout('main', [
            'titulo' => 'Produtos / Estoque',
            'content' => $this->renderView('produtos/index', [
                'produtos' => $paginacao['items'],
                'paginacao' => $paginacao,
                'estatisticas' => $estatisticas,
                'produtosEmFalta' => $produtosEmFalta,
                'busca' => $busca
            ])
        ]);
    }

    /**
     * Formulário de novo produto
     */
    public function create(): void
    {
        $this->layout('main', [
            'titulo' => 'Novo Produto',
            'content' => $this->renderView('produtos/create', [
                'old' => $this->getOldData()
            ])
        ]);
    }

    /**
     * Salva novo produto
     */
    public function store(): void
    {
        if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
            setFlash('error', 'Token de segurança inválido.');
            $this->redirect('produtos/create');
        }

        $data = $this->getFormData(true);
        
        if (empty($data['nome'])) {
            $_SESSION['errors'] = ['Nome do produto é obrigatório.'];
            $this->setOldData($data);
            $this->redirect('produtos/create');
        }

        $id = $this->produtoModel->create($data);
        
        if ($id) {
            // Criar despesa automaticamente se houver estoque inicial com custo
            $quantidadeEstoque = $data['quantidade_estoque'] ?? 0;
            $custoUnitario = $data['custo_unitario'] ?? 0;
            $valorTotalCompra = parseMoney($_POST['valor_total_compra'] ?? '0');
            
            error_log("[DEBUG] Produto cadastrado ID: $id, Qtd: $quantidadeEstoque, Custo: $custoUnitario");
            
            if ($quantidadeEstoque > 0 && ($custoUnitario > 0 || $valorTotalCompra > 0)) {
                $valorTotal = $valorTotalCompra > 0 ? $valorTotalCompra : ($custoUnitario * $quantidadeEstoque);
                $custoUnitarioCalculado = $custoUnitario > 0
                    ? $custoUnitario
                    : ($valorTotal / $quantidadeEstoque);
                
                error_log("[DEBUG] Tentando criar despesa: Valor Total = $valorTotal");
                
                $formaPagamento = $_POST['forma_pagamento_despesa'] ?? 'pix';
                $statusDespesa = $_POST['status_despesa'] ?? 'pago';
                $dataDespesa = $_POST['data_despesa'] ?? date('Y-m-d');

                if ($valorTotalCompra > 0 && $custoUnitario <= 0) {
                    $this->produtoModel->update($id, ['custo_unitario' => $custoUnitarioCalculado]);
                }
                
                $despesaId = $this->despesaModel->create([
                    'descricao' => 'Compra de estoque: ' . $data['nome'] . ' (' . $quantidadeEstoque . ' ' . $data['unidade'] . ')',
                    'categoria' => 'material',
                    'valor' => $valorTotal,
                    'data_despesa' => $dataDespesa,
                    'forma_pagamento' => $formaPagamento,
                    'status' => $statusDespesa,
                    'observacoes' => 'Cadastro inicial de produto com estoque. Cálculo: ' . number_format($quantidadeEstoque, 2, ',', '.') . ' x R$ ' . number_format($custoUnitarioCalculado, 2, ',', '.') . ' = R$ ' . number_format($valorTotal, 2, ',', '.')
                ]);
                
                error_log("[DEBUG] Resultado despesaId: " . ($despesaId ?: 'NULL'));
                
                $statusLabel = $statusDespesa === 'pago' ? 'paga' : 'pendente';
                setFlash('success', 'Produto cadastrado! Despesa de R$ ' . number_format($valorTotal, 2, ',', '.') . ' (' . $statusLabel . ' via ' . $formaPagamento . ') registrada.');
            } else {
                setFlash('success', 'Produto cadastrado com sucesso!');
            }
            
            $this->redirect('produtos');
        } else {
            setFlash('error', 'Erro ao cadastrar produto.');
            $this->redirect('produtos/create');
        }
    }

    /**
     * Formulário de edição
     */
    public function edit(int $id): void
    {
        $produto = $this->produtoModel->findById($id);
        
        if (!$produto) {
            setFlash('error', 'Produto não encontrado.');
            $this->redirect('produtos');
        }
        
        $historico = $this->produtoModel->getHistoricoMovimentacao($id);
        
        $this->layout('main', [
            'titulo' => 'Editar Produto',
            'content' => $this->renderView('produtos/edit', [
                'produto' => $produto,
                'historico' => $historico
            ])
        ]);
    }

    /**
     * Atualiza produto
     */
    public function update(int $id): void
    {
        if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
            setFlash('error', 'Token de segurança inválido.');
            $this->redirect('produtos/edit/' . $id);
        }

        $produto = $this->produtoModel->findById($id);
        
        if (!$produto) {
            setFlash('error', 'Produto não encontrado.');
            $this->redirect('produtos');
        }

        $data = $this->getFormData(false);
        
        if (empty($data['nome'])) {
            $_SESSION['errors'] = ['Nome do produto é obrigatório.'];
            $this->redirect('produtos/edit/' . $id);
        }

        if ($this->produtoModel->update($id, $data)) {
            setFlash('success', 'Produto atualizado com sucesso!');
        } else {
            setFlash('error', 'Erro ao atualizar produto.');
        }
        
        $this->redirect('produtos');
    }

    /**
     * Exclui produto
     */
    public function delete(int $id): void
    {
        if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
            setFlash('error', 'Token de segurança inválido.');
            $this->redirect('produtos');
        }

        $produto = $this->produtoModel->findById($id);
        
        if (!$produto) {
            setFlash('error', 'Produto não encontrado.');
            $this->redirect('produtos');
        }

        // Desativar ao invés de excluir
        if ($this->produtoModel->update($id, ['ativo' => 0])) {
            setFlash('success', 'Produto removido com sucesso!');
        } else {
            setFlash('error', 'Erro ao remover produto.');
        }
        
        $this->redirect('produtos');
    }

    /**
     * Entrada de estoque
     */
    public function entrada(int $id): void
    {
        if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
            setFlash('error', 'Token de segurança inválido.');
            $this->redirect('produtos');
        }

        $quantidade = parseMoney($_POST['quantidade'] ?? '0');
        $custoRaw = (string) ($_POST['custo_unitario_entrada'] ?? '');
        $custo = trim($custoRaw) === '' ? null : parseMoney($custoRaw);
        $motivo = sanitizeInput($_POST['motivo'] ?? 'Entrada manual');
        
        if ($quantidade <= 0) {
            setFlash('error', 'Quantidade deve ser maior que zero.');
            $this->redirect('produtos/edit/' . $id);
        }

        if ($this->produtoModel->entradaEstoque($id, $quantidade, $custo, $motivo)) {
            // Criar despesa automaticamente se houver custo
            if ($custo !== null && $custo > 0) {
                $produto = $this->produtoModel->findById($id);
                $valorTotal = $custo * $quantidade;
                
                $formaPagamento = $_POST['forma_pagamento_entrada'] ?? 'pix';
                $statusDespesa = $_POST['status_despesa_entrada'] ?? 'pago';
                $dataDespesa = $_POST['data_despesa_entrada'] ?? date('Y-m-d');
                
                $this->despesaModel->create([
                    'descricao' => 'Compra de estoque: ' . ($produto['nome'] ?? 'Produto') . ' (' . $quantidade . ' ' . ($produto['unidade'] ?? 'UN') . ')',
                    'categoria' => 'material',
                    'valor' => $valorTotal,
                    'data_despesa' => $dataDespesa,
                    'forma_pagamento' => $formaPagamento,
                    'status' => $statusDespesa,
                    'observacoes' => 'Entrada de estoque registrada. Custo unitário: R$ ' . number_format($custo, 2, ',', '.')
                ]);
            }
            
            setFlash('success', "Entrada de {$quantidade} unidades registrada com sucesso!");
        } else {
            setFlash('error', 'Erro ao registrar entrada.');
        }
        
        $this->redirect('produtos/edit/' . $id);
    }

    /**
     * Busca AJAX de produtos
     */
    public function buscar(): void
    {
        $termo = sanitizeInput($_GET['q'] ?? '');
        
        if (strlen($termo) < 2) {
            $this->jsonResponse([]);
        }
        
        $produtos = $this->produtoModel->buscar($termo);
        
        $resultado = array_map(function($produto) {
            $quantidadeEstoque = (float) ($produto['quantidade_estoque'] ?? 0);
            $precoVenda = (float) ($produto['preco_venda'] ?? 0);
            return [
                'id' => $produto['id'],
                'nome' => $produto['nome'],
                'codigo' => $produto['codigo_sku'],
                // Campos esperados pelo autocomplete na criação de OS
                'quantidade_estoque' => $quantidadeEstoque,
                'preco_venda' => $precoVenda,
                // Compatibilidade/uso futuro
                'estoque' => $quantidadeEstoque,
                'custo' => (float) ($produto['custo_unitario'] ?? 0),
                'text' => $produto['nome'] . ' (Estoque: ' . $quantidadeEstoque . ')'
            ];
        }, $produtos);
        
        $this->jsonResponse($resultado);
    }

    private function getFormData(bool $includeEstoque): array
    {
        $data = [
            'nome' => sanitizeInput($_POST['nome'] ?? ''),
            'codigo_sku' => sanitizeInput($_POST['codigo_sku'] ?? ''),
            'categoria' => sanitizeInput($_POST['categoria'] ?? ''),
            'unidade' => sanitizeInput($_POST['unidade'] ?? 'UN'),
            'quantidade_minima' => parseMoney($_POST['quantidade_minima'] ?? '0'),
            'custo_unitario' => parseMoney($_POST['custo_unitario'] ?? '0'),
            'preco_venda' => parseMoney($_POST['preco_venda'] ?? '0'),
            'fornecedor' => sanitizeInput($_POST['fornecedor'] ?? ''),
            'observacoes' => sanitizeInput($_POST['observacoes'] ?? '')
        ];

        if ($includeEstoque) {
            $data['quantidade_estoque'] = parseMoney($_POST['quantidade_estoque'] ?? '0');
        }

        return $data;
    }

    private function getOldData(): array
    {
        return $_SESSION['old'] ?? [];
    }

    private function setOldData(array $data): void
    {
        $_SESSION['old'] = $data;
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
