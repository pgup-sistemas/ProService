<?= breadcrumb(['Dashboard' => 'dashboard', 'Financeiro']) ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1">Financeiro</h4>
        <p class="text-muted mb-0">Acompanhe suas finanças</p>
    </div>
    <div>
        <a href="<?= url('financeiro/receitas') ?>" class="btn btn-success me-2">
            <i class="bi bi-cash-stack"></i> Receitas
        </a>
        <a href="<?= url('financeiro/despesas') ?>" class="btn btn-danger">
            <i class="bi bi-cart-dash"></i> Despesas
        </a>
    </div>
</div>

<!-- Cards de Resumo -->
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card stat-card green">
            <h3><?= formatMoney($totalReceitas) ?></h3>
            <p>Receitas (Período)</p>
            <i class="bi bi-arrow-down-circle"></i>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card orange">
            <h3><?= formatMoney($totalDespesas) ?></h3>
            <p>Despesas (Período)</p>
            <i class="bi bi-arrow-up-circle"></i>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card <?= $lucro >= 0 ? 'blue' : 'red' ?>">
            <h3><?= formatMoney($lucro) ?></h3>
            <p>Lucro (Período)</p>
            <i class="bi bi-cash-coin"></i>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card gray">
            <h3><?= formatMoney($receitasPendentes) ?></h3>
            <p>A Receber</p>
            <i class="bi bi-hourglass-split"></i>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card stat-card orange">
            <h3><?= formatMoney($despesasPendentes) ?></h3>
            <p>A Pagar</p>
            <i class="bi bi-hourglass-split"></i>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card <?= $resultadoPrevisto >= 0 ? 'blue' : 'red' ?>">
            <h3><?= formatMoney($resultadoPrevisto) ?></h3>
            <p>Resultado Previsto</p>
            <i class="bi bi-graph-up"></i>
        </div>
    </div>
</div>

<!-- Filtro de Período -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-md-4">
                <label class="form-label">Data Início</label>
                <input type="date" name="data_inicio" class="form-control" value="<?= $dataInicio ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label">Data Fim</label>
                <input type="date" name="data_fim" class="form-control" value="<?= $dataFim ?>">
            </div>
            <div class="col-md-4">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-funnel"></i> Filtrar
                </button>
            </div>
        </form>
    </div>
</div>

<div class="row g-3">
    <!-- Receitas por Forma de Pagamento -->
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header">
                <h6 class="mb-0"><i class="bi bi-credit-card"></i> Receitas por Forma de Pagamento</h6>
            </div>
            <div class="card-body">
                <?php if (empty($receitasPorForma)): ?>
                    <p class="text-muted text-center py-4">Nenhuma receita no período</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Forma</th>
                                    <th class="text-end">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($receitasPorForma as $r): ?>
                                <tr>
                                    <td><?= ucfirst(str_replace('_', ' ', $r['forma_pagamento'] ?? '')) ?></td>
                                    <td class="text-end"><?= formatMoney($r['total']) ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Despesas por Categoria -->
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header">
                <h6 class="mb-0"><i class="bi bi-pie-chart"></i> Despesas por Categoria</h6>
            </div>
            <div class="card-body">
                <?php if (empty($despesasPorCategoria)): ?>
                    <p class="text-muted text-center py-4">Nenhuma despesa no período</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Categoria</th>
                                    <th class="text-end">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($despesasPorCategoria as $d): ?>
                                <tr>
                                    <td><?= ucfirst($d['categoria']) ?></td>
                                    <td class="text-end"><?= formatMoney($d['total']) ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
