<?php
/**
 * proService - Model Cliente
 * Arquivo: /app/models/Cliente.php
 */

namespace App\Models;

class Cliente extends Model
{
    protected string $table = 'clientes';

    /**
     * Busca clientes por nome ou telefone
     */
    public function buscar(string $termo): array
    {
        $termo = '%' . $termo . '%';
        
        $where = "(nome LIKE ? OR telefone LIKE ? OR whatsapp LIKE ? OR cpf_cnpj LIKE ?)";
        $params = [$termo, $termo, $termo, $termo];
        $this->addEmpresaFilter($where, $params);
        
        $sql = "SELECT * FROM {$this->table} WHERE {$where} ORDER BY nome ASC LIMIT 20";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetchAll();
    }

    /**
     * Busca cliente por telefone
     */
    public function findByTelefone(string $telefone): ?array
    {
        // Remove formatação
        $telefone = preg_replace('/\D/', '', $telefone);
        
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE REPLACE(REPLACE(REPLACE(REPLACE(telefone, '(', ''), ')', ''), '-', ''), ' ', '') = ? OR REPLACE(REPLACE(REPLACE(REPLACE(whatsapp, '(', ''), ')', ''), '-', ''), ' ', '') = ? LIMIT 1");
        $stmt->execute([$telefone, $telefone]);
        
        $result = $stmt->fetch();
        return $result ?: null;
    }

    /**
     * Busca cliente por CPF/CNPJ
     */
    public function findByCpfCnpj(string $cpfCnpj): ?array
    {
        $cpfCnpj = preg_replace('/\D/', '', $cpfCnpj);
        
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE REPLACE(REPLACE(REPLACE(cpf_cnpj, '.', ''), '-', ''), '/', '') = ? LIMIT 1");
        $stmt->execute([$cpfCnpj]);
        
        $result = $stmt->fetch();
        return $result ?: null;
    }

    /**
     * Retorna histórico do cliente (OS e gastos)
     */
    public function getHistorico(int $clienteId): array
    {
        $historico = [];
        
        // Total gasto
        $stmt = $this->db->prepare("
            SELECT SUM(valor_total) as total_gasto, COUNT(*) as total_os 
            FROM ordens_servico 
            WHERE cliente_id = ? AND status = 'paga'
        ");
        $stmt->execute([$clienteId]);
        $result = $stmt->fetch();
        $historico['total_gasto'] = (float) ($result['total_gasto'] ?? 0);
        $historico['total_os'] = (int) ($result['total_os'] ?? 0);
        
        // Ticket médio
        $historico['ticket_medio'] = $historico['total_os'] > 0 
            ? $historico['total_gasto'] / $historico['total_os'] 
            : 0;
        
        // Último serviço
        $stmt = $this->db->prepare("
            SELECT os.*, s.nome as servico_nome 
            FROM ordens_servico os
            LEFT JOIN servicos s ON os.servico_id = s.id
            WHERE os.cliente_id = ?
            ORDER BY os.created_at DESC
            LIMIT 1
        ");
        $stmt->execute([$clienteId]);
        $historico['ultimo_servico'] = $stmt->fetch() ?: null;
        
        // OS em aberto
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as total FROM ordens_servico 
            WHERE cliente_id = ? AND status NOT IN ('paga', 'cancelada')
        ");
        $stmt->execute([$clienteId]);
        $historico['os_em_aberto'] = (int) $stmt->fetch()['total'];
        
        // Lista de todas as OS
        $stmt = $this->db->prepare("
            SELECT os.*, s.nome as servico_nome 
            FROM ordens_servico os
            LEFT JOIN servicos s ON os.servico_id = s.id
            WHERE os.cliente_id = ?
            ORDER BY os.created_at DESC
        ");
        $stmt->execute([$clienteId]);
        $historico['ordens_servico'] = $stmt->fetchAll();
        
        return $historico;
    }

    /**
     * Lista clientes com OS pendentes
     */
    public function listarComOsPendentes(): array
    {
        $empresaId = $this->getEmpresaId();
        
        $sql = "
            SELECT DISTINCT c.* 
            FROM {$this->table} c
            INNER JOIN ordens_servico os ON c.id = os.cliente_id
            WHERE c.empresa_id = ? AND os.status NOT IN ('paga', 'cancelada')
            ORDER BY c.nome ASC
        ";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$empresaId]);
        
        return $stmt->fetchAll();
    }

    /**
     * Lista clientes inativos (sem OS há X meses)
     */
    public function listarInativos(int $meses = 6): array
    {
        $empresaId = $this->getEmpresaId();
        $dataLimite = date('Y-m-d', strtotime("-{$meses} months"));
        
        $sql = "
            SELECT c.*, MAX(os.created_at) as ultima_os
            FROM {$this->table} c
            LEFT JOIN ordens_servico os ON c.id = os.cliente_id
            WHERE c.empresa_id = ?
            GROUP BY c.id
            HAVING (ultima_os IS NULL OR ultima_os < ?)
            ORDER BY c.nome ASC
        ";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$empresaId, $dataLimite]);
        
        return $stmt->fetchAll();
    }

    /**
     * Clientes com pagamento pendente
     */
    public function listarInadimplentes(): array
    {
        $empresaId = $this->getEmpresaId();
        
        $sql = "
            SELECT DISTINCT c.* 
            FROM {$this->table} c
            INNER JOIN ordens_servico os ON c.id = os.cliente_id
            WHERE c.empresa_id = ? AND os.status = 'finalizada'
            ORDER BY c.nome ASC
        ";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$empresaId]);
        
        return $stmt->fetchAll();
    }

    /**
     * Busca global (Ctrl+K) - busca clientes por nome, telefone, CPF/CNPJ
     */
    public function buscarGlobal(string $query, int $empresaId): array
    {
        $sql = "
            SELECT * FROM {$this->table}
            WHERE empresa_id = ? 
            AND (
                nome LIKE ? 
                OR telefone LIKE ?
                OR cpf_cnpj LIKE ?
                OR email LIKE ?
            )
            ORDER BY nome ASC
            LIMIT 10
        ";
        
        $busca = '%' . $query . '%';
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$empresaId, $busca, $busca, $busca, $busca]);
        
        return $stmt->fetchAll();
    }
}
