<?php
/**
 * proService - RelatorioController
 * Arquivo: /app/controllers/RelatorioController.php
 * 
 * Implementa relatórios avançados conforme especificação:
 * - Relatório de Serviços (Seção 15.1)
 * - Relatório Financeiro (Seção 15.2)
 * - Relatório de Estoque (Seção 15.3)
 * - Relatório de Desempenho por Técnico (Seção 15.4)
 * - Relatório de Despesas (Seção 15.5)
 */

namespace App\Controllers;

use App\Models\OrdemServico;
use App\Models\Cliente;
use App\Models\Servico;
use App\Models\Produto;
use App\Models\Receita;
use App\Models\Despesa;
use App\Models\Usuario;
use App\Models\Empresa;

class RelatorioController extends Controller
{
    private OrdemServico $osModel;
    private Cliente $clienteModel;
    private Servico $servicoModel;
    private Produto $produtoModel;
    private Receita $receitaModel;
    private Despesa $despesaModel;
    private Usuario $usuarioModel;
    private Empresa $empresaModel;

    public function __construct()
    {
        $this->osModel = new OrdemServico();
        $this->clienteModel = new Cliente();
        $this->servicoModel = new Servico();
        $this->produtoModel = new Produto();
        $this->receitaModel = new Receita();
        $this->despesaModel = new Despesa();
        $this->usuarioModel = new Usuario();
        $this->empresaModel = new Empresa();
    }

    /**
     * Dashboard de relatórios - página inicial
     */
    public function index(): void
    {
        $empresaId = getEmpresaId();
        
        // Resumo rápido para cards
        $totalOSMes = $this->osModel->countByPeriod(date('Y-m-01'), date('Y-m-t'));
        $totalReceitasMes = $this->receitaModel->getTotalPorPeriodo(date('Y-m-01'), date('Y-m-t'), 'recebido');
        $totalDespesasMes = $this->despesaModel->getTotalPorPeriodo(date('Y-m-01'), date('Y-m-t'));
        $lucroMes = $totalReceitasMes - $totalDespesasMes;
        
        // OS por status
        $osPorStatus = $this->osModel->countByStatus();
        
        // Top serviços
        $topServicos = $this->osModel->getTopServicos(5);
        
        $this->layout('main', [
            'titulo' => 'Relatórios',
            'content' => $this->renderView('relatorios/index', [
                'totalOSMes' => $totalOSMes,
                'totalReceitasMes' => $totalReceitasMes,
                'totalDespesasMes' => $totalDespesasMes,
                'lucroMes' => $lucroMes,
                'osPorStatus' => $osPorStatus,
                'topServicos' => $topServicos
            ])
        ]);
    }

    /**
     * Relatório de Serviços (Seção 15.1 da SPEC)
     * Filtros: período, status, técnico, cliente, serviço
     */
    public function servicos(): void
    {
        $empresaId = getEmpresaId();
        
        // Filtros
        $dataInicio = $_GET['data_inicio'] ?? date('Y-m-01');
        $dataFim = $_GET['data_fim'] ?? date('Y-m-t');
        $status = $_GET['status'] ?? '';
        $tecnicoId = $_GET['tecnico_id'] ?? '';
        $clienteId = $_GET['cliente_id'] ?? '';
        $servicoId = $_GET['servico_id'] ?? '';
        
        // Buscar dados
        $filtros = [
            'data_inicio' => $dataInicio,
            'data_fim' => $dataFim,
            'status' => $status,
            'tecnico_id' => $tecnicoId,
            'cliente_id' => $clienteId,
            'servico_id' => $servicoId
        ];
        
        $ordens = $this->osModel->getRelatorioServicos($filtros);
        
        // Totalizadores
        $totalOS = count($ordens);
        $valorTotal = array_sum(array_column($ordens, 'valor_total'));
        $custoTotal = array_sum(array_column($ordens, 'custo_produtos'));
        $lucroTotal = $valorTotal - $custoTotal;
        $margemMedia = $valorTotal > 0 ? ($lucroTotal / $valorTotal) * 100 : 0;
        
        // Dados para filtros
        $clientes = $this->clienteModel->findAll(['ativo' => 1], 'nome ASC');
        $tecnicos = $this->usuarioModel->listarTecnicos();
        $servicos = $this->servicoModel->findAll(['ativo' => 1], 'nome ASC');
        
        $this->layout('main', [
            'titulo' => 'Relatório de Serviços',
            'content' => $this->renderView('relatorios/servicos', [
                'ordens' => $ordens,
                'totalOS' => $totalOS,
                'valorTotal' => $valorTotal,
                'custoTotal' => $custoTotal,
                'lucroTotal' => $lucroTotal,
                'margemMedia' => $margemMedia,
                'clientes' => $clientes,
                'tecnicos' => $tecnicos,
                'servicos' => $servicos,
                'filtros' => [
                    'data_inicio' => $dataInicio,
                    'data_fim' => $dataFim,
                    'status' => $status,
                    'tecnico_id' => $tecnicoId,
                    'cliente_id' => $clienteId,
                    'servico_id' => $servicoId
                ]
            ])
        ]);
    }

