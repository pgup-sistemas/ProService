<?= breadcrumb(['Dashboard' => 'dashboard', 'Recibos de Pagamento']) ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4><i class="bi bi-receipt"></i> Recibos de Pagamento</h4>
</div>

<!-- Filtros -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="<?= url('recibos') ?>" class="row g-3">
            <div class="col-md-4">
                <label class="form-label">Cliente</label>
                <select name="cliente_id" class="form-select">
                    <option value="">Todos</option>
                    <?php foreach ($clientes as $cliente): ?>
                    <option value="<?= $cliente['id'] ?>" <?= ($filtros['cliente_id'] ?? '') == $cliente['id'] ? 'selected' : '' ?>>
                        <?= e($cliente['nome']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Data Início</label>
                <input type="date" name="data_inicio" class="form-control" value="<?= $filtros['data_inicio'] ?? '' ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label">Data Fim</label>
                <input type="date" name="data_fim" class="form-control" value="<?= $filtros['data_fim'] ?? '' ?>">
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-search"></i> Filtrar
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Lista de Recibos -->
<div class="card">
    <div class="card-body p-0">
        <?php if (!empty($recibos)): ?>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Nº Recibo</th>
                        <th>OS</th>
                        <th>Cliente</th>
                        <th>Valor</th>
                        <th>Forma Pagamento</th>
                        <th>Data Pagamento</th>
                        <th>Status</th>
                        <th width="120">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recibos as $recibo): ?>
                    <tr>
                        <td><strong>#<?= str_pad($recibo['numero_recibo'] ?? $recibo['id'], 6, '0', STR_PAD_LEFT) ?></strong></td>
                        <td>
                            <a href="<?= url('ordens/show/' . $recibo['os_id']) ?>">
                                #<?= str_pad($recibo['numero_os'] ?? '---', 4, '0', STR_PAD_LEFT) ?>
                            </a>
                        </td>
                        <td><?= e($recibo['cliente_nome']) ?></td>
                        <td class="fw-bold"><?= formatMoney($recibo['valor']) ?></td>
                        <td><?= ucfirst($recibo['forma_pagamento']) ?></td>
                        <td><?= $recibo['data_pagamento'] ? date('d/m/Y', strtotime($recibo['data_pagamento'])) : '-' ?></td>
                        <td>
                            <span class="badge bg-<?= $recibo['status'] === 'emitido' ? 'success' : 'danger' ?>">
                                <?= $recibo['status'] === 'emitido' ? 'Emitido' : 'Cancelado' ?>
                            </span>
                        </td>
                        <td>
                            <a href="<?= url('recibos/show/' . $recibo['id']) ?>" class="btn btn-sm btn-outline-primary" target="_blank">
                                <i class="bi bi-eye"></i> Ver
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Paginação -->
        <?php if ($paginacao['last_page'] > 1): ?>
        <div class="card-footer">
            <nav>
                <ul class="pagination justify-content-center mb-0">
                    <?php for ($i = 1; $i <= $paginacao['last_page']; $i++): ?>
                    <li class="page-item <?= $i === $paginacao['current_page'] ? 'active' : '' ?>">
                        <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>"><?= $i ?></a>
                    </li>
                    <?php endfor; ?>
                </ul>
            </nav>
        </div>
        <?php endif; ?>
        
        <?php else: ?>
        <div class="text-center py-5">
            <i class="bi bi-receipt text-muted" style="font-size: 3rem;"></i>
            <p class="text-muted mt-3">Nenhum recibo encontrado.</p>
        </div>
        <?php endif; ?>
    </div>
</div>
