<?php
/**
 * CONFIGURAÇÃO DE PRODUÇÃO - SISTEMA DE ASSINATURAS
 * 
 * Domínio: proservice.pageup.net.br
 * Ambiente: Produção
 * Data: 14/02/2026
 * 
 * ⚠️  INSTRUÇÕES:
 * 1. Copiar este arquivo para /app/config/config-producao.php
 * 2. Substituir valores [AQUI] pelas credenciais reais
 * 3. Atualizar no config.php ou usar este arquivo separado
 */

// =====================================================
// DOMÍNIO E URL DE PRODUÇÃO
// =====================================================
define('DOMINIO_PRODUCAO', 'proservice.pageup.net.br');
define('URL_PRODUCAO', 'https://proservice.pageup.net.br');
define('WEBHOOK_URL_PRODUCAO', 'https://proservice.pageup.net.br/webhook/efipay');

// =====================================================
// CONFIGURAÇÕES EFIPAY - PRODUÇÃO
// =====================================================

/**
 * CLIENT_ID DE PRODUÇÃO
 * 
 * Obter em: https://dashboard.efipay.com.br > Configurações > API
 * Valor: Client_Id_[seus_digitos]
 * ⚠️  MUDE PARA SUA CHAVE DE PRODUÇÃO
 */
define('EFIPAY_CLIENT_ID', 'Client_Id_[COLOQUE_AQUI_SEU_CLIENT_ID_PRODUCAO]');

/**
 * CLIENT_SECRET DE PRODUÇÃO
 * 
 * Obter em: https://dashboard.efipay.com.br > Configurações > API
 * Valor: Client_Secret_[seus_digitos]
 * ⚠️  MUDE PARA SUA CHAVE DE PRODUÇÃO
 */
define('EFIPAY_CLIENT_SECRET', 'Client_Secret_[COLOQUE_AQUI_SEU_CLIENT_SECRET_PRODUCAO]');

/**
 * MODO SANDBOX
 * 
 * true = Homologação (testes)
 * false = Produção (real, cobra cartões)
 * 
 * ⚠️  DEVE SER FALSE EM PRODUÇÃO
 */
define('EFIPAY_SANDBOX', false); // ← PRODUÇÃO

/**
 * CAMINHO DO CERTIFICADO SSL (.P12)
 * 
 * Obtido em: https://dashboard.efipay.com.br > Config > Certificados
 * Formato: .P12 (PKCS12)
 * Local: /app/certs/
 * 
 * Exemplos de nomes:
 * - producao-123456.p12
 * - producao-2026-02-14.p12
 * 
 * ⚠️  DESCOMENTE E AJUSTE O NOME DO SEU CERTIFICADO
 */
define('EFIPAY_CERT_PATH', PROSERVICE_ROOT . '/app/certs/producao-123456.p12');
// ↑ MUDE "producao-123456.p12" PARA O NOME DO SEU CERTIFICADO

/**
 * SENHA DO CERTIFICADO
 * 
 * Definida ao gerar o certificado no painel EfiPay
 * Você escolhe a senha, anote-a em local seguro
 * 
 * ⚠️  DESCOMENTE E COLOQUE SUA SENHA
 */
define('EFIPAY_CERT_PASS', '');
// ↑ MUDE "[COLOQUE...]" PARA A SENHA QUE VOCÊ DEFINIU

// =====================================================
// CONFIGURAÇÕES DE WEBHOOK
// =====================================================

/**
 * URL DO WEBHOOK PARA EFIPAY
 * 
 * Configure no painel EfiPay em:
 * https://dashboard.efipay.com.br > Configurações > Webhooks
 * 
 * URL a configurar:
 */
// echo "URL a configurar no painel EfiPay: " . WEBHOOK_URL_PRODUCAO . "/webhook/efipay";

/**
 * EVENTOS DO WEBHOOK A ATIVAR
 * 
 * Selecione estes eventos no painel:
 * ✓ subscription.payment
 * ✓ subscription.suspended
 * ✓ subscription.canceled
 * ✓ subscription.reactivated
 */

// =====================================================
// VERIFICAÇÃO DE SEGURANÇA
// =====================================================

// Verificar se certificado existe
if (!file_exists(EFIPAY_CERT_PATH)) {
    error_log('⚠️  AVISO: Certificado não encontrado em ' . EFIPAY_CERT_PATH);
    error_log('   Verifique se o arquivo .p12 foi uploadado corretamente');
}

// Verificar permissões do certificado
$perms = fileperms(EFIPAY_CERT_PATH);
$perms_octal = substr(sprintf('%o', $perms), -3);
if ($perms_octal !== '600') {
    error_log('⚠️  AVISO: Permissões do certificado não são 600 (' . $perms_octal . ')');
    error_log('   Execute: chmod 600 ' . EFIPAY_CERT_PATH);
}

// Verificar credenciais não estão com placeholder
if (strpos(EFIPAY_CLIENT_ID, '[') !== false) {
    error_log('❌ ERRO: EFIPAY_CLIENT_ID ainda tem placeholder!');
    error_log('   Atualize com sua chave de produção');
}

if (strpos(EFIPAY_CLIENT_SECRET, '[') !== false) {
    error_log('❌ ERRO: EFIPAY_CLIENT_SECRET ainda tem placeholder!');
    error_log('   Atualize com sua chave de produção');
}

// =====================================================
// RESUMO DE CONFIGURAÇÃO
// =====================================================

/*
CHECKLIST DE CONFIGURAÇÃO:
═════════════════════════

[ ] 1. EFIPAY_CLIENT_ID
    De: Client_Id_88b1ea1a0cee068e4781794f94970dd9cd06ef11
    Para: Client_Id_[SUA_CHAVE_PRODUCAO]
    
[ ] 2. EFIPAY_CLIENT_SECRET  
    De: Client_Secret_4490ae783fee256da5c558aa6dc954605368ab17
    Para: Client_Secret_[SUA_CHAVE_PRODUCAO]
    
[ ] 3. EFIPAY_SANDBOX
    De: true
    Para: false ← PRODUÇÃO
    
[ ] 4. EFIPAY_CERT_PATH
    De: /certs/homologacao-573055-proService.p12
    Para: /certs/producao-XXXX.p12 (seu arquivo)
    
[ ] 5. EFIPAY_CERT_PASS
    Descomente e coloque sua senha
    
[ ] 6. DOMÍNIO
    Verificado: proservice.pageup.net.br
    
[ ] 7. WEBHOOK
    URL: https://proservice.pageup.net.br/webhook/efipay
    Eventos: 4 selecionados

[ ] 8. CERTIFICADO
    Upload: /app/certs/producao-XXXX.p12
    Permissões: chmod 600
    
[ ] 9. BANCO DE DADOS
    ALTER TABLE executado
    4 campos novos criados
    
[ ] 10. TESTES
    Checkout funcionando
    Webhook recebido
    Plano atualizado
    
PRONTO PARA PRODUÇÃO! 🚀
*/

?>
