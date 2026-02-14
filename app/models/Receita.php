<?php
/**
 * proService - Model Receita
 * Arquivo: /app/models/Receita.php
 */

namespace App\Models;

class Receita extends Model
{
    protected string $table = 'receitas';

    /**
     * Lista receitas com filtros
     */
    public function listar(array $filtros = [], int $page = 1, int $perPage = 20): array
    {
        $where = 'r.empresa_id = ?';
        $params = [$this->empresaId];
        
        // Aplicar filtros
        if (!empty($filtros['status'])) {
            $where .= ' AND r.status = ?';
            $params[] = $filtros['status'];
        }
        
        if (!empty($filtros['data_inicio'])) {
            $where .= ' AND r.data_recebimento >= ?';
            $params[] = $filtros['data_inicio'];
        }
        
        if (!empty($filtros['data_fim'])) {
            $where .= ' AND r.data_recebimento <= ?';
            $params[] = $filtros['data_fim'];
        }
        
        if (!empty($filtros['busca'])) {
            $where .= ' AND r.descricao LIKE ?';
            $params[] = '%' . $filtros['busca'] . '%';
        }
        
        // Contar total
        $sqlCount = "SELECT COUNT(*) as total FROM {$this->table} r WHERE {$where}";
        $stmt = $this->db->prepare($sqlCount);
        $stmt->execute($params);
        $total = (int) $stmt->fetch()['total'];
        
        // Buscar registros
        $offset = ($page - 1) * $perPage;
        $sql = "
            SELECT r.*, c.nome as cliente_nome, os.numero_os
            FROM {$this->table} r
            LEFT JOIN ordens_servico os ON r.os_id = os.id
            LEFT JOIN clientes c ON os.cliente_id = c.id
            WHERE {$where}
            ORDER BY r.data_recebimento DESC, r.created_at DESC
            LIMIT {$offset}, {$perPage}
        ";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $items = $stmt->fetchAll();
        
        return [
            'items' => $items,
            'total' => $total,
            'page' => $page,
            'per_page' => $perPage,
            'last_page' => (int) ceil($total / $perPage)
        ];
    }

