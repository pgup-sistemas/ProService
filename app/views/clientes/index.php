<?= breadcrumb(['Dashboard' => 'dashboard', 'Clientes']) ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1">Clientes</h4>
        <p class="text-muted mb-0">Gerencie seus clientes</p>
    </div>
    <a href="<?= url('clientes/create') ?>" class="btn btn-primary">
        <i class="bi bi-plus-lg"></i> Novo Cliente
    </a>
</div>

<!-- Busca -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-8">
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                    <input type="text" name="busca" class="form-control" placeholder="Buscar por nome, telefone ou CPF..." value="<?= e($busca ?? '') ?>">
                    <button type="submit" class="btn btn-primary">Buscar</button>
                    <?php if (!empty($busca)): ?>
                        <a href="<?= url('clientes') ?>" class="btn btn-outline-secondary">Limpar</a>
                    <?php endif; ?>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Lista de Clientes -->
<div class="card">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>Cliente</th>
                    <th>Contato</th>
                    <th>Cidade/UF</th>
                    <th class="text-end">Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($clientes)): ?>
                    <tr>
                        <td colspan="4" class="text-center py-4 text-muted">
                            <i class="bi bi-inbox fs-1"></i>
                            <p class="mb-0">Nenhum cliente encontrado</p>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($clientes as $cliente): ?>
                    <tr>
                        <td>
                            <div class="fw-medium"><?= e($cliente['nome']) ?></div>
                            <?php if ($cliente['cpf_cnpj']): ?>
                                <small class="text-muted"><?= formatCpfCnpj($cliente['cpf_cnpj']) ?></small>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($cliente['telefone']): ?>
                                <div><i class="bi bi-telephone text-muted"></i> <?= formatPhone($cliente['telefone']) ?></div>
                            <?php endif; ?>
                            <?php if ($cliente['whatsapp']): ?>
                                <div><i class="bi bi-whatsapp text-success"></i> <?= formatPhone($cliente['whatsapp']) ?></div>
                            <?php endif; ?>
                            <?php if ($cliente['email']): ?>
                                <div><i class="bi bi-envelope text-muted"></i> <?= e($cliente['email']) ?></div>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?= e($cliente['cidade']) ?><?= $cliente['estado'] ? '/' . e($cliente['estado']) : '' ?>
                        </td>
                        <td class="text-end">
                            <div class="btn-group">
                                <a href="<?= url('clientes/edit/' . $cliente['id']) ?>" class="btn btn-sm btn-outline-primary" title="Editar">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <a href="tel:<?= preg_replace('/\D/', '', $cliente['telefone'] ?? $cliente['whatsapp']) ?>" class="btn btn-sm btn-outline-success" title="Ligar">
                                    <i class="bi bi-telephone"></i>
                                </a>
                                <a href="<?= url('ordens/create?cliente_id=' . $cliente['id']) ?>" class="btn btn-sm btn-outline-info" title="Nova OS">
                                    <i class="bi bi-clipboard-plus"></i>
                                </a>
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
                    <a class="page-link" href="<?= url('clientes?page=' . $i . '&busca=' . urlencode($busca ?? '')) ?>"><?= $i ?></a>
                </li>
                <?php endfor; ?>
            </ul>
        </nav>
    </div>
    <?php endif; ?>
</div>
