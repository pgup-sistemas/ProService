<?php
/**
 * proService - Assinatura Model (Tipos: autorizacao, conformidade)
 * Arquivo: /app/models/Assinatura.php
 */

namespace App\Models;

class Assinatura extends Model
{
    protected string $table = 'assinaturas';

    /**
     * Registra uma nova assinatura
     */
    public function registrar(array $dados): int|false
    {
        return $this->create([
            'empresa_id' => $dados['empresa_id'],
            'os_id' => $dados['os_id'],
            'tipo' => $dados['tipo'], // 'autorizacao' ou 'conformidade'
            'assinante_nome' => $dados['assinante_nome'],
            'assinante_documento' => $dados['assinante_documento'] ?? null,
            'arquivo' => $dados['arquivo'],
            'ip_address' => $dados['ip_address'] ?? $this->getIpAddress(),
            'user_agent' => $dados['user_agent'] ?? ($_SERVER['HTTP_USER_AGENT'] ?? null),
            'observacoes' => $dados['observacoes'] ?? null
        ]);
    }

    /**
     * Busca assinaturas de uma OS
     */
    public function getByOs(int $osId, ?string $tipo = null): array
    {
        $sql = "SELECT * FROM {$this->table} WHERE os_id = ? AND empresa_id = ?";
        $params = [$osId, $this->getEmpresaId()];
        
        if ($tipo) {
            $sql .= " AND tipo = ?";
            $params[] = $tipo;
        }
        
        $sql .= " ORDER BY created_at DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Verifica se existe assinatura de determinado tipo
     */
    public function hasAssinatura(int $osId, string $tipo): bool
    {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as total 
            FROM {$this->table} 
            WHERE os_id = ? AND tipo = ? AND empresa_id = ?
        ");
        $stmt->execute([$osId, $tipo, $this->getEmpresaId()]);
        return $stmt->fetch()['total'] > 0;
    }

    /**
     * Verifica se OS tem todas as assinaturas necessárias
     */
    public function statusAssinaturas(int $osId): array
    {
        return [
            'autorizacao' => $this->hasAssinatura($osId, 'autorizacao'),
            'conformidade' => $this->hasAssinatura($osId, 'conformidade')
        ];
    }

    /**
     * Get última assinatura por tipo
     */
    public function getUltima(int $osId, string $tipo): ?array
    {
        $stmt = $this->db->prepare("
            SELECT * FROM {$this->table} 
            WHERE os_id = ? AND tipo = ? AND empresa_id = ?
            ORDER BY created_at DESC 
            LIMIT 1
        ");
        $stmt->execute([$osId, $tipo, $this->getEmpresaId()]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    /**
     * Lista todas assinaturas da empresa
     */
    public function listar(array $filtros = [], int $limit = 50): array
    {
        $where = 'a.empresa_id = ?';
        $params = [$this->getEmpresaId()];
        
        if (!empty($filtros['tipo'])) {
            $where .= ' AND a.tipo = ?';
            $params[] = $filtros['tipo'];
        }
        
        if (!empty($filtros['os_id'])) {
            $where .= ' AND a.os_id = ?';
            $params[] = $filtros['os_id'];
        }
        
        $sql = "
            SELECT a.*, os.numero_os, c.nome as cliente_nome
            FROM {$this->table} a
            JOIN ordens_servico os ON a.os_id = os.id
            JOIN clientes c ON os.cliente_id = c.id
            WHERE {$where}
            ORDER BY a.created_at DESC
            LIMIT {$limit}
        ";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Remove assinatura
     */
    public function remover(int $id): bool
    {
        $assinatura = $this->findById($id);
        if (!$assinatura || $assinatura['empresa_id'] != $this->getEmpresaId()) {
            return false;
        }
        
        // Remove arquivo físico
        if ($assinatura['arquivo']) {
            $filepath = PROSERVICE_ROOT . '/public/' . $assinatura['arquivo'];
            if (file_exists($filepath)) {
                unlink($filepath);
            }
        }
        
        return $this->delete($id);
    }

    /**
     * Helper: retorna IP
     */
    private function getIpAddress(): ?string
    {
        $headers = ['HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR'];
        foreach ($headers as $header) {
            if (!empty($_SERVER[$header])) {
                $ip = $_SERVER[$header];
                if (strpos($ip, ',') !== false) {
                    $ips = explode(',', $ip);
                    $ip = trim($ips[0]);
                }
                if (filter_var($ip, FILTER_VALIDATE_IP)) {
                    return $ip;
                }
            }
        }
        return null;
    }
}
