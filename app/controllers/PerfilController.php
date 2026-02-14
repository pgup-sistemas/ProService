<?php

namespace App\Controllers;

use App\Models\Usuario;

class PerfilController extends Controller
{
    private Usuario $usuarioModel;

    public function __construct()
    {
        $this->usuarioModel = new Usuario();
    }

    public function index(): void
    {
        $usuarioId = getUsuarioId();
        if (!$usuarioId) {
            setFlash('error', 'Usuário não identificado.');
            $this->redirect('dashboard');
        }

        $usuario = $this->usuarioModel->findById((int) $usuarioId);
        if (!$usuario) {
            setFlash('error', 'Usuário não encontrado.');
            $this->redirect('dashboard');
        }

        $this->layout('main', [
            'titulo' => 'Meu Perfil',
            'content' => $this->render('usuarios/perfil', [
                'usuario' => $usuario,
                'old' => $_SESSION['old'] ?? [],
                'errors' => $_SESSION['errors'] ?? [],
            ])
        ]);

        unset($_SESSION['old'], $_SESSION['errors']);
    }

    public function update(): void
    {
        $usuarioId = getUsuarioId();
        if (!$usuarioId) {
            setFlash('error', 'Usuário não identificado.');
            $this->redirect('dashboard');
        }

        if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
            setFlash('error', 'Token de segurança inválido.');
            $this->redirect('perfil');
        }

        $nome = sanitizeInput($_POST['nome'] ?? '');
        $email = sanitizeInput($_POST['email'] ?? '');
        $telefone = sanitizeInput($_POST['telefone'] ?? '');

        $errors = [];
        if ($nome === '') {
            $errors[] = 'Nome é obrigatório.';
        }
        if (!isValidEmail($email)) {
            $errors[] = 'E-mail inválido.';
        }

        $existing = $this->usuarioModel->findByEmail($email);
        if ($existing && (int) $existing['id'] !== (int) $usuarioId) {
            $errors[] = 'Este e-mail já está cadastrado nesta empresa.';
        }

        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            $_SESSION['old'] = $_POST;
            $this->redirect('perfil');
        }

        $ok = $this->usuarioModel->update((int) $usuarioId, [
            'nome' => $nome,
            'email' => $email,
            'telefone' => $telefone,
        ]);

        if ($ok) {
            $_SESSION['usuario_nome'] = $nome;
            setFlash('success', 'Perfil atualizado com sucesso!');
            
            // Log de atualização de perfil
            logSistema('perfil_atualizado', 'perfil', $usuarioId);
        } else {
            setFlash('error', 'Erro ao atualizar perfil.');
        }

        $this->redirect('perfil');
    }

    public function senha(): void
    {
        $usuarioId = getUsuarioId();
        if (!$usuarioId) {
            setFlash('error', 'Usuário não identificado.');
            $this->redirect('dashboard');
        }

        if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
            setFlash('error', 'Token de segurança inválido.');
            $this->redirect('perfil');
        }

        $senhaAtual = (string) ($_POST['senha_atual'] ?? '');
        $novaSenha = (string) ($_POST['nova_senha'] ?? '');
        $confirmacao = (string) ($_POST['confirmar_senha'] ?? '');

        $errors = [];
        if ($senhaAtual === '') {
            $errors[] = 'Senha atual é obrigatória.';
        }
        if (strlen($novaSenha) < 8) {
            $errors[] = 'Nova senha deve ter no mínimo 8 caracteres.';
        }
        if ($novaSenha !== $confirmacao) {
            $errors[] = 'Confirmação de senha não confere.';
        }

        $usuario = $this->usuarioModel->findById((int) $usuarioId);
        if (!$usuario) {
            setFlash('error', 'Usuário não encontrado.');
            $this->redirect('dashboard');
        }

        if (!password_verify($senhaAtual, (string) ($usuario['senha'] ?? ''))) {
            $errors[] = 'Senha atual incorreta.';
        }

        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            $this->redirect('perfil');
        }

        if ($this->usuarioModel->atualizarSenha((int) $usuarioId, $novaSenha)) {
            setFlash('success', 'Senha atualizada com sucesso!');
            
            // Log de alteração de senha
            logSistema('senha_alterada', 'perfil', $usuarioId);
        } else {
            setFlash('error', 'Erro ao atualizar senha.');
        }

        $this->redirect('perfil');
    }
}
