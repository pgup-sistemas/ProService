<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($titulo) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f8f9fa; }
        .status-card {
            border-radius: 16px;
            padding: 30px;
            text-align: center;
        }
        .status-aberta { background: #6c757d; color: white; }
        .status-em_execucao { background: #ffc107; color: #000; }
        .status-finalizada { background: #198754; color: white; }
        .status-paga { background: #0d6efd; color: white; }
    </style>
</head>
<body>
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">
                <!-- Header -->
                <div class="text-center mb-4">
                    <?php if ($os['empresa_logo']): ?>
                        <img src="<?= uploadUrl($os['empresa_logo']) ?>" alt="Logo" height="60" class="mb-3">
                    <?php else: ?>
                        <h3 class="mb-1">⚡ <?= e($os['empresa_nome'] ?? APP_NAME) ?></h3>
                    <?php endif; ?>
                    <p class="text-muted">Acompanhamento de Ordem de Serviço</p>
                </div>
                
                <!-- OS Info -->
                <div class="card shadow-sm mb-4">
                    <div class="card-body text-center">
                        <h1 class="display-4 mb-2">#<?= str_pad($os['numero_os'], 4, '0', STR_PAD_LEFT) ?></h1>
                        <div class="status-card status-<?= $os['status'] ?> mb-3">
                            <h4 class="mb-0"><?= getStatusLabel($os['status']) ?></h4>
                        </div>
                        <p class="text-muted">
                            Última atualização: <?= formatDateTime($os['updated_at']) ?>
                        </p>
                    </div>
                </div>
                
                <!-- Detalhes -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="bi bi-info-circle"></i> Detalhes do Serviço</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="text-muted small">Cliente</label>
                            <p class="fw-medium mb-0"><?= e($os['cliente_nome']) ?></p>
                        </div>
                        <div class="mb-3">
                            <label class="text-muted small">Serviço</label>
                            <p class="fw-medium mb-0"><?= e($os['servico_nome'] ?? '-') ?></p>
                        </div>
                        <?php if ($os['descricao']): ?>
                        <div class="mb-3">
                            <label class="text-muted small">Descrição</label>
                            <p><?= nl2br(e($os['descricao'])) ?></p>
                        </div>
                        <?php endif; ?>
                        <?php if ($os['observacoes_cliente']): ?>
                        <div class="alert alert-info">
                            <label class="fw-medium"><i class="bi bi-chat-dots"></i> Observações</label>
                            <p class="mb-0"><?= nl2br(e($os['observacoes_cliente'])) ?></p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Valores -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="bi bi-cash"></i> Valores</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-2">
                            <span>Valor do Serviço:</span>
                            <span><?= formatMoney($os['valor_servico']) ?></span>
                        </div>
                        <?php if ($os['taxas_adicionais'] > 0): ?>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Taxas Adicionais:</span>
                            <span><?= formatMoney($os['taxas_adicionais']) ?></span>
                        </div>
                        <?php endif; ?>
                        <?php if ($os['desconto'] > 0): ?>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Desconto:</span>
                            <span class="text-danger">-<?= formatMoney($os['desconto']) ?></span>
                        </div>
                        <?php endif; ?>
                        <hr>
                        <div class="d-flex justify-content-between">
                            <span class="fw-bold">Valor Total:</span>
                            <span class="fw-bold fs-5"><?= formatMoney($os['valor_total']) ?></span>
                        </div>
                    </div>
                </div>
                
                <!-- Produtos -->
                <?php if (!empty($produtos)): ?>
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="bi bi-box-seam"></i> Produtos Utilizados</h5>
                    </div>
                    <div class="card-body">
                        <ul class="list-group list-group-flush">
                            <?php foreach ($produtos as $p): ?>
                            <li class="list-group-item d-flex justify-content-between">
                                <span><?= e($p['produto_nome']) ?> (<?= $p['quantidade'] ?>)</span>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- Contato -->
                <div class="card shadow-sm">
                    <div class="card-body text-center">
                        <p class="text-muted mb-2">Dúvidas? Entre em contato</p>
                        <?php if ($os['empresa_telefone']): ?>
                        <a href="tel:<?= preg_replace('/\D/', '', $os['empresa_telefone']) ?>" class="btn btn-outline-primary">
                            <i class="bi bi-telephone"></i> <?= formatPhone($os['empresa_telefone']) ?>
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="text-center mt-4 text-muted small">
                    <p>© <?= date('Y') ?> <?= e($os['empresa_nome'] ?? APP_NAME) ?></p>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
