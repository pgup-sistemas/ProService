<?= breadcrumb(['Dashboard' => 'dashboard', 'Produtos' => 'produtos', 'Job #' . $job['id']]) ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1">Job de Importação #<?= $job['id'] ?></h4>
        <small class="text-muted">Arquivo: <?= e($job['original_filename']) ?> — Status: <strong><?= e($job['status']) ?></strong></small>
    </div>
    <div>
        <a href="<?= url('produtos/import-jobs') ?>" class="btn btn-outline-secondary">&larr; Voltar</a>
    </div>
</div>

<div class="row g-3">
    <div class="col-md-6">
        <div class="card mb-3">
            <div class="card-body">
                <p><strong>Tipo:</strong> <?= e($job['type']) ?></p>
                <p><strong>Criado por (user_id):</strong> <?= e($job['user_id']) ?></p>
                <p><strong>Linhas processadas:</strong> <?= (int)$job['processed_rows'] ?> / <?= (int)$job['total_rows'] ?></p>
                <p><strong>Progresso:</strong> <?= (float)$job['progress'] ?>%</p>
                <p><strong>Criado em:</strong> <?= $job['created_at'] ?></p>
                <p><strong>Iniciado em:</strong> <?= $job['started_at'] ?? '-' ?></p>
                <p><strong>Finalizado em:</strong> <?= $job['finished_at'] ?? '-' ?></p>
                <?php if (!empty($job['stored_path'])): ?>
                    <p><a href="<?= APP_URL . '/public/uploads/imports/' . basename($job['stored_path']) ?>" target="_blank" class="btn btn-sm btn-outline-secondary">Baixar arquivo</a></p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card mb-3">
            <div class="card-body">
                <h6>Resultado / Erros</h6>
                <?php if (!empty($job['result_json'])): ?>
                    <?php $r = json_decode($job['result_json'], true); ?>
                    <pre class="small bg-light p-2" style="max-height:300px; overflow:auto;"><?= e(json_encode($r, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) ?></pre>
                <?php elseif (!empty($job['error_text'])): ?>
                    <pre class="text-danger small bg-light p-2" style="max-height:300px; overflow:auto;"><?= e($job['error_text']) ?></pre>
                <?php else: ?>
                    <div class="text-muted small">Nenhum detalhe disponível.</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>