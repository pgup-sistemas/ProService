<?php
/**
 * proService - Lista de Parcelas
 * Arquivo: /app/views/financeiro/parcelas.php
 */
?>

<?= breadcrumb(['Dashboard' => 'dashboard', 'Financeiro' => 'financeiro', 'Parcelas']) ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1">Parcelas</h4>
        <p class="text-muted mb-0">Controle de parcelas a receber</p>
    </div>
    <a href="<?= url('financeiro/receitas') ?>" class="btn btn-outline-primary">
        <i class="bi bi-arrow-left"></i> Voltar para Receitas
    </a>
</div>

<!-- Resumo -->
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card bg-warning text-white">
            <div class="card-body">
                <h6 class="text-white-50">Total Pendente</h6>
                <h3 class="mb-0">R$ <?= number_format(array_sum(array_column(array_filter($parcelas, fn($p) => $p['status'] === 'pendente'), 'valor')), 2, ',', '.') ?></h3>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card bg-danger text-white">
            <div class="card-body">
                <h6 class="text-white-50">Atrasadas</h6>
                <h3 class="mb-0">R$ <?= number_format(array_sum(array_column(array_filter($parcelas, fn($p) => $p['status'] === 'atrasado'), 'valor')), 2, ',', '.') ?></h3>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card bg-success text-white">
            <div class="card-body">
                <h6 class="text-white-50">Pagas no Mês</h6>
                <h3 class="mb-0">R$ <?= number_format(array_sum(array_column(array_filter($parcelas, fn($p) => $p['status'] === 'pago' && date('Y-m', strtotime($p['data_pagamento'] ?? '')) === date('Y-m')), 'valor')), 2, ',', '.') ?></h3>
            </div>
        </div>
    </div>
</div>

<!-- Lista de Parcelas -->
<div class="card">
    <div class="card-header">
        <h5 class="mb-0"><i class="bi bi-calendar-check"></i> Parcelas do Período</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Parcela</th>
                        <th>Descrição</th>
                        <th>Vencimento</th>
                        <th>Valor</th>
                        <th>Status</th>
                        <th class="text-end">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($parcelas)): ?>
                    <tr>
                        <td colspan="6" class="text-center py-4 text-muted">
                            <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                            Nenhuma parcela encontrada
                        </td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($parcelas as $parcela): 
                        $statusClass = match($parcela['status']) {
                            'pago' => 'success',
                            'atrasado' => 'danger',
                            default => 'warning'
                        };
                        $isAtrasada = $parcela['status'] === 'pendente' && $parcela['data_vencimento'] < date('Y-m-d');
                    ?>
                    <tr class="<?= $isAtrasada ? 'table-danger' : '' ?>">
                        <td>
                            <strong>#<?= $parcela['numero_parcela'] ?></strong>
                            <?php if ($parcela['receita_descricao']): ?>
                            <br><small class="text-muted"><?= e($parcela['receita_descricao']) ?></small>
                            <?php endif; ?>
                        </td>
                        <td><?= e($parcela['receita_descricao'] ?? 'Parcela #' . $parcela['numero_parcela']) ?></td>
                        <td>
                            <?= date('d/m/Y', strtotime($parcela['data_vencimento'])) ?>
                            <?php if ($isAtrasada): ?>
                            <br><span class="badge bg-danger">Atrasada</span>
                            <?php endif; ?>
                        </td>
                        <td>R$ <?= number_format($parcela['valor'], 2, ',', '.') ?></td>
                        <td>
                            <span class="badge bg-<?= $statusClass ?>">
                                <?= ucfirst($parcela['status']) ?>
                            </span>
                        </td>
                        <td class="text-end">
                            <?php if ($parcela['status'] !== 'pago'): ?>
                            <form method="POST" action="<?= url('financeiro/parcelas/' . $parcela['id'] . '/pagar') ?>" class="d-inline">
                                <?= csrfField() ?>
                                <select name="forma_pagamento" class="form-select form-select-sm d-inline-block w-auto">
                                    <option value="dinheiro">Dinheiro</option>
                                    <option value="pix">PIX</option>
                                    <option value="cartao_credito">Cartão Crédito</option>
                                    <option value="cartao_debito">Cartão Débito</option>
                                    <option value="boleto">Boleto</option>
                                    <option value="transferencia">Transferência</option>
                                </select>
                                <button type="submit" class="btn btn-sm btn-success">
                                    <i class="bi bi-check"></i> Pagar
                                </button>
                            </form>
                            <?php else: ?>
                            <span class="text-muted">
                                <i class="bi bi-check-circle"></i> 
                                <?= date('d/m/Y', strtotime($parcela['data_pagamento'])) ?>
                            </span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <?php if ($paginacao['last_page'] > 1): ?>
    <div class="card-footer">
        <nav aria-label="Paginação">
            <ul class="pagination justify-content-center mb-0">
                <?php for ($i = 1; $i <= $paginacao['last_page']; $i++): ?>
                <li class="page-item <?= $i === $paginacao['page'] ? 'active' : '' ?>">
                    <a class="page-link" href="<?= url('financeiro/parcelas?page=' . $i) ?>"><?= $i ?></a>
                </li>
                <?php endfor; ?>
            </ul>
        </nav>
    </div>
    <?php endif; ?>
</div>
