<?php
/**
 * proService - Relatório de Estoque
 * Arquivo: /app/views/relatorios/estoque.php
 */
?>

<?= breadcrumb(['Dashboard' => 'dashboard', 'Relatórios' => 'relatorios', 'Estoque']) ?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-0"><i class="bi bi-box-seam text-warning"></i> Relatório de Estoque</h2>
            <p class="text-muted mb-0">Controle e análise de estoque</p>
        </div>
        <div class="d-flex gap-2">
            <a href="<?= url('relatorios/exportar?tipo=estoque') ?>" class="btn btn-success">
                <i class="bi bi-download"></i> Exportar CSV
            </a>
            <a href="<?= url('relatorios/imprimir?tipo=estoque') ?>" class="btn btn-outline-secondary" target="_blank">
                <i class="bi bi-printer"></i> Imprimir
            </a>
        </div>
    </div>

    <!-- Cards Resumo -->
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h6 class="text-white-50">Custo Total em Estoque</h6>
                    <h3 class="mb-0">R$ <?= number_format($custoTotalEstoque, 2, ',', '.') ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-danger text-white">
                <div class="card-body">
                    <h6 class="text-white-50">Produtos em Falta</h6>
                    <h3 class="mb-0"><?= count($produtosEmFalta) ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h6 class="text-white-50">Produtos OK</h6>
                    <h3 class="mb-0">
                        <?= count($produtos) - count($produtosEmFalta) ?>
                    </h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabs -->
    <ul class="nav nav-tabs mb-4">
        <li class="nav-item">
            <a class="nav-link <?= $tipo == 'posicao' ? 'active' : '' ?>" href="<?= url('relatorios/estoque?tipo=posicao') ?>">
                <i class="bi bi-box"></i> Posição Atual
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?= $tipo == 'movimentacao' ? 'active' : '' ?>" href="<?= url('relatorios/estoque?tipo=movimentacao') ?>">
                <i class="bi bi-arrow-left-right"></i> Movimentações
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?= $tipo == 'produtos_usados' ? 'active' : '' ?>" href="<?= url('relatorios/estoque?tipo=produtos_usados') ?>">
                <i class="bi bi-graph-up"></i> Mais Utilizados
            </a>
        </li>
    </ul>

    <!-- Filtros por tipo -->
    <?php if ($tipo != 'posicao'): ?>
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="<?= url('relatorios/estoque') ?>">
                <input type="hidden" name="tipo" value="<?= $tipo ?>">
                <div class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label">Data Início</label>
                        <input type="date" name="data_inicio" class="form-control" value="<?= $filtros['data_inicio'] ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Data Fim</label>
                        <input type="date" name="data_fim" class="form-control" value="<?= $filtros['data_fim'] ?>">
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-funnel"></i> Filtrar
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <?php endif; ?>

    <!-- Conteúdo por tipo -->
    <?php if ($tipo == 'posicao'): ?>
    <!-- Posição Atual -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Posição Atual do Estoque</h5>
            <span class="badge bg-secondary"><?= count($produtos) ?> produtos</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Produto</th>
                            <th>Código</th>
                            <th class="text-center">Quantidade</th>
                            <th class="text-center">Mínima</th>
                            <th>Status</th>
                            <th class="text-end">Custo Unit.</th>
                            <th class="text-end">Custo Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($produtos as $p): 
                            $statusClass = '';
                            $statusText = '';
                            if ($p['quantidade_estoque'] <= 0) {
                                $statusClass = 'bg-danger';
                                $statusText = 'Zerado';
                            } elseif ($p['quantidade_estoque'] <= $p['quantidade_minima']) {
                                $statusClass = 'bg-warning text-dark';
                                $statusText = 'Baixo';
                            } else {
                                $statusClass = 'bg-success';
                                $statusText = 'OK';
                            }
                        ?>
                        <tr>
                            <td><?= e($p['nome']) ?></td>
                            <td><code><?= e($p['codigo_sku']) ?></code></td>
                            <td class="text-center">
                                <span class="fw-bold <?= $p['quantidade_estoque'] <= $p['quantidade_minima'] ? 'text-danger' : '' ?>">
                                    <?= number_format($p['quantidade_estoque'], 2) ?> <?= $p['unidade'] ?>
                                </span>
                            </td>
                            <td class="text-center"><?= number_format($p['quantidade_minima'], 2) ?></td>
                            <td><span class="badge <?= $statusClass ?>"><?= $statusText ?></span></td>
                            <td class="text-end">R$ <?= number_format($p['custo_unitario'], 2, ',', '.') ?></td>
                            <td class="text-end">R$ <?= number_format($p['custo_total'], 2, ',', '.') ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot class="table-light">
                        <tr class="fw-bold">
                            <td colspan="6" class="text-end">CUSTO TOTAL EM ESTOQUE:</td>
                            <td class="text-end">R$ <?= number_format($custoTotalEstoque, 2, ',', '.') ?></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    <!-- Produtos em Falta -->
    <?php if (!empty($produtosEmFalta)): ?>
    <div class="card mt-4 border-danger">
        <div class="card-header bg-danger bg-opacity-10">
            <h5 class="mb-0 text-danger"><i class="bi bi-exclamation-triangle"></i> Produtos em Falta</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Produto</th>
                            <th>Código</th>
                            <th class="text-center">Atual</th>
                            <th class="text-center">Mínima</th>
                            <th class="text-end">Custo Unit.</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($produtosEmFalta as $p): ?>
                        <tr class="table-danger">
                            <td><strong><?= e($p['nome']) ?></strong></td>
                            <td><code><?= e($p['codigo_sku']) ?></code></td>
                            <td class="text-center">
                                <span class="badge bg-danger"><?= number_format($p['quantidade_estoque'], 2) ?></span>
                            </td>
                            <td class="text-center"><?= number_format($p['quantidade_minima'], 2) ?></td>
                            <td class="text-end">R$ <?= number_format($p['custo_unitario'], 2, ',', '.') ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <?php elseif ($tipo == 'movimentacao'): ?>
    <!-- Movimentações -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Movimentações de Estoque</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Data</th>
                            <th>Produto</th>
                            <th>Tipo</th>
                            <th class="text-center">Quantidade</th>
                            <th>Motivo</th>
                            <th>OS</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($movimentacoes)): ?>
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">
                                <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                Nenhuma movimentação no período
                            </td>
                        </tr>
                        <?php else: ?>
                        <?php foreach ($movimentacoes as $m): 
                            $tipoClass = [
                                'entrada' => 'success',
                                'saida' => 'danger',
                                'ajuste' => 'warning'
                            ][$m['tipo']] ?? 'secondary';
                        ?>
                        <tr>
                            <td><?= date('d/m/Y H:i', strtotime($m['created_at'])) ?></td>
                            <td><?= e($m['produto_nome']) ?></td>
                            <td>
                                <span class="badge bg-<?= $tipoClass ?>">
                                    <?= ucfirst($m['tipo']) ?>
                                </span>
                            </td>
                            <td class="text-center <?= $m['tipo'] == 'saida' ? 'text-danger' : 'text-success' ?>">
                                <?= $m['tipo'] == 'saida' ? '-' : '+' ?> <?= number_format($m['quantidade'], 2) ?>
                            </td>
                            <td><?= e($m['motivo']) ?></td>
                            <td>
                                <?php if ($m['os_id']): ?>
                                <a href="<?= url('ordens/show/' . $m['os_id']) ?>" class="badge bg-info text-decoration-none">
                                    OS #<?= $m['os_id'] ?>
                                </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <?php elseif ($tipo == 'produtos_usados'): ?>
    <!-- Produtos Mais Utilizados -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Produtos Mais Utilizados</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Produto</th>
                            <th>Código</th>
                            <th class="text-center">Quantidade Usada</th>
                            <th class="text-end">Custo Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($produtosMaisUsados)): ?>
                        <tr>
                            <td colspan="5" class="text-center py-4 text-muted">
                                <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                Nenhum produto utilizado no período
                            </td>
                        </tr>
                        <?php else: ?>
                        <?php $pos = 1; foreach ($produtosMaisUsados as $p): ?>
                        <tr>
                            <td><span class="badge bg-<?= $pos <= 3 ? 'warning text-dark' : 'secondary' ?>">#<?= $pos++ ?></span></td>
                            <td><?= e($p['nome']) ?></td>
                            <td><code><?= e($p['codigo_sku']) ?></code></td>
                            <td class="text-center">
                                <span class="fw-bold"><?= number_format($p['total_usado'] ?? 0, 2) ?> <?= $p['unidade'] ?? 'UN' ?></span>
                            </td>
                            <td class="text-end">R$ <?= number_format($p['custo_total'], 2, ',', '.') ?></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>
