<?= breadcrumb(['Dashboard' => 'dashboard', 'Produtos / Import Jobs']) ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1">Jobs de Importação</h4>
        <p class="text-muted mb-0">Acompanhe arquivos enfileirados e históricos de importação</p>
    </div>
    <div>
        <a href="<?= url('produtos') ?>" class="btn btn-outline-secondary">&larr; Voltar</a>
    </div>
</div>

<form method="GET" class="row g-2 mb-3">
    <div class="col-auto">
        <select name="status" class="form-select">
            <option value="">Todos os status</option>
            <option value="pending" <?= (!empty($filter_status) && $filter_status === 'pending') ? 'selected' : '' ?>>Pendente</option>
            <option value="processing" <?= (!empty($filter_status) && $filter_status === 'processing') ? 'selected' : '' ?>>Processando</option>
            <option value="completed" <?= (!empty($filter_status) && $filter_status === 'completed') ? 'selected' : '' ?>>Concluído</option>
            <option value="failed" <?= (!empty($filter_status) && $filter_status === 'failed') ? 'selected' : '' ?>>Falhou</option>
            <option value="cancelled" <?= (!empty($filter_status) && $filter_status === 'cancelled') ? 'selected' : '' ?>>Cancelado</option>
        </select>
    </div>
    <div class="col">
        <input type="search" name="q" class="form-control" placeholder="Pesquisar por nome de arquivo" value="<?= e($filter_q ?? '') ?>">
    </div>
    <div class="col-auto">
        <button type="submit" class="btn btn-primary">Filtrar</button>
        <a href="<?= url('produtos/import-jobs') ?>" class="btn btn-outline-secondary ms-2">Limpar</a>
    </div>
</form>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Arquivo</th>
                    <th>Tipo</th>
                    <th>Status</th>
                    <th>Linhas</th>
                    <th>Progresso</th>
                    <th>Criado em</th>
                    <th class="text-end">Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($jobs)): ?>
                    <tr>
                        <td colspan="8" class="text-center py-4 text-muted">Nenhum job encontrado</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($jobs as $job): ?>
                        <tr>
                            <td><?= $job['id'] ?></td>
                            <td><?= e($job['original_filename']) ?></td>
                            <td><?= e($job['type']) ?></td>
                            <td>
                                <?php if ($job['status'] === 'pending'): ?><span class="badge bg-secondary">Pendente</span>
                                <?php elseif ($job['status'] === 'processing'): ?><span class="badge bg-primary">Em processamento</span>
                                <?php elseif ($job['status'] === 'completed'): ?><span class="badge bg-success">Concluído</span>
                                <?php else: ?><span class="badge bg-danger"><?= e($job['status']) ?></span>
                                <?php endif; ?>
                            </td>
                            <td><?= (int)$job['processed_rows'] ?> / <?= (int)$job['total_rows'] ?></td>
                            <td style="min-width:160px;">
                                <div class="progress" style="height:10px;">
                                    <div class="progress-bar bg-success" role="progressbar" style="width: <?= (float)$job['progress'] ?>%;" aria-valuenow="<?= (float)$job['progress'] ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                            </td>
                            <td><?= $job['created_at'] ?></td>
                            <td class="text-end">
                                <a href="<?= url('produtos/import-jobs/' . $job['id']) ?>" class="btn btn-sm btn-outline-primary">Ver</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <?php if ($paginacao['last_page'] > 1): ?>
    <div class="card-footer">
        <nav aria-label="Paginação">
            <ul class="pagination justify-content-center mb-0">
                <?php
                    $extra = [];
                    if (!empty($filter_status)) $extra['status'] = $filter_status;
                    if (!empty($filter_q)) $extra['q'] = $filter_q;
                    $qs = http_build_query($extra);
                ?>
                <?php for ($i = 1; $i <= $paginacao['last_page']; $i++): ?>
                <li class="page-item <?= $i === $paginacao['page'] ? 'active' : '' ?>">
                    <a class="page-link" href="<?= url('produtos/import-jobs?page=' . $i . ($qs ? '&' . $qs : '')) ?>"><?= $i ?></a>
                </li>
                <?php endfor; ?>
            </ul>
        </nav>
    </div>
    <?php endif; ?>
</div>
