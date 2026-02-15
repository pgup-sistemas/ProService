<?php
/**
 * View para Calendário de OS
 */

// Função helper para cor do status
function getStatusColor(string $status): string {
    return match($status) {
        'aberta' => 'secondary',
        'em_orcamento' => 'info',
        'aprovada' => 'warning',
        'em_execucao' => 'primary',
        'pausada' => 'dark',
        'finalizada' => 'success',
        'paga' => 'success',
        'cancelada' => 'danger',
        default => 'secondary'
    };
}

// Primeiro dia do mês
$primeiroDiaSemana = date('w', strtotime("{$ano}-{$mes}-01"));
$ultimoDiaMes = date('t', strtotime("{$ano}-{$mes}-01"));
$diasSemana = ['Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb'];

// Contar total de OS no período
$totalOS = 0;
foreach ($ordensPorDia as $osDia) {
    $totalOS += count($osDia);
}
?>

<?= breadcrumb(['Dashboard' => 'dashboard', 'Ordens de Serviço' => 'ordens', 'Calendário']) ?>

<!-- Header Compacto -->
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="mb-0"><i class="bi bi-calendar3"></i> Calendário de OS</h5>
    <a href="<?= url('ordens/create') ?>" class="btn btn-primary btn-sm">
        <i class="bi bi-plus-lg"></i> Nova OS
    </a>
</div>

