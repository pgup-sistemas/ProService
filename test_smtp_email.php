<?php
/**
 * Teste de E-mail SMTP
 * Arquivo: test_smtp_email.php
 * Localizado na raiz - diagnostica e testa envio de e-mail
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
    die("❌ Erro: Não conseguiu conectar ao banco de dados\n");
}

$resultado = [];
$teste_ok = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $acao = $_POST['acao'] ?? '';
    
    // Obter dados da empresa
    $stmt = $pdo->query("SELECT * FROM empresas LIMIT 1");
    $empresa = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$empresa) {
        $resultado[] = ['tipo' => 'erro', 'msg' => '❌ Nenhuma empresa encontrada no banco'];
    } else {
        $resultado[] = ['tipo' => 'info', 'msg' => '✅ Empresa encontrada: ' . $empresa['nome_fantasia']];
        
        // Verificar dados SMTP
        $host = $empresa['smtp_host'] ?? '';
        $port = (int) ($empresa['smtp_port'] ?? 587);
        $user = $empresa['smtp_user'] ?? '';
        $pass = $empresa['smtp_pass'] ?? '';
        $encryption = $empresa['smtp_encryption'] ?? 'TLS';
        
        $resultado[] = ['tipo' => 'info', 'msg' => "📊 Dados SMTP: Host=$host, Porta=$port, Usuário=$user"];
        
        // Validações
        if (empty($host) || empty($user)) {
            $resultado[] = ['tipo' => 'erro', 'msg' => '❌ SMTP não está configurado (Host ou Usuário vazio)'];
        } elseif (empty($pass)) {
            $resultado[] = ['tipo' => 'erro', 'msg' => '❌ SENHA SMTP está vazia! Configure a senha no painel.'];
        } else {
            $resultado[] = ['tipo' => 'sucesso', 'msg' => '✅ Configuração SMTP encontrada'];
            
            // TESTE 1: Conexão TCP
            $resultado[] = ['tipo' => 'info', 'msg' => "\n📡 TESTE 1: Conectando a $host:$port..."];
            
            $socket = @fsockopen($host, $port, $errno, $errstr, 10);
            
            if (!$socket) {
                $resultado[] = ['tipo' => 'erro', 'msg' => "❌ Falha na conexão: $errstr (erro $errno)"];
                $resultado[] = ['tipo' => 'info', 'msg' => "💡 Seu ISP pode estar bloqueando a porta $port. Solicite liberação."];
            } else {
                $resultado[] = ['tipo' => 'sucesso', 'msg' => "✅ Conexão TCP estabelecida"];
                fclose($socket);
                
                // TESTE 2: Envio via stream direto
                if ($acao === 'send_test') {
                    $email_destino = $_POST['email_destino'] ?? $empresa['email'];
                    
                    $resultado[] = ['tipo' => 'info', 'msg' => "\n✉️ TESTE 2: Enviando e-mail para $email_destino..."];
                    
                    try {
                        $socket = stream_socket_client("tcp://{$host}:{$port}", $errno, $errstr, 10);
                        
                        if (!$socket) {
                            $resultado[] = ['tipo' => 'erro', 'msg' => "❌ Reconexão falhou: $errstr"];
                        } else {
                            // Lê resposta inicial
                            $response = fgets($socket, 1024);
                            $resultado[] = ['tipo' => 'info', 'msg' => "→ Servidor: " . trim($response)];
                            
                            // EHLO
                            fputs($socket, "EHLO proservice\r\n");
                            $response = fgets($socket, 1024);
                            
                            // STARTTLS
                            fputs($socket, "STARTTLS\r\n");
                            $response = fgets($socket, 1024);
                            
                            if (strpos($response, '220') !== false) {
                                stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
                                $resultado[] = ['tipo' => 'sucesso', 'msg' => "✅ TLS habilitado"];
                                
                                fputs($socket, "EHLO proservice\r\n");
                                $response = fgets($socket, 1024);
                            }
                            
                            // AUTH LOGIN
                            fputs($socket, "AUTH LOGIN\r\n");
                            $response = fgets($socket, 1024);
                            
                            // Enviar usuário em base64
                            fputs($socket, base64_encode($user) . "\r\n");
                            $response = fgets($socket, 1024);
                            
                            // Enviar senha em base64
                            fputs($socket, base64_encode($pass) . "\r\n");
                            $response = fgets($socket, 1024);
                            
                            if (strpos($response, '235') !== false) {
                                $resultado[] = ['tipo' => 'sucesso', 'msg' => "✅ Autenticação bem-sucedida"];
                                
                                // MAIL FROM
                                fputs($socket, "MAIL FROM:<{$user}>\r\n");
                                $response = fgets($socket, 1024);
                                
                                // RCPT TO
                                fputs($socket, "RCPT TO:<{$email_destino}>\r\n");
                                $response = fgets($socket, 1024);
                                
                                // DATA
                                fputs($socket, "DATA\r\n");
                                $response = fgets($socket, 1024);
                                
                                $headers = "From: " . $empresa['nome_fantasia'] . " <" . $user . ">\r\n";
                                $headers .= "To: $email_destino\r\n";
                                $headers .= "Subject: Teste ProService\r\n";
                                $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
                                $headers .= "Content-Transfer-Encoding: 8bit\r\n\r\n";
                                
                                $body = "<h1>Teste de E-mail</h1>";
                                $body .= "<p>Se você recebeu este e-mail, o SMTP do ProService está funcionando corretamente!</p>";
                                $body .= "<p>Hora: " . date('d/m/Y H:i:s') . "</p>";
                                
                                $message = $headers . $body . "\r\n.\r\n";
                                
                                fputs($socket, $message);
                                $response = fgets($socket, 1024);
                                
                                if (strpos($response, '250') !== false) {
                                    $resultado[] = ['tipo' => 'sucesso', 'msg' => "✅ E-MAIL ENVIADO COM SUCESSO!"];
                                    $resultado[] = ['tipo' => 'info', 'msg' => "📬 Verifique a caixa de entrada de: $email_destino"];
                                    $teste_ok = true;
                                } else {
                                    $resultado[] = ['tipo' => 'erro', 'msg' => "❌ Erro ao enviar: " . trim($response)];
                                }
                                
                                fputs($socket, "QUIT\r\n");
                            } else {
                                $resultado[] = ['tipo' => 'erro', 'msg' => "❌ Falha na autenticação"];
                                $resultado[] = ['tipo' => 'erro', 'msg' => "💡 Verifique se a senha está correta. Para Gmail, use App Password!"];
                            }
                            
                            fclose($socket);
                        }
                    } catch (Exception $e) {
                        $resultado[] = ['tipo' => 'erro', 'msg' => "❌ Exceção: " . $e->getMessage()];
                    }
                }
            }
        }
    }
}

// Obter dados atuais para exibir no formulário
$stmt = $pdo->query("SELECT * FROM empresas LIMIT 1");
$empresa = $stmt->fetch(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teste SMTP - ProService</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: Courier New, monospace;
            background: #1e1e1e;
            color: #00ff00;
            padding: 20px;
            line-height: 1.6;
        }
        .container {
            max-width: 900px;
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
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            color: #ce9178;
            margin-bottom: 5px;
        }
        input {
            width: 100%;
            padding: 8px;
            background: #1e1e1e;
            border: 1px solid #3e3e42;
            color: #00ff00;
            border-radius: 3px;
        }
        input:focus {
            outline: none;
            border-color: #4ec9b0;
            box-shadow: 0 0 5px #4ec9b0;
        }
        button {
            background: #0e639c;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 3px;
            cursor: pointer;
            font-family: Courier New, monospace;
            font-weight: bold;
        }
        button:hover {
            background: #1177bb;
        }
        .resultado {
            margin-top: 30px;
            background: #1e1e1e;
            border: 1px solid #3e3e42;
            padding: 15px;
            border-radius: 3px;
        }
        .resultado-line {
            margin: 5px 0;
            word-break: break-all;
        }
        .resultado-line.sucesso {
            color: #4ec9b0;
        }
        .resultado-line.erro {
            color: #f48771;
        }
        .resultado-line.info {
            color: #9cdcfe;
        }
        .info-box {
            background: #264f78;
            border-left: 3px solid #4ec9b0;
            padding: 15px;
            margin-top: 20px;
            border-radius: 3px;
        }
        .info-box p {
            margin: 5px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>📧 Teste de SMTP - ProService</h1>
        
        <form method="POST">
            <div class="form-group">
                <label>E-mail para teste:</label>
                <input type="email" name="email_destino" required 
                       value="new.normando@gmail.com"
                       placeholder="seu@email.com">
            </div>
            
            <button type="submit" name="acao" value="send_test">🧪 Enviar E-mail de Teste</button>
        </form>

        <?php if (!empty($resultado)): ?>
            <div class="resultado">
                <h2 style="color: #4ec9b0; margin-bottom: 15px;">📋 Resultado do Teste:</h2>
                <?php foreach ($resultado as $item): ?>
                    <div class="resultado-line <?php echo $item['tipo']; ?>">
                        <?php echo $item['msg']; ?>
                    </div>
                <?php endforeach; ?>
            </div>

            <?php if ($teste_ok): ?>
                <div class="info-box">
                    <h3 style="color: #4ec9b0;">✅ SUCESSO!</h3>
                    <p>✓ O e-mail foi enviado com sucesso!</p>
                    <p>✓ O sistema de e-mail do ProService está funcionando corretamente</p>
                    <p>✓ Os e-mails de recuperação de senha, notificações, etc. funcionarão normalmente</p>
                </div>
            <?php endif; ?>
        <?php endif; ?>

        <div class="info-box">
            <h3 style="color: #4ec9b0;">💡 Informações Importantes:</h3>
            <p><strong>Gmail:</strong> Use App Password (16 caracteres), não a senha comum</p>
            <p><strong>Locaweb:</strong> Use smtplw.com.br:587 com sua senha de e-mail</p>
            <p><strong>Outlook:</strong> Use smtp-mail.outlook.com:587</p>
            <p style="margin-top: 15px; color: #f48771;"><strong>Se timeout:</strong> Seu ISP pode estar bloqueando. Solicite liberação de porta 587.</p>
        </div>
    </div>
</body>
</html>
