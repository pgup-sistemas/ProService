<?php
/**
 * proService - Modelo de configuração (exemplo)
 * NÃO coloque segredos reais aqui — copie para `app/config/config.php` e preencha.
 */

if (defined('CONFIG_LOADED')) {
    return [];
}

define('CONFIG_LOADED', true);

define('ENVIRONMENT', 'development'); // development | production

// Banco de dados
define('DB_HOST', 'your-db-host');
define('DB_NAME', 'your-db-name');
define('DB_USER', 'your-db-user');
define('DB_PASS', 'your-db-password');
define('DB_CHARSET', 'utf8mb4');

// Aplicação
define('APP_NAME', 'ProService');
define('APP_VERSION', '1.0.0');
define('APP_URL', 'https://example.com');
define('APP_EMAIL', 'you@example.com');

// EfiPay (pagamentos)
define('EFIPAY_CLIENT_ID', 'your-efipay-client-id');
define('EFIPAY_CLIENT_SECRET', 'your-efipay-client-secret');
define('EFIPAY_SANDBOX', true); // true = homologação, false = produção

// Outros caminhos/valores não sensíveis podem ficar aqui como padrão
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
