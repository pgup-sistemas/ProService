<?php
/**
 * Página de gerenciamento da assinatura
 * @var array $empresa Dados da empresa
 * @var array|null $assinatura Dados da assinatura EfiPay
 * @var array $historico Histórico de pagamentos
 * @var array|null $plano Dados do plano atual
 */
?>

<div class="container-fluid py-4">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= url('dashboard') ?>">Dashboard</a></li>
            <li class="breadcrumb-item active" aria-current="page">Minha Assinatura</li>
        </ol>
    </nav>

    <div class="row g-4">
        <!-- Status da Assinatura -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-shield-check me-2"></i>Status da Assinatura</h5>
                </div>
                <div class="card-body">
                    <?php if ($empresa['plano'] === 'trial'): ?>
                        <div class="text-center">
                            <div class="display-6 text-warning mb-3">
                                <i class="bi bi-clock-history"></i>
                            </div>
                            <h5>Período de Trial</h5>
                            <p class="text-muted">
                                Você está usando o período de teste gratuito.
                            </p>
                            <a href="<?= url('assinaturas') ?>" class="btn btn-primary">
                                <i class="bi bi-arrow-up-circle me-2"></i>Escolher Plano
                            </a>
                        </div>

                    <?php elseif ($empresa['plano'] === 'inactive'): ?>
                        <div class="text-center">
                            <div class="display-6 text-danger mb-3">
                                <i class="bi bi-x-circle"></i>
                            </div>
                            <h5>Assinatura Inativa</h5>
                            <p class="text-muted">
                                Sua assinatura está inativa. Reative para continuar usando.
                            </p>
                            <a href="<?= url('assinaturas') ?>" class="btn btn-primary">
                                <i class="bi bi-arrow-up-circle me-2"></i>Reativar Assinatura
                            </a>
                        </div>

                    <?php elseif (!empty($assinatura)): ?>
                        <?php
                        $statusClass = [
                            'active' => 'success',
                            'paused' => 'warning',
                            'cancelled' => 'danger',
                            'pending' => 'info'
                        ][$assinatura['status']] ?? 'secondary';
                        
                        $statusLabel = [
                            'active' => 'Ativa',
                            'paused' => 'Pausada',
                            'cancelled' => 'Cancelada',
                            'pending' => 'Pendente'
                        ][$assinatura['status']] ?? $assinatura['status'];
                        ?>
                        
                        <div class="text-center mb-4">
                            <span class="badge bg-<?= $statusClass ?> fs-6 px-3 py-2">
                                <i class="bi bi-circle-fill me-2"></i><?= $statusLabel ?>
                            </span>
                        </div>

                        <ul class="list-group list-group-flush">
                            <li class="list-group-item d-flex justify-content-between">
                                <span class="text-muted">Plano</span>
                                <span class="fw-bold"><?= $plano['nome'] ?? $empresa['plano_nome'] ?></span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <span class="text-muted">Valor Mensal</span>
                                <span class="fw-bold">
                                    R$ <?= number_format(($plano['preco'] ?? 0) / 100, 2, ',', '.') ?>
                                </span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <span class="text-muted">Início</span>
                                <span><?= !empty($empresa['assinatura_inicio']) ? date('d/m/Y', strtotime($empresa['assinatura_inicio'])) : '-' ?></span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <span class="text-muted">Próxima Cobrança</span>
                                <span>
                                    <?= !empty($assinatura['next_charge_at']) ? date('d/m/Y', strtotime($assinatura['next_charge_at'])) : '-' ?>
                                </span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <span class="text-muted">ID da Assinatura</span>
                                <span class="font-monospace small"><?= $empresa['assinatura_id'] ?></span>
                            </li>
                        </ul>

                        <?php if ($assinatura['status'] === 'active'): ?>
                            <div class="d-grid gap-2 mt-4">
                                <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#cancelarModal">
                                    <i class="bi bi-x-circle me-2"></i>Cancelar Assinatura
                                </button>
                            </div>
                        <?php endif; ?>

                    <?php else: ?>
                        <div class="text-center text-muted">
                            <i class="bi bi-info-circle fs-1 mb-3 d-block"></i>
                            <p>Nenhuma informação de assinatura disponível.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Recursos do Plano -->
            <?php if (!empty($plano)): ?>
                <div class="card border-0 shadow-sm mt-4">
                    <div class="card-header bg-white border-bottom">
                        <h6 class="mb-0"><i class="bi bi-stars me-2"></i>Recursos Incluídos</h6>
                    </div>
                    <div class="card-body">
                        <ul class="list-unstyled mb-0">
                            <?php foreach ($plano['recursos'] as $recurso): ?>
                                <li class="mb-2">
                                    <i class="bi bi-check-circle-fill text-success me-2"></i>
                                    <?= $recurso ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Histórico de Pagamentos -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-clock-history me-2"></i>Histórico de Pagamentos</h5>
                    <?php if (!empty($historico)): ?>
                        <span class="badge bg-secondary"><?= count($historico) ?> pagamentos</span>
                    <?php endif; ?>
                </div>
                <div class="card-body p-0">
                    <?php if (!empty($historico)): ?>
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Data</th>
                                        <th>Descrição</th>
                                        <th>Valor</th>
                                        <th>Status</th>
                                        <th>Método</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($historico as $pagamento): 
                                        $statusPag = $pagamento['status'] ?? 'unknown';
                                        $statusPagClass = [
                                            'paid' => 'success',
                                            'pending' => 'warning',
                                            'failed' => 'danger',
                                            'refunded' => 'info'
                                        ][$statusPag] ?? 'secondary';
                                        
                                        $statusPagLabel = [
                                            'paid' => 'Pago',
                                            'pending' => 'Pendente',
                                            'failed' => 'Falhou',
                                            'refunded' => 'Estornado'
                                        ][$statusPag] ?? $statusPag;
                                    ?>
                                        <tr>
                                            <td>
                                                <?= !empty($pagamento['created_at']) ? date('d/m/Y', strtotime($pagamento['created_at'])) : '-' ?>
                                            </td>
                                            <td>
                                                <?= $pagamento['description'] ?? 'Assinatura ' . ($plano['nome'] ?? '') ?>
                                            </td>
                                            <td class="fw-bold">
                                                R$ <?= number_format(($pagamento['value'] ?? 0) / 100, 2, ',', '.') ?>
                                            </td>
                                            <td>
                                                <span class="badge bg-<?= $statusPagClass ?>">
                                                    <?= $statusPagLabel ?>
                                                </span>
                                            </td>
                                            <td>
                                                <i class="bi bi-credit-card me-1"></i>
                                                Cartão de Crédito
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-5 text-muted">
                            <i class="bi bi-receipt fs-1 mb-3 d-block"></i>
                            <p class="mb-0">Nenhum pagamento encontrado.</p>
                            <?php if ($empresa['plano'] === 'trial'): ?>
                                <small>Os pagamentos aparecerão aqui após sua primeira assinatura.</small>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Ações Rápidas -->
            <div class="card border-0 shadow-sm mt-4">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0"><i class="bi bi-lightning me-2"></i>Ações Rápidas</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <a href="<?= url('assinaturas') ?>" class="btn btn-outline-primary w-100">
                                <i class="bi bi-arrow-up-circle me-2"></i>Alterar Plano
                            </a>
                        </div>
                        <div class="col-md-6">
                            <a href="<?= url('configuracoes/empresa') ?>" class="btn btn-outline-secondary w-100">
                                <i class="bi bi-building me-2"></i>Dados da Empresa
                            </a>
                        </div>
                        <div class="col-md-6">
                            <a href="mailto:suporte@proservice.com.br" class="btn btn-outline-info w-100">
                                <i class="bi bi-headset me-2"></i>Suporte
                            </a>
                        </div>
                        <div class="col-md-6">
                            <button type="button" class="btn btn-outline-dark w-100" onclick="window.print()">
                                <i class="bi bi-printer me-2"></i>Imprimir
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Cancelamento -->
<div class="modal fade" id="cancelarModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-danger">
                    <i class="bi bi-exclamation-triangle me-2"></i>Cancelar Assinatura
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?= url('assinaturas/cancelar') ?>" method="POST">
                <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
                
                <div class="modal-body">
                    <p>Tem certeza que deseja cancelar sua assinatura?</p>
                    
                    <div class="alert alert-warning">
                        <strong>Atenção:</strong>
                        <ul class="mb-0 mt-2">
                            <li>Você terá acesso até o final do período pago</li>
                            <li>Não haverá reembolso do valor proporcional</li>
                            <li>Seus dados serão preservados por 30 dias</li>
                            <li>Você pode reativar a qualquer momento</li>
                        </ul>
                    </div>

                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="confirmarCancelamento" required>
                        <label class="form-check-label" for="confirmarCancelamento">
                            Entendo que ao cancelar, minha assinatura será encerrada no fim do período atual.
                        </label>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        Voltar
                    </button>
                    <button type="submit" class="btn btn-danger">
                        <i class="bi bi-x-circle me-2"></i>Confirmar Cancelamento
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
