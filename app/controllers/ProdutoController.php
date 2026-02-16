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

    /**
     * Exportar produtos como CSV
     */
    public function export(): void
    {
        $filename = 'produtos_export_' . date('Ymd_His') . '.csv';
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        // BOM para Excel
        echo "\xEF\xBB\xBF";

        $output = fopen('php://output', 'w');
        $headers = ['codigo_sku','nome','categoria','unidade','quantidade_estoque','quantidade_minima','custo_unitario','preco_venda','fornecedor','observacoes'];
        fputcsv($output, $headers);

        $produtos = $this->produtoModel->findAll(['ativo' => 1], 'nome ASC');
        foreach ($produtos as $p) {
            $row = [
                $p['codigo_sku'] ?? '',
                $p['nome'] ?? '',
                $p['categoria'] ?? '',
                $p['unidade'] ?? '',
                (float) $p['quantidade_estoque'],
                (float) $p['quantidade_minima'],
                number_format((float) $p['custo_unitario'], 2, '.', ''),
                number_format((float) $p['preco_venda'], 2, '.', ''),
                $p['fornecedor'] ?? '',
                $p['observacoes'] ?? ''
            ];
            fputcsv($output, $row);
        }
        fclose($output);
        exit;
    }

    /**
     * Preview do arquivo CSV — retorna JSON com primeiras linhas e erros (dry-run)
     */
    public function importPreview(): void
    {
        if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
            http_response_code(400);
            echo json_encode(['error' => 'Arquivo não enviado ou inválido']);
            return;
        }

        $file = $_FILES['file'];
        // Limite 8MB para preview
        if ($file['size'] > 8 * 1024 * 1024) {
            http_response_code(400);
            echo json_encode(['error' => 'Arquivo muito grande para preview (máx 8MB)']);
            return;
        }

        $handle = fopen($file['tmp_name'], 'r');
        if ($handle === false) {
            http_response_code(500);
            echo json_encode(['error' => 'Não foi possível ler o arquivo']);
            return;
        }

        // Ler cabeçalho
        $header = fgetcsv($handle);
        if (!$header) {
            echo json_encode(['error' => 'Arquivo CSV vazio ou inválido']);
            return;
        }

        // Normalizar cabeçalho
        $cols = array_map(function($c) { return strtolower(trim($c)); }, $header);
        $expected = ['codigo_sku','nome','categoria','unidade','quantidade_estoque','quantidade_minima','custo_unitario','preco_venda','fornecedor','observacoes'];

        // Ler até 200 linhas para preview
        $preview = [];
        $line = 1;
        while (($data = fgetcsv($handle)) !== false && count($preview) < 200) {
            $line++;
            $row = [];
            foreach ($cols as $i => $col) {
                $row[$col] = $data[$i] ?? null;
            }

            // Validações simples
            $errors = [];
            if (empty(trim((string)($row['nome'] ?? '')))) {
                $errors[] = 'Nome é obrigatório';
            }
            if (!empty($row['quantidade_estoque']) && !is_numeric(str_replace([',', '.'], ['', '.'], $row['quantidade_estoque']))) {
                $errors[] = 'Quantidade inválida';
            }
            if (!empty($row['custo_unitario']) && !is_numeric(str_replace([',', '.'], ['', '.'], $row['custo_unitario']))) {
                $errors[] = 'Custo unitário inválido';
            }

            $preview[] = [
                'line' => $line,
                'data' => $row,
                'errors' => $errors
            ];
        }

        fclose($handle);

        echo json_encode([
            'header' => $cols,
            'preview' => $preview,
            'expected_columns' => $expected
        ]);
    }

    /**
     * Processa o import (criar/atualizar)
     */
    public function import(): void
    {
        if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
            setFlash('error', 'Token de segurança inválido.');
            $this->redirect('produtos');
        }

        if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
            setFlash('error', 'Arquivo não enviado ou inválido.');
            $this->redirect('produtos');
        }

        $updateExisting = isset($_POST['update_existing']) ? true : false;
        $createNew = isset($_POST['create_new']) ? true : false;

        $file = $_FILES['file'];
        if ($file['size'] > 10 * 1024 * 1024) {
            setFlash('error', 'Arquivo muito grande (máx 10MB).');
            $this->redirect('produtos');
        }

        $handle = fopen($file['tmp_name'], 'r');
        if ($handle === false) {
            setFlash('error', 'Não foi possível ler o arquivo.');
            $this->redirect('produtos');
        }

        $header = fgetcsv($handle);
        $cols = array_map(function($c) { return strtolower(trim($c)); }, $header ?: []);

        $total = 0;
        $created = 0;
        $updated = 0;
        $skipped = 0;
        $errors = [];

        $db = \App\Config\Database::getInstance();
        $db->beginTransaction();
        try {
            while (($data = fgetcsv($handle)) !== false) {
                $total++;
                $row = [];
                foreach ($cols as $i => $col) {
                    $row[$col] = isset($data[$i]) ? trim($data[$i]) : null;
                }

                // Sanitização básica (prevenir CSV injection)
                foreach ($row as $k => $v) {
                    if (is_string($v) && preg_match('/^[=\+\-@]/', $v)) {
                        $row[$k] = "'" . $v; // prefixa com aspas simples
                    }
                }

                // Validações mínimas
                if (empty($row['nome'])) {
                    $errors[] = [ 'line' => $total+1, 'error' => 'Nome obrigatório' ];
                    $skipped++;
                    continue;
                }

                $sku = $row['codigo_sku'] ?? null;
                $quantidade = isset($row['quantidade_estoque']) && $row['quantidade_estoque'] !== '' ? floatval(str_replace(',', '.', $row['quantidade_estoque'])) : null;
                $custo = isset($row['custo_unitario']) && $row['custo_unitario'] !== '' ? floatval(str_replace(',', '.', $row['custo_unitario'])) : null;
                $preco = isset($row['preco_venda']) && $row['preco_venda'] !== '' ? floatval(str_replace(',', '.', $row['preco_venda'])) : null;

                if ($sku) {
                    $existing = $this->produtoModel->findBy('codigo_sku', $sku);
                } else {
                    $existing = null;
                }

                $produtoData = [
                    'nome' => $row['nome'] ?? '',
                    'categoria' => $row['categoria'] ?? '',
                    'unidade' => $row['unidade'] ?? 'UN',
                    'quantidade_minima' => isset($row['quantidade_minima']) ? floatval(str_replace(',', '.', $row['quantidade_minima'])) : 0,
                    'custo_unitario' => $custo ?? 0,
                    'preco_venda' => $preco ?? 0,
                    'fornecedor' => $row['fornecedor'] ?? '',
                    'observacoes' => $row['observacoes'] ?? ''
                ];

                if ($existing) {
                    if ($updateExisting) {
                        // atualizar
                        $this->produtoModel->update($existing['id'], array_merge($produtoData, ['codigo_sku' => $sku]));
                        $updated++;

                        // ajustar estoque se informado
                        if ($quantidade !== null && $quantidade != $existing['quantidade_estoque']) {
                            $diff = $quantidade - $existing['quantidade_estoque'];
                            if ($diff > 0) {
                                $this->produtoModel->entradaEstoque($existing['id'], $diff, $custo, 'Import CSV');
                            } else {
                                $this->produtoModel->saidaEstoque($existing['id'], abs($diff), 'Import CSV');
                            }
                        }
                    } else {
                        $skipped++;
                    }
                } else {
                    if ($createNew) {
                        // cria novo produto
                        if ($sku) {
                            $produtoData['codigo_sku'] = $sku;
                        }
                        if ($quantidade !== null) {
                            $produtoData['quantidade_estoque'] = $quantidade;
                        } else {
                            $produtoData['quantidade_estoque'] = 0;
                        }

                        $newId = $this->produtoModel->create($produtoData);
                        if ($newId && $quantidade !== null && $quantidade > 0) {
                            $this->produtoModel->entradaEstoque($newId, $quantidade, $custo, 'Import CSV');
                        }
                        $created++;
                    } else {
                        $skipped++;
                    }
                }
            }

            $db->commit();

            // Registrar log no sistema
            \App\Models\LogSistema::registrar('import_produtos', 'produtos', null, [
                'total' => $total,
                'created' => $created,
                'updated' => $updated,
                'skipped' => $skipped,
                'errors' => $errors
            ]);

            $msg = "Import concluído: total={$total}, criados={$created}, atualizados={$updated}, ignorados={$skipped}";
            if (!empty($errors)) {
                $msg .= ' (erros: ' . count($errors) . ')';
            }

            setFlash('success', $msg);
            fclose($handle);
            $this->redirect('produtos');
        } catch (\Exception $e) {
            $db->rollBack();
            fclose($handle);
            error_log('Erro importando CSV: ' . $e->getMessage());
            setFlash('error', 'Erro ao processar importação: ' . $e->getMessage());
            $this->redirect('produtos');
        }
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
