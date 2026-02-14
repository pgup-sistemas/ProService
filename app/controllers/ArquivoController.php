<?php
/**
 * proService - ArquivoController
 * Arquivo: /app/controllers/ArquivoController.php
 */

namespace App\Controllers;

use App\Models\Assinatura;
use App\Models\Despesa;

class ArquivoController extends Controller
{
    public function assinatura(int $id): void
    {
        $assinaturaModel = new Assinatura();
        $assinatura = $assinaturaModel->findById($id);

        if (!$assinatura) {
            http_response_code(404);
            echo 'Arquivo não encontrado.';
            return;
        }

        $relPath = $assinatura['arquivo'] ?? '';
        if ($relPath === '') {
            http_response_code(404);
            echo 'Arquivo não encontrado.';
            return;
        }

        $filepath = rtrim(PROSERVICE_ROOT, '/\\') . '/public/' . ltrim($relPath, '/\\');
        $this->outputFile($filepath);
    }

    public function comprovante(int $id): void
    {
        $despesaModel = new Despesa();
        $despesa = $despesaModel->findById($id);

        if (!$despesa) {
            http_response_code(404);
            echo 'Arquivo não encontrado.';
            return;
        }

        $relPath = $despesa['comprovante'] ?? '';
        if ($relPath === '') {
            http_response_code(404);
            echo 'Arquivo não encontrado.';
            return;
        }

        $filepath = rtrim(PROSERVICE_ROOT, '/\\') . '/public/uploads/' . ltrim($relPath, '/\\');
        $this->outputFile($filepath);
    }

    private function outputFile(string $filepath): void
    {
        $real = realpath($filepath);
        if ($real === false || !is_file($real)) {
            http_response_code(404);
            echo 'Arquivo não encontrado.';
            return;
        }

        $mime = @mime_content_type($real);
        if (!$mime) {
            $mime = 'application/octet-stream';
        }

        header('Content-Type: ' . $mime);
        header('Content-Length: ' . filesize($real));
        header('X-Content-Type-Options: nosniff');
        header('Content-Disposition: inline; filename="' . basename($real) . '"');

        readfile($real);
        exit;
    }
}
