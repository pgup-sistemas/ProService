<?php
/**
 * Página de seleção de planos
 * @var array $planos Lista de planos disponíveis
 * @var string $planoAtual Plano atual da empresa
 * @var int $diasTrial Dias restantes do trial
 * @var array $empresa Dados da empresa
 */
?>

<div class="container-fluid py-4">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= url('dashboard') ?>">Dashboard</a></li>
            <li class="breadcrumb-item active" aria-current="page">Planos e Assinaturas</li>
        </ol>
    </nav>

    <!-- Alerta de Trial -->
    <?php if ($diasTrial > 0): ?>
        <div class="alert alert-info alert-dismissible fade show mb-4" role="alert">
            <div class="d-flex align-items-center">
                <i class="bi bi-clock-history fs-4 me-3"></i>
                <div>
                    <strong>Você está no período de trial!</strong><br>
                    Aproveite todos os recursos do sistema gratuitamente por mais <strong><?= $diasTrial ?> dias</strong>.
                    <br><small class="text-muted">Após esse período, escolha um plano para continuar usando o proService.</small>
                </div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php elseif ($diasTrial <= 0 && $planoAtual === 'trial'): ?>
        <div class="alert alert-warning alert-dismissible fade show mb-4" role="alert">
            <div class="d-flex align-items-center">
                <i class="bi bi-exclamation-triangle fs-4 me-3"></i>
                <div>
                    <strong>Seu trial expirou!</strong><br>
                    Escolha um plano abaixo para continuar usando o proService sem interrupções.
                </div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Header -->
    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-md-between mb-4">
        <div class="text-center text-md-start">
            <h1 class="h2 fw-bold text-primary mb-2">Planos e Assinaturas</h1>
            <p class="text-muted mb-0">Escolha o plano ideal para o seu volume de OS e equipe</p>
        </div>
        <?php if (!empty($empresa['assinatura_id']) || ($planoAtual !== 'trial' && $planoAtual !== 'inactive')): ?>
            <div class="mt-3 mt-md-0">
                <a class="btn btn-outline-secondary" href="<?= url('assinaturas/gerenciar') ?>">
                    <i class="bi bi-sliders"></i> Gerenciar Assinatura
                </a>
            </div>
        <?php endif; ?>
    </div>

    <!-- Uso atual (dados reais) -->
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="text-muted small">Ordens de Serviço (mês)</div>
                            <div class="fw-bold">
                                <?= (int) ($usoOS ?? 0) ?>
                                <span class="text-muted">/ <?php 
                                    $lim = (int) ($limiteOS ?? 0);
                                    echo ($lim <= 0 || $lim > 1000) ? '∞' : $lim;
                                ?></span>
                            </div>
                        </div>
                        <div class="icon-pill bg-primary bg-opacity-10 text-primary">
                            <i class="bi bi-clipboard-check"></i>
                        </div>
                    </div>
                    <div class="progress mt-2" style="height: 6px;">
                        <?php
                            $lim = (int) ($limiteOS ?? 0);
                            $uso = (int) ($usoOS ?? 0);
                            // Se ilimitado ou valor inválido, não mostrar progress bar percentual
                            if ($lim <= 0 || $lim > 1000) {
                                // Para ilimitado, mostrar progress como 0%
                                $pct = 0;
                            } else {
                                $pct = min(100, ($uso / $lim) * 100);
                            }
                        ?>
                        <div class="progress-bar" style="width: <?= (int) $pct ?>%"></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="text-muted small">Técnicos ativos</div>
                            <div class="fw-bold">
                                <?= (int) ($totalTecnicos ?? 0) ?>
                                <span class="text-muted">/ <?php 
                                    $limTec = (int) ($limiteTecnicos ?? 0);
                                    echo ($limTec <= 0 || $limTec > 1000) ? '∞' : $limTec;
                                ?></span>
                            </div>
                        </div>
                        <div class="icon-pill bg-success bg-opacity-10 text-success">
                            <i class="bi bi-people"></i>
                        </div>
                    </div>
                    <div class="progress mt-2" style="height: 6px;">
                        <?php
                            $limT = (int) ($limiteTecnicos ?? 0);
                            $usoT = (int) ($totalTecnicos ?? 0);
                            $pctT = $limT > 0 ? min(100, ($usoT / $limT) * 100) : 0;
                        ?>
                        <div class="progress-bar bg-success" style="width: <?= (int) $pctT ?>%"></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="text-muted small">Armazenamento do plano</div>
                            <div class="fw-bold"><?= (int) ($limiteArmazenamento ?? 0) ?> MB</div>
                        </div>
                        <div class="icon-pill bg-warning bg-opacity-10 text-warning">
                            <i class="bi bi-hdd"></i>
                        </div>
                    </div>
                    <div class="text-muted small mt-2">Fotos e anexos das OS</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Planos -->
    <div class="row justify-content-center g-3">
        <?php foreach ($planos as $id => $plano): ?>
            <div class="col-md-4">
                <div class="card h-100 border-0 shadow-sm plan-card <?= $plano['destaque'] ? 'plan-highlight' : '' ?>">
                    <?php if ($plano['destaque']): ?>
                        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center py-2">
                            <span class="small fw-semibold">Recomendado</span>
                            <span class="badge bg-warning text-dark">Mais popular</span>
                        </div>
                    <?php endif; ?>
                    
                    <div class="card-body p-3">
                        <div class="d-flex align-items-start justify-content-between">
                            <div>
                                <h3 class="h5 fw-bold mb-1"><?= $plano['nome'] ?></h3>
                                <div class="text-muted small"><?= $plano['descricao'] ?></div>
                            </div>
                            <div class="text-end">
                                <div class="h4 fw-bold text-primary mb-0">R$ <?= number_format($plano['preco'] / 100, 2, ',', '.') ?></div>
                                <div class="text-muted small">por mês</div>
                            </div>
                        </div>
                        
                        <hr class="my-3">

                        <div class="row g-2 mb-3">
                            <div class="col-4">
                                <div class="mini-metric">
                                    <div class="text-muted small">OS/mês</div>
                                    <div class="fw-semibold"><?= $plano['limite_os'] == -1 ? '∞' : (int) $plano['limite_os'] ?></div>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="mini-metric">
                                    <div class="text-muted small">Técnicos</div>
                                    <div class="fw-semibold"><?= $plano['limite_tecnicos'] == -1 ? '∞' : (int) $plano['limite_tecnicos'] ?></div>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="mini-metric">
                                    <div class="text-muted small">Storage</div>
                                    <div class="fw-semibold"><?= (int) $plano['limite_armazenamento'] >= 1024 ? ((int) ($plano['limite_armazenamento'] / 1024)) . ' GB' : (int) $plano['limite_armazenamento'] . ' MB' ?></div>
                                </div>
                            </div>
                        </div>

                        <ul class="list-unstyled small mb-3">
                            <?php foreach (array_slice($plano['recursos'], 0, 4) as $recurso): ?>
                                <li class="mb-2 d-flex align-items-start">
                                    <i class="bi bi-check-circle-fill text-success me-2 mt-1"></i>
                                    <span><?= $recurso ?></span>
                                </li>
                            <?php endforeach; ?>
                        </ul>

                        <div class="d-grid">
                            <?php if ($planoAtual === $id): ?>
                                <button class="btn btn-success" disabled>
                                    <i class="bi bi-check-lg me-2"></i>Plano Atual
                                </button>
                            <?php else: ?>
                                <a href="<?= url("assinaturas/efipay-checkout/{$id}") ?>" 
                                   class="btn <?= $plano['destaque'] ? 'btn-primary' : 'btn-outline-primary' ?>">
                                    <i class="bi bi-credit-card me-2"></i><?= ($planoAtual === 'trial' || $planoAtual === 'inactive') ? 'Assinar Agora' : 'Fazer Upgrade' ?>
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="card-footer bg-light text-center py-3">
                        <small class="text-muted">
                            <i class="bi bi-shield-check me-1"></i>
                            Pagamento seguro via EfiPay
                        </small>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Informações Adicionais -->
    <div class="row mt-5">
        <div class="col-12">
            <div class="card border-0 shadow-lg" style="background: linear-gradient(135deg, #1e40af 0%, #059669 100%); color: white;">
                <div class="card-body p-5">
                    <h4 class="mb-4 fw-bold"><i class="bi bi-info-circle me-2"></i>Informações Importantes</h4>
                    
                    <div class="row g-4">
                        <div class="col-md-3">
                            <div class="d-flex align-items-start">
                                <div class="icon-circle bg-white bg-opacity-25 me-3">
                                    <i class="bi bi-credit-card fs-4"></i>
                                </div>
                                <div>
                                    <h6 class="fw-bold mb-2">Pagamento</h6>
                                    <p class="small mb-0 opacity-75">
                                        Aceitamos cartões de crédito das principais bandeiras. 
                                        A cobrança é recorrente mensal.
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="d-flex align-items-start">
                                <div class="icon-circle bg-white bg-opacity-25 me-3">
                                    <i class="bi bi-arrow-repeat fs-4"></i>
                                </div>
                                <div>
                                    <h6 class="fw-bold mb-2">Cancelamento</h6>
                                    <p class="small mb-0 opacity-75">
                                        Cancele quando quiser, sem taxas ou multas. 
                                        Você mantém o acesso até o final do período pago.
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="d-flex align-items-start">
                                <div class="icon-circle bg-white bg-opacity-25 me-3">
                                    <i class="bi bi-headset fs-4"></i>
                                </div>
                                <div>
                                    <h6 class="fw-bold mb-2">Suporte</h6>
                                    <p class="small mb-0 opacity-75">
                                        Suporte técnico disponível para ajudar com 
                                        qualquer dúvida sobre sua assinatura.
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="d-flex align-items-start">
                                <div class="icon-circle bg-white bg-opacity-25 me-3">
                                    <i class="bi bi-shield-lock fs-4"></i>
                                </div>
                                <div>
                                    <h6 class="fw-bold mb-2">Segurança</h6>
                                    <p class="small mb-0 opacity-75">
                                        Seus dados de pagamento são criptografados e 
                                        processados com total segurança.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.card {
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}
.card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 30px rgba(0,0,0,0.1) !important;
}
.plan-highlight {
    outline: 2px solid rgba(var(--bs-primary-rgb), 0.35);
}
.icon-pill {
    width: 40px;
    height: 40px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
}
.mini-metric {
    border: 1px solid rgba(0,0,0,0.06);
    border-radius: 10px;
    padding: 8px 10px;
    background: #fff;
    text-align: center;
}
.info-gradient {
    background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 50%, #084298 100%) !important;
    color: white;
}
.info-gradient .icon-circle {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}
</style>
