-- ============================================
-- MIGRAÇÃO: Adicionar novas funcionalidades ao proService
-- Data: 2024
-- Objetivo: Adicionar tabelas e colunas SEM perder dados
-- ============================================

-- --------------------------------------------
-- 1. ADICIONAR CAMPOS À TABELA DESPESAS (Recorrentes)
-- --------------------------------------------
ALTER TABLE despesas 
ADD COLUMN IF NOT EXISTS recorrente TINYINT(1) DEFAULT 0,
ADD COLUMN IF NOT EXISTS frequencia ENUM('mensal', 'semanal', 'anual') NULL,
ADD COLUMN IF NOT EXISTS despesa_pai_id INT NULL,
ADD COLUMN IF NOT EXISTS data_proximo_vencimento DATE NULL;

-- --------------------------------------------
-- 2. CRIAR TABELA PARCELAS (novo)
-- --------------------------------------------
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
    INDEX idx_receita (receita_id),
    INDEX idx_status (status),
    INDEX idx_vencimento (data_vencimento)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------
-- 3. CRIAR TABELA LOGS_SISTEMA (novo)
-- --------------------------------------------
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
    INDEX idx_empresa (empresa_id),
    INDEX idx_usuario (usuario_id),
    INDEX idx_modulo (modulo),
    INDEX idx_nivel (nivel),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------
-- 4. ADICIONAR CAMPO ONBOARDING À EMPRESA
-- --------------------------------------------
ALTER TABLE empresas 
ADD COLUMN IF NOT EXISTS onboarding_completo TINYINT(1) DEFAULT 0;

-- --------------------------------------------
-- 5. CRIAR TABELA ASSINATURAS (tipos: autorizacao, conformidade)
-- --------------------------------------------
CREATE TABLE IF NOT EXISTS assinaturas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    empresa_id INT NOT NULL,
    os_id INT NOT NULL,
    tipo ENUM('autorizacao', 'conformidade') NOT NULL DEFAULT 'conformidade',
    assinante_nome VARCHAR(255) NOT NULL,
    assinante_documento VARCHAR(20) NULL,
    arquivo VARCHAR(255) NOT NULL,
    ip_address VARCHAR(45) NULL,
    user_agent TEXT NULL,
    observacoes TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (empresa_id) REFERENCES empresas(id) ON DELETE CASCADE,
    FOREIGN KEY (os_id) REFERENCES ordens_servico(id) ON DELETE CASCADE,
    INDEX idx_os (os_id),
    INDEX idx_tipo (tipo),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------
-- 6. ADICIONAR COLUNA ONBOARDING_ETAPA (persistir progresso do wizard)
-- --------------------------------------------
ALTER TABLE empresas 
ADD COLUMN IF NOT EXISTS onboarding_etapa TINYINT UNSIGNED DEFAULT 1;

-- --------------------------------------------
-- 7. ADICIONAR COLUNAS ACEITE LGPD (evidência do consentimento)
-- --------------------------------------------
ALTER TABLE empresas ADD COLUMN IF NOT EXISTS aceite_termos_em DATETIME NULL;
ALTER TABLE empresas ADD COLUMN IF NOT EXISTS aceite_ip VARCHAR(45) NULL;
ALTER TABLE empresas ADD COLUMN IF NOT EXISTS aceite_user_agent VARCHAR(500) NULL;
ALTER TABLE empresas ADD COLUMN IF NOT EXISTS aceite_versao_termo VARCHAR(20) DEFAULT '1.0';

-- ============================================
-- MIGRAÇÃO CONCLUÍDA
-- ============================================
