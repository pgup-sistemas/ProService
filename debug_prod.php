<?php
/**
 * DEBUG PRODUÇÃO - Por que onboarding não aparece
 * Coloque este arquivo na raiz e acesse em: https://proservice.pageup.net.br/debug_prod.php
 */

session_start();

// Verifica se está autenticado
if (empty($_SESSION['empresa_id'])) {
    die('<h2>❌ Não autenticado</h2><p>Faça login primeiro e acesse esta página.</p>');
}

define('APP_URL', 'https://proservice.pageup.net.br');
define('ENVIRONMENT', 'production');

require 'app/config/Database.php';
require 'app/config/helpers.php';
require 'app/models/Model.php';
require 'app/models/Empresa.php';
require 'app/models/Servico.php';
require 'app/models/Cliente.php';
require 'app/models/OrdemServico.php';

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>🐛 Debug Onboarding</title>
    <style>
        body { font-family: monospace; background: #1e1e1e; color: #00ff00; padding: 20px; }
        .ok { color: #00ff00; } .error { color: #ff0000; } .warn { color: #ffff00; }
        pre { background: #000; padding: 10px; overflow-x: auto; }
        h2 { border-bottom: 2px solid #00ff00; padding-bottom: 5px; }
    </style>
</head>
<body>

<?php
try {
    $empresaId = $_SESSION['empresa_id'];
    $empresaModel = new App\Models\Empresa();
    
    echo "<h2>🔍 Debug Onboarding - Produção</h2>";
    echo "<p>Empresa ID: <strong>$empresaId</strong></p>";
    
    // 1. Verificar empresa
    echo "<h3>1️⃣ Dados da Empresa</h3>";
    $empresa = $empresaModel->findById($empresaId);
    
    if (!$empresa) {
        echo "<p class='error'>❌ Empresa não encontrada no banco!</p>";
        exit;
    }
    
    echo "<pre>";
    echo "Nome: {$empresa['nome_fantasia']}\n";
    echo "Criada em: {$empresa['created_at']}\n";
    echo "onboarding_completo: " . ($empresa['onboarding_completo'] ?? 'NULL - CAMPO NÃO EXISTE!') . "\n";
    echo "onboarding_etapa: " . ($empresa['onboarding_etapa'] ?? 'NULL - CAMPO NÃO EXISTE!') . "\n";
    echo "</pre>";
    
    // 2. Verificar regras
    echo "<h3>2️⃣ Regras de Exibição</h3>";
    echo "<pre>";
    
    // Regra 1
    $regraCompleto = empty($empresa['onboarding_completo']);
    echo ($regraCompleto ? "✅" : "❌") . " onboarding_completo vazio: " . ($regraCompleto ? 'SIM' : 'NÃO') . "\n";
    
    // Regra 2
    $dataCriacao = strtotime($empresa['created_at']);
    $diasDesdeCriacao = (time() - $dataCriacao) / 86400;
    $regraRecente = $diasDesdeCriacao <= 7;
    echo ($regraRecente ? "✅" : "❌") . " Criada há " . round($diasDesdeCriacao, 2) . " dias (<= 7)\n";
    
    // Resultado
    $mostrarOnboarding = $regraCompleto && $regraRecente;
    echo "\n";
    echo ($mostrarOnboarding ? "✅ " : "❌ ") . "<strong>RESULTADO: " . ($mostrarOnboarding ? "MOSTRAR ONBOARDING" : "NÃO MOSTRAR") . "</strong>\n";
    echo "</pre>";
    
    if (!$mostrarOnboarding) {
        echo "<h3>⚠️ Por que não aparece:</h3>";
        echo "<ul>";
        if (!$regraCompleto) {
            echo "<li><strong>onboarding_completo = 1</strong><br>";
            echo "👉 O onboarding foi finalizado. Para resetar: <code>UPDATE empresas SET onboarding_completo = 0 WHERE id = $empresaId</code></li>";
        }
        if (!$regraRecente) {
            echo "<li><strong>Criada há " . round($diasDesdeCriacao, 1) . " dias</strong><br>";
            echo "👉 Passou do período de 7 dias. Para resetar: <code>UPDATE empresas SET created_at = NOW() WHERE id = $empresaId</code></li>";
        }
        echo "</ul>";
        exit;
    }
    
    // 3. Progresso
    echo "<h3>3️⃣ Progresso do Onboarding</h3>";
    
    $servicoModel = new App\Models\Servico();
    $clienteModel = new App\Models\Cliente();
    $osModel = new App\Models\OrdemServico();
    
    $logo = !empty($empresa['logo']);
    $servico = $servicoModel->count(['empresa_id' => $empresaId]) > 0;
    $cliente = $clienteModel->count(['empresa_id' => $empresaId]) > 0;
    $os = $osModel->count(['empresa_id' => $empresaId]) > 0;
    
    echo "<pre>";
    echo ($logo ? "✅" : "❌") . " Logo enviada\n";
    echo ($servico ? "✅" : "❌") . " Serviço cadastrado\n";
    echo ($cliente ? "✅" : "❌") . " Cliente cadastrado\n";
    echo ($os ? "✅" : "❌") . " OS criada\n";
    
    $etapaCalculada = 1;
    if ($logo) $etapaCalculada = 2;
    if ($logo && $servico) $etapaCalculada = 3;
    if ($logo && $servico && $cliente) $etapaCalculada = 4;
    if ($logo && $servico && $cliente && $os) $etapaCalculada = 5;
    
    echo "\nEtapa: $etapaCalculada/5\n";
    echo "</pre>";
    
    echo "<h3>✅ Diagnóstico Completo!</h3>";
    echo "<p>O onboarding <strong>DEVE</strong> aparecer no dashboard.</p>";
    echo "<p>Se não aparece, verifique:</p>";
    echo "<ol>";
    echo "<li>Limpou o cache do navegador (Ctrl+Shift+Delete)?</li>";
    echo "<li>Está acessando a página correta (https://...)?</li>";
    echo "<li>Verificou o console do navegador (F12 &gt; Console) para erros?</li>";
    echo "</ol>";
    
} catch (Exception $e) {
    echo "<p class='error'>❌ Erro: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
}
?>

</body>
</html>
