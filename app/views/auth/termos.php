<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Termos de Uso - <?= APP_NAME ?></title>
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
            <h1>Termos de Uso</h1>
            <p>Última atualização: <?= date('d/m/Y') ?></p>
        </div>
        
        <div class="legal-body">
            <p>Ao utilizar o <?= APP_NAME ?> (“Plataforma”), você concorda com os presentes Termos de Uso. Leia-os com atenção antes de criar sua conta.</p>
            
            <h2>1. Aceitação</h2>
            <p>Ao cadastrar-se e utilizar a Plataforma, você declara ter lido, compreendido e concordado integralmente com estes Termos e com a Política de Privacidade.</p>
            
            <h2>2. Descrição do Serviço</h2>
            <p>O <?= APP_NAME ?> é uma plataforma SaaS para gestão de ordens de serviço, permitindo o cadastro de clientes, técnicos, serviços, emissão de recibos e demais funcionalidades disponibilizadas conforme o plano contratado.</p>
            
            <h2>3. Cadastro e Conta</h2>
            <p>O usuário deve informar dados verdadeiros e manter seu cadastro atualizado. A empresa é responsável por todas as atividades realizadas em sua conta e deve manter a confidencialidade das credenciais de acesso.</p>
            
            <h2>4. Uso Adequado</h2>
            <p>É vedado o uso da Plataforma para fins ilegais, abusivos ou que infrinjam direitos de terceiros. O provedor reserva-se o direito de suspender ou encerrar contas que violem estes Termos.</p>
            
            <h2>5. Planos e Pagamento</h2>
            <p>Os planos, valores e limites estão descritos na página de assinaturas. O não pagamento das mensalidades poderá resultar na suspensão ou cancelamento do acesso.</p>
            
            <h2>6. Propriedade Intelectual</h2>
            <p>O software, marca e conteúdos da Plataforma são de propriedade do provedor. O usuário não adquire direitos sobre a plataforma, exceto o de uso nos termos da licença concedida.</p>
            
            <h2>7. Limitação de Responsabilidade</h2>
            <p>A Plataforma é fornecida "como está". O provedor não se responsabiliza por decisões de negócio tomadas com base nos dados ou relatórios gerados, nem por interrupções ocasionais do serviço dentro do previsto em contrato.</p>
            
            <h2>8. Alterações</h2>
            <p>Estes Termos podem ser alterados. Alterações significativas serão comunicadas por e-mail ou aviso na Plataforma. O uso continuado após a alteração constitui aceite das novas condições.</p>
            
            <h2>9. Contato</h2>
            <p>Dúvidas sobre estes Termos devem ser encaminhadas ao suporte através dos canais disponíveis na Plataforma.</p>
        </div>
    </div>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
</body>
</html>
