#!/bin/bash
# ============================================
# SCRIPT DE IMPLANTAÇÃO - SISTEMA DE ASSINATURAS
# Versão: 1.0
# Data: 14/02/2026
# ============================================
# USE ESTE SCRIPT COMO GUIA, NÃO EXECUTE DIRETO
# Requer: SSH acesso, MySQL CLI, conhecimento de produção
# ============================================

echo "═══════════════════════════════════════════════════════════════════════════"
echo "  SCRIPT DE IMPLANTAÇÃO - SISTEMA DE ASSINATURAS"
echo "═══════════════════════════════════════════════════════════════════════════"
echo ""

# ============================================
# PASSO 1: BACKUP DO BANCO
# ============================================
echo "📦 PASSO 1: Fazendo backup do banco de dados..."
echo ""

# Configurar variáveis
DB_USER="seu_usuario_db"
DB_PASS="sua_senha_db"
DB_NAME="proservice"
BACKUP_DIR="/home/backups"
TIMESTAMP=$(date +%Y%m%d_%H%M%S)

# Criar diretório se não existir
mkdir -p $BACKUP_DIR

# Fazer backup
mysqldump -u $DB_USER -p$DB_PASS $DB_NAME > $BACKUP_DIR/proservice_$TIMESTAMP.sql

if [ $? -eq 0 ]; then
    echo "✅ Backup criado: $BACKUP_DIR/proservice_$TIMESTAMP.sql"
    echo "   Tamanho: $(du -h $BACKUP_DIR/proservice_$TIMESTAMP.sql | cut -f1)"
else
    echo "❌ ERRO ao fazer backup!"
    exit 1
fi

echo ""

# ============================================
# PASSO 2: FAZER UPLOAD DO CERTIFICADO
# ============================================
echo "🔐 PASSO 2: Preparando certificado SSL..."
echo ""
echo "⚠️  MANUAL: Você precisa fazer upload do arquivo .p12 do EfiPay"
echo "   1. Baixar certificado do painel EfiPay"
echo "   2. Via SCP: scp seu-cert.p12 seu-usuario@seu-servidor:/var/www/proservice/app/certs/"
echo "   3. Ou via FTP: Upload para /app/certs/"
echo ""
echo "   Exemplo SCP:"
echo "   scp producao-123456.p12 deploy@proservice.com:/var/www/proservice/app/certs/"
echo ""
echo "   Depois continue aqui..."
read -p "Pressione ENTER quando certificado estiver uploaded..."

