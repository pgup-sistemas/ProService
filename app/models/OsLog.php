<?php
/**
 * Model para Histórico de Logs da OS
 */
namespace App\Models;

class OsLog extends Model
{
    protected string $table = 'os_logs';

    /**
     * Registra um log de alteração na OS
     */
    public function registrar(int $osId, string $acao, string $tipoAcao = 'outro', ?array $dadosAnteriores = null, ?array $dadosNovos = null): bool
    {
        $data = [
            'os_id' => $osId,
            'usuario_id' => getUsuarioId() ?? 0,
            'acao' => $acao,
            'tipo_acao' => $tipoAcao,
            'dados_anteriores' => $dadosAnteriores ? json_encode($dadosAnteriores) : null,
            'dados_novos' => $dadosNovos ? json_encode($dadosNovos) : null,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null
        ];

        return $this->create($data) > 0;
    }

    /**
     * Lista logs de uma OS específica
     */
    public function listarPorOS(int $osId, int $limite = 50): array
    {
        $sql = "
            SELECT 
                ol.*,
                u.nome as usuario_nome,
                u.perfil as usuario_perfil
            FROM {$this->table} ol
            LEFT JOIN usuarios u ON ol.usuario_id = u.id
            WHERE ol.os_id = ?
            ORDER BY ol.created_at DESC
            LIMIT ?
        ";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$osId, $limite]);
        
        return $stmt->fetchAll();
    }

    /**
     * Lista logs com filtro por tipo de ação
     */
    public function listarPorTipo(int $osId, string $tipoAcao): array
    {
        $sql = "
            SELECT 
                ol.*,
                u.nome as usuario_nome,
                u.perfil as usuario_perfil
            FROM {$this->table} ol
            LEFT JOIN usuarios u ON ol.usuario_id = u.id
            WHERE ol.os_id = ? AND ol.tipo_acao = ?
            ORDER BY ol.created_at DESC
        ";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$osId, $tipoAcao]);
        
        return $stmt->fetchAll();
    }

    /**
     * Obtém ícone baseado no tipo de ação
     */
    public static function getIconePorTipo(string $tipoAcao): string
    {
        return match($tipoAcao) {
            'status' => 'bi-arrow-repeat',
            'valor' => 'bi-cash',
            'produto' => 'bi-box-seam',
            'dados' => 'bi-pencil',
            'assinatura' => 'bi-pen',
            'visualizacao' => 'bi-eye',
            default => 'bi-circle'
        };
    }

    /**
     * Obtém cor baseada no tipo de ação
     */
    public static function getCorPorTipo(string $tipoAcao): string
    {
        return match($tipoAcao) {
            'status' => 'primary',
            'valor' => 'success',
            'produto' => 'info',
            'dados' => 'warning',
            'assinatura' => 'dark',
            'visualizacao' => 'secondary',
            default => 'light'
        };
    }

    /**
     * Registra mudança de status
     */
    public function registrarMudancaStatus(int $osId, string $statusAnterior, string $statusNovo): bool
    {
        return $this->registrar(
            $osId,
            "Status alterado de '{$statusAnterior}' para '{$statusNovo}'",
            'status',
            ['status' => $statusAnterior],
            ['status' => $statusNovo]
        );
    }

    /**
     * Registra alteração de valor
     */
    public function registrarMudancaValor(int $osId, array $valoresAntigos, array $valoresNovos): bool
    {
        $mudancas = [];
        foreach ($valoresNovos as $campo => $valor) {
            if (isset($valoresAntigos[$campo]) && $valoresAntigos[$campo] != $valor) {
                $mudancas[] = "{$campo}: " . formatMoney($valoresAntigos[$campo]) . " → " . formatMoney($valor);
            }
        }
        
        if (empty($mudancas)) {
            return false;
        }
        
        return $this->registrar(
            $osId,
            'Valores alterados: ' . implode(', ', $mudancas),
            'valor',
            $valoresAntigos,
            $valoresNovos
        );
    }

    /**
     * Registra adição de produto
     */
    public function registrarAdicaoProduto(int $osId, string $produtoNome, float $quantidade, float $valor): bool
    {
        return $this->registrar(
            $osId,
            "Produto adicionado: {$produtoNome} ({$quantidade} un - " . formatMoney($valor) . ")",
            'produto',
            null,
            ['produto' => $produtoNome, 'quantidade' => $quantidade, 'valor' => $valor]
        );
    }

    /**
     * Registra remoção de produto
     */
    public function registrarRemocaoProduto(int $osId, string $produtoNome, float $quantidade): bool
    {
        return $this->registrar(
            $osId,
            "Produto removido: {$produtoNome} ({$quantidade} un)",
            'produto',
            ['produto' => $produtoNome, 'quantidade' => $quantidade],
            null
        );
    }

    /**
     * Registra assinatura do cliente
     */
    public function registrarAssinatura(int $osId, string $tipoAssinatura = 'cliente'): bool
    {
        return $this->registrar(
            $osId,
            "Assinatura do {$tipoAssinatura} registrada",
            'assinatura',
            null,
            ['tipo' => $tipoAssinatura, 'data' => date('Y-m-d H:i:s')]
        );
    }

    /**
     * Registra visualização do link público
     */
    public function registrarVisualizacao(int $osId, ?string $ip = null): bool
    {
        return $this->registrar(
            $osId,
            'Link público visualizado pelo cliente',
            'visualizacao',
            null,
            ['ip' => $ip ?? $_SERVER['REMOTE_ADDR'] ?? 'desconhecido']
        );
    }

    /**
     * Registra criação da OS
     */
    public function registrarCriacao(int $osId): bool
    {
        return $this->registrar(
            $osId,
            'OS criada',
            'outro',
            null,
            ['data_criacao' => date('Y-m-d H:i:s')]
        );
    }

    /**
     * Registra edição de dados da OS
     */
    public function registrarEdicao(int $osId, array $camposAlterados): bool
    {
        return $this->registrar(
            $osId,
            'Dados da OS editados: ' . implode(', ', $camposAlterados),
            'dados',
            null,
            ['campos' => $camposAlterados]
        );
    }
}