    /**
     * Relatório Financeiro (Seção 15.2 da SPEC)
     * Receitas, despesas, fluxo de caixa
     */
    public function financeiro(): void
    {
        $empresaId = getEmpresaId();
        
        $dataInicio = $_GET['data_inicio'] ?? date('Y-m-01');
        $dataFim = $_GET['data_fim'] ?? date('Y-m-t');
        $tipo = $_GET['tipo'] ?? 'geral'; // geral, receitas, despesas
        
        // Dados financeiros
        $receitas = $this->receitaModel->getPorPeriodo($dataInicio, $dataFim);
        $despesas = $this->despesaModel->getPorPeriodo($dataInicio, $dataFim);
        
        // Totais
        $totalReceitas = array_sum(array_column($receitas, 'valor'));
        $totalDespesas = array_sum(array_column($despesas, 'valor'));
        $lucro = $totalReceitas - $totalDespesas;
        
        // Por categoria/forma de pagamento
        $receitasPorForma = $this->receitaModel->getPorFormaPagamento(date('Y-m', strtotime($dataInicio)));
        $despesasPorCategoria = $this->despesaModel->getPorCategoria(date('Y-m', strtotime($dataInicio)));
        
        // Evolução mensal (últimos 6 meses)
        $evolucaoMensal = $this->getEvolucaoMensal();
        
        // Inadimplência
        $inadimplencia = $this->receitaModel->getInadimplencia(30); // > 30 dias pendentes
        
        $this->layout('main', [
            'titulo' => 'Relatório Financeiro',
            'content' => $this->renderView('relatorios/financeiro', [
                'receitas' => $receitas,
                'despesas' => $despesas,
                'totalReceitas' => $totalReceitas,
                'totalDespesas' => $totalDespesas,
                'lucro' => $lucro,
                'receitasPorForma' => $receitasPorForma,
                'despesasPorCategoria' => $despesasPorCategoria,
                'evolucaoMensal' => $evolucaoMensal,
                'inadimplencia' => $inadimplencia,
                'filtros' => [
                    'data_inicio' => $dataInicio,
                    'data_fim' => $dataFim,
                    'tipo' => $tipo
                ]
            ])
        ]);
    }

    /**
     * Relatório de Estoque (Seção 15.3 da SPEC)
     * Posição atual, movimentações, produtos mais utilizados
     */
    public function estoque(): void
    {
        $empresaId = getEmpresaId();
        
        $tipo = $_GET['tipo'] ?? 'posicao'; // posicao, movimentacao, produtos_usados
        $dataInicio = $_GET['data_inicio'] ?? date('Y-m-01');
        $dataFim = $_GET['data_fim'] ?? date('Y-m-t');
        
        $produtos = [];
        $movimentacoes = [];
        $produtosMaisUsados = [];
        
        switch ($tipo) {
            case 'posicao':
                $produtos = $this->produtoModel->getRelatorioPosicao();
                break;
            case 'movimentacao':
                $movimentacoes = $this->produtoModel->getMovimentacoes($dataInicio, $dataFim);
                break;
            case 'produtos_usados':
                $produtosMaisUsados = $this->produtoModel->getMaisUtilizados($dataInicio, $dataFim);
                break;
        }
        
        // Produtos em falta
        $produtosEmFalta = $this->produtoModel->listarEmFalta();
        
        // Custo total em estoque
        $custoTotalEstoque = $this->produtoModel->getCustoTotalEstoque();
        
        $this->layout('main', [
            'titulo' => 'Relatório de Estoque',
            'content' => $this->renderView('relatorios/estoque', [
                'tipo' => $tipo,
                'produtos' => $produtos,
                'movimentacoes' => $movimentacoes,
                'produtosMaisUsados' => $produtosMaisUsados,
                'produtosEmFalta' => $produtosEmFalta,
                'custoTotalEstoque' => $custoTotalEstoque,
                'filtros' => [
                    'data_inicio' => $dataInicio,
                    'data_fim' => $dataFim,
                    'tipo' => $tipo
                ]
            ])
        ]);
    }

