<?php
/**
 * proService - ServicoController
 * Arquivo: /app/controllers/ServicoController.php
 */

namespace App\Controllers;

use App\Models\Servico;

class ServicoController extends Controller
{
    private Servico $servicoModel;

    public function __construct()
    {
        $this->servicoModel = new Servico();
    }

    /**
     * Lista de serviços
     */
    public function index(): void
    {
        $page = (int) ($_GET['page'] ?? 1);
        $busca = sanitizeInput($_GET['busca'] ?? '');
        
        if (!empty($busca)) {
            $servicos = $this->servicoModel->buscar($busca);
            $paginacao = [
                'items' => $servicos,
                'total' => count($servicos),
                'page' => 1,
                'per_page' => count($servicos),
                'last_page' => 1
            ];
        } else {
            $paginacao = $this->servicoModel->paginate($page, 20, ['ativo' => 1], 'nome ASC');
        }
        
        $categorias = $this->servicoModel->getCategorias();
        $maisUtilizados = $this->servicoModel->getMaisUtilizados(5);
        
        $this->layout('main', [
            'titulo' => 'Serviços',
            'content' => $this->renderView('servicos/index', [
                'servicos' => $paginacao['items'],
                'paginacao' => $paginacao,
                'categorias' => $categorias,
                'maisUtilizados' => $maisUtilizados,
                'busca' => $busca
            ])
        ]);
    }

    /**
     * Formulário de novo serviço
     */
    public function create(): void
    {
        $this->layout('main', [
            'titulo' => 'Novo Serviço',
            'content' => $this->renderView('servicos/create', [
                'old' => $this->getOldData()
            ])
        ]);
    }

    /**
     * Salva novo serviço
     */
    public function store(): void
    {
        if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
            setFlash('error', 'Token de segurança inválido.');
            $this->redirect('servicos/create');
        }

        $data = $this->getFormData();
        
        if (empty($data['nome'])) {
            $_SESSION['errors'] = ['Nome do serviço é obrigatório.'];
            $this->setOldData($data);
            $this->redirect('servicos/create');
        }

        $id = $this->servicoModel->create($data);
        
        if ($id) {
            setFlash('success', 'Serviço cadastrado com sucesso!');
            $this->redirect('servicos');
        } else {
            setFlash('error', 'Erro ao cadastrar serviço.');
            $this->redirect('servicos/create');
        }
    }

    /**
     * Formulário de edição
     */
    public function edit(int $id): void
    {
        $servico = $this->servicoModel->findById($id);
        
        if (!$servico) {
            setFlash('error', 'Serviço não encontrado.');
            $this->redirect('servicos');
        }
        
        $this->layout('main', [
            'titulo' => 'Editar Serviço',
            'content' => $this->renderView('servicos/edit', [
                'servico' => $servico
            ])
        ]);
    }

    /**
     * Atualiza serviço
     */
    public function update(int $id): void
    {
        if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
            setFlash('error', 'Token de segurança inválido.');
            $this->redirect('servicos/edit/' . $id);
        }

        $servico = $this->servicoModel->findById($id);
        
        if (!$servico) {
            setFlash('error', 'Serviço não encontrado.');
            $this->redirect('servicos');
        }

        $data = $this->getFormData();
        
        if (empty($data['nome'])) {
            $_SESSION['errors'] = ['Nome do serviço é obrigatório.'];
            $this->redirect('servicos/edit/' . $id);
        }

        if ($this->servicoModel->update($id, $data)) {
            setFlash('success', 'Serviço atualizado com sucesso!');
        } else {
            setFlash('error', 'Erro ao atualizar serviço.');
        }
        
        $this->redirect('servicos');
    }

    /**
     * Exclui serviço
     */
    public function delete(int $id): void
    {
        if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
            setFlash('error', 'Token de segurança inválido.');
            $this->redirect('servicos');
        }

        $servico = $this->servicoModel->findById($id);
        
        if (!$servico) {
            setFlash('error', 'Serviço não encontrado.');
            $this->redirect('servicos');
        }

        // Desativar ao invés de excluir
        if ($this->servicoModel->update($id, ['ativo' => 0])) {
            setFlash('success', 'Serviço removido com sucesso!');
        } else {
            setFlash('error', 'Erro ao remover serviço.');
        }
        
        $this->redirect('servicos');
    }

    /**
     * Duplica serviço
     */
    public function duplicar(int $id): void
    {
        if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
            setFlash('error', 'Token de segurança inválido.');
            $this->redirect('servicos');
        }

        $novoId = $this->servicoModel->duplicar($id);
        
        if ($novoId) {
            setFlash('success', 'Serviço duplicado com sucesso!');
            $this->redirect('servicos/edit/' . $novoId);
        } else {
            setFlash('error', 'Erro ao duplicar serviço.');
            $this->redirect('servicos');
        }
    }

    /**
     * Busca AJAX de serviços
     */
    public function buscar(): void
    {
        $termo = sanitizeInput($_GET['q'] ?? '');
        
        if (strlen($termo) < 2) {
            $this->jsonResponse([]);
        }
        
        $servicos = $this->servicoModel->buscar($termo);
        
        $resultado = array_map(function($servico) {
            return [
                'id' => $servico['id'],
                'nome' => $servico['nome'],
                'valor' => $servico['valor_padrao'],
                'garantia' => $servico['garantia_dias'],
                'text' => $servico['nome'] . ' (R$ ' . number_format($servico['valor_padrao'], 2, ',', '.') . ')'
            ];
        }, $servicos);
        
        $this->jsonResponse($resultado);
    }

    /**
     * Detalhes do serviço (API)
     */
    public function show(int $id): void
    {
        $servico = $this->servicoModel->findById($id);
        
        if (!$servico) {
            $this->jsonResponse(['error' => 'Serviço não encontrado'], 404);
        }
        
        $this->jsonResponse($servico);
    }

    private function getFormData(): array
    {
        return [
            'nome' => sanitizeInput($_POST['nome'] ?? ''),
            'categoria' => sanitizeInput($_POST['categoria'] ?? ''),
            'descricao_padrao' => sanitizeInput($_POST['descricao_padrao'] ?? ''),
            'valor_padrao' => parseMoney($_POST['valor_padrao'] ?? '0'),
            'garantia_dias' => (int) ($_POST['garantia_dias'] ?? 0),
            'tempo_medio_horas' => (float) ($_POST['tempo_medio_horas'] ?? 0)
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
