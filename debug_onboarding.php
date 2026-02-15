<?php
/**
 * Debug Onboarding - Ver por que não está aparecendo
 */

// Simula uma requisição logada
$_SESSION['user_id'] = $_SESSION['usuario_id'] ?? 1;
$_SESSION['empresa_id'] = $_SESSION['empresa_id'] ?? 1;

require 'app/config/config.php';
require 'app/config/Database.php';
require 'app/config/helpers.php';
require 'app/models/Model.php';
require 'app/models/Empresa.php';

try {
    $empresaModel = new App\Models\Empresa();
    
    echo "=== DEBUG ONBOARDING ===\n\n";
    
    // Tenta pegar empresa
    $empresaId = 1;
    $empresa = $empresaModel->findById($empresaId);
    
    if (!$empresa) {
        echo "✗ Empresa não encontrada!\n";
        exit(1);
    }
    
    echo "Empresa: {$empresa['nome_fantasia']}\n";
    echo "ID: {$empresa['id']}\n";
    echo "Criada: {$empresa['created_at']}\n";
    
    // Verifica campos
    echo "\nCampos de Onboarding:\n";
    if (isset($empresa['onboarding_completo'])) {
        echo "  onboarding_completo: " . var_export($empresa['onboarding_completo'], true) . "\n";
    } else {
        echo "  ✗ Campo 'onboarding_completo' NÃO EXISTE\n";
    }
    
    if (isset($empresa['onboarding_etapa'])) {
        echo "  onboarding_etapa: " . var_export($empresa['onboarding_etapa'], true) . "\n";
    } else {
        echo "  ✗ Campo 'onboarding_etapa' NÃO EXISTE\n";
    }
    
    // Simula lógica de verificação
    echo "\n=== LÓGICA DE VERIFICAÇÃO ===\n";
    
    $dataCriacao = strtotime($empresa['created_at']);
    $agora = time();
    $diasDesdeCriacao = ($agora - $dataCriacao) / 86400;
    
    echo "Data de criação (timestamp): $dataCriacao\n";
    echo "Agora (timestamp): $agora\n";
    echo "Diferença em dias: " . round($diasDesdeCriacao, 2) . "\n";
    
    echo "\nVerificações:\n";
    echo "  empty(\$empresa['onboarding_completo']): " . (empty($empresa['onboarding_completo']) ? 'TRUE' : 'FALSE') . "\n";
    echo "  \$diasDesdeCriacao <= 7: " . ($diasDesdeCriacao <= 7 ? 'TRUE' : 'FALSE') . "\n";
    
    $mostrarOnboarding = empty($empresa['onboarding_completo']) && $diasDesdeCriacao <= 7;
    echo "\nMostrar onboarding: " . ($mostrarOnboarding ? '✓ SIM' : '✗ NÃO') . "\n";
    
} catch (Exception $e) {
    echo "Erro: " . $e->getMessage() . "\n";
    echo "Arquivo: " . $e->getFile() . ":" . $e->getLine() . "\n";
}
?>
