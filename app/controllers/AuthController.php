<?php
/**
 * proService - AuthController
 * Arquivo: /app/controllers/AuthController.php
 */

namespace App\Controllers;

use App\Models\Empresa;
use App\Models\Usuario;
use App\Services\EmailService;

class AuthController extends Controller
{
    private Empresa $empresaModel;
    private Usuario $usuarioModel;

    public function __construct()
    {
        $this->empresaModel = new Empresa();
        $this->usuarioModel = new Usuario();
    }

    /**
     * Página de login
     */
    public function login(): void
    {
        if (isLoggedIn()) {
            $this->redirect('dashboard');
        }

        $this->view('auth/login', [
            'titulo' => 'Login - ' . APP_NAME
        ]);
    }

    /**
     * Processa login
     */
    public function doLogin(): void
    {
        // Valida CSRF
        if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
            setFlash('error', 'Token de segurança inválido.');
            $this->redirect('login');
        }

        $email = sanitizeInput($_POST['email'] ?? '');
        $senha = $_POST['senha'] ?? '';

        // Buscar empresa pelo email
        $empresa = $this->empresaModel->findByEmail($email);
        
        if (!$empresa) {
            setFlash('error', 'E-mail ou senha incorretos.');
            $this->redirect('login');
        }

        // Verificar login
        $usuario = $this->usuarioModel->verificarLogin($email, $senha, $empresa['id']);

        if (!$usuario) {
            setFlash('error', 'E-mail ou senha incorretos.');
            $this->redirect('login');
        }

        if (!$usuario['ativo']) {
            setFlash('error', 'Usuário desativado. Entre em contato com o administrador.');
            $this->redirect('login');
        }

        // Verificar trial expirado
        if ($empresa['plano'] === 'trial' && $empresa['data_fim_trial'] < date('Y-m-d')) {
            setFlash('warning', 'Seu período de trial expirou. Atualize seu plano para continuar.');
        }

        // Registrar acesso
        $this->usuarioModel->registrarAcesso($usuario['id']);

        // Criar sessão
        $_SESSION['usuario_id'] = $usuario['id'];
        $_SESSION['usuario_nome'] = $usuario['nome'];
        $_SESSION['empresa_id'] = $empresa['id'];
        $_SESSION['perfil'] = $usuario['perfil'];
        $_SESSION['empresa'] = $empresa;

        // Log de login
        logSistema('login', 'auth', $usuario['id'], ['ip' => $_SERVER['REMOTE_ADDR'] ?? null]);

