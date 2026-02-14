<?= breadcrumb(['Dashboard' => 'dashboard', 'Serviços']) ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1">Serviços</h4>
        <p class="text-muted mb-0">Gerencie seus serviços</p>
    </div>
    <a href="<?= url('servicos/create') ?>" class="btn btn-primary">
        <i class="bi bi-plus-lg"></i> Novo Serviço
    </a>
</div>

<!-- Serviços Mais Utilizados -->
<div class="card mb-4">
    <div class="card-header">
        <h6 class="mb-0"><i class="bi bi-star-fill text-warning"></i> Mais Utilizados</h6>
    </div>
    <div class="card-body">
        <div class="row g-2">
            <?php foreach (array_slice($maisUtilizados, 0, 5) as $servico): ?>
            <div class="col-md-4 col-lg-2">
                <div class="p-3 bg-light rounded text-center">
                    <div class="fw-medium text-truncate"><?= e($servico['nome']) ?></div>
                    <small class="text-muted"><?= $servico['total_os'] ?> OS</small>
                </div>
            </div>
            <?php endforeach; ?>
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
                    <input type="text" name="busca" class="form-control" placeholder="Buscar serviço..." value="<?= e($busca ?? '') ?>">
                    <button type="submit" class="btn btn-primary">Buscar</button>
                    <?php if (!empty($busca)): ?>
                        <a href="<?= url('servicos') ?>" class="btn btn-outline-secondary">Limpar</a>
                    <?php endif; ?>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Lista de Serviços -->
<div class="card">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>Serviço</th>
                    <th>Categoria</th>
                    <th>Valor Padrão</th>
                    <th>Garantia</th>
                    <th class="text-end">Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($servicos)): ?>
                    <tr>
                        <td colspan="5" class="text-center py-4 text-muted">
                            <i class="bi bi-inbox fs-1"></i>
                            <p class="mb-0">Nenhum serviço encontrado</p>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($servicos as $servico): ?>
                    <tr>
                        <td>
                            <div class="fw-medium"><?= e($servico['nome']) ?></div>
                            <?php if ($servico['descricao_padrao']): ?>
                                <small class="text-muted"><?= truncate($servico['descricao_padrao'], 50) ?></small>
                            <?php endif; ?>
                        </td>
                        <td><?= e($servico['categoria'] ?? '-') ?></td>
                        <td><?= formatMoney($servico['valor_padrao']) ?></td>
                        <td><?= $servico['garantia_dias'] ? $servico['garantia_dias'] . ' dias' : '-' ?></td>
                        <td class="text-end">
                            <div class="btn-group">
                                <a href="<?= url('servicos/edit/' . $servico['id']) ?>" class="btn btn-sm btn-outline-primary" title="Editar">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form method="POST" action="<?= url('servicos/duplicar/' . $servico['id']) ?>" class="d-inline">
                                    <?= csrfField() ?>
                                    <button type="submit" class="btn btn-sm btn-outline-info" title="Duplicar">
                                        <i class="bi bi-copy"></i>
                                    </button>
                                </form>
                            </div>
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
                    <a class="page-link" href="<?= url('servicos?page=' . $i . '&busca=' . urlencode($busca ?? '')) ?>"><?= $i ?></a>
                </li>
                <?php endfor; ?>
            </ul>
        </nav>
    </div>
    <?php endif; ?>
</div>
