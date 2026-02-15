<?php
/**
 * proService - Serviço de E-mail
 * Arquivo: /app/services/EmailService.php
 * 
 * Envia e-mails usando SMTP configurado no banco de dados
 * Compatível com Locaweb, Gmail, Outlook e outros provedores SMTP
 */

namespace App\Services;

use App\Models\Empresa;

class EmailService
{
    private array $smtpConfig;
    private string $fromEmail;
    private string $fromName;
    private ?object $mailer = null;

    public function __construct(int $empresaId)
    {
        $empresaModel = new Empresa();
        $empresa = $empresaModel->findById($empresaId);
        
        $this->smtpConfig = [
            'host' => $empresa['smtp_host'] ?? null,
            'port' => $empresa['smtp_port'] ?? 587,
            'user' => $empresa['smtp_user'] ?? null,
            'pass' => $empresa['smtp_pass'] ?? null,
            'encryption' => $empresa['smtp_encryption'] ?? 'TLS',
        ];
        
        $this->fromEmail = $empresa['email'] ?? APP_EMAIL;
        $this->fromName = $empresa['nome_fantasia'] ?? APP_NAME;
    }

    /**
     * Verifica se SMTP está configurado
     */
    public function isConfigured(): bool
    {
        return !empty($this->smtpConfig['host']) && 
               !empty($this->smtpConfig['user']) && 
               !empty($this->smtpConfig['pass']);
    }

    /**
     * Envia e-mail usando SMTP configurado
     */
    public function send(string $to, string $subject, string $body, bool $isHtml = true): bool
    {
        if (!$this->isConfigured()) {
            error_log("EmailService: SMTP não configurado");
            return false;
        }

        // Usar método SMTP direto (mais confiável que PHPMailer)
        return $this->sendViaSmtp($to, $subject, $body, $isHtml);
    }

    /**
     * Envia via SMTP usando stream_socket_client (método alternativo)
     */
    private function sendViaSmtp(string $to, string $subject, string $body, bool $isHtml): bool
    {
        try {
            $host = $this->smtpConfig['host'];
            $port = (int) $this->smtpConfig['port'];
            $user = $this->smtpConfig['user'];
            $pass = $this->smtpConfig['pass'];
            $encryption = $this->smtpConfig['encryption'];

            // Conexão SMTP simplificada
            $socket = @stream_socket_client(
                "tcp://{$host}:{$port}",
                $errno,
                $errstr,
                30
            );

            if (!$socket) {
                error_log("SMTP Connection failed: {$errstr} ({$errno})");
                return false;
            }

            // Leitura da resposta inicial
            $this->getSmtpResponse($socket);

            // EHLO
            fputs($socket, "EHLO {$host}\r\n");
            $this->getSmtpResponse($socket);

            // STARTTLS se necessário
            if ($encryption === 'TLS' || $encryption === 'tls') {
                fputs($socket, "STARTTLS\r\n");
                $this->getSmtpResponse($socket);
                
                stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
                
                fputs($socket, "EHLO {$host}\r\n");
                $this->getSmtpResponse($socket);
            }

            // Autenticação
            fputs($socket, "AUTH LOGIN\r\n");
            $this->getSmtpResponse($socket);
            
            fputs($socket, base64_encode($user) . "\r\n");
            $this->getSmtpResponse($socket);
            
            fputs($socket, base64_encode($pass) . "\r\n");
            $this->getSmtpResponse($socket);

            // From
            fputs($socket, "MAIL FROM:<{$this->fromEmail}>\r\n");
            $this->getSmtpResponse($socket);

            // To
            fputs($socket, "RCPT TO:<{$to}>\r\n");
            $this->getSmtpResponse($socket);

            // Data
            fputs($socket, "DATA\r\n");
            $this->getSmtpResponse($socket);

            // Headers e corpo
            $headers = "From: \"{$this->fromName}\" <{$this->fromEmail}>\r\n";
            $headers .= "To: {$to}\r\n";
            $headers .= "Subject: {$subject}\r\n";
            $headers .= "MIME-Version: 1.0\r\n";
            
            if ($isHtml) {
                $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
            } else {
                $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
            }
            
            $headers .= "Content-Transfer-Encoding: 8bit\r\n";
            $headers .= "\r\n";

            $message = $headers . $body . "\r\n.\r\n";
            fputs($socket, $message);
            $this->getSmtpResponse($socket);

            // Quit
            fputs($socket, "QUIT\r\n");
            fclose($socket);

            // Log do envio
            $this->logEmail($to, $subject, 'success');
            
            return true;

        } catch (\Exception $e) {
            error_log("SMTP Send Error: " . $e->getMessage());
            $this->logEmail($to, $subject, 'failed');
            return false;
        }
    }

    /**
     * Lê resposta do servidor SMTP
     */
    private function getSmtpResponse($socket): string
    {
        $response = '';
        while ($line = fgets($socket, 515)) {
            $response .= $line;
            if (substr($line, 3, 1) === ' ') {
                break;
            }
        }
        return $response;
    }

    /**
     * Registra log de e-mail enviado
     */
    private function logEmail(string $to, string $subject, string $status): void
    {
        try {
            $db = \App\Config\Database::getInstance();
            $stmt = $db->prepare("
                INSERT INTO email_logs (empresa_id, para, assunto, status, created_at) 
                VALUES (?, ?, ?, ?, NOW())
            ");
            $stmt->execute([
                getEmpresaId() ?? 0,
                $to,
                $subject,
                $status
            ]);
        } catch (\Exception $e) {
            error_log("Erro ao logar email: " . $e->getMessage());
        }
    }

    /**
     * Template de e-mail para recuperação de senha
     */
    public function sendPasswordReset(string $to, string $nome, string $resetLink): bool
    {
        $subject = "Recuperação de Senha - " . APP_NAME;
        
        $body = <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #1e40af; color: white; padding: 20px; text-align: center; }
        .content { background: #f9f9f9; padding: 30px; }
        .button { display: inline-block; background: #059669; color: white; padding: 12px 30px; 
                  text-decoration: none; border-radius: 5px; margin: 20px 0; }
        .footer { text-align: center; padding: 20px; font-size: 12px; color: #666; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>{$this->fromName}</h2>
        </div>
        <div class="content">
            <h3>Olá, {$nome}!</h3>
            <p>Recebemos uma solicitação para recuperação de senha da sua conta.</p>
            <p>Para redefinir sua senha, clique no botão abaixo:</p>
            <p style="text-align: center;">
                <a href="{$resetLink}" class="button">Redefinir Senha</a>
            </p>
            <p>Ou copie e cole este link no seu navegador:</p>
            <p style="word-break: break-all; background: #eee; padding: 10px; font-size: 12px;">
                {$resetLink}
            </p>
            <p><strong>Importante:</strong> Este link expira em 1 hora por segurança.</p>
            <p>Se você não solicitou esta recuperação, ignore este e-mail.</p>
        </div>
        <div class="footer">
            <p>{$this->fromName}<br>
            Este é um e-mail automático, não responda.</p>
        </div>
    </div>
</body>
</html>
HTML;

        return $this->send($to, $subject, $body, true);
    }
}
