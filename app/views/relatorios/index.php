<?php
/**
 * proService - Dashboard de Relatórios
 * Arquivo: /app/views/relatorios/index.php
 */
?>

<?= breadcrumb(['Dashboard' => 'dashboard', 'Relatórios']) ?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-0"><i class="bi bi-graph-up text-primary"></i> Relatórios</h2>
            <p class="text-muted mb-0">Análise e insights do seu negócio</p>
        </div>
    </div>

    <!-- Cards de Resumo -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card h-100 border-start border-primary border-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h6 class="text-muted mb-1">OS este Mês</h6>
                            <h3 class="mb-0"><?= number_format($totalOSMes) ?></h3>
                        </div>
                        <div class="bg-primary bg-opacity-10 p-2 rounded">
                            <i class="bi bi-clipboard-data text-primary fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card h-100 border-start border-success border-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h6 class="text-muted mb-1">Receitas</h6>
                            <h3 class="mb-0">R$ <?= number_format($totalReceitasMes, 2, ',', '.') ?></h3>
                        </div>
                        <div class="bg-success bg-opacity-10 p-2 rounded">
                            <i class="bi bi-arrow-up-circle text-success fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card h-100 border-start border-danger border-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h6 class="text-muted mb-1">Despesas</h6>
                            <h3 class="mb-0">R$ <?= number_format($totalDespesasMes, 2, ',', '.') ?></h3>
                        </div>
                        <div class="bg-danger bg-opacity-10 p-2 rounded">
                            <i class="bi bi-arrow-down-circle text-danger fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card h-100 border-start border-info border-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h6 class="text-muted mb-1">Lucro</h6>
                            <h3 class="mb-0">R$ <?= number_format($lucroMes, 2, ',', '.') ?></h3>
                        </div>
                        <div class="bg-info bg-opacity-10 p-2 rounded">
                            <i class="bi bi-cash-coin text-info fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Menu de Relatórios -->
    <div class="row g-4">
        <div class="col-md-4">
            <a href="<?= url('relatorios/servicos') ?>" class="text-decoration-none">
                <div class="card h-100 hover-shadow">
                    <div class="card-body text-center p-4">
                        <div class="bg-primary bg-opacity-10 p-3 rounded-circle d-inline-flex mb-3">
                            <i class="bi bi-clipboard-check text-primary fs-1"></i>
                        </div>
                        <h5 class="card-title">Relatório de Serviços</h5>
                        <p class="card-text text-muted">OS por período, técnico, cliente e status. Análise de margem de lucro.</p>
                        <span class="btn btn-outline-primary btn-sm">Acessar</span>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-4">
            <a href="<?= url('relatorios/financeiro') ?>" class="text-decoration-none">
                <div class="card h-100 hover-shadow">
                    <div class="card-body text-center p-4">
                        <div class="bg-success bg-opacity-10 p-3 rounded-circle d-inline-flex mb-3">
                            <i class="bi bi-cash-stack text-success fs-1"></i>
                        </div>
                        <h5 class="card-title">Relatório Financeiro</h5>
                        <p class="card-text text-muted">Receitas, despesas, fluxo de caixa e evolução mensal.</p>
                        <span class="btn btn-outline-success btn-sm">Acessar</span>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-4">
            <a href="<?= url('relatorios/estoque') ?>" class="text-decoration-none">
                <div class="card h-100 hover-shadow">
                    <div class="card-body text-center p-4">
                        <div class="bg-warning bg-opacity-10 p-3 rounded-circle d-inline-flex mb-3">
                            <i class="bi bi-box-seam text-warning fs-1"></i>
                        </div>
                        <h5 class="card-title">Relatório de Estoque</h5>
                        <p class="card-text text-muted">Posição atual, movimentações e produtos mais utilizados.</p>
                        <span class="btn btn-outline-warning btn-sm">Acessar</span>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-4">
            <a href="<?= url('relatorios/tecnicos') ?>" class="text-decoration-none">
                <div class="card h-100 hover-shadow">
                    <div class="card-body text-center p-4">
                        <div class="bg-info bg-opacity-10 p-3 rounded-circle d-inline-flex mb-3">
                            <i class="bi bi-people text-info fs-1"></i>
                        </div>
                        <h5 class="card-title">Desempenho por Técnico</h5>
                        <p class="card-text text-muted">Produtividade, receita gerada e ranking de técnicos.</p>
                        <span class="btn btn-outline-info btn-sm">Acessar</span>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-4">
            <a href="<?= url('relatorios/despesas') ?>" class="text-decoration-none">
                <div class="card h-100 hover-shadow">
                    <div class="card-body text-center p-4">
                        <div class="bg-danger bg-opacity-10 p-3 rounded-circle d-inline-flex mb-3">
                            <i class="bi bi-graph-down-arrow text-danger fs-1"></i>
                        </div>
                        <h5 class="card-title">Relatório de Despesas</h5>
                        <p class="card-text text-muted">Análise por categoria, evolução mensal e despesas recorrentes.</p>
                        <span class="btn btn-outline-danger btn-sm">Acessar</span>
                    </div>
                </div>
            </a>
        </div>
    </div>

    <!-- Top Serviços -->
    <?php if (!empty($topServicos)): ?>
    <div class="card mt-4">
        <div class="card-header">
            <h5 class="mb-0"><i class="bi bi-trophy text-warning"></i> Top 5 Serviços mais Realizados</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Serviço</th>
                            <th>Categoria</th>
                            <th class="text-center">Quantidade</th>
                            <th class="text-end">Receita Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($topServicos as $servico): ?>
                        <tr>
                            <td><?= e($servico['servico'] ?? 'N/A') ?></td>
                            <td><?= e($servico['categoria'] ?? '-') ?></td>
                            <td class="text-center">
                                <span class="badge bg-primary"><?= $servico['total'] ?></span>
                            </td>
                            <td class="text-end">R$ <?= number_format($servico['valor_total'] ?? 0, 2, ',', '.') ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- OS por Status -->
    <div class="card mt-4">
        <div class="card-header">
            <h5 class="mb-0"><i class="bi bi-pie-chart text-primary"></i> Ordens de Serviço por Status</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <?php 
                $statusColors = [
                    'aberta' => 'secondary',
                    'em_orcamento' => 'info',
                    'aprovada' => 'warning',
                    'em_execucao' => 'primary',
                    'pausada' => 'dark',
                    'finalizada' => 'success',
                    'paga' => 'success',
                    'cancelada' => 'danger'
                ];
                foreach ($osPorStatus as $status => $dados): 
                    $color = $statusColors[$status] ?? 'secondary';
                    $total = $dados['total'] ?? 0;
                ?>
                <div class="col-md-3 mb-3">
                    <div class="d-flex align-items-center p-3 border rounded">
                        <span class="badge bg-<?= $color ?> me-3"><?= $total ?></span>
                        <span class="text-capitalize"><?= str_replace('_', ' ', $status) ?></span>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<style>
.hover-shadow:hover {
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
    transition: box-shadow 0.3s ease;
}
</style>