    /**
     * Relatório de Desempenho por Técnico (Seção 15.4 da SPEC)
     */
    public function tecnicos(): void
    {
        // Apenas admin pode ver
        if (!isAdmin()) {
            setFlash('error', 'Acesso restrito a administradores.');
            $this->redirect('dashboard');
        }
        
        $dataInicio = $_GET['data_inicio'] ?? date('Y-m-01');
        $dataFim = $_GET['data_fim'] ?? date('Y-m-t');
        
        $desempenho = $this->usuarioModel->getDesempenhoTecnicos($dataInicio, $dataFim);
        
        // Totalizadores
        $totalOS = array_sum(array_column($desempenho, 'total_os'));
        $totalReceita = array_sum(array_column($desempenho, 'receita_gerada'));
        
        $this->layout('main', [
            'titulo' => 'Relatório de Desempenho',
            'content' => $this->renderView('relatorios/tecnicos', [
                'desempenho' => $desempenho,
                'totalOS' => $totalOS,
                'totalReceita' => $totalReceita,
                'filtros' => [
                    'data_inicio' => $dataInicio,
                    'data_fim' => $dataFim
                ]
            ])
        ]);
    }

    /**
     * Relatório de Despesas (Seção 15.5 da SPEC)
     * Por categoria, variação mensal
     */
    public function despesas(): void
    {
        $dataInicio = $_GET['data_inicio'] ?? date('Y-m-01');
        $dataFim = $_GET['data_fim'] ?? date('Y-m-t');
        $categoria = $_GET['categoria'] ?? '';
        
        $filtros = [
            'data_inicio' => $dataInicio,
            'data_fim' => $dataFim,
            'categoria' => $categoria
        ];
        
        $despesas = $this->despesaModel->getRelatorioDetalhado($filtros);
        
        // Por categoria
        $porCategoria = $this->despesaModel->getPorCategoria(date('Y-m', strtotime($dataInicio)));
        
        // Evolução mensal
        $evolucao = $this->despesaModel->getEvolucaoMensal(6);
        
        // Total
        $totalDespesas = array_sum(array_column($despesas, 'valor'));
        
        // Categorias para filtro
        $categorias = $this->despesaModel->getCategorias();
        
        $this->layout('main', [
            'titulo' => 'Relatório de Despesas',
            'content' => $this->renderView('relatorios/despesas', [
                'despesas' => $despesas,
                'porCategoria' => $porCategoria,
                'evolucao' => $evolucao,
                'totalDespesas' => $totalDespesas,
                'categorias' => $categorias,
                'filtros' => $filtros
            ])
        ]);
    }

    /**
     * Exportar relatório para CSV
     */
    public function exportar(): void
    {
        $tipo = $_GET['tipo'] ?? 'servicos';
        $formato = $_GET['formato'] ?? 'csv';
        
        $dataInicio = $_GET['data_inicio'] ?? date('Y-m-01');
        $dataFim = $_GET['data_fim'] ?? date('Y-m-t');
        
        $filename = "relatorio_{$tipo}_" . date('Y-m-d') . ".{$formato}";
        
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        
        $output = fopen('php://output', 'w');
        
        // UTF-8 BOM
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        
        switch ($tipo) {
            case 'servicos':
                $this->exportarServicosCSV($output, $dataInicio, $dataFim);
                break;
            case 'financeiro':
                $this->exportarFinanceiroCSV($output, $dataInicio, $dataFim);
                break;
            case 'estoque':
                $this->exportarEstoqueCSV($output);
                break;
        }
        
        fclose($output);
        exit;
    }

    /**
     * Imprimir relatório
     */
    public function imprimir(): void
    {
        $tipo = $_GET['tipo'] ?? 'servicos';
        $dataInicio = $_GET['data_inicio'] ?? date('Y-m-01');
        $dataFim = $_GET['data_fim'] ?? date('Y-m-t');
        
        $data = [
            'tipo' => $tipo,
            'filtros' => [
                'data_inicio' => $dataInicio,
                'data_fim' => $dataFim
            ]
        ];
        
        // Buscar dados conforme tipo
        switch ($tipo) {
            case 'servicos':
                $ordens = $this->osModel->getRelatorioServicos([
                    'data_inicio' => $dataInicio,
                    'data_fim' => $dataFim
                ]);
                $data['ordens'] = $ordens;
                $data['valorTotal'] = array_sum(array_column($ordens, 'valor_total'));
                break;
                
            case 'financeiro':
                $receitas = $this->receitaModel->getPorPeriodo($dataInicio, $dataFim);
                $despesas = $this->despesaModel->getPorPeriodo($dataInicio, $dataFim);
                $data['receitas'] = $receitas;
                $data['despesas'] = $despesas;
                $data['totalReceitas'] = array_sum(array_column($receitas, 'valor'));
                $data['totalDespesas'] = array_sum(array_column($despesas, 'valor'));
                break;
                
            case 'estoque':
                $data['produtos'] = $this->produtoModel->getRelatorioPosicao();
                $data['custoTotalEstoque'] = $this->produtoModel->getCustoTotalEstoque();
                break;
                
            case 'tecnicos':
                $data['desempenho'] = $this->usuarioModel->getDesempenhoTecnicos($dataInicio, $dataFim);
                break;
                
            case 'despesas':
                $despesas = $this->despesaModel->getRelatorioDetalhado([
                    'data_inicio' => $dataInicio,
                    'data_fim' => $dataFim
                ]);
                $data['despesas'] = $despesas;
                $data['totalDespesas'] = array_sum(array_column($despesas, 'valor'));
                break;
        }
        
        $this->view('relatorios/imprimir', $data);
    }

