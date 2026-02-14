-- ============================================
-- proService SaaS - Script SQL Inicial
-- ============================================
-- Stack: PHP 8+, MySQL 8+, Bootstrap 5, PDO
-- Modelo: Multiempresa com isolamento por empresa_id
-- ============================================

-- Criar banco de dados
CREATE DATABASE IF NOT EXISTS proservice CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE proservice;

-- ============================================
-- 1. TABELA: empresas
-- ============================================
CREATE TABLE empresas (
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
    -- Configurações de plano
    plano ENUM('trial', 'basico', 'profissional') DEFAULT 'trial',
    data_inicio_plano DATE,
    data_fim_trial DATE,
    limite_os_mes INT DEFAULT 20,
    limite_tecnicos INT DEFAULT 1,
    limite_armazenamento_mb INT DEFAULT 100,
    os_criadas_mes_atual INT DEFAULT 0,
    mes_referencia_os VARCHAR(7), -- Formato: YYYY-MM
    status ENUM('ativo', 'bloqueado', 'cancelado') DEFAULT 'ativo',
    -- Dados bancários (para recibos)
    banco_nome VARCHAR(100),
    banco_agencia VARCHAR(20),
    banco_conta VARCHAR(20),
    banco_tipo ENUM('corrente', 'poupanca'),
    chave_pix VARCHAR(100),
    -- Configurações
    termos_contrato TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_plano (plano),
    INDEX idx_status (status),
    INDEX idx_cnpj (cnpj_cpf)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 2. TABELA: usuarios
-- ============================================
CREATE TABLE usuarios (
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
    -- Recuperação de senha
    reset_token VARCHAR(64) DEFAULT NULL,
    reset_token_expira DATETIME DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (empresa_id) REFERENCES empresas(id) ON DELETE CASCADE,
    UNIQUE KEY uk_empresa_email (empresa_id, email),
    INDEX idx_empresa_perfil (empresa_id, perfil),
    INDEX idx_ativo (ativo),
    INDEX idx_reset_token (reset_token)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 3. TABELA: clientes
-- ============================================
CREATE TABLE clientes (
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
    INDEX idx_empresa_telefone (empresa_id, telefone),
    INDEX idx_empresa_cpf (empresa_id, cpf_cnpj),
    INDEX idx_ativo (ativo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 4. TABELA: servicos
-- ============================================
CREATE TABLE servicos (
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
    INDEX idx_empresa_categoria (empresa_id, categoria),
    INDEX idx_ativo (ativo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 5. TABELA: produtos
-- ============================================
CREATE TABLE produtos (
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
    INDEX idx_empresa_codigo (empresa_id, codigo_sku),
    INDEX idx_empresa_categoria (empresa_id, categoria),
    INDEX idx_ativo (ativo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 6. TABELA: ordens_servico
-- ============================================
CREATE TABLE ordens_servico (
    id INT AUTO_INCREMENT PRIMARY KEY,
    empresa_id INT NOT NULL,
    numero_os INT NOT NULL,
    cliente_id INT NOT NULL,
    tecnico_id INT,
    servico_id INT,
    descricao TEXT,
    prioridade ENUM('urgente', 'alta', 'normal', 'baixa') DEFAULT 'normal',
    data_entrada DATE DEFAULT CURRENT_DATE,
    previsao_entrega DATE,
    data_finalizacao DATE,
    -- Valores
    valor_servico DECIMAL(10, 2) DEFAULT 0,
    taxas_adicionais DECIMAL(10, 2) DEFAULT 0,
    desconto DECIMAL(10, 2) DEFAULT 0,
    valor_total DECIMAL(10, 2) DEFAULT 0,
    custo_produtos DECIMAL(10, 2) DEFAULT 0,
    lucro_real DECIMAL(10, 2) DEFAULT 0,
    forma_pagamento_acordada ENUM('dinheiro', 'pix', 'cartao_credito', 'cartao_debito', 'boleto', 'transferencia'),
    -- Status
    status ENUM('aberta', 'em_orcamento', 'aprovada', 'em_execucao', 'pausada', 'finalizada', 'paga', 'cancelada') DEFAULT 'aberta',
    -- Garantia e observações
    garantia_dias INT DEFAULT 0,
    observacoes_internas TEXT,
    observacoes_cliente TEXT,
    -- Link público
    token_publico VARCHAR(64) UNIQUE,
    -- Assinaturas
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
    INDEX idx_empresa_status (empresa_id, status),
    INDEX idx_empresa_cliente (empresa_id, cliente_id),
    INDEX idx_empresa_tecnico (empresa_id, tecnico_id),
    INDEX idx_token_publico (token_publico),
    INDEX idx_data_entrada (data_entrada)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 7. TABELA: os_produtos (produtos usados na OS)
-- ============================================
CREATE TABLE os_produtos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    os_id INT NOT NULL,
    produto_id INT NOT NULL,
    quantidade DECIMAL(10, 2) NOT NULL,
    custo_unitario DECIMAL(10, 2) NOT NULL,
    custo_total DECIMAL(10, 2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (os_id) REFERENCES ordens_servico(id) ON DELETE CASCADE,
    FOREIGN KEY (produto_id) REFERENCES produtos(id),
    INDEX idx_os (os_id),
    INDEX idx_produto (produto_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 8. TABELA: os_fotos
-- ============================================
CREATE TABLE os_fotos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    os_id INT NOT NULL,
    arquivo VARCHAR(255) NOT NULL,
    tipo ENUM('antes', 'durante', 'depois') DEFAULT 'antes',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (os_id) REFERENCES ordens_servico(id) ON DELETE CASCADE,
    INDEX idx_os (os_id),
    INDEX idx_tipo (tipo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 9. TABELA: os_logs (histórico de alterações)
-- ============================================
CREATE TABLE os_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    os_id INT NOT NULL,
    usuario_id INT,
    acao VARCHAR(255) NOT NULL,
    dados_anteriores JSON,
    dados_novos JSON,
    ip_address VARCHAR(45),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (os_id) REFERENCES ordens_servico(id) ON DELETE CASCADE,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id),
    INDEX idx_os (os_id),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 10. TABELA: receitas
-- ============================================
CREATE TABLE receitas (
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
    INDEX idx_empresa_status (empresa_id, status),
    INDEX idx_empresa_data (empresa_id, data_recebimento),
    INDEX idx_os (os_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 11. TABELA: despesas
-- ============================================
CREATE TABLE despesas (
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
    -- Campos para despesas recorrentes
    recorrente TINYINT(1) DEFAULT 0,
    frequencia ENUM('mensal', 'semanal', 'anual') NULL,
    despesa_pai_id INT NULL,
    data_proximo_vencimento DATE NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (empresa_id) REFERENCES empresas(id) ON DELETE CASCADE,
    FOREIGN KEY (despesa_pai_id) REFERENCES despesas(id) ON DELETE SET NULL,
    INDEX idx_empresa_categoria (empresa_id, categoria),
    INDEX idx_empresa_data (empresa_id, data_despesa),
    INDEX idx_status (status),
    INDEX idx_recorrente (recorrente),
    INDEX idx_proximo_vencimento (data_proximo_vencimento)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 12. TABELA: movimentacao_estoque
-- ============================================
CREATE TABLE movimentacao_estoque (
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
    INDEX idx_empresa_produto (empresa_id, produto_id),
    INDEX idx_tipo (tipo),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 13. TABELA: recibos
-- ============================================
CREATE TABLE recibos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    empresa_id INT NOT NULL,
    numero_recibo INT NOT NULL,
    os_id INT NOT NULL,
    cliente_id INT NOT NULL,
    valor DECIMAL(10, 2) NOT NULL,
    valor_extenso VARCHAR(255),
    data_emissao DATE DEFAULT CURRENT_DATE,
    forma_pagamento ENUM('dinheiro', 'pix', 'cartao_credito', 'cartao_debito', 'boleto', 'transferencia'),
    observacoes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (empresa_id) REFERENCES empresas(id) ON DELETE CASCADE,
    FOREIGN KEY (os_id) REFERENCES ordens_servico(id),
    FOREIGN KEY (cliente_id) REFERENCES clientes(id),
    UNIQUE KEY uk_empresa_numero_recibo (empresa_id, numero_recibo),
    INDEX idx_os (os_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 14. TABELA: configuracoes_empresa
-- ============================================
CREATE TABLE configuracoes_empresa (
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

-- ============================================
-- 15. TABELA: csrf_tokens
-- ============================================
CREATE TABLE csrf_tokens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    token VARCHAR(64) NOT NULL,
    session_id VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expires_at TIMESTAMP NOT NULL,
    used TINYINT(1) DEFAULT 0,
    INDEX idx_token (token),
    INDEX idx_session (session_id),
    INDEX idx_expires (expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 16. TABELA: os_logs (Histórico de Alterações da OS)
-- ============================================
CREATE TABLE os_logs (
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
    INDEX idx_os_id (os_id),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- FUNÇÕES E TRIGGERS
-- ============================================

-- Trigger para gerar número de OS sequencial por empresa
DELIMITER //

CREATE TRIGGER trg_gerar_numero_os BEFORE INSERT ON ordens_servico
FOR EACH ROW
BEGIN
    DECLARE max_numero INT;
    
    IF NEW.numero_os IS NULL THEN
        SELECT COALESCE(MAX(numero_os), 0) + 1 INTO max_numero
        FROM ordens_servico
        WHERE empresa_id = NEW.empresa_id;
        
        SET NEW.numero_os = max_numero;
    END IF;
    
    -- Gerar token público único
    IF NEW.token_publico IS NULL THEN
        SET NEW.token_publico = MD5(CONCAT(NEW.empresa_id, '-', UNIX_TIMESTAMP(), '-', RAND()));
    END IF;
END//

-- Trigger para baixa automática no estoque
CREATE TRIGGER trg_baixa_estoque AFTER INSERT ON os_produtos
FOR EACH ROW
BEGIN
    -- Atualizar quantidade em estoque
    UPDATE produtos
    SET quantidade_estoque = quantidade_estoque - NEW.quantidade,
        updated_at = NOW()
    WHERE id = NEW.produto_id;
    
    -- Registrar movimentação
    INSERT INTO movimentacao_estoque (empresa_id, produto_id, tipo, quantidade, motivo, os_id)
    SELECT p.empresa_id, NEW.produto_id, 'saida', NEW.quantidade, 'Saída via OS', NEW.os_id
    FROM produtos p
    WHERE p.id = NEW.produto_id;
END//

-- Trigger para estorno no estoque ao remover produto da OS
CREATE TRIGGER trg_estorno_estoque AFTER DELETE ON os_produtos
FOR EACH ROW
BEGIN
    -- Atualizar quantidade em estoque
    UPDATE produtos
    SET quantidade_estoque = quantidade_estoque + OLD.quantidade,
        updated_at = NOW()
    WHERE id = OLD.produto_id;
    
    -- Registrar movimentação
    INSERT INTO movimentacao_estoque (empresa_id, produto_id, tipo, quantidade, motivo, os_id)
    SELECT p.empresa_id, OLD.produto_id, 'entrada', OLD.quantidade, 'Estorno - remoção da OS', OLD.os_id
    FROM produtos p
    WHERE p.id = OLD.produto_id;
END//

-- Trigger para gerar receita automaticamente ao criar OS
CREATE TRIGGER trg_gerar_receita_os AFTER INSERT ON ordens_servico
FOR EACH ROW
BEGIN
    IF NEW.valor_total > 0 THEN
        INSERT INTO receitas (empresa_id, os_id, descricao, valor, forma_pagamento, status, created_at)
        VALUES (NEW.empresa_id, NEW.id, CONCAT('OS #', NEW.numero_os), NEW.valor_total, 'nao_informado', 'pendente', NOW());
    END IF;
END//

-- Trigger para gerar número de recibo sequencial
CREATE TRIGGER trg_gerar_numero_recibo BEFORE INSERT ON recibos
FOR EACH ROW
BEGIN
    DECLARE max_numero INT;
    
    IF NEW.numero_recibo IS NULL THEN
        SELECT COALESCE(MAX(numero_recibo), 0) + 1 INTO max_numero
        FROM recibos
        WHERE empresa_id = NEW.empresa_id;
        
        SET NEW.numero_recibo = max_numero;
    END IF;
END//

-- Trigger para atualizar contador de OS mensal da empresa
CREATE TRIGGER trg_atualizar_contador_os AFTER INSERT ON ordens_servico
FOR EACH ROW
BEGIN
    DECLARE mes_atual VARCHAR(7);
    SET mes_atual = DATE_FORMAT(NEW.created_at, '%Y-%m');
    
    -- Atualiza o contador de OS do mês atual na empresa
    UPDATE empresas
    SET os_criadas_mes_atual = (
        SELECT COUNT(*) 
        FROM ordens_servico 
        WHERE empresa_id = NEW.empresa_id 
        AND DATE_FORMAT(created_at, '%Y-%m') = mes_atual
    )
    WHERE id = NEW.empresa_id;
END//

-- Trigger para resetar contador mensal (executa no primeiro dia do mês)
-- Nota: Este trigger é simbólico, o reset real deve ser feito por cron job
CREATE TRIGGER trg_verificar_reset_contador BEFORE INSERT ON ordens_servico
FOR EACH ROW
BEGIN
    DECLARE ultimo_mes VARCHAR(7);
    DECLARE mes_atual VARCHAR(7);
    
    SET mes_atual = DATE_FORMAT(NOW(), '%Y-%m');
    
    -- Verifica se há OS do mês anterior e reseta o contador se necessário
    SELECT DATE_FORMAT(MAX(created_at), '%Y-%m') INTO ultimo_mes
    FROM ordens_servico
    WHERE empresa_id = NEW.empresa_id;
    
    IF ultimo_mes IS NOT NULL AND ultimo_mes != mes_atual THEN
        -- Reset do contador (primeira OS do novo mês)
        UPDATE empresas
        SET os_criadas_mes_atual = 0
        WHERE id = NEW.empresa_id;
    END IF;
END//

DELIMITER ;

-- ============================================
-- DADOS INICIAIS DE EXEMPLO
-- ============================================

-- Inserir uma empresa de teste (será criada via registro na prática)
-- INSERT INTO empresas (nome_fantasia, razao_social, cnpj_cpf, email, telefone, plano, data_fim_trial)
-- VALUES ('Empresa Teste', 'Empresa Teste LTDA', '00.000.000/0000-00', 'teste@empresa.com', '(11) 99999-9999', 'trial', DATE_ADD(CURDATE(), INTERVAL 15 DAY));

-- ============================================
-- 17. TABELA: comunicacoes (Histórico de comunicações)
-- ============================================
CREATE TABLE comunicacoes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    empresa_id INT NOT NULL,
    os_id INT NOT NULL,
    cliente_id INT NOT NULL,
    tipo ENUM('whatsapp', 'email') DEFAULT 'whatsapp',
    template_usado VARCHAR(100) DEFAULT NULL,
    mensagem_enviada TEXT NOT NULL,
    status ENUM('enviado', 'erro') DEFAULT 'enviado',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (os_id) REFERENCES ordens_servico(id) ON DELETE CASCADE,
    FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE CASCADE,
    FOREIGN KEY (empresa_id) REFERENCES empresas(id) ON DELETE CASCADE,
    INDEX idx_os_id (os_id),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 18. TABELA: parcelas (Controle de parcelas)
-- ============================================
CREATE TABLE parcelas (
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
    INDEX idx_receita (receita_id),
    INDEX idx_status (status),
    INDEX idx_vencimento (data_vencimento)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 20. TABELA: email_logs (Logs de envio de e-mails)
-- ============================================
CREATE TABLE email_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    empresa_id INT NOT NULL,
    para VARCHAR(255) NOT NULL,
    assunto VARCHAR(255) NOT NULL,
    status ENUM('success', 'failed', 'pending') DEFAULT 'pending',
    erro TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (empresa_id) REFERENCES empresas(id) ON DELETE CASCADE,
    INDEX idx_empresa (empresa_id),
    INDEX idx_status (status),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 19. TABELA: logs_sistema (Logs de ações críticas)
-- ============================================
CREATE TABLE logs_sistema (
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
    INDEX idx_empresa (empresa_id),
    INDEX idx_usuario (usuario_id),
    INDEX idx_modulo (modulo),
    INDEX idx_nivel (nivel),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
==
