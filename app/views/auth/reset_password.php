<?php
/**
 * proService - Redefinição de Senha
 * Arquivo: /app/views/auth/reset_password.php
 */
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($titulo) ?></title>
    <link rel="icon" type="image/svg+xml" href="<?= asset('favicon.svg') ?>">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        body {
            background: linear-gradient(135deg, #1e40af 0%, #059669 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            padding: 40px;
            width: 100%;
            max-width: 450px;
        }
        .brand {
            text-align: center;
            margin-bottom: 30px;
        }
        .brand h1 {
            font-size: 2rem;
            font-weight: 700;
            background: linear-gradient(135deg, #1e40af 0%, #059669 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .form-control {
            padding: 12px 16px;
            border-radius: 8px;
        }
        .btn-primary {
            background: linear-gradient(135deg, #1e40af 0%, #059669 100%);
            border: none;
            padding: 12px 24px;
            border-radius: 8px;
        }
        .btn-primary:hover {
            opacity: 0.9;
        }
        .password-strength {
            height: 4px;
            border-radius: 2px;
            margin-top: 8px;
            transition: all 0.3s;
        }
    </style>
</head>
<body>
    <div class="login-card">
        <div class="brand">
            <h1><i class="bi bi-lightning-charge"></i> proService</h1>
            <p class="text-muted">Redefinir Senha</p>
        </div>

        <?php if ($flash = getFlash()): ?>
        <div class="alert alert-<?= $flash['type'] ?> alert-dismissible fade show">
            <?= $flash['message'] ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <div class="alert alert-info mb-4">
            <i class="bi bi-shield-lock"></i> 
            Crie uma nova senha segura. Mínimo de 8 caracteres.
        </div>

        <form method="POST" action="<?= url('do-reset-password') ?>">
            <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
            <input type="hidden" name="token" value="<?= e($token) ?>">
            
            <div class="mb-3">
                <label class="form-label">Nova Senha</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-lock"></i></span>
                    <input type="password" name="senha" id="senha" class="form-control" 
                           placeholder="Mínimo 8 caracteres" 
                           required minlength="8">
                </div>
                <div id="passwordStrength" class="password-strength bg-secondary"></div>
                <small class="text-muted">Use letras, números e caracteres especiais</small>
            </div>

            <div class="mb-4">
                <label class="form-label">Confirmar Nova Senha</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
                    <input type="password" name="senha_confirmacao" id="senhaConfirmacao" 
                           class="form-control" placeholder="Repita a senha" required>
                </div>
                <div id="passwordMatch" class="mt-1"></div>
            </div>

            <button type="submit" class="btn btn-primary w-100 mb-3" id="btnSubmit">
                <i class="bi bi-check-lg"></i> Redefinir Senha
            </button>
        </form>

        <div class="text-center">
            <a href="<?= url('login') ?>" class="text-decoration-none">
                <i class="bi bi-arrow-left"></i> Voltar para o login
            </a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    document.getElementById('senha').addEventListener('input', function() {
        const senha = this.value;
        const strengthBar = document.getElementById('passwordStrength');
        
        let strength = 0;
        if (senha.length >= 8) strength++;
        if (senha.match(/[a-z]/)) strength++;
        if (senha.match(/[A-Z]/)) strength++;
        if (senha.match(/[0-9]/)) strength++;
        if (senha.match(/[^a-zA-Z0-9]/)) strength++;
        
        const colors = ['bg-danger', 'bg-warning', 'bg-info', 'bg-primary', 'bg-success'];
        const widths = ['20%', '40%', '60%', '80%', '100%'];
        
        strengthBar.className = 'password-strength ' + colors[strength - 1] || 'bg-secondary';
        strengthBar.style.width = widths[strength - 1] || '0%';
    });

    document.getElementById('senhaConfirmacao').addEventListener('input', function() {
        const senha = document.getElementById('senha').value;
        const confirmacao = this.value;
        const matchDiv = document.getElementById('passwordMatch');
        
        if (confirmacao === '') {
            matchDiv.innerHTML = '';
        } else if (senha === confirmacao) {
            matchDiv.innerHTML = '<small class="text-success"><i class="bi bi-check-circle"></i> Senhas conferem</small>';
        } else {
            matchDiv.innerHTML = '<small class="text-danger"><i class="bi bi-x-circle"></i> Senhas não conferem</small>';
        }
    });
    </script>
</body>
</html>
