-- ============================================================================
-- 🚀 PROSERVICE SaaS - BANCO DE DADOS (VERSÃO FINAL)
-- ============================================================================
-- Data: 14/02/2026
-- Stack: PHP 8+, MySQL 8+
-- Nota: Removidos DEFAULT de datas (incompatível com MySQL < 8.0)
-- ============================================================================

CREATE DATABASE IF NOT EXISTS proservice CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE proservice;

CREATE TABLE IF NOT EXISTS empresas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome_fantasia VARCHAR(255) NOT NULL,
    razao_social VARCHAR(255),
    cnpj_cpf VARCHAR(20) NOT NULL UNIQUE,
    email VARCHAR(255) NOT NULL UNIQUE,
    telefone VARCHAR(20),
    whatsapp VARCHAR(20),
    cep VARCHAR(10),
    endereco VARCHAR(255),
    numero VARCHAR(20),
    complemento VARCHAR(100),
    bairro VARCHAR(100),
    cidade VARCHAR(100),
    estado CHAR(2),
    logo VARCHAR(255),
    plano ENUM('trial', 'basico', 'starter', 'profissional', 'pro') DEFAULT 'trial',
    data_inicio_plano DATE,
    data_fim_trial DATE,
    limite_os_mes INT DEFAULT 20,
    limite_tecnicos INT DEFAULT 1,
    limite_armazenamento_mb INT DEFAULT 100,
    os_criadas_mes_atual INT DEFAULT 0,
    mes_referencia_os VARCHAR(7),
    assinatura_id BIGINT NULL,
    assinatura_status ENUM('inactive', 'pending', 'active', 'suspended', 'canceled') DEFAULT 'inactive',
    cpf_responsavel VARCHAR(20),
    responsavel_nome VARCHAR(255),
    status ENUM('ativo', 'bloqueado', 'cancelado') DEFAULT 'ativo',
    banco_nome VARCHAR(100),
    banco_agencia VARCHAR(20),
    banco_conta VARCHAR(20),
    banco_tipo ENUM('corrente', 'poupanca'),
    chave_pix VARCHAR(100),
    termos_contrato TEXT,
    onboarding_completo TINYINT(1) DEFAULT 0,
    onboarding_etapa TINYINT UNSIGNED DEFAULT 1,
    aceite_termos_em DATETIME NULL,
    aceite_ip VARCHAR(45) NULL,
    aceite_user_agent VARCHAR(500) NULL,
    aceite_versao_termo VARCHAR(20) DEFAULT '1.0',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_plano (plano),
    INDEX idx_status (status),
    INDEX idx_cnpj (cnpj_cpf),
    INDEX idx_assinatura_id (assinatura_id),
    INDEX idx_assinatura_status (assinatura_status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    empresa_id INT NOT NULL,
    nome VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    senha VARCHAR(255) NOT NULL,
    telefone VARCHAR(20),
    perfil ENUM('admin', 'tecnico') DEFAULT 'tecnico',
    ativo TINYINT(1) DEFAULT 1,
    assinatura_digital VARCHAR(255),
    ultimo_acesso DATETIME,
    reset_token VARCHAR(64) DEFAULT NULL,
    reset_token_expira DATETIME DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (empresa_id) REFERENCES empresas(id) ON DELETE CASCADE,
    UNIQUE KEY uk_empresa_email (empresa_id, email),
    INDEX idx_empresa_perfil (empresa_id, perfil),
    INDEX idx_ativo (ativo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS clientes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    empresa_id INT NOT NULL,
    nome VARCHAR(255) NOT NULL,
    cpf_cnpj VARCHAR(20),
    telefone VARCHAR(20),
    whatsapp VARCHAR(20),
    email VARCHAR(255),
    cep VARCHAR(10),
    endereco VARCHAR(255),
    numero VARCHAR(20),
    complemento VARCHAR(100),
    bairro VARCHAR(100),
    cidade VARCHAR(100),
    estado CHAR(2),
    data_nascimento DATE,
    observacoes TEXT,
    como_conheceu ENUM('indicacao', 'google', 'redes_sociais', 'outros'),
    ativo TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (empresa_id) REFERENCES empresas(id) ON DELETE CASCADE,
    INDEX idx_empresa_nome (empresa_id, nome),
    INDEX idx_ativo (ativo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS servicos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    empresa_id INT NOT NULL,
    nome VARCHAR(255) NOT NULL,
    categoria VARCHAR(100),
    descricao_padrao TEXT,
    valor_padrao DECIMAL(10, 2) DEFAULT 0,
    garantia_dias INT DEFAULT 0,
    tempo_medio_horas DECIMAL(5, 2),
    ativo TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (empresa_id) REFERENCES empresas(id) ON DELETE CASCADE,
    INDEX idx_empresa_nome (empresa_id, nome),
    INDEX idx_ativo (ativo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS produtos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    empresa_id INT NOT NULL,
    nome VARCHAR(255) NOT NULL,
    codigo_sku VARCHAR(50),
    categoria VARCHAR(100),
    unidade ENUM('UN', 'KG', 'M', 'L', 'CX', 'PC') DEFAULT 'UN',
    quantidade_estoque DECIMAL(10, 2) DEFAULT 0,
    quantidade_minima DECIMAL(10, 2) DEFAULT 0,
    custo_unitario DECIMAL(10, 2) DEFAULT 0,
    preco_venda DECIMAL(10, 2) DEFAULT 0,
    fornecedor VARCHAR(255),
    observacoes TEXT,
    ativo TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (empresa_id) REFERENCES empresas(id) ON DELETE CASCADE,
    INDEX idx_empresa_nome (empresa_id, nome),
    INDEX idx_ativo (ativo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS ordens_servico (
    id INT AUTO_INCREMENT PRIMARY KEY,
    empresa_id INT NOT NULL,
    numero_os INT NOT NULL,
    cliente_id INT NOT NULL,
    tecnico_id INT,
    servico_id INT,
    descricao TEXT,
    prioridade ENUM('urgente', 'alta', 'normal', 'baixa') DEFAULT 'normal',
    data_entrada DATE,
    previsao_entrega DATE,
    data_finalizacao DATE,
    valor_servico DECIMAL(10, 2) DEFAULT 0,
    taxas_adicionais DECIMAL(10, 2) DEFAULT 0,
    desconto DECIMAL(10, 2) DEFAULT 0,
    valor_total DECIMAL(10, 2) DEFAULT 0,
    custo_produtos DECIMAL(10, 2) DEFAULT 0,
    lucro_real DECIMAL(10, 2) DEFAULT 0,
    forma_pagamento_acordada ENUM('dinheiro', 'pix', 'cartao_credito', 'cartao_debito', 'boleto', 'transferencia'),
    status ENUM('aberta', 'em_orcamento', 'aprovada', 'em_execucao', 'pausada', 'finalizada', 'paga', 'cancelada') DEFAULT 'aberta',
    garantia_dias INT DEFAULT 0,
    observacoes_internas TEXT,
    observacoes_cliente TEXT,
    token_publico VARCHAR(64) UNIQUE,
    assinatura_cliente VARCHAR(255),
    assinatura_data DATETIME,
    assinatura_tecnico VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (empresa_id) REFERENCES empresas(id) ON DELETE CASCADE,
    FOREIGN KEY (cliente_id) REFERENCES clientes(id),
    FOREIGN KEY (tecnico_id) REFERENCES usuarios(id),
    FOREIGN KEY (servico_id) REFERENCES servicos(id),
    UNIQUE KEY uk_empresa_numero_os (empresa_id, numero_os),
    INDEX idx_empresa_status (empresa_id, status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS os_produtos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    os_id INT NOT NULL,
    produto_id INT NOT NULL,
    quantidade DECIMAL(10, 2) NOT NULL,
    custo_unitario DECIMAL(10, 2) NOT NULL,
    custo_total DECIMAL(10, 2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (os_id) REFERENCES ordens_servico(id) ON DELETE CASCADE,
    FOREIGN KEY (produto_id) REFERENCES produtos(id),
    INDEX idx_os (os_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS os_fotos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    os_id INT NOT NULL,
    arquivo VARCHAR(255) NOT NULL,
    tipo ENUM('antes', 'durante', 'depois') DEFAULT 'antes',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (os_id) REFERENCES ordens_servico(id) ON DELETE CASCADE,
    INDEX idx_os (os_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS os_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    os_id INT NOT NULL,
    usuario_id INT NOT NULL,
    acao VARCHAR(100) NOT NULL,
    tipo_acao ENUM('status', 'valor', 'produto', 'dados', 'assinatura', 'visualizacao', 'outro') DEFAULT 'outro',
    dados_anteriores JSON DEFAULT NULL,
    dados_novos JSON DEFAULT NULL,
    ip_address VARCHAR(45) DEFAULT NULL,
    user_agent TEXT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (os_id) REFERENCES ordens_servico(id) ON DELETE CASCADE,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    INDEX idx_os_id (os_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS receitas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    empresa_id INT NOT NULL,
    os_id INT,
    descricao VARCHAR(255) NOT NULL,
    valor DECIMAL(10, 2) NOT NULL,
    data_recebimento DATE,
    forma_pagamento ENUM('dinheiro', 'pix', 'cartao_credito', 'cartao_debito', 'boleto', 'transferencia'),
    status ENUM('pendente', 'recebido', 'cancelado') DEFAULT 'pendente',
    observacoes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (empresa_id) REFERENCES empresas(id) ON DELETE CASCADE,
    FOREIGN KEY (os_id) REFERENCES ordens_servico(id) ON DELETE SET NULL,
    INDEX idx_empresa_status (empresa_id, status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS despesas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    empresa_id INT NOT NULL,
    descricao VARCHAR(255) NOT NULL,
    categoria ENUM('material', 'servico', 'salario', 'aluguel', 'imposto', 'outros') DEFAULT 'outros',
    valor DECIMAL(10, 2) NOT NULL,
    data_despesa DATE NOT NULL,
    forma_pagamento ENUM('dinheiro', 'pix', 'cartao_credito', 'cartao_debito', 'boleto', 'transferencia'),
    status ENUM('pendente', 'pago', 'cancelado') DEFAULT 'pago',
    comprovante VARCHAR(255) NULL,
    observacoes TEXT,
    recorrente TINYINT(1) DEFAULT 0,
    frequencia ENUM('mensal', 'semanal', 'anual') NULL,
    despesa_pai_id INT NULL,
    data_proximo_vencimento DATE NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (empresa_id) REFERENCES empresas(id) ON DELETE CASCADE,
    FOREIGN KEY (despesa_pai_id) REFERENCES despesas(id) ON DELETE SET NULL,
    INDEX idx_empresa_categoria (empresa_id, categoria)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS movimentacao_estoque (
    id INT AUTO_INCREMENT PRIMARY KEY,
    empresa_id INT NOT NULL,
    produto_id INT NOT NULL,
    tipo ENUM('entrada', 'saida', 'ajuste') NOT NULL,
    quantidade DECIMAL(10, 2) NOT NULL,
    custo_unitario DECIMAL(10, 2),
    motivo VARCHAR(255),
    os_id INT,
    usuario_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (empresa_id) REFERENCES empresas(id) ON DELETE CASCADE,
    FOREIGN KEY (produto_id) REFERENCES produtos(id),
    FOREIGN KEY (os_id) REFERENCES ordens_servico(id) ON DELETE SET NULL,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id),
    INDEX idx_empresa_produto (empresa_id, produto_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS recibos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    empresa_id INT NOT NULL,
    numero_recibo INT NOT NULL,
    os_id INT NOT NULL,
    cliente_id INT NOT NULL,
    valor DECIMAL(10, 2) NOT NULL,
    valor_extenso VARCHAR(255),
    data_emissao DATE,
    forma_pagamento ENUM('dinheiro', 'pix', 'cartao_credito', 'cartao_debito', 'boleto', 'transferencia'),
    observacoes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (empresa_id) REFERENCES empresas(id) ON DELETE CASCADE,
    FOREIGN KEY (os_id) REFERENCES ordens_servico(id),
    FOREIGN KEY (cliente_id) REFERENCES clientes(id),
    UNIQUE KEY uk_empresa_numero_recibo (empresa_id, numero_recibo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS configuracoes_empresa (
    id INT AUTO_INCREMENT PRIMARY KEY,
    empresa_id INT NOT NULL UNIQUE,
    cor_primaria VARCHAR(7) DEFAULT '#1e40af',
    cor_sucesso VARCHAR(7) DEFAULT '#059669',
    cor_alerta VARCHAR(7) DEFAULT '#ea580c',
    template_contrato TEXT,
    mensagem_whatsapp_os_criada TEXT,
    mensagem_whatsapp_os_finalizada TEXT,
    mensagem_whatsapp_recibo TEXT,
    enviar_notificacoes_auto TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (empresa_id) REFERENCES empresas(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS csrf_tokens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    token VARCHAR(64) NOT NULL,
    session_id VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expires_at TIMESTAMP NOT NULL,
    used TINYINT(1) DEFAULT 0,
    INDEX idx_token (token),
    INDEX idx_expires (expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS comunicacoes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    empresa_id INT NOT NULL,
    os_id INT NOT NULL,
    cliente_id INT NOT NULL,
    tipo ENUM('whatsapp', 'email') DEFAULT 'whatsapp',
    template_usado VARCHAR(100) DEFAULT NULL,
    mensagem_enviada TEXT NOT NULL,
    status ENUM('enviado', 'erro') DEFAULT 'enviado',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (empresa_id) REFERENCES empresas(id) ON DELETE CASCADE,
    FOREIGN KEY (os_id) REFERENCES ordens_servico(id) ON DELETE CASCADE,
    FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE CASCADE,
    INDEX idx_os_id (os_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS parcelas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    empresa_id INT NOT NULL,
    receita_id INT NOT NULL,
    numero_parcela INT NOT NULL,
    valor DECIMAL(10, 2) NOT NULL,
    data_vencimento DATE NOT NULL,
    data_pagamento DATE NULL,
    status ENUM('pendente', 'pago', 'atrasado') DEFAULT 'pendente',
    forma_pagamento ENUM('dinheiro', 'pix', 'cartao_credito', 'cartao_debito', 'boleto', 'transferencia') NULL,
    observacoes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (empresa_id) REFERENCES empresas(id) ON DELETE CASCADE,
    FOREIGN KEY (receita_id) REFERENCES receitas(id) ON DELETE CASCADE,
    INDEX idx_receita (receita_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS logs_sistema (
    id INT AUTO_INCREMENT PRIMARY KEY,
    empresa_id INT NOT NULL,
    usuario_id INT NOT NULL,
    acao VARCHAR(100) NOT NULL,
    modulo VARCHAR(50) NOT NULL,
    entidade_id INT NULL,
    detalhes JSON NULL,
    ip_address VARCHAR(45) NULL,
    user_agent TEXT NULL,
    nivel ENUM('info', 'warning', 'error', 'critical') DEFAULT 'info',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (empresa_id) REFERENCES empresas(id) ON DELETE CASCADE,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    INDEX idx_empresa (empresa_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS email_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    empresa_id INT NOT NULL,
    para VARCHAR(255) NOT NULL,
    assunto VARCHAR(255) NOT NULL,
    status ENUM('success', 'failed', 'pending') DEFAULT 'pending',
    erro TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (empresa_id) REFERENCES empresas(id) ON DELETE CASCADE,
    INDEX idx_empresa (empresa_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS pagamentos_rastreamento (
    id INT AUTO_INCREMENT PRIMARY KEY,
    empresa_id INT NOT NULL,
    assinatura_id BIGINT,
    plano_anterior VARCHAR(50),
    plano_novo VARCHAR(50),
    status_anterior VARCHAR(50),
    status_novo VARCHAR(50),
    valor DECIMAL(10,2),
    webhook_event VARCHAR(100),
    webhook_id VARCHAR(255) UNIQUE,
    payload JSON,
    processado_em DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (empresa_id) REFERENCES empresas(id) ON DELETE CASCADE,
    INDEX idx_empresa (empresa_id),
    INDEX idx_assinatura (assinatura_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE OR REPLACE VIEW v_relatorio_assinaturas AS
SELECT 
    e.id, e.nome_fantasia, e.email, e.plano, e.assinatura_id, e.assinatura_status,
    e.data_fim_trial, e.limite_os_mes, e.limite_tecnicos, e.limite_armazenamento_mb, e.created_at
FROM empresas e
ORDER BY e.updated_at DESC;
