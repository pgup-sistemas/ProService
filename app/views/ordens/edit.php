<?= breadcrumb(['Dashboard' => 'dashboard', 'Ordens de Serviço' => 'ordens', 'Editar OS #' . str_pad($os['numero_os'], 4, '0', STR_PAD_LEFT)]) ?>

<div class="d-flex align-items-center mb-4">
    <a href="<?= url('ordens/show/' . $os['id']) ?>" class="btn btn-outline-secondary btn-sm me-3">
        <i class="bi bi-arrow-left"></i>
    </a>
    <div>
        <h4 class="mb-0"><i class="bi bi-pencil-square"></i> Editar OS #<?= str_pad($os['numero_os'], 4, '0', STR_PAD_LEFT) ?></h4>
        <p class="text-muted mb-0">Altere os dados da ordem de serviço</p>
    </div>
</div>

<?php if (!empty($_SESSION['errors'])): ?>
<div class="alert alert-danger">
    <i class="bi bi-exclamation-triangle-fill me-2"></i>
    <?php foreach ($_SESSION['errors'] as $error): ?>
        <div><?= e($error) ?></div>
    <?php endforeach; ?>
    <?php unset($_SESSION['errors']); ?>
</div>
<?php endif; ?>

<form method="POST" action="<?= url('ordens/update/' . $os['id']) ?>" id="formOS">
    <?= csrfField() ?>
    
    <div class="row g-3">
        <!-- Cliente, Serviço e Técnico -->
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-light-subtle">
                    <h6 class="mb-0"><i class="bi bi-person"></i> Cliente e Serviço</h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label fw-bold"><i class="bi bi-person-badge"></i> Cliente *</label>
                            <select name="cliente_id" class="form-select" required>
                                <option value="">Selecione um cliente...</option>
                                <?php foreach ($clientes as $c): ?>
                                <option value="<?= $c['id'] ?>" <?= $os['cliente_id'] == $c['id'] ? 'selected' : '' ?>><?= e($c['nome']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold"><i class="bi bi-tools"></i> Serviço</label>
                            <select name="servico_id" class="form-select" id="servico_select">
                                <option value="">Selecione um serviço...</option>
                                <?php foreach ($servicos as $s): ?>
                                <option value="<?= $s['id'] ?>" 
                                        data-valor="<?= $s['valor_padrao'] ?>" 
                                        data-garantia="<?= $s['garantia_dias'] ?>"
                                        <?= $os['servico_id'] == $s['id'] ? 'selected' : '' ?>>
                                    <?= e($s['nome']) ?> - <?= formatMoney($s['valor_padrao']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold"><i class="bi bi-person-gear"></i> Técnico Responsável</label>
                            <?php if (($perfilAtual ?? '') === 'tecnico'): ?>
                                <input type="hidden" name="tecnico_id" value="<?= (int) ($usuarioAtualId ?? 0) ?>">
                                <div class="form-control bg-light">
                                    <i class="bi bi-person-check"></i> <?= e($tecnicos[0]['nome'] ?? 'Técnico atual') ?>
                                </div>
                            <?php else: ?>
                                <select name="tecnico_id" class="form-select">
                                    <option value="">Selecione...</option>
                                    <?php foreach ($tecnicos as $t): ?>
                                    <option value="<?= $t['id'] ?>" <?= $os['tecnico_id'] == $t['id'] ? 'selected' : '' ?>><?= e($t['nome']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Detalhes da OS -->
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header bg-light-subtle">
                    <h6 class="mb-0"><i class="bi bi-clipboard-data"></i> Detalhes</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label"><i class="bi bi-flag"></i> Prioridade</label>
                        <select name="prioridade" class="form-select">
                            <option value="normal" <?= $os['prioridade'] === 'normal' ? 'selected' : '' ?>>Normal</option>
                            <option value="urgente" <?= $os['prioridade'] === 'urgente' ? 'selected' : '' ?>>Urgente</option>
                            <option value="alta" <?= $os['prioridade'] === 'alta' ? 'selected' : '' ?>>Alta</option>
                            <option value="baixa" <?= $os['prioridade'] === 'baixa' ? 'selected' : '' ?>>Baixa</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label"><i class="bi bi-calendar-event"></i> Previsão de Entrega</label>
                        <input type="date" name="previsao_entrega" class="form-control" value="<?= $os['previsao_entrega'] ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label"><i class="bi bi-file-text"></i> Descrição do Problema/Serviço</label>
                        <textarea name="descricao" class="form-control" rows="3"><?= e($os['descricao']) ?></textarea>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Valores -->
        <div class="col-md-6">
            <div class="card h-100 border-info">
                <div class="card-header bg-light-subtle">
                    <h6 class="mb-0"><i class="bi bi-cash-stack"></i> Valores da OS</h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-primary"><i class="bi bi-currency-dollar"></i> Valor do Serviço</label>
                            <div class="input-group input-group-lg">
                                <span class="input-group-text">R$</span>
                                <input type="text" name="valor_servico" class="form-control money" id="valor_servico" value="<?= number_format($os['valor_servico'], 2, ',', '.') ?>">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-warning"><i class="bi bi-plus-circle"></i> Taxas Adicionais</label>
                            <div class="input-group input-group-lg">
                                <span class="input-group-text">R$</span>
                                <input type="text" name="taxas_adicionais" class="form-control money" id="taxas_adicionais" value="<?= number_format($os['taxas_adicionais'], 2, ',', '.') ?>">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-success"><i class="bi bi-dash-circle"></i> Desconto</label>
                            <div class="input-group input-group-lg">
                                <span class="input-group-text">R$</span>
                                <input type="text" name="desconto" class="form-control money" id="desconto" value="<?= number_format($os['desconto'], 2, ',', '.') ?>">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold"><i class="bi bi-wallet2"></i> Forma de Pagamento</label>
                            <select name="forma_pagamento" class="form-select">
                                <option value="dinheiro" <?= $os['forma_pagamento_acordada'] === 'dinheiro' ? 'selected' : '' ?>>Dinheiro</option>
                                <option value="pix" <?= $os['forma_pagamento_acordada'] === 'pix' ? 'selected' : '' ?>>PIX</option>
                                <option value="cartao_credito" <?= $os['forma_pagamento_acordada'] === 'cartao_credito' ? 'selected' : '' ?>>Cartão de Crédito</option>
                                <option value="cartao_debito" <?= $os['forma_pagamento_acordada'] === 'cartao_debito' ? 'selected' : '' ?>>Cartão de Débito</option>
                                <option value="boleto" <?= $os['forma_pagamento_acordada'] === 'boleto' ? 'selected' : '' ?>>Boleto</option>
                                <option value="transferencia" <?= $os['forma_pagamento_acordada'] === 'transferencia' ? 'selected' : '' ?>>Transferência</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label"><i class="bi bi-shield-check"></i> Garantia (dias)</label>
                            <input type="number" name="garantia_dias" class="form-control" id="garantia_dias" value="<?= $os['garantia_dias'] ?>" min="0">
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-light">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="h5 mb-0"><i class="bi bi-calculator"></i> Valor Total:</span>
                        <span class="h3 mb-0 text-success" id="display_total">R$ <?= number_format($os['valor_total'], 2, ',', '.') ?></span>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Produtos Utilizados -->
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0"><i class="bi bi-box-seam"></i> Produtos Utilizados</h6>
                </div>
                <div class="card-body">
                    <div class="row g-2 mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Buscar Produto</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-search"></i></span>
                                <input type="text" id="busca_produto" class="form-control" placeholder="Digite o nome do produto...">
                            </div>
                            <div id="resultados_produto" class="list-group position-absolute w-100" style="z-index: 1000; max-height: 200px; overflow-y: auto;"></div>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Quantidade</label>
                            <input type="number" id="qtd_produto" class="form-control" value="1" min="1" step="1">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Preço Unit.</label>
                            <div class="input-group">
                                <span class="input-group-text">R$</span>
                                <input type="text" id="preco_produto" class="form-control money" value="0,00">
                            </div>
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="button" class="btn btn-success w-100" id="btn_add_produto">
                                <i class="bi bi-plus-lg"></i> Adicionar
                            </button>
                        </div>
                    </div>
                    
                    <div id="produtos_selecionados">
                        <?php if (empty($produtosOS)): ?>
                            <p class="text-muted">Nenhum produto adicionado. Busque e adicione produtos acima.</p>
                        <?php else: ?>
                            <table class="table table-sm">
                                <thead>
                                    <tr><th>Produto</th><th>Qtd</th><th>Preço Unit.</th><th>Subtotal</th><th></th></tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($produtosOS as $i => $p): ?>
                                    <tr>
                                        <td><?= e($p['produto_nome']) ?></td>
                                        <td><?= $p['quantidade'] ?></td>
                                        <td>R$ <?= number_format($p['preco_unitario'] ?? $p['custo_unitario'], 2, ',', '.') ?></td>
                                        <td>R$ <?= number_format(($p['preco_total'] ?? $p['custo_total']), 2, ',', '.') ?></td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-outline-danger" onclick="removerProduto(<?= $i ?>)">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                                <tfoot>
                                    <tr class="table-info">
                                        <td colspan="3" class="text-end fw-bold">Total em Produtos:</td>
                                        <td class="fw-bold">R$ <?= number_format(array_sum(array_column($produtosOS, 'preco_total')), 2, ',', '.') ?></td>
                                        <td></td>
                                    </tr>
                                </tfoot>
                            </table>
                            <?php foreach ($produtosOS as $i => $p): ?>
                                <input type="hidden" name="produtos[<?= $i ?>][id]" value="<?= $p['produto_id'] ?>">
                                <input type="hidden" name="produtos[<?= $i ?>][quantidade]" value="<?= $p['quantidade'] ?>">
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Observações -->
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0"><i class="bi bi-chat-square-text"></i> Observações</h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label"><i class="bi bi-eye-slash"></i> Observações Internas</label>
                            <textarea name="observacoes_internas" class="form-control" rows="3"><?= e($os['observacoes_internas']) ?></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label"><i class="bi bi-eye"></i> Observações para o Cliente</label>
                            <textarea name="observacoes_cliente" class="form-control" rows="3"><?= e($os['observacoes_cliente']) ?></textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-12">
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-lg"></i> Salvar Alterações
                </button>
                <a href="<?= url('ordens/show/' . $os['id']) ?>" class="btn btn-outline-secondary">
                    <i class="bi bi-x-lg"></i> Cancelar
                </a>
            </div>
        </div>
    </div>
</form>

<script>
// Array para armazenar produtos selecionados - carregar existentes
let produtosOS = <?= json_encode(array_map(function($p) {
    return [
        'id' => $p['produto_id'],
        'nome' => $p['produto_nome'],
        'quantidade' => (float) $p['quantidade'],
        'preco_unitario' => (float) ($p['preco_unitario'] ?? $p['custo_unitario']),
        'subtotal' => (float) ($p['preco_total'] ?? $p['custo_total'])
    ];
}, $produtosOS ?? [])) ?>;

// ... (mesmos scripts do create.php para busca, adicionar, remover produtos)

// Busca de produtos
let timeoutBusca;
document.getElementById('busca_produto').addEventListener('input', function() {
    clearTimeout(timeoutBusca);
    const termo = this.value.trim();
    
    if (termo.length < 2) {
        document.getElementById('resultados_produto').innerHTML = '';
        return;
    }
    
    timeoutBusca = setTimeout(() => {
        fetch('<?= url("api/produtos/buscar") ?>?q=' + encodeURIComponent(termo))
            .then(r => r.json())
            .then(data => {
                const div = document.getElementById('resultados_produto');
                if (data.length === 0) {
                    div.innerHTML = '<div class="list-group-item text-muted">Nenhum produto encontrado</div>';
                    return;
                }
                div.innerHTML = data.map(p => `
                    <button type="button" class="list-group-item list-group-item-action" 
                            onclick="selecionarProduto(${p.id}, '${p.nome}', ${p.preco_venda || 0}, ${p.quantidade_estoque || 0})">
                        <div class="d-flex justify-content-between">
                            <span>${p.nome}</span>
                            <span class="text-primary">R$ ${(p.preco_venda || 0).toFixed(2).replace('.', ',')}</span>
                        </div>
                        <small class="text-muted">Estoque: ${p.quantidade_estoque || 0}</small>
                    </button>
                `).join('');
            });
    }, 300);
});

// Selecionar produto
function selecionarProduto(id, nome, preco, estoque) {
    document.getElementById('busca_produto').value = nome;
    document.getElementById('busca_produto').dataset.produtoId = id;
    document.getElementById('preco_produto').value = preco.toFixed(2).replace('.', ',');
    document.getElementById('resultados_produto').innerHTML = '';
    
    if (estoque <= 0) {
        alert('Produto sem estoque disponível!');
    }
}

// Adicionar produto
document.getElementById('btn_add_produto').addEventListener('click', function() {
    const busca = document.getElementById('busca_produto');
    const produtoId = busca.dataset.produtoId;
    const nome = busca.value.trim();
    const qtd = parseInt(document.getElementById('qtd_produto').value) || 1;
    const preco = parseFloat(document.getElementById('preco_produto').value.replace('.', '').replace(',', '.')) || 0;
    
    if (!nome) {
        alert('Selecione um produto primeiro');
        return;
    }
    
    produtosOS.push({
        id: produtoId || Date.now(),
        nome: nome,
        quantidade: qtd,
        preco_unitario: preco,
        subtotal: qtd * preco
    });
    
    atualizarListaProdutos();
    calcularTotal();
    
    // Limpar campos
    busca.value = '';
    busca.dataset.produtoId = '';
    document.getElementById('qtd_produto').value = 1;
    document.getElementById('preco_produto').value = '0,00';
});

// Atualizar lista de produtos
function atualizarListaProdutos() {
    const div = document.getElementById('produtos_selecionados');
    if (produtosOS.length === 0) {
        div.innerHTML = '<p class="text-muted">Nenhum produto adicionado. Busque e adicione produtos acima.</p>';
        return;
    }
    
    let html = '<table class="table table-sm"><thead><tr><th>Produto</th><th>Qtd</th><th>Preço Unit.</th><th>Subtotal</th><th></th></tr></thead><tbody>';
    let totalProdutos = 0;
    
    produtosOS.forEach((p, index) => {
        totalProdutos += p.subtotal;
        html += `<tr>
            <td>${p.nome}</td>
            <td>${p.quantidade}</td>
            <td>R$ ${p.preco_unitario.toFixed(2).replace('.', ',')}</td>
            <td>R$ ${p.subtotal.toFixed(2).replace('.', ',')}</td>
            <td><button type="button" class="btn btn-sm btn-outline-danger" onclick="removerProduto(${index})"><i class="bi bi-trash"></i></button></td>
        </tr>`;
    });
    
    html += `</tbody><tfoot><tr class="table-info"><td colspan="3" class="text-end fw-bold">Total em Produtos:</td><td class="fw-bold">R$ ${totalProdutos.toFixed(2).replace('.', ',')}</td><td></td></tr></tfoot></table>`;
    
    // Inputs hidden para enviar os produtos
    html += produtosOS.map((p, i) => `
        <input type="hidden" name="produtos[${i}][id]" value="${p.id}">
        <input type="hidden" name="produtos[${i}][quantidade]" value="${p.quantidade}">
    `).join('');
    
    div.innerHTML = html;
}

// Remover produto
function removerProduto(index) {
    produtosOS.splice(index, 1);
    atualizarListaProdutos();
    calcularTotal();
}

// Calcular total
calcularTotal = function() {
    const valor = parseFloat(document.getElementById('valor_servico').value.replace('.', '').replace(',', '.')) || 0;
    const taxas = parseFloat(document.getElementById('taxas_adicionais').value.replace('.', '').replace(',', '.')) || 0;
    const desconto = parseFloat(document.getElementById('desconto').value.replace('.', '').replace(',', '.')) || 0;
    
    // Somar total dos produtos
    let totalProdutos = 0;
    produtosOS.forEach(p => {
        totalProdutos += p.subtotal;
    });

    const total = valor + taxas + totalProdutos - desconto;
    document.getElementById('display_total').textContent = 'R$ ' + total.toFixed(2).replace('.', ',');
};

// Serviço select
document.getElementById('servico_select').addEventListener('change', function() {
    const option = this.options[this.selectedIndex];
    const valor = option.getAttribute('data-valor');
    const garantia = option.getAttribute('data-garantia');
    
    if (valor) {
        document.getElementById('valor_servico').value = parseFloat(valor).toFixed(2).replace('.', ',');
        calcularTotal();
    }
    if (garantia) {
        document.getElementById('garantia_dias').value = garantia;
    }
});

// Eventos para cálculo
['valor_servico', 'taxas_adicionais', 'desconto'].forEach(id => {
    document.getElementById(id).addEventListener('blur', calcularTotal);
});

// Inicializar
calcularTotal();
</script>
