<?php
/**
 * proService - Model Base
 * Arquivo: /app/models/Model.php
 */

namespace App\Models;

use App\Config\Database;
use PDO;
use PDOException;

abstract class Model
{
    protected PDO $db;
    protected string $table;
    protected string $primaryKey = 'id';
    protected ?int $empresaId = null;
    protected bool $useEmpresaFilter = true; // Nova propriedade para controlar filtro

    public function __construct()
    {
        $this->db = Database::getInstance();
        
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $this->empresaId = $_SESSION['empresa_id'] ?? null;
    }

    /**
     * Define o empresa_id manualmente (útil para seeds/testes)
     */
    public function setEmpresaId(int $empresaId): void
    {
        $this->empresaId = $empresaId;
    }

    /**
     * Retorna empresa_id atual
     */
    protected function getEmpresaId(): ?int
    {
        return $this->empresaId;
    }

    /**
     * Adiciona cláusula WHERE empresa_id à query
     */
    protected function addEmpresaFilter(string &$where, array &$params): void
    {
        if ($this->empresaId !== null && $this->useEmpresaFilter) {
            if (!empty($where)) {
                $where .= ' AND ';
            }
            $where .= ' empresa_id = ? ';
            $params[] = $this->empresaId;
        }
    }

    /**
     * Busca todos os registros
     */
    public function findAll(array $conditions = [], string $orderBy = 'id DESC', ?int $limit = null): array
    {
        $where = '';
        $params = [];
        
        foreach ($conditions as $key => $value) {
            if (!empty($where)) {
                $where .= ' AND ';
            }
            $where .= " {$key} = ? ";
            $params[] = $value;
        }
        
        $this->addEmpresaFilter($where, $params);
        
        $sql = "SELECT * FROM {$this->table}";
        if (!empty($where)) {
            $sql .= " WHERE {$where}";
        }
        $sql .= " ORDER BY {$orderBy}";
        if ($limit) {
            $sql .= " LIMIT {$limit}";
        }
        
        $stmt = $this->db->prepare($sql);
        
        // Garante que todos os params são escalares (não arrays)
        $params = array_map(function($p) {
            return is_array($p) ? json_encode($p) : $p;
        }, $params);
        
        $stmt->execute($params);
        
        return $stmt->fetchAll();
    }

    /**
     * Busca um registro por ID
     */
    public function findById(int $id): ?array
    {
        $where = "{$this->primaryKey} = ?";
        $params = [$id];
        $this->addEmpresaFilter($where, $params);
        
        $sql = "SELECT * FROM {$this->table} WHERE {$where} LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        
        $result = $stmt->fetch();
        return $result ?: null;
    }

    /**
     * Busca registro por campo específico
     */
    public function findBy(string $field, $value): ?array
    {
        $where = "{$field} = ?";
        $params = [$value];
        $this->addEmpresaFilter($where, $params);
        
        $sql = "SELECT * FROM {$this->table} WHERE {$where} LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        
        $result = $stmt->fetch();
        return $result ?: null;
    }

