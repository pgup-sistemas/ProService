<?php
/**
 * Debug completo do fluxo de onboarding
 */

// Simula logged in
$_SESSION['user_id'] = 1;
$_SESSION['usuario_id'] = 1;
$_SESSION['empresa_id'] = 1;

define('APP_URL', 'https://proservice.pageup.net.br');
define('APP_NAME', 'ProService');

require 'app/config/Database.php';
require 'app/config/helpers.php';
require 'app/models/Model.php';
require 'app/models/Empresa.php';
require 'app/models/Servico.php';
require 'app/models/Cliente.php';
require 'app/models/OrdemServico.php';

echo "=== ANÁLISE COMPLETA DO ONBOARDING ===\n\n";

try {
    $empresaModel = new App\Models\Empresa();
    $servicoModel = new App\Models\Servico();
    $clienteModel = new App\Models\Cliente();
    $osModel = new App\Models\OrdemServico();
    
    $empresaId = 1;
    $empresa = $empresaModel->findById($empresaId);
    
    if (!$empresa) {
        echo "✗ Empresa não encontrada\n";
        exit(1);
    }
    
    echo "1. DADOS DA EMPRESA:\n";
    echo "   Nome: {$empresa['nome_fantasia']}\n";
    echo "   Criada: {$empresa['created_at']}\n";
    echo "   onboarding_completo: " . ($empresa['onboarding_completo'] ?? 'NULL') . "\n";
    echo "   onboarding_etapa: " . ($empresa['onboarding_etapa'] ?? 'NULL') . "\n\n";
    
    echo "2. VERIFICAÇÃO DO ONBOARDING:\n";
    
    // Regra 1: onboarding_completo
    $regraCompleto = empty($empresa['onboarding_completo']);
    echo "   ✓ onboarding_completo está vazio: " . ($regraCompleto ? 'SIM' : 'NÃO') . "\n";
    
    // Regra 2: criação recente
    $dataCriacao = strtotime($empresa['created_at']);
    $diasDesdeCriacao = (time() - $dataCriacao) / 86400;
    $regraRecente = $diasDesdeCriacao <= 7;
    echo "   ✓ Criada há " . round($diasDesdeCriacao, 2) . " dias (<= 7): " . ($regraRecente ? 'SIM' : 'NÃO') . "\n";
    
    $mostrarOnboarding = $regraCompleto && $regraRecente;
    echo "   → Mostrar onboarding: " . ($mostrarOnboarding ? 'SIM ✓' : 'NÃO ✗') . "\n\n";
    
    if (!$mostrarOnboarding) {
        echo "⚠️ PROBLEMA: Onboarding NÃO deve aparecer com essas configurações!\n";
        exit(0);
    }
    
    echo "3. PROGRESSO DO ONBOARDING:\n";
    
    $logo = !empty($empresa['logo']);
    $servico = $servicoModel->count(['empresa_id' => $empresaId]) > 0;
    $cliente = $clienteModel->count(['empresa_id' => $empresaId]) > 0;
    $os = $osModel->count(['empresa_id' => $empresaId]) > 0;
    
    echo "   Logo: " . ($logo ? 'SIM' : 'NÃO') . "\n";
    echo "   Serviço: " . ($servico ? 'SIM' : 'NÃO') . "\n";
    echo "   Cliente: " . ($cliente ? 'SIM' : 'NÃO') . "\n";
    echo "   OS: " . ($os ? 'SIM' : 'NÃO') . "\n";
    
    $etapaCalculada = 1;
    if ($logo) $etapaCalculada = 2;
    if ($logo && $servico) $etapaCalculada = 3;
    if ($logo && $servico && $cliente) $etapaCalculada = 4;
    if ($logo && $servico && $cliente && $os) $etapaCalculada = 5;
    
    $etapaSalva = (int) ($empresa['onboarding_etapa'] ?? 1);
    $etapa = max($etapaCalculada, $etapaSalva);
    
    echo "   Etapa calculada: $etapaCalculada\n";
    echo "   Etapa salva: $etapaSalva\n";
    echo "   Etapa final: $etapa\n\n";
    
    echo "4. VARIÁVEIS PARA A VIEW:\n";
    echo "   \$mostrarOnboarding: " . ($mostrarOnboarding ? 'true' : 'false') . "\n";
    echo "   \$progressoOnboarding: " . json_encode([
        'logo' => $logo,
        'servico' => $servico,
        'cliente' => $cliente,
        'os' => $os,
        'etapa_atual' => $etapa,
        'completo' => $logo && $servico && $cliente && $os
    ]) . "\n\n";
    
    echo "✓ TUDO CORRETO! O onboarding DEVE aparecer!\n";
    
} catch (Exception $e) {
    echo "✗ Erro: " . $e->getMessage() . "\n";
    echo "  " . $e->getFile() . ":" . $e->getLine() . "\n";
}
?>
