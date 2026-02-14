<?php
/**
 * proService - PublicoController (Acompanhamento via link público)
 * Arquivo: /app/controllers/PublicoController.php
 */

namespace App\Controllers;

use App\Models\OrdemServico;

class PublicoController extends Controller
{
    private OrdemServico $osModel;

    public function __construct()
    {
        $this->osModel = new OrdemServico();
    }

    /**
     * Página de acompanhamento pública da OS
     */
    public function acompanhar(string $token): void
    {
        $os = $this->osModel->findByToken($token);
        
        if (!$os) {
            http_response_code(404);
            $this->view('publicos/nao_encontrado', [
                'titulo' => 'OS não encontrada'
            ]);
            return;
        }
        
        // Registrar visualização (log)
        $this->registrarVisualizacao($os['id']);
        
        // Produtos da OS
        $produtos = $this->osModel->getProdutos($os['id']);
        
        $this->view('publicos/acompanhar', [
            'titulo' => 'OS #' . str_pad($os['numero_os'], 4, '0', STR_PAD_LEFT) . ' - ' . APP_NAME,
            'os' => $os,
            'produtos' => $produtos
        ]);
    }

    /**
     * Gera novo token para a OS
     */
    public function gerarToken(int $osId): void
    {
        if (!isLoggedIn()) {
            setFlash('error', 'Acesso negado.');
            redirect('login');
        }

        $os = $this->osModel->findById($osId);
        
        if (!$os || $os['empresa_id'] !== getEmpresaId()) {
            setFlash('error', 'OS não encontrada.');
            redirect('ordens');
        }

        // Gerar novo token
        $novoToken = md5(uniqid(rand(), true));
        
        if ($this->osModel->update($osId, ['token_publico' => $novoToken])) {
            setFlash('success', 'Novo link de acompanhamento gerado!');
        } else {
            setFlash('error', 'Erro ao gerar link.');
        }
        
        redirect('ordens/show/' . $osId);
    }

    /**
     * Registrar visualização no log
     */
    private function registrarVisualizacao(int $osId): void
    {
        // Implementar se necessário (salvar IP, data, etc.)
        // Por enquanto, apenas um log simples
        error_log("OS {$osId} visualizada via link público em " . date('Y-m-d H:i:s'));
    }
}