    /**
     * Evolução mensal para gráficos
     */
    private function getEvolucaoMensal(int $meses = 6): array
    {
        $dados = [];
        
        for ($i = $meses - 1; $i >= 0; $i--) {
            $mes = date('Y-m', strtotime("-{$i} months"));
            $inicio = $mes . '-01';
            $fim = date('Y-m-t', strtotime($inicio));
            
            $receitas = $this->receitaModel->getTotalPorPeriodo($inicio, $fim, 'recebido');
            $despesas = $this->despesaModel->getTotalPorPeriodo($inicio, $fim);
            
            $dados[] = [
                'mes' => $mes,
                'receitas' => $receitas,
                'despesas' => $despesas,
                'lucro' => $receitas - $despesas
            ];
        }
        
        return $dados;
    }

    private function exportarServicosCSV($output, $dataInicio, $dataFim): void
    {
        // Header
        fputcsv($output, ['Nº OS', 'Data', 'Cliente', 'Serviço', 'Técnico', 'Status', 'Valor', 'Custo', 'Lucro']);
        
        $ordens = $this->osModel->getRelatorioServicos([
            'data_inicio' => $dataInicio,
            'data_fim' => $dataFim
        ]);
        
        foreach ($ordens as $os) {
            fputcsv($output, [
                $os['numero_os'],
                date('d/m/Y', strtotime($os['data_entrada'])),
                $os['cliente_nome'],
                $os['servico_nome'],
                $os['tecnico_nome'] ?? 'Não atribuído',
                $os['status'],
                number_format($os['valor_total'], 2, ',', '.'),
                number_format($os['custo_produtos'], 2, ',', '.'),
                number_format($os['lucro_real'], 2, ',', '.')
            ]);
        }
    }

    private function exportarFinanceiroCSV($output, $dataInicio, $dataFim): void
    {
        fputcsv($output, ['Tipo', 'Data', 'Descrição', 'Categoria', 'Valor', 'Status']);
        
        // Receitas
        $receitas = $this->receitaModel->getPorPeriodo($dataInicio, $dataFim);
        foreach ($receitas as $r) {
            fputcsv($output, [
                'Receita',
                date('d/m/Y', strtotime($r['data_recebimento'])),
                $r['descricao'],
                $r['forma_pagamento'],
                number_format($r['valor'], 2, ',', '.'),
                $r['status']
            ]);
        }
        
        // Despesas
        $despesas = $this->despesaModel->getPorPeriodo($dataInicio, $dataFim);
        foreach ($despesas as $d) {
            fputcsv($output, [
                'Despesa',
                date('d/m/Y', strtotime($d['data_despesa'])),
                $d['descricao'],
                $d['categoria'],
                number_format($d['valor'], 2, ',', '.'),
                $d['status']
            ]);
        }
    }

    private function exportarEstoqueCSV($output): void
    {
        fputcsv($output, ['Produto', 'Código', 'Quantidade', 'Mínima', 'Status', 'Custo Unit.', 'Custo Total']);
        
        $produtos = $this->produtoModel->getRelatorioPosicao();
        foreach ($produtos as $p) {
            fputcsv($output, [
                $p['nome'],
                $p['codigo_sku'],
                $p['quantidade_estoque'],
                $p['quantidade_minima'],
                $p['quantidade_estoque'] <= 0 ? 'Zerado' : ($p['quantidade_estoque'] <= $p['quantidade_minima'] ? 'Baixo' : 'OK'),
                number_format($p['custo_unitario'], 2, ',', '.'),
                number_format($p['custo_total'], 2, ',', '.')
            ]);
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
}
