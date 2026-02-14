<?php
/**
 * proService - Configuração Principal
 * Arquivo: /app/config/config.php
 */

// Prevenir múltiplos carregamentos
if (defined('CONFIG_LOADED')) {
    return [
        'database' => [
            'host' => DB_HOST,
            'name' => DB_NAME,
            'user' => DB_USER,
            'pass' => DB_PASS,
            'charset' => DB_CHARSET
        ],
        'app' => [
            'name' => APP_NAME,
            'version' => APP_VERSION,
            'url' => APP_URL,
            'email' => APP_EMAIL
        ],
        'efipay' => [
            'client_id' => EFIPAY_CLIENT_ID,
            'client_secret' => EFIPAY_CLIENT_SECRET,
            'sandbox' => EFIPAY_SANDBOX
        ]
    ];
}
define('CONFIG_LOADED', true);

// Prevenir acesso direto
if (!defined('PROSERVICE_ROOT')) {
    define('PROSERVICE_ROOT', dirname(__DIR__, 2));
}

// Ambiente
define('ENVIRONMENT', 'development'); // development | production

// Configurações de erro
if (ENVIRONMENT === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// Configurações do banco de dados
define('DB_HOST', 'localhost');
define('DB_NAME', 'proservice');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// Configurações da aplicação
define('APP_NAME', 'proService');
define('APP_VERSION', '1.0.0');
define('APP_URL', 'http://localhost/proService');
define('APP_EMAIL', 'contato@proservice.com.br');

// Configurações de sessão
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 0); // Alterar para 1 em HTTPS

// Configurações de timezone
date_default_timezone_set('America/Sao_Paulo');
setlocale(LC_ALL, 'pt_BR', 'pt_BR.utf-8', 'portuguese');

// Configurações de upload
define('UPLOAD_MAX_SIZE', 5 * 1024 * 1024); // 5MB
define('UPLOAD_ALLOWED_TYPES', ['image/jpeg', 'image/png', 'image/gif', 'image/webp']);
define('UPLOAD_PATH', PROSERVICE_ROOT . '/public/uploads/');

// Configurações de paginação
define('ITEMS_PER_PAGE', 20);

// Duração do trial em dias
define('TRIAL_DAYS', 15);

// Limites dos planos - Starter, Pro, Business
define('PLANO_STARTER_OS', 50);
define('PLANO_STARTER_TECNICOS', 1);
define('PLANO_STARTER_ARMAZENAMENTO', 100); // MB

// Pro - ilimitado OS, 5 técnicos, 1 GB
define('PLANO_PRO_OS', -1); // Ilimitado
define('PLANO_PRO_TECNICOS', 5);
define('PLANO_PRO_ARMAZENAMENTO', 1024); // 1 GB

// Business - tudo ilimitado, 5 GB
define('PLANO_BUSINESS_OS', -1); // Ilimitado
define('PLANO_BUSINESS_TECNICOS', -1); // Ilimitado
define('PLANO_BUSINESS_ARMAZENAMENTO', 5120); // 5 GB

// Preços dos planos (mensal)
define('PLANO_STARTER_PRECO', 29.00);
define('PLANO_PRO_PRECO', 59.00);
define('PLANO_BUSINESS_PRECO', 99.00);

// CSRF Token validity (em segundos)
define('CSRF_TOKEN_LIFETIME', 3600); // 1 hora

// =====================================================
// Configurações EfiPay (RFI Bank) - Ambiente de Homologação
// =====================================================
define('EFIPAY_CLIENT_ID', 'Client_Id_88b1ea1a0cee068e4781794f94970dd9cd06ef11');
define('EFIPAY_CLIENT_SECRET', 'Client_Secret_4490ae783fee256da5c558aa6dc954605368ab17');
define('EFIPAY_SANDBOX', true); // Homologação = true, Produção = false

// Certificado SSL para homologação (sandbox) - desativado temporariamente para teste
// define('EFIPAY_CERT_PATH', PROSERVICE_ROOT . '/homologacao-573055-proService.p12');
// define('EFIPAY_CERT_PASS', ''); // Senha do certificado, se necessário

// Retornar configurações como array (para uso em classes)
return [
    'database' => [
        'host' => DB_HOST,
        'name' => DB_NAME,
        'user' => DB_USER,
        'pass' => DB_PASS,
        'charset' => DB_CHARSET
    ],
    'app' => [
        'name' => APP_NAME,
        'version' => APP_VERSION,
        'url' => APP_URL,
        'email' => APP_EMAIL
    ],
    'efipay' => [
        'client_id' => EFIPAY_CLIENT_ID,
        'client_secret' => EFIPAY_CLIENT_SECRET,
        'sandbox' => EFIPAY_SANDBOX
    ]
];
