<?php
/**
 * proService - Criar Usuário
 */
?>

<?= breadcrumb(['Dashboard' => 'dashboard', 'Gestão de Usuários' => 'usuarios', 'Novo Usuário']) ?>

<div class="container-fluid">
    <div class="d-flex align-items-center mb-4">
        <a href="<?= url('usuarios') ?>" class="btn btn-outline-secondary btn-sm me-3">
            <i class="bi bi-arrow-left"></i>
        </a>
        <div>
            <h4 class="mb-0"><i class="bi bi-person-plus"></i> Novo Usuário</h4>
            <p class="text-muted mb-0">Cadastre um novo administrador ou técnico</p>
        </div>
    </div>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <?php foreach ($errors as $error): ?>
                <div><?= e($error) ?></div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <div class="card">
        <div class="card-body">
            <form method="POST" action="<?= url('usuarios/create') ?>">
                <?= csrfField() ?>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Nome *</label>
                        <input type="text" name="nome" class="form-control" value="<?= e($old['nome'] ?? '') ?>" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">E-mail *</label>
                        <input type="email" name="email" class="form-control" value="<?= e($old['email'] ?? '') ?>" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Telefone</label>
                        <input type="text" name="telefone" class="form-control" value="<?= e($old['telefone'] ?? '') ?>">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Perfil *</label>
                        <select name="perfil" class="form-select" required>
                            <option value="tecnico" <?= ($old['perfil'] ?? '') === 'tecnico' ? 'selected' : '' ?>>Técnico</option>
                            <option value="admin" <?= ($old['perfil'] ?? '') === 'admin' ? 'selected' : '' ?>>Administrador</option>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Senha *</label>
                        <input type="password" name="senha" class="form-control" required minlength="8">
                        <small class="text-muted">Mínimo 8 caracteres</small>
                    </div>

                    <div class="col-12">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save"></i> Cadastrar Usuário
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