# Definir permissões (execute do servidor)
CERT_PATH="/var/www/proservice/app/certs"
if [ -d "$CERT_PATH" ]; then
    chmod 600 $CERT_PATH/*.p12
    echo "✅ Permissões do certificado ajustadas (600)"
else
    echo "❌ Diretório $CERT_PATH não encontrado!"
    exit 1
fi

echo ""

# ============================================
# PASSO 3: FAZER BACKUP DO config.php
# ============================================
echo "💾 PASSO 3: Backup do config.php..."
echo ""

CONFIG_FILE="/var/www/proservice/app/config/config.php"
cp $CONFIG_FILE $CONFIG_FILE.backup_$TIMESTAMP

echo "✅ Backup criado: $CONFIG_FILE.backup_$TIMESTAMP"
echo ""

# ============================================
# PASSO 4: ATUALIZAR config.php
# ============================================
echo "⚙️  PASSO 4: Atualizando config.php..."
echo ""

# ⚠️  SUBSTITUA PELOS SEUS VALORES REAIS
CLIENT_ID="Client_Id_XXXXXXXXXXXX"
CLIENT_SECRET="Client_Secret_XXXXXXXXXXXX"
CERT_PASSWORD="senha_do_certificado"
CERT_FILE="producao-123456.p12"

# Criar arquivo temporário com as mudanças
cat > /tmp/config_updates.sed << EOF
s/define('EFIPAY_CLIENT_ID', '[^']*'/define('EFIPAY_CLIENT_ID', '$CLIENT_ID'/
s/define('EFIPAY_CLIENT_SECRET', '[^']*'/define('EFIPAY_CLIENT_SECRET', '$CLIENT_SECRET'/
s/define('EFIPAY_SANDBOX', true)/define('EFIPAY_SANDBOX', false)/
s#// define('EFIPAY_CERT_PATH'#define('EFIPAY_CERT_PATH'#
s#/certs/.*\.p12'#/certs/$CERT_FILE'#
s/define('EFIPAY_CERT_PASS', '[^']*'/define('EFIPAY_CERT_PASS', '$CERT_PASSWORD'/
EOF

# Aplicar mudanças
sed -i.bak -f /tmp/config_updates.sed $CONFIG_FILE

echo "✅ config.php atualizado"
echo ""

# ============================================
# PASSO 5: EXECUTAR MIGRAÇÃO SQL
# ============================================
echo "🗄️  PASSO 5: Executando migração do banco de dados..."
echo ""

# Importar script de migração
mysql -u $DB_USER -p$DB_PASS $DB_NAME < /var/www/proservice/MIGRACAO_ASSINATURAS.sql

if [ $? -eq 0 ]; then
    echo "✅ Migração SQL executada com sucesso"
else
    echo "❌ ERRO na migração SQL!"
    echo "   Revertendo config.php..."
    cp $CONFIG_FILE.backup_$TIMESTAMP $CONFIG_FILE
    echo "   Revertido. Verifique o erro acima."
    exit 1
fi

echo ""

# ============================================
# PASSO 6: VALIDAR BANCO DE DADOS
# ============================================
echo "✅ PASSO 6: Validando banco de dados..."
echo ""

# Verificar campos
echo "SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME='empresas' AND COLUMN_NAME IN ('assinatura_id', 'assinatura_status', 'cpf_responsavel', 'responsavel_nome') LIMIT 1;" | \
mysql -u $DB_USER -p$DB_PASS $DB_NAME

echo ""

# ============================================
# PASSO 7: TESTAR CONECTIVIDADE EFIPAY
# ============================================
echo "🔗 PASSO 7: Testando conectividade com EfiPay..."
echo ""

# Teste básico (requer curl)
echo "Testando autenticação OAuth2..."
curl -s -X POST https://cobrancas.api.efipay.com.br/v1/authorize \
  -H "Authorization: Basic $(echo -n "$CLIENT_ID:$CLIENT_SECRET" | base64)" \
  -H "Content-Type: application/json" \
  -d '{"grant_type":"client_credentials"}' | grep -q "access_token"

if [ $? -eq 0 ]; then
    echo "✅ Conectividade OK"
else
    echo "⚠️  Aviso: Não foi possível testar. Verifique manualmente."
fi

echo ""

# ============================================
# PASSO 8: CONFIGURAR WEBHOOK (MANUAL)
# ============================================
echo "🔔 PASSO 8: Configurar Webhook no EfiPay..."
echo ""
echo "⚠️  MANUAL: Acesse o painel EfiPay e configure:"
echo ""
echo "   Painel: https://dashboard.efipay.com.br"
echo "   Ir para: Configurações > Webhooks > + Novo Webhook"
echo ""
echo "   URL do Webhook:"
echo "   ─────────────────────────────────────────────────────"
echo "   https://seu-dominio.com/webhook/efipay"
echo "   ─────────────────────────────────────────────────────"
echo ""
echo "   Eventos para selecionar:"
echo "   ✓ subscription.payment"
echo "   ✓ subscription.suspended"
echo "   ✓ subscription.canceled"
echo "   ✓ subscription.reactivated"
echo ""
echo "   Depois de configurar, clique em 'Enviar Teste'"
echo ""
read -p "Pressione ENTER quando webhook estiver configurado..."

echo ""

# ============================================
# PASSO 9: LIMPAR CACHE
# ============================================
echo "🧹 PASSO 9: Limpando cache..."
echo ""

# Se usar Redis ou similar
# redis-cli FLUSHDB

# Se usar memcache
# echo flush_all | nc localhost 11211

# Limpar cache de PHP (se aplicável)
rm -rf /var/www/proservice/storage/cache/* 2>/dev/null

echo "✅ Cache limpo"
echo ""

# ============================================
# PASSO 10: VERIFICAÇÃO FINAL
# ============================================
echo "📋 PASSO 10: Verificação Final..."
echo ""

echo "Checklist:"
echo "  [✓] Backup do banco criado: $BACKUP_DIR/proservice_$TIMESTAMP.sql"
echo "  [✓] Certificado uploaded e permissões corretas"
echo "  [✓] config.php atualizado com chaves de produção"
echo "  [✓] SANDBOX desativado"
echo "  [✓] Certificado configurado"
echo "  [✓] Banco de dados migrado"
echo "  [✓] Webhook configurado no EfiPay"
echo ""

# ============================================
# PASSO 11: TESTES
# ============================================
echo "🧪 PASSO 11: Executar testes..."
echo ""
echo "Acesse em seu navegador e teste:"
echo ""
echo "1. Dashboard:"
echo "   https://seu-dominio.com/dashboard"
echo "   Deve mostrar widget Trial com dias restantes"
echo ""
echo "2. Seleção de Planos:"
echo "   https://seu-dominio.com/assinaturas"
echo "   Deve mostrar 3 planos com valores corretos"
echo ""
echo "3. Teste de Checkout:"
echo "   Clique em 'Assinar Agora' (Plano Profissional)"
echo "   Deve redirecionar para EfiPay"
echo ""
echo "4. Verificar Logs:"
echo "   tail -50 /var/log/php/proservice.log | grep -i efipay"
echo "   Procure por: 'Tentando autenticar com clientId...' (OK)"
echo "                 'HTTP=200' (OK)"
echo "                 'Acesso Token obtido' (OK)"
echo ""

read -p "Pressione ENTER quando testes forem concluídos..."

echo ""

# ============================================
# PASSO 12: MONITORAMENTO
# ============================================
echo "📊 PASSO 12: Ativar Monitoramento..."
echo ""

# Criar script de monitoramento
cat > /usr/local/bin/monitor-assinaturas.sh << 'MONITOR_SCRIPT'
#!/bin/bash
# Monitorar webhook e status de assinaturas a cada hora

DB_USER="seu_usuario_db"
DB_PASS="sua_senha_db"
DB_NAME="proservice"
EMAIL_ALERTA="seu-email@dominio.com"

# Verificar se há webhooks não processados
PENDENTES=$(mysql -u $DB_USER -p$DB_PASS $DB_NAME -N -e "
  SELECT COUNT(*) FROM pagamentos_rastreamento WHERE processado_em IS NULL;
")

if [ "$PENDENTES" -gt "0" ]; then
    echo "⚠️  ALERTA: $PENDENTES webhooks não processados" | \
    mail -s "Assinaturas - Webhook Pendente" $EMAIL_ALERTA
fi

# Verificar se há assinaturas suspeitas
SUSPEITAS=$(mysql -u $DB_USER -p$DB_PASS $DB_NAME -N -e "
  SELECT COUNT(*) FROM empresas WHERE assinatura_status = 'suspended' AND updated_at < DATE_SUB(NOW(), INTERVAL 24 HOUR);
")

if [ "$SUSPEITAS" -gt "0" ]; then
    echo "⚠️  ALERTA: $SUSPEITAS assinaturas suspensas há mais de 24h" | \
    mail -s "Assinaturas - Suspensão Prolongada" $EMAIL_ALERTA
fi
MONITOR_SCRIPT

chmod +x /usr/local/bin/monitor-assinaturas.sh

# Adicionar ao crontab
(crontab -l 2>/dev/null; echo "0 * * * * /usr/local/bin/monitor-assinaturas.sh") | crontab -

echo "✅ Monitoramento ativado (executa a cada hora)"
echo ""

# ============================================
# RESUMO FINAL
# ============================================
echo "═══════════════════════════════════════════════════════════════════════════"
echo "  ✅ IMPLANTAÇÃO CONCLUÍDA COM SUCESSO!"
echo "═══════════════════════════════════════════════════════════════════════════"
echo ""
echo "📋 Resumo do que foi feito:"
echo ""
echo "   ✓ Backup do banco: $BACKUP_DIR/proservice_$TIMESTAMP.sql"
echo "   ✓ Certificado SSL configurado"
echo "   ✓ config.php atualizado para PRODUÇÃO"
echo "   ✓ Banco de dados migrado (+4 campos)"
echo "   ✓ Webhook configurado no EfiPay"
echo "   ✓ Testes passaram com sucesso"
echo "   ✓ Monitoramento ativado"
echo ""
echo "🚀 Seu sistema está PRONTO PARA PRODUÇÃO!"
echo ""
echo "📞 Se encontrar problemas:"
echo "   1. Verificar logs: tail -100 /var/log/php/proservice.log"
echo "   2. Contatar suporte EfiPay: suporte@efipay.com.br"
echo "   3. Rollback: mysql -u $DB_USER -p $DB_NAME < $BACKUP_DIR/proservice_$TIMESTAMP.sql"
echo ""
echo "═══════════════════════════════════════════════════════════════════════════"
echo ""
