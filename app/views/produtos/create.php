<?= breadcrumb(['Dashboard' => 'dashboard', 'Produtos' => 'produtos', 'Novo Produto']) ?>

<div class="d-flex align-items-center mb-4">
    <a href="<?= url('produtos') ?>" class="btn btn-outline-secondary btn-sm me-3">
        <i class="bi bi-arrow-left"></i>
    </a>
    <div>
        <h4 class="mb-0"><i class="bi bi-box-seam"></i> Novo Produto</h4>
        <p class="text-muted mb-0">Cadastre um produto no estoque</p>
    </div>
</div>

<?php if (!empty($_SESSION['errors'])): ?>
<div class="alert alert-danger">
    <?php foreach ($_SESSION['errors'] as $error): ?>
        <div><?= e($error) ?></div>
    <?php endforeach; ?>
    <?php unset($_SESSION['errors']); ?>
</div>
<?php endif; ?>

<form method="POST" action="<?= url('produtos/create') ?>">
    <?= csrfField() ?>
    
    <div class="row g-3">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0"><i class="bi bi-box"></i> Informações do Produto</h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Nome do Produto *</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-card-text"></i></span>
                                <input type="text" name="nome" class="form-control" value="<?= e($old['nome'] ?? '') ?>" required autofocus>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Código/SKU</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-upc-scan"></i></span>
                                <input type="text" name="codigo_sku" class="form-control" value="<?= e($old['codigo_sku'] ?? '') ?>">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Categoria</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-tags"></i></span>
                                <input type="text" name="categoria" class="form-control" value="<?= e($old['categoria'] ?? '') ?>" placeholder="Ex: Elétrica">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Unidade</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-tag"></i></span>
                                <select name="unidade" class="form-select">
                                    <option value="UN">UN</option>
                                    <option value="KG">KG</option>
                                    <option value="M">M</option>
                                    <option value="L">L</option>
                                    <option value="CX">CX</option>
                                    <option value="PC">PC</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Estoque Inicial</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-123"></i></span>
                                <input type="text" name="quantidade_estoque" class="form-control" value="<?= e($old['quantidade_estoque'] ?? '0,00') ?>">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Estoque Mínimo</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-exclamation-triangle"></i></span>
                                <input type="text" name="quantidade_minima" class="form-control" value="<?= e($old['quantidade_minima'] ?? '0,00') ?>">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Custo Unitário</label>
                            <div class="input-group">
                                <span class="input-group-text">R$</span>
                                <input type="text" name="custo_unitario" class="form-control" value="<?= e($old['custo_unitario'] ?? '0,00') ?>">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Preço de Venda</label>
                            <div class="input-group">
                                <span class="input-group-text">R$</span>
                                <input type="text" name="preco_venda" class="form-control" value="<?= e($old['preco_venda'] ?? '0,00') ?>">
                            </div>
                        </div>
                    </div>
                    
                    <!-- Dados da Compra (aparece quando tem custo) -->
                    <div class="mt-3" id="dados_compra" style="display: none; flex-direction: row !important;">
                        <div class="card border-warning">
                            <div class="card-header bg-warning text-dark">
                                <h6 class="mb-0"><i class="bi bi-cash-coin"></i> Dados da Compra (para registro de despesa)</h6>
                            </div>
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label class="form-label">Valor Total da Compra (opcional)</label>
                                        <div class="input-group">
                                            <span class="input-group-text">R$</span>
                                            <input type="text" name="valor_total_compra" class="form-control" value="<?= e($old['valor_total_compra'] ?? '') ?>" placeholder="Ex: 132,00">
                                        </div>
                                        <small class="text-muted">Se preenchido, o sistema registra a despesa por este total (e calcula o custo unitário por quantidade).</small>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Forma de Pagamento</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="bi bi-credit-card"></i></span>
                                            <select name="forma_pagamento_despesa" class="form-select">
                                                <option value="dinheiro">Dinheiro</option>
                                                <option value="pix" selected>PIX</option>
                                                <option value="cartao_credito">Cartão de Crédito</option>
                                                <option value="cartao_debito">Cartão de Débito</option>
                                                <option value="boleto">Boleto</option>
                                                <option value="transferencia">Transferência</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Status do Pagamento</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="bi bi-flag"></i></span>
                                            <select name="status_despesa" class="form-select">
                                                <option value="pago" selected>Pago</option>
                                                <option value="pendente">Pendente</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Data da Compra</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="bi bi-calendar3"></i></span>
                                            <input type="date" name="data_despesa" class="form-control" value="<?= date('Y-m-d') ?>">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row g-3 mt-2">
                        <div class="col-md-6">
                            <label class="form-label">Fornecedor</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-truck"></i></span>
                                <input type="text" name="fornecedor" class="form-control" value="<?= e($old['fornecedor'] ?? '') ?>">
                            </div>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Observações</label>
                            <textarea name="observacoes" class="form-control" rows="2"><?= e($old['observacoes'] ?? '') ?></textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-12">
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-lg"></i> Salvar Produto
                </button>
                <a href="<?= url('produtos') ?>" class="btn btn-outline-secondary">Cancelar</a>
            </div>
        </div>
    </div>
</form>

<script>
// Mostrar/ocultar campos de dados da compra baseado no custo unitário
const custoInput = document.querySelector('input[name="custo_unitario"]');
const estoqueInput = document.querySelector('input[name="quantidade_estoque"]');
const dadosCompra = document.getElementById('dados_compra');

function toggleDadosCompra() {
    const custo = parseFloat(custoInput.value.replace(/\./g, '').replace(',', '.')) || 0;
    const estoque = parseFloat(estoqueInput.value.replace(/\./g, '').replace(',', '.')) || 0;
    
    if (custo > 0 && estoque > 0) {
        dadosCompra.style.display = 'block';
    } else {
        dadosCompra.style.display = 'none';
    }
}

if (custoInput) {
    custoInput.addEventListener('blur', toggleDadosCompra);
}
if (estoqueInput) {
    estoqueInput.addEventListener('blur', toggleDadosCompra);
}
</script>
