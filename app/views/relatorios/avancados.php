<?php
/**
 * proService - Relatórios Avançados
 * Dashboard analítico com KPIs e gráficos
 */
?>

<?= breadcrumb(['Dashboard' => 'dashboard', 'Relatórios' => 'relatorios', 'Dashboard Avançado']) ?>

<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1"><i class="bi bi-graph-up-arrow text-primary"></i> Relatórios Avançados</h2>
            <p class="text-muted mb-0">Dashboard analítico e indicadores de performance</p>
        </div>
        <div class="d-flex gap-2">
            <a href="<?= url('relatorios') ?>" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Voltar
            </a>
            <button onclick="window.print()" class="btn btn-outline-primary">
                <i class="bi bi-printer"></i> Imprimir
            </button>
        </div>
    </div>

    <!-- KPI Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-4 col-lg-2">
            <div class="card h-100 border-success">
                <div class="card-body text-center">
                    <div class="mb-2">
                        <i class="bi bi-check-circle-fill text-success fs-1"></i>
                    </div>
                    <h3 class="mb-1"><?= $kpis['os_no_prazo'] ?>%</h3>
                    <small class="text-muted">OS no Prazo</small>
                    <div class="mt-2">
                        <div class="progress" style="height: 6px;">
                            <div class="progress-bar bg-success" style="width: <?= $kpis['os_no_prazo'] ?>"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-4 col-lg-2">
            <div class="card h-100 border-primary">
                <div class="card-body text-center">
                    <div class="mb-2">
                        <i class="bi bi-currency-exchange text-primary fs-1"></i>
                    </div>
                    <h3 class="mb-1"><?= $kpis['taxa_conversao'] ?>%</h3>
                    <small class="text-muted">Taxa de Conversão</small>
                    <div class="mt-2">
                        <div class="progress" style="height: 6px;">
                            <div class="progress-bar bg-primary" style="width: <?= $kpis['taxa_conversao'] ?>"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-4 col-lg-2">
            <div class="card h-100 border-info">
                <div class="card-body text-center">
                    <div class="mb-2">
                        <i class="bi bi-receipt text-info fs-1"></i>
                    </div>
                    <h3 class="mb-1">R$ <?= number_format($kpis['ticket_medio'], 2, ',', '.') ?></h3>
                    <small class="text-muted">Ticket Médio</small>
                </div>
            </div>
        </div>
        
        <div class="col-md-4 col-lg-2">
            <div class="card h-100 border-warning">
                <div class="card-body text-center">
                    <div class="mb-2">
                        <i class="bi bi-emoji-smile text-warning fs-1"></i>
                    </div>
                    <h3 class="mb-1"><?= $kpis['satisfacao_estimada'] ?>%</h3>
                    <small class="text-muted">Satisfação Est.</small>
                    <div class="mt-2">
                        <div class="progress" style="height: 6px;">
                            <div class="progress-bar bg-warning" style="width: <?= $kpis['satisfacao_estimada'] ?>"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-4 col-lg-2">
            <div class="card h-100 border-secondary">
                <div class="card-body text-center">
                    <div class="mb-2">
                        <i class="bi bi-clock text-secondary fs-1"></i>
                    </div>
                    <h3 class="mb-1"><?= number_format($kpis['tempo_medio_execucao'], 1) ?>d</h3>
                    <small class="text-muted">Tempo Médio Exec.</small>
                </div>
            </div>
        </div>
        
        <div class="col-md-4 col-lg-2">
            <div class="card h-100 border-dark">
                <div class="card-body text-center">
                    <div class="mb-2">
                        <i class="bi bi-people text-dark fs-1"></i>
                    </div>
                    <h3 class="mb-1"><?= $kpis['clientes_ativos'] ?></h3>
                    <small class="text-muted">Clientes Ativos</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Gráficos Principais -->
    <div class="row g-4 mb-4">
        <!-- Tendência Financeira -->
        <div class="col-lg-8">
            <div class="card h-100">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="bi bi-graph-line text-primary"></i> Tendência Financeira (6 meses)</h5>
                </div>
                <div class="card-body">
                    <canvas id="financeChart" height="300"></canvas>
                </div>
            </div>
        </div>
        
        <!-- Comparativo Mensal -->
        <div class="col-lg-4">
            <div class="card h-100">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="bi bi-bar-chart text-success"></i> Mês Atual vs Anterior</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <small>Receitas</small>
                            <small class="<?= $comparativos['receita_atual'] >= $comparativos['receita_anterior'] ? 'text-success' : 'text-danger' ?>">
                                <i class="bi bi-<?= $comparativos['receita_atual'] >= $comparativos['receita_anterior'] ? 'arrow-up' : 'arrow-down' ?>"></i>
                                <?= $comparativos['receita_anterior'] > 0 ? number_format((($comparativos['receita_atual'] - $comparativos['receita_anterior']) / $comparativos['receita_anterior']) * 100, 1) : 0 ?>%
                            </small>
                        </div>
                        <div class="progress" style="height: 20px;">
                            <div class="progress-bar bg-success" style="width: 100%">Atual: R$ <?= number_format($comparativos['receita_atual'], 2, ',', '.') ?></div>
                        </div>
                        <small class="text-muted">Anterior: R$ <?= number_format($comparativos['receita_anterior'], 2, ',', '.') ?></small>
                    </div>
                    
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <small>Despesas</small>
                            <small class="<?= $comparativos['despesa_atual'] <= $comparativos['despesa_anterior'] ? 'text-success' : 'text-danger' ?>">
                                <i class="bi bi-<?= $comparativos['despesa_atual'] <= $comparativos['despesa_anterior'] ? 'arrow-down' : 'arrow-up' ?>"></i>
                                <?= $comparativos['despesa_anterior'] > 0 ? number_format((($comparativos['despesa_atual'] - $comparativos['despesa_anterior']) / $comparativos['despesa_anterior']) * 100, 1) : 0 ?>%
                            </small>
                        </div>
                        <div class="progress" style="height: 20px;">
                            <div class="progress-bar bg-danger" style="width: 100%">Atual: R$ <?= number_format($comparativos['despesa_atual'], 2, ',', '.') ?></div>
                        </div>
                        <small class="text-muted">Anterior: R$ <?= number_format($comparativos['despesa_anterior'], 2, ',', '.') ?></small>
                    </div>
                    
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <small>OS Criadas</small>
                            <small class="<?= $comparativos['os_atual'] >= $comparativos['os_anterior'] ? 'text-success' : 'text-danger' ?>">
                                <i class="bi bi-<?= $comparativos['os_atual'] >= $comparativos['os_anterior'] ? 'arrow-up' : 'arrow-down' ?>"></i>
                                <?= $comparativos['os_anterior'] > 0 ? number_format((($comparativos['os_atual'] - $comparativos['os_anterior']) / $comparativos['os_anterior']) * 100, 1) : 0 ?>%
                            </small>
                        </div>
                        <div class="progress" style="height: 20px;">
                            <div class="progress-bar bg-primary" style="width: 100%">Atual: <?= $comparativos['os_atual'] ?></div>
                        </div>
                        <small class="text-muted">Anterior: <?= $comparativos['os_anterior'] ?></small>
                    </div>
                    
                    <div class="mb-0">
                        <div class="d-flex justify-content-between mb-1">
                            <small>Ticket Médio</small>
                            <small class="<?= $comparativos['ticket_atual'] >= $comparativos['ticket_anterior'] ? 'text-success' : 'text-danger' ?>">
                                <i class="bi bi-<?= $comparativos['ticket_atual'] >= $comparativos['ticket_anterior'] ? 'arrow-up' : 'arrow-down' ?>"></i>
                                <?= $comparativos['ticket_anterior'] > 0 ? number_format((($comparativos['ticket_atual'] - $comparativos['ticket_anterior']) / $comparativos['ticket_anterior']) * 100, 1) : 0 ?>%
                            </small>
                        </div>
                        <div class="progress" style="height: 20px;">
                            <div class="progress-bar bg-info" style="width: 100%">Atual: R$ <?= number_format($comparativos['ticket_atual'], 2, ',', '.') ?></div>
                        </div>
                        <small class="text-muted">Anterior: R$ <?= number_format($comparativos['ticket_anterior'], 2, ',', '.') ?></small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Segunda Linha de Gráficos -->
    <div class="row g-4 mb-4">
        <!-- Tendência de OS -->
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="bi bi-clipboard-data text-info"></i> Tendência de OS</h5>
                </div>
                <div class="card-body">
                    <div style="height: 350px;">
                        <canvas id="osChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Status de Clientes -->
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="bi bi-people-fill text-warning"></i> Status de Clientes</h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-4">
                            <div class="p-3 bg-success bg-opacity-10 rounded">
                                <h4 class="text-success mb-1"><?= $analiseClientes['recorrentes'] ?></h4>
                                <small class="text-muted">Recorrentes<br>(3+ OS)</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="p-3 bg-primary bg-opacity-10 rounded">
                                <h4 class="text-primary mb-1"><?= $analiseClientes['novos'] ?></h4>
                                <small class="text-muted">Novos<br>(90 dias)</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="p-3 bg-secondary bg-opacity-10 rounded">
                                <h4 class="text-secondary mb-1"><?= $analiseClientes['inativos'] ?></h4>
                                <small class="text-muted">Inativos<br>(6+ meses)</small>
                            </div>
                        </div>
                    </div>
                    <div class="mt-3" style="height: 220px;">
                        <canvas id="clientesChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Top 10 Clientes -->
    <div class="card">
        <div class="card-header bg-light">
            <h5 class="mb-0"><i class="bi bi-trophy text-warning"></i> Top 10 Clientes - Últimos 12 Meses</h5>
        </div>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Cliente</th>
                        <th class="text-center">Total OS</th>
                        <th class="text-end">Valor Total</th>
                        <th class="text-end">Ticket Médio</th>
                        <th class="text-center">Última OS</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($analiseClientes['top_clientes']) && is_array($analiseClientes['top_clientes'])): ?>
                        <?php foreach ($analiseClientes['top_clientes'] as $index => $cliente): ?>
                        <tr>
                            <td>
                                <?php if ($index < 3): ?>
                                    <i class="bi bi-trophy-fill text-<?= ['warning', 'secondary', 'danger'][$index] ?> fs-5"></i>
                                <?php else: ?>
                                    <?= $index + 1 ?>
                                <?php endif; ?>
                            </td>
                            <td>
                                <strong><?= e($cliente['nome']) ?></strong>
                                <br><small class="text-muted">ID: <?= $cliente['id'] ?></small>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-primary"><?= $cliente['total_os'] ?></span>
                            </td>
                            <td class="text-end fw-bold text-success">
                                R$ <?= number_format($cliente['valor_total'], 2, ',', '.') ?>
                            </td>
                            <td class="text-end">
                                R$ <?= number_format($cliente['ticket_medio'], 2, ',', '.') ?>
                            </td>
                            <td class="text-center">
                                <?php if ($cliente['dias_ultima_os'] <= 30): ?>
                                    <span class="badge bg-success"><?= $cliente['dias_ultima_os'] ?> dias</span>
                                <?php elseif ($cliente['dias_ultima_os'] <= 90): ?>
                                    <span class="badge bg-warning"><?= $cliente['dias_ultima_os'] ?> dias</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary"><?= $cliente['dias_ultima_os'] ?> dias</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($cliente['total_os'] >= 5): ?>
                                    <span class="badge bg-success">VIP</span>
                                <?php elseif ($cliente['total_os'] >= 3): ?>
                                    <span class="badge bg-primary">Fiel</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Ocasional</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">
                                <i class="bi bi-inbox fs-1"></i>
                                <p class="mb-0">Nenhum cliente encontrado no período</p>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Atividades / Serviços Mais Executados -->
    <div class="row g-4 mb-5">
        <div class="col-lg-5">
            <div class="card h-100">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="bi bi-pie-chart-fill text-primary"></i> Atividades Mais Recorrentes</h5>
                </div>
                <div class="card-body text-center">
                    <?php if (!empty($servicosMaisExecutados['grafico']['labels'])): ?>
                        <canvas id="servicosChart" height="250"></canvas>
                    <?php else: ?>
                        <div class="alert alert-info mt-4">
                            <i class="bi bi-info-circle"></i> Nenhum serviço executado no período
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="col-lg-7">
            <div class="card h-100">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="bi bi-list-stars text-success"></i> Top Serviços Executados</h5>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Serviço</th>
                                <th class="text-center">Execuções</th>
                                <th class="text-end">Valor Total</th>
                                <th class="text-end">Valor Médio</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($servicosMaisExecutados['lista'])): ?>
                                <?php foreach ($servicosMaisExecutados['lista'] as $index => $servico): ?>
                                <tr>
                                    <td>
                                        <?php if ($index < 3): ?>
                                            <i class="bi bi-award-fill text-<?= ['warning', 'secondary', 'danger'][$index] ?> fs-5"></i>
                                        <?php else: ?>
                                            <?= $index + 1 ?>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <strong><?= e($servico['nome']) ?></strong>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-primary fs-6"><?= $servico['total_execucoes'] ?></span>
                                    </td>
                                    <td class="text-end fw-bold text-success">
                                        R$ <?= number_format($servico['valor_total'], 2, ',', '.') ?>
                                    </td>
                                    <td class="text-end text-muted">
                                        R$ <?= number_format($servico['valor_medio'], 2, ',', '.') ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">
                                        <i class="bi bi-inbox fs-1"></i>
                                        <p class="mb-0">Nenhum serviço executado no último ano</p>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Configuração global
