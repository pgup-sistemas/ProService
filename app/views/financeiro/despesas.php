<?= breadcrumb(['Dashboard' => 'dashboard', 'Financeiro' => 'financeiro', 'Despesas']) ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1">Despesas</h4>
        <p class="text-muted mb-0">Controle de despesas</p>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= url('financeiro') ?>" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Voltar
        </a>
        <a href="<?= url('financeiro/despesas/create') ?>" class="btn btn-primary">
            <i class="bi bi-plus-lg"></i> Nova Despesa
        </a>
    </div>
</div>

<!-- Cards de Resumo -->
<div class="row g-3 mb-4">
    <div class="col-md-6">
        <div class="card border-warning">
            <div class="card-body d-flex align-items-center">
                <div class="flex-shrink-0">
                    <div class="bg-warning bg-opacity-10 p-3 rounded">
                        <i class="bi bi-hourglass-split text-warning fs-4"></i>
                    </div>
                </div>
                <div class="flex-grow-1 ms-3">
                    <h6 class="mb-1">A Pagar</h6>
                    <?php
                    $totalPendente = 0;
                    foreach ($pendentes as $p) $totalPendente += $p['valor'];
                    ?>
                    <h4 class="mb-0 text-warning"><?= formatMoney($totalPendente) ?></h4>
                    <small class="text-muted"><?= count($pendentes) ?> despesa(s) pendente(s)</small>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card border-success">
            <div class="card-body d-flex align-items-center">
                <div class="flex-shrink-0">
                    <div class="bg-success bg-opacity-10 p-3 rounded">
                        <i class="bi bi-check-circle text-success fs-4"></i>
                    </div>
                </div>
                <div class="flex-grow-1 ms-3">
                    <h6 class="mb-1">Mês Atual</h6>
                    <?php
                    $totalPagoMes = 0;
                    $mesAtual = date('Y-m');
                    foreach ($despesas as $d) {
                        if ($d['status'] === 'pago' && strpos($d['data_despesa'], $mesAtual) === 0) {
                            $totalPagoMes += $d['valor'];
                        }
                    }
                    ?>
                    <h4 class="mb-0 text-success"><?= formatMoney($totalPagoMes) ?></h4>
                    <small class="text-muted">Total pago em <?= date('m/Y') ?></small>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Filtros -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-3">
                <input type="text" name="busca" class="form-control" placeholder="Buscar..." value="<?= e($filtros['busca'] ?? '') ?>">
            </div>
            <div class="col-md-2">
                <select name="categoria" class="form-select">
                    <option value="">Todas Categorias</option>
                    <?php foreach ($categorias as $cat): ?>
                    <option value="<?= $cat ?>" <?= ($filtros['categoria'] ?? '') === $cat ? 'selected' : '' ?>><?= ucfirst($cat) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <select name="status" class="form-select">
                    <option value="">Todos Status</option>
                    <option value="pago" <?= ($filtros['status'] ?? '') === 'pago' ? 'selected' : '' ?>>Pago</option>
                    <option value="pendente" <?= ($filtros['status'] ?? '') === 'pendente' ? 'selected' : '' ?>>Pendente</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">Filtrar</button>
            </div>
        </form>
    </div>
</div>

