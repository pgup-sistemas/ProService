<?php
/**
 * proService - Funções Helper
 * Arquivo: /app/config/helpers.php
 */

use App\Config\Database;

// Carregar configurações
require_once __DIR__ . '/config.php';

/**
 * Sanitiza saída HTML
 */
function e(string $text): string
{
    return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
}

/**
 * Formata valor monetário
 */
function formatMoney(float $value): string
{
    return 'R$ ' . number_format($value, 2, ',', '.');
}

/**
 * Converte valor monetário brasileiro para float
 * Aceita qualquer formato e converte corretamente
 */
function parseMoney(string $value): float
{
    // Remove espaços
    $value = trim($value);
    
    // Se vazio, retorna 0
    if (empty($value)) {
        return 0.0;
    }
    
    // Remove tudo exceto números, ponto e vírgula
    $value = preg_replace('/[^\d.,]/', '', $value);
    
    // Conta separadores
    $commaCount = substr_count($value, ',');
    $dotCount = substr_count($value, '.');
    
    // ESTRATÉGIA: Se tem vírgula, é formato BR (vírgula = decimal)
    if ($commaCount > 0) {
        // Remove todos os pontos (milhar)
        $value = str_replace('.', '', $value);
        // Troca vírgula por ponto (decimal)
        $value = str_replace(',', '.', $value);
    }
    // Se tem mais de um ponto, assume que pontos são milhar
    elseif ($dotCount > 1) {
        $value = str_replace('.', '', $value);
        // Assume 2 casas decimais
        $value = substr($value, 0, -2) . '.' . substr($value, -2);
    }
    // Se tem só um ponto (ex: 1500.00), já está correto
    
    return (float) $value;
}

/**
 * Formata data para exibição (BR)
 */
function formatDate(?string $date): string
{
    if (!$date) return '-';
    return date('d/m/Y', strtotime($date));
}

/**
 * Formata data e hora para exibição (BR)
 */
function formatDateTime(?string $datetime): string
{
    if (!$datetime) return '-';
    return date('d/m/Y H:i', strtotime($datetime));
}

/**
 * Formata telefone para exibição
 */
function formatPhone(?string $phone): string
{
    if (!$phone) return '-';
    
    $phone = preg_replace('/\D/', '', $phone);
    
    if (strlen($phone) === 11) {
        return '(' . substr($phone, 0, 2) . ') ' . substr($phone, 2, 5) . '-' . substr($phone, 7);
    } elseif (strlen($phone) === 10) {
        return '(' . substr($phone, 0, 2) . ') ' . substr($phone, 2, 4) . '-' . substr($phone, 6);
    }
    
    return $phone;
}

/**
 * Formata CPF/CNPJ
 */
function formatCpfCnpj(?string $doc): string
{
    if (!$doc) return '-';
    
    $doc = preg_replace('/\D/', '', $doc);
    
    if (strlen($doc) === 11) {
        // CPF
        return substr($doc, 0, 3) . '.' . substr($doc, 3, 3) . '.' . substr($doc, 6, 3) . '-' . substr($doc, 9);
    } elseif (strlen($doc) === 14) {
        // CNPJ
        return substr($doc, 0, 2) . '.' . substr($doc, 2, 3) . '.' . substr($doc, 5, 3) . '/' . substr($doc, 8, 4) . '-' . substr($doc, 12);
    }
    
    return $doc;
}

/**
 * Gera CSRF Token
 */
function generateCsrfToken(): string
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    $token = bin2hex(random_bytes(32));
    $_SESSION['csrf_token'] = $token;
    $_SESSION['csrf_token_time'] = time();
    
    return $token;
}

/**
 * Obtém CSRF Token atual (sem regenerar a cada chamada)
 */
function getCsrfToken(): string
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $token = $_SESSION['csrf_token'] ?? null;
    $time = $_SESSION['csrf_token_time'] ?? null;

    $expired = false;
    if ($time !== null && (time() - (int) $time > CSRF_TOKEN_LIFETIME)) {
        $expired = true;
    }

    if (empty($token) || $expired) {
        return generateCsrfToken();
    }

    return (string) $token;
}

