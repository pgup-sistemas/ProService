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
  - Processamento assíncrono (import background):
    - O worker processa arquivos enfileirados em `public/uploads/imports/`.
    - Linux (cron — a cada minuto):
      ```bash
      * * * * * cd /c/xampp/htdocs/proService && php scripts/import_worker.php >> /var/log/proservice/import_worker.log 2>&1
      ```
    - Windows: agende `php C:\\xampp\\htdocs\\proService\\scripts\\import_worker.php` no Task Scheduler (repetir cada 1 minuto).
    - Não esqueça: rode o SQL em `migrations/20260216_create_import_jobs.sql` antes de usar.

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