    /**
     * Cria novo registro
     */
    public function create(array $data): ?int
    {
        // Adiciona empresa_id automaticamente
        if ($this->empresaId !== null && !isset($data['empresa_id'])) {
            $data['empresa_id'] = $this->empresaId;
        }
        
        $fields = array_keys($data);
        $placeholders = array_fill(0, count($fields), '?');
        
        $sql = "INSERT INTO {$this->table} (" . implode(', ', $fields) . ") 
                VALUES (" . implode(', ', $placeholders) . ")";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute(array_values($data));
            return (int) $this->db->lastInsertId();
        } catch (PDOException $e) {
            $errorInfo = null;
            if (method_exists($e, 'errorInfo')) {
                $errorInfo = $e->errorInfo;
            }
            error_log("Erro ao criar registro ({$this->table}): " . $e->getMessage());
            if ($errorInfo) {
                error_log('PDO errorInfo: ' . print_r($errorInfo, true));
            }
            error_log('SQL: ' . $sql);
            error_log('Fields: ' . print_r(array_keys($data), true));

            if (defined('ENVIRONMENT') && ENVIRONMENT === 'development') {
                $extra = $errorInfo ? (' | errorInfo: ' . json_encode($errorInfo)) : '';
                throw new PDOException(
                    "Erro ao criar registro ({$this->table}): {$e->getMessage()}{$extra}",
                    (int) $e->getCode(),
                    $e
                );
            }

            return null;
        }
    }

    /**
     * Atualiza registro
     */
    public function update(int $id, array $data): bool
    {
        $fields = [];
        $params = [];
        
        foreach ($data as $key => $value) {
            $fields[] = "{$key} = ?";
            $params[] = $value;
        }
        
        $params[] = $id;
        
        $sql = "UPDATE {$this->table} SET " . implode(', ', $fields) . " WHERE {$this->primaryKey} = ?";
        
        // Adiciona verificação de empresa_id se necessário
        if ($this->empresaId !== null && $this->useEmpresaFilter) {
            $sql .= " AND empresa_id = ?";
            $params[] = $this->empresaId;
        }
        
        try {
            $stmt = $this->db->prepare($sql);
            return $stmt->execute($params);
        } catch (PDOException $e) {
            error_log("Erro ao atualizar registro: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Deleta registro
     */
    public function delete(int $id): bool
    {
        $sql = "DELETE FROM {$this->table} WHERE {$this->primaryKey} = ?";
        $params = [$id];
        
        if ($this->empresaId !== null && $this->useEmpresaFilter) {
            $sql .= " AND empresa_id = ?";
            $params[] = $this->empresaId;
        }
        
        try {
            $stmt = $this->db->prepare($sql);
            return $stmt->execute($params);
        } catch (PDOException $e) {
            error_log("Erro ao deletar registro: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Conta registros
     */
    public function count(array $conditions = []): int
    {
        $where = '';
        $params = [];
        
        foreach ($conditions as $key => $value) {
            if (!empty($where)) {
                $where .= ' AND ';
            }
            $where .= " {$key} = ? ";
            $params[] = $value;
        }
        
        $this->addEmpresaFilter($where, $params);
        
        $sql = "SELECT COUNT(*) as total FROM {$this->table}";
        if (!empty($where)) {
            $sql .= " WHERE {$where}";
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch();
        
        return (int) ($result['total'] ?? 0);
    }

    /**
     * Busca com paginação
     */
    public function paginate(int $page = 1, int $perPage = 20, array $conditions = [], string $orderBy = 'id DESC'): array
    {
        $offset = ($page - 1) * $perPage;
        
        $where = '';
        $params = [];
        
        foreach ($conditions as $key => $value) {
            if (!empty($where)) {
                $where .= ' AND ';
            }
            $where .= " {$key} = ? ";
            $params[] = $value;
        }
        
        $this->addEmpresaFilter($where, $params);
        
        // Conta total
        $sqlCount = "SELECT COUNT(*) as total FROM {$this->table}";
        if (!empty($where)) {
            $sqlCount .= " WHERE {$where}";
        }
        $stmt = $this->db->prepare($sqlCount);
        $stmt->execute($params);
        $total = (int) $stmt->fetch()['total'];
        
        // Busca registros
        $sql = "SELECT * FROM {$this->table}";
        if (!empty($where)) {
            $sql .= " WHERE {$where}";
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
     * Busca com LIKE (busca parcial)
     */
    public function search(string $field, string $term, string $orderBy = 'id DESC', ?int $limit = null): array
    {
        $where = "{$field} LIKE ?";
        $params = ['%' . $term . '%'];
        $this->addEmpresaFilter($where, $params);
        
        $sql = "SELECT * FROM {$this->table} WHERE {$where} ORDER BY {$orderBy}";
        if ($limit) {
            $sql .= " LIMIT {$limit}";
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetchAll();
    }
}
