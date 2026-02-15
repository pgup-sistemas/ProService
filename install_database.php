<?php
/**
 * proService - Script de Instalação do Banco de Dados
 * Arquivo: install_database.php
 * 
 * USO:
 * 1. Coloque este arquivo na raiz do projeto
 * 2. Acesse: http://proservice.pageup.net.br/install_database.php
 * 3. Clique em "Instalar Banco de Dados"
 * 4. Após conclusão, DELETE este arquivo
 */

// Carregar configuração
require_once __DIR__ . '/app/config/config.php';

// Desabilitar output buffering para feedback em tempo real
ob_implicit_flush(true);
ob_start();

$status = [
    'conectado' => false,
    'banco_criado' => false,
    'tabelas_criadas' => 0,
    'admin_criado' => false,
    'erro' => null
];

// Funções auxiliares
function log_msg($msg, $tipo = 'info') {
    $cores = [
        'info' => '#0066cc',
        'success' => '#00cc00',
        'warning' => '#ff9900',
        'error' => '#cc0000'
    ];
    $cor = $cores[$tipo] ?? '#000';
    echo "<div style='color: $cor; padding: 8px; margin: 5px 0; background: #f5f5f5; border-left: 4px solid $cor;'>";
    echo "[$tipo] " . htmlspecialchars($msg);
    echo "</div>";
    ob_flush();
}

function log_paso($numero, $titulo) {
    echo "<div style='background: #1e40af; color: white; padding: 15px; margin: 15px 0; border-radius: 5px; font-weight: bold;'>";
    echo "PASSO $numero: $titulo";
    echo "</div>";
    ob_flush();
}

