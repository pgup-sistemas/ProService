<?= breadcrumb(['Dashboard' => 'dashboard', 'Financeiro' => 'financeiro', 'Despesas' => 'financeiro/despesas', 'Nova Despesa']) ?>

<div class="d-flex align-items-center mb-4">
    <a href="<?= url('financeiro/despesas') ?>" class="btn btn-outline-secondary btn-sm me-3">
        <i class="bi bi-arrow-left"></i>
    </a>
    <div>
        <h4 class="mb-0"><i class="bi bi-cart-dash"></i> Nova Despesa</h4>
        <p class="text-muted mb-0">Registre uma nova despesa</p>
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

<div class="card">
    <div class="card-header">
        <h6 class="mb-0"><i class="bi bi-pencil-square"></i> Dados do Lançamento</h6>
    </div>
    <div class="card-body">
        <form method="POST" action="<?= url('financeiro/despesas/create') ?>" enctype="multipart/form-data">
            <?= csrfField() ?>
            
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Tipo de Lançamento</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-ui-checks"></i></span>
                        <select name="tipo_despesa" class="form-select" id="tipo_despesa">
                            <option value="operacional" selected>Despesa Operacional</option>
                            <option value="estoque">Compra de Estoque / Materiais</option>
                        </select>
                    </div>
                </div>

                <div class="col-12" id="bloco_estoque" style="display:none;">
                    <div class="card border-warning">
                        <div class="card-header bg-warning text-dark">
                            <h6 class="mb-0"><i class="bi bi-box-seam"></i> Compra de Estoque / Materiais</h6>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Produto (estoque) *</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bi bi-box"></i></span>
                                        <select name="produto_id" class="form-select" id="produto_id">
                                            <option value="">Selecione...</option>
                                            <?php foreach (($produtosPorCategoria ?? []) as $cat => $produtos): ?>
                                                <optgroup label="<?= e($cat) ?>">
                                                    <?php foreach ($produtos as $p): ?>
                                                        <option value="<?= (int) $p['id'] ?>" data-unidade="<?= e($p['unidade'] ?? 'UN') ?>">
                                                            <?= e($p['nome']) ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </optgroup>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <small class="text-muted">Este lançamento dá entrada no estoque e registra a despesa automaticamente.</small>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Quantidade *</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bi bi-123"></i></span>
                                        <input type="text" name="quantidade" class="form-control" value="0,00" id="quantidade">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Unidade</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bi bi-tag"></i></span>
                                        <input type="text" class="form-control" id="unidade_visual" value="UN" disabled>
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <label class="form-label">Custo Unitário</label>
                                    <div class="input-group">
                                        <span class="input-group-text">R$</span>
                                        <input type="text" name="custo_unitario" class="form-control" value="0,00" id="custo_unitario">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Valor Total da Compra</label>
                                    <div class="input-group">
                                        <span class="input-group-text">R$</span>
                                        <input type="text" name="valor_total_compra" class="form-control" value="" placeholder="Ex: 132,00" id="valor_total_compra">
                                    </div>
                                    <small class="text-muted">Se preenchido, este valor é usado como total (e o custo unitário é calculado por quantidade).</small>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Fornecedor</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bi bi-truck"></i></span>
                                        <input type="text" name="fornecedor" class="form-control" id="fornecedor">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12">
                    <h6 class="text-muted mb-0"><i class="bi bi-receipt"></i> Informações da Despesa</h6>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Descrição *</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-card-text"></i></span>
                        <input type="text" name="descricao" class="form-control" placeholder="Ex: Compra de materiais" required autofocus>
                    </div>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Categoria</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-tags"></i></span>
                        <select name="categoria" class="form-select">
                            <?php foreach ($categorias as $cat): ?>
                            <option value="<?= $cat ?>"><?= ucfirst($cat) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Valor *</label>
                    <div class="input-group">
                        <span class="input-group-text">R$</span>
                        <input type="text" name="valor" class="form-control" value="0,00" required id="valor">
                    </div>
                    <small class="text-muted" id="valor_hint" style="display:none;">Calculado automaticamente a partir da compra de estoque.</small>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Data</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-calendar3"></i></span>
                        <input type="date" name="data_despesa" class="form-control" value="<?= date('Y-m-d') ?>">
                    </div>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Forma de Pagamento</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-credit-card"></i></span>
                        <select name="forma_pagamento" class="form-select">
                            <option value="dinheiro">Dinheiro</option>
                            <option value="pix">PIX</option>
                            <option value="cartao_credito">Cartão Crédito</option>
                            <option value="cartao_debito">Cartão Débito</option>
                            <option value="transferencia">Transferência</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Status</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-flag"></i></span>
                        <select name="status" class="form-select">
                            <option value="pago">Pago</option>
                            <option value="pendente">Pendente</option>
                        </select>
                    </div>
                </div>
                <div class="col-12">
                    <label class="form-label">Observações</label>
                    <textarea name="observacoes" class="form-control" rows="3"></textarea>
                </div>
                <div class="col-12">
                    <label class="form-label">Comprovante</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-paperclip"></i></span>
                        <input type="file" name="comprovante" class="form-control" accept="image/*,application/pdf">
                    </div>
                    <small class="text-muted">Envie um comprovante (imagem ou PDF).</small>
                </div>
                <div class="col-12">
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-lg"></i> Salvar Despesa
                        </button>
                        <a href="<?= url('financeiro/despesas') ?>" class="btn btn-outline-secondary">Cancelar</a>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