/**
 * Valida CSRF Token
 */
function validateCsrfToken(?string $token): bool
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    if (empty($token) || empty($_SESSION['csrf_token'])) {
        return false;
    }
    
    if (!hash_equals($_SESSION['csrf_token'], $token)) {
        return false;
    }
    
    // Verificar se o token não expirou
    if (isset($_SESSION['csrf_token_time'])) {
        if (time() - $_SESSION['csrf_token_time'] > CSRF_TOKEN_LIFETIME) {
            return false;
        }
    }
    
    return true;
}

/**
 * Retorna CSRF Token para formulários
 */
function csrfField(): string
{
    $token = getCsrfToken();
    return '<input type="hidden" name="csrf_token" value="' . e($token) . '">';
}

/**
 * Alias para generateCsrfToken - retorna o token como string
 */
function csrfToken(): string
{
    return getCsrfToken();
}

/**
 * Gera URL
 */
function url(string $path = ''): string
{
    return APP_URL . '/' . ltrim($path, '/');
}

/**
 * Gera URL para assets
 */
function asset(string $path): string
{
    return APP_URL . '/public/assets/' . ltrim($path, '/');
}

/**
 * Gera URL para uploads
 * Aceita caminhos nos formatos:
 * - "logos/file.png"
 * - "uploads/logos/file.png"
 * - "public/uploads/logos/file.png"
 * - URLs absolutas (http/https)
 */
function uploadUrl(string $filename): string
{
    $filename = trim($filename);
    if ($filename === '') {
        return APP_URL . '/public/assets/img/placeholder.png';
    }

    // Retorna URLs externas inalteradas
    if (preg_match('#^https?://#i', $filename) || strpos($filename, '//') === 0) {
        return $filename;
    }

    // Normaliza removendo possíveis prefixos redundantes
    $filename = ltrim($filename, '/');
    if (str_starts_with($filename, 'public/uploads/')) {
        $filename = substr($filename, strlen('public/uploads/'));
    }
    if (str_starts_with($filename, 'uploads/')) {
        $filename = substr($filename, strlen('uploads/'));
    }

    return APP_URL . '/public/uploads/' . $filename;
}

/**
 * Redireciona para uma URL
 */
function redirect(string $path): void
{
    header('Location: ' . url($path));
    exit;
}

/**
 * Define mensagem flash
 */
function setFlash(string $type, string $message): void
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

/**
 * Obtém e limpa mensagem flash
 */
function getFlash(): ?array
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    
    return null;
}

/**
 * Verifica se usuário está logado
 */
function isLoggedIn(): bool
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    return isset($_SESSION['usuario_id']) && !empty($_SESSION['usuario_id']);
}

/**
 * Obtém ID do usuário logado
 */
function getUsuarioId(): ?int
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    return $_SESSION['usuario_id'] ?? null;
}

/**
 * Obtém ID da empresa do usuário logado
 */
function getEmpresaId(): ?int
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    return $_SESSION['empresa_id'] ?? null;
}

/**
 * Obtém perfil do usuário logado
 */
function getPerfil(): ?string
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    return $_SESSION['perfil'] ?? null;
}

/**
 * Verifica se usuário é admin
 */
function isAdmin(): bool
{
    return getPerfil() === 'admin';
}

/**
 * Obtém nome do usuário logado
 */
function getUsuarioNome(): ?string
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    return $_SESSION['usuario_nome'] ?? null;
}

/**
 * Obtém dados da empresa logada
 */
function getEmpresaDados(): ?array
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    return $_SESSION['empresa'] ?? null;
}

/**
 * Verifica limite de OS do plano
 */
