<?= breadcrumb(['Dashboard' => 'dashboard', 'Ordens de Serviço' => 'ordens', 'Lista']) ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1">Ordens de Serviço</h4>
        <p class="text-muted mb-0">Gerencie suas OS</p>
    </div>
    <a href="<?= url('ordens/create') ?>" class="btn btn-primary">
        <i class="bi bi-plus-lg"></i> Nova OS
    </a>
</div>

<!-- Filtros -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-3">
                <input type="text" name="busca" class="form-control" placeholder="Buscar OS # ou cliente..." value="<?= e($filtros['busca'] ?? '') ?>">
            </div>
            <div class="col-md-2">
                <select name="status" class="form-select">
                    <option value="">Todos Status</option>
                    <option value="aberta" <?= ($filtros['status'] ?? '') === 'aberta' ? 'selected' : '' ?>>Aberta</option>
                    <option value="em_orcamento" <?= ($filtros['status'] ?? '') === 'em_orcamento' ? 'selected' : '' ?>>Em Orçamento</option>
                    <option value="aprovada" <?= ($filtros['status'] ?? '') === 'aprovada' ? 'selected' : '' ?>>Aprovada</option>
                    <option value="em_execucao" <?= ($filtros['status'] ?? '') === 'em_execucao' ? 'selected' : '' ?>>Em Execução</option>
                    <option value="finalizada" <?= ($filtros['status'] ?? '') === 'finalizada' ? 'selected' : '' ?>>Finalizada</option>
                    <option value="paga" <?= ($filtros['status'] ?? '') === 'paga' ? 'selected' : '' ?>>Paga</option>
                </select>
            </div>
            <div class="col-md-2">
                <select name="prioridade" class="form-select">
                    <option value="">Todas Prioridades</option>
                    <option value="urgente" <?= ($filtros['prioridade'] ?? '') === 'urgente' ? 'selected' : '' ?>>Urgente</option>
                    <option value="alta" <?= ($filtros['prioridade'] ?? '') === 'alta' ? 'selected' : '' ?>>Alta</option>
                    <option value="normal" <?= ($filtros['prioridade'] ?? '') === 'normal' ? 'selected' : '' ?>>Normal</option>
                </select>
            </div>
            <script>
            // Autocomplete para filtro de cliente (index)
            (function(){
                const input = document.getElementById('cliente_busca_filter');
                if (!input) return;
                const hidden = document.getElementById('cliente_id_filter');
                const resultados = document.getElementById('resultados_cliente_filter');
                let timeout;
                input.addEventListener('input', function(){
                    clearTimeout(timeout);
                    hidden.value = '';
                    const termo = this.value.trim();
                    if (termo.length < 2) { resultados.innerHTML = ''; return; }
                    timeout = setTimeout(() => {
                        fetch('<?= url("api/clientes/buscar") ?>?q=' + encodeURIComponent(termo))
                            .then(r => r.json())
                            .then(data => {
                                resultados.innerHTML = data.map(c => `
                                    <button type="button" class="list-group-item list-group-item-action" data-id="${c.id}" data-nome="${c.nome}">${c.nome} <small class="text-muted">${c.telefone||''}</small></button>
                                `).join('');
                            });
                    }, 250);
                });
                resultados.addEventListener('click', function(e){
                    const btn = e.target.closest('button'); if(!btn) return;
                    hidden.value = btn.dataset.id; input.value = btn.dataset.nome; resultados.innerHTML = '';
                });
            })();
            </script>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100"><i class="bi bi-funnel"></i> Filtrar</button>
            </div>
        </form>
    </div>
</div>

<!-- Lista de OS -->
<div class="card">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>OS</th>
                    <th>Cliente</th>
                    <th>Serviço</th>
                    <th>Status</th>
                    <th>Prioridade</th>
                    <th>Valor</th>
                    <th class="text-end">Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($ordens)): ?>
                    <tr>
                        <td colspan="7" class="text-center py-4 text-muted">
                            <i class="bi bi-inbox fs-1"></i>
                            <p class="mb-0">Nenhuma OS encontrada</p>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($ordens as $os): ?>
                    <tr>
                        <td>
                            <strong>#<?= str_pad($os['numero_os'], 4, '0', STR_PAD_LEFT) ?></strong>
                            <br><small class="text-muted"><?= formatDate($os['data_entrada']) ?></small>
                        </td>
                        <td><?= e($os['cliente_nome'] ?? '-') ?></td>
                        <td><?= e($os['servico_nome'] ?? '-') ?></td>
                        <td>
                            <span class="badge <?= getStatusClass($os['status']) ?>">
                                <?= getStatusLabel($os['status']) ?>
                            </span>
                        </td>
                        <td>
                            <span class="badge <?= getPrioridadeClass($os['prioridade']) ?>">
                                <?= getPrioridadeLabel($os['prioridade']) ?>
                            </span>
                        </td>
                        <td><?= formatMoney($os['valor_total']) ?></td>
                        <td class="text-end">
                            <div class="btn-group btn-group-sm">
                                <a href="<?= url('ordens/show/' . $os['id']) ?>" class="btn btn-outline-primary" title="Visualizar">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <?php if (!in_array($os['status'], ['cancelada', 'paga'])): ?>
                                    <a href="<?= url('ordens/edit/' . $os['id']) ?>" class="btn btn-outline-success" title="Editar">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                <?php endif; ?>
                                <?php if (isAdmin() && !in_array($os['status'], ['paga'])): ?>
                                    <form method="POST" action="<?= url('ordens/destroy/' . $os['id']) ?>" class="d-inline" onsubmit="return confirm('Tem certeza que deseja excluir esta OS?');">
                                        <?= csrfField() ?>
                                        <button type="submit" class="btn btn-outline-danger" title="Excluir">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </div>
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
                    <a class="page-link" href="<?= url('ordens?page=' . $i) ?>"><?= $i ?></a>
                </li>
                <?php endfor; ?>
            </ul>
        </nav>
    </div>
    <?php endif; ?>
</div>
