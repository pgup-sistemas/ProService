<?php
// Flash messages
$flash = getFlash();
?>

<?php if ($flash): ?>
<div class="alert alert-<?= $flash['type'] === 'error' ? 'danger' : $flash['type'] ?> alert-dismissible fade show">
    <?= e($flash['message']) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<?php if (!empty($mostrarOnboarding)): ?>
<?php include __DIR__ . '/onboarding.php'; ?>
<?php endif; ?>

<?php if ($empresa['plano'] === 'trial'): ?>
<div class="alert alert-warning">
    <div class="d-flex align-items-center justify-content-between">
        <div>
            <i class="bi bi-clock"></i> <strong>Período de Trial</strong>
            Você tem <?= ceil($diasTrial) ?> dias restantes do seu trial gratuito.
        </div>
        <a href="<?= url('assinaturas') ?>" class="btn btn-warning btn-sm">
            <i class="bi bi-arrow-up-circle me-1"></i>Fazer Upgrade
        </a>
    </div>
</div>
<?php endif; ?>

<!-- Estatísticas Principais -->
<div class="row g-3 mb-4">
    <div class="col-md-6 col-lg-3">
        <div class="stat-card blue">
            <h3><?= formatMoney($receitasMes['total'] ?? 0) ?></h3>
            <p>Receitas do Mês</p>
            <i class="bi bi-cash-stack"></i>
        </div>
    </div>
    <div class="col-md-6 col-lg-3">
        <div class="stat-card orange">
            <h3><?= formatMoney($despesasMes['total'] ?? 0) ?></h3>
            <p>Despesas do Mês</p>
            <i class="bi bi-cart-dash"></i>
        </div>
    </div>
    <div class="col-md-6 col-lg-3">
        <div class="stat-card <?= $lucro >= 0 ? 'green' : 'red' ?>">
            <h3><?= formatMoney($lucro) ?></h3>
            <p>Lucro do Mês</p>
            <i class="bi bi-graph-<?= $lucro >= 0 ? 'up' : 'down' ?>"></i>
        </div>
    </div>
    <div class="col-md-6 col-lg-3">
        <div class="stat-card gray">
            <h3><?= formatMoney($totalPendente) ?></h3>
            <p>Pendente a Receber</p>
            <i class="bi bi-hourglass-split"></i>
        </div>
    </div>
</div>

