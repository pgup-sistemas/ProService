<?php
/**
 * proService - Model Log (Logs do Sistema)
 * Arquivo: /app/models/LogSistema.php
 * 
 * Gerencia logs de auditoria do sistema
 */

namespace App\Models;

class LogSistema extends Model
{
    protected string $table = 'logs_sistema';
    protected bool $useEmpresaFilter = true;

    /**
     * Registra uma ação no log
     */
    public static function registrar(string $acao, string $modulo, ?int $entidadeId = null, ?array $detalhes = null, string $nivel = 'info'): void
    {
        try {
            $db = \App\Config\Database::getInstance();
            
            $stmt = $db->prepare("
                INSERT INTO logs_sistema 
                (empresa_id, usuario_id, acao, modulo, entidade_id, detalhes, ip_address, user_agent, nivel, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
            ");
            
            $stmt->execute([
                getEmpresaId() ?? 0,
                getUsuarioId() ?? 0,
                $acao,
                $modulo,
                $entidadeId,
                $detalhes ? json_encode($detalhes) : null,
                $_SERVER['REMOTE_ADDR'] ?? null,
                $_SERVER['HTTP_USER_AGENT'] ?? null,
                $nivel
            ]);
        } catch (\Exception $e) {
            error_log("Erro ao registrar log: " . $e->getMessage());
        }
    }

    /**
     * Busca logs com filtros
     */
    public function buscar(array $filtros = [], int $page = 1, int $perPage = 50): array
    {
        // Build WHERE conditions sem alias (para contagem)
        $whereBase = '';
        $params = [];

        if (!empty($filtros['acao'])) {
            $whereBase .= ($whereBase ? ' AND ' : '') . "acao LIKE ?";
            $params[] = '%' . $filtros['acao'] . '%';
        }

        if (!empty($filtros['modulo'])) {
            $whereBase .= ($whereBase ? ' AND ' : '') . "modulo = ?";
            $params[] = $filtros['modulo'];
        }

        if (!empty($filtros['nivel'])) {
            $whereBase .= ($whereBase ? ' AND ' : '') . "nivel = ?";
            $params[] = $filtros['nivel'];
        }

        if (!empty($filtros['data_inicio'])) {
            $whereBase .= ($whereBase ? ' AND ' : '') . "DATE(created_at) >= ?";
            $params[] = $filtros['data_inicio'];
        }

        if (!empty($filtros['data_fim'])) {
            $whereBase .= ($whereBase ? ' AND ' : '') . "DATE(created_at) <= ?";
            $params[] = $filtros['data_fim'];
        }

        if (!empty($filtros['usuario_id'])) {
            $whereBase .= ($whereBase ? ' AND ' : '') . "usuario_id = ?";
            $params[] = $filtros['usuario_id'];
        }

        $this->addEmpresaFilter($whereBase, $params);

        // Contagem total (sem JOIN, usa WHERE base)
        $countSql = "SELECT COUNT(*) as total FROM {$this->table}";
        if ($whereBase) {
            $countSql .= " WHERE {$whereBase}";
        }
        $stmt = $this->db->prepare($countSql);
        $stmt->execute($params);
        $total = $stmt->fetch()['total'];

        // Dados paginados (com JOIN, usa WHERE com alias l)
        $whereAliased = $whereBase;
        if ($whereAliased) {
            // Adiciona alias l. a cada coluna
            $whereAliased = preg_replace('/\b(acao|modulo|nivel|created_at|usuario_id|empresa_id)\b/', 'l.$1', $whereAliased);
        }

        $sql = "SELECT l.*, u.nome as usuario_nome 
                FROM {$this->table} l 
                LEFT JOIN usuarios u ON l.usuario_id = u.id";
        if ($whereAliased) {
            $sql .= " WHERE {$whereAliased}";
        }
        $sql .= " ORDER BY l.created_at DESC LIMIT ? OFFSET ?";
        
        $params[] = $perPage;
        $params[] = ($page - 1) * $perPage;

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $items = $stmt->fetchAll();

        return [
            'items' => $items,
            'total' => $total,
            'current_page' => $page,
            'per_page' => $perPage,
            'total_pages' => ceil($total / $perPage),
            'has_next' => $page < ceil($total / $perPage),
            'has_previous' => $page > 1,
        ];
    }

    /**
     * Estatísticas de logs
     */
    public function estatisticas(int $dias = 7): array
    {
        $sql = "
            SELECT 
                nivel,
                COUNT(*) as total
            FROM {$this->table}
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
        ";
        
        $params = [$dias];
        
        if ($this->empresaId) {
            $sql .= " AND empresa_id = ?";
            $params[] = $this->empresaId;
        }
        
        $sql .= " GROUP BY nivel";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $porNivel = $stmt->fetchAll();

        // Top ações
        $sql = "
            SELECT 
                acao,
                COUNT(*) as total
            FROM {$this->table}
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
        ";
        
        $params = [$dias];
        
        if ($this->empresaId) {
            $sql .= " AND empresa_id = ?";
            $params[] = $this->empresaId;
        }
        
        $sql .= " GROUP BY acao ORDER BY total DESC LIMIT 10";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $topAcoes = $stmt->fetchAll();

        return [
            'por_nivel' => $porNivel,
            'top_acoes' => $topAcoes,
        ];
    }

    /**
     * Limpar logs antigos
     */
    public function limparAntigos(int $dias = 90): int
    {
        $sql = "DELETE FROM {$this->table} WHERE created_at < DATE_SUB(NOW(), INTERVAL ? DAY)";
        $params = [$dias];
        
        if ($this->empresaId) {
            $sql .= " AND empresa_id = ?";
            $params[] = $this->empresaId;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->rowCount();
    }
}