    /**
     * Busca receita por ID com dados da OS
     */
    public function findComplete(int $id): ?array
    {
        $stmt = $this->db->prepare("
            SELECT r.*, c.nome as cliente_nome, os.numero_os, os.valor_total as os_valor
            FROM {$this->table} r
            LEFT JOIN ordens_servico os ON r.os_id = os.id
            LEFT JOIN clientes c ON os.cliente_id = c.id
            WHERE r.id = ? AND r.empresa_id = ?
            LIMIT 1
        ");
        $stmt->execute([$id, $this->empresaId]);
        
        return $stmt->fetch() ?: null;
    }

    /**
     * Marca receita como paga
     */
    public function marcarComoPago(int $id, string $formaPagamento, ?string $dataRecebimento = null): bool
    {
        $dataRecebimento = $dataRecebimento ?: date('Y-m-d');
        
        return $this->update($id, [
            'status' => 'recebido',
            'data_recebimento' => $dataRecebimento,
            'forma_pagamento' => $formaPagamento
        ]);
    }

    /**
     * Cancela receita
     */
    public function cancelar(int $id): bool
    {
        return $this->update($id, ['status' => 'cancelado']);
    }

    /**
     * Total de receitas por período
     */
    public function getTotalPorPeriodo(string $dataInicio, string $dataFim, string $status = 'recebido'): float
    {
        $stmt = $this->db->prepare("
            SELECT COALESCE(SUM(valor), 0) as total 
            FROM {$this->table}
            WHERE empresa_id = ? AND data_recebimento >= ? AND data_recebimento <= ? AND status = ?
        ");
        $stmt->execute([$this->empresaId, $dataInicio, $dataFim, $status]);
        
        return (float) $stmt->fetch()['total'];
    }

    /**
     * Receitas do mês atual
     */
    public function getReceitasMes(bool $apenasPendente = false): array
    {
        $status = $apenasPendente ? 'pendente' : 'recebido';
        
        $stmt = $this->db->prepare("
            SELECT COALESCE(SUM(valor), 0) as total, COUNT(*) as quantidade
            FROM {$this->table}
            WHERE empresa_id = ? AND DATE_FORMAT(data_recebimento, '%Y-%m') = ? AND status = ?
        ");
        $stmt->execute([$this->empresaId, date('Y-m'), $status]);
        
        return $stmt->fetch();
    }

    /**
     * Receitas pendentes (a receber)
     */
    public function getPendentes(): array
    {
        $stmt = $this->db->prepare("
            SELECT r.*, c.nome as cliente_nome, os.numero_os
            FROM {$this->table} r
            LEFT JOIN ordens_servico os ON r.os_id = os.id
            LEFT JOIN clientes c ON os.cliente_id = c.id
            WHERE r.empresa_id = ? AND r.status = 'pendente'
            ORDER BY r.data_recebimento ASC
        ");
        $stmt->execute([$this->empresaId]);
        
        return $stmt->fetchAll();
    }

    /**
     * Total pendente a receber
     */
    public function getTotalPendente(): float
    {
        $stmt = $this->db->prepare("
            SELECT COALESCE(SUM(valor), 0) as total 
            FROM {$this->table}
            WHERE empresa_id = ? AND status = 'pendente'
        ");
        $stmt->execute([$this->empresaId]);
        
        return (float) $stmt->fetch()['total'];
    }

    /**
     * Estatísticas por forma de pagamento
     */
    public function getPorFormaPagamento(string $mes = null): array
    {
        $mes = $mes ?: date('Y-m');
        
        $stmt = $this->db->prepare("
            SELECT 
                COALESCE(NULLIF(forma_pagamento, ''), 'nao_informado') as forma_pagamento, 
                COALESCE(SUM(valor), 0) as total, 
                COUNT(*) as quantidade
            FROM {$this->table}
            WHERE empresa_id = ? AND DATE_FORMAT(data_recebimento, '%Y-%m') = ? AND status = 'recebido'
            GROUP BY COALESCE(NULLIF(forma_pagamento, ''), 'nao_informado')
        ");
        $stmt->execute([$this->empresaId, $mes]);
        
        return $stmt->fetchAll();
    }

    /**
     * Receitas por dia do mês
     */
    public function getPorDia(string $mes = null): array
    {
        $mes = $mes ?: date('Y-m');
        
        $stmt = $this->db->prepare("
            SELECT DATE(data_recebimento) as dia, COALESCE(SUM(valor), 0) as total
            FROM {$this->table}
            WHERE empresa_id = ? AND DATE_FORMAT(data_recebimento, '%Y-%m') = ? AND status = 'recebido'
            GROUP BY DATE(data_recebimento)
            ORDER BY dia ASC
        ");
        $stmt->execute([$this->empresaId, $mes]);
        
        return $stmt->fetchAll();
    }

    /**
     * Receitas por período (lista detalhada)
     */
    public function getPorPeriodo(string $dataInicio, string $dataFim, ?string $status = null): array
    {
        $where = 'r.empresa_id = ? AND r.data_recebimento >= ? AND r.data_recebimento <= ?';
        $params = [$this->empresaId, $dataInicio, $dataFim];
        
        if ($status) {
            $where .= ' AND r.status = ?';
            $params[] = $status;
        }
        
        $sql = "
            SELECT r.*, c.nome as cliente_nome, os.numero_os
            FROM {$this->table} r
            LEFT JOIN ordens_servico os ON r.os_id = os.id
            LEFT JOIN clientes c ON os.cliente_id = c.id
            WHERE {$where}
            ORDER BY r.data_recebimento DESC
        ";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetchAll();
    }

    /**
     * Inadimplência - receitas pendentes com mais de X dias
     */
    public function getInadimplencia(int $dias = 30): array
    {
        $stmt = $this->db->prepare("
            SELECT r.*, c.nome as cliente_nome, os.numero_os,
                   DATEDIFF(CURDATE(), r.data_recebimento) as dias_atraso
            FROM {$this->table} r
            LEFT JOIN ordens_servico os ON r.os_id = os.id
            LEFT JOIN clientes c ON os.cliente_id = c.id
            WHERE r.empresa_id = ? 
              AND r.status = 'pendente'
              AND r.data_recebimento IS NOT NULL
              AND DATEDIFF(CURDATE(), r.data_recebimento) > ?
            ORDER BY r.data_recebimento ASC
        ");
        $stmt->execute([$this->empresaId, $dias]);
        
        return $stmt->fetchAll();
    }
}
