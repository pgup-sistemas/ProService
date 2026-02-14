<?php
/**
 * proService - Configurações: Aparência
 * Arquivo: /app/views/configuracoes/aparencia.php
 */
?>

<?= breadcrumb(['Dashboard' => 'dashboard', 'Configurações' => 'configuracoes', 'Aparência']) ?>

<div class="container-fluid">
    <div class="mb-4">
        <h2 class="mb-0"><i class="bi bi-gear text-primary"></i> Configurações</h2>
        <p class="text-muted">Gerencie as configurações do sistema</p>
    </div>

    <!-- Tabs -->
    <ul class="nav nav-tabs mb-4">
        <li class="nav-item">
            <a class="nav-link" href="<?= url('configuracoes/empresa') ?>">
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
            <a class="nav-link active" href="<?= url('configuracoes/contrato') ?>">
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
            <h5 class="mb-0"><i class="bi bi-palette"></i> Cores Personalizadas</h5>
        </div>
        <div class="card-body">
            <form method="POST" action="<?= url('configuracoes/aparencia') ?>">
                <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
                
                <div class="alert alert-info">
                    <i class="bi bi-info-circle"></i> 
                    Escolha as cores principais que serão aplicadas na interface do sistema.
                    <strong>Nota:</strong> As cores são salvas e ficam disponíveis para futuras implementações de temas personalizados.
                </div>

                <div class="row">
                            <!-- Cor Primária -->
                            <div class="col-md-4 mb-4">
                                <div class="card h-100 border-0 shadow-sm">
                                    <div class="card-body text-center">
                                        <h6 class="card-title">Cor Primária</h6>
                                        <p class="text-muted small">Botões principais, links, destaques</p>
                                        
                                        <div class="color-preview rounded mb-3" style="height: 80px; background-color: <?= e($cores['cor_primaria'] ?? '#1e40af') ?>"></div>
                                        
                                        <div class="input-group">
                                            <input type="color" name="cor_primaria" class="form-control form-control-color" 
                                                   value="<?= e($cores['cor_primaria'] ?? '#1e40af') ?>" 
                                                   id="corPrimaria" style="width: 50px;">
                                            <input type="text" class="form-control text-center font-monospace" 
                                                   value="<?= e($cores['cor_primaria'] ?? '#1e40af') ?>" 
                                                   id="textoPrimaria" maxlength="7">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Cor Sucesso -->
                            <div class="col-md-4 mb-4">
                                <div class="card h-100 border-0 shadow-sm">
                                    <div class="card-body text-center">
                                        <h6 class="card-title">Cor Sucesso</h6>
                                        <p class="text-muted small">OS finalizadas, confirmações, positivo</p>
                                        
                                        <div class="color-preview rounded mb-3" style="height: 80px; background-color: <?= e($cores['cor_sucesso'] ?? '#059669') ?>"></div>
                                        
                                        <div class="input-group">
                                            <input type="color" name="cor_sucesso" class="form-control form-control-color" 
                                                   value="<?= e($cores['cor_sucesso'] ?? '#059669') ?>" 
                                                   id="corSucesso" style="width: 50px;">
                                            <input type="text" class="form-control text-center font-monospace" 
                                                   value="<?= e($cores['cor_sucesso'] ?? '#059669') ?>" 
                                                   id="textoSucesso" maxlength="7">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Cor Alerta -->
                            <div class="col-md-4 mb-4">
                                <div class="card h-100 border-0 shadow-sm">
                                    <div class="card-body text-center">
                                        <h6 class="card-title">Cor Alerta</h6>
                                        <p class="text-muted small">Avisos, urgentes, trial expirando</p>
                                        
                                        <div class="color-preview rounded mb-3" style="height: 80px; background-color: <?= e($cores['cor_alerta'] ?? '#ea580c') ?>"></div>
                                        
                                        <div class="input-group">
                                            <input type="color" name="cor_alerta" class="form-control form-control-color" 
                                                   value="<?= e($cores['cor_alerta'] ?? '#ea580c') ?>" 
                                                   id="corAlerta" style="width: 50px;">
                                            <input type="text" class="form-control text-center font-monospace" 
                                                   value="<?= e($cores['cor_alerta'] ?? '#ea580c') ?>" 
                                                   id="textoAlerta" maxlength="7">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Preview das Cores -->
                        <div class="card mb-4 border-0 bg-light">
                            <div class="card-body">
                                <h6 class="mb-3"><i class="bi bi-eye"></i> Prévia das Cores</h6>
                                <div class="d-flex gap-2 flex-wrap">
                                    <button type="button" class="btn" id="previewPrimaria" style="background-color: <?= e($cores['cor_primaria'] ?? '#1e40af') ?>; color: white;">
                                        Botão Primário
                                    </button>
                                    <button type="button" class="btn" id="previewSucesso" style="background-color: <?= e($cores['cor_sucesso'] ?? '#059669') ?>; color: white;">
                                        <i class="bi bi-check-circle"></i> Sucesso
                                    </button>
                                    <span class="badge" id="previewAlerta" style="background-color: <?= e($cores['cor_alerta'] ?? '#ea580c') ?>; color: white; font-size: 1rem;">
                                        <i class="bi bi-exclamation-triangle"></i> Alerta
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="alert alert-warning">
                            <i class="bi bi-exclamation-triangle"></i> 
                            <strong>Importante:</strong> As cores personalizadas são salvas no banco de dados e 
                            serão aplicadas automaticamente quando o recurso de temas personalizados for ativado.
                        </div>

                        <div class="d-flex justify-content-between align-items-center pt-3 border-top">
                            <a href="<?= url('configuracoes/empresa') ?>" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-left"></i> Voltar
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save"></i> Salvar Cores
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Cores Pré-definidas -->
            <div class="card mt-4">
                <div class="card-header">
                    <h6 class="mb-0"><i class="bi bi-magic"></i> Palettes Sugeridas</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <div class="card cursor-pointer palette-preset" data-primaria="#1e40af" data-sucesso="#059669" data-alerta="#ea580c">
                                <div class="card-body p-2">
                                    <div class="d-flex gap-1 mb-2">
                                        <div style="width: 30px; height: 30px; background: #1e40af; border-radius: 4px;"></div>
                                        <div style="width: 30px; height: 30px; background: #059669; border-radius: 4px;"></div>
                                        <div style="width: 30px; height: 30px; background: #ea580c; border-radius: 4px;"></div>
                                    </div>
                                    <small class="text-muted">Padrão (Azul)</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="card cursor-pointer palette-preset" data-primaria="#7c3aed" data-sucesso="#10b981" data-alerta="#f59e0b">
                                <div class="card-body p-2">
                                    <div class="d-flex gap-1 mb-2">
                                        <div style="width: 30px; height: 30px; background: #7c3aed; border-radius: 4px;"></div>
                                        <div style="width: 30px; height: 30px; background: #10b981; border-radius: 4px;"></div>
                                        <div style="width: 30px; height: 30px; background: #f59e0b; border-radius: 4px;"></div>
                                    </div>
                                    <small class="text-muted">Violeta</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="card cursor-pointer palette-preset" data-primaria="#0891b2" data-sucesso="#22c55e" data-alerta="#ef4444">
                                <div class="card-body p-2">
                                    <div class="d-flex gap-1 mb-2">
                                        <div style="width: 30px; height: 30px; background: #0891b2; border-radius: 4px;"></div>
                                        <div style="width: 30px; height: 30px; background: #22c55e; border-radius: 4px;"></div>
                                        <div style="width: 30px; height: 30px; background: #ef4444; border-radius: 4px;"></div>
                                    </div>
                                    <small class="text-muted">Ciano</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="card cursor-pointer palette-preset" data-primaria="#be123c" data-sucesso="#16a34a" data-alerta="#ea580c">
                                <div class="card-body p-2">
                                    <div class="d-flex gap-1 mb-2">
                                        <div style="width: 30px; height: 30px; background: #be123c; border-radius: 4px;"></div>
                                        <div style="width: 30px; height: 30px; background: #16a34a; border-radius: 4px;"></div>
                                        <div style="width: 30px; height: 30px; background: #ea580c; border-radius: 4px;"></div>
                                    </div>
                                    <small class="text-muted">Rosa Escuro</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="card cursor-pointer palette-preset" data-primaria="#4338ca" data-sucesso="#84cc16" data-alerta="#f97316">
                                <div class="card-body p-2">
                                    <div class="d-flex gap-1 mb-2">
                                        <div style="width: 30px; height: 30px; background: #4338ca; border-radius: 4px;"></div>
                                        <div style="width: 30px; height: 30px; background: #84cc16; border-radius: 4px;"></div>
                                        <div style="width: 30px; height: 30px; background: #f97316; border-radius: 4px;"></div>
                                    </div>
                                    <small class="text-muted">Índigo</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="card cursor-pointer palette-preset" data-primaria="#0f766e" data-sucesso="#65a30d" data-alerta="#dc2626">
                                <div class="card-body p-2">
                                    <div class="d-flex gap-1 mb-2">
                                        <div style="width: 30px; height: 30px; background: #0f766e; border-radius: 4px;"></div>
                                        <div style="width: 30px; height: 30px; background: #65a30d; border-radius: 4px;"></div>
                                        <div style="width: 30px; height: 30px; background: #dc2626; border-radius: 4px;"></div>
                                    </div>
                                    <small class="text-muted">Teal</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.cursor-pointer { cursor: pointer; }
