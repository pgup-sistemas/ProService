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

<?php 
// Verificação robusta de trial: se plano é 'trial' OU plano está vazio mas dataFimTrial está definido
$isTrialActive = !empty($empresa) && (!empty($diasTrial) || (!empty($dataFimTrial) && strtotime($dataFimTrial) > time()));
if ($isTrialActive): ?>
<!-- Alert de Trial - Versão Melhorada -->
<div class="mb-4">
    <div class="card border-warning bg-warning bg-opacity-10 shadow-sm">
        <div class="card-body p-md-4 p-3">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <div class="d-flex align-items-center mb-2 mb-md-3">
                        <span class="fs-3 fs-md-1 me-2 me-md-3">⏰</span>
                        <div>
                            <h4 class="mb-0 text-warning fs-6 fs-md-5">Seu período de trial está terminando!</h4>
                            <p class="text-muted mb-0 small">Aproveite os últimos dias de acesso ilimitado</p>
                        </div>
                    </div>
                    
                    <div class="row g-2 g-md-3 mt-2 mt-md-3">
                        <div class="col-4 col-md-4">
                            <div class="p-2 p-md-3 bg-white rounded border border-warning">
                                <div class="text-center">
                                    <h5 class="text-warning mb-0 fs-5 fs-md-3"><?= (int) ceil($diasTrial) ?></h5>
                                    <small class="text-muted d-block">dias</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-4 col-md-4">
                            <div class="p-2 p-md-3 bg-white rounded">
                                <div class="text-center">
                                    <h6 class="mb-0 fs-5">∞</h6>
                                    <small class="text-muted d-block">OS</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-4 col-md-4">
                            <div class="p-2 p-md-3 bg-white rounded">
                                <div class="text-center">
                                    <h6 class="mb-0 fs-5">∞</h6>
                                    <small class="text-muted d-block">Técnicos</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-2 mt-md-3">
                        <small class="text-muted d-block mb-1">
                            <i class="bi bi-calendar3"></i> Expira: <strong><?= date('d/m/Y', strtotime($dataFimTrial)) ?></strong>
                        </small>
                        <small class="text-muted d-none d-md-block">
                            <i class="bi bi-info-circle"></i> Após o trial, escolha um plano para continuar.
                        </small>
                    </div>
                </div>
                <div class="col-md-4 mt-3 mt-md-0">
                    <div class="text-center text-md-end">
                        <h6 class="mb-2 mb-md-3 text-dark">Escolha seu Plano</h6>
                        <div class="d-grid gap-2">
                            <a href="<?= url('assinaturas/efipay-checkout/starter') ?>" class="btn btn-primary btn-sm btn-md-lg">
                                <i class="bi bi-arrow-up-circle me-1"></i><span class="d-none d-md-inline">Plano </span>Básico
                                <div><small>R$ 49,90</small></div>
                            </a>
                            <a href="<?= url('assinaturas/efipay-checkout/pro') ?>" class="btn btn-success btn-sm btn-md-lg">
                                <i class="bi bi-star-fill me-1"></i><span class="d-none d-md-inline">Plano </span>Pro
                                <div><small>R$ 99,90</small></div>
                            </a>
                            <a href="<?= url('assinaturas') ?>" class="btn btn-outline-secondary btn-sm">
                                Ver planos
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <style>
        @media (max-width: 768px) {
            .btn-md-lg {
                font-size: 0.875rem;
                padding: 0.4rem 0.8rem;
            }
            .fs-md-5 {
                font-size: 1rem !important;
            }
            .fs-md-1 {
                font-size: 3rem !important;
            }
            .me-md-3 {
                margin-right: 1rem !important;
            }
            .p-md-3 {
                padding: 0.75rem !important;
            }
            .p-md-4 {
                padding: 0.75rem !important;
            }
            .gap-md-3 {
                gap: 0.75rem !important;
            }
        }
    </style>
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
