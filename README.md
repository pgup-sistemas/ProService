# proService - Sistema de Gestão de Ordens de Serviço (SaaS)

Sistema profissional para prestadores de serviço organizarem e profissionalizarem seu negócio. MVP funcional multiempresa com isolamento por empresa_id.

## 🎯 Stack Tecnológico

- **PHP 8+**
- **MySQL 8+**
- **Bootstrap 5** (mobile-first)
- **PDO** (prepared statements)
- **Arquitetura MVC** simples modular (sem frameworks pesados)

## 📁 Estrutura do Projeto

```
proService/
├── app/
│   ├── config/
│   │   ├── config.php          # Configurações da aplicação
│   │   ├── Database.php        # Classe de conexão PDO
│   │   ├── helpers.php         # Funções auxiliares
│   │   └── Router.php          # Sistema de rotas
│   ├── controllers/
│   │   ├── Controller.php      # Controller base
│   │   ├── AuthController.php  # Autenticação
│   │   ├── DashboardController.php
│   │   ├── ClienteController.php
│   │   ├── ProdutoController.php
│   │   ├── ServicoController.php
│   │   ├── OrdemServicoController.php
│   │   ├── FinanceiroController.php
│   │   └── PublicoController.php
│   ├── models/
│   │   ├── Model.php           # Model base
│   │   ├── Empresa.php
│   │   ├── Usuario.php
│   │   ├── Cliente.php
│   │   ├── Produto.php
│   │   ├── Servico.php
│   │   ├── OrdemServico.php
│   │   ├── Receita.php
│   │   └── Despesa.php
│   ├── middlewares/
│   │   ├── AuthMiddleware.php
│   │   └── PlanoMiddleware.php
│   └── views/
│       ├── layouts/            # Layouts principais
│       ├── auth/               # Telas de login/registro
│       ├── dashboard/           # Dashboard
│       ├── clientes/          # CRUD clientes
│       ├── produtos/          # CRUD produtos
│       ├── servicos/          # CRUD serviços
│       ├── ordens/            # Ordens de serviço
│       ├── financeiro/        # Financeiro
│       └── publicos/          # Páginas públicas
├── public/
│   ├── assets/                # CSS, JS, imagens
│   └── uploads/               # Arquivos de upload
├── database.sql               # Script SQL inicial
└── index.php                  # Ponto de entrada
```

## 🚀 Instalação

### 1. Requisitos

- XAMPP ou servidor PHP 8+
- MySQL 8+
- Extensões PHP: pdo, pdo_mysql

### 2. Configuração

1. Clone o projeto para `c:\xampp\htdocs\proService`
2. Importe o banco de dados:
   ```bash
   mysql -u root -p < database.sql
   ```
   Ou use phpMyAdmin para importar `database.sql`

