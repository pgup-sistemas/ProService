<?php
/**
 * proService - UsuarioController
 * Gestão de Usuários (Admin gerencia todos: admins e técnicos)
 */

namespace App\Controllers;

use App\Models\Usuario;

class UsuarioController extends Controller
{
    private Usuario $usuarioModel;

    public function __construct()
    {
        $this->usuarioModel = new Usuario();
    }

    public function index(): void
    {
        \App\Middlewares\AuthMiddleware::adminOnly();

        $page = (int) ($_GET['page'] ?? 1);
        $perPage = 20;
        $filtroPerfil = $_GET['perfil'] ?? '';

        $conditions = [];
        if ($filtroPerfil) {
            $conditions['perfil'] = $filtroPerfil;
        }

        $result = $this->usuarioModel->paginate($page, $perPage, $conditions, 'perfil DESC, nome ASC');

        $this->layout('main', [
            'titulo' => 'Gestão de Usuários',
            'content' => $this->render('usuarios/index', [
                'usuarios' => $result['items'],
                'paginacao' => $result,
                'filtroPerfil' => $filtroPerfil,
                'totalAtivos' => $this->usuarioModel->count(['ativo' => 1]),
                'totalInativos' => $this->usuarioModel->count(['ativo' => 0]),
            ])
        ]);
    }

    public function create(): void
    {
        \App\Middlewares\AuthMiddleware::adminOnly();

        $this->layout('main', [
            'titulo' => 'Novo Usuário',
            'content' => $this->render('usuarios/create', [
                'old' => $_SESSION['old'] ?? [],
                'errors' => $_SESSION['errors'] ?? [],
            ])
        ]);

        unset($_SESSION['old'], $_SESSION['errors']);
    }

    public function store(): void
    {
        \App\Middlewares\AuthMiddleware::adminOnly();

        if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
            setFlash('error', 'Token de segurança inválido.');
            $this->redirect('usuarios/create');
        }

        $nome = sanitizeInput($_POST['nome'] ?? '');
        $email = sanitizeInput($_POST['email'] ?? '');
        $telefone = sanitizeInput($_POST['telefone'] ?? '');
        $perfil = $_POST['perfil'] ?? 'tecnico';
        $senha = (string) ($_POST['senha'] ?? '');

        $errors = [];
        if ($nome === '') $errors[] = 'Nome é obrigatório.';
        if (!isValidEmail($email)) $errors[] = 'E-mail inválido.';
        if (strlen($senha) < 8) $errors[] = 'Senha deve ter no mínimo 8 caracteres.';
        if (!in_array($perfil, ['admin', 'tecnico'])) $errors[] = 'Perfil inválido.';

        if ($perfil === 'tecnico') {
            $empresa = getEmpresaDados();
            $limite = (int) ($empresa['limite_tecnicos'] ?? 0);
            if ($limite !== -1) {
                $qtde = $this->usuarioModel->contarTecnicos();
                if ($qtde >= $limite) {
                    $errors[] = 'Limite de técnicos do seu plano atingido. Faça upgrade para adicionar mais.';
                }
            }
        }

