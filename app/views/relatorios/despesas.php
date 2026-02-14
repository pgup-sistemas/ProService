<?php
/**
 * proService - Relatório de Despesas
 * Arquivo: /app/views/relatorios/despesas.php
 */
?>

<?= breadcrumb(['Dashboard' => 'dashboard', 'Relatórios' => 'relatorios', 'Despesas']) ?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-0"><i class="bi bi-graph-down-arrow text-danger"></i> Relatório de Despesas</h2>
            <p class="text-muted mb-0">Análise detalhada de despesas</p>
        </div>
        <div class="d-flex gap-2">
            <a href="<?= url('relatorios/exportar?tipo=despesas&data_inicio=' . $filtros['data_inicio'] . '&data_fim=' . $filtros['data_fim']) ?>" class="btn btn-success">
                <i class="bi bi-download"></i> Exportar CSV
            </a>
        </div>
    </div>

    <!-- Filtros -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="<?= url('relatorios/despesas') ?>">
                <div class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label">Data Início</label>
                        <input type="date" name="data_inicio" class="form-control" value="<?= $filtros['data_inicio'] ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Data Fim</label>
                        <input type="date" name="data_fim" class="form-control" value="<?= $filtros['data_fim'] ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Categoria</label>
                        <select name="categoria" class="form-select">
                            <option value="">Todas</option>
                            <?php foreach ($categorias as $cat): ?>
                            <option value="<?= $cat ?>" <?= $filtros['categoria'] == $cat ? 'selected' : '' ?>>
                                <?= ucfirst($cat) ?>
                            </option>
                            <?php endforeach; ?>
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

    <!-- Total -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card bg-danger text-white">
                <div class="card-body">
                    <h6 class="text-white-50">Total de Despesas</h6>
                    <h3 class="mb-0">R$ <?= number_format($totalDespesas, 2, ',', '.') ?></h3>
                    <small>No período selecionado</small>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Por Categoria -->
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-pie-chart text-danger"></i> Despesas por Categoria</h5>
                </div>
                <div class="card-body">
                    <div style="height: 250px;">
                        <canvas id="categoriaChart"></canvas>
                    </div>
                    <div class="table-responsive mt-2" style="max-height: 100px; overflow-y: auto;">
                        <table class="table table-sm mb-0">
                            <thead>
                                <tr>
                                    <th>Categoria</th>
                                    <th class="text-end">Valor</th>
                                    <th class="text-end">%</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($porCategoria as $cat): 
                                    $percentual = $totalDespesas > 0 ? ($cat['total'] / $totalDespesas) * 100 : 0;
                                ?>
                                <tr>
                                    <td><?= ucfirst($cat['categoria']) ?></td>
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

        <!-- Evolução Mensal -->
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-graph-up text-danger"></i> Evolução Mensal</h5>
                </div>
                <div class="card-body">
                    <canvas id="evolucaoChart" height="200"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Lista de Despesas -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Despesas Detalhadas</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Data</th>
                            <th>Descrição</th>
                            <th>Categoria</th>
                            <th>Forma Pag.</th>
                            <th class="text-end">Valor</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($despesas)): ?>
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">
                                <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                Nenhuma despesa encontrada para os filtros
                            </td>
                        </tr>
                        <?php else: ?>
                        <?php foreach ($despesas as $d): ?>
                        <tr>
                            <td><?= date('d/m/Y', strtotime($d['data_despesa'])) ?></td>
                            <td><?= e($d['descricao']) ?></td>
                            <td>
                                <span class="badge bg-secondary"><?= ucfirst($d['categoria']) ?></span>
                            </td>
                            <td><?= ucfirst(str_replace('_', ' ', $d['forma_pagamento'] ?? '')) ?></td>
                            <td class="text-end">R$ <?= number_format($d['valor'], 2, ',', '.') ?></td>
                            <td>
                                <span class="badge bg-<?= $d['status'] == 'pago' ? 'success' : ($d['status'] == 'pendente' ? 'warning text-dark' : 'danger') ?>">
                                    <?= ucfirst($d['status']) ?>
                                </span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                    <tfoot class="table-light">
                        <tr class="fw-bold">
                            <td colspan="4" class="text-end">TOTAL:</td>
                            <td class="text-end">R$ <?= number_format($totalDespesas, 2, ',', '.') ?></td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Gráfico por Categoria
const ctxCat = document.getElementById('categoriaChart').getContext('2d');
const categoriasData = <?= json_encode($porCategoria) ?>;

new Chart(ctxCat, {
    type: 'doughnut',
    data: {
        labels: categoriasData.map(c => c.categoria.charAt(0).toUpperCase() + c.categoria.slice(1)),
        datasets: [{
            data: categoriasData.map(c => c.total),
            backgroundColor: [
                '#dc2626', '#ea580c', '#f59e0b', '#10b981', '#3b82f6', '#6366f1', '#8b5cf6', '#ec4899'
            ]
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'bottom',
                labels: {
                    boxWidth: 10,
                    font: { size: 11 }
                }
            }
        }
    }
});

// Gráfico de Evolução
const ctxEvo = document.getElementById('evolucaoChart').getContext('2d');
const evolucaoData = <?= json_encode($evolucao) ?>;

new Chart(ctxEvo, {
    type: 'bar',
    data: {
        labels: evolucaoData.map(e => {
            const [ano, mes] = e.mes.split('-');
            return `${mes}/${ano}`;
        }),
        datasets: [{
            label: 'Despesas',
            data: evolucaoData.map(e => e.total),
            backgroundColor: '#dc2626',
            borderRadius: 4
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                display: false
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
