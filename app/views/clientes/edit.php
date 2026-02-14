<?= breadcrumb(['Dashboard' => 'dashboard', 'Clientes' => 'clientes', 'Editar: ' . e($cliente['nome'])]) ?>

<div class="d-flex align-items-center mb-4">
    <a href="<?= url('clientes') ?>" class="btn btn-outline-secondary btn-sm me-3">
        <i class="bi bi-arrow-left"></i>
    </a>
    <div>
        <h4 class="mb-0">Editar Cliente</h4>
        <p class="mb-0 text-muted"><?= e($cliente['nome']) ?></p>
    </div>
    <form method="POST" action="<?= url('clientes/delete/' . $cliente['id']) ?>" class="ms-auto" onsubmit="return confirm('Tem certeza que deseja remover este cliente?')">
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
        <form method="POST" action="<?= url('clientes/edit/' . $cliente['id']) ?>">
            <?= csrfField() ?>
            
            <div class="card mb-3">
                <div class="card-header">
                    <h6 class="mb-0"><i class="bi bi-person"></i> Dados Principais</h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label">Nome Completo *</label>
                            <input type="text" name="nome" class="form-control" value="<?= e($cliente['nome']) ?>" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">CPF/CNPJ</label>
                            <input type="text" name="cpf_cnpj" class="form-control" value="<?= e($cliente['cpf_cnpj']) ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Telefone</label>
                            <input type="text" name="telefone" class="form-control" value="<?= e($cliente['telefone']) ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">WhatsApp</label>
                            <input type="text" name="whatsapp" class="form-control" value="<?= e($cliente['whatsapp']) ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">E-mail</label>
                            <input type="email" name="email" class="form-control" value="<?= e($cliente['email']) ?>">
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="card mb-3">
                <div class="card-header">
                    <h6 class="mb-0"><i class="bi bi-geo-alt"></i> Endereço</h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">CEP</label>
                            <input type="text" name="cep" class="form-control" value="<?= e($cliente['cep']) ?>">
                        </div>
                        <div class="col-md-7">
                            <label class="form-label">Endereço</label>
                            <input type="text" name="endereco" class="form-control" value="<?= e($cliente['endereco']) ?>">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Número</label>
                            <input type="text" name="numero" class="form-control" value="<?= e($cliente['numero']) ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Complemento</label>
                            <input type="text" name="complemento" class="form-control" value="<?= e($cliente['complemento']) ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Bairro</label>
                            <input type="text" name="bairro" class="form-control" value="<?= e($cliente['bairro']) ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Cidade</label>
                            <input type="text" name="cidade" class="form-control" value="<?= e($cliente['cidade']) ?>">
                        </div>
                        <div class="col-md-1">
                            <label class="form-label">UF</label>
                            <input type="text" name="estado" class="form-control" maxlength="2" value="<?= e($cliente['estado']) ?>">
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-lg"></i> Salvar Alterações
                </button>
                <a href="<?= url('clientes') ?>" class="btn btn-outline-secondary">Cancelar</a>
            </div>
        </form>
    </div>
    
    <!-- Histórico -->
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0"><i class="bi bi-clock-history"></i> Histórico</h6>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <small class="text-muted">Total Gasto</small>
                    <h5 class="mb-0"><?= formatMoney($historico['total_gasto']) ?></h5>
                </div>
                <div class="mb-3">
                    <small class="text-muted">Serviços Realizados</small>
                    <h5 class="mb-0"><?= $historico['total_os'] ?></h5>
                </div>
                <div class="mb-3">
                    <small class="text-muted">Ticket Médio</small>
                    <h5 class="mb-0"><?= formatMoney($historico['ticket_medio']) ?></h5>
                </div>
                <div class="mb-3">
                    <small class="text-muted">OS em Aberto</small>
                    <h5 class="mb-0"><?= $historico['os_em_aberto'] ?></h5>
                </div>
                <hr>
                <a href="<?= url('ordens?cliente_id=' . $cliente['id']) ?>" class="btn btn-outline-primary w-100">
                    <i class="bi bi-clipboard-data"></i> Ver Todas as OS
                </a>
            </div>
        </div>
    </div>
</div>
