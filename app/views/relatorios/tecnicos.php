<?php
/**
 * proService - Relatório de Desempenho por Técnico
 * Arquivo: /app/views/relatorios/tecnicos.php
 */
?>

<?= breadcrumb(['Dashboard' => 'dashboard', 'Relatórios' => 'relatorios', 'Desempenho por Técnico']) ?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-0"><i class="bi bi-people text-info"></i> Desempenho por Técnico</h2>
            <p class="text-muted mb-0">Produtividade e receita gerada por técnico</p>
        </div>
    </div>

    <!-- Filtros -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="<?= url('relatorios/tecnicos') ?>">
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
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-funnel"></i> Filtrar
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Ranking -->
    <div class="row g-4 mb-4">
        <?php 
        $pos = 1;
        $medalhas = ['🥇', '🥈', '🥉'];
        foreach ($desempenho as $t): 
            $cardClass = $pos == 1 ? 'border-warning' : ($pos == 2 ? 'border-secondary' : ($pos == 3 ? 'border-danger' : ''));
        ?>
        <div class="col-md-4">
            <div class="card h-100 <?= $cardClass ?> border-2">
                <div class="card-body text-center">
                    <div class="mb-3">
                        <span class="display-4"><?= $medalhas[$pos - 1] ?? ('#' . $pos) ?></span>
                    </div>
                    <h5 class="card-title"><?= e($t['nome']) ?></h5>
                    <div class="row g-2 mt-3">
                        <div class="col-6">
                            <div class="p-2 bg-light rounded">
                                <small class="text-muted d-block">OS Finalizadas</small>
                                <strong class="fs-4"><?= number_format($t['total_os']) ?></strong>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="p-2 bg-light rounded">
                                <small class="text-muted d-block">Receita</small>
                                <strong class="fs-5">R$ <?= number_format($t['receita_gerada'], 2, ',', '.') ?></strong>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="p-2 bg-light rounded">
                                <small class="text-muted d-block">Ticket Médio</small>
                                <strong>R$ <?= number_format($t['ticket_medio'], 2, ',', '.') ?></strong>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="p-2 bg-light rounded">
                                <small class="text-muted d-block">Tempo Médio</small>
                                <strong><?= $t['tempo_medio'] ? round($t['tempo_medio']) : 0 ?> dias</strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php $pos++; endforeach; ?>
    </div>

    <!-- Tabela Detalhada -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Ranking Completo</h5>
            <span class="badge bg-primary"><?= count($desempenho) ?> técnicos</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Técnico</th>
                            <th class="text-center">OS Finalizadas</th>
                            <th class="text-end">Receita Gerada</th>
                            <th class="text-end">Ticket Médio</th>
                            <th class="text-center">Tempo Médio</th>
                            <th class="text-center">% do Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($desempenho)): ?>
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">
                                <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                Nenhum dado encontrado para o período
                            </td>
                        </tr>
                        <?php else: ?>
                        <?php 
                        $pos = 1;
                        foreach ($desempenho as $t): 
                            $percentual = $totalReceita > 0 ? ($t['receita_gerada'] / $totalReceita) * 100 : 0;
                        ?>
                        <tr>
                            <td>
                                <span class="badge bg-<?= $pos <= 3 ? 'warning text-dark' : 'light text-dark' ?>">
                                    #<?= $pos++ ?>
                                </span>
                            </td>
                            <td>
                                <strong><?= e($t['nome']) ?></strong>
                                <br><small class="text-muted"><?= e($t['email']) ?></small>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-primary"><?= number_format($t['total_os']) ?></span>
                            </td>
                            <td class="text-end">R$ <?= number_format($t['receita_gerada'], 2, ',', '.') ?></td>
                            <td class="text-end">R$ <?= number_format($t['ticket_medio'], 2, ',', '.') ?></td>
                            <td class="text-center">
                                <?= $t['tempo_medio'] ? round($t['tempo_medio']) : 0 ?> dias
                            </td>
                            <td class="text-center">
                                <div class="d-flex align-items-center justify-content-center">
                                    <div class="progress flex-grow-1 me-2" style="height: 8px; max-width: 100px;">
                                        <div class="progress-bar bg-success" style="width: <?= $percentual ?>%"></div>
                                    </div>
                                    <small><?= number_format($percentual, 1) ?>%</small>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                    <tfoot class="table-light">
                        <tr class="fw-bold">
                            <td colspan="2" class="text-end">TOTAIS:</td>
                            <td class="text-center"><?= number_format($totalOS) ?></td>
                            <td class="text-end">R$ <?= number_format($totalReceita, 2, ',', '.') ?></td>
                            <td colspan="3"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>
