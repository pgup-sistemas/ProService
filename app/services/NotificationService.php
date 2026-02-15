<?php
/**
 * proService - NotificationService
 * Dispara notificações automáticas (WhatsApp/Email) conforme eventos e config enviar_notificacoes_auto
 */

namespace App\Services;

use App\Models\Configuracao;
use App\Models\Comunicacao;
use App\Models\OrdemServico;
use App\Models\Recibo;

class NotificationService
{
    private int $empresaId;
    private Configuracao $configModel;
    private Comunicacao $comunicacaoModel;
    private OrdemServico $osModel;

    public function __construct(int $empresaId)
    {
        $this->empresaId = $empresaId;
        $this->configModel = new Configuracao();
        $this->comunicacaoModel = new Comunicacao();
        $this->osModel = new OrdemServico();
    }

    private function notificacoesAutoHabilitadas(): bool
    {
        $config = $this->configModel->getByEmpresaId($this->empresaId);
        return !empty($config['enviar_notificacoes_auto']);
    }

    private function getVariaveisOS(array $os, ?string $linkRecibo = null): array
    {
        $numeroOs = str_pad($os['numero_os'] ?? 0, 4, '0', STR_PAD_LEFT);
        $valor = isset($os['valor_total']) ? 'R$ ' . number_format((float) $os['valor_total'], 2, ',', '.') : 'R$ 0,00';
        $previsao = !empty($os['previsao_entrega']) ? date('d/m/Y', strtotime($os['previsao_entrega'])) : 'A definir';
        $link = !empty($os['token_publico']) ? url('acompanhar/' . $os['token_publico']) : '';

        return [
            'cliente_nome' => $os['cliente_nome'] ?? 'Cliente',
            'numero_os' => $numeroOs,
            'servico' => $os['servico_nome'] ?? ($os['descricao'] ?? 'Serviço'),
            'valor' => $valor,
            'previsao' => $previsao,
            'link_acompanhamento' => $link,
            'link_recibo' => $linkRecibo ?? $link
        ];
    }

    public function notificarOsCriada(int $osId): void
    {
        if (!$this->notificacoesAutoHabilitadas()) {
            return;
        }

        $os = $this->osModel->findComplete($osId);
        if (!$os || empty($os['cliente_id'])) {
            return;
        }

        $variaveis = $this->getVariaveisOS($os);
        $mensagem = Comunicacao::processarTemplate('os_criada', $variaveis);

        $this->comunicacaoModel->registrar($osId, (int) $os['cliente_id'], 'whatsapp', 'os_criada', $mensagem, 'disparo_auto');

        $email = $os['cliente_email'] ?? null;
        if ($email && filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $emailService = new EmailService($this->empresaId);
            if ($emailService->isConfigured()) {
                $assunto = "OS #{$variaveis['numero_os']} criada - {$variaveis['servico']}";
                $enviado = $emailService->send($email, $assunto, nl2br(e($mensagem)), true);
                $this->comunicacaoModel->registrar($osId, (int) $os['cliente_id'], 'email', 'os_criada', $mensagem, $enviado ? 'enviado' : 'falha');
            }
        }
    }

    public function notificarMudancaStatus(int $osId, string $statusAnterior, string $novoStatus): void
    {
        if (!$this->notificacoesAutoHabilitadas()) {
            return;
        }

        $os = $this->osModel->findComplete($osId);
        if (!$os || empty($os['cliente_id'])) {
            return;
        }

        $template = null;
        $linkRecibo = null;
        if ($novoStatus === 'finalizada') {
            $template = 'os_finalizada';
        } elseif ($novoStatus === 'paga') {
            $template = 'pagamento_recebido';
            $reciboModel = new Recibo();
            $recibo = $reciboModel->findByOS($osId);
            $linkRecibo = $recibo ? url('recibos/show/' . $recibo['id']) : null;
        }

        if (!$template) {
            return;
        }
        $variaveis = $this->getVariaveisOS($os, $linkRecibo);
        $mensagem = Comunicacao::processarTemplate($template, $variaveis);

        $this->comunicacaoModel->registrar($osId, (int) $os['cliente_id'], 'whatsapp', $template, $mensagem, 'disparo_auto');

        $email = $os['cliente_email'] ?? null;
        if ($email && filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $emailService = new EmailService($this->empresaId);
            if ($emailService->isConfigured()) {
                $assunto = "OS #{$variaveis['numero_os']} - " . ($template === 'os_finalizada' ? 'Finalizada' : 'Pagamento confirmado');
                $enviado = $emailService->send($email, $assunto, nl2br(e($mensagem)), true);
                $this->comunicacaoModel->registrar($osId, (int) $os['cliente_id'], 'email', $template, $mensagem, $enviado ? 'enviado' : 'falha');
            }
        }
    }
}
