<?php
/**
 * proService - Model OrdemServico
 * Arquivo: /app/models/OrdemServico.php
 */

namespace App\Models;

class OrdemServico extends Model
{
    protected string $table = 'ordens_servico';

    /**
     * Busca OS com dados completos (cliente, serviço, técnico)
     */
    public function findComplete(int $id): ?array
    {
        $stmt = $this->db->prepare("
            SELECT os.*, 
                   c.nome as cliente_nome, c.telefone as cliente_telefone, c.whatsapp as cliente_whatsapp, c.email as cliente_email,
                   c.cpf_cnpj as cliente_cpf_cnpj, c.endereco as cliente_endereco, c.numero as cliente_numero,
                   c.bairro as cliente_bairro, c.cidade as cliente_cidade, c.estado as cliente_estado,
                   s.nome as servico_nome, s.garantia_dias as servico_garantia_padrao,
                   u.nome as tecnico_nome
            FROM {$this->table} os
            LEFT JOIN clientes c ON os.cliente_id = c.id
            LEFT JOIN servicos s ON os.servico_id = s.id
            LEFT JOIN usuarios u ON os.tecnico_id = u.id
            WHERE os.id = ? AND os.empresa_id = ?
            LIMIT 1
        ");
        $stmt->execute([$id, $this->empresaId]);
        
        return $stmt->fetch() ?: null;
    }

    /**
     * Busca OS pelo número
     */
    public function findByNumero(int $numero): ?array
    {
        return $this->findBy('numero_os', $numero);
    }

    /**
     * Busca OS pelo token público
     */
    public function findByToken(string $token): ?array
    {
        $stmt = $this->db->prepare("
            SELECT os.*, 
                   c.nome as cliente_nome,
                   s.nome as servico_nome,
                   e.nome_fantasia as empresa_nome, e.telefone as empresa_telefone, 
                   e.endereco as empresa_endereco, e.logo as empresa_logo
            FROM {$this->table} os
            LEFT JOIN clientes c ON os.cliente_id = c.id
            LEFT JOIN servicos s ON os.servico_id = s.id
            LEFT JOIN empresas e ON os.empresa_id = e.id
            WHERE os.token_publico = ?
            LIMIT 1
        ");
        $stmt->execute([$token]);
        
        return $stmt->fetch() ?: null;
    }

    /**
     * Lista OS com filtros
     */
    public function listar(array $filtros = [], string $orderBy = 'os.created_at DESC', int $page = 1, int $perPage = 20): array
    {
        $where = 'os.empresa_id = ?';
        $params = [$this->empresaId];
        
        // Aplicar filtros
        if (!empty($filtros['status'])) {
            $where .= ' AND os.status = ?';
            $params[] = $filtros['status'];
        }
        
        if (!empty($filtros['cliente_id'])) {
            $where .= ' AND os.cliente_id = ?';
            $params[] = $filtros['cliente_id'];
        }
        
        if (!empty($filtros['tecnico_id'])) {
            $where .= ' AND os.tecnico_id = ?';
            $params[] = $filtros['tecnico_id'];
        }
        
        if (!empty($filtros['prioridade'])) {
            $where .= ' AND os.prioridade = ?';
            $params[] = $filtros['prioridade'];
        }
        
        if (!empty($filtros['data_inicio'])) {
            $where .= ' AND os.data_entrada >= ?';
            $params[] = $filtros['data_inicio'];
        }
        
        if (!empty($filtros['data_fim'])) {
            $where .= ' AND os.data_entrada <= ?';
            $params[] = $filtros['data_fim'];
        }
        
        if (!empty($filtros['busca'])) {
            $where .= ' AND (os.numero_os LIKE ? OR c.nome LIKE ?)';
            $termo = '%' . $filtros['busca'] . '%';
            $params[] = $termo;
            $params[] = $termo;
        }
        
        // Contar total
        $sqlCount = "SELECT COUNT(*) as total FROM {$this->table} os LEFT JOIN clientes c ON os.cliente_id = c.id WHERE {$where}";
        $stmt = $this->db->prepare($sqlCount);
        $stmt->execute($params);
        $total = (int) $stmt->fetch()['total'];
        
        // Buscar registros
        $offset = ($page - 1) * $perPage;
        $sql = "
            SELECT os.*, c.nome as cliente_nome, u.nome as tecnico_nome, s.nome as servico_nome
            FROM {$this->table} os
            LEFT JOIN clientes c ON os.cliente_id = c.id
            LEFT JOIN usuarios u ON os.tecnico_id = u.id
            LEFT JOIN servicos s ON os.servico_id = s.id
            WHERE {$where}
            ORDER BY {$orderBy}
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
     * Cria nova OS
     */
    public function criar(array $data, array $produtos = []): ?int
    {
        // Iniciar transação
        $this->db->beginTransaction();
        
        try {
            // Gerar número da OS por empresa (evita duplicidade na uk_empresa_numero_os)
            if (empty($data['numero_os'])) {
                if (empty($this->empresaId)) {
                    throw new \Exception('empresa_id ausente no model ao gerar numero_os');
                }

                $stmt = $this->db->prepare("SELECT MAX(numero_os) AS max_numero FROM {$this->table} WHERE empresa_id = ? FOR UPDATE");
                $stmt->execute([$this->empresaId]);
                $row = $stmt->fetch();
                $maxNumero = (int) ($row['max_numero'] ?? 0);
                $data['numero_os'] = $maxNumero + 1;
            }

            // Processar produtos primeiro para calcular totais
            $custoTotalProdutos = 0;
            $precoTotalProdutos = 0;
            $produtosProcessados = [];
            
            foreach ($produtos as $produto) {
                $produtoId = $produto['produto_id'];
                $quantidade = $produto['quantidade'];
                
                // Buscar produto
                $stmt = $this->db->prepare("SELECT * FROM produtos WHERE id = ? AND empresa_id = ?");
                $stmt->execute([$produtoId, $this->empresaId]);
                $prodData = $stmt->fetch();
                
                if (!$prodData) {
                    continue;
                }
                
                // Verificar estoque
                if ($prodData['quantidade_estoque'] < $quantidade) {
                    throw new \Exception("Estoque insuficiente para: " . $prodData['nome']);
                }
                
                $custoUnitario = $prodData['custo_unitario'] ?? 0;
                $precoUnitario = $prodData['preco_venda'] ?? $custoUnitario;
                $custoTotal = $custoUnitario * $quantidade;
                $precoTotal = $precoUnitario * $quantidade;
                
                $custoTotalProdutos += $custoTotal;
                $precoTotalProdutos += $precoTotal;
                
                $produtosProcessados[] = [
                    'produto_id' => $produtoId,
                    'quantidade' => $quantidade,
                    'custo_unitario' => $custoUnitario,
                    'custo_total' => $custoTotal,
                    'preco_unitario' => $precoUnitario,
                    'preco_total' => $precoTotal
                ];
            }
            
            // Calcular valor_total incluindo preço dos produtos
            $data['valor_total'] = 
                ($data['valor_servico'] ?? 0) + 
                ($data['taxas_adicionais'] ?? 0) + 
                $precoTotalProdutos - 
                ($data['desconto'] ?? 0);
            
            // Criar OS
            $osId = $this->create($data);
            
            if (!$osId) {
                throw new \Exception("Erro ao criar OS");
            }
            
            // Inserir produtos na OS
            foreach ($produtosProcessados as $p) {
                $stmt = $this->db->prepare("
                    INSERT INTO os_produtos (os_id, produto_id, quantidade, custo_unitario, custo_total, preco_unitario, preco_total)
                    VALUES (?, ?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([
                    $osId, 
                    $p['produto_id'], 
                    $p['quantidade'], 
                    $p['custo_unitario'], 
                    $p['custo_total'],
                    $p['preco_unitario'],
                    $p['preco_total']
                ]);
            }
            
            // Atualizar custo e lucro da OS
            $lucroReal = $data['valor_total'] - $custoTotalProdutos;
            $stmt = $this->db->prepare("
                UPDATE {$this->table} 
                SET custo_produtos = ?, lucro_real = ? 
                WHERE id = ?
            ");
            $stmt->execute([$custoTotalProdutos, $lucroReal, $osId]);
            
            $this->db->commit();
            return $osId;
            
        } catch (\Exception $e) {
            $this->db->rollBack();
            error_log("Erro ao criar OS: " . $e->getMessage());
            if (defined('ENVIRONMENT') && ENVIRONMENT === 'development') {
                throw $e;
            }
            return null;
        }
    }

    /**
     * Atualiza status da OS
     */
    public function atualizarStatus(int $osId, string $novoStatus, ?int $usuarioId = null): bool
    {
        $os = $this->findById($osId);
        if (!$os) {
            return false;
        }
        
        $statusAnterior = $os['status'];
        $dadosAtualizacao = ['status' => $novoStatus];
        
        // Se finalizou, registra data
        if ($novoStatus === 'finalizada' && $statusAnterior !== 'finalizada') {
            $dadosAtualizacao['data_finalizacao'] = date('Y-m-d');
        }
        
        // Se pagou, atualiza receita
        if ($novoStatus === 'paga' && $statusAnterior !== 'paga') {
            $stmt = $this->db->prepare("
                UPDATE receitas SET status = 'recebido', data_recebimento = ?
                WHERE os_id = ? AND empresa_id = ?
            ");
            $stmt->execute([date('Y-m-d'), $osId, $this->empresaId]);
        }
        
        // Registrar log
        logAuditoria(
            "Status alterado de '{$statusAnterior}' para '{$novoStatus}'",
            $osId,
            ['status' => $statusAnterior],
            ['status' => $novoStatus]
        );
        
        return $this->update($osId, $dadosAtualizacao);
    }

    /**
     * Atualiza OS completa (dados + produtos)
     */
    public function atualizar(int $osId, array $data, array $produtos = []): bool
    {
        $os = $this->findById($osId);
        if (!$os) {
            return false;
        }
        
        $this->db->beginTransaction();
        
        try {
            // Processar produtos - excluir antigos e inserir novos
            $stmt = $this->db->prepare("DELETE FROM os_produtos WHERE os_id = ?");
            $stmt->execute([$osId]);
            
            $custoTotalProdutos = 0;
            $precoTotalProdutos = 0;
            
            foreach ($produtos as $produto) {
                $produtoId = $produto['produto_id'];
                $quantidade = $produto['quantidade'];
                
                // Buscar produto
                $stmt = $this->db->prepare("SELECT * FROM produtos WHERE id = ? AND empresa_id = ?");
                $stmt->execute([$produtoId, $this->empresaId]);
                $prodData = $stmt->fetch();
                
                if (!$prodData) {
                    continue;
                }
                
                // Verificar estoque
                if ($prodData['quantidade_estoque'] < $quantidade) {
                    throw new \Exception("Estoque insuficiente para: " . $prodData['nome']);
                }
                
                $custoUnitario = $prodData['custo_unitario'] ?? 0;
                $precoUnitario = $prodData['preco_venda'] ?? $custoUnitario;
                $custoTotal = $custoUnitario * $quantidade;
                $precoTotal = $precoUnitario * $quantidade;
                
                $custoTotalProdutos += $custoTotal;
                $precoTotalProdutos += $precoTotal;
                
                // Inserir na tabela os_produtos
                $stmt = $this->db->prepare("
                    INSERT INTO os_produtos (os_id, produto_id, quantidade, custo_unitario, custo_total, preco_unitario, preco_total)
                    VALUES (?, ?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([$osId, $produtoId, $quantidade, $custoUnitario, $custoTotal, $precoUnitario, $precoTotal]);
            }
            
            // Calcular valor_total incluindo preço dos produtos
            $data['valor_total'] = 
                ($data['valor_servico'] ?? 0) + 
                ($data['taxas_adicionais'] ?? 0) + 
                $precoTotalProdutos - 
                ($data['desconto'] ?? 0);
            
            // Atualizar OS
            $data['custo_produtos'] = $custoTotalProdutos;
            $data['lucro_real'] = $data['valor_total'] - $custoTotalProdutos;
            
            $this->update($osId, $data);
            
            $this->db->commit();
            return true;
            
        } catch (\Exception $e) {
            $this->db->rollBack();
            error_log("Erro ao atualizar OS: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Exclui OS e produtos associados
     */
    public function excluir(int $osId): bool
    {
        $this->db->beginTransaction();
        
        try {
            // Excluir produtos da OS (a trigger estorna o estoque)
            $stmt = $this->db->prepare("DELETE FROM os_produtos WHERE os_id = ?");
            $stmt->execute([$osId]);
            
            // Excluir fotos
            $stmt = $this->db->prepare("DELETE FROM os_fotos WHERE os_id = ?");
            $stmt->execute([$osId]);
            
            // Excluir logs
            $stmt = $this->db->prepare("DELETE FROM os_logs WHERE os_id = ?");
            $stmt->execute([$osId]);
            
            // Excluir receita vinculada (se houver)
            $stmt = $this->db->prepare("DELETE FROM receitas WHERE os_id = ? AND empresa_id = ?");
            $stmt->execute([$osId, $this->empresaId]);
            
            // Excluir OS
            $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE id = ? AND empresa_id = ?");
            $result = $stmt->execute([$osId, $this->empresaId]);
            
            $this->db->commit();
            return $result;
            
        } catch (\Exception $e) {
            $this->db->rollBack();
            error_log("Erro ao excluir OS: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Adiciona produto à OS
     */
    public function adicionarProduto(int $osId, int $produtoId, float $quantidade): bool
    {
        $os = $this->findById($osId);
        if (!$os) {
            return false;
        }
        
        // Buscar produto
        $stmt = $this->db->prepare("SELECT * FROM produtos WHERE id = ? AND empresa_id = ?");
        $stmt->execute([$produtoId, $this->empresaId]);
        $produto = $stmt->fetch();
        
        if (!$produto) {
            return false;
        }
        
        // Verificar estoque
        if ($produto['quantidade_estoque'] < $quantidade) {
            return false;
        }
        
        $custoUnitario = $produto['custo_unitario'] ?? 0;
        $precoUnitario = $produto['preco_venda'] ?? $custoUnitario;
        $custoTotal = $custoUnitario * $quantidade;
        $precoTotal = $precoUnitario * $quantidade;
        
        // Inserir os_produtos - a trigger fará a baixa no estoque
        $stmt = $this->db->prepare("
            INSERT INTO os_produtos (os_id, produto_id, quantidade, custo_unitario, custo_total, preco_unitario, preco_total)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        
        if (!$stmt->execute([$osId, $produtoId, $quantidade, $custoUnitario, $custoTotal, $precoUnitario, $precoTotal])) {
            return false;
        }
        
        // Recalcular custo da OS
        $stmt = $this->db->prepare("SELECT SUM(custo_total) as total FROM os_produtos WHERE os_id = ?");
        $stmt->execute([$osId]);
        $custoTotalProdutos = (float) ($stmt->fetch()['total'] ?? 0);
        
        $lucroReal = $os['valor_total'] - $custoTotalProdutos;
        
        $stmt = $this->db->prepare("
            UPDATE {$this->table} 
            SET custo_produtos = ?, lucro_real = ? 
            WHERE id = ?
        ");
        
        logAuditoria("Produto adicionado: {$produto['nome']} ({$quantidade} un)", $osId);
        
        return $stmt->execute([$custoTotalProdutos, $lucroReal, $osId]);
    }

    /**
     * Remove produto da OS
     */
    public function removerProduto(int $osId, int $osProdutoId): bool
    {
        $stmt = $this->db->prepare("DELETE FROM os_produtos WHERE id = ? AND os_id = ?");
        
        // A trigger fará o estorno no estoque
        return $stmt->execute([$osProdutoId, $osId]);
    }

    /**
     * Lista produtos da OS
     */
    public function getProdutos(int $osId): array
    {
        $stmt = $this->db->prepare("
            SELECT op.*, p.nome as produto_nome, p.codigo_sku
            FROM os_produtos op
            JOIN produtos p ON op.produto_id = p.id
            WHERE op.os_id = ?
        ");
        $stmt->execute([$osId]);
        
        return $stmt->fetchAll();
    }

    /**
     * Lista fotos da OS
     */
    public function getFotos(int $osId): array
    {
        $stmt = $this->db->prepare("SELECT * FROM os_fotos WHERE os_id = ? ORDER BY created_at DESC");
        $stmt->execute([$osId]);
        
        return $stmt->fetchAll();
    }

    /**
     * Adiciona foto à OS
     */
    public function adicionarFoto(int $osId, string $arquivo, string $tipo = 'antes'): bool
    {
        $stmt = $this->db->prepare("INSERT INTO os_fotos (os_id, arquivo, tipo) VALUES (?, ?, ?)");
        return $stmt->execute([$osId, $arquivo, $tipo]);
    }

    /**
     * Busca foto específica pelo ID
     */
    public function getFotoById(int $fotoId): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM os_fotos WHERE id = ?");
        $stmt->execute([$fotoId]);
        $foto = $stmt->fetch();
        return $foto ?: null;
    }

    /**
     * Remove foto da OS
     */
    public function removerFoto(int $fotoId): bool
    {
        $stmt = $this->db->prepare("DELETE FROM os_fotos WHERE id = ?");
        return $stmt->execute([$fotoId]);
    }

    /**
     * Registra assinatura do cliente
     */
    public function registrarAssinaturaCliente(int $osId, string $assinaturaPath): bool
    {
        return $this->update($osId, [
            'assinatura_cliente' => $assinaturaPath,
            'assinatura_data' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Busca OS urgentes
     */
    public function getUrgentes(int $limit = 10): array
    {
        return $this->findAll(
            ['prioridade' => 'urgente', 'status' => ['aberta', 'em_orcamento', 'aprovada', 'em_execucao']],
            'data_entrada ASC',
            $limit
        );
    }

    /**
     * Busca OS atrasadas (previsão vencida)
     */
    public function getAtrasadas(): array
    {
        $stmt = $this->db->prepare("
            SELECT os.*, c.nome as cliente_nome
            FROM {$this->table} os
            LEFT JOIN clientes c ON os.cliente_id = c.id
            WHERE os.empresa_id = ? 
            AND os.previsao_entrega < CURDATE()
            AND os.status NOT IN ('finalizada', 'paga', 'cancelada')
            ORDER BY os.previsao_entrega ASC
        ");
        $stmt->execute([$this->empresaId]);
        
        return $stmt->fetchAll();
    }

    /**
     * Lista OS por período para calendário
     */
    public function listarPorData(string $dataInicio, string $dataFim): array
    {
        $sql = "
            SELECT 
                os.*,
                c.nome as cliente_nome,
                c.telefone as cliente_telefone,
                s.nome as servico_nome,
                u.nome as tecnico_nome
            FROM {$this->table} os
            LEFT JOIN clientes c ON os.cliente_id = c.id
            LEFT JOIN servicos s ON os.servico_id = s.id
            LEFT JOIN usuarios u ON os.tecnico_id = u.id
            WHERE os.empresa_id = ? 
                AND (
                    DATE(os.previsao_entrega) BETWEEN ? AND ?
                    OR DATE(os.data_entrada) BETWEEN ? AND ?
                    OR DATE(os.created_at) BETWEEN ? AND ?
                )
                AND os.status != 'cancelada'
            ORDER BY os.previsao_entrega ASC, os.created_at ASC
        ";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $this->empresaId, 
            $dataInicio, $dataFim,
            $dataInicio, $dataFim,
            $dataInicio, $dataFim
        ]);
        
        return $stmt->fetchAll();
    }

    /**
     * Lista OS por período para calendário com filtros
     */
    public function listarPorDataComFiltros(string $dataInicio, string $dataFim, array $filtros = []): array
    {
        $where = "os.empresa_id = ? AND os.status != 'cancelada'";
        $params = [$this->empresaId];

        // Filtro de data
        $campoData = match($filtros['tipo_data'] ?? 'previsao_entrega') {
            'data_entrada' => 'os.data_entrada',
            default => 'os.previsao_entrega'
        };
        
        $where .= " AND (DATE({$campoData}) BETWEEN ? AND ? OR ({$campoData} IS NULL AND DATE(os.created_at) BETWEEN ? AND ?))";
        $params[] = $dataInicio;
        $params[] = $dataFim;
        $params[] = $dataInicio;
        $params[] = $dataFim;

        // Filtro de status
        if (!empty($filtros['status'])) {
            $where .= " AND os.status = ?";
            $params[] = $filtros['status'];
        }

        // Filtro de cliente
        if (!empty($filtros['cliente_id'])) {
            $where .= " AND os.cliente_id = ?";
            $params[] = (int) $filtros['cliente_id'];
        }

        // Filtro de técnico
        if (!empty($filtros['tecnico_id'])) {
            $where .= " AND os.tecnico_id = ?";
            $params[] = (int) $filtros['tecnico_id'];
        }

        // Filtro de busca por texto
        if (!empty($filtros['busca'])) {
            $busca = '%' . $filtros['busca'] . '%';
            $where .= " AND (os.numero_os LIKE ? OR c.nome LIKE ? OR s.nome LIKE ? OR os.descricao LIKE ?)";
            $params[] = $busca;
            $params[] = $busca;
            $params[] = $busca;
            $params[] = $busca;
        }

        $sql = "
            SELECT 
                os.*,
                c.nome as cliente_nome,
                c.telefone as cliente_telefone,
                s.nome as servico_nome,
                u.nome as tecnico_nome
            FROM {$this->table} os
            LEFT JOIN clientes c ON os.cliente_id = c.id
            LEFT JOIN servicos s ON os.servico_id = s.id
            LEFT JOIN usuarios u ON os.tecnico_id = u.id
            WHERE {$where}
            ORDER BY os.previsao_entrega ASC, os.created_at ASC
        ";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetchAll();
    }

    /**
     * Estatísticas para dashboard
     */
    public function getEstatisticas(): array
    {
        $stats = [];
        
        // Total por status
        $stmt = $this->db->prepare("
            SELECT status, COUNT(*) as total, SUM(valor_total) as valor
            FROM {$this->table}
            WHERE empresa_id = ?
            GROUP BY status
        ");
        $stmt->execute([$this->empresaId]);
        $stats['por_status'] = $stmt->fetchAll();
        
        // OS do mês
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as total, SUM(valor_total) as valor_total, SUM(lucro_real) as lucro
            FROM {$this->table}
            WHERE empresa_id = ? AND DATE_FORMAT(created_at, '%Y-%m') = ?
        ");
        $stmt->execute([$this->empresaId, date('Y-m')]);
        $stats['mes_atual'] = $stmt->fetch();
        
        // OS do dia
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as total, SUM(valor_total) as valor_total
            FROM {$this->table}
            WHERE empresa_id = ? AND DATE(created_at) = CURDATE()
        ");
        $stmt->execute([$this->empresaId]);
        $stats['hoje'] = $stmt->fetch();
        
        return $stats;
    }

    /**
     * Total de OS por técnico
     */
    public function getPorTecnico(string $periodo = 'mes'): array
    {
        $dataFiltro = match($periodo) {
            'hoje' => 'DATE(os.created_at) = CURDATE()',
            'semana' => 'YEARWEEK(os.created_at) = YEARWEEK(CURDATE())',
            default => "DATE_FORMAT(os.created_at, '%Y-%m') = ?"
        };
        
        $sql = "
            SELECT u.nome as tecnico, COUNT(*) as total, SUM(os.valor_total) as valor_total
            FROM {$this->table} os
            LEFT JOIN usuarios u ON os.tecnico_id = u.id
            WHERE os.empresa_id = ? AND {$dataFiltro} AND os.status = 'paga'
            GROUP BY os.tecnico_id
            ORDER BY total DESC
        ";
        
        $params = [$this->empresaId];
        if ($periodo === 'mes') {
            $params[] = date('Y-m');
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetchAll();
    }

    /**
     * Relatório de serviços com filtros avançados
     */
    public function getRelatorioServicos(array $filtros = []): array
    {
        $where = 'os.empresa_id = ?';
        $params = [$this->empresaId];
        
        if (!empty($filtros['data_inicio'])) {
            $where .= ' AND os.data_entrada >= ?';
            $params[] = $filtros['data_inicio'];
        }
        
        if (!empty($filtros['data_fim'])) {
            $where .= ' AND os.data_entrada <= ?';
            $params[] = $filtros['data_fim'];
        }
        
        if (!empty($filtros['status'])) {
            $where .= ' AND os.status = ?';
            $params[] = $filtros['status'];
        }
        
        if (!empty($filtros['tecnico_id'])) {
            $where .= ' AND os.tecnico_id = ?';
            $params[] = $filtros['tecnico_id'];
        }
        
        if (!empty($filtros['cliente_id'])) {
            $where .= ' AND os.cliente_id = ?';
            $params[] = $filtros['cliente_id'];
        }
        
        if (!empty($filtros['servico_id'])) {
            $where .= ' AND os.servico_id = ?';
            $params[] = $filtros['servico_id'];
        }
        
        $sql = "
            SELECT os.*, 
                   c.nome as cliente_nome,
                   s.nome as servico_nome,
                   u.nome as tecnico_nome
            FROM {$this->table} os
            LEFT JOIN clientes c ON os.cliente_id = c.id
            LEFT JOIN servicos s ON os.servico_id = s.id
            LEFT JOIN usuarios u ON os.tecnico_id = u.id
            WHERE {$where}
            ORDER BY os.data_entrada DESC, os.numero_os DESC
        ";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetchAll();
    }

    /**
     * Conta OS por período
     */
    public function countByPeriod(string $dataInicio, string $dataFim, ?string $status = null): int
    {
        $where = 'empresa_id = ? AND data_entrada BETWEEN ? AND ?';
        $params = [$this->empresaId, $dataInicio, $dataFim];
        
        if ($status) {
            $where .= ' AND status = ?';
            $params[] = $status;
        }
        
        $stmt = $this->db->prepare("SELECT COUNT(*) as total FROM {$this->table} WHERE {$where}");
        $stmt->execute($params);
        
        return (int) ($stmt->fetch()['total'] ?? 0);
    }

    /**
     * Conta OS por status
     */
    public function countByStatus(): array
    {
        $stmt = $this->db->prepare("
            SELECT status, COUNT(*) as total, SUM(valor_total) as valor
            FROM {$this->table}
            WHERE empresa_id = ?
            GROUP BY status
        ");
        $stmt->execute([$this->empresaId]);
        
        $result = [];
        foreach ($stmt->fetchAll() as $row) {
            $result[$row['status']] = $row;
        }
        
        return $result;
    }

    /**
     * Top serviços mais realizados
     */
    public function getTopServicos(int $limit = 5, ?string $dataInicio = null, ?string $dataFim = null): array
    {
        $where = 'os.empresa_id = ?';
        $params = [$this->empresaId];
        
        if ($dataInicio && $dataFim) {
            $where .= ' AND os.data_entrada BETWEEN ? AND ?';
            $params[] = $dataInicio;
            $params[] = $dataFim;
        }
        
        $sql = "
            SELECT s.nome as servico, s.categoria, COUNT(*) as total, SUM(os.valor_total) as valor_total
            FROM {$this->table} os
            JOIN servicos s ON os.servico_id = s.id
            WHERE {$where}
            GROUP BY os.servico_id, s.categoria
            ORDER BY total DESC
            LIMIT {$limit}
        ";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetchAll();
    }

    /**
     * Conta OS criadas no mês atual
     */
    public function countMesAtual(): int
    {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as total 
            FROM {$this->table} 
            WHERE empresa_id = ? 
            AND DATE_FORMAT(created_at, '%Y-%m') = ?
        ");
        $stmt->execute([$this->empresaId, date('Y-m')]);
        
        return (int) ($stmt->fetch()['total'] ?? 0);
    }

    /**
     * Busca global (Ctrl+K) - busca OS por número, cliente, serviço
     */
    public function buscarGlobal(string $query, int $empresaId): array
    {
        $sql = "
            SELECT os.*, c.nome as cliente_nome, s.nome as servico_nome
            FROM {$this->table} os
            LEFT JOIN clientes c ON os.cliente_id = c.id
            LEFT JOIN servicos s ON os.servico_id = s.id
            WHERE os.empresa_id = ? 
            AND (
                os.numero_os LIKE ? 
                OR c.nome LIKE ? 
                OR c.telefone LIKE ?
                OR s.nome LIKE ?
                OR os.descricao LIKE ?
            )
            ORDER BY os.created_at DESC
            LIMIT 10
        ";
        
        $busca = '%' . $query . '%';
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$empresaId, $busca, $busca, $busca, $busca, $busca]);
        
        return $stmt->fetchAll();
    }
}

