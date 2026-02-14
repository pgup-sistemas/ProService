<?php
/**
 * proService - Meu Perfil
 */
?>

<?= breadcrumb(['Dashboard' => 'dashboard', 'Meu Perfil']) ?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-0"><i class="bi bi-person-circle text-primary"></i> Meu Perfil</h2>
            <p class="text-muted mb-0">Atualize seus dados e sua senha</p>
        </div>
    </div>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <?php foreach ($errors as $error): ?>
                <div><?= e($error) ?></div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <div class="row g-4">
        <div class="col-lg-7">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0"><i class="bi bi-person-lines-fill"></i> Dados do Perfil</h6>
                </div>
                <div class="card-body">
                    <form method="POST" action="<?= url('perfil') ?>">
                        <?= csrfField() ?>

                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label">Nome *</label>
                                <input type="text" name="nome" class="form-control" value="<?= e($old['nome'] ?? $usuario['nome'] ?? '') ?>" required>
                            </div>

                            <div class="col-md-7">
                                <label class="form-label">E-mail *</label>
                                <input type="email" name="email" class="form-control" value="<?= e($old['email'] ?? $usuario['email'] ?? '') ?>" required>
                            </div>

                            <div class="col-md-5">
                                <label class="form-label">Telefone</label>
                                <input type="text" name="telefone" class="form-control" value="<?= e($old['telefone'] ?? $usuario['telefone'] ?? '') ?>">
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

        <div class="col-lg-5">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0"><i class="bi bi-shield-lock"></i> Alterar Senha</h6>
                </div>
                <div class="card-body">
                    <form method="POST" action="<?= url('perfil/senha') ?>">
                        <?= csrfField() ?>

                        <div class="mb-3">
                            <label class="form-label">Senha atual *</label>
                            <input type="password" name="senha_atual" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Nova senha *</label>
                            <input type="password" name="nova_senha" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Confirmar nova senha *</label>
                            <input type="password" name="confirmar_senha" class="form-control" required>
                        </div>

                        <button type="submit" class="btn btn-outline-primary">
                            <i class="bi bi-key"></i> Atualizar Senha
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
