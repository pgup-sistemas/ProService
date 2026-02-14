<?= breadcrumb(['Dashboard' => 'dashboard', 'Clientes' => 'clientes', 'Novo Cliente']) ?>

<div class="d-flex align-items-center mb-4">
    <a href="<?= url('clientes') ?>" class="btn btn-outline-secondary btn-sm me-3">
        <i class="bi bi-arrow-left"></i>
    </a>
    <div>
        <h4 class="mb-0">Novo Cliente</h4>
        <p class="text-muted mb-0">Cadastre um novo cliente</p>
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

<form method="POST" action="<?= url('clientes/create') ?>">
    <?= csrfField() ?>
    
    <div class="row g-3">
        <!-- Dados Principais -->
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0"><i class="bi bi-person"></i> Dados Principais</h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label">Nome Completo *</label>
                            <input type="text" name="nome" class="form-control" value="<?= e($old['nome'] ?? '') ?>" required autofocus>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">CPF/CNPJ</label>
                            <input type="text" name="cpf_cnpj" class="form-control" value="<?= e($old['cpf_cnpj'] ?? '') ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Telefone</label>
                            <input type="text" name="telefone" class="form-control" value="<?= e($old['telefone'] ?? '') ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">WhatsApp</label>
                            <input type="text" name="whatsapp" class="form-control" value="<?= e($old['whatsapp'] ?? '') ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">E-mail</label>
                            <input type="email" name="email" class="form-control" value="<?= e($old['email'] ?? '') ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Data Nascimento</label>
                            <input type="date" name="data_nascimento" class="form-control" value="<?= e($old['data_nascimento'] ?? '') ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Como conheceu?</label>
                            <select name="como_conheceu" class="form-select">
                                <option value="">Selecione...</option>
                                <option value="indicacao" <?= ($old['como_conheceu'] ?? '') === 'indicacao' ? 'selected' : '' ?>>Indicação</option>
                                <option value="google" <?= ($old['como_conheceu'] ?? '') === 'google' ? 'selected' : '' ?>>Google</option>
                                <option value="redes_sociais" <?= ($old['como_conheceu'] ?? '') === 'redes_sociais' ? 'selected' : '' ?>>Redes Sociais</option>
                                <option value="outros" <?= ($old['como_conheceu'] ?? '') === 'outros' ? 'selected' : '' ?>>Outros</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Endereço -->
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0"><i class="bi bi-geo-alt"></i> Endereço</h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">CEP</label>
                            <input type="text" name="cep" class="form-control" value="<?= e($old['cep'] ?? '') ?>">
                        </div>
                        <div class="col-md-7">
                            <label class="form-label">Endereço</label>
                            <input type="text" name="endereco" class="form-control" value="<?= e($old['endereco'] ?? '') ?>">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Número</label>
                            <input type="text" name="numero" class="form-control" value="<?= e($old['numero'] ?? '') ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Complemento</label>
                            <input type="text" name="complemento" class="form-control" value="<?= e($old['complemento'] ?? '') ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Bairro</label>
                            <input type="text" name="bairro" class="form-control" value="<?= e($old['bairro'] ?? '') ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Cidade</label>
                            <input type="text" name="cidade" class="form-control" value="<?= e($old['cidade'] ?? '') ?>">
                        </div>
                        <div class="col-md-1">
                            <label class="form-label">UF</label>
                            <input type="text" name="estado" class="form-control" maxlength="2" value="<?= e($old['estado'] ?? '') ?>">
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Observações -->
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0"><i class="bi bi-sticky"></i> Observações</h6>
                </div>
                <div class="card-body">
                    <textarea name="observacoes" class="form-control" rows="3" placeholder="Observações internas sobre o cliente..."><?= e($old['observacoes'] ?? '') ?></textarea>
                </div>
            </div>
        </div>
        
        <!-- Botões -->
        <div class="col-12">
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-lg"></i> Salvar Cliente
                </button>
                <a href="<?= url('clientes') ?>" class="btn btn-outline-secondary">Cancelar</a>
            </div>
        </div>
    </div>
</form>
