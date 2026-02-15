<?php
/**
 * proService - Configurações da Empresa
 * Arquivo: /app/views/configuracoes/index.php
 */
?>

<?= breadcrumb(['Dashboard' => 'dashboard', 'Configurações']) ?>

<div class="container-fluid">
    <div class="mb-4">
        <h2 class="mb-0"><i class="bi bi-gear text-primary"></i> Configurações</h2>
        <p class="text-muted">Personalize sua empresa e preferências do sistema</p>
    </div>

    <div class="row">
        <!-- Menu Lateral -->
        <div class="col-md-3 mb-4">
            <div class="list-group">
                <a href="<?= url('configuracoes?tab=empresa') ?>" class="list-group-item list-group-item-action <?= $activeTab == 'empresa' ? 'active' : '' ?>">
                    <i class="bi bi-building"></i> Dados da Empresa
                </a>
                <a href="<?= url('configuracoes?tab=documentos') ?>" class="list-group-item list-group-item-action <?= $activeTab == 'documentos' ? 'active' : '' ?>">
                    <i class="bi bi-file-text"></i> Modelos de Documentos
                </a>
                <a href="<?= url('configuracoes?tab=comunicacao') ?>" class="list-group-item list-group-item-action <?= $activeTab == 'comunicacao' ? 'active' : '' ?>">
                    <i class="bi bi-chat-dots"></i> Comunicação
                </a>
                <a href="<?= url('configuracoes?tab=preferencias') ?>" class="list-group-item list-group-item-action <?= $activeTab == 'preferencias' ? 'active' : '' ?>">
                    <i class="bi bi-palette"></i> Preferências
                </a>
                <a href="<?= url('configuracoes/plano') ?>" class="list-group-item list-group-item-action">
                    <i class="bi bi-credit-card"></i> Meu Plano
                    <?php if ($empresa['plano'] === 'trial'): ?>
                    <span class="badge bg-warning text-dark float-end">Trial</span>
                    <?php endif; ?>
                </a>
            </div>
        </div>

        <!-- Conteúdo -->
        <div class="col-md-9">
            <?php if ($activeTab == 'empresa'): ?>
            <!-- Dados da Empresa -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-building"></i> Dados da Empresa</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="<?= url('configuracoes/salvar-empresa') ?>" enctype="multipart/form-data">
                        <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
                        
                        <!-- Logo -->
                        <div class="mb-4 text-center">
                            <?php if (!empty($empresa['logo'])): ?>
                            <div class="mb-3">
                                <img src="<?= uploadUrl($empresa['logo']) ?>" alt="Logo" class="img-thumbnail" style="max-height: 100px;">
                            </div>
                            <?php endif; ?>
                            <label class="form-label">Logo da Empresa</label>
                            <input type="file" name="logo" class="form-control" accept="image/*">
                            <small class="text-muted">Recomendado: 300x100px, fundo transparente (PNG)</small>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Nome Fantasia *</label>
                                <input type="text" name="nome_fantasia" class="form-control" value="<?= e($empresa['nome_fantasia']) ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Razão Social</label>
                                <input type="text" name="razao_social" class="form-control" value="<?= e($empresa['razao_social'] ?? '') ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">CNPJ/CPF *</label>
                                <input type="text" name="cnpj_cpf" class="form-control cnpj-cpf" value="<?= e($empresa['cnpj_cpf']) ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">E-mail *</label>
                                <input type="email" name="email" class="form-control" value="<?= e($empresa['email']) ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Telefone</label>
                                <input type="tel" name="telefone" class="form-control telefone" value="<?= e($empresa['telefone'] ?? '') ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">WhatsApp Comercial</label>
                                <input type="tel" name="whatsapp" class="form-control telefone" value="<?= e($empresa['whatsapp'] ?? '') ?>">
                            </div>
                        </div>

                        <hr class="my-4">
                        <h6 class="text-muted mb-3"><i class="bi bi-geo-alt"></i> Endereço</h6>
                        
                        <div class="row">
                            <div class="col-md-3 mb-3">
                                <label class="form-label">CEP</label>
                                <input type="text" name="cep" class="form-control cep" value="<?= e($empresa['cep'] ?? '') ?>" maxlength="9">
                            </div>
                            <div class="col-md-7 mb-3">
                                <label class="form-label">Endereço</label>
                                <input type="text" name="endereco" class="form-control" value="<?= e($empresa['endereco'] ?? '') ?>">
                            </div>
                            <div class="col-md-2 mb-3">
                                <label class="form-label">Nº</label>
                                <input type="text" name="numero" class="form-control" value="<?= e($empresa['numero'] ?? '') ?>">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Complemento</label>
                                <input type="text" name="complemento" class="form-control" value="<?= e($empresa['complemento'] ?? '') ?>">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Bairro</label>
                                <input type="text" name="bairro" class="form-control" value="<?= e($empresa['bairro'] ?? '') ?>">
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Cidade</label>
                                <input type="text" name="cidade" class="form-control" value="<?= e($empresa['cidade'] ?? '') ?>">
                            </div>
                            <div class="col-md-1 mb-3">
                                <label class="form-label">UF</label>
                                <input type="text" name="estado" class="form-control" value="<?= e($empresa['estado'] ?? '') ?>" maxlength="2">
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
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Chave PIX</label>
                                <input type="text" name="chave_pix" class="form-control" value="<?= e($empresa['chave_pix'] ?? '') ?>">
                                <small class="text-muted">E-mail, telefone, CPF ou chave aleatória</small>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save"></i> Salvar Dados
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <?php elseif ($activeTab == 'documentos'): ?>
            <!-- Modelos de Documentos -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-file-text"></i> Modelo de Contrato</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="<?= url('configuracoes/salvar-documentos') ?>">
                        <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
                        
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle"></i> <strong>Variáveis disponíveis:</strong><br>
                            <code>{{cliente_nome}}</code>, <code>{{cliente_cpf}}</code>, 
                            <code>{{empresa_nome}}</code>, <code>{{empresa_cnpj}}</code>,
                            <code>{{servico}}</code>, <code>{{valor}}</code>, <code>{{numero_os}}</code>,
                            <code>{{garantia}}</code>, <code>{{data}}</code>, <code>{{cidade}}</code>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Template do Contrato</label>
                            <textarea name="template_contrato" class="form-control" rows="20"><?= e($empresa['template_contrato'] ?? $configuracoes['template_contrato'] ?? '') ?></textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Termos de Garantia</label>
                            <textarea name="termos_contrato" class="form-control" rows="5"><?= e($empresa['termos_contrato'] ?? '') ?></textarea>
                            <small class="text-muted">Texto exibido na seção de garantia dos documentos</small>
                        </div>

                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save"></i> Salvar Modelos
                        </button>
                    </form>
                </div>
            </div>

            <?php elseif ($activeTab == 'comunicacao'): ?>
            <!-- Comunicação -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-chat-dots"></i> Configurações de Comunicação</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="<?= url('configuracoes/salvar-comunicacao') ?>">
                        <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
                        
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle"></i> <strong>Variáveis disponíveis:</strong><br>
                            <code>{{cliente_nome}}</code>, <code>{{numero_os}}</code>, 
                            <code>{{servico}}</code>, <code>{{valor}}</code>, <code>{{link}}</code>
                        </div>

                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="enviar_notificacoes_auto" id="notifAuto" <?= ($configuracoes['enviar_notificacoes_auto'] ?? 0) ? 'checked' : '' ?>>
                                <label class="form-check-label" for="notifAuto">
                                    Enviar notificações automaticamente
                                </label>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Mensagem - OS Criada</label>
                            <textarea name="mensagem_whatsapp_os_criada" class="form-control" rows="4"><?= e($configuracoes['mensagem_whatsapp_os_criada'] ?? "Olá {{cliente_nome}}! 👋\n\nSua Ordem de Serviço #{{numero_os}} foi criada com sucesso!\n\n📋 Serviço: {{servico}}\n💰 Valor: {{valor}}\n\nAcompanhe em: {{link}}") ?></textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Mensagem - OS Finalizada</label>
                            <textarea name="mensagem_whatsapp_os_finalizada" class="form-control" rows="4"><?= e($configuracoes['mensagem_whatsapp_os_finalizada'] ?? "Olá {{cliente_nome}}! ✅\n\nSua Ordem de Serviço #{{numero_os}} foi finalizada!\n\n📋 Serviço: {{servico}}\n💰 Valor: {{valor}}\n\nAguardamos seu pagamento. Obrigado!") ?></textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Mensagem - Recibo</label>
                            <textarea name="mensagem_whatsapp_recibo" class="form-control" rows="4"><?= e($configuracoes['mensagem_whatsapp_recibo'] ?? "Olá {{cliente_nome}}! 🧾\n\nPagamento confirmado!\n\n📋 OS #{{numero_os}}\n💰 Valor: {{valor}}\n\nObrigado pela preferência!") ?></textarea>
                        </div>

                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save"></i> Salvar Configurações
                        </button>
                    </form>
                </div>
            </div>

            <?php elseif ($activeTab == 'preferencias'): ?>
            <!-- Preferências -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-palette"></i> Preferências do Sistema</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="<?= url('configuracoes/salvar-preferencias') ?>">
                        <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
                        
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Cor Primária</label>
                                <div class="input-group">
                                    <input type="color" name="cor_primaria" class="form-control form-control-color" value="<?= e($configuracoes['cor_primaria'] ?? '#1e40af') ?>">
                                    <input type="text" class="form-control" value="<?= e($configuracoes['cor_primaria'] ?? '#1e40af') ?>" readonly>
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Cor Sucesso</label>
                                <div class="input-group">
                                    <input type="color" name="cor_sucesso" class="form-control form-control-color" value="<?= e($configuracoes['cor_sucesso'] ?? '#059669') ?>">
                                    <input type="text" class="form-control" value="<?= e($configuracoes['cor_sucesso'] ?? '#059669') ?>" readonly>
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Cor Alerta</label>
                                <div class="input-group">
                                    <input type="color" name="cor_alerta" class="form-control form-control-color" value="<?= e($configuracoes['cor_alerta'] ?? '#ea580c') ?>">
                                    <input type="text" class="form-control" value="<?= e($configuracoes['cor_alerta'] ?? '#ea580c') ?>" readonly>
                                </div>
                            </div>
                        </div>

                        <div class="alert alert-warning">
                            <i class="bi bi-exclamation-triangle"></i> 
                            As cores personalizadas serão aplicadas em futuras atualizações do tema.
                        </div>

                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save"></i> Salvar Preferências
                        </button>
                    </form>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
// Atualizar campo de texto ao mudar cor
document.querySelectorAll('input[type="color"]').forEach(input => {
    input.addEventListener('input', function() {
        this.nextElementSibling.value = this.value;
    });
});
</script>
