<?php
/**
 * Model para Recibos
 */
namespace App\Models;

class Recibo extends Model
{
    protected string $table = 'recibos';

    /**
     * Gera recibo automaticamente quando OS é paga
     */
    public function gerarDoOS(array $os): ?int
    {
        // Verificar se já existe recibo para esta OS
        $existente = $this->findBy('os_id', $os['id']);
        if ($existente) {
            return $existente['id'];
        }

        $data = [
            'empresa_id' => $os['empresa_id'],
            'os_id' => $os['id'],
            'cliente_id' => $os['cliente_id'],
            'valor' => $os['valor_total'],
            'forma_pagamento' => $os['forma_pagamento_acordada'] ?? 'dinheiro',
            'data_pagamento' => date('Y-m-d'),
            'status' => 'emitido'
        ];

        return $this->create($data);
    }

    /**
     * Busca recibo completo com dados do cliente
     */
    public function findComplete(int $id): ?array
    {
        $sql = "
            SELECT 
                r.*,
                c.nome as cliente_nome,
                c.cpf_cnpj as cliente_cpf_cnpj,
                c.telefone as cliente_telefone,
                c.endereco as cliente_endereco,
                os.numero_os,
                os.descricao as os_descricao,
                e.nome_fantasia as empresa_nome,
                e.cnpj_cpf as empresa_cnpj,
                e.endereco as empresa_endereco,
                e.telefone as empresa_telefone
            FROM {$this->table} r
            LEFT JOIN ordens_servico os ON r.os_id = os.id
            LEFT JOIN clientes c ON r.cliente_id = c.id
            LEFT JOIN empresas e ON r.empresa_id = e.id
            WHERE r.id = ? AND r.empresa_id = ?
        ";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id, $this->empresaId]);
        $recibo = $stmt->fetch();
        
        return $recibo ?: null;
    }

    /**
     * Busca recibo por OS
     */
    public function findByOS(int $osId): ?array
    {
        return $this->findBy('os_id', $osId);
    }

    /**
     * Lista recibos com filtros
     */
    public function listar(array $filtros = [], string $ordem = 'r.created_at DESC', int $pagina = 1, int $porPagina = 20): array
    {
        $where = "r.empresa_id = ?";
        $params = [$this->empresaId];

        if (!empty($filtros['cliente_id'])) {
            $where .= " AND r.cliente_id = ?";
            $params[] = $filtros['cliente_id'];
        }

        if (!empty($filtros['data_inicio'])) {
            $where .= " AND r.data_pagamento >= ?";
            $params[] = $filtros['data_inicio'];
        }

        if (!empty($filtros['data_fim'])) {
            $where .= " AND r.data_pagamento <= ?";
            $params[] = $filtros['data_fim'];
        }

        $sql = "
            SELECT 
                r.*,
                c.nome as cliente_nome,
                os.numero_os
            FROM {$this->table} r
            LEFT JOIN clientes c ON r.cliente_id = c.id
            LEFT JOIN ordens_servico os ON r.os_id = os.id
            WHERE {$where}
            ORDER BY {$ordem}
            LIMIT {$porPagina} OFFSET " . (($pagina - 1) * $porPagina) . "
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $items = $stmt->fetchAll();

        // Contar total
        $sqlCount = "SELECT COUNT(*) FROM {$this->table} r WHERE {$where}";
        $stmtCount = $this->db->prepare($sqlCount);
        $stmtCount->execute($params);
        $total = $stmtCount->fetchColumn();

        return [
            'items' => $items,
            'total' => $total,
            'per_page' => $porPagina,
            'current_page' => $pagina,
            'last_page' => ceil($total / $porPagina)
        ];
    }

    /**
     * Formata valor por extenso (simplificado)
     */
    public static function valorPorExtenso(float $valor): string
    {
        // Implementação básica - pode ser melhorada com biblioteca
        $formatter = new \NumberFormatter('pt_BR', \NumberFormatter::SPELLOUT);
        $valorExtenso = $formatter->format($valor);
        return ucfirst($valorExtenso) . ' reais';
    }

    /**
     * Cancela recibo
     */
    public function cancelar(int $id, string $motivo): bool
    {
        return $this->update($id, [
            'status' => 'cancelado',
            'observacoes' => $motivo
        ]);
    }
}
