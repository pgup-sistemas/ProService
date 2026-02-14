<?php
/**
 * proService - Model Empresa
 * Arquivo: /app/models/Empresa.php
 */

namespace App\Models;

class Empresa extends Model
{
    protected string $table = 'empresas';
    protected bool $useEmpresaFilter = false; // Desativa filtro empresa_id para tabela empresas

    /**
     * Busca empresa por email
     */
    public function findByEmail(string $email): ?array
    {
        return $this->findBy('email', $email);
    }

    /**
     * Busca empresa por CNPJ/CPF
     */
    public function findByCnpjCpf(string $cnpjCpf): ?array
    {
        // Remove formatação
        $cnpjCpf = preg_replace('/\D/', '', $cnpjCpf);
        
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE REPLACE(REPLACE(REPLACE(cnpj_cpf, '.', ''), '-', ''), '/', '') = ? LIMIT 1");
        $stmt->execute([$cnpjCpf]);
        
        $result = $stmt->fetch();
        return $result ?: null;
    }

    /**
     * Cria nova empresa com trial
     */
    public function criarComTrial(array $data): ?int
    {
        $data['plano'] = 'trial';
        $data['data_inicio_plano'] = date('Y-m-d');
        $data['data_fim_trial'] = date('Y-m-d', strtotime('+15 days'));
        $data['limite_os_mes'] = PLANO_TRIAL_OS;
        $data['limite_tecnicos'] = PLANO_TRIAL_TECNICOS;
        $data['limite_armazenamento_mb'] = PLANO_TRIAL_ARMAZENAMENTO;
        $data['mes_referencia_os'] = date('Y-m');
        
        return $this->create($data);
    }

    /**
     * Verifica se trial expirou
     */
    public function trialExpirado(int $empresaId): bool
    {
        $empresa = $this->findById($empresaId);
        
        if (!$empresa || $empresa['plano'] !== 'trial') {
            return false;
        }
        
        return $empresa['data_fim_trial'] < date('Y-m-d');
    }

    /**
     * Atualiza plano da empresa
     */
    public function atualizarPlano(int $empresaId, string $plano): bool
    {
        $dadosPlano = $this->getDadosPlano($plano);
        
        return $this->update($empresaId, [
            'plano' => $plano,
            'limite_os_mes' => $dadosPlano['limite_os'],
            'limite_tecnicos' => $dadosPlano['limite_tecnicos'],
            'limite_armazenamento_mb' => $dadosPlano['limite_armazenamento']
        ]);
    }

    /**
     * Retorna dados do plano
     */
    public function getDadosPlano(string $plano): array
    {
        // Planos pagos têm acesso total
        $planosPagos = ['starter', 'pro', 'business'];
        $isPago = in_array($plano, $planosPagos);
        
        $planos = [
            'trial' => [
                'nome' => 'Trial',
                'limite_os' => 20,
                'limite_tecnicos' => 1,
                'limite_armazenamento' => 100,
                'preco' => 0,
                'pago' => false
            ],
            'starter' => [
                'nome' => 'Starter',
                'limite_os' => PLANO_STARTER_OS,
                'limite_tecnicos' => PLANO_STARTER_TECNICOS,
                'limite_armazenamento' => PLANO_STARTER_ARMAZENAMENTO,
                'preco' => PLANO_STARTER_PRECO,
                'pago' => true
            ],
            'pro' => [
                'nome' => 'Pro',
                'limite_os' => PLANO_PRO_OS,
                'limite_tecnicos' => PLANO_PRO_TECNICOS,
                'limite_armazenamento' => PLANO_PRO_ARMAZENAMENTO,
                'preco' => PLANO_PRO_PRECO,
                'pago' => true
            ],
            'business' => [
                'nome' => 'Business',
                'limite_os' => PLANO_BUSINESS_OS,
                'limite_tecnicos' => PLANO_BUSINESS_TECNICOS,
                'limite_armazenamento' => PLANO_BUSINESS_ARMAZENAMENTO,
                'preco' => PLANO_BUSINESS_PRECO,
                'pago' => true
            ]
        ];
        
        return $planos[$plano] ?? $planos['trial'];
    }

    /**
     * Verifica se empresa está em plano pago (acesso total)
     */
    public function isPlanoPago(int $empresaId): bool
    {
        $empresa = $this->findById($empresaId);
        if (!$empresa) {
            return false;
        }

        $dadosPlano = $this->getDadosPlano($empresa['plano'] ?? 'trial');
        return $dadosPlano['pago'] ?? false;
    }

