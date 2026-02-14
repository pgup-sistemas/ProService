<?php
/**
 * proService - Model Configuracao
 * Arquivo: /app/models/Configuracao.php
 * 
 * Gerencia configurações da empresa:
 * - Cores personalizadas
 * - Templates de contrato
 * - Templates de mensagens WhatsApp
 * - Configurações SMTP
 * - Preferências de sistema
 */

namespace App\Models;

class Configuracao extends Model
{
    protected string $table = 'configuracoes_empresa';

    /**
     * Busca configurações da empresa ou cria padrão se não existir
     */
    public function getByEmpresaId(int $empresaId): array
    {
        $config = $this->findBy('empresa_id', $empresaId);
        
        if (!$config) {
            // Cria configurações padrão
            $this->create([
                'empresa_id' => $empresaId,
                'cor_primaria' => '#1e40af',
                'cor_sucesso' => '#059669',
                'cor_alerta' => '#ea580c',
                'template_contrato' => $this->getTemplateContratoPadrao(),
                'mensagem_whatsapp_os_criada' => $this->getTemplateWhatsAppOScriada(),
                'mensagem_whatsapp_os_finalizada' => $this->getTemplateWhatsAppOSfinalizada(),
                'mensagem_whatsapp_recibo' => $this->getTemplateWhatsAppRecibo(),
                'enviar_notificacoes_auto' => 0
            ]);
            
            $config = $this->findBy('empresa_id', $empresaId);
        }
        
        return $config;
    }

    /**
     * Atualiza configurações da empresa
     */
    public function atualizar(int $empresaId, array $data): bool
    {
        $config = $this->findBy('empresa_id', $empresaId);
        
        if (!$config) {
            $data['empresa_id'] = $empresaId;
            return (bool) $this->create($data);
        }
        
        return $this->update($config['id'], $data);
    }

    /**
     * Atualiza apenas as cores
     */
    public function atualizarCores(int $empresaId, array $cores): bool
    {
        $config = $this->findBy('empresa_id', $empresaId);
        
        if (!$config) {
            return $this->create([
                'empresa_id' => $empresaId,
                'cor_primaria' => $cores['cor_primaria'] ?? '#1e40af',
                'cor_sucesso' => $cores['cor_sucesso'] ?? '#059669',
                'cor_alerta' => $cores['cor_alerta'] ?? '#ea580c'
            ]);
        }
        
        return $this->update($config['id'], [
            'cor_primaria' => $cores['cor_primaria'] ?? $config['cor_primaria'],
            'cor_sucesso' => $cores['cor_sucesso'] ?? $config['cor_sucesso'],
            'cor_alerta' => $cores['cor_alerta'] ?? $config['cor_alerta']
        ]);
    }

    /**
     * Atualiza template de contrato
     */
    public function atualizarTemplateContrato(int $empresaId, string $template): bool
    {
        $config = $this->findBy('empresa_id', $empresaId);
        
        if (!$config) {
            return $this->create([
                'empresa_id' => $empresaId,
                'template_contrato' => $template
            ]);
        }
        
        return $this->update($config['id'], ['template_contrato' => $template]);
    }

    /**
     * Atualiza templates de WhatsApp
     */
    public function atualizarTemplatesWhatsApp(int $empresaId, array $templates): bool
    {
        $data = [];
        
        if (isset($templates['os_criada'])) {
            $data['mensagem_whatsapp_os_criada'] = $templates['os_criada'];
        }
        if (isset($templates['os_finalizada'])) {
            $data['mensagem_whatsapp_os_finalizada'] = $templates['os_finalizada'];
        }
        if (isset($templates['recibo'])) {
            $data['mensagem_whatsapp_recibo'] = $templates['recibo'];
        }
        
        if (empty($data)) {
            return false;
        }
        
        $config = $this->findBy('empresa_id', $empresaId);
        
        if (!$config) {
            $data['empresa_id'] = $empresaId;
            return (bool) $this->create($data);
        }
        
        return $this->update($config['id'], $data);
    }

    /**
     * Atualiza configurações SMTP
     */
    public function atualizarSMTP(int $empresaId, array $smtp): bool
    {
        // SMTP é armazenado na tabela empresas
        $empresaModel = new Empresa();
        return $empresaModel->update($empresaId, [
            'smtp_host' => $smtp['host'] ?? null,
            'smtp_port' => $smtp['port'] ?? null,
            'smtp_user' => $smtp['user'] ?? null,
            'smtp_pass' => $smtp['pass'] ?? null,
            'smtp_encryption' => $smtp['encryption'] ?? 'tls'
        ]);
    }

