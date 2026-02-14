<?= breadcrumb(['Dashboard' => 'dashboard', 'Técnicos' => 'tecnicos', 'Novo Técnico']) ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1"><i class="bi bi-person-plus"></i> Novo Técnico</h4>
        <p class="text-muted mb-0">Cadastre um novo membro da equipe técnica</p>
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
        <form method="POST" action="<?= url('tecnicos/create') ?>">
            <?= csrfField() ?>

            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label"><i class="bi bi-person"></i> Nome *</label>
                    <input type="text" name="nome" class="form-control" value="<?= e($old['nome'] ?? '') ?>" placeholder="Nome completo do técnico" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label"><i class="bi bi-envelope"></i> E-mail *</label>
                    <input type="email" name="email" class="form-control" value="<?= e($old['email'] ?? '') ?>" placeholder="email@exemplo.com" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label"><i class="bi bi-telephone"></i> Telefone</label>
                    <input type="text" name="telefone" class="form-control" value="<?= e($old['telefone'] ?? '') ?>" placeholder="(00) 00000-0000">
                </div>

                <div class="col-md-6">
                    <label class="form-label"><i class="bi bi-lock"></i> Senha *</label>
                    <input type="password" name="senha" class="form-control" minlength="8" placeholder="Mínimo 8 caracteres" required>
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
