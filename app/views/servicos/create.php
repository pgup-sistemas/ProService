<?= breadcrumb(['Dashboard' => 'dashboard', 'Serviços' => 'servicos', 'Novo Serviço']) ?>

<div class="d-flex align-items-center mb-4">
    <a href="<?= url('servicos') ?>" class="btn btn-outline-secondary btn-sm me-3">
        <i class="bi bi-arrow-left"></i>
    </a>
    <div>
        <h4 class="mb-0">Novo Serviço</h4>
        <p class="text-muted mb-0">Cadastre um serviço</p>
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

<form method="POST" action="<?= url('servicos/create') ?>">
    <?= csrfField() ?>
    
    <div class="row g-3">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0"><i class="bi bi-tools"></i> Informações do Serviço</h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Nome do Serviço *</label>
                            <input type="text" name="nome" class="form-control" value="<?= e($old['nome'] ?? '') ?>" required autofocus>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Categoria</label>
                            <input type="text" name="categoria" class="form-control" value="<?= e($old['categoria'] ?? '') ?>" placeholder="Ex: Elétrica">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Valor Padrão</label>
                            <div class="input-group">
                                <span class="input-group-text">R$</span>
                                <input type="text" name="valor_padrao" class="form-control" value="<?= e($old['valor_padrao'] ?? '0,00') ?>">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Garantia (dias)</label>
                            <input type="number" name="garantia_dias" class="form-control" value="<?= e($old['garantia_dias'] ?? '0') ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Tempo Médio (horas)</label>
                            <input type="number" name="tempo_medio_horas" class="form-control" value="<?= e($old['tempo_medio_horas'] ?? '0') ?>" step="0.5">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Descrição Padrão</label>
                            <textarea name="descricao_padrao" class="form-control" rows="3" placeholder="Descrição que aparecerá automaticamente na OS..."><?= e($old['descricao_padrao'] ?? '') ?></textarea>
                            <small class="text-muted">Esta descrição será pré-preenchida ao selecionar este serviço na OS</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-12">
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-lg"></i> Salvar Serviço
                </button>
                <a href="<?= url('servicos') ?>" class="btn btn-outline-secondary">Cancelar</a>
            </div>
        </div>
    </div>
</form>
