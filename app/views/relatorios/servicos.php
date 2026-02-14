<?php
/**
 * proService - Relatório de Serviços
 * Arquivo: /app/views/relatorios/servicos.php
 */
?>

<?= breadcrumb(['Dashboard' => 'dashboard', 'Relatórios' => 'relatorios', 'Serviços']) ?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-0"><i class="bi bi-clipboard-check text-primary"></i> Relatório de Serviços</h2>
            <p class="text-muted mb-0">Análise detalhada de ordens de serviço</p>
        </div>
        <div class="d-flex gap-2">
            <a href="<?= url('relatorios/exportar?tipo=servicos&data_inicio=' . $filtros['data_inicio'] . '&data_fim=' . $filtros['data_fim']) ?>" class="btn btn-success">
                <i class="bi bi-download"></i> Exportar CSV
            </a>
            <a href="<?= url('relatorios/imprimir?tipo=servicos') ?>" class="btn btn-outline-secondary" target="_blank">
                <i class="bi bi-printer"></i> Imprimir
            </a>
        </div>
    </div>

    <!-- Filtros -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="<?= url('relatorios/servicos') ?>">
                <div class="row g-3">
                    <div class="col-md-2">
                        <label class="form-label">Data Início</label>
                        <input type="date" name="data_inicio" class="form-control" value="<?= $filtros['data_inicio'] ?>">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Data Fim</label>
                        <input type="date" name="data_fim" class="form-control" value="<?= $filtros['data_fim'] ?>">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="">Todos</option>
                            <option value="aberta" <?= $filtros['status'] == 'aberta' ? 'selected' : '' ?>>Aberta</option>
                            <option value="em_orcamento" <?= $filtros['status'] == 'em_orcamento' ? 'selected' : '' ?>>Em Orçamento</option>
                            <option value="aprovada" <?= $filtros['status'] == 'aprovada' ? 'selected' : '' ?>>Aprovada</option>
                            <option value="em_execucao" <?= $filtros['status'] == 'em_execucao' ? 'selected' : '' ?>>Em Execução</option>
                            <option value="finalizada" <?= $filtros['status'] == 'finalizada' ? 'selected' : '' ?>>Finalizada</option>
                            <option value="paga" <?= $filtros['status'] == 'paga' ? 'selected' : '' ?>>Paga</option>
                            <option value="cancelada" <?= $filtros['status'] == 'cancelada' ? 'selected' : '' ?>>Cancelada</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Cliente</label>
                        <select name="cliente_id" class="form-select">
                            <option value="">Todos</option>
                            <?php foreach ($clientes as $c): ?>
                            <option value="<?= $c['id'] ?>" <?= $filtros['cliente_id'] == $c['id'] ? 'selected' : '' ?>>
                                <?= e($c['nome']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Técnico</label>
                        <select name="tecnico_id" class="form-select">
                            <option value="">Todos</option>
                            <?php foreach ($tecnicos as $t): ?>
                            <option value="<?= $t['id'] ?>" <?= $filtros['tecnico_id'] == $t['id'] ? 'selected' : '' ?>>
                                <?= e($t['nome']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-12">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-funnel"></i> Filtrar
                        </button>
                        <a href="<?= url('relatorios/servicos') ?>" class="btn btn-outline-secondary">
                            <i class="bi bi-x-circle"></i> Limpar
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Totalizadores -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h6 class="text-white-50">Total de OS</h6>
                    <h3 class="mb-0"><?= number_format($totalOS) ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h6 class="text-white-50">Valor Total</h6>
                    <h3 class="mb-0">R$ <?= number_format($valorTotal, 2, ',', '.') ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-dark">
                <div class="card-body">
                    <h6>Custo Total</h6>
                    <h3 class="mb-0">R$ <?= number_format($custoTotal, 2, ',', '.') ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <h6 class="text-white-50">Lucro / Margem</h6>
                    <h3 class="mb-0">R$ <?= number_format($lucroTotal, 2, ',', '.') ?></h3>
                    <small class="text-white-50"><?= number_format($margemMedia, 1) ?>% margem</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabela de OS -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Ordens de Serviço</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Nº OS</th>
                            <th>Data</th>
                            <th>Cliente</th>
                            <th>Serviço</th>
                            <th>Técnico</th>
                            <th>Status</th>
                            <th class="text-end">Valor</th>
                            <th class="text-end">Custo</th>
                            <th class="text-end">Lucro</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($ordens)): ?>
                        <tr>
                            <td colspan="9" class="text-center py-4 text-muted">
                                <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                Nenhuma OS encontrada para os filtros selecionados
                            </td>
                        </tr>
                        <?php else: ?>
                        <?php foreach ($ordens as $os): 
                            $statusClass = [
                                'aberta' => 'bg-secondary',
                                'em_orcamento' => 'bg-info',
                                'aprovada' => 'bg-warning',
                                'em_execucao' => 'bg-primary',
                                'pausada' => 'bg-dark',
                                'finalizada' => 'bg-success',
                                'paga' => 'bg-success',
                                'cancelada' => 'bg-danger'
                            ][$os['status']] ?? 'bg-secondary';
                        ?>
                        <tr>
                            <td>
                                <a href="<?= url('ordens/show/' . $os['id']) ?>" class="fw-bold text-decoration-none">
                                    #<?= str_pad($os['numero_os'], 5, '0', STR_PAD_LEFT) ?>
                                </a>
                            </td>
                            <td><?= date('d/m/Y', strtotime($os['data_entrada'])) ?></td>
                            <td><?= e($os['cliente_nome']) ?></td>
                            <td><?= e($os['servico_nome']) ?></td>
                            <td><?= e($os['tecnico_nome'] ?? 'Não atribuído') ?></td>
                            <td>
                                <span class="badge <?= $statusClass ?>">
                                    <?= str_replace('_', ' ', $os['status']) ?>
                                </span>
                            </td>
                            <td class="text-end">R$ <?= number_format($os['valor_total'], 2, ',', '.') ?></td>
                            <td class="text-end">R$ <?= number_format($os['custo_produtos'], 2, ',', '.') ?></td>
                            <td class="text-end">
                                <span class="<?= $os['lucro_real'] >= 0 ? 'text-success' : 'text-danger' ?>">
                                    R$ <?= number_format($os['lucro_real'], 2, ',', '.') ?>
                                </span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                    <tfoot class="table-light">
                        <tr class="fw-bold">
                            <td colspan="6" class="text-end">TOTAIS:</td>
                            <td class="text-end">R$ <?= number_format($valorTotal, 2, ',', '.') ?></td>
                            <td class="text-end">R$ <?= number_format($custoTotal, 2, ',', '.') ?></td>
                            <td class="text-end">R$ <?= number_format($lucroTotal, 2, ',', '.') ?></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>
