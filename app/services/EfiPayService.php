<?php
/**
 * Serviço de integração com EfiPay (antiga Gerencianet)
 * API de Cobranças - Assinaturas/Cartão de Crédito Recorrente
 * Ambiente: Homologação
 */
namespace App\Services;

class EfiPayService
{
    private string $clientId;
    private string $clientSecret;
    private bool $sandbox;
    private ?string $accessToken = null;

    // URLs da API
    private const API_BASE_SANDBOX = 'https://cobrancas-h.api.efipay.com.br';
    private const API_BASE_PRODUCTION = 'https://cobrancas.api.efipay.com.br';

    public function __construct()
    {
        $config = require __DIR__ . '/../config/config.php';
        
        $this->clientId = $config['efipay']['client_id'] ?? '';
        $this->clientSecret = $config['efipay']['client_secret'] ?? '';
        $this->sandbox = $config['efipay']['sandbox'] ?? true;
    }

    /**
     * Log customizado para depurar EfiPay
     */
    private function log(string $msg): void
    {
        $logFile = __DIR__ . '/../../logs/efipay.log';
        $dir = dirname($logFile);
        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }
        file_put_contents($logFile, '[' . date('Y-m-d H:i:s') . '] ' . $msg . PHP_EOL, FILE_APPEND);
    }

    /**
     * Retorna a URL base da API
     */
    private function getApiBase(): string
    {
        return $this->sandbox ? self::API_BASE_SANDBOX : self::API_BASE_PRODUCTION;
    }

    /**
     * Obtém token de acesso OAuth2
     */
    public function getAccessToken(): ?string
    {
        if ($this->accessToken) {
            return $this->accessToken;
        }

        $credentials = base64_encode($this->clientId . ':' . $this->clientSecret);
        
        $this->log('Tentando autenticar com clientId=' . substr($this->clientId, 0, 8) . '...');
        $this->log('Endpoint=' . $this->getApiBase() . '/v1/authorize');

        $ch = curl_init($this->getApiBase() . '/v1/authorize');
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true, // Seguir redirecionamentos (301/302)
            CURLOPT_SSL_VERIFYPEER => false, // Apenas para homologação
            CURLOPT_HTTPHEADER => [
                'Authorization: Basic ' . $credentials,
                'Content-Type: application/json'
            ],
            CURLOPT_POSTFIELDS => '{"grant_type":"client_credentials"}',
        ]);

        // Adicionar certificado SSL se estiver em produção e os arquivos existirem
        if (!$this->sandbox && defined('EFIPAY_CERT_PATH') && file_exists(EFIPAY_CERT_PATH)) {
            curl_setopt($ch, CURLOPT_SSLCERT, EFIPAY_CERT_PATH);
            // Se for arquivo .p12, usar CURLOPT_SSLCERTPASSWD
            if (pathinfo(EFIPAY_CERT_PATH, PATHINFO_EXTENSION) === 'p12' && defined('EFIPAY_CERT_PASS')) {
                curl_setopt($ch, CURLOPT_SSLCERTPASSWD, EFIPAY_CERT_PASS);
                curl_setopt($ch, CURLOPT_SSLCERTTYPE, 'P12');
            }
        }
        
        // Adicionar certificado SSL se estiver em homologação e os arquivos existirem
        if ($this->sandbox && defined('EFIPAY_CERT_PATH') && file_exists(EFIPAY_CERT_PATH)) {
            curl_setopt($ch, CURLOPT_SSLCERT, EFIPAY_CERT_PATH);
            // Se for arquivo .p12, usar CURLOPT_SSLCERTPASSWD
            if (pathinfo(EFIPAY_CERT_PATH, PATHINFO_EXTENSION) === 'p12' && defined('EFIPAY_CERT_PASS')) {
                curl_setopt($ch, CURLOPT_SSLCERTPASSWD, EFIPAY_CERT_PASS);
                curl_setopt($ch, CURLOPT_SSLCERTTYPE, 'P12');
            }
        }

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        $this->log('HTTP=' . $httpCode . ' cURL_error=' . ($curlError ?: 'none'));
        $this->log('response=' . $response);

        if ($httpCode === 200 && $response) {
            $data = json_decode($response, true);
            $this->accessToken = $data['access_token'] ?? null;
            return $this->accessToken;
        }

        $this->log('Falha ao obter token. Verifique credenciais, ambiente (sandbox) ou conectividade.');
        return null;
    }

    /**
     * Faz requisição para a API
     */
    private function request(string $method, string $endpoint, array $data = []): array
    {
        $token = $this->getAccessToken();
        if (!$token) {
            return ['error' => 'Falha na autenticação'];
        }

        $url = $this->getApiBase() . $endpoint;
        
        $this->log('Request: ' . $method . ' ' . $url);
        if (!empty($data)) {
            $this->log('Data: ' . json_encode($data));
        }
        
        $ch = curl_init($url);
        $headers = [
            'Authorization: Bearer ' . $token,
            'Content-Type: application/json'
        ];

        $options = [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_SSL_VERIFYPEER => false, // Apenas para homologação
        ];

        // Adicionar certificado SSL se estiver em produção e os arquivos existirem
        if (!$this->sandbox && defined('EFIPAY_CERT_PATH') && file_exists(EFIPAY_CERT_PATH)) {
            $options[CURLOPT_SSLCERT] = EFIPAY_CERT_PATH;
            // Se for arquivo .p12, usar CURLOPT_SSLCERTPASSWD
            if (pathinfo(EFIPAY_CERT_PATH, PATHINFO_EXTENSION) === 'p12' && defined('EFIPAY_CERT_PASS')) {
                $options[CURLOPT_SSLCERTPASSWD] = EFIPAY_CERT_PASS;
                $options[CURLOPT_SSLCERTTYPE] = 'P12';
            }
        }
        
        // Adicionar certificado SSL se estiver em homologação e os arquivos existirem
        if ($this->sandbox && defined('EFIPAY_CERT_PATH') && file_exists(EFIPAY_CERT_PATH)) {
            $options[CURLOPT_SSLCERT] = EFIPAY_CERT_PATH;
            // Se for arquivo .p12, usar CURLOPT_SSLCERTPASSWD
            if (pathinfo(EFIPAY_CERT_PATH, PATHINFO_EXTENSION) === 'p12' && defined('EFIPAY_CERT_PASS')) {
                $options[CURLOPT_SSLCERTPASSWD] = EFIPAY_CERT_PASS;
                $options[CURLOPT_SSLCERTTYPE] = 'P12';
            }
        }

        if ($method === 'POST') {
            $options[CURLOPT_POST] = true;
            $options[CURLOPT_POSTFIELDS] = json_encode($data);
        } elseif ($method === 'PUT') {
            $options[CURLOPT_CUSTOMREQUEST] = 'PUT';
            $options[CURLOPT_POSTFIELDS] = json_encode($data);
        } elseif ($method === 'DELETE') {
            $options[CURLOPT_CUSTOMREQUEST] = 'DELETE';
        }

        curl_setopt_array($ch, $options);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        $this->log('Response HTTP=' . $httpCode . ' cURL_error=' . ($error ?: 'none'));
        $this->log('Response=' . $response);

        if ($error) {
            return ['error' => $error, 'http_code' => $httpCode];
        }

        $result = [];
        if (is_string($response) && $response !== '') {
            $decoded = json_decode($response, true);
            if (is_array($decoded)) {
                $result = $decoded;
            } else {
                $result = ['raw_response' => $response];
            }
        }

        $result['http_code'] = $httpCode;

        if ($httpCode >= 400) {
            $errorDescription = $result['error_description']
                ?? $result['error']
                ?? $result['message']
                ?? ($result['raw_response'] ?? null);

            if (!$errorDescription) {
                $errorDescription = 'Erro na API EfiPay';
            }

            $result['error_description'] = $errorDescription;
            error_log('EfiPay API Error (' . $httpCode . '): ' . (is_string($response) ? $response : ''));
        }

        return $result;
    }

    /**
     * Cria um plano de assinatura
     */
    public function criarPlano(string $nome, int $intervalo, array $metadados = []): array
    {
        return $this->request('POST', '/v1/plan', [
            'name' => $nome,
            'interval' => $intervalo,
            'repeats' => null // null = repetições infinitas
        ]);
    }

    /**
     * Lista todos os planos
     */
    public function listarPlanos(): array
    {
        return $this->request('GET', '/v1/plan', []);
    }

    /**
     * Cria uma assinatura (vincula cliente a um plano)
     */
    public function criarAssinatura(
        int $planoId,
        array $cliente,
        array $cartao,
        string $dataInicio,
        array $metadados = [],
        ?string $cupom = null
    ): array {
        $body = [
            'plan_id' => $planoId,
            'customer' => $cliente,
            'payment' => [
                'credit_card' => [
                    'billing_address' => $cartao['billing_address'] ?? [],
                    'payment_token' => $cartao['payment_token'] ?? null,
                    'installments' => $cartao['installments'] ?? 1,
                    'card_brand' => $cartao['card_brand'] ?? null
                ]
            ],
            'start_at' => $dataInicio,
            'metadata' => $metadados
        ];

        if ($cupom) {
            $body['coupon_code'] = $cupom;
        }

        return $this->request('POST', '/subscriptions', $body);
    }

    /**
     * Define o token de pagamento para uma assinatura
     */
    public function definirTokenPagamento(
        int $assinaturaId,
        string $paymentToken,
        array $cartao,
        ?string $parcelas = null
    ): array {
        $body = [
            'payment_token' => $paymentToken,
            'payment' => [
                'credit_card' => [
                    'billing_address' => $cartao['billing_address'] ?? [],
                    'card_brand' => $cartao['card_brand'] ?? null
                ]
            ]
        ];

        if ($parcelas) {
            $body['payment']['credit_card']['installments'] = $parcelas;
        }

        return $this->request('POST', "/subscriptions/{$assinaturaId}/pay", $body);
    }

    /**
     * Cancela uma assinatura
     */
    public function cancelarAssinatura(int $assinaturaId): array
    {
        return $this->request('PUT', "/subscriptions/{$assinaturaId}/cancel", []);
    }

    /**
     * Reativa uma assinatura suspensa
     */
    public function reativarAssinatura(int $assinaturaId): array
    {
        return $this->request('PUT', "/subscriptions/{$assinaturaId}/reactivate", []);
    }

    /**
     * Lista todas as assinaturas
     */
    public function listarAssinaturas(?string $status = null, int $pagina = 1, int $itensPorPagina = 20): array
    {
        $query = "?page={$pagina}&per_page={$itensPorPagina}";
        if ($status) {
            $query .= "&status={$status}";
        }
        return $this->request('GET', '/subscriptions' . $query, []);
    }

    /**
     * Busca detalhes de uma assinatura específica
     */
    public function buscarAssinatura(int $assinaturaId): array
    {
        return $this->request('GET', "/subscriptions/{$assinaturaId}", []);
    }

    /**
     * Cria pagamento avulso com cartão de crédito
     */
    public function criarCobrancaCartao(
        array $cliente,
        array $itens,
        array $cartao,
        array $configuracoes = []
    ): array {
        $body = [
            'items' => $itens,
            'customer' => $cliente,
            'payment' => [
                'credit_card' => [
                    'installments' => $cartao['installments'] ?? 1,
                    'payment_token' => $cartao['payment_token'] ?? null,
                    'billing_address' => $cartao['billing_address'] ?? [],
                    'card_brand' => $cartao['card_brand'] ?? null
                ]
            ]
        ];

        if (!empty($configuracoes)) {
            $body['settings'] = $configuracoes;
        }

        return $this->request('POST', '/charges', $body);
    }

    /**
     * Cria cobrança para pagamento via Pix
     */
    public function criarCobrancaPix(
        array $cliente,
        array $itens,
        int $expiraEm = 3600
    ): array {
        return $this->request('POST', '/charges', [
            'items' => $itens,
            'customer' => $cliente,
            'pix' => [
                'expires_in' => $expiraEm
            ]
        ]);
    }

    /**
     * Gera QR Code para pagamento Pix
     */
    public function gerarQrCodePix(int $chargeId): array
    {
        return $this->request('GET', "/charges/{$chargeId}/pix", []);
    }

    /**
     * Estorna uma cobrança
     */
    public function estornarCobranca(int $chargeId): array
    {
        return $this->request('PUT', "/charges/{$chargeId}/refund", []);
    }

    /**
     * Cancela uma cobrança pendente
     */
    public function cancelarCobranca(int $chargeId): array
    {
        return $this->request('PUT', "/charges/{$chargeId}/cancel", []);
    }

    /**
     * Lista histórico de pagamentos de uma assinatura
     */
    public function listarPagamentosAssinatura(int $assinaturaId): array
    {
        return $this->request('GET', "/subscriptions/{$assinaturaId}/payments", []);
    }

    /**
     * Atualiza informações da assinatura
     */
    public function atualizarAssinatura(
        int $assinaturaId,
        ?string $dataInicio = null,
        array $metadados = [],
        ?int $planoId = null,
        ?string $cupom = null,
        ?array $desconto = null
    ): array {
        $body = ['metadata' => $metadados];

        if ($dataInicio) {
            $body['start_at'] = $dataInicio;
        }
        if ($planoId) {
            $body['plan_id'] = $planoId;
        }
        if ($cupom) {
            $body['coupon_code'] = $cupom;
        }
        if ($desconto) {
            $body['discount'] = $desconto;
        }

        return $this->request('PUT', "/subscriptions/{$assinaturaId}", $body);
    }

    /**
     * Cria um plano de assinatura (se não existir) e gera link de pagamento
     * Endpoint correto: /v1/plan/:id/subscription/one-step/link
     */
    public function criarLinkPagamento(
        string $descricao,
        int $valorCentavos,
        array $cliente,
        string $redirectUrl,
        ?string $customId = null
    ): array {
        // 1. Primeiro, criar ou buscar o plano
        $planResult = $this->criarOuBuscarPlano($descricao, $valorCentavos);
        if (!empty($planResult['error'])) {
            return $planResult;
        }

        $planId = $planResult['data']['plan_id'] ?? null;
        if (!$planId) {
            return ['error' => 'Não foi possível obter plan_id'];
        }

        // 2. Gerar link de pagamento associado ao plano
        $body = [
            'items' => [
                [
                    'name' => $descricao,
                    'value' => $valorCentavos,
                    'amount' => 1
                ]
            ],
            'metadata' => [
                'custom_id' => $customId ?? uniqid('PS_', true)
            ],
            'settings' => [
                'payment_method' => 'all',
                'expire_at' => date('Y-m-d', strtotime('+7 days')),
                'request_delivery_address' => false
            ]
        ];

        return $this->request('POST', "/v1/plan/{$planId}/subscription/one-step/link", $body);
    }

    /**
     * Cria ou busca um plano pelo nome (para evitar duplicados)
     */
    private function criarOuBuscarPlano(string $nome, int $valorCentavos): array
    {
        // Tenta buscar planos existentes
        $listResult = $this->listarPlanos();
        if (!empty($listResult['data'])) {
            foreach ($listResult['data'] as $plano) {
                if (trim($plano['name']) === trim($nome)) {
                    return ['data' => ['plan_id' => $plano['plan_id']]];
                }
            }
        }

        // Se não encontrou, cria um novo
        return $this->criarPlano($nome, 1);
    }

    /**
     * Verifica status de um pagamento
     */
    public function verificarStatusPagamento(int $chargeId): array
    {
        return $this->request('GET', "/v1/charges/{$chargeId}", []);
    }

    /**
     * Retorna saldo da conta EfiPay
     */
    public function consultarSaldo(): array
    {
        return $this->request('GET', '/v1/balance', []);
    }
}
