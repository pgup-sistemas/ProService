<?php
/**
 * proService - Model Despesa
 * Arquivo: /app/models/Despesa.php
 */

namespace App\Models;

class Despesa extends Model
{
    protected string $table = 'despesas';

    /**
     * Lista despesas com filtros
     */
    public function listar(array $filtros = [], int $page = 1, int $perPage = 20): array
    {
        $where = 'empresa_id = ?';
        $params = [$this->empresaId];
        
        // Aplicar filtros
        if (!empty($filtros['categoria'])) {
            $where .= ' AND categoria = ?';
            $params[] = $filtros['categoria'];
        }
        
        if (!empty($filtros['status'])) {
            $where .= ' AND status = ?';
            $params[] = $filtros['status'];
        }
        
        if (!empty($filtros['data_inicio'])) {
            $where .= ' AND data_despesa >= ?';
            $params[] = $filtros['data_inicio'];
        }
        
        if (!empty($filtros['data_fim'])) {
            $where .= ' AND data_despesa <= ?';
            $params[] = $filtros['data_fim'];
        }
        
        if (!empty($filtros['busca'])) {
            $where .= ' AND descricao LIKE ?';
            $params[] = '%' . $filtros['busca'] . '%';
        }
        
        // Contar total
        $sqlCount = "SELECT COUNT(*) as total FROM {$this->table} WHERE {$where}";
        $stmt = $this->db->prepare($sqlCount);
        $stmt->execute($params);
        $total = (int) $stmt->fetch()['total'];
        
        // Buscar registros
        $offset = ($page - 1) * $perPage;
        $sql = "
            SELECT * FROM {$this->table}
            WHERE {$where}
            ORDER BY data_despesa DESC, created_at DESC
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
     * Total de despesas por período
     */
    public function getTotalPorPeriodo(string $dataInicio, string $dataFim): float
    {
        $stmt = $this->db->prepare("
            SELECT COALESCE(SUM(valor), 0) as total 
            FROM {$this->table}
            WHERE empresa_id = ? AND data_despesa >= ? AND data_despesa <= ? AND status = 'pago'
        ");
        $stmt->execute([$this->empresaId, $dataInicio, $dataFim]);
        
        return (float) $stmt->fetch()['total'];
    }

    /**
     * Despesas do mês atual
     */
    public function getDespesasMes(): array
    {
        $stmt = $this->db->prepare("
            SELECT COALESCE(SUM(valor), 0) as total, COUNT(*) as quantidade
            FROM {$this->table}
            WHERE empresa_id = ? AND DATE_FORMAT(data_despesa, '%Y-%m') = ? AND status = 'pago'
        ");
        $stmt->execute([$this->empresaId, date('Y-m')]);
        
        return $stmt->fetch();
    }

    /**
     * Despesas por categoria
     */
    public function getPorCategoria(string $mes = null): array
    {
        $mes = $mes ?: date('Y-m');
        
        $stmt = $this->db->prepare("
            SELECT categoria, COALESCE(SUM(valor), 0) as total, COUNT(*) as quantidade
            FROM {$this->table}
            WHERE empresa_id = ? AND DATE_FORMAT(data_despesa, '%Y-%m') = ? AND status = 'pago'
            GROUP BY categoria
            ORDER BY total DESC
        ");
        $stmt->execute([$this->empresaId, $mes]);
        
        return $stmt->fetchAll();
    }

    /**
     * Estatísticas mensais
     */
    public function getEstatisticas(): array
    {
        $stats = [];
        
        // Total do mês
        $stmt = $this->db->prepare("
            SELECT COALESCE(SUM(valor), 0) as total, COUNT(*) as quantidade
            FROM {$this->table}
            WHERE empresa_id = ? AND DATE_FORMAT(data_despesa, '%Y-%m') = ? AND status = 'pago'
        ");
        $stmt->execute([$this->empresaId, date('Y-m')]);
        $stats['mes_atual'] = $stmt->fetch();
        
        // Total pendente
        $stmt = $this->db->prepare("
            SELECT COALESCE(SUM(valor), 0) as total, COUNT(*) as quantidade
            FROM {$this->table}
            WHERE empresa_id = ? AND status = 'pendente'
        ");
        $stmt->execute([$this->empresaId]);
        $stats['pendente'] = $stmt->fetch();
        
        // Maiores despesas do mês
        $stmt = $this->db->prepare("
            SELECT * FROM {$this->table}
            WHERE empresa_id = ? AND DATE_FORMAT(data_despesa, '%Y-%m') = ? AND status = 'pago'
            ORDER BY valor DESC
            LIMIT 5
        ");
        $stmt->execute([$this->empresaId, date('Y-m')]);
        $stats['maiores'] = $stmt->fetchAll();
        
        return $stats;
    }

    /**
     * Retorna categorias disponíveis
     */
    public function getCategorias(): array
    {
        return ['material', 'servico', 'salario', 'aluguel', 'imposto', 'outros'];
    }

    /**
     * Retorna label da categoria
     */
    public function getCategoriaLabel(string $categoria): string
    {
        $labels = [
            'material' => 'Material',
            'servico' => 'Serviço',
            'salario' => 'Salário',
            'aluguel' => 'Aluguel',
            'imposto' => 'Imposto',
            'outros' => 'Outros'
        ];
        
        return $labels[$categoria] ?? 'Outros';
    }

    /**
     * Despesas por período (lista detalhada)
     */
    public function getPorPeriodo(string $dataInicio, string $dataFim): array
    {
        $stmt = $this->db->prepare("
            SELECT * FROM {$this->table}
            WHERE empresa_id = ? AND data_despesa >= ? AND data_despesa <= ?
            ORDER BY data_despesa DESC
        ");
        $stmt->execute([$this->empresaId, $dataInicio, $dataFim]);
        
        return $stmt->fetchAll();
    }

    /**
     * Relatório detalhado de despesas com filtros
     */
    public function getRelatorioDetalhado(array $filtros = []): array
    {
        $where = 'empresa_id = ?';
        $params = [$this->empresaId];
        
        if (!empty($filtros['data_inicio'])) {
            $where .= ' AND data_despesa >= ?';
            $params[] = $filtros['data_inicio'];
        }
        
        if (!empty($filtros['data_fim'])) {
            $where .= ' AND data_despesa <= ?';
            $params[] = $filtros['data_fim'];
        }
        
        if (!empty($filtros['categoria'])) {
            $where .= ' AND categoria = ?';
            $params[] = $filtros['categoria'];
        }
        
        $sql = "
            SELECT * FROM {$this->table}
            WHERE {$where}
            ORDER BY data_despesa DESC, created_at DESC
        ";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetchAll();
    }

    /**
     * Marcar despesa como paga
     */
    public function marcarComoPago(int $id, string $formaPagamento = null): bool
    {
        $sql = "UPDATE {$this->table} SET status = 'pago'";
        $params = [];
        
        if ($formaPagamento) {
            $sql .= ", forma_pagamento = ?";
            $params[] = $formaPagamento;
        }
        
        $sql .= " WHERE id = ? AND empresa_id = ?";
        $params[] = $id;
        $params[] = $this->empresaId;
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    /**
     * Despesas pendentes (a pagar)
     */
    public function getPendentes(): array
    {
        $stmt = $this->db->prepare("
            SELECT * FROM {$this->table}
            WHERE empresa_id = ? AND status = 'pendente'
            ORDER BY data_despesa ASC
        ");
        $stmt->execute([$this->empresaId]);
        
        return $stmt->fetchAll();
    }

    /**
     * Total pendente a pagar
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
     * Evolução mensal de despesas (últimos N meses)
     */
    public function getEvolucaoMensal(int $meses = 6): array
    {
        $dados = [];
        
        for ($i = $meses - 1; $i >= 0; $i--) {
            $mes = date('Y-m', strtotime("-{$i} months"));
            $inicio = $mes . '-01';
            $fim = date('Y-m-t', strtotime($inicio));
            
            $stmt = $this->db->prepare("
                SELECT COALESCE(SUM(valor), 0) as total
                FROM {$this->table}
                WHERE empresa_id = ? AND data_despesa BETWEEN ? AND ? AND status = 'pago'
            ");
            $stmt->execute([$this->empresaId, $inicio, $fim]);
            
            $dados[] = [
                'mes' => $mes,
                'total' => (float) $stmt->fetch()['total']
            ];
        }
        
        return $dados;
    }

    /**
     * Lista despesas recorrentes (templates)
     */
    public function listarRecorrentes(): array
    {
        return $this->findAll(
            ['recorrente' => 1, 'despesa_pai_id' => null],
            'descricao ASC'
        );
    }

    /**
     * Cria próxima despesa recorrente se necessário
     */
    public function processarRecorrentes(): int
    {
        $recorrentes = $this->listarRecorrentes();
        $criadas = 0;
        
        foreach ($recorrentes as $despesa) {
            // Verifica se já existe despesa filha para o próximo período
            $proximoVencimento = $this->calcularProximoVencimento($despesa);
            
            if (!$this->existeDespesaFilha($despesa['id'], $proximoVencimento)) {
                // Cria nova despesa filha
                $novaDespesa = $despesa;
                $novaDespesa['despesa_pai_id'] = $despesa['id'];
                $novaDespesa['data_despesa'] = $proximoVencimento;
                $novaDespesa['data_proximo_vencimento'] = null;
                $novaDespesa['status'] = 'pendente';
                $novaDespesa['recorrente'] = 0;
                
                unset($novaDespesa['id'], $novaDespesa['created_at'], $novaDespesa['updated_at']);
                
                if ($this->create($novaDespesa)) {
                    $criadas++;
                    // Atualiza data do próximo vencimento na despesa pai
                    $this->update($despesa['id'], [
                        'data_proximo_vencimento' => $proximoVencimento
                    ]);
                }
            }
        }
        
        return $criadas;
    }

    /**
     * Verifica se existe despesa filha para uma data
     */
    private function existeDespesaFilha(int $despesaPaiId, string $data): bool
    {
        $stmt = $this->db->prepare("SELECT id FROM {$this->table} WHERE despesa_pai_id = ? AND data_despesa = ? LIMIT 1");
        $stmt->execute([$despesaPaiId, $data]);
        return $stmt->fetch() !== false;
    }

    /**
     * Calcula próximo vencimento baseado na frequência
     */
    private function calcularProximoVencimento(array $despesa): string
    {
        $base = $despesa['data_proximo_vencimento'] ?? $despesa['data_despesa'];
        $frequencia = $despesa['frequencia'] ?? 'mensal';
        
        return match($frequencia) {
            'semanal' => date('Y-m-d', strtotime($base . ' + 1 week')),
            'anual' => date('Y-m-d', strtotime($base . ' + 1 year')),
            default => date('Y-m-d', strtotime($base . ' + 1 month'))
        };
    }

    /**
     * Alerta despesas próximas do vencimento
     */
    public function alertasProximas(int $dias = 3): array
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE empresa_id = ? AND status = 'pendente' AND data_despesa <= DATE_ADD(CURDATE(), INTERVAL ? DAY) ORDER BY data_despesa ASC");
        $stmt->execute([$this->empresaId, $dias]);
        return $stmt->fetchAll();
    }
}
