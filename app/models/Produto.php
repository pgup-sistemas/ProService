<?php
/**
 * proService - Model Produto
 * Arquivo: /app/models/Produto.php
 */

namespace App\Models;

class Produto extends Model
{
    protected string $table = 'produtos';

    /**
     * Busca produtos por nome ou código
     */
    public function buscar(string $termo): array
    {
        $termo = '%' . $termo . '%';
        
        $where = "(nome LIKE ? OR codigo_sku LIKE ?)";
        $params = [$termo, $termo];
        $this->addEmpresaFilter($where, $params);
        
        $sql = "SELECT * FROM {$this->table} WHERE {$where} AND ativo = 1 ORDER BY nome ASC LIMIT 20";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetchAll();
    }

    /**
     * Lista produtos em falta (estoque <= mínimo)
     */
    public function listarEmFalta(): array
    {
        $stmt = $this->db->prepare("
            SELECT * FROM {$this->table}
            WHERE empresa_id = ? AND ativo = 1 AND quantidade_estoque <= quantidade_minima
            ORDER BY quantidade_estoque ASC
        ");
        $stmt->execute([$this->empresaId]);
        return $stmt->fetchAll();
    }

    /**
     * Verifica se produto está em falta
     */
    public function estaEmFalta(int $produtoId): bool
    {
        $produto = $this->findById($produtoId);
        
        if (!$produto) {
            return false;
        }
        
        return $produto['quantidade_estoque'] <= $produto['quantidade_minima'];
    }

    /**
     * Verifica se há estoque suficiente
     */
    public function verificarEstoque(int $produtoId, float $quantidade): bool
    {
        $produto = $this->findById($produtoId);
        
        if (!$produto) {
            return false;
        }
        
        return $produto['quantidade_estoque'] >= $quantidade;
    }

    /**
     * Registra entrada de estoque
     */
    public function entradaEstoque(int $produtoId, float $quantidade, ?float $custoUnitario = null, ?string $motivo = null): bool
    {
        $produto = $this->findById($produtoId);
        if (!$produto) {
            return false;
        }
        
        $data = [
            'quantidade_estoque' => $produto['quantidade_estoque'] + $quantidade
        ];
        
        if ($custoUnitario !== null) {
            $data['custo_unitario'] = $custoUnitario;
        }
        
        $this->update($produtoId, $data);
        
        // Registra movimentação
        $stmt = $this->db->prepare("
            INSERT INTO movimentacao_estoque (empresa_id, produto_id, tipo, quantidade, custo_unitario, motivo, created_at)
            VALUES (?, ?, 'entrada', ?, ?, ?, NOW())
        ");
        
        return $stmt->execute([
            $this->empresaId,
            $produtoId,
            $quantidade,
            $custoUnitario,
            $motivo
        ]);
    }

    /**
     * Registra saída/ajuste de estoque
     */
    public function saidaEstoque(int $produtoId, float $quantidade, ?string $motivo = null): bool
    {
        $produto = $this->findById($produtoId);
        if (!$produto) {
            return false;
        }
        
        $this->update($produtoId, [
            'quantidade_estoque' => $produto['quantidade_estoque'] - $quantidade
        ]);
        
        // Registra movimentação
        $stmt = $this->db->prepare("
            INSERT INTO movimentacao_estoque (empresa_id, produto_id, tipo, quantidade, motivo, created_at)
            VALUES (?, ?, 'ajuste', ?, ?, NOW())
        ");
        
        return $stmt->execute([
            $this->empresaId,
            $produtoId,
            -$quantidade,
            $motivo
        ]);
    }

    /**
     * Retorna histórico de movimentação do produto
     */
    public function getHistoricoMovimentacao(int $produtoId, int $limit = 50): array
    {
        $stmt = $this->db->prepare("
            SELECT m.*, u.nome as usuario_nome
            FROM movimentacao_estoque m
            LEFT JOIN usuarios u ON m.usuario_id = u.id
            WHERE m.produto_id = ? AND m.empresa_id = ?
            ORDER BY m.created_at DESC
            LIMIT {$limit}
        ");
        $stmt->execute([$produtoId, $this->empresaId]);
        
        return $stmt->fetchAll();
    }

    /**
     * Lista produtos por categoria
     */
    public function listarPorCategoria(): array
    {
        $stmt = $this->db->prepare("
            SELECT * FROM {$this->table} 
            WHERE empresa_id = ? AND ativo = 1 
            ORDER BY categoria ASC, nome ASC
        ");
        $stmt->execute([$this->empresaId]);
        
        $produtos = $stmt->fetchAll();
        
        // Agrupar por categoria
        $agrupado = [];
        foreach ($produtos as $produto) {
            $categoria = $produto['categoria'] ?: 'Sem Categoria';
            $agrupado[$categoria][] = $produto;
        }
        
        return $agrupado;
    }

    /**
     * Estatísticas de estoque
     */
    public function getEstatisticas(): array
    {
        $stats = [];
        
        // Total de produtos
        $stats['total'] = $this->count(['ativo' => 1]);
        
        // Produtos em falta
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as total FROM {$this->table}
            WHERE empresa_id = ? AND ativo = 1 AND quantidade_estoque <= quantidade_minima
        ");
        $stmt->execute([$this->empresaId]);
        $stats['em_falta'] = (int) $stmt->fetch()['total'];
        
        // Valor total em estoque
        $stmt = $this->db->prepare("
            SELECT SUM(quantidade_estoque * custo_unitario) as total FROM {$this->table}
            WHERE empresa_id = ? AND ativo = 1
        ");
        $stmt->execute([$this->empresaId]);
        $stats['valor_total'] = (float) ($stmt->fetch()['total'] ?? 0);
        
        return $stats;
    }

    /**
     * Relatório de posição de estoque
     */
    public function getRelatorioPosicao(): array
    {
        $stmt = $this->db->prepare("
            SELECT p.*, 
                   (p.quantidade_estoque * p.custo_unitario) as custo_total
            FROM {$this->table} p
            WHERE p.empresa_id = ? AND p.ativo = 1
            ORDER BY p.nome ASC
        ");
        $stmt->execute([$this->empresaId]);
        
        return $stmt->fetchAll();
    }

    /**
     * Movimentações de estoque por período
     */
    public function getMovimentacoes(string $dataInicio, string $dataFim): array
    {
        $stmt = $this->db->prepare("
            SELECT m.*, p.nome as produto_nome, p.codigo_sku
            FROM movimentacao_estoque m
            JOIN {$this->table} p ON m.produto_id = p.id
            WHERE m.empresa_id = ? AND DATE(m.created_at) BETWEEN ? AND ?
            ORDER BY m.created_at DESC
        ");
        $stmt->execute([$this->empresaId, $dataInicio, $dataFim]);
        
        return $stmt->fetchAll();
    }

    /**
     * Produtos mais utilizados em OS
     */
    public function getMaisUtilizados(string $dataInicio, string $dataFim, int $limit = 10): array
    {
        $stmt = $this->db->prepare("
            SELECT p.*, 
                   SUM(op.quantidade) as total_usado,
                   SUM(op.custo_total) as custo_total,
                   COUNT(DISTINCT op.os_id) as os_utilizadas
            FROM {$this->table} p
            JOIN os_produtos op ON p.id = op.produto_id
            JOIN ordens_servico os ON op.os_id = os.id
            WHERE p.empresa_id = ? 
              AND os.data_entrada BETWEEN ? AND ?
            GROUP BY p.id
            ORDER BY total_usado DESC
            LIMIT {$limit}
        ");
        $stmt->execute([$this->empresaId, $dataInicio, $dataFim]);
        
        return $stmt->fetchAll();
    }

    /**
     * Custo total em estoque
     */
    public function getCustoTotalEstoque(): float
    {
        $stmt = $this->db->prepare("
            SELECT SUM(quantidade_estoque * custo_unitario) as total 
            FROM {$this->table}
            WHERE empresa_id = ? AND ativo = 1
        ");
        $stmt->execute([$this->empresaId]);
        
        return (float) ($stmt->fetch()['total'] ?? 0);
    }

    /**
     * Busca global (Ctrl+K) - busca produtos por nome, código SKU
     */
    public function buscarGlobal(string $query, int $empresaId): array
    {
        $sql = "
            SELECT * FROM {$this->table}
            WHERE empresa_id = ? 
            AND (
                nome LIKE ? 
                OR codigo_sku LIKE ?
                OR descricao LIKE ?
            )
            ORDER BY nome ASC
            LIMIT 10
        ";
        
        $busca = '%' . $query . '%';
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$empresaId, $busca, $busca, $busca]);
        
        return $stmt->fetchAll();
    }
}
