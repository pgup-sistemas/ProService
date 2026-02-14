<?php
/**
 * proService - Parcela Model
 * Arquivo: /app/models/Parcela.php
 */

namespace App\Models;

class Parcela extends Model
{
    protected string $table = 'parcelas';

    /**
     * Cria parcelas para uma receita
     */
    public function criarParcelas(int $receitaId, int $numeroParcelas, float $valorTotal, string $dataPrimeiroVencimento): bool
    {
        $valorParcela = round($valorTotal / $numeroParcelas, 2);
        
        // Ajustar última parcela para compensar arredondamento
        $valorUltima = $valorTotal - ($valorParcela * ($numeroParcelas - 1));
        
        for ($i = 1; $i <= $numeroParcelas; $i++) {
            $valor = ($i == $numeroParcelas) ? $valorUltima : $valorParcela;
            $vencimento = date('Y-m-d', strtotime($dataPrimeiroVencimento . ' + ' . ($i - 1) . ' months'));
            
            $this->create([
                'receita_id' => $receitaId,
                'numero_parcela' => $i,
                'valor' => $valor,
                'data_vencimento' => $vencimento,
                'status' => 'pendente'
            ]);
        }
        
        return true;
    }

    /**
     * Busca parcelas por receita
     */
    public function getPorReceita(int $receitaId): array
    {
        return $this->findAll(
            ['receita_id' => $receitaId],
            'numero_parcela ASC'
        );
    }

    /**
     * Marca parcela como paga
     */
    public function marcarComoPaga(int $parcelaId, string $formaPagamento, ?string $dataPagamento = null): bool
    {
        return $this->update($parcelaId, [
            'status' => 'pago',
            'forma_pagamento' => $formaPagamento,
            'data_pagamento' => $dataPagamento ?? date('Y-m-d')
        ]);
    }

    /**
     * Verifica parcelas atrasadas e atualiza status
     */
    public function verificarAtrasadas(): int
    {
        $stmt = $this->db->prepare("
            UPDATE {$this->table} 
            SET status = 'atrasado'
            WHERE status = 'pendente' 
            AND data_vencimento < CURDATE()
        ");
        
        $stmt->execute();
        return $stmt->rowCount();
    }

    /**
     * Total de parcelas pendentes/a vencer
     */
    public function getTotalPendentes(): float
    {
        $stmt = $this->db->prepare("
            SELECT SUM(valor) as total 
            FROM {$this->table}
            WHERE empresa_id = ? AND status = 'pendente'
        ");
        
        $stmt->execute([$this->getEmpresaId()]);
        return (float) ($stmt->fetch()['total'] ?? 0);
    }

    /**
     * Parcelas que vencem em um período (com paginação)
     */
    public function getPorPeriodoPaginado(string $dataInicio, string $dataFim, int $page = 1, int $perPage = 20, ?string $status = null): array
    {
        $offset = ($page - 1) * $perPage;
        
        $where = 'p.empresa_id = ? AND p.data_vencimento BETWEEN ? AND ?';
        $params = [$this->getEmpresaId(), $dataInicio, $dataFim];
        
        if ($status) {
            $where .= ' AND p.status = ?';
            $params[] = $status;
        }
        
        // Contar total
        $countStmt = $this->db->prepare("
            SELECT COUNT(*) as total 
            FROM {$this->table} p
            WHERE {$where}
        ");
        $countStmt->execute($params);
        $total = $countStmt->fetch()['total'];
        
        // Buscar dados paginados
        $stmt = $this->db->prepare("
            SELECT p.*, r.os_id, r.descricao as receita_descricao
            FROM {$this->table} p
            JOIN receitas r ON p.receita_id = r.id
            WHERE {$where}
            ORDER BY p.data_vencimento ASC
            LIMIT {$offset}, {$perPage}
        ");
        
        $stmt->execute($params);
        $data = $stmt->fetchAll();
        
        return [
            'data' => $data,
            'total' => $total,
            'page' => $page,
            'per_page' => $perPage,
            'last_page' => (int) ceil($total / $perPage)
        ];
    }
}