Chart.defaults.font.family = "'Segoe UI', 'Helvetica Neue', 'Arial', sans-serif";
Chart.defaults.color = '#666';

// Gráfico Financeiro
const financeCtx = document.getElementById('financeChart').getContext('2d');
new Chart(financeCtx, {
    type: 'line',
    data: {
        labels: <?= json_encode($dadosFinanceiros['meses']) ?>,
        datasets: [
            {
                label: 'Receitas',
                data: <?= json_encode($dadosFinanceiros['receitas']) ?>,
                borderColor: '#198754',
                backgroundColor: 'rgba(25, 135, 84, 0.1)',
                fill: true,
                tension: 0.4
            },
            {
                label: 'Despesas',
                data: <?= json_encode($dadosFinanceiros['despesas']) ?>,
                borderColor: '#dc3545',
                backgroundColor: 'rgba(220, 53, 69, 0.1)',
                fill: true,
                tension: 0.4
            },
            {
                label: 'Lucro',
                data: <?= json_encode($dadosFinanceiros['lucros']) ?>,
                borderColor: '#0d6efd',
                backgroundColor: 'rgba(13, 110, 253, 0.1)',
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

// Gráfico de OS
const osCtx = document.getElementById('osChart').getContext('2d');
new Chart(osCtx, {
    type: 'bar',
    data: {
        labels: <?= json_encode($tendenciaOS['meses']) ?>,
        datasets: [
            {
                label: 'OS Criadas',
                data: <?= json_encode($tendenciaOS['criadas']) ?>,
                backgroundColor: 'rgba(13, 110, 253, 0.7)'
            },
            {
                label: 'OS Finalizadas',
                data: <?= json_encode($tendenciaOS['finalizadas']) ?>,
                backgroundColor: 'rgba(25, 135, 84, 0.7)'
            }
        ]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
            y: {
                beginAtZero: true
            }
        }
    }
});

// Gráfico de Clientes
const clientesCtx = document.getElementById('clientesChart').getContext('2d');
new Chart(clientesCtx, {
    type: 'doughnut',
    data: {
        labels: ['Recorrentes', 'Novos', 'Inativos'],
        datasets: [{
            data: [
                <?= $analiseClientes['recorrentes'] ?>,
                <?= $analiseClientes['novos'] ?>,
                <?= $analiseClientes['inativos'] ?>
            ],
            backgroundColor: [
                '#198754',
                '#0d6efd',
                '#6c757d'
            ]
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'bottom'
            }
        }
    }
});

// Gráfico de Serviços (Pizza)
<?php if (!empty($servicosMaisExecutados['grafico']['labels'])): ?>
const servicosCtx = document.getElementById('servicosChart').getContext('2d');
new Chart(servicosCtx, {
    type: 'pie',
    data: {
        labels: <?= json_encode($servicosMaisExecutados['grafico']['labels']) ?>,
        datasets: [{
            data: <?= json_encode($servicosMaisExecutados['grafico']['valores']) ?>,
            backgroundColor: <?= json_encode($servicosMaisExecutados['grafico']['cores']) ?>
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'bottom',
                labels: {
                    boxWidth: 12,
                    padding: 10,
                    font: {
                        size: 11
                    }
                }
            },
            tooltip: {
                callbacks: {
                    label: function(context) {
                        const label = context.label || '';
                        const value = context.parsed || 0;
                        const total = context.dataset.data.reduce((a, b) => a + b, 0);
                        const percentage = ((value / total) * 100).toFixed(1);
                        return label + ': ' + value + ' execuções (' + percentage + '%)';
                    }
                }
            }
        }
    }
});
<?php endif; ?>
</script>