<?php if (!empty($pendentes) && empty($filtros['status'])): ?>
<div class="card border-warning mb-4">
    <div class="card-header text-dark">
        <h6 class="mb-0"><i class="bi bi-hourglass-split"></i> Despesas Pendentes (A Pagar)</h6>
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>Data</th>
                    <th>Descrição</th>
                    <th>Categoria</th>
                    <th>Valor</th>
                    <th>Comp.</th>
                    <th class="text-end">Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($pendentes as $p): ?>
                <tr class="table-warning">
                    <td><?= formatDate($p['data_despesa']) ?></td>
                    <td><?= e($p['descricao']) ?></td>
                    <td>
                        <span class="badge bg-secondary"><?= ucfirst($p['categoria']) ?></span>
                    </td>
                    <td class="fw-bold text-danger"><?= formatMoney($p['valor']) ?></td>
                    <td>
                        <?php if (!empty($p['comprovante'])): ?>
                            <a class="btn btn-sm btn-outline-secondary" href="<?= url('files/comprovante/' . $p['id']) ?>" target="_blank" rel="noopener">
                                <i class="bi bi-paperclip"></i> Ver
                            </a>
                        <?php else: ?>
                            <small class="text-muted">-</small>
                        <?php endif; ?>
                    </td>
                    <td class="text-end">
                        <form method="POST" action="<?= url('financeiro/despesas/' . $p['id'] . '/pagar') ?>" class="d-inline">
                            <?= csrfField() ?>
                            <select name="forma_pagamento" class="form-select form-select-sm d-inline w-auto">
                                <option value="dinheiro">Dinheiro</option>
                                <option value="pix">PIX</option>
                                <option value="cartao_credito">Cartão Crédito</option>
                                <option value="cartao_debito">Cartão Débito</option>
                                <option value="boleto">Boleto</option>
                                <option value="transferencia">Transferência</option>
                            </select>
                            <button type="submit" class="btn btn-sm btn-success">
                                <i class="bi bi-check-lg"></i> Pagar
                            </button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="4" class="text-end fw-bold">Total a pagar</td>
                    <td class="fw-bold text-danger"><?= formatMoney($totalPendente) ?></td>
                    <td></td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>
<?php endif; ?>

<!-- Lista de Despesas -->
<div class="card">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>Data</th>
                    <th>Descrição</th>
                    <th>Categoria</th>
                    <th>Valor</th>
                    <th>Status</th>
                    <th>Forma Pag.</th>
                    <th>Comp.</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($despesas)): ?>
                    <tr>
                        <td colspan="7" class="text-center py-4 text-muted">
                            <i class="bi bi-inbox fs-1"></i>
                            <p class="mb-0">Nenhuma despesa encontrada</p>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($despesas as $d): ?>
                    <tr class="<?= $d['status'] === 'pendente' ? 'table-warning' : '' ?>">
                        <td><?= formatDate($d['data_despesa']) ?></td>
                        <td><?= e($d['descricao']) ?></td>
                        <td>
                            <span class="badge bg-secondary"><?= ucfirst($d['categoria']) ?></span>
                        </td>
                        <td class="fw-bold text-danger"><?= formatMoney($d['valor']) ?></td>
                        <td>
                            <span class="badge bg-<?= $d['status'] === 'pago' ? 'success' : ($d['status'] === 'pendente' ? 'warning' : 'danger') ?>">
                                <?= ucfirst($d['status']) ?>
                            </span>
                        </td>
                        <td>
                            <?php if ($d['forma_pagamento']): ?>
                                <small class="text-muted"><?= ucfirst(str_replace('_', ' ', $d['forma_pagamento'])) ?></small>
                            <?php else: ?>
                                <small class="text-muted">-</small>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if (!empty($d['comprovante'])): ?>
                                <a class="btn btn-sm btn-outline-secondary" href="<?= url('files/comprovante/' . $d['id']) ?>" target="_blank" rel="noopener">
                                    <i class="bi bi-paperclip"></i>
                                </a>
                            <?php else: ?>
                                <small class="text-muted">-</small>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    
    <?php if ($paginacao['last_page'] > 1): ?>
    <div class="card-footer">
        <nav aria-label="Paginação">
            <ul class="pagination justify-content-center mb-0">
                <?php for ($i = 1; $i <= $paginacao['last_page']; $i++): ?>
                <li class="page-item <?= $i === $paginacao['page'] ? 'active' : '' ?>">
                    <a class="page-link" href="<?= url('financeiro/despesas?page=' . $i) ?>"><?= $i ?></a>
                </li>
                <?php endfor; ?>
            </ul>
        </nav>
    </div>
    <?php endif; ?>
</div>
