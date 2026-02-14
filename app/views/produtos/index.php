<?= breadcrumb(['Dashboard' => 'dashboard', 'Produtos / Estoque']) ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1">Produtos / Estoque</h4>
        <p class="text-muted mb-0">Gerencie seu estoque</p>
    </div>
    <a href="<?= url('produtos/create') ?>" class="btn btn-primary">
        <i class="bi bi-plus-lg"></i> Novo Produto
    </a>
</div>

<?php $flash = getFlash(); ?>
<?php if ($flash): ?>
<div class="alert alert-<?= $flash['type'] ?> alert-dismissible fade show" role="alert">
    <?= e($flash['message']) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<!-- Estatísticas -->
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <div class="bg-primary bg-opacity-10 p-3 rounded">
                            <i class="bi bi-box-seam text-primary fs-4"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h6 class="mb-0"><?= $estatisticas['total'] ?></h6>
                        <small class="text-muted">Produtos Ativos</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <div class="bg-warning bg-opacity-10 p-3 rounded">
                            <i class="bi bi-exclamation-triangle text-warning fs-4"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h6 class="mb-0"><?= $estatisticas['em_falta'] ?></h6>
                        <small class="text-muted">Produtos em Falta</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <div class="bg-success bg-opacity-10 p-3 rounded">
                            <i class="bi bi-currency-dollar text-success fs-4"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h6 class="mb-0"><?= formatMoney($estatisticas['valor_total']) ?></h6>
                        <small class="text-muted">Valor em Estoque</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Busca -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-8">
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                    <input type="text" name="busca" class="form-control" placeholder="Buscar produto..." value="<?= e($busca ?? '') ?>">
                    <button type="submit" class="btn btn-primary">Buscar</button>
                    <?php if (!empty($busca)): ?>
                        <a href="<?= url('produtos') ?>" class="btn btn-outline-secondary">Limpar</a>
                    <?php endif; ?>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Lista de Produtos -->
<div class="card">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>Produto</th>
                    <th>Código</th>
                    <th>Estoque</th>
                    <th>Custo</th>
                    <th class="text-end">Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($produtos)): ?>
                    <tr>
                        <td colspan="5" class="text-center py-4 text-muted">
                            <i class="bi bi-inbox fs-1"></i>
                            <p class="mb-0">Nenhum produto encontrado</p>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($produtos as $produto): ?>
                    <tr class="<?= $produto['quantidade_estoque'] <= $produto['quantidade_minima'] ? 'table-warning' : '' ?>">
                        <td>
                            <div class="fw-medium"><?= e($produto['nome']) ?></div>
                            <?php if ($produto['categoria']): ?>
                                <small class="text-muted"><?= e($produto['categoria']) ?></small>
                            <?php endif; ?>
                            <?php if ($produto['quantidade_estoque'] <= $produto['quantidade_minima']): ?>
                                <span class="badge bg-warning text-dark ms-2">Estoque Baixo</span>
                            <?php endif; ?>
                        </td>
                        <td><?= e($produto['codigo_sku'] ?? '-') ?></td>
                        <td>
                            <?= $produto['quantidade_estoque'] ?> <?= e($produto['unidade']) ?>
                            <?php if ($produto['quantidade_minima'] > 0): ?>
                                <br><small class="text-muted">Mín: <?= $produto['quantidade_minima'] ?></small>
                            <?php endif; ?>
                        </td>
                        <td><?= formatMoney($produto['custo_unitario']) ?></td>
                        <td class="text-end">
                            <a href="<?= url('produtos/edit/' . $produto['id']) ?>" class="btn btn-sm btn-outline-primary" title="Editar">
                                <i class="bi bi-pencil"></i>
                            </a>
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
                <?php for ($i = 1; $i <= $paginacao['last_page']; $i++): ?>
                <li class="page-item <?= $i === $paginacao['page'] ? 'active' : '' ?>">
                    <a class="page-link" href="<?= url('produtos?page=' . $i . '&busca=' . urlencode($busca ?? '')) ?>"><?= $i ?></a>
                </li>
                <?php endfor; ?>
            </ul>
        </nav>
    </div>
    <?php endif; ?>
</div>
