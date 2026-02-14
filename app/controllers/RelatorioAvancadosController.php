<?php
/**
 * proService - RelatoriosAvancadosController
 * Relatórios analíticos avançados com dashboards e KPIs
 */

namespace App\Controllers;

use App\Models\Cliente;
use App\Models\OrdemServico;
use App\Models\Receita;
use App\Models\Despesa;
use App\Models\Servico;

class RelatorioAvancadosController extends Controller
{
    private Cliente $clienteModel;
    private OrdemServico $osModel;
    private Receita $receitaModel;
    private Despesa $despesaModel;
    private Servico $servicoModel;

    public function __construct()
    {
        $this->clienteModel = new Cliente();
        $this->osModel = new OrdemServico();
        $this->receitaModel = new Receita();
        $this->despesaModel = new Despesa();
        $this->servicoModel = new Servico();
    }

    /**
     * Dashboard de relatórios avançados
     */
    public function index(): void
    {
        $empresaId = getEmpresaId();
        
        // Período padrão: últimos 6 meses
        $dataFim = date('Y-m-d');
        $dataInicio = date('Y-m-d', strtotime('-6 months'));
        
        // KPIs
        $kpis = $this->calcularKPIs($empresaId);
        
        // Dados financeiros para gráficos
        $dadosFinanceiros = $this->getDadosFinanceirosMensais($empresaId, $dataInicio, $dataFim);
        
        // Análise de clientes
        $analiseClientes = $this->getAnaliseClientes($empresaId);
        
        // Comparativos
        $comparativos = $this->getComparativosMensais($empresaId);
        
        // Dados para gráficos
        $tendenciaOS = $this->getTendenciaOS($empresaId, $dataInicio, $dataFim);
        
        // Serviços mais executados (atividades)
        $servicosMaisExecutados = $this->getServicosMaisExecutados($empresaId);
        
        $this->layout('main', [
            'titulo' => 'Relatórios Avançados',
            'content' => $this->render('relatorios/avancados', [
                'kpis' => $kpis,
                'dadosFinanceiros' => $dadosFinanceiros,
                'analiseClientes' => $analiseClientes,
                'comparativos' => $comparativos,
                'tendenciaOS' => $tendenciaOS,
                'servicosMaisExecutados' => $servicosMaisExecutados,
                'dataInicio' => $dataInicio,
                'dataFim' => $dataFim
            ])
        ]);
    }

