<?php
/**
 * proService - DashboardController
 * Arquivo: /app/controllers/DashboardController.php
 */

namespace App\Controllers;

use App\Models\Empresa;
use App\Models\OrdemServico;
use App\Models\Cliente;
use App\Models\Produto;
use App\Models\Receita;
use App\Models\Despesa;
use App\Models\Servico;

class DashboardController extends Controller
{
    private Empresa $empresaModel;
    private OrdemServico $osModel;
    private Cliente $clienteModel;
    private Produto $produtoModel;
    private Receita $receitaModel;
    private Despesa $despesaModel;
    private Servico $servicoModel;

    public function __construct()
    {
        $this->empresaModel = new Empresa();
        $this->osModel = new OrdemServico();
        $this->clienteModel = new Cliente();
        $this->produtoModel = new Produto();
        $this->receitaModel = new Receita();
        $this->despesaModel = new Despesa();
        $this->servicoModel = new Servico();
    }

    /**
     * Página principal do dashboard
     */
    public function index(): void
    {
        $empresaId = getEmpresaId();
        
        // Estatísticas da empresa
        $estatisticasEmpresa = $this->empresaModel->getEstatisticas($empresaId);
        
        // Estatísticas de OS
        $estatisticasOS = $this->osModel->getEstatisticas();
        
        // Receitas do mês
        $receitasMes = $this->receitaModel->getReceitasMes();
        
        // Despesas do mês
        $despesasMes = $this->despesaModel->getDespesasMes();
        
        // Lucro
        $lucro = ($receitasMes['total'] ?? 0) - ($despesasMes['total'] ?? 0);
        
        // Total pendente a receber
        $totalPendente = $this->receitaModel->getTotalPendente();
        
        // OS urgentes
        $osUrgentes = $this->osModel->getUrgentes(5);
        
        // OS atrasadas
        $osAtrasadas = $this->osModel->getAtrasadas();
        
        // Produtos em falta
        $produtosEmFalta = $this->produtoModel->listarEmFalta();
        
        // Verificar limite de OS
        $limiteOS = verificarLimiteOS();
        
        // Verificar trial
        $empresa = getEmpresaDados();
        $diasTrial = 0;
        if ($empresa && $empresa['plano'] === 'trial') {
            $diasTrial = max(0, (strtotime($empresa['data_fim_trial']) - time()) / 86400);
        }
        
        // Verificar se onboarding deve ser mostrado
        $mostrarOnboarding = $this->verificarOnboarding();
        $progressoOnboarding = $mostrarOnboarding ? $this->getProgressoOnboarding() : null;
        
        $this->layout('main', [
            'titulo' => 'Dashboard',
            'content' => $this->renderView('dashboard/index', [
                'estatisticas' => $estatisticasEmpresa,
                'estatisticasOS' => $estatisticasOS,
                'receitasMes' => $receitasMes,
                'despesasMes' => $despesasMes,
                'lucro' => $lucro,
                'totalPendente' => $totalPendente,
                'osUrgentes' => $osUrgentes,
                'osAtrasadas' => $osAtrasadas,
                'produtosEmFalta' => $produtosEmFalta,
                'limiteOS' => $limiteOS,
                'diasTrial' => $diasTrial,
                'empresa' => $empresa,
                'mostrarOnboarding' => $mostrarOnboarding,
                'progressoOnboarding' => $progressoOnboarding
            ])
        ]);
    }

    /**
     * Verifica se deve mostrar o onboarding
     */
    private function verificarOnboarding(): bool
    {
        $empresaId = getEmpresaId();
        $empresa = $this->empresaModel->findById($empresaId);
        
        // Se já completou onboarding, não mostrar
        if (!empty($empresa['onboarding_completo'])) {
            return false;
        }
        
        // Se login é recente (últimos 7 dias), mostrar
        $dataCriacao = strtotime($empresa['created_at']);
        $diasDesdeCriacao = (time() - $dataCriacao) / 86400;
        
        return $diasDesdeCriacao <= 7;
    }

