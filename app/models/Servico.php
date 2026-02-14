<?php
/**
 * proService - Model Servico
 * Arquivo: /app/models/Servico.php
 */

namespace App\Models;

class Servico extends Model
{
    protected string $table = 'servicos';

    /**
     * Busca serviços por nome
     */
    public function buscar(string $termo): array
    {
        $termo = '%' . $termo . '%';
        
        $where = "nome LIKE ? AND ativo = 1";
        $params = [$termo];
        $this->addEmpresaFilter($where, $params);
        
        $sql = "SELECT * FROM {$this->table} WHERE {$where} ORDER BY nome ASC LIMIT 20";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetchAll();
    }

    /**
     * Lista serviços mais utilizados
     */
    public function getMaisUtilizados(int $limit = 5): array
    {
        $stmt = $this->db->prepare("
            SELECT s.*, COUNT(os.id) as total_os
            FROM {$this->table} s
            LEFT JOIN ordens_servico os ON s.id = os.servico_id
            WHERE s.empresa_id = ? AND s.ativo = 1
            GROUP BY s.id
            ORDER BY total_os DESC
            LIMIT {$limit}
        ");
        $stmt->execute([$this->empresaId]);
        
        return $stmt->fetchAll();
    }

    /**
     * Lista serviços por categoria
     */
    public function listarPorCategoria(): array
    {
        $stmt = $this->db->prepare("
            SELECT * FROM {$this->table} 
            WHERE empresa_id = ? AND ativo = 1 
            ORDER BY categoria ASC, nome ASC
        ");
        $stmt->execute([$this->empresaId]);
        
        $servicos = $stmt->fetchAll();
        
        // Agrupar por categoria
        $agrupado = [];
        foreach ($servicos as $servico) {
            $categoria = $servico['categoria'] ?: 'Sem Categoria';
            $agrupado[$categoria][] = $servico;
        }
        
        return $agrupado;
    }

    /**
     * Duplica um serviço existente
     */
    public function duplicar(int $servicoId): ?int
    {
        $servico = $this->findById($servicoId);
        
        if (!$servico) {
            return null;
        }
        
        unset($servico['id']);
        unset($servico['created_at']);
        unset($servico['updated_at']);
        
        $servico['nome'] .= ' (Cópia)';
        $servico['ativo'] = 1;
        
        return $this->create($servico);
    }

    /**
     * Retorna categorias únicas
     */
    public function getCategorias(): array
    {
        $stmt = $this->db->prepare("
            SELECT DISTINCT categoria FROM {$this->table}
            WHERE empresa_id = ? AND ativo = 1 AND categoria IS NOT NULL AND categoria != ''
            ORDER BY categoria ASC
        ");
        $stmt->execute([$this->empresaId]);
        
        return array_column($stmt->fetchAll(), 'categoria');
    }
}