    /**
     * Calcula KPIs principais
     */
    private function calcularKPIs(int $empresaId): array
    {
        $db = \App\Config\Database::getInstance();
        
        // OS no prazo (últimos 30 dias)
        $stmt = $db->prepare("
            SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN status IN ('finalizada', 'paga') 
                         AND (data_finalizacao <= previsao_entrega OR previsao_entrega IS NULL) 
                         THEN 1 ELSE 0 END) as no_prazo
            FROM ordens_servico 
            WHERE empresa_id = ? 
            AND data_entrada >= DATE_SUB(NOW(), INTERVAL 30 DAY)
        ");
        $stmt->execute([$empresaId]);
        $result = $stmt->fetch();
        $osNoPrazo = $result['total'] > 0 ? round(($result['no_prazo'] / $result['total']) * 100, 1) : 0;
        
        // Taxa de conversão (orçamentos aprovados nos últimos 90 dias)
        $stmt = $db->prepare("
            SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN status IN ('aprovada', 'em_execucao', 'finalizada', 'paga') THEN 1 ELSE 0 END) as aprovados
            FROM ordens_servico 
            WHERE empresa_id = ? 
            AND status IN ('aberta', 'em_orcamento', 'aprovada', 'em_execucao', 'finalizada', 'paga')
            AND data_entrada >= DATE_SUB(NOW(), INTERVAL 90 DAY)
        ");
        $stmt->execute([$empresaId]);
        $result = $stmt->fetch();
        $taxaConversao = $result['total'] > 0 ? round(($result['aprovados'] / $result['total']) * 100, 1) : 0;
        
        // Ticket médio (últimos 30 dias)
        $stmt = $db->prepare("
            SELECT AVG(valor_total) as ticket_medio
            FROM ordens_servico 
            WHERE empresa_id = ? 
            AND status IN ('finalizada', 'paga')
            AND data_entrada >= DATE_SUB(NOW(), INTERVAL 30 DAY)
        ");
        $stmt->execute([$empresaId]);
        $ticketMedio = (float) ($stmt->fetch()['ticket_medio'] ?? 0);
        
        // Satisfação estimada (baseada em OS finalizadas sem reabertura)
        $stmt = $db->prepare("
            SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN status = 'paga' THEN 1 ELSE 0 END) as pagas
            FROM ordens_servico 
            WHERE empresa_id = ? 
            AND status IN ('finalizada', 'paga')
            AND data_entrada >= DATE_SUB(NOW(), INTERVAL 60 DAY)
        ");
        $stmt->execute([$empresaId]);
        $result = $stmt->fetch();
        $satisfacao = $result['total'] > 0 ? round(($result['pagas'] / $result['total']) * 100, 1) : 0;
        
        // Tempo médio de execução
        $stmt = $db->prepare("
            SELECT AVG(DATEDIFF(data_finalizacao, data_entrada)) as dias_media
            FROM ordens_servico 
            WHERE empresa_id = ? 
            AND status IN ('finalizada', 'paga')
            AND data_finalizacao IS NOT NULL
            AND data_entrada >= DATE_SUB(NOW(), INTERVAL 90 DAY)
        ");
        $stmt->execute([$empresaId]);
        $tempoMedio = (float) ($stmt->fetch()['dias_media'] ?? 0);
        
        // Clientes ativos (com OS nos últimos 90 dias)
        $stmt = $db->prepare("
            SELECT COUNT(DISTINCT cliente_id) as ativos
            FROM ordens_servico 
            WHERE empresa_id = ? 
            AND data_entrada >= DATE_SUB(NOW(), INTERVAL 90 DAY)
        ");
        $stmt->execute([$empresaId]);
        $clientesAtivos = (int) ($stmt->fetch()['ativos'] ?? 0);
        
        return [
            'os_no_prazo' => $osNoPrazo,
            'taxa_conversao' => $taxaConversao,
            'ticket_medio' => $ticketMedio,
            'satisfacao_estimada' => $satisfacao,
            'tempo_medio_execucao' => $tempoMedio,
            'clientes_ativos' => $clientesAtivos
        ];
    }

    /**
     * Dados financeiros mensais para gráficos
     */
    private function getDadosFinanceirosMensais(int $empresaId, string $dataInicio, string $dataFim): array
    {
        $db = \App\Config\Database::getInstance();
        
        $meses = [];
        $receitas = [];
        $despesas = [];
        $lucros = [];
        
        for ($i = 5; $i >= 0; $i--) {
            $mes = date('Y-m', strtotime("-{$i} months"));
            $meses[] = date('M/Y', strtotime("-{$i} months"));
            
            // Receitas do mês
            $stmt = $db->prepare("
                SELECT COALESCE(SUM(valor), 0) as total
                FROM receitas 
                WHERE empresa_id = ? 
                AND status = 'recebido'
                AND DATE_FORMAT(data_recebimento, '%Y-%m') = ?
            ");
            $stmt->execute([$empresaId, $mes]);
            $receita = (float) $stmt->fetch()['total'];
            $receitas[] = $receita;
            
            // Despesas do mês
            $stmt = $db->prepare("
                SELECT COALESCE(SUM(valor), 0) as total
                FROM despesas 
                WHERE empresa_id = ? 
                AND status = 'pago'
                AND DATE_FORMAT(data_despesa, '%Y-%m') = ?
            ");
            $stmt->execute([$empresaId, $mes]);
            $despesa = (float) $stmt->fetch()['total'];
            $despesas[] = $despesa;
            
            $lucros[] = $receita - $despesa;
        }
        
        return [
            'meses' => $meses,
            'receitas' => $receitas,
            'despesas' => $despesas,
            'lucros' => $lucros
        ];
    }

    /**
     * Análise de clientes - versão simplificada sem subqueries
     */
    private function getAnaliseClientes(int $empresaId): array
    {
        $db = \App\Config\Database::getInstance();
        
        // Top 10 clientes
        $stmt = $db->prepare("
            SELECT c.id, c.nome, COUNT(os.id) as total_os,
                SUM(os.valor_total) as valor_total,
                AVG(os.valor_total) as ticket_medio,
                DATEDIFF(NOW(), MAX(os.data_entrada)) as dias_ultima_os
            FROM clientes c
            INNER JOIN ordens_servico os ON c.id = os.cliente_id
            WHERE c.empresa_id = ?
            AND os.data_entrada >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
            GROUP BY c.id
            ORDER BY SUM(os.valor_total) DESC
            LIMIT 10
        ");
        $stmt->execute([$empresaId]);
        $topClientes = $stmt->fetchAll() ?: [];
        
        // Estatísticas simples - uma linha por cliente
        $stmt = $db->prepare("
            SELECT cliente_id, COUNT(*) as total, 
                MIN(data_entrada) as primeira, 
                MAX(data_entrada) as ultima
            FROM ordens_servico
            WHERE empresa_id = ?
            AND data_entrada >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
            GROUP BY cliente_id
        ");
        $stmt->execute([$empresaId]);
        $rows = $stmt->fetchAll();
        
        $recorrentes = 0; $novos = 0; $inativos = 0;
        $limiteNovo = date('Y-m-d', strtotime('-90 days'));
        $limiteInativo = date('Y-m-d', strtotime('-6 months'));
        
        foreach ($rows as $r) {
            if ($r['total'] >= 3) $recorrentes++;
            if ($r['primeira'] >= $limiteNovo) $novos++;
            if ($r['ultima'] < $limiteInativo) $inativos++;
        }
        
        return [
            'top_clientes' => $topClientes,
            'recorrentes' => $recorrentes,
            'novos' => $novos,
            'inativos' => $inativos
        ];
    }

    /**
     * Comparativos mensais (mês atual vs anterior)
     */
    private function getComparativosMensais(int $empresaId): array
    {
        $db = \App\Config\Database::getInstance();
        
        $mesAtual = date('Y-m');
        $mesAnterior = date('Y-m', strtotime('-1 month'));
        
        // Receitas
        $stmt = $db->prepare("
            SELECT 
                DATE_FORMAT(data_recebimento, '%Y-%m') as mes,
                COALESCE(SUM(valor), 0) as total
            FROM receitas 
            WHERE empresa_id = ? 
            AND status = 'recebido'
            AND DATE_FORMAT(data_recebimento, '%Y-%m') IN (?, ?)
            GROUP BY mes
        ");
        $stmt->execute([$empresaId, $mesAtual, $mesAnterior]);
        $receitasMes = $stmt->fetchAll(\PDO::FETCH_KEY_PAIR);
        
        // Despesas
        $stmt = $db->prepare("
            SELECT 
                DATE_FORMAT(data_despesa, '%Y-%m') as mes,
                COALESCE(SUM(valor), 0) as total
            FROM despesas 
            WHERE empresa_id = ? 
            AND status = 'pago'
            AND DATE_FORMAT(data_despesa, '%Y-%m') IN (?, ?)
            GROUP BY mes
        ");
        $stmt->execute([$empresaId, $mesAtual, $mesAnterior]);
        $despesasMes = $stmt->fetchAll(\PDO::FETCH_KEY_PAIR);
        
        // Quantidade de OS
        $stmt = $db->prepare("
            SELECT 
                DATE_FORMAT(data_entrada, '%Y-%m') as mes,
                COUNT(*) as total
            FROM ordens_servico 
            WHERE empresa_id = ? 
            AND DATE_FORMAT(data_entrada, '%Y-%m') IN (?, ?)
            GROUP BY mes
        ");
        $stmt->execute([$empresaId, $mesAtual, $mesAnterior]);
        $osMes = $stmt->fetchAll(\PDO::FETCH_KEY_PAIR);
        
        // Ticket médio
        $stmt = $db->prepare("
            SELECT 
                DATE_FORMAT(data_entrada, '%Y-%m') as mes,
                AVG(valor_total) as ticket
            FROM ordens_servico 
            WHERE empresa_id = ? 
            AND status IN ('finalizada', 'paga')
            AND DATE_FORMAT(data_entrada, '%Y-%m') IN (?, ?)
            GROUP BY mes
        ");
        $stmt->execute([$empresaId, $mesAtual, $mesAnterior]);
        $ticketMes = $stmt->fetchAll(\PDO::FETCH_KEY_PAIR);
        
        return [
            'receita_atual' => $receitasMes[$mesAtual] ?? 0,
            'receita_anterior' => $receitasMes[$mesAnterior] ?? 0,
            'despesa_atual' => $despesasMes[$mesAtual] ?? 0,
            'despesa_anterior' => $despesasMes[$mesAnterior] ?? 0,
            'os_atual' => $osMes[$mesAtual] ?? 0,
            'os_anterior' => $osMes[$mesAnterior] ?? 0,
            'ticket_atual' => $ticketMes[$mesAtual] ?? 0,
            'ticket_anterior' => $ticketMes[$mesAnterior] ?? 0
        ];
    }

    /**
     * Tendência de OS criadas/finalizadas
     */
    private function getTendenciaOS(int $empresaId, string $dataInicio, string $dataFim): array
    {
        $db = \App\Config\Database::getInstance();
        
        $meses = [];
        $criadas = [];
        $finalizadas = [];
        
        for ($i = 5; $i >= 0; $i--) {
            $mes = date('Y-m', strtotime("-{$i} months"));
            $meses[] = date('M/Y', strtotime("-{$i} months"));
            
            // OS criadas
            $stmt = $db->prepare("
                SELECT COUNT(*) as total
                FROM ordens_servico 
                WHERE empresa_id = ? 
                AND DATE_FORMAT(data_entrada, '%Y-%m') = ?
            ");
            $stmt->execute([$empresaId, $mes]);
            $criadas[] = (int) $stmt->fetch()['total'];
            
            // OS finalizadas
            $stmt = $db->prepare("
                SELECT COUNT(*) as total
                FROM ordens_servico 
                WHERE empresa_id = ? 
                AND status IN ('finalizada', 'paga')
                AND DATE_FORMAT(data_finalizacao, '%Y-%m') = ?
            ");
            $stmt->execute([$empresaId, $mes]);
            $finalizadas[] = (int) $stmt->fetch()['total'];
        }
        
        return [
            'meses' => $meses,
            'criadas' => $criadas,
            'finalizadas' => $finalizadas
        ];
    }

    /**
     * Serviços mais executados - atividades recorrentes
     */
    private function getServicosMaisExecutados(int $empresaId): array
    {
        $db = \App\Config\Database::getInstance();
        
        // Top serviços executados (últimos 12 meses)
        $stmt = $db->prepare("
            SELECT 
                s.nome,
                COUNT(os.id) as total_execucoes,
                SUM(os.valor_servico) as valor_total,
                AVG(os.valor_servico) as valor_medio
            FROM servicos s
            INNER JOIN ordens_servico os ON s.id = os.servico_id
            WHERE s.empresa_id = ?
            AND os.data_entrada >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
            AND os.status IN ('finalizada', 'paga', 'aprovada', 'em_execucao')
            GROUP BY s.id, s.nome
            ORDER BY COUNT(os.id) DESC
            LIMIT 10
        ");
        $stmt->execute([$empresaId]);
        $servicos = $stmt->fetchAll() ?: [];
        
        // Preparar dados para gráfico (top 5 + outros)
        $labels = [];
        $valores = [];
        $cores = ['#0d6efd', '#198754', '#dc3545', '#ffc107', '#6f42c1', '#20c997', '#fd7e14', '#6c757d'];
        
        $totalTop5 = 0;
        for ($i = 0; $i < min(5, count($servicos)); $i++) {
            $labels[] = $servicos[$i]['nome'];
            $valores[] = (int) $servicos[$i]['total_execucoes'];
            $totalTop5 += $servicos[$i]['total_execucoes'];
        }
        
        // Se houver mais de 5, agrupar como "Outros"
        if (count($servicos) > 5) {
            $totalOutros = 0;
            for ($i = 5; $i < count($servicos); $i++) {
                $totalOutros += $servicos[$i]['total_execucoes'];
            }
            $labels[] = 'Outros';
            $valores[] = $totalOutros;
        }
        
        return [
            'lista' => $servicos,
            'grafico' => [
                'labels' => $labels,
                'valores' => $valores,
                'cores' => array_slice($cores, 0, count($labels))
            ]
        ];
    }
}
