<?php
/**
 * Model para Comunicações (WhatsApp/Email)
 */
namespace App\Models;

class Comunicacao extends Model
{
    protected string $table = 'comunicacoes';

    /**
     * Templates padrão de mensagens
     */
    public static array $templates = [
        'os_criada' => [
            'nome' => 'OS Criada',
            'mensagem' => "Olá {{cliente_nome}}! 👋\n\nSua *Ordem de Serviço #{{numero_os}}* foi criada com sucesso!\n\n📋 Serviço: {{servico}}\n💰 Valor: {{valor}}\n📅 Previsão: {{previsao}}\n\nAcompanhe em tempo real:\n{{link_acompanhamento}}\n\nQualquer dúvida, estamos à disposição!"
        ],
        'orcamento_enviado' => [
            'nome' => 'Orçamento Enviado',
            'mensagem' => "Olá {{cliente_nome}}!\n\nSeu orçamento para a *OS #{{numero_os}}* está pronto!\n\n📋 Serviço: {{servico}}\n💰 Valor: {{valor}}\n\nPor favor, aprove ou entre em contato.\n\nLink: {{link_acompanhamento}}"
        ],
        'os_finalizada' => [
            'nome' => 'OS Finalizada',
            'mensagem' => "Olá {{cliente_nome}}! ✅\n\nSua *Ordem de Serviço #{{numero_os}}* foi finalizada!\n\n📋 Serviço: {{servico}}\n💰 Valor: {{valor}}\n\nAguardamos seu pagamento.\n\n{{link_acompanhamento}}"
        ],
        'pagamento_recebido' => [
            'nome' => 'Pagamento Recebido',
            'mensagem' => "Olá {{cliente_nome}}! 🎉\n\nPagamento confirmado para a *OS #{{numero_os}}*!\n\n💰 Valor: {{valor}}\n📄 Recibo: {{link_recibo}}\n\nObrigado pela preferência!"
        ],
        'garantia' => [
            'nome' => 'Lembrete de Garantia',
            'mensagem' => "Olá {{cliente_nome}}!\n\nA garantia do serviço *OS #{{numero_os}}* termina em *{{dias_restantes}} dias*.\n\nAproveite para verificar se tudo está funcionando perfeitamente.\n\nQualquer problema, entre em contato!"
        ]
    ];

    /**
     * Registra comunicação enviada
     */
    public function registrar(int $osId, int $clienteId, string $tipo, string $template, string $mensagem, string $status = 'enviado'): bool
    {
        $data = [
            'empresa_id' => getEmpresaId() ?? 0,
            'os_id' => $osId,
            'cliente_id' => $clienteId,
            'tipo' => $tipo,
            'template_usado' => $template,
            'mensagem_enviada' => $mensagem,
            'status' => $status
        ];

        return $this->create($data) > 0;
    }

    /**
     * Lista comunicações de uma OS
     */
    public function listarPorOS(int $osId): array
    {
        $sql = "
            SELECT c.*, cl.nome as cliente_nome
            FROM {$this->table} c
            LEFT JOIN clientes cl ON c.cliente_id = cl.id
            WHERE c.os_id = ?
            ORDER BY c.created_at DESC
        ";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$osId]);
        
        return $stmt->fetchAll();
    }

    /**
     * Processa template com variáveis
     */
    public static function processarTemplate(string $template, array $variaveis): string
    {
        $mensagem = self::$templates[$template]['mensagem'] ?? '';
        
        foreach ($variaveis as $chave => $valor) {
            $mensagem = str_replace('{{' . $chave . '}}', $valor, $mensagem);
        }
        
        return $mensagem;
    }

    /**
     * Gera link wa.me para envio WhatsApp
     */
    public static function gerarLinkWhatsApp(string $telefone, string $mensagem): string
    {
        // Remove caracteres não numéricos do telefone
        $telefoneLimpo = preg_replace('/[^0-9]/', '', $telefone);
        
        // Adiciona código do país se necessário
        if (strlen($telefoneLimpo) === 10 || strlen($telefoneLimpo) === 11) {
            $telefoneLimpo = '55' . $telefoneLimpo;
        }
        
        // Codifica mensagem para URL
        $mensagemCodificada = urlencode($mensagem);
        
        return "https://wa.me/{$telefoneLimpo}?text={$mensagemCodificada}";
    }
}
