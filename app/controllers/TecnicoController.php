<?php

namespace App\Controllers;

use App\Models\Usuario;

class TecnicoController extends Controller
{
    private $usuarioModel;

    public function __construct()
    {
        $this->usuarioModel = new Usuario();
    }

    public function index(): void
    {
        \App\Middlewares\AuthMiddleware::adminOnly();

        $page = (int) ($_GET['page'] ?? 1);
        $perPage = 20;

        $result = $this->usuarioModel->paginate($page, $perPage, ['perfil' => 'tecnico'], 'nome ASC');

        $this->layout('main', [
            'titulo' => 'Técnicos',
            'content' => $this->renderView('tecnicos/index', [
                'tecnicos' => $result['items'],
                'paginacao' => $result,
                'empresa' => getEmpresaDados()
            ])
        ]);
    }

    public function create(): void
    {
        \App\Middlewares\AuthMiddleware::adminOnly();

        $this->layout('main', [
            'titulo' => 'Novo Técnico',
            'content' => $this->renderView('tecnicos/create', [
                'old' => $_SESSION['old'] ?? []
            ])
        ]);
    }

    public function store(): void
    {
        \App\Middlewares\AuthMiddleware::adminOnly();

        if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
            setFlash('error', 'Token de segurança inválido.');
            $this->redirect('tecnicos/create');
        }

        $nome = sanitizeInput($_POST['nome'] ?? '');
        $email = sanitizeInput($_POST['email'] ?? '');
        $telefone = sanitizeInput($_POST['telefone'] ?? '');
        $senha = (string) ($_POST['senha'] ?? '');

        $errors = [];
        if ($nome === '') $errors[] = 'Nome é obrigatório.';
        if (!isValidEmail($email)) $errors[] = 'E-mail inválido.';
        if (strlen($senha) < 8) $errors[] = 'Senha deve ter no mínimo 8 caracteres.';

        $empresa = getEmpresaDados();
        $limite = (int) ($empresa['limite_tecnicos'] ?? 0);
        if ($limite !== -1) {
            $qtde = $this->usuarioModel->contarTecnicos();
            if ($qtde >= $limite) {
                $errors[] = 'Limite de técnicos do seu plano atingido.';
            }
        }

        if ($this->usuarioModel->findByEmail($email)) {
            $errors[] = 'Este e-mail já está cadastrado nesta empresa.';
        }

        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            $_SESSION['old'] = $_POST;
            $this->redirect('tecnicos/create');
        }

        $id = $this->usuarioModel->criar([
            'empresa_id' => getEmpresaId(),
            'nome' => $nome,
            'email' => $email,
            'telefone' => $telefone,
            'senha' => $senha,
            'perfil' => 'tecnico',
            'ativo' => 1
        ]);

        if ($id) {
            setFlash('success', 'Técnico cadastrado com sucesso!');
            unset($_SESSION['old']);
            $this->redirect('tecnicos');
        }

        setFlash('error', 'Erro ao cadastrar técnico.');
        $this->redirect('tecnicos/create');
    }

    public function edit(int $id): void
    {
        \App\Middlewares\AuthMiddleware::adminOnly();

        $tecnico = $this->usuarioModel->findById($id);
        if (!$tecnico || $tecnico['perfil'] !== 'tecnico') {
            setFlash('error', 'Técnico não encontrado.');
            $this->redirect('tecnicos');
        }

        $this->layout('main', [
            'titulo' => 'Editar Técnico',
            'content' => $this->renderView('tecnicos/edit', [
                'tecnico' => $tecnico
            ])
        ]);
    }

    public function update(int $id): void
    {
        \App\Middlewares\AuthMiddleware::adminOnly();

        if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
            setFlash('error', 'Token de segurança inválido.');
            $this->redirect('tecnicos/edit/' . $id);
        }

        $tecnico = $this->usuarioModel->findById($id);
        if (!$tecnico || $tecnico['perfil'] !== 'tecnico') {
            setFlash('error', 'Técnico não encontrado.');
            $this->redirect('tecnicos');
        }

        $nome = sanitizeInput($_POST['nome'] ?? '');
        $email = sanitizeInput($_POST['email'] ?? '');
        $telefone = sanitizeInput($_POST['telefone'] ?? '');

        $errors = [];
        if ($nome === '') $errors[] = 'Nome é obrigatório.';
        if (!isValidEmail($email)) $errors[] = 'E-mail inválido.';

        $existing = $this->usuarioModel->findByEmail($email);
        if ($existing && (int) $existing['id'] !== (int) $id) {
            $errors[] = 'Este e-mail já está cadastrado nesta empresa.';
        }

        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            $this->redirect('tecnicos/edit/' . $id);
        }

        $ok = $this->usuarioModel->update($id, [
            'nome' => $nome,
            'email' => $email,
            'telefone' => $telefone
        ]);

        if ($ok) {
            setFlash('success', 'Técnico atualizado com sucesso!');
        } else {
            setFlash('error', 'Erro ao atualizar técnico.');
        }

        $this->redirect('tecnicos');
    }

    public function toggle(int $id): void
    {
        \App\Middlewares\AuthMiddleware::adminOnly();

        if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
            setFlash('error', 'Token de segurança inválido.');
            $this->redirect('tecnicos');
        }

        $tecnico = $this->usuarioModel->findById($id);
        if (!$tecnico || $tecnico['perfil'] !== 'tecnico') {
            setFlash('error', 'Técnico não encontrado.');
            $this->redirect('tecnicos');
        }

        if ($this->usuarioModel->toggleStatus($id)) {
            setFlash('success', 'Status do técnico atualizado.');
        } else {
            setFlash('error', 'Erro ao atualizar status do técnico.');
        }

        $this->redirect('tecnicos');
    }

    public function resetSenha(int $id): void
    {
        \App\Middlewares\AuthMiddleware::adminOnly();

        if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
            setFlash('error', 'Token de segurança inválido.');
            $this->redirect('tecnicos');
        }

        $tecnico = $this->usuarioModel->findById($id);
        if (!$tecnico || $tecnico['perfil'] !== 'tecnico') {
            setFlash('error', 'Técnico não encontrado.');
            $this->redirect('tecnicos');
        }

        if ($this->usuarioModel->resetarSenha($id)) {
            setFlash('success', 'Senha resetada para: proservice123');
        } else {
            setFlash('error', 'Erro ao resetar senha.');
        }

        $this->redirect('tecnicos');
    }

    private function renderView(string $view, array $data = []): string
    {
        extract($data);
        ob_start();
        $viewPath = __DIR__ . '/../views/' . $view . '.php';
        if (file_exists($viewPath)) {
            include $viewPath;
        }
        return ob_get_clean();
    }
}
