<?php
/**
 * Script para verificar campos de onboarding no banco
 */

require 'app/config/Database.php';
require 'app/config/config.php';

$db = new App\Config\Database();
$pdo = $db->getConnection();

echo "=== VERIFICANDO ESTRUTURA TABELA EMPRESAS ===\n";
echo "Procurando por campos 'onboarding':\n\n";

$result = $pdo->query('DESCRIBE empresas');
$columns = $result->fetchAll(PDO::FETCH_ASSOC);

$found = false;
foreach ($columns as $col) {
    if (stripos($col['Field'], 'onboarding') !== false) {
        echo "✓ Campo encontrado: {$col['Field']}\n";
        echo "  Tipo: {$col['Type']}\n";
        echo "  Null: {$col['Null']}\n";
        echo "  Default: {$col['Default']}\n\n";
        $found = true;
    }
}

if (!$found) {
    echo "✗ NENHUM campo 'onboarding' encontrado!\n";
    echo "\nCampos na tabela:\n";
    foreach ($columns as $col) {
        echo "  - {$col['Field']}\n";
    }
}

echo "\n=== VERIFICANDO EMPRESAS EXISTENTES ===\n";
$result = $pdo->query("SELECT id, nome_fantasia, created_at, onboarding_completo, onboarding_etapa FROM empresas LIMIT 3");
$empresas = $result->fetchAll(PDO::FETCH_ASSOC);

foreach ($empresas as $emp) {
    echo "\nEmpresa ID {$emp['id']}: {$emp['nome_fantasia']}\n";
    echo "  Criada: {$emp['created_at']}\n";
    echo "  onboarding_completo: " . ($emp['onboarding_completo'] ?? 'NULL') . "\n";
    echo "  onboarding_etapa: " . ($emp['onboarding_etapa'] ?? 'NULL') . "\n";
}

echo "\n";
?>
