<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($titulo) ?> - proService</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; min-height: 100vh; background: #0f172a; }
        .login-split { display: flex; min-height: 100vh; }
        .login-side { flex: 0 0 420px; background: white; display: flex; flex-direction: column; justify-content: center; padding: 40px 48px; position: relative; }
        .login-header { position: absolute; top: 60px; left: 48px; }
        .login-header .logo { font-size: 1.5rem; font-weight: 800; color: #1e40af; display: flex; align-items: center; gap: 8px; }
        .login-header .logo i { font-size: 1.75rem; color: #059669; }
        .login-content { width: 100%; max-width: 320px; margin: 0 auto; }
        .login-title { font-size: 1.75rem; font-weight: 700; color: #0f172a; margin-bottom: 8px; }
        .login-subtitle { color: #64748b; font-size: 0.95rem; margin-bottom: 28px; }
        .form-floating { margin-bottom: 16px; }
        .form-floating .form-control { border: 2px solid #e2e8f0; border-radius: 12px; height: 52px; padding: 12px 16px; font-size: 0.95rem; transition: all 0.2s; }
        .form-floating .form-control:focus { border-color: #1e40af; box-shadow: 0 0 0 4px rgba(30, 64, 175, 0.08); }
        .form-floating label { padding: 12px 16px; color: #64748b; }
        .btn-login { background: linear-gradient(135deg, #1e40af 0%, #059669 100%); border: none; border-radius: 12px; padding: 14px 24px; font-weight: 600; font-size: 1rem; width: 100%; color: white; transition: all 0.2s; margin-top: 8px; }
        .btn-login:hover { transform: translateY(-1px); box-shadow: 0 8px 20px rgba(30, 64, 175, 0.35); color: white; }
        .login-links { display: flex; justify-content: center; margin-top: 16px; font-size: 0.875rem; }
        .login-links a { color: #1e40af; text-decoration: none; font-weight: 500; }
        .login-links a:hover { color: #059669; }
        .divider { display: flex; align-items: center; margin: 24px 0; color: #94a3b8; font-size: 0.875rem; }
        .divider::before, .divider::after { content: ''; flex: 1; height: 1px; background: #e2e8f0; }
        .divider span { padding: 0 12px; }
        .btn-trial { display: flex; align-items: center; justify-content: center; gap: 8px; background: white; border: 2px dashed #059669; border-radius: 12px; padding: 14px 24px; font-weight: 600; font-size: 0.95rem; width: 100%; color: #059669; text-decoration: none; transition: all 0.2s; }
        .btn-trial:hover { background: #059669; color: white; border-style: solid; }
        .whatsapp-float { position: fixed; bottom: 24px; right: 24px; width: 56px; height: 56px; background: #25d366; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-size: 1.75rem; box-shadow: 0 4px 12px rgba(37, 211, 102, 0.4); transition: all 0.3s; z-index: 1000; text-decoration: none; }
        .whatsapp-float:hover { transform: scale(1.1); box-shadow: 0 6px 20px rgba(37, 211, 102, 0.5); color: white; }
        .company-footer { position: absolute; bottom: 24px; left: 48px; font-size: 0.875rem; color: #94a3b8; margin-top: 32px; }
        .company-footer strong { color: #1e40af; }
        .marketing-side { flex: 1; background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #0f172a 100%); display: flex; flex-direction: column; justify-content: center; padding: 60px 80px; position: relative; overflow: hidden; }
        .marketing-side::before { content: ''; position: absolute; top: -50%; right: -20%; width: 600px; height: 600px; background: radial-gradient(circle, rgba(30, 64, 175, 0.15) 0%, transparent 70%); pointer-events: none; }
        .marketing-side::after { content: ''; position: absolute; bottom: -30%; left: -10%; width: 500px; height: 500px; background: radial-gradient(circle, rgba(5, 150, 105, 0.12) 0%, transparent 70%); pointer-events: none; }
        .marketing-content { position: relative; z-index: 1; max-width: 540px; }
        .marketing-badge { display: inline-flex; align-items: center; gap: 8px; background: rgba(5, 150, 105, 0.15); color: #34d399; padding: 8px 16px; border-radius: 20px; font-size: 0.875rem; font-weight: 500; margin-bottom: 24px; }
        .marketing-title { font-size: 2.75rem; font-weight: 800; color: white; line-height: 1.15; margin-bottom: 20px; letter-spacing: -0.02em; }
        .marketing-title span { background: linear-gradient(135deg, #34d399 0%, #60a5fa 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; }
        .marketing-subtitle { font-size: 1.25rem; color: #94a3b8; line-height: 1.6; margin-bottom: 40px; }
        .features-list { list-style: none; padding: 0; margin: 0; }
        .features-list li { display: flex; align-items: center; gap: 12px; color: #e2e8f0; font-size: 1rem; margin-bottom: 16px; }
        .features-list li i { width: 24px; height: 24px; background: linear-gradient(135deg, #059669 0%, #1e40af 100%); border-radius: 6px; display: flex; align-items: center; justify-content: center; font-size: 0.75rem; color: white; flex-shrink: 0; }
        .stats-row { display: flex; gap: 40px; margin-top: 48px; padding-top: 40px; border-top: 1px solid rgba(255, 255, 255, 0.1); }
        .stat-item { text-align: left; }
        .stat-number { font-size: 2rem; font-weight: 800; color: #34d399; display: block; }
        .stat-label { font-size: 0.875rem; color: #64748b; }
        @media (max-width: 991px) { .login-split { flex-direction: column-reverse; } .login-side { flex: none; padding: 32px 24px; } .login-header { position: static; margin-bottom: 24px; text-align: center; } .marketing-side { padding: 40px 24px; min-height: auto; } .marketing-title { font-size: 2rem; } .stats-row { flex-wrap: wrap; gap: 24px; } .btn-trial { margin-bottom: 60px; } }
        @media (max-width: 480px) { .marketing-title { font-size: 1.75rem; } .features-list li { font-size: 0.9rem; } }
        .alert { border-radius: 12px; border: none; font-size: 0.9rem; }
        .alert-danger { background: #fef2f2; color: #dc2626; }
        .alert-success { background: #f0fdf4; color: #059669; }
    </style>
</head>
<body>
    <div class="login-split">
        <div class="login-side">
            <div class="login-header">
                <div class="logo">
                    <i class="bi bi-lightning-charge-fill"></i>
                    proService
                </div>
            </div>
            <div class="login-content">
                <h1 class="login-title">Bem-vindo</h1>
                <p class="login-subtitle">Entre para gerenciar seus serviços</p>
                <?php $flash = getFlash(); ?>
                <?php if ($flash): ?>
                    <div class="alert alert-<?= $flash['type'] === 'error' ? 'danger' : $flash['type'] ?> alert-dismissible fade show mb-3">
                        <?= e($flash['message']) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                <form method="POST" action="<?= url('login') ?>">
                    <?= csrfField() ?>
                    <div class="form-floating">
                        <input type="email" name="email" class="form-control" id="email" placeholder="email@exemplo.com" required autofocus>
                        <label for="email">E-mail</label>
                    </div>
                    <div class="form-floating">
                        <input type="password" name="senha" class="form-control" id="senha" placeholder="Senha" required>
                        <label for="senha">Senha</label>
                    </div>
                    <button type="submit" class="btn btn-login">
                        <i class="bi bi-box-arrow-in-right me-2"></i> Entrar no sistema
                    </button>
                </form>
                <div class="login-links">
                    <a href="<?= url('forgot-password') ?>">Esqueceu a senha?</a>
                </div>
                <div class="divider"><span>ou</span></div>
                <a href="<?= url('register') ?>" class="btn-trial">
                    <i class="bi bi-gift"></i> Teste 15 dias grátis
                </a>
                <div class="company-footer">
                    Uma solução <strong>PageUp Sistemas</strong>
                </div>
            </div>
        </div>
        <div class="marketing-side">
            <div class="marketing-content">
                <div class="marketing-badge">
                    <i class="bi bi-stars"></i> Sistema completo para prestadores de serviço
                </div>
                <h2 class="marketing-title">
                    Organize seus serviços.<br><span>Aumente seus lucros.</span>
                </h2>
                <p class="marketing-subtitle">
                    Chega de anotações perdidas e noites sem dormir organizando papelada. Tudo que você precisa em um só lugar.
                </p>
                <ul class="features-list">
                    <li><i class="bi bi-check-lg"></i> Ordens de Serviço com assinatura digital</li>
                    <li><i class="bi bi-check-lg"></i> Controle financeiro completo</li>
                    <li><i class="bi bi-check-lg"></i> Estoque com baixa automática</li>
                    <li><i class="bi bi-check-lg"></i> Envio automático pelo WhatsApp</li>
                    <li><i class="bi bi-check-lg"></i> Portal do cliente para acompanhar serviços</li>
                </ul>
                <div class="stats-row">
                    <div class="stat-item"><span class="stat-number">2.500+</span><span class="stat-label">Empresas usando</span></div>
                    <div class="stat-item"><span class="stat-number">500k+</span><span class="stat-label">OS geradas</span></div>
                    <div class="stat-item"><span class="stat-number">15 dias</span><span class="stat-label">Teste gratuito</span></div>
                </div>
            </div>
        </div>
    </div>
    <a href="https://wa.me/5569993882222" target="_blank" class="whatsapp-float" title="Falar no WhatsApp">
        <i class="bi bi-whatsapp"></i>
    </a>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