    /**
     * Gera contrato preenchido com merge tags
     */
    public function gerarContrato(int $empresaId, array $dados): string
    {
        $config = $this->getByEmpresaId($empresaId);
        $template = $config['template_contrato'] ?? $this->getTemplateContratoPadrao();

        $osValor = $dados['os_valor'] ?? null;
        if (is_string($osValor)) {
            $osValor = trim($osValor);
            if ($osValor !== '') {
                $osValor = preg_replace('/[^0-9,\.\-]/', '', $osValor);
                if (str_contains($osValor, ',') && str_contains($osValor, '.')) {
                    $osValor = str_replace('.', '', $osValor);
                    $osValor = str_replace(',', '.', $osValor);
                } elseif (str_contains($osValor, ',')) {
                    $osValor = str_replace(',', '.', $osValor);
                }
                $osValor = is_numeric($osValor) ? (float) $osValor : null;
            } else {
                $osValor = null;
            }
        }
        
        // Merge tags disponíveis
        $tags = [
            '{{empresa_nome}}' => $dados['empresa_nome'] ?? '',
            '{{empresa_cnpj}}' => $dados['empresa_cnpj'] ?? '',
            '{{empresa_endereco}}' => $dados['empresa_endereco'] ?? '',
            '{{empresa_telefone}}' => $dados['empresa_telefone'] ?? '',
            '{{cliente_nome}}' => $dados['cliente_nome'] ?? '',
            '{{cliente_cpf_cnpj}}' => $dados['cliente_cpf_cnpj'] ?? '',
            '{{cliente_endereco}}' => $dados['cliente_endereco'] ?? '',
            '{{cliente_telefone}}' => $dados['cliente_telefone'] ?? '',
            '{{os_numero}}' => $dados['os_numero'] ?? '',
            '{{os_data}}' => $dados['os_data'] ?? date('d/m/Y'),
            '{{os_servico}}' => $dados['os_servico'] ?? '',
            '{{os_descricao}}' => $dados['os_descricao'] ?? '',
            '{{os_valor}}' => $osValor !== null ? number_format($osValor, 2, ',', '.') : '',
            '{{os_garantia}}' => $dados['os_garantia'] ?? '',
            '{{data_atual}}' => date('d/m/Y'),
            '{{hora_atual}}' => date('H:i')
        ];
        
        return strtr($template, $tags);
    }

    /**
     * Gera mensagem WhatsApp com merge tags
     */
    public function gerarMensagemWhatsApp(int $empresaId, string $tipo, array $dados): string
    {
        $config = $this->getByEmpresaId($empresaId);
        
        $template = match($tipo) {
            'os_criada' => $config['mensagem_whatsapp_os_criada'] ?? $this->getTemplateWhatsAppOScriada(),
            'os_finalizada' => $config['mensagem_whatsapp_os_finalizada'] ?? $this->getTemplateWhatsAppOSfinalizada(),
            'recibo' => $config['mensagem_whatsapp_recibo'] ?? $this->getTemplateWhatsAppRecibo(),
            default => ''
        };
        
        // Merge tags
        $tags = [
            '{{cliente_nome}}' => $dados['cliente_nome'] ?? '',
            '{{os_numero}}' => $dados['os_numero'] ?? '',
            '{{os_link}}' => $dados['os_link'] ?? '',
            '{{empresa_nome}}' => $dados['empresa_nome'] ?? '',
            '{{empresa_telefone}}' => $dados['empresa_telefone'] ?? '',
            '{{valor_total}}' => isset($dados['valor_total']) ? number_format($dados['valor_total'], 2, ',', '.') : '',
            '{{servico_nome}}' => $dados['servico_nome'] ?? ''
        ];
        
        return strtr($template, $tags);
    }

    /**
     * Template de contrato padrão
     */
    private function getTemplateContratoPadrao(): string
    {
        return <<<HTML
<h2>CONTRATO DE PRESTAÇÃO DE SERVIÇOS</h2>

<p><strong>CONTRATANTE:</strong> {{cliente_nome}}, CPF/CNPJ: {{cliente_cpf_cnpj}}, residente em {{cliente_endereco}}, telefone: {{cliente_telefone}}.</p>

<p><strong>CONTRATADA:</strong> {{empresa_nome}}, CNPJ: {{empresa_cnpj}}, endereço: {{empresa_endereco}}, telefone: {{empresa_telefone}}.</p>

<h3>OBJETO DO CONTRATO</h3>
<p>Prestação do serviço de: <strong>{{os_servico}}</strong></p>
<p>Descrição: {{os_descricao}}</p>

<h3>VALOR E FORMA DE PAGAMENTO</h3>
<p>Valor total: R$ {{os_valor}}</p>

<h3>GARANTIA</h3>
<p>Garantia de {{os_garantia}} dias para o serviço prestado, conforme artigo 26 do CDC.</p>

<h3>DATA E ASSINATURAS</h3>
<p>Data: {{data_atual}}</p>

<table style="width: 100%; margin-top: 50px;">
<tr>
<td style="text-align: center;">_________________________________<br>Assinatura do Contratante<br>{{cliente_nome}}</td>
<td style="text-align: center;">_________________________________<br>Assinatura do Responsável<br>{{empresa_nome}}</td>
</tr>
</table>
HTML;
    }

