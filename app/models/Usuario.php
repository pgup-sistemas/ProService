<?php
/**
 * proService - Model Usuario
 * Arquivo: /app/models/Usuario.php
 */

namespace App\Models;

class Usuario extends Model
{
    protected string $table = 'usuarios';

    /**
     * Busca usuário por email dentro da empresa
     */
    public function findByEmail(string $email, ?int $empresaId = null): ?array
    {
        $empresaId = $empresaId ?? $this->empresaId;
        
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE email = ? AND empresa_id = ? LIMIT 1");
        $stmt->execute([$email, $empresaId]);
        
        $result = $stmt->fetch();
        return $result ?: null;
    }

    /**
     * Cria novo usuário com senha hasheada
     */
    public function criar(array $data): ?int
    {
        // Hash da senha
        $data['senha'] = password_hash($data['senha'], PASSWORD_BCRYPT, ['cost' => 12]);
        
        return $this->create($data);
    }

    /**
     * Verifica login
     */
    public function verificarLogin(string $email, string $senha, int $empresaId): ?array
    {
        $usuario = $this->findByEmail($email, $empresaId);
        
        if (!$usuario) {
            return null;
        }
        
        if (!password_verify($senha, $usuario['senha'])) {
            return null;
        }
        
        // Rehash se necessário
        if (password_needs_rehash($usuario['senha'], PASSWORD_BCRYPT)) {
            $this->update($usuario['id'], ['senha' => password_hash($senha, PASSWORD_BCRYPT)]);
        }
        
        unset($usuario['senha']);
        return $usuario;
    }

    /**
     * Atualiza senha
     */
    public function atualizarSenha(int $id, string $novaSenha): bool
    {
        $senhaHash = password_hash($novaSenha, PASSWORD_BCRYPT, ['cost' => 12]);
        return $this->update($id, ['senha' => $senhaHash]);
    }

    /**
     * Reseta senha para padrão
     */
    public function resetarSenha(int $id): bool
    {
        $senhaPadrao = 'proservice123'; // Em produção, gerar senha aleatória
        return $this->atualizarSenha($id, $senhaPadrao);
    }

    /**
     * Atualiza último acesso
     */
    public function registrarAcesso(int $id): bool
    {
        return $this->update($id, ['ultimo_acesso' => date('Y-m-d H:i:s')]);
    }

    /**
     * Ativa/desativa usuário
     */
    public function toggleStatus(int $id): bool
    {
        $usuario = $this->findById($id);
        if (!$usuario) {
            return false;
        }
        
        return $this->update($id, ['ativo' => !$usuario['ativo']]);
    }

    /**
     * Lista técnicos ativos
     */
    public function listarTecnicos(): array
    {
        return $this->findAll(['perfil' => 'tecnico', 'ativo' => 1], 'nome ASC');
    }

    /**
     * Busca todos os usuários da empresa
     */
    public function listarTodos(): array
    {
        $where = '';
        $params = [];
        $this->addEmpresaFilter($where, $params);
        
        $sql = "SELECT * FROM {$this->table}";
        if (!empty($where)) {
            $sql .= " WHERE {$where}";
        }
        $sql .= " ORDER BY perfil DESC, nome ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetchAll();
    }

    /**
     * Conta técnicos ativos
     */
    public function contarTecnicos(): int
    {
        return $this->count(['perfil' => 'tecnico', 'ativo' => 1]);
    }

    /**
     * Atualiza assinatura digital
     */
    public function atualizarAssinatura(int $id, string $assinaturaPath): bool
    {
        return $this->update($id, ['assinatura_digital' => $assinaturaPath]);
    }

    /**
     * Gera token de recuperação de senha
     */
    public function gerarTokenRecuperacao(string $email, int $empresaId): ?string
    {
        $usuario = $this->findByEmail($email, $empresaId);
        
        if (!$usuario) {
            return null;
        }
        
        $token = bin2hex(random_bytes(32));
        $expira = date('Y-m-d H:i:s', strtotime('+1 hour'));
        
        $sql = "UPDATE {$this->table} SET reset_token = ?, reset_token_expira = ? WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$token, $expira, $usuario['id']]);
        
        return $token;
    }

    /**
     * Valida token de recuperação
     */
    public function validarTokenRecuperacao(string $token): ?array
    {
        $sql = "SELECT * FROM {$this->table} WHERE reset_token = ? AND reset_token_expira > NOW() LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$token]);
        
        $result = $stmt->fetch();
        return $result ?: null;
    }

    /**
     * Limpa token de recuperação
     */
    public function limparTokenRecuperacao(int $id): bool
    {
        $sql = "UPDATE {$this->table} SET reset_token = NULL, reset_token_expira = NULL WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$id]);
    }

    /**
     * Desempenho de técnicos por período
     */
    public function getDesempenhoTecnicos(string $dataInicio, string $dataFim): array
    {
        $stmt = $this->db->prepare("
            SELECT 
                u.id,
                u.nome,
                u.email,
                COUNT(DISTINCT os.id) as total_os,
                SUM(os.valor_total) as receita_gerada,
                AVG(os.valor_total) as ticket_medio,
                AVG(DATEDIFF(os.data_finalizacao, os.data_entrada)) as tempo_medio
            FROM {$this->table} u
            LEFT JOIN ordens_servico os ON u.id = os.tecnico_id
                AND os.data_entrada BETWEEN ? AND ?
                AND os.status IN ('finalizada', 'paga')
            WHERE u.empresa_id = ? AND u.perfil = 'tecnico' AND u.ativo = 1
            GROUP BY u.id
            ORDER BY total_os DESC, receita_gerada DESC
        ");
        $stmt->execute([$dataInicio, $dataFim, $this->empresaId]);
        
        return $stmt->fetchAll();
    }
}