        setFlash('success', 'Bem-vindo, ' . $usuario['nome'] . '!');
        $this->redirect('dashboard');
    }

    /**
     * Página de registro
     */
    public function register(): void
    {
        if (isLoggedIn()) {
            $this->redirect('dashboard');
        }

        $this->view('auth/register', [
            'titulo' => 'Criar Conta - ' . APP_NAME
        ]);
    }

    /**
     * Página pública - Termos de Uso
     */
    public function termos(): void
    {
        $this->view('auth/termos', []);
    }

    /**
     * Página pública - Política de Privacidade
     */
    public function privacidade(): void
    {
        $this->view('auth/privacidade', []);
    }

    /**
     * Processa registro
     */
    public function doRegister(): void
    {
        // Valida CSRF
        if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
            setFlash('error', 'Token de segurança inválido.');
            $this->redirect('register');
        }

        // Validações
        $errors = [];
        
        $nomeFantasia = sanitizeInput($_POST['nome_fantasia'] ?? '');
        $cnpjCpf = sanitizeInput($_POST['cnpj_cpf'] ?? '');
        $email = sanitizeInput($_POST['email'] ?? '');
        $senha = $_POST['senha'] ?? '';
        $telefone = sanitizeInput($_POST['telefone'] ?? '');
        $nomeAdmin = sanitizeInput($_POST['nome_admin'] ?? '');
        $aceite = isset($_POST['aceite']);

        if (empty($nomeFantasia)) {
            $errors[] = 'Nome da empresa é obrigatório.';
        }

        if (empty($cnpjCpf)) {
            $errors[] = 'CNPJ/CPF é obrigatório.';
        }

        if (!isValidEmail($email)) {
            $errors[] = 'E-mail inválido.';
        }

        if (strlen($senha) < 8) {
            $errors[] = 'Senha deve ter no mínimo 8 caracteres.';
        }

        if (empty($nomeAdmin)) {
            $errors[] = 'Nome do administrador é obrigatório.';
        }

        if (!$aceite) {
            $errors[] = 'Você deve aceitar os termos de uso.';
        }

        // Verificar se email já existe
        if ($this->empresaModel->findByEmail($email)) {
            $errors[] = 'Este e-mail já está cadastrado.';
        }

        // Verificar se CNPJ/CPF já existe
        if ($this->empresaModel->findByCnpjCpf($cnpjCpf)) {
            $errors[] = 'Este CNPJ/CPF já está cadastrado.';
        }

        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            $this->setOld($_POST);
            $this->redirect('register');
        }

        // Criar empresa
        $empresaId = $this->empresaModel->criarComTrial([
            'nome_fantasia' => $nomeFantasia,
            'cnpj_cpf' => $cnpjCpf,
            'email' => $email,
            'telefone' => $telefone
        ]);

        if ($empresaId) {
            $this->empresaModel->update($empresaId, [
                'aceite_termos_em' => date('Y-m-d H:i:s'),
                'aceite_ip' => $_SERVER['REMOTE_ADDR'] ?? null,
                'aceite_user_agent' => isset($_SERVER['HTTP_USER_AGENT']) ? substr($_SERVER['HTTP_USER_AGENT'], 0, 500) : null,
                'aceite_versao_termo' => '1.0'
            ]);
        }

        if (!$empresaId) {
            setFlash('error', 'Erro ao criar empresa. Tente novamente.');
            $this->redirect('register');
        }

        // Criar usuário admin
        $usuarioId = $this->usuarioModel->criar([
            'empresa_id' => $empresaId,
            'nome' => $nomeAdmin,
            'email' => $email,
            'senha' => $senha,
            'telefone' => $telefone,
            'perfil' => 'admin'
        ]);

        if (!$usuarioId) {
            // Rollback - deletar empresa
            $this->empresaModel->delete($empresaId);
            setFlash('error', 'Erro ao criar usuário. Tente novamente.');
            $this->redirect('register');
        }

        setFlash('success', 'Conta criada com sucesso! Faça login para começar seu trial de 15 dias.');
        
        // Log de registro
        logSistema('registro_empresa', 'auth', $usuarioId, ['empresa_id' => $empresaId]);
        
        $this->redirect('login');
    }

    /**
     * Logout
     */
    public function logout(): void
    {
        // Log antes de destruir sessão
        if (isLoggedIn()) {
            logSistema('logout', 'auth', getUsuarioId());
        }
        
        session_start();
        session_destroy();
        
        setFlash('info', 'Você saiu do sistema.');
        $this->redirect('login');
    }

    /**
     * Página de recuperação de senha
     */
    public function forgotPassword(): void
    {
        $this->view('auth/forgot_password', [
            'titulo' => 'Recuperar Senha - ' . APP_NAME
        ]);
    }

    /**
     * Processa recuperação de senha
     */
    public function doForgotPassword(): void
    {
        // Valida CSRF
        if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
            setFlash('error', 'Token de segurança inválido.');
            $this->redirect('forgot-password');
        }

        $email = sanitizeInput($_POST['email'] ?? '');

        // Buscar empresa
        $empresa = $this->empresaModel->findByEmail($email);

        if (!$empresa) {
            // Não revelar se email existe ou não (segurança)
            setFlash('info', 'Se o e-mail existir em nossa base, enviaremos instruções de recuperação.');
            $this->redirect('forgot-password');
        }

        // Gerar token
        $token = $this->usuarioModel->gerarTokenRecuperacao($email, $empresa['id']);
        
        if ($token) {
            $link = url('reset-password?token=' . $token);
            
            // Enviar e-mail via SMTP
            $emailService = new EmailService($empresa['id']);
            $usuario = $this->usuarioModel->findByEmail($email, $empresa['id']);
            
            if ($emailService->sendPasswordReset($email, $usuario['nome'] ?? 'Usuário', $link)) {
                setFlash('success', 'Instruções enviadas! Verifique seu e-mail.');
                
                // Log de recuperação de senha
                logSistema('recuperacao_senha_solicitada', 'auth', $usuario['id'], ['email' => $email]);
            } else {
                // Fallback: mostra o link em desenvolvimento se falhar
                setFlash('warning', 'Não foi possível enviar o e-mail. Link de recuperação (modo desenvolvimento): ' . $link);
                $_SESSION['debug_reset_link'] = $link;
            }
        } else {
            setFlash('error', 'Erro ao processar solicitação. Tente novamente.');
        }
        
        $this->redirect('forgot-password');
    }

    /**
     * Página de redefinição de senha
     */
    public function resetPassword(): void
    {
        $token = $_GET['token'] ?? '';
        
        if (empty($token)) {
            setFlash('error', 'Token inválido.');
            $this->redirect('login');
        }
        
        // Validar token
        $usuario = $this->usuarioModel->validarTokenRecuperacao($token);
        
        if (!$usuario) {
            setFlash('error', 'Token expirado ou inválido. Solicite uma nova recuperação.');
            $this->redirect('forgot-password');
        }
        
        $this->view('auth/reset_password', [
            'titulo' => 'Redefinir Senha - ' . APP_NAME,
            'token' => $token
        ]);
    }

    /**
     * Processa redefinição de senha
     */
    public function doResetPassword(): void
    {
        // Valida CSRF
        if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
            setFlash('error', 'Token de segurança inválido.');
            $this->back();
        }

        $token = $_POST['token'] ?? '';
        $senha = $_POST['senha'] ?? '';
        $senhaConfirmacao = $_POST['senha_confirmacao'] ?? '';
        
        // Validações
        if (strlen($senha) < 8) {
            setFlash('error', 'A senha deve ter no mínimo 8 caracteres.');
            $this->back();
        }
        
        if ($senha !== $senhaConfirmacao) {
            setFlash('error', 'As senhas não conferem.');
            $this->back();
        }
        
        // Validar token
        $usuario = $this->usuarioModel->validarTokenRecuperacao($token);
        
        if (!$usuario) {
            setFlash('error', 'Token expirado ou inválido.');
            $this->redirect('forgot-password');
        }
        
        // Atualizar senha
        if ($this->usuarioModel->atualizarSenha($usuario['id'], $senha)) {
            // Limpar token
            $this->usuarioModel->limparTokenRecuperacao($usuario['id']);
            
            // Log de redefinição de senha
            logSistema('senha_redefinida', 'auth', $usuario['id']);
            
            setFlash('success', 'Senha redefinida com sucesso! Faça login com sua nova senha.');
            $this->redirect('login');
        } else {
            setFlash('error', 'Erro ao redefinir senha. Tente novamente.');
            $this->back();
        }
    }
}
