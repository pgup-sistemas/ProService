<?= breadcrumb(['Dashboard' => 'dashboard', 'Financeiro' => 'financeiro', 'Receitas']) ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1"><i class="bi bi-cash-stack"></i> Receitas</h4>
        <p class="text-muted mb-0">Controle de receitas e pagamentos</p>
    </div>
</div>

<!-- Filtros -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-3">
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                    <input type="text" name="busca" class="form-control" placeholder="Buscar..." value="<?= e($filtros['busca'] ?? '') ?>">
                </div>
            </div>
            <div class="col-md-2">
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-flag"></i></span>
                    <select name="status" class="form-select">
                        <option value="">Todos Status</option>
                        <option value="pendente" <?= ($filtros['status'] ?? '') === 'pendente' ? 'selected' : '' ?>>Pendente</option>
                        <option value="recebido" <?= ($filtros['status'] ?? '') === 'recebido' ? 'selected' : '' ?>>Recebido</option>
                    </select>
                </div>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-funnel"></i> Filtrar
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Pendentes -->
<?php if (!empty($pendentes)): ?>
<div class="card border-warning mb-4">
    <div class="card-header bg-warning text-dark">
        <h6 class="mb-0"><i class="bi bi-hourglass-split"></i> Receitas Pendentes</h6>
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>Descrição</th>
                    <th>Cliente</th>
                    <th>OS</th>
                    <th>Valor</th>
                    <th class="text-end">Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($pendentes as $r): ?>
                <tr>
                    <td><?= e($r['descricao']) ?></td>
                    <td><?= e($r['cliente_nome'] ?? '-') ?></td>
                    <td>#<?= str_pad($r['numero_os'] ?? 0, 4, '0', STR_PAD_LEFT) ?></td>
                    <td class="fw-bold"><?= formatMoney($r['valor']) ?></td>
                    <td class="text-end">
                        <form method="POST" action="<?= url('financeiro/receitas/' . $r['id'] . '/receber') ?>" class="d-inline">
                            <?= csrfField() ?>
                            <select name="forma_pagamento" class="form-select form-select-sm d-inline w-auto">
                                <option value="dinheiro">Dinheiro</option>
                                <option value="pix">PIX</option>
                                <option value="cartao_credito">Cartão Crédito</option>
                                <option value="cartao_debito">Cartão Débito</option>
                                <option value="transferencia">Transferência</option>
                            </select>
                            <button type="submit" class="btn btn-sm btn-success">
                                <i class="bi bi-check-lg"></i> Receber
                            </button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<!-- Lista de Receitas -->
<div class="card">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>Data</th>
                    <th>Descrição</th>
                    <th>Cliente</th>
                    <th>OS</th>
                    <th>Valor</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($receitas)): ?>
                    <tr>
                        <td colspan="6" class="text-center py-4 text-muted">
                            <i class="bi bi-inbox fs-1"></i>
                            <p class="mb-0">Nenhuma receita encontrada</p>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($receitas as $r): ?>
                    <tr>
                        <td><?= formatDate($r['data_recebimento'] ?? $r['created_at']) ?></td>
                        <td><?= e($r['descricao']) ?></td>
                        <td><?= e($r['cliente_nome'] ?? '-') ?></td>
                        <td><?= $r['numero_os'] ? '#' . str_pad($r['numero_os'], 4, '0', STR_PAD_LEFT) : '-' ?></td>
                        <td class="fw-bold"><?= formatMoney($r['valor']) ?></td>
                        <td>
                            <span class="badge bg-<?= $r['status'] === 'recebido' ? 'success' : ($r['status'] === 'pendente' ? 'warning' : 'danger') ?>">
                                <?= ucfirst($r['status']) ?>
                            </span>
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
                    <a class="page-link" href="<?= url('financeiro/receitas?page=' . $i) ?>"><?= $i ?></a>
                </li>
                <?php endfor; ?>
            </ul>
        </nav>
    </div>
    <?php endif; ?>
</div>