function verificarLimiteOS(): array
{
    $empresaId = getEmpresaId();
    if (!$empresaId) {
        return ['permitido' => false, 'mensagem' => 'Empresa não identificada'];
    }
    
    $db = Database::getInstance();
    
    // Buscar dados da empresa
    $stmt = $db->prepare("SELECT plano, limite_os_mes, os_criadas_mes_atual, mes_referencia_os, data_fim_trial FROM empresas WHERE id = ?");
    $stmt->execute([$empresaId]);
    $empresa = $stmt->fetch();
    
    if (!$empresa) {
        return ['permitido' => false, 'mensagem' => 'Empresa não encontrada'];
    }
    
    // Verificar trial expirado
    if ($empresa['plano'] === 'trial' && $empresa['data_fim_trial'] < date('Y-m-d')) {
        return ['permitido' => false, 'mensagem' => 'Seu período de trial expirou. Faça upgrade para continuar.'];
    }
    
    // Verificar se mudou o mês e resetar contador
    $mesAtual = date('Y-m');
    if ($empresa['mes_referencia_os'] !== $mesAtual) {
        $stmt = $db->prepare("UPDATE empresas SET os_criadas_mes_atual = 0, mes_referencia_os = ? WHERE id = ?");
        $stmt->execute([$mesAtual, $empresaId]);
        $empresa['os_criadas_mes_atual'] = 0;
    }
    
    // Verificar limite (-1 = ilimitado)
    if ($empresa['limite_os_mes'] === -1) {
        return ['permitido' => true, 'mensagem' => ''];
    }
    
    if ($empresa['os_criadas_mes_atual'] >= $empresa['limite_os_mes']) {
        return ['permitido' => false, 'mensagem' => 'Limite de ' . $empresa['limite_os_mes'] . ' OS por mês atingido. Faça upgrade do plano.'];
    }
    
    return [
        'permitido' => true,
        'mensagem' => '',
        'usado' => $empresa['os_criadas_mes_atual'],
        'limite' => $empresa['limite_os_mes'],
        'restante' => $empresa['limite_os_mes'] - $empresa['os_criadas_mes_atual']
    ];
}

/**
 * Retorna uso de armazenamento em MB para a empresa
 */