    /**
     * Template WhatsApp - OS Criada
     */
    private function getTemplateWhatsAppOScriada(): string
    {
        return "Olá {{cliente_nome}}!\n\n" .
               "Sua Ordem de Serviço #{{os_numero}} foi registrada com sucesso!\n\n" .
               "📋 Serviço: {{servico_nome}}\n" .
               "💰 Valor: R$ {{valor_total}}\n\n" .
               "Acompanhe o status pelo link:\n{{os_link}}\n\n" .
               "Dúvidas? Fale conosco: {{empresa_telefone}}\n" .
               "{{empresa_nome}}";
    }

    /**
     * Template WhatsApp - OS Finalizada
     */
    private function getTemplateWhatsAppOSfinalizada(): string
    {
        return "Olá {{cliente_nome}}!\n\n" .
               "✅ Ótima notícia! Sua Ordem de Serviço #{{os_numero}} foi finalizada!\n\n" .
               "📋 Serviço: {{servico_nome}}\n" .
               "💰 Valor: R$ {{valor_total}}\n\n" .
               "Aguardamos seu contato para retirada.\n\n" .
               "Acesse o recibo e detalhes:\n{{os_link}}\n\n" .
               "{{empresa_nome}}";
    }

    /**
     * Template WhatsApp - Recibo
     */
    private function getTemplateWhatsAppRecibo(): string
    {
        return "Olá {{cliente_nome}}!\n\n" .
               "🧾 Recibo da OS #{{os_numero}}\n\n" .
               "Serviço: {{servico_nome}}\n" .
               "Valor: R$ {{valor_total}}\n\n" .
               "Pagamento confirmado. Obrigado pela preferência!\n\n" .
               "{{empresa_nome}}\n" .
               "{{empresa_telefone}}";
    }

    /**
     * Lista de merge tags disponíveis
     */
    public function getMergeTags(): array
    {
        return [
            'contrato' => [
                ['tag' => '{{empresa_nome}}', 'descricao' => 'Nome da empresa'],
                ['tag' => '{{empresa_cnpj}}', 'descricao' => 'CNPJ da empresa'],
                ['tag' => '{{empresa_endereco}}', 'descricao' => 'Endereço da empresa'],
                ['tag' => '{{empresa_telefone}}', 'descricao' => 'Telefone da empresa'],
                ['tag' => '{{cliente_nome}}', 'descricao' => 'Nome do cliente'],
                ['tag' => '{{cliente_cpf_cnpj}}', 'descricao' => 'CPF/CNPJ do cliente'],
                ['tag' => '{{cliente_endereco}}', 'descricao' => 'Endereço do cliente'],
                ['tag' => '{{cliente_telefone}}', 'descricao' => 'Telefone do cliente'],
                ['tag' => '{{os_numero}}', 'descricao' => 'Número da OS'],
                ['tag' => '{{os_data}}', 'descricao' => 'Data da OS'],
                ['tag' => '{{os_servico}}', 'descricao' => 'Nome do serviço'],
                ['tag' => '{{os_descricao}}', 'descricao' => 'Descrição do serviço'],
                ['tag' => '{{os_valor}}', 'descricao' => 'Valor total formatado'],
                ['tag' => '{{os_garantia}}', 'descricao' => 'Dias de garantia'],
                ['tag' => '{{data_atual}}', 'descricao' => 'Data atual'],
                ['tag' => '{{hora_atual}}', 'descricao' => 'Hora atual']
            ],
            'whatsapp' => [
                ['tag' => '{{cliente_nome}}', 'descricao' => 'Nome do cliente'],
                ['tag' => '{{os_numero}}', 'descricao' => 'Número da OS'],
                ['tag' => '{{os_link}}', 'descricao' => 'Link público da OS'],
                ['tag' => '{{empresa_nome}}', 'descricao' => 'Nome da empresa'],
                ['tag' => '{{empresa_telefone}}', 'descricao' => 'Telefone da empresa'],
                ['tag' => '{{valor_total}}', 'descricao' => 'Valor total formatado'],
                ['tag' => '{{servico_nome}}', 'descricao' => 'Nome do serviço']
            ]
        ];
    }
}
