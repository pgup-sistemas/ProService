<?php
/**
 * proService - Editar Usuário
 */
?>

<?= breadcrumb(['Dashboard' => 'dashboard', 'Gestão de Usuários' => 'usuarios', 'Editar Usuário']) ?>

<div class="container-fluid">
    <div class="d-flex align-items-center mb-4">
        <a href="<?= url('usuarios') ?>" class="btn btn-outline-secondary btn-sm me-3">
            <i class="bi bi-arrow-left"></i>
        </a>
        <div>
            <h4 class="mb-0"><i class="bi bi-pencil-square"></i> Editar Usuário</h4>
            <p class="text-muted mb-0"><?= e($usuario['nome']) ?> (<?= $usuario['perfil'] === 'admin' ? 'Administrador' : 'Técnico' ?>)</p>
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
            <form method="POST" action="<?= url('usuarios/edit/' . $usuario['id']) ?>">
                <?= csrfField() ?>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Nome *</label>
                        <input type="text" name="nome" class="form-control" value="<?= e($old['nome'] ?? $usuario['nome']) ?>" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">E-mail *</label>
                        <input type="email" name="email" class="form-control" value="<?= e($old['email'] ?? $usuario['email']) ?>" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Telefone</label>
                        <input type="text" name="telefone" class="form-control" value="<?= e($old['telefone'] ?? $usuario['telefone'] ?? '') ?>">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Perfil *</label>
                        <select name="perfil" class="form-select" required>
                            <option value="tecnico" <?= ($old['perfil'] ?? $usuario['perfil']) === 'tecnico' ? 'selected' : '' ?>>Técnico</option>
                            <option value="admin" <?= ($old['perfil'] ?? $usuario['perfil']) === 'admin' ? 'selected' : '' ?>>Administrador</option>
                        </select>
                    </div>

                    <div class="col-12">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save"></i> Salvar Alterações
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