        if ($this->usuarioModel->findByEmail($email)) {
            $errors[] = 'Este e-mail já está cadastrado nesta empresa.';
        }

        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            $_SESSION['old'] = $_POST;
            $this->redirect('usuarios/create');
        }

        $id = $this->usuarioModel->criar([
            'empresa_id' => getEmpresaId(),
            'nome' => $nome,
            'email' => $email,
            'telefone' => $telefone,
            'senha' => $senha,
            'perfil' => $perfil,
            'ativo' => 1
        ]);

        if ($id) {
            setFlash('success', 'Usuário cadastrado com sucesso!');
            unset($_SESSION['old']);
            
            // Log de criação de usuário
            logSistema('usuario_criado', 'usuarios', $id, ['perfil' => $perfil]);
            
            $this->redirect('usuarios');
        }

        setFlash('error', 'Erro ao cadastrar usuário.');
        $this->redirect('usuarios/create');
    }

    public function edit(int $id): void
    {
        \App\Middlewares\AuthMiddleware::adminOnly();

        $usuario = $this->usuarioModel->findById($id);
        if (!$usuario) {
            setFlash('error', 'Usuário não encontrado.');
            $this->redirect('usuarios');
        }

        // Não permitir editar a si mesmo aqui (use /perfil)
        if ((int) $usuario['id'] === getUsuarioId()) {
            setFlash('info', 'Use "Meu Perfil" para editar seus próprios dados.');
            $this->redirect('perfil');
        }

        $this->layout('main', [
            'titulo' => 'Editar Usuário',
            'content' => $this->render('usuarios/edit', [
                'usuario' => $usuario,
                'old' => $_SESSION['old'] ?? [],
                'errors' => $_SESSION['errors'] ?? [],
            ])
        ]);

        unset($_SESSION['old'], $_SESSION['errors']);
    }

    public function update(int $id): void
    {
        \App\Middlewares\AuthMiddleware::adminOnly();

        if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
            setFlash('error', 'Token de segurança inválido.');
            $this->redirect('usuarios/edit/' . $id);
        }

        $usuario = $this->usuarioModel->findById($id);
        if (!$usuario) {
            setFlash('error', 'Usuário não encontrado.');
            $this->redirect('usuarios');
        }

        // Não permitir editar a si mesmo aqui
        if ((int) $usuario['id'] === getUsuarioId()) {
            setFlash('info', 'Use "Meu Perfil" para editar seus próprios dados.');
            $this->redirect('perfil');
        }

        $nome = sanitizeInput($_POST['nome'] ?? '');
        $email = sanitizeInput($_POST['email'] ?? '');
        $telefone = sanitizeInput($_POST['telefone'] ?? '');
        $perfil = $_POST['perfil'] ?? $usuario['perfil'];

        $errors = [];
        if ($nome === '') $errors[] = 'Nome é obrigatório.';
        if (!isValidEmail($email)) $errors[] = 'E-mail inválido.';
        if (!in_array($perfil, ['admin', 'tecnico'])) $errors[] = 'Perfil inválido.';

        $existing = $this->usuarioModel->findByEmail($email);
        if ($existing && (int) $existing['id'] !== (int) $id) {
            $errors[] = 'Este e-mail já está cadastrado nesta empresa.';
        }

        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            $_SESSION['old'] = $_POST;
            $this->redirect('usuarios/edit/' . $id);
        }

        $ok = $this->usuarioModel->update($id, [
            'nome' => $nome,
            'email' => $email,
            'telefone' => $telefone,
            'perfil' => $perfil,
        ]);

        if ($ok) {
            setFlash('success', 'Usuário atualizado com sucesso!');
            
            // Log de atualização de usuário
            logSistema('usuario_atualizado', 'usuarios', $id, ['perfil' => $perfil]);
        } else {
            setFlash('error', 'Erro ao atualizar usuário.');
        }

        $this->redirect('usuarios');
    }

    public function toggle(int $id): void
    {
        \App\Middlewares\AuthMiddleware::adminOnly();

        if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
            setFlash('error', 'Token de segurança inválido.');
            $this->redirect('usuarios');
        }

        $usuario = $this->usuarioModel->findById($id);
        if (!$usuario) {
            setFlash('error', 'Usuário não encontrado.');
            $this->redirect('usuarios');
        }

        // Não permitir desativar a si mesmo
        if ((int) $usuario['id'] === getUsuarioId()) {
            setFlash('error', 'Você não pode desativar seu próprio usuário.');
            $this->redirect('usuarios');
        }

        if ($this->usuarioModel->toggleStatus($id)) {
            $status = $usuario['ativo'] ? 'desativado' : 'ativado';
            setFlash('success', 'Usuário ' . $status . ' com sucesso!');
            
            // Log de toggle de status
            logSistema('usuario_' . $status, 'usuarios', $id, ['ativo' => !$usuario['ativo']]);
        } else {
            setFlash('error', 'Erro ao alterar status do usuário.');
        }

        $this->redirect('usuarios');
    }

    public function resetSenha(int $id): void
    {
        \App\Middlewares\AuthMiddleware::adminOnly();

        if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
            setFlash('error', 'Token de segurança inválido.');
            $this->redirect('usuarios');
        }

        $usuario = $this->usuarioModel->findById($id);
        if (!$usuario) {
            setFlash('error', 'Usuário não encontrado.');
            $this->redirect('usuarios');
        }

        if ($this->usuarioModel->resetarSenha($id)) {
            setFlash('success', 'Senha resetada para: proservice123');
            
            // Log de reset de senha
            logSistema('senha_resetada', 'usuarios', $id);
        } else {
            setFlash('error', 'Erro ao resetar senha.');
        }

        $this->redirect('usuarios');
    }
}
