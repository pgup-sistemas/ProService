<?php
/**
 * proService - ReciboController
 * Arquivo: /app/controllers/ReciboController.php
 */

namespace App\Controllers;

use App\Models\Recibo;
use App\Models\Cliente;

class ReciboController extends Controller
{
    private Recibo $reciboModel;
    private Cliente $clienteModel;

    public function __construct()
    {
        $this->reciboModel = new Recibo();
        $this->clienteModel = new Cliente();
    }

    /**
     * Lista de recibos
     */
    public function index(): void
    {
        $filtros = [];
        
        if (!empty($_GET['cliente_id'])) $filtros['cliente_id'] = (int) $_GET['cliente_id'];
        if (!empty($_GET['data_inicio'])) $filtros['data_inicio'] = $_GET['data_inicio'];
        if (!empty($_GET['data_fim'])) $filtros['data_fim'] = $_GET['data_fim'];
        
        $page = (int) ($_GET['page'] ?? 1);
        
        $resultado = $this->reciboModel->listar($filtros, 'r.created_at DESC', $page, 20);
        
        $clientes = $this->clienteModel->findAll(['ativo' => 1], 'nome ASC');
        
        $this->layout('main', [
            'titulo' => 'Recibos de Pagamento',
            'content' => $this->renderView('recibos/index', [
                'recibos' => $resultado['items'],
                'paginacao' => $resultado,
                'clientes' => $clientes,
                'filtros' => $filtros
            ])
        ]);
    }

    /**
     * Visualiza recibo específico
     */
    public function show(int $id): void
    {
        $recibo = $this->reciboModel->findComplete($id);
        
        if (!$recibo) {
            setFlash('error', 'Recibo não encontrado.');
            $this->redirect('recibos');
        }
        
        // Renderizar view sem layout (para impressão) e exibir
        echo $this->renderView('recibos/view', [
            'recibo' => $recibo
        ]);
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