.cursor-pointer:hover { transform: scale(1.02); transition: transform 0.2s; }
</style>

<script>
// Atualiza previews em tempo real
function atualizarPreviews() {
    const primaria = document.getElementById('corPrimaria').value;
    const sucesso = document.getElementById('corSucesso').value;
    const alerta = document.getElementById('corAlerta').value;
    
    // Atualiza textos
    document.getElementById('textoPrimaria').value = primaria;
    document.getElementById('textoSucesso').value = sucesso;
    document.getElementById('textoAlerta').value = alerta;
    
    // Atualiza previews
    document.getElementById('previewPrimaria').style.backgroundColor = primaria;
    document.getElementById('previewSucesso').style.backgroundColor = sucesso;
    document.getElementById('previewAlerta').style.backgroundColor = alerta;
    
    // Atualiza divs de preview
    document.querySelectorAll('.color-preview')[0].style.backgroundColor = primaria;
    document.querySelectorAll('.color-preview')[1].style.backgroundColor = sucesso;
    document.querySelectorAll('.color-preview')[2].style.backgroundColor = alerta;
}

// Listeners para inputs color
document.getElementById('corPrimaria').addEventListener('input', atualizarPreviews);
document.getElementById('corSucesso').addEventListener('input', atualizarPreviews);
document.getElementById('corAlerta').addEventListener('input', atualizarPreviews);

// Atualiza cor quando digita no campo texto
document.getElementById('textoPrimaria').addEventListener('change', function() {
    if (/^#[0-9A-F]{6}$/i.test(this.value)) {
        document.getElementById('corPrimaria').value = this.value;
        atualizarPreviews();
    }
});
document.getElementById('textoSucesso').addEventListener('change', function() {
    if (/^#[0-9A-F]{6}$/i.test(this.value)) {
        document.getElementById('corSucesso').value = this.value;
        atualizarPreviews();
    }
});
document.getElementById('textoAlerta').addEventListener('change', function() {
    if (/^#[0-9A-F]{6}$/i.test(this.value)) {
        document.getElementById('corAlerta').value = this.value;
        atualizarPreviews();
    }
});

// Palettes pré-definidas
document.querySelectorAll('.palette-preset').forEach(card => {
    card.addEventListener('click', function() {
        document.getElementById('corPrimaria').value = this.dataset.primaria;
        document.getElementById('corSucesso').value = this.dataset.sucesso;
        document.getElementById('corAlerta').value = this.dataset.alerta;
        atualizarPreviews();
    });
});
</script>