<div class="row g-3">
    <!-- OS Urgentes -->
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h5 class="mb-0"><i class="bi bi-exclamation-triangle text-danger"></i> OS Urgentes</h5>
                <a href="<?= url('ordens?prioridade=urgente') ?>" class="btn btn-sm btn-outline-primary">Ver Todas</a>
            </div>
            <div class="card-body p-0">
                <?php if (empty($osUrgentes)): ?>
                    <div class="text-center py-4 text-muted">
                        <i class="bi bi-check-circle fs-1"></i>
                        <p class="mb-0">Nenhuma OS urgente!</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <tbody>
                                <?php foreach (array_slice($osUrgentes, 0, 5) as $os): ?>
                                <tr>
                                    <td>
                                        <strong>#<?= str_pad($os['numero_os'], 4, '0', STR_PAD_LEFT) ?></strong>
                                        <br><small class="text-muted"><?= e($os['cliente_nome'] ?? 'Cliente') ?></small>
                                    </td>
                                    <td>
                                        <span class="badge <?= getStatusClass($os['status']) ?>"><?= getStatusLabel($os['status']) ?></span>
                                    </td>
                                    <td class="text-end">
                                        <a href="<?= url('ordens/show/' . $os['id']) ?>" class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- OS Atrasadas -->
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h5 class="mb-0"><i class="bi bi-clock-history text-warning"></i> OS Atrasadas</h5>
                <a href="<?= url('ordens?filtro=atrasadas') ?>" class="btn btn-sm btn-outline-primary">Ver Todas</a>
            </div>
            <div class="card-body p-0">
                <?php if (empty($osAtrasadas)): ?>
                    <div class="text-center py-4 text-muted">
                        <i class="bi bi-calendar-check fs-1"></i>
                        <p class="mb-0">Nenhuma OS atrasada!</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <tbody>
                                <?php foreach (array_slice($osAtrasadas, 0, 5) as $os): ?>
                                <tr>
                                    <td>
                                        <strong>#<?= str_pad($os['numero_os'], 4, '0', STR_PAD_LEFT) ?></strong>
                                        <br><small class="text-muted"><?= e($os['cliente_nome']) ?></small>
                                    </td>
                                    <td>
                                        <small class="text-danger">
                                            Previsão: <?= formatDate($os['previsao_entrega']) ?>
                                        </small>
                                    </td>
                                    <td class="text-end">
                                        <a href="<?= url('ordens/show/' . $os['id']) ?>" class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Ações Rápidas -->
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-lightning"></i> Ações Rápidas</h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-6 col-md-3">
                        <a href="<?= url('ordens/create') ?>" class="btn btn-outline-primary w-100 py-3">
                            <i class="bi bi-clipboard-plus fs-3 d-block mb-2"></i>
                            Nova OS
                        </a>
                    </div>
                    <div class="col-6 col-md-3">
                        <a href="<?= url('clientes/create') ?>" class="btn btn-outline-success w-100 py-3">
                            <i class="bi bi-person-plus fs-3 d-block mb-2"></i>
                            Novo Cliente
                        </a>
                    </div>
                    <div class="col-6 col-md-3">
                        <a href="<?= url('financeiro/receitas') ?>" class="btn btn-outline-warning w-100 py-3">
                            <i class="bi bi-cash-coin fs-3 d-block mb-2"></i>
                            Receitas
                        </a>
                    </div>
                    <div class="col-6 col-md-3">
                        <a href="<?= url('produtos') ?>" class="btn btn-outline-info w-100 py-3">
                            <i class="bi bi-box-seam fs-3 d-block mb-2"></i>
                            Estoque
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Alertas de Estoque -->
    <?php if (!empty($produtosEmFalta)): ?>
    <div class="col-12">
        <div class="card border-warning">
            <div class="card-header bg-warning text-dark">
                <h5 class="mb-0"><i class="bi bi-box-arrow-in-down"></i> Produtos em Falta</h5>
            </div>
            <div class="card-body">
                <div class="row g-2">
                    <?php foreach (array_slice($produtosEmFalta, 0, 4) as $produto): ?>
                    <div class="col-md-3">
                        <div class="d-flex align-items-center p-2 bg-light rounded">
                            <i class="bi bi-exclamation-circle text-warning me-2"></i>
                            <div>
                                <div class="fw-medium"><?= e($produto['nome']) ?></div>
                                <small class="text-muted">Estoque: <?= $produto['quantidade_estoque'] ?> (Mín: <?= $produto['quantidade_minima'] ?>)</small>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
    
    <!-- Resumo de OS -->
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-clipboard-data"></i> Resumo de Ordens de Serviço</h5>
            </div>
            <div class="card-body">
                <div class="row g-3 text-center">
                    <div class="col-6 col-md-3">
                        <div class="p-3 bg-primary bg-opacity-10 rounded">
                            <h4 class="text-primary mb-1"><?= $estatisticasOS['mes_atual']['total'] ?? 0 ?></h4>
                            <small class="text-muted">OS no Mês</small>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="p-3 bg-success bg-opacity-10 rounded">
                            <h4 class="text-success mb-1"><?= $estatisticasOS['hoje']['total'] ?? 0 ?></h4>
                            <small class="text-muted">OS Hoje</small>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="p-3 bg-warning bg-opacity-10 rounded">
                            <h4 class="text-warning mb-1"><?= count($osAtrasadas) ?></h4>
                            <small class="text-muted">OS Atrasadas</small>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="p-3 bg-info bg-opacity-10 rounded">
                            <h4 class="text-info mb-1"><?= $limiteOS['restante'] ?? '∞' ?></h4>
                            <small class="text-muted">OS Restantes (Mês)</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
