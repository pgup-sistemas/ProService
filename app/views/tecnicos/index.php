<?= breadcrumb(['Dashboard' => 'dashboard', 'Técnicos']) ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1">Técnicos</h4>
        <p class="text-muted mb-0">Gerencie sua equipe técnica</p>
    </div>
    <a href="<?= url('tecnicos/create') ?>" class="btn btn-primary">
        <i class="bi bi-person-plus"></i> Novo Técnico
    </a>
</div>

<!-- Estatísticas -->
<div class="row g-3 mb-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <div class="bg-primary bg-opacity-10 p-3 rounded">
                            <i class="bi bi-people text-primary fs-4"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h6 class="mb-0"><?= count(array_filter($tecnicos, fn($t) => !empty($t['ativo']))) ?></h6>
                        <small class="text-muted">Técnicos Ativos</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <div class="bg-secondary bg-opacity-10 p-3 rounded">
                            <i class="bi bi-person-slash text-secondary fs-4"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h6 class="mb-0"><?= count(array_filter($tecnicos, fn($t) => empty($t['ativo']))) ?></h6>
                        <small class="text-muted">Inativos</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php if (!empty($_SESSION['errors'])): ?>
    <div class="alert alert-danger">
        <?php foreach ($_SESSION['errors'] as $err): ?>
            <div><?= e($err) ?></div>
        <?php endforeach; ?>
    </div>
    <?php unset($_SESSION['errors']); ?>
<?php endif; ?>

<?php if (empty($tecnicos)): ?>
    <div class="alert alert-info mb-0">
        <i class="bi bi-info-circle"></i> Nenhum técnico cadastrado.
        <a href="<?= url('tecnicos/create') ?>" class="alert-link">Cadastre o primeiro</a>
    </div>
<?php else: ?>
    <div class="card">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th><i class="bi bi-person"></i> Nome</th>
                        <th><i class="bi bi-envelope"></i> E-mail</th>
                        <th><i class="bi bi-telephone"></i> Telefone</th>
                        <th><i class="bi bi-circle-fill"></i> Status</th>
                        <th class="text-end"><i class="bi bi-gear"></i> Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($tecnicos as $t): ?>
                        <tr>
                            <td class="fw-medium"><?= e($t['nome']) ?></td>
                            <td><?= e($t['email']) ?></td>
                            <td><?= e($t['telefone'] ?? '-') ?></td>
                            <td>
                                <?php if (!empty($t['ativo'])): ?>
                                    <span class="badge bg-success"><i class="bi bi-check-circle"></i> Ativo</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary"><i class="bi bi-x-circle"></i> Inativo</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end">
                                <div class="d-inline-flex gap-1">
                                    <a href="<?= url('tecnicos/edit/' . $t['id']) ?>" class="btn btn-sm btn-outline-primary" title="Editar">
                                        <i class="bi bi-pencil"></i>
                                    </a>

                                    <form method="POST" action="<?= url('tecnicos/toggle/' . $t['id']) ?>" class="d-inline">
                                        <?= csrfField() ?>
                                        <button type="submit" class="btn btn-sm btn-outline-warning" title="Ativar/Desativar">
                                            <i class="bi bi-power"></i>
                                        </button>
                                    </form>

                                    <form method="POST" action="<?= url('tecnicos/reset-senha/' . $t['id']) ?>" class="d-inline" onsubmit="return confirm('Resetar senha para proservice123?');">
                                        <?= csrfField() ?>
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Resetar Senha">
                                            <i class="bi bi-key"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <?php if ($paginacao['last_page'] > 1): ?>
        <div class="card-footer">
            <nav aria-label="Paginação">
                <ul class="pagination justify-content-center mb-0">
                    <?php for ($i = 1; $i <= $paginacao['last_page']; $i++): ?>
                    <li class="page-item <?= $i === $paginacao['page'] ? 'active' : '' ?>">
                        <a class="page-link" href="<?= url('tecnicos?page=' . $i) ?>"><?= $i ?></a>
                    </li>
                    <?php endfor; ?>
                </ul>
            </nav>
        </div>
        <?php endif; ?>
    </div>
<?php endif; ?>