<!-- Card Principal com Navegação e Filtros -->
<div class="card mb-3">
    <!-- Navegação do Mês -->
    <div class="card-header bg-white py-2 border-bottom">
        <div class="d-flex justify-content-between align-items-center">
            <a href="<?= url('ordens/calendario?mes=' . $mesAnterior . '&ano=' . $anoAnterior . '&' . http_build_query(array_diff_key($filtros, array_flip(['mes', 'ano']))) ) ?>" 
               class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-chevron-left"></i>
            </a>
            <h5 class="mb-0 fw-bold text-primary"><?= $nomeMes ?></h5>
            <a href="<?= url('ordens/calendario?mes=' . $mesProximo . '&ano=' . $anoProximo . '&' . http_build_query(array_diff_key($filtros, array_flip(['mes', 'ano']))) ) ?>" 
               class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-chevron-right"></i>
            </a>
        </div>
    </div>

    <!-- Filtros -->
    <div class="card-body py-3">
        <form method="GET" action="<?= url('ordens/calendario') ?>" id="filtrosForm">
            <input type="hidden" name="mes" value="<?= $mes ?>">
            <input type="hidden" name="ano" value="<?= $ano ?>">
            
            <div class="row g-3 mb-3">
                <div class="col-6 col-md-3">
                    <label class="form-label small text-muted mb-1"><i class="bi bi-pie-chart-fill me-1"></i> Status</label>
                    <select name="status" class="form-select" onchange="this.form.submit()">
                        <option value="">Todos Status</option>
                        <option value="aberta" <?= ($filtros['status'] ?? '') === 'aberta' ? 'selected' : '' ?>>Aberta</option>
                        <option value="em_orcamento" <?= ($filtros['status'] ?? '') === 'em_orcamento' ? 'selected' : '' ?>>Em Orçamento</option>
                        <option value="aprovada" <?= ($filtros['status'] ?? '') === 'aprovada' ? 'selected' : '' ?>>Aprovada</option>
                        <option value="em_execucao" <?= ($filtros['status'] ?? '') === 'em_execucao' ? 'selected' : '' ?>>Em Execução</option>
                        <option value="pausada" <?= ($filtros['status'] ?? '') === 'pausada' ? 'selected' : '' ?>>Pausada</option>
                        <option value="finalizada" <?= ($filtros['status'] ?? '') === 'finalizada' ? 'selected' : '' ?>>Finalizada</option>
                        <option value="paga" <?= ($filtros['status'] ?? '') === 'paga' ? 'selected' : '' ?>>Paga</option>
                    </select>
                </div>
                <div class="col-6 col-md-3">
                    <label class="form-label small text-muted mb-1"><i class="bi bi-people-fill me-1"></i> Cliente</label>
                    <select name="cliente_id" class="form-select" onchange="this.form.submit()">
                        <option value="">Todos Clientes</option>
                        <?php foreach ($clientes as $cliente): ?>
                        <option value="<?= $cliente['id'] ?>" <?= ($filtros['cliente_id'] ?? '') == $cliente['id'] ? 'selected' : '' ?>>
                            <?= e(substr($cliente['nome'], 0, 20)) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-6 col-md-3">
                    <label class="form-label small text-muted mb-1"><i class="bi bi-person-badge-fill me-1"></i> Técnico</label>
                    <select name="tecnico_id" class="form-select" onchange="this.form.submit()">
                        <option value="">Todos Técnicos</option>
                        <?php foreach ($tecnicos as $tecnico): ?>
                        <option value="<?= $tecnico['id'] ?>" <?= ($filtros['tecnico_id'] ?? '') == $tecnico['id'] ? 'selected' : '' ?>>
                            <?= e($tecnico['nome']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-6 col-md-3">
                    <label class="form-label small text-muted mb-1"><i class="bi bi-calendar3 me-1"></i> Período</label>
                    <select name="tipo_data" class="form-select" onchange="this.form.submit()">
                        <option value="previsao_entrega" <?= ($filtros['tipo_data'] ?? '') === 'previsao_entrega' ? 'selected' : '' ?>>Por Previsão</option>
                        <option value="data_entrada" <?= ($filtros['tipo_data'] ?? '') === 'data_entrada' ? 'selected' : '' ?>>Por Entrada</option>
                    </select>
                </div>
            </div>
            
            <div class="row g-3 align-items-end">
                <div class="col-md-10">
                    <label class="form-label small text-muted mb-1"><i class="bi bi-search me-1"></i> Buscar</label>
                    <input type="text" name="busca" class="form-control" 
                           value="<?= $filtros['busca'] ?? '' ?>" placeholder="OS, cliente ou serviço...">
                </div>
                <div class="col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-primary flex-fill">
                        <i class="bi bi-search me-1"></i> Buscar
                    </button>
                    <?php if (array_filter($filtros)): ?>
                    <a href="<?= url('ordens/calendario?mes=' . $mes . '&ano=' . $ano) ?>" class="btn btn-outline-secondary" title="Limpar filtros">
                        <i class="bi bi-x-lg"></i>
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        </form>
    </div>
    
    <!-- Resumo -->
    <div class="card-footer bg-light py-2">
        <small class="text-muted d-flex align-items-center gap-2">
            <span class="badge bg-primary"><i class="bi bi-clipboard-data me-1"></i><?= $totalOS ?> OS</span>
            <?php if (array_filter($filtros)): ?>
                <span><i class="bi bi-funnel-fill text-primary me-1"></i>Filtros ativos</span>
            <?php endif; ?>
        </small>
    </div>
</div>

<!-- Calendário -->
<div class="card">
    <div class="card-body p-2">
        <!-- Dias da Semana -->
        <div class="row text-center fw-bold mb-2 gx-1">
            <?php foreach ($diasSemana as $dia): ?>
            <div class="col">
                <small class="text-muted"><?= $dia ?></small>
            </div>
            <?php endforeach; ?>
        </div>
        
        <!-- Dias do Mês -->
        <div class="row gx-1 gy-1">
            <?php 
            // Espaços vazios antes do primeiro dia
            for ($i = 0; $i < $primeiroDiaSemana; $i++): 
            ?>
            <div class="col" style="min-height: 90px;"></div>
            <?php endfor; ?>
            
            <?php for ($dia = 1; $dia <= $ultimoDiaMes; $dia++): 
                $dataAtual = sprintf('%04d-%02d-%02d', $ano, $mes, $dia);
                $osDoDia = $ordensPorDia[$dataAtual] ?? [];
                $hoje = date('Y-m-d') === $dataAtual;
            ?>
            <div class="col p-1" style="min-height: 90px;">
                <div class="h-100 border rounded p-1 <?= $hoje ? 'border-primary bg-light' : 'bg-white' ?>" 
                     style="cursor: <?= !empty($osDoDia) ? 'pointer' : 'default' ?>;"
                     <?= !empty($osDoDia) ? 'onclick="mostrarOS(\'' . $dataAtual . '\')"' : '' ?>>
                    
                    <!-- Cabeçalho do Dia -->
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="badge <?= $hoje ? 'bg-primary' : 'bg-light text-dark' ?>" style="font-size: 0.7rem;">
                            <?= $dia ?>
                        </span>
                        <?php if (!empty($osDoDia)): ?>
                        <span class="badge bg-danger rounded-pill" style="font-size: 0.6rem;">
                            <?= count($osDoDia) ?>
                        </span>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Lista de OS -->
                    <div class="os-list" style="max-height: 70px; overflow-y: auto;">
                        <?php foreach (array_slice($osDoDia, 0, 3) as $os): ?>
                        <a href="<?= url('ordens/show/' . $os['id']) ?>" 
                           class="badge bg-<?= getStatusColor($os['status']) ?> d-block mb-1 text-decoration-none text-white" 
                           style="font-size: 0.65rem; padding: 2px 4px;"
                           title="#<?= str_pad($os['numero_os'], 4, '0', STR_PAD_LEFT) ?> - <?= e($os['cliente_nome']) ?>"
                           onclick="event.stopPropagation()">
                            #<?= str_pad($os['numero_os'], 4, '0', STR_PAD_LEFT) ?>
                        </a>
                        <?php endforeach; ?>
                        
                        <?php if (count($osDoDia) > 3): ?>
                        <small class="text-muted d-block text-center" style="font-size: 0.6rem;">
                            +<?= count($osDoDia) - 3 ?> mais
                        </small>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <?php 
            // Quebra de linha após sábado
            if (($primeiroDiaSemana + $dia) % 7 == 0): 
            ?>
        </div>
        <div class="row gx-1 gy-1">
            <?php endif; ?>
            <?php endfor; ?>
        </div>
    </div>
</div>

<!-- Legenda -->
<div class="card mt-3">
    <div class="card-body py-3">
        <div class="d-flex flex-wrap gap-2 justify-content-center align-items-center">
            <span class="badge bg-secondary"><i class="bi bi-folder me-1"></i>Aberta</span>
            <span class="badge bg-info"><i class="bi bi-calculator me-1"></i>Em Orçamento</span>
            <span class="badge bg-warning"><i class="bi bi-check2-circle me-1"></i>Aprovada</span>
            <span class="badge bg-primary"><i class="bi bi-gear-wide-connected me-1"></i>Em Execução</span>
            <span class="badge bg-dark"><i class="bi bi-pause-circle me-1"></i>Pausada</span>
            <span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>Finalizada</span>
            <span class="badge bg-success"><i class="bi bi-cash-coin me-1"></i>Paga</span>
            <span class="badge bg-danger"><i class="bi bi-x-circle me-1"></i>Cancelada</span>
        </div>
    </div>
</div>

<!-- Lista de OS do Período (Colapsável) -->
<div class="card mt-3">
    <div class="card-header bg-white py-3" data-bs-toggle="collapse" data-bs-target="#listaOS" style="cursor: pointer;">
        <div class="d-flex justify-content-between align-items-center">
            <h6 class="mb-0"><i class="bi bi-list"></i> Lista de OS do Período</h6>
            <i class="bi bi-chevron-down"></i>
        </div>
    </div>
    <div class="collapse show" id="listaOS">
        <div class="card-body p-0">
            <?php if ($totalOS > 0): ?>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="py-3">OS</th>
                            <th class="py-3">Data</th>
                            <th class="py-3">Cliente</th>
                            <th class="py-3">Serviço</th>
                            <th class="py-3">Técnico</th>
                            <th class="py-3">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        ksort($ordensPorDia);
                        foreach ($ordensPorDia as $data => $osDia): 
                            foreach ($osDia as $os):
                        ?>
                        <tr>
                            <td class="py-3">
                                <a href="<?= url('ordens/show/' . $os['id']) ?>" class="fw-bold text-decoration-none">
                                    #<?= str_pad($os['numero_os'], 4, '0', STR_PAD_LEFT) ?>
                                </a>
                            </td>
                            <td class="py-3"><?= date('d/m', strtotime($data)) ?></td>
                            <td class="py-3"><?= e(substr($os['cliente_nome'], 0, 25)) ?></td>
                            <td class="py-3"><?= e(substr($os['servico_nome'] ?? 'N/A', 0, 25)) ?></td>
                            <td class="py-3"><?= e(substr($os['tecnico_nome'] ?? 'N/A', 0, 20)) ?></td>
                            <td class="py-3">
                                <span class="badge bg-<?= getStatusColor($os['status']) ?>">
                                    <i class="bi <?= getStatusIcon($os['status']) ?> me-1"></i><?= getStatusLabel($os['status']) ?>
                                </span>
                            </td>
                        </tr>
                        <?php 
                            endforeach;
                        endforeach; 
                        ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
            <div class="text-center py-4">
                <i class="bi bi-calendar-x text-muted" style="font-size: 2rem;"></i>
                <p class="text-muted mb-0 mt-2">Nenhuma OS encontrada para este período.</p>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>
