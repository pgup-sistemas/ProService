<?php
/**
 * proService - Contrato Gerado (com tipos de assinatura)
 * Arquivo: /app/views/configuracoes/contrato_gerado.php
 */

// Buscar assinaturas da OS
$assinaturaModel = new \App\Models\Assinatura();
$assinaturas = $assinaturaModel->getByOs($os['id']);
$statusAssinaturas = $assinaturaModel->statusAssinaturas($os['id']);

$assinaturaAutorizacao = $assinaturaModel->getUltima($os['id'], 'autorizacao');
$assinaturaConformidade = $assinaturaModel->getUltima($os['id'], 'conformidade');
?>

<?= breadcrumb(['Dashboard' => 'dashboard', 'Ordens de Serviço' => 'ordens', 'OS #' . str_pad($os['numero_os'], 4, '0', STR_PAD_LEFT) => 'ordens/show/' . $os['id'], 'Contrato']) ?>

<div class="container-fluid">
    <div class="mb-4">
        <h2 class="mb-0"><i class="bi bi-file-text text-primary"></i> Contrato</h2>
        <p class="text-muted">OS #<?= str_pad($os['numero_os'], 4, '0', STR_PAD_LEFT) ?> - <?= e($cliente['nome'] ?? 'Cliente') ?></p>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-10">
            <!-- Botões de ação -->
            <div class="d-flex justify-content-between mb-3">
                <div>
                    <a href="<?= url('ordens/show/' . $os['id']) ?>" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left"></i> Voltar para OS
                    </a>
                </div>
                <div class="d-flex gap-2">
                    <button onclick="window.print()" class="btn btn-primary">
                        <i class="bi bi-printer"></i> Imprimir
                    </button>
                    <a href="<?= url('configuracoes/contrato') ?>" class="btn btn-outline-info">
                        <i class="bi bi-pencil"></i> Editar Template
                    </a>
                </div>
            </div>

            <!-- Status das Assinaturas -->
            <div class="card mb-3">
                <div class="card-header bg-light">
                    <h6 class="mb-0"><i class="bi bi-shield-check"></i> Status das Assinaturas Digitais</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <?php if ($statusAssinaturas['autorizacao']): ?>
                                <span class="badge bg-success fs-6"><i class="bi bi-check-circle"></i> Autorização OK</span>
                                <small class="d-block text-muted mt-1">
                                    Assinado por: <?= e($assinaturaAutorizacao['assinante_nome']) ?><br>
                                    Data: <?= date('d/m/Y H:i', strtotime($assinaturaAutorizacao['created_at'])) ?>
                                </small>
                            <?php else: ?>
                                <span class="badge bg-warning text-dark fs-6"><i class="bi bi-clock"></i> Autorização Pendente</span>
                                <small class="d-block text-muted mt-1">Cliente ainda não autorizou o serviço</small>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-6">
                            <?php if ($statusAssinaturas['conformidade']): ?>
                                <span class="badge bg-success fs-6"><i class="bi bi-check-circle"></i> Conformidade OK</span>
                                <small class="d-block text-muted mt-1">
                                    Assinado por: <?= e($assinaturaConformidade['assinante_nome']) ?><br>
                                    Data: <?= date('d/m/Y H:i', strtotime($assinaturaConformidade['created_at'])) ?>
                                </small>
                            <?php else: ?>
                                <span class="badge bg-secondary fs-6"><i class="bi bi-circle"></i> Conformidade Pendente</span>
                                <small class="d-block text-muted mt-1">Aguardando execução do serviço</small>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Contrato -->
            <div class="card">
                <div class="card-body p-5" id="contratoContent">
                    <?= $contrato ?>
                </div>
            </div>

            <!-- Assinaturas -->
            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-pen"></i> Assinaturas</h5>
                </div>
                <div class="card-body">
                    <!-- Assinatura de Autorização -->
                    <?php if ($assinaturaAutorizacao): ?>
                    <div class="row mb-4 border-bottom pb-3">
                        <div class="col-12">
                            <h6 class="text-primary"><i class="bi bi-check-square"></i> Assinatura de Autorização</h6>
                            <p class="small text-muted">O signatário abaixo autoriza a execução do serviço descrito neste contrato.</p>
                        </div>
                        <div class="col-md-6 text-center">
                            <img src="<?= url('files/assinatura/' . $assinaturaAutorizacao['id']) ?>" 
                                 alt="Assinatura de Autorização" 
                                 style="max-height: 80px; max-width: 100%; border-bottom: 1px solid #333; padding-bottom: 5px;">
                            <p class="mb-0 fw-bold mt-2"><?= e($assinaturaAutorizacao['assinante_nome']) ?></p>
                            <small class="text-muted">Autorização em <?= date('d/m/Y', strtotime($assinaturaAutorizacao['created_at'])) ?></small>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Assinatura de Conformidade -->
                    <?php if ($assinaturaConformidade): ?>
                    <div class="row">
                        <div class="col-12">
                            <h6 class="text-success"><i class="bi bi-check-circle-fill"></i> Assinatura de Conformidade</h6>
                            <p class="small text-muted">O signatário abaixo confirma que recebeu o serviço executado conforme contratado.</p>
                        </div>
                        <div class="col-md-6 text-center">
                            <img src="<?= url('files/assinatura/' . $assinaturaConformidade['id']) ?>" 
                                 alt="Assinatura de Conformidade" 
                                 style="max-height: 80px; max-width: 100%; border-bottom: 1px solid #333; padding-bottom: 5px;">
                            <p class="mb-0 fw-bold mt-2"><?= e($assinaturaConformidade['assinante_nome']) ?></p>
                            <small class="text-muted">Conformidade em <?= date('d/m/Y', strtotime($assinaturaConformidade['created_at'])) ?></small>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (!$assinaturaAutorizacao && !$assinaturaConformidade): ?>
                    <div class="text-center py-4">
                        <div class="row">
                            <div class="col-md-6 text-center">
                                <div class="border-bottom border-dark mb-2" style="height: 80px;"></div>
                                <p class="mb-0 fw-bold"><?= e($cliente['nome'] ?? 'Cliente') ?></p>
                                <small class="text-muted">Contratante</small>
                                <br><small class="text-warning"><i class="bi bi-exclamation-triangle"></i> Não assinado</small>
                            </div>
                            <div class="col-md-6 text-center">
                                <div class="border-bottom border-dark mb-2" style="height: 80px;"></div>
                                <p class="mb-0 fw-bold">Responsável Técnico</p>
                                <small class="text-muted">Contratada</small>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <div class="text-center mt-4 pt-3 border-top">
                        <p class="mb-0">Data: ____/____/________</p>
                        <small class="text-muted">Local: _________________________________</small>
                    </div>
                    
                    <?php if (!$assinaturaAutorizacao && in_array($os['status'], ['aberta', 'orcamento', 'aprovada'])): ?>
                    <div class="alert alert-warning mt-3">
                        <i class="bi bi-info-circle"></i> 
                        <strong>Atenção:</strong> Este contrato precisa ser assinado digitalmente como <strong>Autorização</strong>.
                        <a href="<?= url('ordens/assinatura/' . $os['id']) ?>" class="alert-link">
                            Clique aqui para assinar
                        </a>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Disclaimer -->
            <div class="alert alert-info mt-4">
                <i class="bi bi-info-circle"></i>
                <strong>Importante:</strong> Este contrato foi gerado automaticamente com base no template configurado. 
                Revise o conteúdo antes de imprimir ou enviar ao cliente. Para alterar o template, 
                acesse <a href="<?= url('configuracoes/contrato') ?>">Configurações > Contrato</a>.
            </div>
        </div>
    </div>
</div>

<style>
@media print {
    .btn, .alert, .card-header {
        display: none !important;
    }
    .card {
        border: none !important;
        box-shadow: none !important;
    }
    #contratoContent {
        padding: 0 !important;
    }
}
</style>
