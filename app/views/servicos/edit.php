<?= breadcrumb(['Dashboard' => 'dashboard', 'Serviços' => 'servicos', 'Editar: ' . e($servico['nome'])]) ?>

<div class="d-flex align-items-center mb-4">
    <a href="<?= url('servicos') ?>" class="btn btn-outline-secondary btn-sm me-3">
        <i class="bi bi-arrow-left"></i>
    </a>
    <div>
        <h4 class="mb-0">Editar Serviço</h4>
        <p class="mb-0 text-muted"><?= e($servico['nome']) ?></p>
    </div>
    <form method="POST" action="<?= url('servicos/delete/' . $servico['id']) ?>" class="ms-auto" onsubmit="return confirm('Tem certeza que deseja remover este serviço?')">
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

<form method="POST" action="<?= url('servicos/edit/' . $servico['id']) ?>">
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
                            <input type="text" name="nome" class="form-control" value="<?= e($servico['nome']) ?>" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Categoria</label>
                            <input type="text" name="categoria" class="form-control" value="<?= e($servico['categoria']) ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Valor Padrão</label>
                            <div class="input-group">
                                <span class="input-group-text">R$</span>
                                <input type="text" name="valor_padrao" class="form-control" value="<?= number_format($servico['valor_padrao'], 2, ',', '.') ?>">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Garantia (dias)</label>
                            <input type="number" name="garantia_dias" class="form-control" value="<?= $servico['garantia_dias'] ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Tempo Médio (horas)</label>
                            <input type="number" name="tempo_medio_horas" class="form-control" value="<?= $servico['tempo_medio_horas'] ?>" step="0.5">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Descrição Padrão</label>
                            <textarea name="descricao_padrao" class="form-control" rows="3"><?= e($servico['descricao_padrao']) ?></textarea>
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
                <a href="<?= url('servicos') ?>" class="btn btn-outline-secondary">Cancelar</a>
            </div>
        </div>
    </div>
</form>