const tipoSelect = document.getElementById('tipo_despesa');
const blocoEstoque = document.getElementById('bloco_estoque');
const descricaoInput = document.querySelector('input[name="descricao"]');
const categoriaSelect = document.querySelector('select[name="categoria"]');
const valorInput = document.getElementById('valor');
const valorHint = document.getElementById('valor_hint');
const produtoSelect = document.getElementById('produto_id');
const unidadeVisual = document.getElementById('unidade_visual');
const qtdInput = document.getElementById('quantidade');
const custoInput = document.getElementById('custo_unitario');
const totalInput = document.getElementById('valor_total_compra');

function parseBR(value) {
    if (!value) return 0;
    const v = String(value).trim().replace(/\./g, '').replace(',', '.');
    const n = parseFloat(v);
    return isNaN(n) ? 0 : n;
}

function formatBR(num) {
    return (num || 0).toFixed(2).replace('.', ',');
}

function updateUnidade() {
    const opt = produtoSelect?.selectedOptions?.[0];
    const unidade = opt?.getAttribute('data-unidade') || 'UN';
    if (unidadeVisual) unidadeVisual.value = unidade;
}

function calcularValor() {
    if (tipoSelect.value !== 'estoque') return;

    const qtd = parseBR(qtdInput.value);
    const custo = parseBR(custoInput.value);
    const total = parseBR(totalInput.value);

    const valor = total > 0 ? total : (qtd * custo);
    if (valorInput) valorInput.value = formatBR(valor);
}

function toggleTipo() {
    const isEstoque = tipoSelect.value === 'estoque';

    if (blocoEstoque) blocoEstoque.style.display = isEstoque ? 'block' : 'none';
    if (valorHint) valorHint.style.display = isEstoque ? 'block' : 'none';

    if (valorInput) valorInput.readOnly = isEstoque;
    if (descricaoInput) descricaoInput.required = !isEstoque;

    if (isEstoque) {
        if (categoriaSelect) categoriaSelect.value = 'material';
        calcularValor();
    } else {
        if (valorInput) valorInput.readOnly = false;
    }
}

tipoSelect?.addEventListener('change', toggleTipo);
produtoSelect?.addEventListener('change', () => { updateUnidade(); calcularValor(); });
qtdInput?.addEventListener('blur', calcularValor);
custoInput?.addEventListener('blur', calcularValor);
totalInput?.addEventListener('blur', calcularValor);

toggleTipo();
updateUnidade();
</script>
