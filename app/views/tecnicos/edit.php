<?= breadcrumb(['Dashboard' => 'dashboard', 'Técnicos' => 'tecnicos', 'Editar Técnico']) ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1"><i class="bi bi-pencil-square"></i> Editar Técnico</h4>
        <p class="text-muted mb-0">Atualize os dados do técnico</p>
    </div>
    <a href="<?= url('tecnicos') ?>" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left"></i> Voltar
    </a>
</div>

<?php if (!empty($_SESSION['errors'])): ?>
    <div class="alert alert-danger">
        <i class="bi bi-exclamation-triangle-fill"></i>
        <?php foreach ($_SESSION['errors'] as $err): ?>
            <div><?= e($err) ?></div>
        <?php endforeach; ?>
    </div>
    <?php unset($_SESSION['errors']); ?>
<?php endif; ?>

<div class="card">
    <div class="card-body">
        <form method="POST" action="<?= url('tecnicos/edit/' . $tecnico['id']) ?>">
            <?= csrfField() ?>

            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label"><i class="bi bi-person"></i> Nome *</label>
                    <input type="text" name="nome" class="form-control" value="<?= e($tecnico['nome']) ?>" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label"><i class="bi bi-envelope"></i> E-mail *</label>
                    <input type="email" name="email" class="form-control" value="<?= e($tecnico['email']) ?>" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label"><i class="bi bi-telephone"></i> Telefone</label>
                    <input type="text" name="telefone" class="form-control" value="<?= e($tecnico['telefone'] ?? '') ?>">
                </div>

                <div class="col-md-6">
                    <label class="form-label"><i class="bi bi-circle-fill"></i> Status</label>
                    <div class="form-control bg-light">
                        <?php if (!empty($tecnico['ativo'])): ?>
                            <span class="badge bg-success"><i class="bi bi-check-circle"></i> Ativo</span>
                        <?php else: ?>
                            <span class="badge bg-secondary"><i class="bi bi-x-circle"></i> Inativo</span>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="col-12">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-lg"></i> Salvar
                    </button>
                    <a href="<?= url('tecnicos') ?>" class="btn btn-outline-secondary">
                        <i class="bi bi-x-lg"></i> Cancelar
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>
