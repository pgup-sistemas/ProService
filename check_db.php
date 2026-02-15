<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    $host = 'proservice.mysql.dbaas.com.br';
    $user = 'proservice';
    $pass = 'uHyR@2K9wL5xQ8mN';
    $db = 'proservice';
    
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "=== VERIFICANDO ESTRUTURA TABELA EMPRESAS ===\n\n";
    
    $result = $pdo->query('DESCRIBE empresas');
    $columns = $result->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Procurando por campos 'onboarding':\n";
    $found = false;
    foreach ($columns as $col) {
        if (stripos($col['Field'], 'onboarding') !== false) {
            echo "✓ {$col['Field']} ({$col['Type']}) - Null: {$col['Null']} - Default: {$col['Default']}\n";
            $found = true;
        }
    }
    
    if (!$found) {
        echo "✗ NENHUM campo 'onboarding' encontrado!\n\n";
        echo "Todos os campos na tabela empresas:\n";
        foreach ($columns as $col) {
            echo "  - {$col['Field']}\n";
        }
    }
    
    echo "\n=== VERIFICANDO EMPRESAS ===\n";
    try {
        $result = $pdo->query("SELECT id, nome_fantasia, created_at, onboarding_completo, onboarding_etapa FROM empresas LIMIT 1");
        $empresa = $result->fetch(PDO::FETCH_ASSOC);
        if ($empresa) {
            echo "ID: {$empresa['id']}\n";
            echo "Nome: {$empresa['nome_fantasia']}\n";
            echo "Criada: {$empresa['created_at']}\n";
            echo "onboarding_completo: " . ($empresa['onboarding_completo'] ?? 'NULL') . "\n";
            echo "onboarding_etapa: " . ($empresa['onboarding_etapa'] ?? 'NULL') . "\n";
        }
    } catch (Exception $e) {
        echo "Erro ao consultar onboarding: " . $e->getMessage() . "\n";
    }
    
} catch (PDOException $e) {
    echo "Erro de conexão: " . $e->getMessage();
    exit(1);
}
?>
