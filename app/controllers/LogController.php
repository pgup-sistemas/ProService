<?php
/**
 * proService - LogController
 * Visualização de Logs do Sistema (Admin only)
 */

namespace App\Controllers;

use App\Models\LogSistema;
use App\Models\Usuario;

class LogController extends Controller
{
    private LogSistema $logModel;
    private Usuario $usuarioModel;

    public function __construct()
    {
        $this->logModel = new LogSistema();
        $this->usuarioModel = new Usuario();
    }

    public function index(): void
    {
        \App\Middlewares\AuthMiddleware::adminOnly();

        $page = (int) ($_GET['page'] ?? 1);
        $filtros = [
            'acao' => $_GET['acao'] ?? '',
            'modulo' => $_GET['modulo'] ?? '',
            'nivel' => $_GET['nivel'] ?? '',
            'usuario_id' => $_GET['usuario_id'] ?? '',
            'data_inicio' => $_GET['data_inicio'] ?? date('Y-m-d', strtotime('-7 days')),
            'data_fim' => $_GET['data_fim'] ?? date('Y-m-d'),
        ];

        // Remove filtros vazios
        $filtros = array_filter($filtros);

        $result = $this->logModel->buscar($filtros, $page, 50);
        $stats = $this->logModel->estatisticas(7);
        $usuarios = $this->usuarioModel->listarTodos();

        $this->layout('main', [
            'titulo' => 'Logs do Sistema',
            'content' => $this->render('logs/index', [
                'logs' => $result['items'],
                'paginacao' => $result,
                'stats' => $stats,
                'usuarios' => $usuarios,
                'filtros' => $filtros,
            ])
        ]);
    }

    public function limpar(): void
    {
        \App\Middlewares\AuthMiddleware::adminOnly();

        if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
            setFlash('error', 'Token de segurança inválido.');
            $this->redirect('logs');
        }

        $dias = (int) ($_POST['dias'] ?? 90);
        $removidos = $this->logModel->limparAntigos($dias);

        setFlash('success', "{$removidos} logs removidos com sucesso.");
        $this->redirect('logs');
    }
}