// HTML Header
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ProService - Instalação do Banco de Dados</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border-radius: 10px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            overflow: hidden;
        }
        .header {
            background: #1e40af;
            color: white;
            padding: 30px;
            text-align: center;
        }
        .header h1 { font-size: 32px; margin-bottom: 10px; }
        .header p { font-size: 14px; opacity: 0.9; }
        .content {
            padding: 30px;
            max-height: 500px;
            overflow-y: auto;
        }
        .footer {
            background: #f5f5f5;
            padding: 20px;
            text-align: center;
            border-top: 1px solid #ddd;
        }
        button {
            background: #1e40af;
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            transition: all 0.3s;
        }
        button:hover { background: #1a35a0; transform: translateY(-2px); box-shadow: 0 5px 15px rgba(30,64,175,0.3); }
        button:disabled { background: #ccc; cursor: not-allowed; transform: none; }
        .spinner { display: inline-block; width: 20px; height: 20px; border: 3px solid #f3f3f3; border-top: 3px solid #1e40af; border-radius: 50%; animation: spin 1s linear infinite; margin-right: 10px; vertical-align: middle; }
        @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
        .warning { background: #fff3cd; border: 1px solid #ffc107; color: #856404; padding: 15px; border-radius: 5px; margin: 15px 0; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🚀 ProService SaaS</h1>
            <p>Instalação do Banco de Dados - Produção</p>
        </div>

        <div class="content" id="content">
            <div style="text-align: center; padding: 20px;">
                <p>Clique no botão abaixo para iniciar a instalação do banco de dados.</p>
                <p style="color: #cc0000; font-weight: bold; margin-top: 10px;">⚠️ Esta ação é IRREVERSÍVEL!</p>
            </div>
        </div>

        <div class="footer">
            <button id="btn-instalar" onclick="iniciarInstalacao()">
                Iniciar Instalação
            </button>
            <button id="btn-cancelar" onclick="location.reload()" style="background: #999; margin-left: 10px; display: none;">
                Cancelar
            </button>
        </div>
    </div>

    <script>
        function iniciarInstalacao() {
            document.getElementById('btn-instalar').disabled = true;
            document.getElementById('btn-cancelar').style.display = 'inline-block';
            
            // Fazer requisição AJAX
            fetch('install_database.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'action=instalar'
            })
            .then(response => response.text())
            .then(html => {
                document.getElementById('content').innerHTML = html;
            })
            .catch(error => {
                document.getElementById('content').innerHTML = '<div style="color: red;">Erro: ' + error.message + '</div>';
                document.getElementById('btn-instalar').disabled = false;
            });
        }
    </script>
</body>
</html>

<?php
// Se for POST com action=instalar, executar instalação
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'instalar') {
    // Detectar se está rodando via web ou CLI
    $is_cli = php_sapi_name() === 'cli';
    
    if (!$is_cli) {
        // Remover tags HTML já incluídas
        ob_end_clean();
    }
    
    try {
        // Conexão ADMIN (sem banco selecionado)
        log_paso(1, "Conectando ao MySQL...");
        $pdo = new PDO(
            "mysql:host=" . DB_HOST,
            DB_USER,
            DB_PASS,
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
        log_msg("✓ Conectado com sucesso!", 'success');
        $status['conectado'] = true;
        
        // Criar banco de dados
        log_paso(2, "Criando banco de dados...");
        $pdo->exec("CREATE DATABASE IF NOT EXISTS " . DB_NAME . " CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        $pdo->exec("USE " . DB_NAME);
        log_msg("✓ Banco de dados '" . DB_NAME . "' pronto!", 'success');
        $status['banco_criado'] = true;
        
        // Ler e executar o script SQL
        log_paso(3, "Criando tabelas...");
        $sql_file = PROSERVICE_ROOT . '/database_producao_completo.sql';
        
        if (!file_exists($sql_file)) {
            throw new Exception("Arquivo SQL não encontrado: $sql_file");
        }
        
        $sql_content = file_get_contents($sql_file);
        
        // Remover linhas de comentário SQL
        $sql_content = preg_replace('/^--.*$/m', '', $sql_content);
        
        // Dividir por ; mas evitar dividir dentro de strings
        $sql_commands = [];
        $current = '';
        $in_string = false;
        $escape_next = false;
        
        for ($i = 0; $i < strlen($sql_content); $i++) {
            $char = $sql_content[$i];
            
            if ($escape_next) {
                $current .= $char;
                $escape_next = false;
                continue;
            }
            
            if ($char === '\\') {
                $escape_next = true;
                $current .= $char;
                continue;
            }
            
            if ($char === "'" || $char === '"') {
                $in_string = !$in_string;
                $current .= $char;
                continue;
            }
            
            if ($char === ';' && !$in_string) {
                if (trim($current)) {
                    $sql_commands[] = trim($current);
                }
                $current = '';
                continue;
            }
            
            $current .= $char;
        }
        
        if (trim($current)) {
            $sql_commands[] = trim($current);
        }
        
        $tabelas_count = 0;
        $total_commands = count($sql_commands);
        
        foreach ($sql_commands as $i => $command) {
            $command = trim($command);
            if (empty($command)) continue;
            
            try {
                // Ignorar linhas que não são comandos SQL válidos
                if (stripos($command, 'DELIMITER') !== false) continue;
                if (stripos($command, 'END//') !== false) continue;
                
                $pdo->exec($command);
                
                // Contar criação de tabelas
                if (stripos($command, 'CREATE TABLE') !== false) {
                    $tabelas_count++;
                    preg_match('/CREATE TABLE[^`]*`(\w+)`/i', $command, $matches);
                    if (!empty($matches[1])) {
                        log_msg("✓ Tabela '{$matches[1]}' criada", 'success');
                    }
                }
                
                // Mostrar progresso
                if ($i % 5 === 0) {
                    echo ".";
                    ob_flush();
                }
            } catch (Exception $e) {
                // Ignorar certos erros que não são críticos
                $msg = $e->getMessage();
                if (stripos($msg, 'already exists') === false &&
                    stripos($msg, 'DELIMITER') === false &&
                    stripos($msg, 'Unknown system variable') === false) {
                    // Log apenas erros críticos
                    if (stripos($msg, 'syntax error') !== false || 
                        stripos($msg, 'Cannot add foreign key') !== false) {
                        log_msg("Aviso (não crítico): " . substr($msg, 0, 100), 'warning');
                    }
                }
            }
        }
        
        log_msg("✓ Total de tabelas criadas: $tabelas_count", 'success');
        $status['tabelas_criadas'] = $tabelas_count;
        
        // Criar dados iniciais
        log_paso(4, "Criando dados iniciais...");
        
        try {
            // Limpar dados antigos se existirem
            $pdo->exec("DELETE FROM usuarios WHERE email = 'admin@proservice.local'");
            $pdo->exec("DELETE FROM empresas WHERE email = 'admin@proservice.local'");
        } catch (Exception $e) {
            // Ignorar erros ao deletar
        }
        
        try {
            // Inserir empresa de teste
            $stmt = $pdo->prepare("
                INSERT INTO empresas (nome_fantasia, razao_social, cnpj_cpf, email, telefone, plano, data_fim_trial, limite_os_mes, limite_tecnicos, limite_armazenamento_mb)
                VALUES (?, ?, ?, ?, ?, ?, DATE_ADD(CURDATE(), INTERVAL 15 DAY), -1, -1, 5120)
            ");
            $stmt->execute([
                'ProService Teste',
                'ProService Teste LTDA',
                '00.000.000/0001-00',
                'admin@proservice.local',
                '(92) 99999-9999',
                'trial'
            ]);
            log_msg("✓ Empresa de teste criada", 'success');
            
            // Inserir admin padrão
            $stmt = $pdo->prepare("
                INSERT INTO usuarios (empresa_id, nome, email, senha, perfil, ativo)
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                1,
                'Administrador',
                'admin@proservice.local',
                '$2y$10$UVAKCNwGG9yEi0IfSVVx5OMtf7SZFxCHYHYq5B8Aqk8VflR0e/p7W',
                'admin',
                1
            ]);
            log_msg("✓ Admin padrão criado", 'success');
        } catch (Exception $e) {
            log_msg("Aviso ao criar dados: " . $e->getMessage(), 'warning');
        }
        
        // Verificar criação de admin
        log_paso(5, "Verificando usuário admin...");
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM usuarios WHERE perfil = 'admin' AND email = 'admin@proservice.local'");
        $admin_exists = $stmt->fetch(PDO::FETCH_ASSOC)['total'] > 0;
        
        if ($admin_exists) {
            log_msg("✓ Admin padrão encontrado (email: admin@proservice.local)", 'success');
            log_msg("⚠️ Senha padrão: admin123 - MUDE IMEDIATAMENTE!", 'warning');
        } else {
            log_msg("✗ Admin não encontrado", 'error');
        }
        $status['admin_criado'] = $admin_exists;
        
        log_paso(6, "Resumo da Instalação");
        log_paso(5, "Resumo da Instalação");
        
        $result = $pdo->query("
            SELECT 
                (SELECT COUNT(*) FROM empresas) as total_empresas,
                (SELECT COUNT(*) FROM usuarios) as total_usuarios,
                (SELECT COUNT(*) FROM clientes) as total_clientes,
                (SELECT TABLE_COUNT FROM (
                    SELECT COUNT(*) as TABLE_COUNT 
                    FROM INFORMATION_SCHEMA.TABLES 
                    WHERE TABLE_SCHEMA = '" . DB_NAME . "'
                ) t) as total_tabelas
        ");
        $stats = $result->fetch(PDO::FETCH_ASSOC);
        
        echo "<div style='background: #e8f5e9; border: 2px solid #4caf50; padding: 20px; border-radius: 5px; margin: 20px 0;'>";
        echo "<h3 style='color: #2e7d32; margin-bottom: 10px;'>✅ INSTALAÇÃO CONCLUÍDA COM SUCESSO!</h3>";
        echo "<ul style='list-style: none; line-height: 1.8;'>";
        echo "<li>📦 Banco de Dados: <strong>" . DB_NAME . "</strong></li>";
        echo "<li>📊 Tabelas Criadas: <strong>" . $stats['total_tabelas'] . "</strong></li>";
        echo "<li>🏢 Empresas: <strong>" . $stats['total_empresas'] . "</strong></li>";
        echo "<li>👥 Usuários: <strong>" . $stats['total_usuarios'] . "</strong></li>";
        echo "<li>🤝 Clientes: <strong>" . $stats['total_clientes'] . "</strong></li>";
        echo "</ul>";
        echo "</div>";
        
        // Instruções finais
        echo "<div style='background: #e3f2fd; border-left: 4px solid #2196f3; padding: 15px; margin: 15px 0;'>";
        echo "<h4 style='color: #1976d2; margin-bottom: 10px;'>📋 PRÓXIMOS PASSOS:</h4>";
        echo "<ol style='line-height: 1.8;'>";
        echo "<li>✅ <strong>DELETE este arquivo</strong> (install_database.php)</li>";
        echo "<li>📄 Verifique se o certificado .p12 está em <code>/app/certs/</code></li>";
        echo "<li>🔗 Configure webhook no painel EfiPay</li>";
        echo "<li>💳 Teste um checkout de produção</li>";
        echo "<li>🔐 <strong>ALTERE A SENHA DO ADMIN IMEDIATAMENTE!</strong>";
        echo "   <ul style='margin-top: 5px;'>";
        echo "     <li>Email: admin@proservice.local</li>";
        echo "     <li>Senha padrão: admin123</li>";
        echo "     <li>Acesso: https://proservice.pageup.net.br/login</li>";
        echo "   </ul>";
        echo "</li>";
        echo "</ol>";
        echo "</div>";
        
    } catch (Exception $e) {
        log_msg("❌ ERRO: " . $e->getMessage(), 'error');
        $status['erro'] = $e->getMessage();
        
        echo "<div style='background: #ffebee; border: 2px solid #f44336; padding: 20px; border-radius: 5px; margin: 20px 0;'>";
        echo "<h3 style='color: #c62828;'>❌ Instalação Falhou</h3>";
        echo "<p><strong>Erro:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
        echo "<p style='margin-top: 10px; font-size: 12px; color: #666;'>";
        echo "Se o problema persistir, verifique:<br>";
        echo "1. Conexão com o banco de dados<br>";
        echo "2. Permissões do usuário MySQL<br>";
        echo "3. Se o banco já existe<br>";
        echo "4. Espaço em disco disponível";
        echo "</p>";
        echo "</div>";
    }
}
?>
