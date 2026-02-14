<?php
/**
 * proService - Onboarding Wizard
 * Arquivo: /app/views/dashboard/onboarding.php
 */

$steps = [
    1 => ['icon' => 'bi-building', 'title' => 'Dados da Empresa', 'desc' => 'Complete seus dados com logo e informações de contato'],
    2 => ['icon' => 'bi-tools', 'title' => 'Primeiro Serviço', 'desc' => 'Cadastre um serviço que você presta'],
    3 => ['icon' => 'bi-person', 'title' => 'Primeiro Cliente', 'desc' => 'Adicione um cliente à sua base'],
    4 => ['icon' => 'bi-clipboard-plus', 'title' => 'Primeira OS', 'desc' => 'Crie sua primeira ordem de serviço'],
    5 => ['icon' => 'bi-check-circle', 'title' => 'Pronto!', 'desc' => 'Você está pronto para começar'],
];

$currentStep = $progresso['etapa_atual'] ?? 1;
$totalSteps = count($steps);
$percentual = ($currentStep / $totalSteps) * 100;
?>

<!-- Onboarding Modal -->
<div class="modal fade" id="onboardingModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h5 class="modal-title">⚡ Bem-vindo ao proService!</h5>
            </div>
            <div class="modal-body">
                <!-- Progress Bar -->
                <div class="progress mb-4" style="height: 8px;">
                    <div class="progress-bar bg-primary" style="width: <?= $percentual ?>%"></div>
                </div>
                
                <!-- Steps -->
                <div class="row g-3 mb-4">
                    <?php foreach ($steps as $num => $step): ?>
                    <div class="col">
                        <div class="text-center <?= $num == $currentStep ? 'opacity-100' : ($num < $currentStep ? 'opacity-50' : 'opacity-25') ?>">
                            <div class="rounded-circle d-flex align-items-center justify-content-center mx-auto mb-2 
                                <?= $num <= $currentStep ? 'bg-primary text-white' : 'bg-light text-muted' ?>" 
                                style="width: 50px; height: 50px;">
                                <i class="bi <?= $step['icon'] ?> fs-4"></i>
                            </div>
                            <small class="fw-medium d-block"><?= $step['title'] ?></small>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                
                <!-- Current Step Content -->
                <div class="card bg-light border-0">
                    <div class="card-body p-4">
                        <?php if ($currentStep == 1): ?>
                            <h4><i class="bi bi-building text-primary"></i> Configure sua Empresa</h4>
                            <p class="text-muted">Vamos começar completando os dados da sua empresa. Isso aparecerá nos documentos e recibos.</p>
                            <div class="d-flex gap-2">
                                <a href="<?= url('configuracoes/empresa') ?>" class="btn btn-primary">
                                    <i class="bi bi-pencil"></i> Completar Dados
                                </a>
                                <button onclick="pularEtapa()" class="btn btn-outline-secondary">Pular por agora</button>
                            </div>
                            
                        <?php elseif ($currentStep == 2): ?>
                            <h4><i class="bi bi-tools text-primary"></i> Cadastre um Serviço</h4>
                            <p class="text-muted">Cadastre o primeiro tipo de serviço que você presta (ex: Manutenção de Ar-Condicionado).</p>
                            <div class="d-flex gap-2">
                                <a href="<?= url('servicos/create') ?>" class="btn btn-primary">
                                    <i class="bi bi-plus-lg"></i> Cadastrar Serviço
                                </a>
                                <button onclick="pularEtapa()" class="btn btn-outline-secondary">Pular por agora</button>
                            </div>
                            
                        <?php elseif ($currentStep == 3): ?>
                            <h4><i class="bi bi-person text-primary"></i> Adicione um Cliente</h4>
                            <p class="text-muted">Vamos adicionar seu primeiro cliente. Você pode usar dados fictícios para teste e remover depois.</p>
                            <div class="d-flex gap-2">
                                <a href="<?= url('clientes/create') ?>" class="btn btn-primary">
                                    <i class="bi bi-plus-lg"></i> Adicionar Cliente
                                </a>
                                <button onclick="pularEtapa()" class="btn btn-outline-secondary">Pular por agora</button>
                            </div>
                            
                        <?php elseif ($currentStep == 4): ?>
                            <h4><i class="bi bi-clipboard-plus text-primary"></i> Crie sua Primeira OS</h4>
                            <p class="text-muted">Agora vamos criar sua primeira Ordem de Serviço usando o cliente e serviço cadastrados.</p>
                            <div class="d-flex gap-2">
                                <a href="<?= url('ordens/create') ?>" class="btn btn-primary">
                                    <i class="bi bi-plus-lg"></i> Criar OS
                                </a>
                                <button onclick="pularEtapa()" class="btn btn-outline-secondary">Pular por agora</button>
                            </div>
                            
                        <?php else: ?>
                            <div class="text-center py-4">
                                <i class="bi bi-check-circle-fill text-success" style="font-size: 4rem;"></i>
                                <h4 class="mt-3">Parabéns! 🎉</h4>
                                <p class="text-muted">Você completou o setup inicial do proService.<br>Agora você está pronto para gerenciar seus serviços!</p>
                                <button onclick="finalizarOnboarding()" class="btn btn-primary btn-lg">
                                    <i class="bi bi-rocket"></i> Ir para o Dashboard
                                </button>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Checklist -->
                <?php if ($currentStep < 5): ?>
                <div class="mt-4">
                    <h6>Seu progresso:</h6>
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex align-items-center <?= $progresso['logo'] ? 'text-success' : 'text-muted' ?>">
                            <i class="bi <?= $progresso['logo'] ? 'bi-check-circle-fill' : 'bi-circle' ?> me-2"></i>
                            Logo da empresa enviada
                        </li>
                        <li class="list-group-item d-flex align-items-center <?= $progresso['servico'] ? 'text-success' : 'text-muted' ?>">
                            <i class="bi <?= $progresso['servico'] ? 'bi-check-circle-fill' : 'bi-circle' ?> me-2"></i>
                            Pelo menos 1 serviço cadastrado
                        </li>
                        <li class="list-group-item d-flex align-items-center <?= $progresso['cliente'] ? 'text-success' : 'text-muted' ?>">
                            <i class="bi <?= $progresso['cliente'] ? 'bi-check-circle-fill' : 'bi-circle' ?> me-2"></i>
                            Pelo menos 1 cliente cadastrado
                        </li>
                        <li class="list-group-item d-flex align-items-center <?= $progresso['os'] ? 'text-success' : 'text-muted' ?>">
                            <i class="bi <?= $progresso['os'] ? 'bi-check-circle-fill' : 'bi-circle' ?> me-2"></i>
                            Primeira OS criada
                        </li>
                    </ul>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
// Mostrar modal automaticamente
const onboardingModal = new bootstrap.Modal(document.getElementById('onboardingModal'));
onboardingModal.show();

function pularEtapa() {
    fetch('<?= url('api/onboarding/pular') ?>', {method: 'POST'})
        .then(() => window.location.reload());
}

function finalizarOnboarding() {
    fetch('<?= url('api/onboarding/finalizar') ?>', {method: 'POST'})
        .then(() => {
            onboardingModal.hide();
            window.location.href = '<?= url('dashboard') ?>';
        });
}
</script>