    /**
     * Calcula progresso do onboarding (usa max entre etapa calculada e etapa salva)
     */
    private function getProgressoOnboarding(): array
    {
        $empresaId = getEmpresaId();
        $empresa = $this->empresaModel->findById($empresaId);
        
        $logo = !empty($empresa['logo']);
        $servico = $this->servicoModel->count(['empresa_id' => $empresaId]) > 0;
        $cliente = $this->clienteModel->count(['empresa_id' => $empresaId]) > 0;
        $os = $this->osModel->count(['empresa_id' => $empresaId]) > 0;
        
        $etapaCalculada = 1;
        if ($logo) $etapaCalculada = 2;
        if ($logo && $servico) $etapaCalculada = 3;
        if ($logo && $servico && $cliente) $etapaCalculada = 4;
        if ($logo && $servico && $cliente && $os) $etapaCalculada = 5;

        $etapaSalva = (int) ($empresa['onboarding_etapa'] ?? 1);
        $etapa = max($etapaCalculada, $etapaSalva);

        if ($etapa > $etapaSalva) {
            $this->empresaModel->update($empresaId, ['onboarding_etapa' => $etapa]);
        }
        
        return [
            'logo' => $logo,
            'servico' => $servico,
            'cliente' => $cliente,
            'os' => $os,
            'etapa_atual' => $etapa,
            'completo' => $logo && $servico && $cliente && $os
        ];
    }

    /**
     * API: Busca global (Ctrl+K)
     */
    public function busca(): void
    {
        $empresaId = getEmpresaId();
        $query = sanitizeInput($_GET['q'] ?? '');
        
        if (strlen($query) < 2) {
            $this->jsonResponse(['results' => []]);
            return;
        }
        
        $results = [];
        
        // Buscar OS
        $ordens = $this->osModel->buscarGlobal($query, $empresaId);
        foreach ($ordens as $os) {
            $results[] = [
                'icon' => 'bi-clipboard-data',
                'title' => 'OS #' . str_pad($os['numero_os'], 4, '0', STR_PAD_LEFT),
                'subtitle' => $os['cliente_nome'] . ' - ' . ($os['servico_nome'] ?? 'Serviço'),
                'url' => url('ordens/show/' . $os['id']),
                'type' => 'os'
            ];
        }
        
        // Buscar Clientes
        $clientes = $this->clienteModel->buscarGlobal($query, $empresaId);
        foreach ($clientes as $cliente) {
            $results[] = [
                'icon' => 'bi-person',
                'title' => $cliente['nome'],
                'subtitle' => 'Telefone: ' . ($cliente['telefone'] ?? 'N/A'),
                'url' => url('clientes/show/' . $cliente['id']),
                'type' => 'cliente'
            ];
        }
        
        // Buscar Produtos
        $produtos = $this->produtoModel->buscarGlobal($query, $empresaId);
        foreach ($produtos as $produto) {
            $results[] = [
                'icon' => 'bi-box-seam',
                'title' => $produto['nome'],
                'subtitle' => 'Estoque: ' . $produto['quantidade_estoque'] . ' unidades',
                'url' => url('produtos/show/' . $produto['id']),
                'type' => 'produto'
            ];
        }
        
        // Limitar resultados
        $results = array_slice($results, 0, 20);
        
        $this->jsonResponse(['results' => $results]);
    }

    /**
     * API: Pular etapa do onboarding (persiste etapa_atual na empresa)
     */
    public function onboardingPular(): void
    {
        $empresaId = getEmpresaId();
        $progresso = $this->getProgressoOnboarding();
        $etapaNova = min($progresso['etapa_atual'] + 1, 5);

        $this->empresaModel->update($empresaId, ['onboarding_etapa' => $etapaNova]);
        
        $this->jsonResponse(['success' => true, 'etapa' => $etapaNova]);
    }

    /**
     * API: Finalizar onboarding
     */
    public function onboardingFinalizar(): void
    {
        $empresaId = getEmpresaId();
        
        // Marca onboarding como completo na empresa
        $this->empresaModel->update($empresaId, ['onboarding_completo' => 1]);
        
        $this->jsonResponse(['success' => true]);
    }

    /**
     * Renderiza uma view e retorna o conteúdo
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
