<?php
/**
 * proService - ClienteController
 * Arquivo: /app/controllers/ClienteController.php
 */

namespace App\Controllers;

use App\Models\Cliente;

class ClienteController extends Controller
{
    private Cliente $clienteModel;

    public function __construct()
    {
        $this->clienteModel = new Cliente();
    }

    /**
     * Lista de clientes
     */
    public function index(): void
    {
        $filtros = [];
        
        // Aplicar filtros da URL
        if (!empty($_GET['busca'])) {
            $filtros['busca'] = sanitizeInput($_GET['busca']);
        }
        
        $page = (int) ($_GET['page'] ?? 1);
        $perPage = 20;
        
        // Se houver busca, usar método de busca
        if (!empty($filtros['busca'])) {
            $clientes = $this->clienteModel->buscar($filtros['busca']);
            $paginacao = [
                'items' => $clientes,
                'total' => count($clientes),
                'page' => 1,
                'per_page' => count($clientes),
                'last_page' => 1
            ];
        } else {
            $paginacao = $this->clienteModel->paginate($page, $perPage, ['ativo' => 1], 'nome ASC');
        }
        
        $this->layout('main', [
            'titulo' => 'Clientes',
            'content' => $this->renderView('clientes/index', [
                'clientes' => $paginacao['items'],
                'paginacao' => $paginacao,
                'busca' => $filtros['busca'] ?? ''
            ])
        ]);
    }

    /**
     * Formulário de novo cliente
     */
    public function create(): void
    {
        $this->layout('main', [
            'titulo' => 'Novo Cliente',
            'content' => $this->renderView('clientes/create', [
                'old' => $this->getOldData()
            ])
        ]);
    }

    /**
     * Salva novo cliente
     */
    public function store(): void
    {
        if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
            setFlash('error', 'Token de segurança inválido.');
            $this->redirect('clientes/create');
        }

        $data = $this->getFormData();
        
        // Validações
        $errors = [];
        if (empty($data['nome'])) {
            $errors[] = 'Nome é obrigatório.';
        }
        
        // Verificar se CPF/CNPJ já existe
        if (!empty($data['cpf_cnpj'])) {
            $existente = $this->clienteModel->findByCpfCnpj($data['cpf_cnpj']);
            if ($existente) {
                $errors[] = 'CPF/CNPJ já cadastrado.';
            }
        }
        
        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            $this->setOldData($data);
            $this->redirect('clientes/create');
        }

        $id = $this->clienteModel->create($data);
        
        if ($id) {
            setFlash('success', 'Cliente cadastrado com sucesso!');
            $this->redirect('clientes');
        } else {
            setFlash('error', 'Erro ao cadastrar cliente.');
            $this->redirect('clientes/create');
        }
    }

    /**
     * Formulário de edição
     */
    public function edit(int $id): void
    {
        $cliente = $this->clienteModel->findById($id);
        
        if (!$cliente) {
            setFlash('error', 'Cliente não encontrado.');
            $this->redirect('clientes');
        }
        
        $historico = $this->clienteModel->getHistorico($id);
        
        $this->layout('main', [
            'titulo' => 'Editar Cliente',
            'content' => $this->renderView('clientes/edit', [
                'cliente' => $cliente,
                'historico' => $historico
            ])
        ]);
    }

    /**
     * Atualiza cliente
     */
    public function update(int $id): void
    {
        if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
            setFlash('error', 'Token de segurança inválido.');
            $this->redirect('clientes/edit/' . $id);
        }

        $cliente = $this->clienteModel->findById($id);
        
        if (!$cliente) {
            setFlash('error', 'Cliente não encontrado.');
            $this->redirect('clientes');
        }

        $data = $this->getFormData();
        
        // Validações
        $errors = [];
        if (empty($data['nome'])) {
            $errors[] = 'Nome é obrigatório.';
        }
        
        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            $this->redirect('clientes/edit/' . $id);
        }

        if ($this->clienteModel->update($id, $data)) {
            setFlash('success', 'Cliente atualizado com sucesso!');
        } else {
            setFlash('error', 'Erro ao atualizar cliente.');
        }
        
        $this->redirect('clientes');
    }

    /**
     * Exclui cliente
     */
    public function delete(int $id): void
    {
        if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
            setFlash('error', 'Token de segurança inválido.');
            $this->redirect('clientes');
        }

        $cliente = $this->clienteModel->findById($id);
        
        if (!$cliente) {
            setFlash('error', 'Cliente não encontrado.');
            $this->redirect('clientes');
        }

        // Verificar se tem OS vinculada
        $historico = $this->clienteModel->getHistorico($id);
        if (!empty($historico['ordens_servico'])) {
            setFlash('error', 'Não é possível excluir cliente com ordens de serviço vinculadas.');
            $this->redirect('clientes');
        }

        // Desativar ao invés de excluir
        if ($this->clienteModel->update($id, ['ativo' => 0])) {
            setFlash('success', 'Cliente removido com sucesso!');
        } else {
            setFlash('error', 'Erro ao remover cliente.');
        }
        
        $this->redirect('clientes');
    }

    /**
     * Busca AJAX de clientes
     */
    public function buscar(): void
    {
        $termo = sanitizeInput($_GET['q'] ?? '');
        
        if (strlen($termo) < 2) {
            $this->jsonResponse([]);
        }
        
        $clientes = $this->clienteModel->buscar($termo);
        
        $resultado = array_map(function($cliente) {
            return [
                'id' => $cliente['id'],
                'nome' => $cliente['nome'],
                'telefone' => $cliente['telefone'],
                'text' => $cliente['nome'] . ' - ' . formatPhone($cliente['telefone'])
            ];
        }, $clientes);
        
        $this->jsonResponse($resultado);
    }

    /**
     * Detalhes do cliente (API)
     */
    public function show(int $id): void
    {
        $cliente = $this->clienteModel->findById($id);
        
        if (!$cliente) {
            $this->jsonResponse(['error' => 'Cliente não encontrado'], 404);
        }
        
        $this->jsonResponse($cliente);
    }

    /**
     * Obtém dados do formulário
     */
    private function getFormData(): array
    {
        return [
            'nome' => sanitizeInput($_POST['nome'] ?? ''),
            'cpf_cnpj' => sanitizeInput($_POST['cpf_cnpj'] ?? ''),
            'telefone' => sanitizeInput($_POST['telefone'] ?? ''),
            'whatsapp' => sanitizeInput($_POST['whatsapp'] ?? ''),
            'email' => sanitizeInput($_POST['email'] ?? ''),
            'cep' => sanitizeInput($_POST['cep'] ?? ''),
            'endereco' => sanitizeInput($_POST['endereco'] ?? ''),
            'numero' => sanitizeInput($_POST['numero'] ?? ''),
            'complemento' => sanitizeInput($_POST['complemento'] ?? ''),
            'bairro' => sanitizeInput($_POST['bairro'] ?? ''),
            'cidade' => sanitizeInput($_POST['cidade'] ?? ''),
            'estado' => sanitizeInput($_POST['estado'] ?? ''),
            'data_nascimento' => !empty($_POST['data_nascimento']) ? $_POST['data_nascimento'] : null,
            'observacoes' => sanitizeInput($_POST['observacoes'] ?? ''),
            'como_conheceu' => sanitizeInput($_POST['como_conheceu'] ?? '')
        ];
    }

    private function getOldData(): array
    {
        return $_SESSION['old'] ?? [];
    }

    private function setOldData(array $data): void
    {
        $_SESSION['old'] = $data;
    }

    /**
     * Renderiza uma view e retorna o conteúdo
     */
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
