<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Política de Privacidade - <?= APP_NAME ?></title>
    <link rel="icon" type="image/svg+xml" href="<?= asset('favicon.svg') ?>">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f8f9fa; padding: 40px 0; font-family: system-ui, sans-serif; }
        .legal-container { max-width: 800px; margin: 0 auto; background: white; padding: 40px; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.06); }
        .legal-header { border-bottom: 2px solid #e9ecef; padding-bottom: 20px; margin-bottom: 30px; }
        .legal-header h1 { font-size: 1.75rem; color: #1e40af; margin-bottom: 8px; }
        .legal-header p { color: #64748b; margin: 0; }
        .legal-body h2 { font-size: 1.2rem; margin-top: 24px; margin-bottom: 12px; color: #334155; }
        .legal-body p { color: #475569; line-height: 1.7; margin-bottom: 12px; }
        .back-link { display: inline-flex; align-items: center; gap: 8px; color: #1e40af; text-decoration: none; font-weight: 500; margin-bottom: 24px; }
        .back-link:hover { color: #059669; }
    </style>
</head>
<body>
    <div class="legal-container">
        <a href="<?= url('register') ?>" class="back-link"><i class="bi bi-arrow-left"></i> Voltar ao cadastro</a>
        
        <div class="legal-header">
            <h1>Política de Privacidade</h1>
            <p>Última atualização: <?= date('d/m/Y') ?></p>
        </div>
        
        <div class="legal-body">
            <p>Esta Política de Privacidade descreve como o <?= APP_NAME ?> coleta, usa, armazena e protege as informações dos usuários, em conformidade com a Lei Geral de Proteção de Dados (LGPD - Lei 13.709/2018).</p>
            
            <h2>1. Dados Coletados</h2>
            <p>Coletamos dados fornecidos diretamente por você no cadastro e uso da Plataforma: nome da empresa, CNPJ/CPF, e-mail, telefone, endereço, dados de clientes e técnicos cadastrados, descrições de serviços e demais informações inseridas no sistema.</p>
            
            <h2>2. Finalidade do Tratamento</h2>
            <p>Os dados são utilizados para: prestação do serviço de gestão de ordens de serviço; emissão de documentos e relatórios; comunicações sobre a conta e o serviço; cumprimento de obrigações legais e regulatórias; e melhoria da Plataforma.</p>
            
            <h2>3. Base Legal</h2>
            <p>O tratamento baseia-se no consentimento (ao aceitar estes termos e a política), na execução de contrato (prestação do serviço) e no legítimo interesse para operação e segurança da Plataforma.</p>
            
            <h2>4. Compartilhamento</h2>
            <p>Os dados não são vendidos. Podem ser compartilhados apenas com: processadores de pagamento (para cobrança); provedores de infraestrutura (hospedagem); e quando exigido por lei ou autoridade competente.</p>
            
            <h2>5. Armazenamento e Segurança</h2>
            <p>Os dados são armazenados em ambiente seguro, com controle de acesso e medidas técnicas adequadas para proteger contra acesso não autorizado, alteração ou divulgação indevida.</p>
            
            <h2>6. Seus Direitos (LGPD)</h2>
            <p>Você tem direito a: confirmar a existência de tratamento; acessar seus dados; corrigir dados incompletos ou desatualizados; solicitar anonimização, bloqueio ou eliminação de dados desnecessários; portabilidade dos dados; revogar o consentimento; e informações sobre compartilhamento. Para exercer esses direitos, entre em contato pelo suporte.</p>
            
            <h2>7. Cookies e Tecnologias Similares</h2>
            <p>Utilizamos cookies e sessões para autenticação e funcionamento da Plataforma. Não utilizamos cookies de rastreamento para publicidade de terceiros.</p>
            
            <h2>8. Retenção</h2>
            <p>Os dados são mantidos pelo tempo necessário para cumprir as finalidades descritas e obrigações legais. Após o cancelamento da conta, os dados podem ser mantidos em backup por período limitado, conforme legislação.</p>
            
            <h2>9. Alterações</h2>
            <p>Esta Política pode ser atualizada. Alterações relevantes serão comunicadas por e-mail ou aviso na Plataforma.</p>
            
            <h2>10. Contato</h2>
            <p>Para dúvidas sobre privacidade ou exercício de direitos, entre em contato através dos canais de suporte disponíveis na Plataforma.</p>
        </div>
    </div>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
</body>
</html>
