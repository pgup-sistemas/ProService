<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($titulo) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        body {
            background: linear-gradient(135deg, #1e40af 0%, #059669 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px 0;
        }
        
        .register-card {
            background: white;
            border-radius: 16px;
            padding: 40px;
            width: 100%;
            max-width: 500px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.2);
        }
        
        .login-logo {
            text-align: center;
            margin-bottom: 32px;
        }
        
        .login-logo h1 {
            font-size: 2.5rem;
            font-weight: 700;
            background: linear-gradient(135deg, #1e40af 0%, #059669 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .trial-highlight {
            background: linear-gradient(135deg, #ea580c 0%, #f97316 100%);
            color: white;
            padding: 16px;
            border-radius: 10px;
            text-align: center;
            margin-bottom: 24px;
        }
        
        .trial-highlight h5 {
            margin: 0;
            font-weight: 600;
        }
        
        .form-control {
            border-radius: 10px;
            padding: 12px 16px;
            border: 2px solid #e5e7eb;
        }
        
        .form-control:focus {
            border-color: #1e40af;
            box-shadow: 0 0 0 0.2rem rgba(30, 64, 175, 0.15);
        }
        
        .btn-register {
            background: linear-gradient(135deg, #1e40af 0%, #059669 100%);
            border: none;
            border-radius: 10px;
            padding: 14px;
            font-weight: 600;
            font-size: 1.1rem;
            width: 100%;
            color: white;
        }
        
        @media (max-width: 480px) {
            .register-card {
                margin: 16px;
                padding: 24px;
            }
        }
    </style>
</head>
<body>
    <div class="register-card">
        <div class="login-logo">
            <h1>⚡ proService</h1>
            <p>Crie sua conta</p>
        </div>
        
        <div class="trial-highlight">
            <h5><i class="bi bi-gift"></i> 15 Dias Grátis</h5>
            <small>Sem cartão de crédito • Sem compromisso</small>
        </div>
        
        <?php if (!empty($_SESSION['errors'])): ?>
            <div class="alert alert-danger">
                <?php foreach ($_SESSION['errors'] as $error): ?>
                    <div><?= e($error) ?></div>
                <?php endforeach; ?>
                <?php unset($_SESSION['errors']); ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="<?= url('register') ?>">
            <?= csrfField() ?>
            
            <h6 class="text-primary mb-3"><i class="bi bi-building"></i> Dados da Empresa</h6>
            
            <div class="mb-3">
                <label class="form-label">Nome da Empresa *</label>
                <input type="text" name="nome_fantasia" class="form-control" value="<?= e($old['nome_fantasia'] ?? '') ?>" required>
            </div>
            
            <div class="mb-3">
                <label class="form-label">CNPJ/CPF *</label>
                <input type="text" name="cnpj_cpf" class="form-control" value="<?= e($old['cnpj_cpf'] ?? '') ?>" required>
            </div>
            
            <div class="mb-3">
                <label class="form-label">Telefone</label>
                <input type="text" name="telefone" class="form-control" value="<?= e($old['telefone'] ?? '') ?>">
            </div>
            
            <hr class="my-4">
            
            <h6 class="text-primary mb-3"><i class="bi bi-person"></i> Dados do Administrador</h6>
            
            <div class="mb-3">
                <label class="form-label">Seu Nome *</label>
                <input type="text" name="nome_admin" class="form-control" value="<?= e($old['nome_admin'] ?? '') ?>" required>
            </div>
            
            <div class="mb-3">
                <label class="form-label">E-mail *</label>
                <input type="email" name="email" class="form-control" value="<?= e($old['email'] ?? '') ?>" required>
            </div>
            
            <div class="mb-3">
                <label class="form-label">Senha * (mínimo 8 caracteres)</label>
                <input type="password" name="senha" class="form-control" minlength="8" required>
            </div>
            
            <div class="mb-4">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="aceite" id="aceite" required>
                    <label class="form-check-label" for="aceite">
                        Li e aceito os <a href="<?= url('termos') ?>" target="_blank">Termos de Uso</a> e <a href="<?= url('privacidade') ?>" target="_blank">Política de Privacidade</a>
                    </label>
                </div>
            </div>
            
            <button type="submit" class="btn btn-register">
                <i class="bi bi-check-circle"></i> Criar Conta Grátis
            </button>
        </form>
        
        <div class="text-center mt-4">
            <span class="text-muted">Já tem conta?</span>
            <a href="<?= url('login') ?>" class="text-decoration-none fw-medium">Faça login</a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
