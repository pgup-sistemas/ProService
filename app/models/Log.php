<?php
/**
 * proService - Log Model (Logs de Ações do Sistema)
 * Arquivo: /app/models/Log.php
 */

namespace App\Models;

class Log extends Model
{
    protected string $table = 'logs_sistema';

    /**
     * Registra uma ação no sistema
     */
    public function registrar(string $acao, string $modulo, ?int $entidadeId = null, array $detalhes = [], string $nivel = 'info'): bool
    {
        $empresaId = $this->getEmpresaId();
        $usuarioId = getUsuarioId() ?? 0;
        
        return $this->create([
            'empresa_id' => $empresaId,
            'usuario_id' => $usuarioId,
            'acao' => $acao,
            'modulo' => $modulo,
            'entidade_id' => $entidadeId,
            'detalhes' => json_encode($detalhes),
            'ip_address' => $this->getIpAddress(),
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null,
            'nivel' => $nivel
        ]) !== false;
    }

    /**
     * Busca logs com filtros
     */
    public function buscar(array $filtros = [], int $limit = 100): array
    {
        $where = 'empresa_id = ?';
        $params = [$this->getEmpresaId()];
        
        if (!empty($filtros['modulo'])) {
            $where .= ' AND modulo = ?';
            $params[] = $filtros['modulo'];
        }
        
        if (!empty($filtros['nivel'])) {
            $where .= ' AND nivel = ?';
            $params[] = $filtros['nivel'];
        }
        
        if (!empty($filtros['usuario_id'])) {
            $where .= ' AND usuario_id = ?';
            $params[] = $filtros['usuario_id'];
        }
        
        if (!empty($filtros['data_inicio'])) {
            $where .= ' AND DATE(created_at) >= ?';
            $params[] = $filtros['data_inicio'];
        }
        
        if (!empty($filtros['data_fim'])) {
            $where .= ' AND DATE(created_at) <= ?';
            $params[] = $filtros['data_fim'];
        }
        
        $sql = "
            SELECT l.*, u.nome as usuario_nome
            FROM {$this->table} l
            LEFT JOIN usuarios u ON l.usuario_id = u.id
            WHERE {$where}
            ORDER BY l.created_at DESC
            LIMIT {$limit}
        ";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetchAll();
    }

    /**
     * Logs recentes para dashboard
     */
    public function getRecentes(int $limit = 10): array
    {
        return $this->buscar([], $limit);
    }

    /**
     * Estatísticas de logs
     */
    public function getEstatisticas(string $periodo = '7d'): array
    {
        $dataLimite = match($periodo) {
            '24h' => 'INTERVAL 1 DAY',
            '30d' => 'INTERVAL 30 DAY',
            default => 'INTERVAL 7 DAY'
        };
        
        // Por nível
        $stmt = $this->db->prepare("
            SELECT nivel, COUNT(*) as total
            FROM {$this->table}
            WHERE empresa_id = ? AND created_at >= DATE_SUB(NOW(), {$dataLimite})
            GROUP BY nivel
        ");
        $stmt->execute([$this->getEmpresaId()]);
        $porNivel = $stmt->fetchAll();
        
        // Por módulo
        $stmt = $this->db->prepare("
            SELECT modulo, COUNT(*) as total
            FROM {$this->table}
            WHERE empresa_id = ? AND created_at >= DATE_SUB(NOW(), {$dataLimite})
            GROUP BY modulo
            ORDER BY total DESC
            LIMIT 5
        ");
        $stmt->execute([$this->getEmpresaId()]);
        $porModulo = $stmt->fetchAll();
        
        // Total
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as total
            FROM {$this->table}
            WHERE empresa_id = ? AND created_at >= DATE_SUB(NOW(), {$dataLimite})
        ");
        $stmt->execute([$this->getEmpresaId()]);
        $total = $stmt->fetch()['total'];
        
        return [
            'por_nivel' => $porNivel,
            'por_modulo' => $porModulo,
            'total' => $total
        ];
    }

    /**
     * Limpa logs antigos
     */
    public function limparAntigos(int $dias = 90): int
    {
        $stmt = $this->db->prepare("
            DELETE FROM {$this->table}
            WHERE empresa_id = ? AND created_at < DATE_SUB(NOW(), INTERVAL ? DAY)
        ");
        $stmt->execute([$this->getEmpresaId(), $dias]);
        return $stmt->rowCount();
    }

    /**
     * Helper: retorna IP do usuário
     */
    private function getIpAddress(): ?string
    {
        $headers = [
            'HTTP_CF_CONNECTING_IP', // Cloudflare
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_FORWARDED',
            'HTTP_X_CLUSTER_CLIENT_IP',
            'HTTP_FORWARDED_FOR',
            'HTTP_FORWARDED',
            'REMOTE_ADDR'
        ];
        
        foreach ($headers as $header) {
            if (!empty($_SERVER[$header])) {
                $ip = $_SERVER[$header];
                if (strpos($ip, ',') !== false) {
                    $ips = explode(',', $ip);
                    $ip = trim($ips[0]);
                }
                if (filter_var($ip, FILTER_VALIDATE_IP)) {
                    return $ip;
                }
            }
        }
        
        return null;
    }
}

/**
 * Helper global para registrar logs
 */
function logSistema(string $acao, string $modulo, ?int $entidadeId = null, array $detalhes = [], string $nivel = 'info'): void
{
    static $logModel = null;
    if ($logModel === null) {
        $logModel = new Log();
    }
    $logModel->registrar($acao, $modulo, $entidadeId, $detalhes, $nivel);
}
