================================================================================
DIRETÓRIO DE CERTIFICADOS SSL - EFIPAY
================================================================================

📍 Localização: /app/certs/
🔐 Permissões: chmod 600 (apenas leitura)
🌐 Domínio: proservice.pageup.net.br

================================================================================
INSTRUÇÕES PARA ADICIONAR CERTIFICADO
================================================================================

PASSO 1: GERAR CERTIFICADO NO EFIPAY
────────────────────────────────────
1. Acesse: https://dashboard.efipay.com.br
2. Menu: Configurações > Segurança > Certificados
3. Clique em: "Gerar novo certificado"
4. Formato: .P12 (certificado PKCS12)
5. Defina uma senha segura (anote!)
6. Baixe o arquivo (ex: producao-123456.p12)

PASSO 2: FAZER UPLOAD DO CERTIFICADO
─────────────────────────────────────
VIA FTP:
  1. Conecte via FTP ao servidor: proservice.pageup.net.br
  2. Navegue até: /app/certs/
  3. Faça upload do arquivo .p12

VIA SCP (Linux/Mac/Git Bash):
  scp seu-usuario@seu-servidor:/var/www/proservice/app/certs/
  scp producao-123456.p12 seu-usuario@proservice.pageup.net.br:/var/www/proservice/app/certs/

VIA SSH (no servidor):
  1. Conecte via SSH
  2. Vá para: cd /var/www/proservice/app/certs/
  3. Cole o arquivo


PASSO 3: AJUSTAR PERMISSÕES
───────────────────────────
Via SSH no servidor:
  chmod 600 /var/www/proservice/app/certs/*.p12

Isso garante que:
  ✓ Apenas o usuário do servidor pode ler
  ✓ Ninguém pode modificar
  ✓ Não fica visível publicamente


PASSO 4: ATUALIZAR config.php
──────────────────────────────
Arquivo: /app/config/config.php
Linhas: ~121-122

Descomente e ajuste:
  define('EFIPAY_CERT_PATH', PROSERVICE_ROOT . '/certs/producao-123456.p12');
  define('EFIPAY_CERT_PASS', 'senha_que_voce_definiu');


PASSO 5: TESTAR CONEXÃO
────────────────────────
1. Acesse: https://proservice.pageup.net.br/dashboard
2. Verifique se não há erro de SSL
3. Tente fazer um checkout de teste
4. Verifique logs: tail -50 /var/log/php/proservice.log

================================================================================
NOMES DE ARQUIVOS RECOMENDADOS
================================================================================

Para facilitar identificação, nomeie assim:

  producao-123456.p12          ← Certificado de produção (com data)
  producao-123456-backup.p12   ← Backup do certificado

Exemplo com timestamp:
  producao-2026-02-14.p12
  producao-2026-02-14-backup.p12

================================================================================
SEGURANÇA
================================================================================

⚠️  IMPORTANTE:

  ✗ NÃO coloque certificado no Git
  ✗ NÃO compartilhe em email/chat
  ✗ NÃO deje público no servidor
  ✓ SEMPRE faça backup seguro
  ✓ SEMPRE use permissões 600
  ✓ SEMPRE mude a senha do certificado
  ✓ SEMPRE guarde a senha em lugar seguro

Adicione à .gitignore:
  /app/certs/*.p12
  /app/certs/*.pfx
  /app/certs/*.key


================================================================================
LISTA DE VERIFICAÇÃO
================================================================================

Antes de colocar em produção:

[ ] Certificado .p12 obtido no painel EfiPay
[ ] Arquivo enviado para /app/certs/
[ ] Permissões ajustadas (chmod 600)
[ ] config.php atualizado com CERT_PATH
[ ] config.php atualizado com CERT_PASS
[ ] EFIPAY_SANDBOX = false em config.php
[ ] Testado checkout em HTTPS
[ ] Webhook recebido com sucesso
[ ] Logs mostram "SSL_CERT OK" ou similar
[ ] Sistema em produção! 🎉

================================================================================
REFERÊNCIA
================================================================================

Arquivo de config: /app/config/config.php (linhas ~115-125)
Documentação EfiPay: https://dev.efipay.com.br
Domínio de produção: https://proservice.pageup.net.br
Webhook: https://proservice.pageup.net.br/webhook/efipay

================================================================================
Data de criação: 14/02/2026
Domínio: proservice.pageup.net.br
Status: PRONTO PARA CERTIFICADO
================================================================================
