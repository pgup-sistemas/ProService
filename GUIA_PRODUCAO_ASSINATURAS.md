<!-- GUIA_PRODUCAO_ASSINATURAS.md -->

# 🚀 GUIA PASSO-A-PASSO: COLOCAR SISTEMA DE ASSINATURAS EM PRODUÇÃO

**Versão:** 1.0  
**Data:** 14/02/2026  
**Tempo estimado:** 1 hora  
**Risco:** Baixo

---

## ⚠️ PRÉ-REQUISITOS

Antes de começar, você deve ter:

- [ ] Conta EfiPay criada (https://www.efipay.com.br)
- [ ] Acesso ao painel EfiPay
- [ ] Credenciais de produção do EfiPay
- [ ] Certificado SSL (.p12) gerado pela EfiPay
- [ ] Senha do certificado (se necessário)
- [ ] Acesso ao servidor/banco de dados de produção
- [ ] Backup recente do banco de dados
- [ ] Domínio e HTTPS configurados
- [ ] Cliente FTP/SFTP ou acesso via terminal

---

## 📋 PASSO 1: PREPARAÇÃO NO PAINEL EFIPAY

### 1.1 Obter Credenciais de Produção

1. Acesse https://dashboard.efipay.com.br
2. Faça login com sua conta
3. Vá em **Configurações > API**
4. Anote:
   - **Client ID (Produção)**
   - **Client Secret (Produção)**

### 1.2 Gerar Certificado SSL

1. Em **Configurações > Segurança > Certificados**
2. Clique em **Gerar novo certificado**
3. Escolha o formato **.P12**
4. Defina uma senha segura (anote!)
5. Baixe o arquivo `.p12`

### 1.3 Configurar Webhooks

1. Em **Configurações > Webhooks**
2. Clique em **+ Novo Webhook**
3. Configure:
   - **URL:** `https://seu-dominio.com/webhook/efipay`
   - **Eventos:**
     - ✅ `subscription.payment`
     - ✅ `subscription.suspended`
     - ✅ `subscription.canceled`
     - ✅ `subscription.reactivated`
4. Salve e teste (EfiPay fará um POST de teste)

### 1.4 Testar Credenciais

No painel, clique em **Testar Conexão**:
- [ ] Conexão estabelecida ✓

---

## 🗄️ PASSO 2: MIGRAÇÃO DO BANCO DE DADOS

### 2.1 Executar Script de Migração

1. Abra seu cliente MySQL (phpMyAdmin, HeidiSQL, etc)
2. Conecte ao banco de dados de produção
3. Abra o arquivo `MIGRACAO_ASSINATURAS.sql`
4. Execute todo o conteúdo

```sql
-- Verificar se funcionou
SELECT * FROM empresas LIMIT 1 \G
```

Procure pelos campos novos:
- `assinatura_id`
- `assinatura_status`
- `cpf_responsavel`
- `responsavel_nome`

### 2.2 Validar Índices

```sql
SHOW INDEXES FROM empresas WHERE Column_name IN ('assinatura_id', 'assinatura_status');
```

Deve retornar 2 linhas (os índices criados).

---

## 🔐 PASSO 3: CONFIGURAÇÃO DE SEGURANÇA

### 3.1 Fazer Upload do Certificado

1. Via FTP/SFTP, navegue até: `/app/certs/` (crie a pasta se não existir)
2. Faça upload do arquivo `.p12` recebido do EfiPay
3. Defina permissões: `chmod 600 certificado.p12` (somente leitura)

### 3.2 Criar Arquivo .env de Produção

Na raiz do projeto, crie `.env.production`:

```
APP_ENV=production
APP_DEBUG=false
EFIPAY_SANDBOX=false
EFIPAY_CLIENT_ID=Client_Id_XXXXX_AQUI_XXXXX
EFIPAY_CLIENT_SECRET=Client_Secret_XXXXX_AQUI_XXXXX
EFIPAY_CERT_PASS=senha_do_certificado
```

### 3.3 Atualizar config.php

Edite `/app/config/config.php`:

```php
// ANTES (Homologação):
define('EFIPAY_CLIENT_ID', 'Client_Id_88b1ea1a0cee068e4781794f94970dd9cd06ef11');
define('EFIPAY_CLIENT_SECRET', 'Client_Secret_4490ae783fee256da5c558aa6dc954605368ab17');
define('EFIPAY_SANDBOX', true);
// define('EFIPAY_CERT_PATH', PROSERVICE_ROOT . '/certs/...');

// DEPOIS (Produção):
define('EFIPAY_CLIENT_ID', 'Client_Id_XXXXXXXXX_DE_PRODUCAO');
define('EFIPAY_CLIENT_SECRET', 'Client_Secret_XXXXXXXXX_DE_PRODUCAO');
define('EFIPAY_SANDBOX', false);
define('EFIPAY_CERT_PATH', PROSERVICE_ROOT . '/certs/producao-YOUR_ACCOUNT.p12');
define('EFIPAY_CERT_PASS', 'senha_do_certificado');
```

### 3.4 Verificar Permissões

```bash
# Via SSH
ls -la /var/www/seu-app/app/certs/
# Deve mostrar: -rw------- (600)

chmod 600 /var/www/seu-app/app/certs/*.p12
```

---

## ✅ PASSO 4: TESTES PRÉ-PRODUÇÃO

### 4.1 Teste de Conectividade

1. Acesse: `http://seu-dominio/dashboard?test-efipay=1`
2. Procure no `error_log` por: `"Tentando autenticar com clientId..."`
3. Se der erro, verifique:
   - [ ] Credenciais estão corretas?
   - [ ] URL da API é de produção?
   - [ ] Certificado existe e tem permissão?

### 4.2 Teste de Fluxo Completo

**Em sua conta de teste (não use conta real!):**

1. **Passo 1:** Login no dashboard
2. **Passo 2:** Ir para `/assinaturas`
3. **Passo 3:** Clicar em "Assinar Agora" (Plano Profissional)
4. **Passo 4:** Ser redirecionado para EfiPay
5. **Passo 5:** Preencher dados de teste (EfiPay fornece)
6. **Passo 6:** Confirmar pagamento
7. **Passo 7:** Retornar para `/assinaturas/gerenciar`

**Verificações:**
- [ ] Plano mudou para "Profissional"?
- [ ] `assinatura_status` está 'pending' ou 'active'?
- [ ] Limites atualizaram?
- [ ] Recebeu e-mail de confirmação?

### 4.3 Teste de Webhook

1. No painel EfiPay, vá em **Configurações > Webhooks**
2. Ao lado do webhook criado, clique em **Enviar teste**
3. No servidor, verifique o log:

```bash
tail -50 storage/logs/error.log | grep "EfiPay Webhook"
```

Procure por:
```
[timestamp] EfiPay Webhook: {"event":"subscription.payment", ...}
```

### 4.4 Teste de Cancelamento

1. Em `/assinaturas/gerenciar`, clique em **Cancelar assinatura**
2. Confirme o cancelamento
3. Verifique:
   - [ ] `assinatura_status` mudou para 'canceled'?
   - [ ] Plano voltou para 'trial'?
   - [ ] Limite_os_mes voltou ao do trial?

---

## 📊 PASSO 5: VALIDAÇÃO FINAL

Execute estas queries para garantir que tudo funcionou:

```sql
-- 1. Contar empresas por status de assinatura
SELECT assinatura_status, COUNT(*) as total 
FROM empresas 
GROUP BY assinatura_status;

-- 2. Verificar se há assinaturas ativas
SELECT id, nome_fantasia, plano, assinatura_status, data_inicio_plano
FROM empresas 
WHERE assinatura_status IN ('active', 'pending')
LIMIT 10;

-- 3. Verificar histórico de pagamentos
SELECT * FROM pagamentos_rastreamento 
ORDER BY created_at DESC 
LIMIT 5;

-- 4. Verificar integridade de limites
SELECT id, nome_fantasia, plano, limite_os_mes, limite_tecnicos, limite_armazenamento_mb
FROM empresas 
WHERE assinatura_status = 'active'
LIMIT 5;
```

---

## 🚨 PASSO 6: MONITORAMENTO EM PRODUÇÃO

### 6.1 Ativar Logging Detalhado

Em `/app/config/config.php`, se houver opção de debug:

```php
// Manter em false, mas log deve estar ativo
error_reporting(E_ALL);
ini_set('log_errors', '1');
ini_set('error_log', '/var/log/php/proservice.log');
```

### 6.2 Monitorar Webhooks

Crie um script para monitorar:

```php
// webhook-monitor.php (privado, só você acessa)
<?php
// Verificar últimas 10 notificações
$db = new PDO('mysql:host=...', ...);
$stmt = $db->query("
    SELECT id, webhook_event, assinatura_id, status_novo, created_at
    FROM pagamentos_rastreamento 
    ORDER BY created_at DESC 
    LIMIT 10
");
?>
<table border="1">
  <tr><th>ID</th><th>Evento</th><th>Assinatura</th><th>Novo Status</th><th>Data</th></tr>
  <?php foreach($stmt as $row): ?>
    <tr>
      <td><?= $row['id'] ?></td>
      <td><?= $row['webhook_event'] ?></td>
      <td><?= $row['assinatura_id'] ?></td>
      <td><?= $row['status_novo'] ?></td>
      <td><?= $row['created_at'] ?></td>
    </tr>
  <?php endforeach; ?>
</table>
```

### 6.3 Alertas Email

Configure um cron job para verificar status a cada hora:

```bash
0 * * * * /usr/bin/php /var/www/seu-app/monitorar-assinaturas.php
```

Script:
```php
<?php
// monitorar-assinaturas.php
// Verificar se há webhooks não processados
$db->query("SELECT COUNT(*) as pendentes FROM pagamentos_rastreamento WHERE processado_em IS NULL");
// Se > 0, enviar e-mail de alerta
?>
```

---

## 🔄 PASSO 7: FALLBACK / ROLLBACK (Se der erro)

### Se a migração falhar:

```bash
# 1. Reverter banco
mysql -u user -p database < backup_antes_migracao.sql

# 2. Voltar config.php
git checkout app/config/config.php

# 3. Remover certificado
rm /app/certs/*.p12

# 4. Voltar para sandbox
define('EFIPAY_SANDBOX', true);
```

### Se o webhook não funcionar:

1. Verificar se a URL é HTTPS (obrigatório)
2. Verificar firewall (bloqueia POST?)
3. No painel EfiPay, clicar em **Ver tentativas** do webhook
4. Ver exatamente qual foi o erro
5. Testar webhook manualmente:

```bash
curl -X POST https://seu-dominio.com/webhook/efipay \
  -H "Content-Type: application/json" \
  -d '{
    "event": "subscription.payment",
    "data": {"subscription_id": 123, "status": "active"}
  }'
```

---

## 📋 CHECKLIST FINAL

Antes de considerar pronto para produção:

- [ ] Banco de dados migrado com sucesso
- [ ] Certificado SSL configurado
- [ ] Credenciais de produção atualizadas
- [ ] Webhooks configurados no painel EfiPay
- [ ] Teste de checkout completo realizado
- [ ] Teste de webhook realizado
- [ ] Teste de cancelamento realizado
- [ ] Limite de OS está funcionando
- [ ] Logs estão sendo criados
- [ ] Backup do banco foi feito
- [ ] Equipe notificada do novo sistema
- [ ] Documentação da API passada para dev team

---

## ❓ TROUBLESHOOTING

### "Erro ao conectar com EfiPay"
- [ ] Verificar Client ID e Secret
- [ ] Verificar se está em produção (SANDBOX=false)
- [ ] Testar conectividade: `curl https://cobrancas.api.efipay.com.br/v1/authorize`

### "Webhook não está sendo recebido"
- [ ] URL é HTTPS?
- [ ] Porta 443 está aberta?
- [ ] Firewall bloqueia POST?
- [ ] Testar manualmente com curl (veja acima)

### "Plano não atualiza após pagamento"
- [ ] Webhook signature validado?
- [ ] Tabela `pagamentos_rastreamento` está criada?
- [ ] Erro no error_log quando webhook chega?

### "Certificado SSL invalido"
- [ ] Arquivo .p12 está correto?
- [ ] Permissões corretas (chmod 600)?
- [ ] Senha está certa?
- [ ] Re-gerar certificado no painel EfiPay

---

## 📞 SUPORTE

Se encontrar problemas:

1. **Verificar logs:**
   ```bash
   tail -100 /var/log/php/proservice.log | grep -i efipay
   ```

2. **Contatar suporte EfiPay:**
   - https://www.efipay.com.br/suporte
   - Chat no dashboard
   - Email: suporte@efipay.com.br

3. **Verificar SPEC:**
   - Abrir `SPEC_COMPLETO_OS_SAAS.md`
   - Comparar com implementação em `AUDIT_ASSINATURAS.md`

---

**✅ Parabéns! Sistema pronto para produção!**

Data de conclusão: ___/___/_____  
Responsável: ____________________  
Assinatura: ____________________

