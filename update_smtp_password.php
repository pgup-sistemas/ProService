<?php
/**
 * Atualizar Senha SMTP
 * Arquivo: update_smtp_password.php
 * Atualiza a senha SMTP no banco de dados
 */

require_once __DIR__ . '/app/config/config.php';

// Conectar ao banco
try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME,
        DB_USER,
        DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
} catch (PDOException $e) {
    die("❌ Erro de conexão: " . $e->getMessage());
}

$resultado = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nova_senha = $_POST['nova_senha'] ?? '';
    
    if (empty($nova_senha)) {
        $resultado = "<div class='erro'>❌ Preencha a senha</div>";
    } else {
        // Validar se parece uma App Password (16 caracteres sem espaços)
        $senha_limpa = str_replace(' ', '', $nova_senha);
        
        if (strlen($senha_limpa) < 10) {
            $resultado = "<div class='erro'>❌ Senha muito curta. App Password do Gmail tem 16 caracteres.</div>";
        } else {
            // Atualizar no banco
            $stmt = $pdo->prepare("
                UPDATE empresas 
                SET smtp_pass = ? 
                WHERE id = 1
                LIMIT 1
            ");
            
            try {
                $stmt->execute([$senha_limpa]);
                $resultado = "<div class='sucesso'>✅ Senha SMTP atualizada com sucesso!</div>";
                $resultado .= "<div class='info'>Agora você pode testar novamente em: <strong>test_smtp_email.php</strong></div>";
            } catch (Exception $e) {
                $resultado = "<div class='erro'>❌ Erro ao atualizar: " . $e->getMessage() . "</div>";
            }
        }
    }
}

// Verificar configuração atual
$stmt = $pdo->query("SELECT smtp_host, smtp_port, smtp_user, smtp_pass FROM empresas WHERE id = 1");
$config = $stmt->fetch(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Atualizar Senha SMTP</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: Courier New, monospace;
            background: #1e1e1e;
            color: #00ff00;
            padding: 20px;
        }
        .container {
            max-width: 700px;
            margin: 0 auto;
            background: #252526;
            border: 1px solid #3e3e42;
            border-radius: 5px;
            padding: 20px;
        }
        h1 {
            color: #4ec9b0;
            margin-bottom: 20px;
            border-bottom: 2px solid #3e3e42;
            padding-bottom: 10px;
        }
        .status {
            background: #1e1e1e;
            border: 1px solid #3e3e42;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 3px;
        }
        .status-item {
            margin: 10px 0;
        }
        .status-label {
            color: #ce9178;
        }
        .status-value {
            color: #9cdcfe;
            margin-left: 10px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            color: #ce9178;
            margin-bottom: 8px;
            font-weight: bold;
        }
        input {
            width: 100%;
            padding: 10px;
            background: #1e1e1e;
            border: 1px solid #3e3e42;
            color: #00ff00;
            border-radius: 3px;
            font-family: Courier New, monospace;
        }
        input:focus {
            outline: none;
            border-color: #4ec9b0;
            box-shadow: 0 0 5px #4ec9b0;
        }
        button {
            width: 100%;
            background: #0e639c;
            color: white;
            padding: 12px;
            border: none;
            border-radius: 3px;
            cursor: pointer;
            font-family: Courier New, monospace;
            font-weight: bold;
            font-size: 14px;
        }
        button:hover {
            background: #1177bb;
        }
        .resultado {
            margin-top: 20px;
            padding: 15px;
            border-radius: 3px;
            background: #1e1e1e;
            border: 1px solid #3e3e42;
        }
        .sucesso {
            color: #4ec9b0;
            border-left: 3px solid #4ec9b0;
            padding-left: 12px;
        }
        .erro {
            color: #f48771;
            border-left: 3px solid #f48771;
            padding-left: 12px;
        }
        .info {
            color: #9cdcfe;
            border-left: 3px solid #9cdcfe;
            padding-left: 12px;
            margin-top: 10px;
        }
        .instrucoes {
            background: #1e3a5f;
            border: 1px solid #2c5aa0;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 3px;
            color: #9cdcfe;
        }
        .instrucoes h3 {
            color: #4ec9b0;
            margin-bottom: 10px;
        }
        .instrucoes ol {
            padding-left: 20px;
            line-height: 1.8;
        }
        .instrucoes li {
            margin-bottom: 8px;
        }
        .instrucoes a {
            color: #569cd6;
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔐 Atualizar Senha SMTP - Gmail</h1>

        <div class="status">
            <div class="status-item">
                <span class="status-label">Host:</span>
                <span class="status-value"><?php echo $config['smtp_host'] ?? 'Não configurado'; ?></span>
            </div>
            <div class="status-item">
                <span class="status-label">Porta:</span>
                <span class="status-value"><?php echo $config['smtp_port'] ?? '587'; ?></span>
            </div>
            <div class="status-item">
                <span class="status-label">Usuário:</span>
                <span class="status-value"><?php echo $config['smtp_user'] ?? 'Não configurado'; ?></span>
            </div>
            <div class="status-item">
                <span class="status-label">Senha:</span>
                <span class="status-value"><?php echo empty($config['smtp_pass']) ? '❌ VAZIA' : '✅ Configurada'; ?></span>
            </div>
        </div>

        <div class="instrucoes">
            <h3>📝 Passo a Passo:</h3>
            <ol>
                <li>Acesse: <a href="https://myaccount.google.com/apppasswords" target="_blank">myaccount.google.com/apppasswords</a></li>
                <li>Selecione: <strong>Mail</strong> + <strong>Windows, Mac, Linux</strong></li>
                <li>Clique em "Gerar"</li>
                <li>Copie a senha de 16 caracteres (sem espaços)</li>
                <li>Cole a senha no formulário abaixo</li>
                <li>Clique em "Atualizar Senha"</li>
            </ol>
        </div>

        <form method="POST">
            <div class="form-group">
                <label for="nova_senha">Senha SMTP (App Password):</label>
                <input type="text" id="nova_senha" name="nova_senha" required 
                       placeholder="Cole a senha de 16 caracteres aqui"
                       autofocus>
            </div>

            <button type="submit">🔑 Atualizar Senha SMTP</button>
        </form>

        <?php if (!empty($resultado)): ?>
            <div class="resultado">
                <?php echo $resultado; ?>
            </div>
        <?php endif; ?>

        <div class="instrucoes" style="margin-top: 30px;">
            <h3>✅ Próximos Passos:</h3>
            <ol>
                <li>Após atualizar a senha aqui</li>
                <li>Acesse: <a href="test_smtp_email.php"><strong>test_smtp_email.php</strong></a></li>
                <li>Clique em "🧪 Enviar E-mail de Teste"</li>
                <li>Se receber o e-mail, o SMTP está funcionando!</li>
            </ol>
        </div>
    </div>
</body>
</html>
