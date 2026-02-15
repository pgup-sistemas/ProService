<?php
/**
 * Reset de Senha - Admin
 * Arquivo: reset_admin_password.php
 * 
 * USO: Acesse http://proservice.pageup.net.br/reset_admin_password.php
 * Digite nova senha e confirme
 * DELETE este arquivo após usar
 */

require_once __DIR__ . '/app/config/config.php';

$mensagem = '';
$tipo_msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nova_senha = $_POST['senha'] ?? '';
    $confirma_senha = $_POST['confirma'] ?? '';
    
    if (empty($nova_senha) || empty($confirma_senha)) {
        $mensagem = '❌ Preencha todos os campos';
        $tipo_msg = 'error';
    } elseif ($nova_senha !== $confirma_senha) {
        $mensagem = '❌ As senhas não conferem';
        $tipo_msg = 'error';
    } elseif (strlen($nova_senha) < 6) {
        $mensagem = '❌ Senha deve ter no mínimo 6 caracteres';
        $tipo_msg = 'error';
    } else {
        try {
            $pdo = new PDO(
                "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME,
                DB_USER,
                DB_PASS,
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );
            
            $hash = password_hash($nova_senha, PASSWORD_BCRYPT);
            
            $stmt = $pdo->prepare("
                UPDATE usuarios 
                SET senha = ? 
                WHERE email = 'admin@proservice.local' AND perfil = 'admin'
            ");
            $stmt->execute([$hash]);
            
            $mensagem = '✅ Senha alterada com sucesso! Faça login com a nova senha.';
            $tipo_msg = 'success';
            $nova_senha = '';
            $confirma_senha = '';
        } catch (Exception $e) {
            $mensagem = '❌ Erro: ' . $e->getMessage();
            $tipo_msg = 'error';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset de Senha - Admin</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            width: 100%;
            max-width: 400px;
            overflow: hidden;
        }
        .header {
            background: #1e40af;
            color: white;
            padding: 30px;
            text-align: center;
        }
        .header h1 { font-size: 28px; margin-bottom: 10px; }
        .content {
            padding: 30px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
        }
        input {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
            transition: border-color 0.3s;
        }
        input:focus {
            outline: none;
            border-color: #1e40af;
            box-shadow: 0 0 0 3px rgba(30, 64, 175, 0.1);
        }
        button {
            width: 100%;
            padding: 12px;
            background: #1e40af;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }
        button:hover {
            background: #1a35a0;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(30, 64, 175, 0.3);
        }
        .mensagem {
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            text-align: center;
        }
        .mensagem.success {
            background: #e8f5e9;
            color: #2e7d32;
            border: 1px solid #4caf50;
        }
        .mensagem.error {
            background: #ffebee;
            color: #c62828;
            border: 1px solid #f44336;
        }
        .warning {
            background: #fff3cd;
            border: 1px solid #ffc107;
            color: #856404;
            padding: 12px;
            border-radius: 5px;
            margin-top: 20px;
            font-size: 12px;
            line-height: 1.6;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔐 Reset de Senha</h1>
            <p>Admin - ProService</p>
        </div>

        <div class="content">
            <?php if (!empty($mensagem)): ?>
                <div class="mensagem <?php echo $tipo_msg; ?>">
                    <?php echo $mensagem; ?>
                </div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-group">
                    <label for="senha">Nova Senha:</label>
                    <input 
                        type="password" 
                        id="senha" 
                        name="senha" 
                        required 
                        placeholder="Mínimo 6 caracteres"
                        value="<?php echo htmlspecialchars($nova_senha); ?>"
                    >
                </div>

                <div class="form-group">
                    <label for="confirma">Confirme a Senha:</label>
                    <input 
                        type="password" 
                        id="confirma" 
                        name="confirma" 
                        required 
                        placeholder="Digite novamente"
                        value="<?php echo htmlspecialchars($confirma_senha); ?>"
                    >
                </div>

                <button type="submit">Redefinir Senha</button>
            </form>

            <div class="warning">
                ⚠️ <strong>IMPORTANTE:</strong><br>
                1. Após resetar, faça login normalmente<br>
                2. DELETE este arquivo (reset_admin_password.php)<br>
                3. Por segurança, não deixe este arquivo acessível
            </div>
        </div>
    </div>
</body>
</html>
