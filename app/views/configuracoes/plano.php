<?php
/**
 * proService - Gestão de Plano
 * Arquivo: /app/views/configuracoes/plano.php
 */

// Define variáveis com fallback
$planoAtualKey = $empresa['plano'] ?? 'trial';

// Garantir que variáveis do controller existam
if (!isset($usoOS)) $usoOS = 0;
if (!isset($limiteOS)) $limiteOS = 20;
if (!isset($totalTecnicos)) $totalTecnicos = 0;
if (!isset($limiteTecnicos)) $limiteTecnicos = 1;
if (!isset($percentualOS)) $percentualOS = 0;
if (!isset($percentualTecnicos)) $percentualTecnicos = 0;
if (!isset($diasTrial)) $diasTrial = 0;

// Recalcular percentuais se necessário
if ($percentualOS == 0 && $limiteOS > 0) {
    $percentualOS = ($usoOS / $limiteOS) * 100;
}
if ($percentualTecnicos == 0 && $limiteTecnicos > 0) {
    $percentualTecnicos = ($totalTecnicos / $limiteTecnicos) * 100;
}

// Cores e ícones dos planos
$planosInfo = [
    'trial' => ['nome' => 'Trial Grátis', 'icon' => '🎁', 'color' => 'warning'],
    'starter' => ['nome' => 'Starter', 'icon' => '💼', 'color' => 'primary'],
    'pro' => ['nome' => 'Pro', 'icon' => '🚀', 'color' => 'success'],
    'business' => ['nome' => 'Business', 'icon' => '🏢', 'color' => 'dark']
];

$planoInfo = $planosInfo[$planoAtualKey] ?? $planosInfo['trial'];
?>

<?= breadcrumb(['Dashboard' => 'dashboard', 'Configurações' => 'configuracoes', 'Meu Plano']) ?>

