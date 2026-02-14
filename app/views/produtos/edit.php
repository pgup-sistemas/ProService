<?= breadcrumb(['Dashboard' => 'dashboard', 'Produtos' => 'produtos', 'Editar: ' . e($produto['nome'])]) ?>

<div class="d-flex align-items-center mb-4">
    <a href="<?= url('produtos') ?>" class="btn btn-outline-secondary btn-sm me-3">
        <i class="bi bi-arrow-left"></i>
    </a>
    <div>
        <h4 class="mb-0">Editar Produto</h4>
        <p class="mb-0 text-muted"><?= e($produto['nome']) ?></p>
    </div>
    <form method="POST" action="<?= url('produtos/delete/' . $produto['id']) ?>" class="ms-auto" onsubmit="return confirm('Tem certeza que deseja remover este produto?')">
        <?= csrfField() ?>
        <button type="submit" class="btn btn-outline-danger">
            <i class="bi bi-trash"></i> Remover
        </button>
    </form>
</div>

<?php if (!empty($_SESSION['errors'])): ?>
<div class="alert alert-danger">
    <?php foreach ($_SESSION['errors'] as $error): ?>
        <div><?= e($error) ?></div>
    <?php endforeach; ?>
    <?php unset($_SESSION['errors']); ?>
</div>
<?php endif; ?>

<div class="row g-3">
    <div class="col-lg-8">
        <form method="POST" action="<?= url('produtos/edit/' . $produto['id']) ?>">
            <?= csrfField() ?>
            
            <div class="card mb-3">
                <div class="card-header">
                    <h6 class="mb-0"><i class="bi bi-box"></i> Informações do Produto</h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Nome do Produto *</label>
                            <input type="text" name="nome" class="form-control" value="<?= e($produto['nome']) ?>" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Código/SKU</label>
                            <input type="text" name="codigo_sku" class="form-control" value="<?= e($produto['codigo_sku']) ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Categoria</label>
                            <input type="text" name="categoria" class="form-control" value="<?= e($produto['categoria']) ?>">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Unidade</label>
                            <select name="unidade" class="form-select">
                                <?php foreach (['UN', 'KG', 'M', 'L', 'CX', 'PC'] as $un): ?>
                                <option value="<?= $un ?>" <?= $produto['unidade'] === $un ? 'selected' : '' ?>><?= $un ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Estoque Mínimo</label>
                            <input type="text" name="quantidade_minima" class="form-control" value="<?= number_format($produto['quantidade_minima'], 2, ',', '.') ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Custo Unitário</label>
                            <div class="input-group">
                                <span class="input-group-text">R$</span>
                                <input type="text" name="custo_unitario" class="form-control" value="<?= number_format($produto['custo_unitario'], 2, ',', '.') ?>">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Preço de Venda</label>
                            <div class="input-group">
                                <span class="input-group-text">R$</span>
                                <input type="text" name="preco_venda" class="form-control" value="<?= number_format($produto['preco_venda'], 2, ',', '.') ?>">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Fornecedor</label>
                            <input type="text" name="fornecedor" class="form-control" value="<?= e($produto['fornecedor']) ?>">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Observações</label>
                            <textarea name="observacoes" class="form-control" rows="2"><?= e($produto['observacoes']) ?></textarea>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-lg"></i> Salvar Alterações
                </button>
                <a href="<?= url('produtos') ?>" class="btn btn-outline-secondary">Cancelar</a>
            </div>
        </form>
    </div>
    
    <!-- Entrada de Estoque -->
    <div class="col-lg-4">
        <div class="card mb-3">
            <div class="card-header bg-success text-white">
                <h6 class="mb-0"><i class="bi bi-box-arrow-in-down"></i> Entrada de Estoque</h6>
            </div>
            <div class="card-body">
                <form method="POST" action="<?= url('produtos/entrada/' . $produto['id']) ?>">
                    <?= csrfField() ?>
                    <div class="mb-3">
                        <label class="form-label">Quantidade</label>
                        <input type="number" name="quantidade" class="form-control" step="0.01" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Custo Unitário (opcional)</label>
                        <div class="input-group">
                            <span class="input-group-text">R$</span>
                            <input type="text" name="custo_unitario_entrada" class="form-control" placeholder="Deixe vazio para manter o custo atual" id="custo_entrada_input">
                        </div>
                    </div>
                    
                    <!-- Campos de pagamento (aparecem quando tem custo) -->
                    <div id="dados_pagamento_entrada" style="display: none;">
                        <div class="mb-3">
                            <label class="form-label">Forma de Pagamento</label>
                            <select name="forma_pagamento_entrada" class="form-select">
                                <option value="dinheiro">Dinheiro</option>
                                <option value="pix" selected>PIX</option>
                                <option value="cartao_credito">Cartão de Crédito</option>
                                <option value="cartao_debito">Cartão de Débito</option>
                                <option value="boleto">Boleto</option>
                                <option value="transferencia">Transferência</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Status</label>
                            <select name="status_despesa_entrada" class="form-select">
                                <option value="pago" selected>Pago</option>
                                <option value="pendente">Pendente</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Data da Compra</label>
                            <input type="date" name="data_despesa_entrada" class="form-control" value="<?= date('Y-m-d') ?>">
                        </div>
                    </div>
                    
                    <script>
                    // Mostrar campos de pagamento quando custo for preenchido
                    document.getElementById('custo_entrada_input').addEventListener('blur', function() {
                        const valor = this.value.replace(/[.,]/g, '');
                        document.getElementById('dados_pagamento_entrada').style.display = valor > 0 ? 'block' : 'none';
                    });
                    </script>
                    <div class="mb-3">
                        <label class="form-label">Motivo</label>
                        <input type="text" name="motivo" class="form-control" placeholder="Ex: Compra, Devolução...">
                    </div>
                    <button type="submit" class="btn btn-success w-100">
                        <i class="bi bi-plus-lg"></i> Registrar Entrada
                    </button>
                </form>
            </div>
        </div>
        
        <!-- Info Atual -->
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0"><i class="bi bi-info-circle"></i> Estoque Atual</h6>
            </div>
            <div class="card-body">
                <h3 class="text-primary mb-1"><?= $produto['quantidade_estoque'] ?> <?= e($produto['unidade']) ?></h3>
                <p class="text-muted mb-0">Valor em Estoque: <?= formatMoney($produto['quantidade_estoque'] * $produto['custo_unitario']) ?></p>
            </div>
        </div>
    </div>
</div>
