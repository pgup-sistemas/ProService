<?php
/**
 * proService - Relatório Financeiro
 * Arquivo: /app/views/relatorios/financeiro.php
 */
?>

<?= breadcrumb(['Dashboard' => 'dashboard', 'Relatórios' => 'relatorios', 'Financeiro']) ?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-0"><i class="bi bi-cash-stack text-success"></i> Relatório Financeiro</h2>
            <p class="text-muted mb-0">Análise financeira completa</p>
        </div>
        <div class="d-flex gap-2">
            <a href="<?= url('relatorios/exportar?tipo=financeiro&data_inicio=' . $filtros['data_inicio'] . '&data_fim=' . $filtros['data_fim']) ?>" class="btn btn-success">
                <i class="bi bi-download"></i> Exportar CSV
            </a>
            <a href="<?= url('relatorios/imprimir?tipo=financeiro') ?>" class="btn btn-outline-secondary" target="_blank">
                <i class="bi bi-printer"></i> Imprimir
            </a>
        </div>
    </div>

    <!-- Filtros -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="<?= url('relatorios/financeiro') ?>">
                <div class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label">Período Início</label>
                        <input type="date" name="data_inicio" class="form-control" value="<?= $filtros['data_inicio'] ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Período Fim</label>
                        <input type="date" name="data_fim" class="form-control" value="<?= $filtros['data_fim'] ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Tipo</label>
                        <select name="tipo" class="form-select">
                            <option value="geral" <?= $filtros['tipo'] == 'geral' ? 'selected' : '' ?>>Geral</option>
                            <option value="receitas" <?= $filtros['tipo'] == 'receitas' ? 'selected' : '' ?>>Apenas Receitas</option>
                            <option value="despesas" <?= $filtros['tipo'] == 'despesas' ? 'selected' : '' ?>>Apenas Despesas</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-funnel"></i> Filtrar
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Cards Resumo -->
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card border-start border-success border-4">
                <div class="card-body">
                    <h6 class="text-muted">Total Receitas</h6>
                    <h3 class="text-success mb-0">R$ <?= number_format($totalReceitas, 2, ',', '.') ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-start border-danger border-4">
                <div class="card-body">
                    <h6 class="text-muted">Total Despesas</h6>
                    <h3 class="text-danger mb-0">R$ <?= number_format($totalDespesas, 2, ',', '.') ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-start border-info border-4">
                <div class="card-body">
                    <h6 class="text-muted">Lucro Líquido</h6>
                    <h3 class="<?= $lucro >= 0 ? 'text-success' : 'text-danger' ?> mb-0">
                        R$ <?= number_format($lucro, 2, ',', '.') ?>
                    </h3>
                    <?php if ($totalReceitas > 0): ?>
                    <small class="text-muted">
                        <?= number_format(($lucro / $totalReceitas) * 100, 1) ?>% margem
                    </small>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Gráfico Evolução Mensal -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0"><i class="bi bi-graph-up"></i> Evolução Mensal (Últimos 6 meses)</h5>
        </div>
        <div class="card-body">
            <canvas id="evolucaoChart" height="100"></canvas>
        </div>
    </div>

    <div class="row">
        <!-- Receitas por Forma de Pagamento -->
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-credit-card text-success"></i> Receitas por Forma de Pagamento</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Forma</th>
                                    <th class="text-end">Valor</th>
                                    <th class="text-end">%</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $totalFormas = array_sum(array_column($receitasPorForma, 'total'));
                                foreach ($receitasPorForma as $forma): 
                                    $percentual = $totalFormas > 0 ? ($forma['total'] / $totalFormas) * 100 : 0;
                                    $formaLabel = $forma['forma_pagamento'] === 'nao_informado' ? 'Não informado' : ucfirst(str_replace('_', ' ', $forma['forma_pagamento'] ?? '-'));
                                ?>
                                <tr>
                                    <td>
                                        <i class="bi bi-circle-fill text-success me-2" style="font-size: 8px;"></i>
                                        <?= $formaLabel ?>
                                    </td>
                                    <td class="text-end">R$ <?= number_format($forma['total'], 2, ',', '.') ?></td>
                                    <td class="text-end"><?= number_format($percentual, 1) ?>%</td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Despesas por Categoria -->
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-pie-chart text-danger"></i> Despesas por Categoria</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Categoria</th>
                                    <th class="text-end">Valor</th>
                                    <th class="text-end">%</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $totalCategorias = array_sum(array_column($despesasPorCategoria, 'total'));
                                foreach ($despesasPorCategoria as $cat): 
                                    $percentual = $totalCategorias > 0 ? ($cat['total'] / $totalCategorias) * 100 : 0;
                                ?>
                                <tr>
                                    <td>
                                        <i class="bi bi-circle-fill text-danger me-2" style="font-size: 8px;"></i>
                                        <?= ucfirst($cat['categoria']) ?>
                                    </td>
                                    <td class="text-end">R$ <?= number_format($cat['total'], 2, ',', '.') ?></td>
                                    <td class="text-end"><?= number_format($percentual, 1) ?>%</td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Inadimplência -->
    <?php if (!empty($inadimplencia)): ?>
    <div class="card mb-4 border-warning">
        <div class="card-header bg-warning bg-opacity-10">
            <h5 class="mb-0 text-warning"><i class="bi bi-exclamation-triangle"></i> Inadimplência (> 30 dias)</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th>OS</th>
                            <th>Cliente</th>
                            <th>Vencimento</th>
                            <th>Dias Atraso</th>
                            <th class="text-end">Valor</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $totalInadimplencia = 0;
                        foreach ($inadimplencia as $item): 
                            $totalInadimplencia += $item['valor'];
                        ?>
                        <tr>
                            <td>#<?= str_pad($item['numero_os'], 5, '0', STR_PAD_LEFT) ?></td>
                            <td><?= e($item['cliente_nome']) ?></td>
                            <td><?= date('d/m/Y', strtotime($item['data_vencimento'])) ?></td>
                            <td>
                                <span class="badge bg-danger">
                                    <?= $item['dias_atraso'] ?> dias
                                </span>
                            </td>
                            <td class="text-end">R$ <?= number_format($item['valor'], 2, ',', '.') ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr class="fw-bold">
                            <td colspan="4" class="text-end">Total Inadimplente:</td>
                            <td class="text-end text-danger">R$ <?= number_format($totalInadimplencia, 2, ',', '.') ?></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const ctx = document.getElementById('evolucaoChart').getContext('2d');
const evolucaoData = <?= json_encode($evolucaoMensal) ?>;

new Chart(ctx, {
    type: 'line',
    data: {
        labels: evolucaoData.map(d => {
            const [ano, mes] = d.mes.split('-');
            return `${mes}/${ano}`;
        }),
        datasets: [
            {
                label: 'Receitas',
                data: evolucaoData.map(d => d.receitas),
                borderColor: '#059669',
                backgroundColor: '#05966920',
                fill: true,
                tension: 0.4
            },
            {
                label: 'Despesas',
                data: evolucaoData.map(d => d.despesas),
                borderColor: '#dc2626',
                backgroundColor: '#dc262620',
                fill: true,
                tension: 0.4
            },
            {
                label: 'Lucro',
                data: evolucaoData.map(d => d.lucro),
                borderColor: '#2563eb',
                backgroundColor: '#2563eb20',
                fill: true,
                tension: 0.4
            }
        ]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'top'
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: function(value) {
                        return 'R$ ' + value.toLocaleString('pt-BR');
                    }
                }
            }
        }
    }
});
</script>