3. Configure o banco de dados em `app/config/config.php`:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_NAME', 'proservice');
   define('DB_USER', 'root');
   define('DB_PASS', '');
   ```

### Configuração segura (não versionar segredos)
- Use o arquivo de exemplo `app/config/config.example.php` como modelo e NÃO comite `app/config/config.php`.

- Copiar o exemplo para o arquivo de configuração real:
  - Linux / macOS:
    ```bash
    cp app/config/config.example.php app/config/config.php
    ```
  - Windows (PowerShell):
    ```powershell
    Copy-Item .\app\config\config.example.php .\app\config\config.php
    ```

- Preencha `app/config/config.php` com suas credenciais (DB, APP_URL, EFIPAY, certificados, etc.).

- Boas práticas e permissões:
  - Não armazenar chaves/segredos no Git; `app/config/config.php` já está em `.gitignore`.
  - Se acidentalmente comitou o arquivo sensível, remova do histórico local rapidamente:
    ```bash
    git rm --cached app/config/config.php
    git commit -m "chore: remove sensitive config.php"
    git push
    ```
  - Garantir permissão de escrita para uploads:
    - Linux:
      ```bash
      sudo chown -R www-data:www-data public/uploads
      sudo chmod -R 775 public/uploads
      ```
    - Windows (IIS/Apache): conceda permissão de escrita ao usuário do serviço web (IUSR / IIS_IUSRS / usuário Apache).
  - Coloque certificados em `app/certs/` e não os versionar (já ignorado pelo `.gitignore`).

  - Importação em background (Import Jobs) — documentação rápida:

    O sistema suporta enfileirar imports grandes (CSV / XLSX) para processamento assíncrono por um *worker*.

    - Migração (obrigatório): execute o SQL da migration para criar `import_jobs`:
      ```bash
      mysql -u <user> -p proservice < migrations/20260216_create_import_jobs.sql
      ```

    - Onde os arquivos ficam: `public/uploads/imports/` (criado automaticamente ao enfileirar).

    - Como o usuário usa (UI):
      1. Produtos → Importar (CSV / XLSX).
      2. Marcar `Processar em background` ou enviar arquivo > 2MB → arquivo será **enfileirado**.
      3. Acompanhar em Produtos → Jobs de Importação (lista, filtro por status, detalhe e download de logs/resultados).

    - Rotas (autenticadas):
      - GET  `/produtos/export?format=csv|xlsx` — export
      - POST `/produtos/import/preview` — preview (CSV/XLSX)
      - POST `/produtos/import` — import (sync ou enqueue)
      - GET  `/produtos/import-jobs` — lista jobs
      - GET  `/produtos/import-jobs/{id}` — detalhe job
      - GET  `/produtos/import-jobs/{id}/download` — baixar erros / resultado
      - POST `/produtos/import-jobs/{id}/cancel` — cancelar job pendente

    - Worker CLI e agendamento:
      - Script: `scripts/import_worker.php` (processa jobs pendentes em lote).
      - Recomenda-se agendar a cada 1 minuto (cron / Task Scheduler).

        Linux (cron):
        ```bash
        * * * * * cd /c/xampp/htdocs/proService && php scripts/import_worker.php >> /var/log/proservice/import_worker.log 2>&1
        ```

        Windows (Task Scheduler): executar `php C:\\xampp\\htdocs\\proService\\scripts\\import_worker.php` periodicamente.

    - Regras e limites:
      - Upload sincrono: arquivos pequenos (até 10MB) continuam sendo processados diretamente no request.
      - Enfileiramento automático: arquivos maiores que 2MB ou quando o usuário marca `Processar em background`.
      - Tipos aceitos: `.csv`, `.xls`, `.xlsx` (PhpSpreadsheet é usado para XLSX).
      - Sanitização: prefixo para mitigar CSV-injection; validações numéricas básicas aplicadas.

    - Estados do job: `pending`, `processing`, `completed`, `failed`, `cancelled`.
      - Resultados e erros são salvos em `result_json` / `error_text` e podem ser baixados na UI.

    - Template mínimo (header CSV/XLSX):
      ```text
      codigo_sku,nome,categoria,unidade,quantidade_estoque,quantidade_minima,custo_unitario,preco_venda,fornecedor,observacoes
      SKU-001,Parafuso M3,Fixação,PC,100,10,0.05,0.10,Fabricante X,Exemplo
      ```

    - Teste rápido:
      1. Faça upload de um arquivo pequeno sem marcar background → deve executar imediatamente.
      2. Faça upload de arquivo grande (>2MB) com background → verifique `/produtos/import-jobs` e acompanhe progresso.
      3. Abra job com falhas e clique em "Baixar log de erros".

    - Manutenção e boas práticas:
      - Remover periodicamente arquivos antigos em `public/uploads/imports/` (retention policy).
      - Monitorar `logs_sistema` e `import_jobs` para falhas frequentes.
      - Conceder permissões adequadas somente ao usuário do serviço web para `public/uploads/`.

    - Problemas comuns:
      - PhpSpreadsheet ausente → execute `composer install` (já incluído em `composer.json`).
      - Worker não agendado → jobs ficam em `pending` até o worker rodar.
      - Arquivo não encontrado → verifique permissões e existência em `public/uploads/imports/`.


4. Acesse: `http://localhost/proService`

## 🌐 URLs Principais

- **Login**: `/login`
- **Registro**: `/register` (trial 15 dias)
- **Dashboard**: `/dashboard`
- **Clientes**: `/clientes`
- **Produtos**: `/produtos`
- **Serviços**: `/servicos`
- **Ordens de Serviço**: `/ordens`
- **Financeiro**: `/financeiro`
- **Link Público**: `/acompanhar/{token}`

## 💎 Funcionalidades MVP

### 🔐 1. Autenticação
- Registro de empresa (inicia trial 15 dias)
- Login seguro com hash bcrypt
- Controle de perfil (admin / tecnico)

### 👥 2. Clientes
- CRUD completo
- Busca por nome/telefone
- Histórico de serviços

### 📦 3. Produtos (Estoque)
- CRUD
- Controle de quantidade
- Alerta estoque mínimo
- Movimentação de entrada

### 🛠 4. Serviços
- Cadastro de serviços
- Valores e garantia padrão
- Duplicação rápida

### 📋 5. Ordem de Serviço (Core)
- Criar OS com cliente, serviço e produtos
- Baixa automática no estoque
- Cálculo de valor total e lucro real
- Status workflow: aberta → execução → finalizada → paga
- Link público de acompanhamento

### 💰 6. Financeiro
- Listagem de receitas e despesas
- Marcar receitas como pagas
- Dashboard com receita, despesas e lucro

## 🔒 Segurança

- Todas as queries filtram por `empresa_id`
- PDO com prepared statements
- Escape de saída com `htmlspecialchars`
- CSRF token em formulários
- Session timeout (2 horas)
- Hash bcrypt para senhas

## 📱 Mobile-First

Layout responsivo otimizado para dispositivos móveis:
- Menu lateral colapsável
- Cards adaptáveis
- Tabelas com scroll horizontal

## 🗄️ Banco de Dados

Tabelas principais:
- `empresas` - Dados das empresas e planos
- `usuarios` - Usuários do sistema
- `clientes` - Cadastro de clientes
- `servicos` - Cadastro de serviços
- `produtos` - Controle de estoque
- `ordens_servico` - Ordens de serviço
- `os_produtos` - Produtos usados na OS
- `receitas` - Controle de receitas
- `despesas` - Controle de despesas

## 📄 Licença

Sistema desenvolvido para uso comercial.

---

**proService** - Gestão Profissional de Serviços