<div class="container-fluid">
    <div class="mb-4">
        <h2 class="mb-0"><i class="bi bi-gear text-primary"></i> Configurações</h2>
        <p class="text-muted">Gerencie as configurações do sistema</p>
    </div>

    <!-- Tabs -->
    <ul class="nav nav-tabs mb-4">
        <li class="nav-item">
            <a class="nav-link" href="<?= url('configuracoes/empresa') ?>">
                <i class="bi bi-building"></i> Dados da Empresa
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="<?= url('configuracoes/aparencia') ?>">
                <i class="bi bi-palette"></i> Aparência
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="<?= url('configuracoes/comunicacao') ?>">
                <i class="bi bi-chat-dots"></i> Comunicação
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="<?= url('configuracoes/contrato') ?>">
                <i class="bi bi-file-text"></i> Contrato
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link active" href="<?= url('configuracoes/plano') ?>">
                <i class="bi bi-credit-card"></i> Meu Plano
            </a>
        </li>
    </ul>

    <!-- Plano Atual -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card border-<?= $planoInfo['color'] ?> border-4">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <span class="display-4 me-3"><?= $planoInfo['icon'] ?></span>
                        <div>
                            <h5 class="card-title mb-0">Plano Atual</h5>
                            <h3 class="mb-0 text-<?= $planoInfo['color'] ?>"><?= $planoAtual['nome'] ?></h3>
                        </div>
                    </div>
                    
                    <?php if ($planoAtualKey === 'trial' && !empty($diasTrial)): ?>
                    <div class="alert alert-warning">
                        <i class="bi bi-clock"></i> 
                        <strong><?= round((float)$diasTrial) ?> dias</strong> restantes no período de teste
                    </div>
                    <?php endif; ?>

                    <ul class="list-unstyled mb-0">
                        <li class="mb-2"><i class="bi bi-check-circle text-success"></i> <?= $limiteOS == -1 ? 'OS ilimitadas' : $limiteOS . ' OS/mês' ?></li>
                        <li class="mb-2"><i class="bi bi-check-circle text-success"></i> <?= $limiteTecnicos == -1 ? 'Técnicos ilimitados' : 'Até ' . $limiteTecnicos . ' técnicos' ?></li>
                        <li class="mb-2"><i class="bi bi-check-circle text-success"></i> <?= ($empresa['limite_armazenamento_mb'] ?? 100) ?> MB armazenamento</li>
                        <li class="mb-2"><i class="bi bi-check-circle text-success"></i> Relatórios incluídos</li>
                    </ul>

                    <?php if ($planoAtualKey === 'trial'): ?>
                    <a href="#planos" class="btn btn-<?= $planoInfo['color'] ?> w-100 mt-3">
                        <i class="bi bi-arrow-up-circle"></i> Fazer Upgrade
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Uso -->
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-speedometer2"></i> Uso do Plano</h5>
                </div>
                <div class="card-body">
                    <!-- OS -->
                    <div class="mb-4">
                        <div class="d-flex justify-content-between mb-1">
                            <span>Ordens de Serviço (mês)</span>
                            <span class="<?= $percentualOS >= 80 ? 'text-warning' : '' ?> <?= $percentualOS >= 100 ? 'text-danger' : '' ?>">
                                <?= $usoOS ?> / <?= $limiteOS > 0 ? $limiteOS : '∞' ?> 
                                <?php if ($limiteOS > 0): ?>
                                (<?= round($percentualOS) ?>%)
                                <?php endif; ?>
                            </span>
                        </div>
                        <div class="progress" style="height: 20px;">
                            <div class="progress-bar <?= $percentualOS >= 100 ? 'bg-danger' : ($percentualOS >= 80 ? 'bg-warning' : 'bg-primary') ?>" 
                                 style="width: <?= $limiteOS > 0 ? min($percentualOS, 100) : 50 ?>%">
                            </div>
                        </div>
                        <?php if ($percentualOS >= 80 && $limiteOS > 0): ?>
                        <small class="text-<?= $percentualOS >= 100 ? 'danger' : 'warning' ?>">
                            <i class="bi bi-exclamation-triangle"></i> 
                            <?= $percentualOS >= 100 ? 'Limite atingido! Faça upgrade para criar mais OS.' : 'Você está próximo do limite.' ?>
                        </small>
                        <?php endif; ?>
                    </div>

                    <!-- Técnicos -->
                    <div class="mb-4">
                        <div class="d-flex justify-content-between mb-1">
                            <span>Técnicos</span>
                            <span class="<?= $percentualTecnicos >= 100 ? 'text-danger' : '' ?>">
                                <?= $totalTecnicos ?> / <?= $limiteTecnicos > 0 ? $limiteTecnicos : '∞' ?>
                            </span>
                        </div>
                        <div class="progress" style="height: 20px;">
                            <div class="progress-bar <?= $percentualTecnicos >= 100 ? 'bg-danger' : 'bg-success' ?>" 
                                 style="width: <?= $limiteTecnicos > 0 ? min($percentualTecnicos, 100) : 50 ?>%">
                            </div>
                        </div>
                    </div>

                    <!-- Armazenamento -->
                    <div>
                        <div class="d-flex justify-content-between mb-1">
                            <span>Armazenamento</span>
                            <span><?= ($empresa['limite_armazenamento_mb'] ?? 100) ?> MB</span>
                        </div>
                        <div class="progress" style="height: 20px;">
                            <div class="progress-bar bg-info" style="width: 30%"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Planos Disponíveis -->
    <div id="planos" class="card">
        <div class="card-header">
            <h5 class="mb-0"><i class="bi bi-stars"></i> Planos Disponíveis</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <?php foreach (($planosDisponiveis ?? []) as $key => $plano): 
                    $info = $planosInfo[$key] ?? ['nome' => ($plano['nome'] ?? $key), 'icon' => '⭐', 'color' => 'secondary'];
                    $isCurrent = $planoAtualKey === $key;
                ?>
                <div class="col-md-6 mb-4">
                    <div class="card h-100 <?= $isCurrent ? 'border-' . $info['color'] . ' border-3' : '' ?>">
                        <div class="card-header bg-<?= $info['color'] ?> bg-opacity-10">
                            <div class="d-flex align-items-center">
                                <span class="display-6 me-2"><?= $info['icon'] ?></span>
                                <div>
                                    <h5 class="mb-0"><?= e($plano['nome'] ?? $info['nome']) ?></h5>
                                    <?php if ($isCurrent): ?>
                                    <span class="badge bg-<?= $info['color'] ?>">Plano Atual</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="text-center mb-4">
                                <h2 class="mb-0">R$ <?= number_format((float) ($plano['preco'] ?? 0), 2, ',', '.') ?></h2>
                                <small class="text-muted">/mês</small>
                            </div>
                            
                            <ul class="list-group list-group-flush mb-4">
                                <li class="list-group-item"><i class="bi bi-check-circle text-success me-2"></i> <?= $plano['limite_os'] == -1 ? 'OS ilimitadas' : $plano['limite_os'] . ' OS/mês' ?></li>
                                <li class="list-group-item"><i class="bi bi-check-circle text-success me-2"></i> <?= $plano['limite_tecnicos'] == -1 ? 'Técnicos ilimitados' : 'Até ' . $plano['limite_tecnicos'] . ' técnicos' ?></li>
                                <li class="list-group-item"><i class="bi bi-check-circle text-success me-2"></i> <?= (int) ($plano['limite_armazenamento'] ?? 0) >= 1024 ? ((float) ($plano['limite_armazenamento'] ?? 0) / 1024) . ' GB' : (int) ($plano['limite_armazenamento'] ?? 0) . ' MB' ?> armazenamento</li>
                                <li class="list-group-item"><i class="bi bi-check-circle text-success me-2"></i> Relatórios incluídos</li>
                                <li class="list-group-item"><i class="bi bi-check-circle text-success me-2"></i> Suporte por e-mail</li>
                                <?php if ($key === 'business'): ?>
                                <li class="list-group-item"><i class="bi bi-check-circle text-success me-2"></i> Suporte prioritário</li>
                                <?php endif; ?>
                            </ul>

                            <?php if (!$isCurrent): ?>
                            <a class="btn btn-<?= $info['color'] ?> w-100" href="<?= url('assinaturas/efipay-checkout/' . $key) ?>">
                                <i class="bi bi-credit-card"></i>
                                <?= ($planoAtualKey === 'trial' || $planoAtualKey === 'inactive') ? 'Assinar Agora' : 'Fazer Upgrade' ?>
                            </a>
                            <?php else: ?>
                            <button class="btn btn-outline-secondary w-100" disabled>
                                <i class="bi bi-check-lg"></i> Plano Ativo
                            </button>
                            <?php endif; ?>
                        </div>
                        <?php if ($key === 'pro'): ?>
                        <div class="card-footer bg-success bg-opacity-10 text-center">
                            <small class="text-success"><i class="bi bi-lightning"></i> Melhor custo-benefício!</small>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- Plano Anual -->
            <div class="card border-success mt-4">
                <div class="card-header bg-success bg-opacity-10">
                    <h5 class="mb-0 text-success">
                        <i class="bi bi-calendar-check"></i> Economize com o Plano Anual!
                    </h5>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-3">Pague 10 meses e leve 12. <strong class="text-success">Economia de 20%!</strong></p>
                    
                    <div class="row">
                        <?php foreach (['starter', 'pro', 'business'] as $k):
                            $p = $planosDisponiveis[$k] ?? null;
                            if (!$p) continue;
                            $anual = ((float) ($p['preco'] ?? 0)) * 10;
                            $mensalEq = $anual / 12;
                            $color = ($planosInfo[$k]['color'] ?? 'primary');
                            $label = ($planosInfo[$k]['nome'] ?? ($p['nome'] ?? $k));
                        ?>
                        <div class="col-md-4 mb-3">
                            <div class="d-flex align-items-center p-3 bg-light rounded">
                                <div class="flex-grow-1">
                                    <span class="text-muted"><?= e($label) ?> Anual</span>
                                    <h4 class="mb-0 text-<?= $color ?>">R$ <?= number_format($anual, 2, ',', '.') ?></h4>
                                    <small class="text-muted">R$ <?= number_format($mensalEq, 2, ',', '.') ?>/mês</small>
                                </div>
                                <span class="badge bg-success">-20%</span>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <div class="text-center mt-2">
                        <small class="text-muted">
                            <i class="bi bi-info-circle"></i> 
                            O desconto é aplicado automaticamente na contratação anual
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Suporte -->
    <div class="card mt-4">
        <div class="card-body">
            <h6><i class="bi bi-headset"></i> Precisa de ajuda?</h6>
            <p class="mb-2">Entre em contato com nosso suporte para dúvidas sobre planos:</p>
            <a href="mailto:suporte@proservice.com.br" class="btn btn-outline-primary btn-sm">
                <i class="bi bi-envelope"></i> suporte@proservice.com.br
            </a>
        </div>
    </div>
</div>
