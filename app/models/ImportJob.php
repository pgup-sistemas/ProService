<?php
/**
 * proService - ImportJob Model
 * Arquivo: /app/models/ImportJob.php
 */

namespace App\Models;

class ImportJob extends Model
{
    protected string $table = 'import_jobs';

    /**
     * Retorna jobs pendentes para a empresa atual
     *
     * @param int $limit
     * @return array
     */
    public function findPending(int $limit = 10): array
    {
        return $this->findAll(['status' => 'pending'], 'created_at ASC', $limit);
    }

    /**
     * Paginação com filtros (status, q => original_filename LIKE)
     * Retorna mesmo formato que Model::paginate
     */
    public function filterPaginate(int $page = 1, int $perPage = 20, array $filters = [], string $orderBy = 'created_at DESC'): array
    {
        $offset = ($page - 1) * $perPage;

        $whereParts = [];
        $params = [];

        if (!empty($filters['status'])) {
            $whereParts[] = ' status = ? ';
            $params[] = $filters['status'];
        }

        if (!empty($filters['q'])) {
            $whereParts[] = ' original_filename LIKE ? ';
            $params[] = '%' . $filters['q'] . '%';
        }

        $where = implode(' AND ', $whereParts);
        // Aplicar filtro por empresa_id quando aplicável
        $this->addEmpresaFilter($where, $params);

        // Contagem total
        $sqlCount = "SELECT COUNT(*) as total FROM {$this->table}";
        if (!empty($where)) {
            $sqlCount .= " WHERE " . $where;
        }
        $stmt = $this->db->prepare($sqlCount);
        $stmt->execute($params);
        $total = (int) $stmt->fetch()['total'];

        // Buscar itens
        $sql = "SELECT * FROM {$this->table}";
        if (!empty($where)) {
            $sql .= " WHERE " . $where;
        }
        $sql .= " ORDER BY {$orderBy} LIMIT {$offset}, {$perPage}";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $items = $stmt->fetchAll();

        return [
            'items' => $items,
            'total' => $total,
            'page' => $page,
            'per_page' => $perPage,
            'last_page' => (int) ceil($total / $perPage),
            'total_pages' => (int) ceil($total / $perPage),
            'current_page' => $page,
            'has_previous' => $page > 1,
            'has_next' => $page < ceil($total / $perPage)
        ];
    }

    /**
     * Busca job por ID (mesmo comportamento do Model::findById com filtro por empresa)
     */
    public function findJobById(int $id): ?array
    {
        return $this->findById($id);
    }
}