function getUsoArmazenamentoMb(int $empresaId): float
{
    $db = Database::getInstance();
    $basePath = PROSERVICE_ROOT . '/public/';
    $totalBytes = 0;

    $paths = [];
    $stmt = $db->prepare("
        SELECT of.arquivo FROM os_fotos of
        INNER JOIN ordens_servico os ON of.os_id = os.id
        WHERE os.empresa_id = ?
    ");
    $stmt->execute([$empresaId]);
    while ($row = $stmt->fetch()) {
        $paths[] = $basePath . $row['arquivo'];
    }

    $stmt = $db->prepare("SELECT arquivo FROM assinaturas WHERE empresa_id = ? AND arquivo IS NOT NULL AND arquivo != ''");
    $stmt->execute([$empresaId]);
    while ($row = $stmt->fetch()) {
        $paths[] = $basePath . $row['arquivo'];
    }

    $stmt = $db->prepare("SELECT comprovante FROM despesas WHERE empresa_id = ? AND comprovante IS NOT NULL AND comprovante != ''");
    $stmt->execute([$empresaId]);
    while ($row = $stmt->fetch()) {
        $p = $row['comprovante'];
        $paths[] = $basePath . (strpos($p, 'uploads/') === 0 ? $p : 'uploads/' . $p);
    }

    $stmt = $db->prepare("SELECT logo FROM empresas WHERE id = ? AND logo IS NOT NULL AND logo != ''");
    $stmt->execute([$empresaId]);
    $row = $stmt->fetch();
    if ($row) {
        $paths[] = $basePath . $row['logo'];
    }

    foreach (array_unique($paths) as $p) {
        if (file_exists($p) && is_file($p)) {
            $totalBytes += filesize($p);
        }
    }

    return round($totalBytes / (1024 * 1024), 2);
}

/**
 * Verifica se empresa pode fazer upload (dentro do limite de armazenamento)
 * Retorna ['permitido' => bool, 'usado_mb' => float, 'limite_mb' => int, 'restante_mb' => float]
 */
function podeFazerUpload(int $empresaId, int $tamanhoBytes = 0): array
{
    $db = Database::getInstance();
    $stmt = $db->prepare("SELECT limite_armazenamento_mb FROM empresas WHERE id = ?");
    $stmt->execute([$empresaId]);
    $empresa = $stmt->fetch();
    $limiteMb = (int) ($empresa['limite_armazenamento_mb'] ?? 0);
    if ($limiteMb <= 0) {
        return ['permitido' => true, 'usado_mb' => 0, 'limite_mb' => -1, 'restante_mb' => -1];
    }
    $usadoMb = getUsoArmazenamentoMb($empresaId);
    $novoTamanhoMb = $tamanhoBytes / (1024 * 1024);
    $totalAposUpload = $usadoMb + $novoTamanhoMb;
    return [
        'permitido' => $totalAposUpload <= $limiteMb,
        'usado_mb' => $usadoMb,
        'limite_mb' => $limiteMb,
        'restante_mb' => max(0, $limiteMb - $usadoMb)
    ];
}

/**
 * Incrementa contador de OS criadas
 */
function incrementarContadorOS(): void
{
    $empresaId = getEmpresaId();
    if (!$empresaId) return;
    
    $db = Database::getInstance();
    $stmt = $db->prepare("UPDATE empresas SET os_criadas_mes_atual = os_criadas_mes_atual + 1 WHERE id = ?");
    $stmt->execute([$empresaId]);
}

/**
 * Log de auditoria do sistema
 * Registra ações críticas no logs_sistema
 */
function logSistema(string $acao, string $modulo, ?int $entidadeId = null, ?array $detalhes = null, string $nivel = 'info'): void
{
    \App\Models\LogSistema::registrar($acao, $modulo, $entidadeId, $detalhes, $nivel);
}

/**
 * Log de auditoria (alias legado - mantido para compatibilidade)
 */
function logAuditoria(string $acao, ?int $osId = null, ?array $dadosAnteriores = null, ?array $dadosNovos = null): void
{
    $usuarioId = getUsuarioId();
    $empresaId = getEmpresaId();
    
    if (!$empresaId) return;
    
    try {
        $db = Database::getInstance();
        $stmt = $db->prepare("INSERT INTO os_logs (os_id, usuario_id, acao, dados_anteriores, dados_novos, ip_address, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())");
        $stmt->execute([
            $osId,
            $usuarioId,
            $acao,
            $dadosAnteriores ? json_encode($dadosAnteriores) : null,
            $dadosNovos ? json_encode($dadosNovos) : null,
            $_SERVER['REMOTE_ADDR'] ?? null
        ]);
    } catch (\Exception $e) {
        error_log("Erro ao registrar log de auditoria: " . $e->getMessage());
    }
}

/**
 * Sanitiza input
 */
function sanitizeInput(string $input): string
{
    return trim(strip_tags($input));
}

/**
 * Valida email
 */
function isValidEmail(string $email): bool
{
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Gera slug
 */
function slugify(string $text): string
{
    $text = preg_replace('~[^\pL\d]+~u', '-', $text);
    $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
    $text = preg_replace('~[^-\w]+~', '', $text);
    $text = trim($text, '-');
    $text = preg_replace('~-+~', '-', $text);
    $text = strtolower($text);
    
    return empty($text) ? 'n-a' : $text;
}

/**
 * Trunca texto
 */
function truncate(string $text, int $length = 100, string $suffix = '...'): string
{
    if (strlen($text) <= $length) {
        return $text;
    }
    
    return substr($text, 0, $length) . $suffix;
}

/**
 * Retorna classe CSS baseada no status da OS
 */
function getStatusClass(string $status): string
{
    $classes = [
        'aberta' => 'bg-secondary',
        'em_orcamento' => 'bg-info',
        'aprovada' => 'bg-primary',
        'em_execucao' => 'bg-warning',
        'pausada' => 'bg-dark',
        'finalizada' => 'bg-success',
        'paga' => 'bg-success',
        'cancelada' => 'bg-danger'
    ];
    
    return $classes[$status] ?? 'bg-secondary';
}

/**
 * Retorna ícone Bootstrap para o status da OS
 */
function getStatusIcon(string $status): string
{
    $icons = [
        'aberta' => 'bi-folder',
        'em_orcamento' => 'bi-calculator',
        'aprovada' => 'bi-check2-circle',
        'em_execucao' => 'bi-gear-wide-connected',
        'pausada' => 'bi-pause-circle',
        'finalizada' => 'bi-check-circle',
        'paga' => 'bi-cash-coin',
        'cancelada' => 'bi-x-circle'
    ];
    return $icons[$status] ?? 'bi-circle';
}

/**
 * Retorna label baseada no status da OS
 */
function getStatusLabel(string $status): string
{
    $labels = [
        'aberta' => 'Aberta',
        'em_orcamento' => 'Em Orçamento',
        'aprovada' => 'Aprovada',
        'em_execucao' => 'Em Execução',
        'pausada' => 'Pausada',
        'finalizada' => 'Finalizada',
        'paga' => 'Paga',
        'cancelada' => 'Cancelada'
    ];
    
    return $labels[$status] ?? $status;
}

/**
 * Retorna classe CSS baseada na prioridade
 */
function getPrioridadeClass(string $prioridade): string
{
    $classes = [
        'urgente' => 'bg-danger',
        'alta' => 'bg-warning',
        'normal' => 'bg-info',
        'baixa' => 'bg-secondary'
    ];
    
    return $classes[$prioridade] ?? 'bg-secondary';
}

/**
 * Retorna label baseada na prioridade
 */
function getPrioridadeLabel(string $prioridade): string
{
    $labels = [
        'urgente' => 'Urgente',
        'alta' => 'Alta',
        'normal' => 'Normal',
        'baixa' => 'Baixa'
    ];
    
    return $labels[$prioridade] ?? $prioridade;
}

/**
 * Converte valor por extenso (simplificado)
 */
function valorPorExtenso(float $valor): string
{
    $valor = number_format($valor, 2, '.', '');
    $inteiro = explode('.', $valor)[0];
    $centavos = explode('.', $valor)[1];
    
    // Implementação simplificada - em produção usar biblioteca específica
    return $inteiro . ' reais e ' . $centavos . ' centavos';
}

/**
 * Debug helper
 */
function dd(...$vars): void
{
    echo '<pre style="background:#222;color:#0f0;padding:15px;overflow:auto;">';
    foreach ($vars as $var) {
        var_dump($var);
    }
    echo '</pre>';
    exit;
}

/**
 * Gera breadcrumb HTML
 * Uso: breadcrumb(['Dashboard' => 'dashboard', 'Clientes' => 'clientes', 'Novo'])
 */
function breadcrumb(array $items): string
{
    if (empty($items)) {
        return '';
    }
    
    $html = '<nav aria-label="breadcrumb" class="mb-3">';
    $html .= '<ol class="breadcrumb bg-light p-2 rounded" style="--bs-breadcrumb-divider: \'›\';">';
    
    $lastIndex = count($items) - 1;
    $index = 0;
    
    foreach ($items as $label => $url) {
        // Se o valor é numérico, é um item sem link (último)
        if (is_int($label)) {
            $html .= '<li class="breadcrumb-item active" aria-current="page">' . e($url) . '</li>';
        } elseif ($index === $lastIndex) {
            // Último item - ativo
            $html .= '<li class="breadcrumb-item active" aria-current="page">' . e($label) . '</li>';
        } else {
            // Item com link
            $html .= '<li class="breadcrumb-item"><a href="' . url($url) . '">' . e($label) . '</a></li>';
        }
        $index++;
    }
    
    $html .= '</ol>';
    $html .= '</nav>';
    
    return $html;
}
