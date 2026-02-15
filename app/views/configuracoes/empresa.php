<?php
/**
 * proService - Configurações: Dados da Empresa
 * Arquivo: /app/views/configuracoes/empresa.php
 */
?>

<?= breadcrumb(['Dashboard' => 'dashboard', 'Configurações' => 'configuracoes', 'Dados da Empresa']) ?>

<div class="container-fluid">
    <div class="mb-4">
        <h2 class="mb-0"><i class="bi bi-gear text-primary"></i> Configurações</h2>
        <p class="text-muted">Gerencie as configurações do sistema</p>
    </div>

    <!-- Tabs -->
    <ul class="nav nav-tabs mb-4">
        <li class="nav-item">
            <a class="nav-link active" href="<?= url('configuracoes/empresa') ?>">
                <i class="bi bi-building"></i> Dados da Empresa
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="<?= url('configuracoes/aparencia') ?>">
                <i class="bi bi-palette"></i> Aparência
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="<?= url('configuracoes/comunicacao') ?>">
                <i class="bi bi-chat-dots"></i> Comunicação
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="<?= url('configuracoes/contrato') ?>">
                <i class="bi bi-file-text"></i> Contrato
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="<?= url('configuracoes/plano') ?>">
                <i class="bi bi-credit-card"></i> Meu Plano
            </a>
        </li>
    </ul>

    <div class="card">
        <div class="card-header">
            <h5 class="mb-0"><i class="bi bi-building"></i> Informações Cadastrais</h5>
        </div>
        <div class="card-body">
            <form method="POST" action="<?= url('configuracoes/empresa') ?>" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
                
                <!-- Logo -->
                <div class="mb-4">
                    <label class="form-label fw-bold">Logo da Empresa</label>
                    <div class="border rounded p-3 text-center bg-light">
                        <?php if (!empty($empresa['logo'])): ?>
                        <div class="mb-3">
                            <img src="<?= uploadUrl($empresa['logo']) ?>?t=<?= time() ?>" alt="Logo" class="img-thumbnail" style="max-height: 120px;">
                            <div class="mt-2">
                                <a href="<?= url('configuracoes/remover-logo') ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Remover logo?')">
                                    <i class="bi bi-trash"></i> Remover
                                </a>
                            </div>
                        </div>
                        <?php else: ?>
                        <div class="mb-3 text-muted">
                            <i class="bi bi-image" style="font-size: 3rem;"></i>
                            <p class="mb-0">Nenhum logo cadastrado</p>
                        </div>
                        <?php endif; ?>
                        <input type="file" name="logo" class="form-control" accept="image/jpeg,image/png,image/gif,image/webp">
                        <small class="text-muted">Formatos: JPG, PNG, GIF, WEBP. Tamanho máximo: 2MB</small>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Nome Fantasia <span class="text-danger">*</span></label>
                        <input type="text" name="nome_fantasia" class="form-control" value="<?= e($empresa['nome_fantasia'] ?? '') ?>" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Razão Social</label>
                        <input type="text" name="razao_social" class="form-control" value="<?= e($empresa['razao_social'] ?? '') ?>">
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">CNPJ/CPF <span class="text-danger">*</span></label>
                        <input type="text" name="cnpj_cpf" class="form-control cnpj-cpf" value="<?= e($empresa['cnpj_cpf'] ?? '') ?>" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">E-mail <span class="text-danger">*</span></label>
                        <input type="email" name="email" class="form-control" value="<?= e($empresa['email'] ?? '') ?>" required>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Telefone</label>
                        <input type="tel" name="telefone" class="form-control telefone" value="<?= e($empresa['telefone'] ?? '') ?>">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">WhatsApp Comercial</label>
                        <input type="tel" name="whatsapp" class="form-control telefone" value="<?= e($empresa['whatsapp'] ?? '') ?>">
                        <small class="text-muted">Será usado nos links de WhatsApp</small>
                    </div>
                </div>

                <hr class="my-4">
                <h6 class="text-muted mb-3"><i class="bi bi-geo-alt"></i> Endereço</h6>
                
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <label class="form-label">CEP</label>
                        <input type="text" name="cep" class="form-control cep" value="<?= e($empresa['cep'] ?? '') ?>" maxlength="9" id="cep">
                    </div>
                    <div class="col-md-7 mb-3">
                        <label class="form-label">Endereço</label>
                        <input type="text" name="endereco" class="form-control" value="<?= e($empresa['endereco'] ?? '') ?>" id="endereco">
                    </div>
                    <div class="col-md-2 mb-3">
                        <label class="form-label">Nº</label>
                        <input type="text" name="numero" class="form-control" value="<?= e($empresa['numero'] ?? '') ?>">
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Complemento</label>
                        <input type="text" name="complemento" class="form-control" value="<?= e($empresa['complemento'] ?? '') ?>">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Bairro</label>
                        <input type="text" name="bairro" class="form-control" value="<?= e($empresa['bairro'] ?? '') ?>" id="bairro">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Cidade</label>
                        <input type="text" name="cidade" class="form-control" value="<?= e($empresa['cidade'] ?? '') ?>" id="cidade">
                    </div>
                    <div class="col-md-1 mb-3">
                        <label class="form-label">UF</label>
                        <input type="text" name="estado" class="form-control" value="<?= e($empresa['estado'] ?? '') ?>" maxlength="2" id="estado">
                    </div>
                </div>

                <hr class="my-4">
                <h6 class="text-muted mb-3"><i class="bi bi-bank"></i> Dados Bancários (para recibos)</h6>
                
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Banco</label>
                        <input type="text" name="banco_nome" class="form-control" value="<?= e($empresa['banco_nome'] ?? '') ?>">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Agência</label>
                        <input type="text" name="banco_agencia" class="form-control" value="<?= e($empresa['banco_agencia'] ?? '') ?>">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Conta</label>
                        <input type="text" name="banco_conta" class="form-control" value="<?= e($empresa['banco_conta'] ?? '') ?>">
                    </div>
                    <div class="col-md-2 mb-3">
                        <label class="form-label">Tipo</label>
                        <select name="banco_tipo" class="form-select">
                            <option value="corrente" <?= ($empresa['banco_tipo'] ?? '') == 'corrente' ? 'selected' : '' ?>>Corrente</option>
                            <option value="poupanca" <?= ($empresa['banco_tipo'] ?? '') == 'poupanca' ? 'selected' : '' ?>>Poupança</option>
                        </select>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Chave PIX</label>
                        <input type="text" name="chave_pix" class="form-control" value="<?= e($empresa['chave_pix'] ?? '') ?>">
                        <small class="text-muted">E-mail, telefone, CPF/CNPJ ou chave aleatória</small>
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-center pt-3 border-top">
                    <a href="<?= url('dashboard') ?>" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left"></i> Voltar
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save"></i> Salvar Alterações
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Busca CEP via ViaCEP
document.getElementById('cep').addEventListener('blur', function() {
    const cep = this.value.replace(/\D/g, '');
    if (cep.length === 8) {
        fetch(`https://viacep.com.br/ws/${cep}/json/`)
            .then(response => response.json())
            .then(data => {
                if (!data.erro) {
                    document.getElementById('endereco').value = data.logradouro;
                    document.getElementById('bairro').value = data.bairro;
                    document.getElementById('cidade').value = data.localidade;
                    document.getElementById('estado').value = data.uf;
                }
            })
            .catch(console.error);
    }
});
</script>