    /**
     * Verifica se tem acesso a recurso (planos pagos = tudo liberado)
     */
    public function temAcessoRecurso(int $empresaId, string $recurso = null): bool
    {
        // Se for plano pago, libera qualquer recurso
        if ($this->isPlanoPago($empresaId)) {
            return true;
        }

        // Trial/gratuito - verifica limites específicos
        return match($recurso) {
            'relatorios_avancados', 'logs_sistema', 'backup_automatico' => false,
            default => true
        };
    }

    /**
     * Atualiza logo da empresa
     */
    public function atualizarLogo(int $empresaId, string $logoPath): bool
    {
        return $this->update($empresaId, ['logo' => $logoPath]);
    }

    /**
     * Incrementa contador de OS do mês
     */
    public function incrementarOS(int $empresaId): bool
    {
        $empresa = $this->findById($empresaId);
        
        if (!$empresa) {
            return false;
        }
        
        $mesAtual = date('Y-m');
        
        // Se mudou o mês, reseta o contador
        if ($empresa['mes_referencia_os'] !== $mesAtual) {
            return $this->update($empresaId, [
                'os_criadas_mes_atual' => 1,
                'mes_referencia_os' => $mesAtual
            ]);
        }
        
        // Incrementa
        $stmt = $this->db->prepare("UPDATE {$this->table} SET os_criadas_mes_atual = os_criadas_mes_atual + 1 WHERE id = ?");
        return $stmt->execute([$empresaId]);
    }

    /**
     * Verifica se atingiu limite de técnicos
     */
    public function verificarLimiteTecnicos(int $empresaId): bool
    {
        $empresa = $this->findById($empresaId);
        
        if (!$empresa || $empresa['limite_tecnicos'] === -1) {
            return true; // Ilimitado
        }
        
        // Conta técnicos ativos
        $stmt = $this->db->prepare("SELECT COUNT(*) as total FROM usuarios WHERE empresa_id = ? AND perfil = 'tecnico' AND ativo = 1");
        $stmt->execute([$empresaId]);
        $total = (int) $stmt->fetch()['total'];
        
        return $total < $empresa['limite_tecnicos'];
    }

    /**
     * Verifica se pode criar nova OS (dentro do limite mensal)
     */
    public function podeCriarOS(int $empresaId): array
    {
        $empresa = $this->findById($empresaId);
        
        if (!$empresa) {
            return ['permitido' => false, 'motivo' => 'Empresa não encontrada'];
        }
        
        // Se for ilimitado (-1), permite
        if ($empresa['limite_os_mes'] === -1) {
            return ['permitido' => true, 'restante' => -1];
        }
        
        // Verifica se mudou o mês
        $mesAtual = date('Y-m');
        if ($empresa['mes_referencia_os'] !== $mesAtual) {
            // Reseta contador automaticamente
            $this->update($empresaId, [
                'os_criadas_mes_atual' => 0,
                'mes_referencia_os' => $mesAtual
            ]);
            $empresa['os_criadas_mes_atual'] = 0;
        }
        
        $criadas = (int) ($empresa['os_criadas_mes_atual'] ?? 0);
        $limite = (int) $empresa['limite_os_mes'];
        $restante = $limite - $criadas;
        
        return [
            'permitido' => $restante > 0,
            'criadas' => $criadas,
            'limite' => $limite,
            'restante' => max(0, $restante),
            'motivo' => $restante <= 0 ? 'Limite de OS do mês atingido' : null
        ];
    }

    /**
     * Retorna estatísticas para dashboard
     */
    public function getEstatisticas(int $empresaId): array
    {
        $stats = [];
        
        // Total de clientes
        $stmt = $this->db->prepare("SELECT COUNT(*) as total FROM clientes WHERE empresa_id = ?");
        $stmt->execute([$empresaId]);
        $stats['total_clientes'] = (int) $stmt->fetch()['total'];
        
        // Total de OS no mês
        $stmt = $this->db->prepare("SELECT COUNT(*) as total FROM ordens_servico WHERE empresa_id = ? AND DATE_FORMAT(created_at, '%Y-%m') = ?");
        $stmt->execute([$empresaId, date('Y-m')]);
        $stats['os_mes'] = (int) $stmt->fetch()['total'];
        
        // OS por status
        $stmt = $this->db->prepare("SELECT status, COUNT(*) as total FROM ordens_servico WHERE empresa_id = ? GROUP BY status");
        $stmt->execute([$empresaId]);
        $stats['os_por_status'] = $stmt->fetchAll();
        
        // Produtos em falta
        $stmt = $this->db->prepare("SELECT COUNT(*) as total FROM produtos WHERE empresa_id = ? AND quantidade_estoque <= quantidade_minima");
        $stmt->execute([$empresaId]);
        $stats['produtos_falta'] = (int) $stmt->fetch()['total'];
        
        return $stats;
    }
}
