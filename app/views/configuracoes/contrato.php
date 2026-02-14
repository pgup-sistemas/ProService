<?php
/**
 * proService - Configurações: Template de Contrato
 * Arquivo: /app/views/configuracoes/contrato.php
 */
?>

<?= breadcrumb(['Dashboard' => 'dashboard', 'Configurações' => 'configuracoes', 'Template de Contrato']) ?>

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

    <form method="POST" action="<?= url('configuracoes/contrato') ?>">
        <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
        
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="bi bi-pencil-square"></i> Editor de Contrato</h5>
                <div>
                    <a href="<?= url('configuracoes/preview-contrato') ?>" class="btn btn-sm btn-outline-primary" target="_blank">
                        <i class="bi bi-eye"></i> Visualizar Preview
                    </a>
                    <button type="button" class="btn btn-sm btn-outline-secondary" id="resetarPadrao">
                        <i class="bi bi-arrow-counterclockwise"></i> Resetar Padrão
                    </button>
                </div>
            </div>
            <div class="card-body">
                        <!-- Merge Tags -->
                        <div class="alert alert-info">
                            <h6 class="alert-heading"><i class="bi bi-magic"></i> Variáveis disponíveis</h6>
                            <p class="mb-2">Clique para inserir no template:</p>
                            <div class="row">
                                <?php 
                                $metade = ceil(count($mergeTags) / 2);
                                $col1 = array_slice($mergeTags, 0, $metade);
                                $col2 = array_slice($mergeTags, $metade);
                                ?>
                                <div class="col-md-6">
                                    <?php foreach ($col1 as $tag): ?>
                                    <div class="d-flex align-items-center mb-1">
                                        <code class="merge-tag cursor-pointer me-2" data-tag="<?= e($tag['tag']) ?>">
                                            <?= e($tag['tag']) ?>
                                        </code>
                                        <small class="text-muted"><?= e($tag['descricao']) ?></small>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                                <div class="col-md-6">
                                    <?php foreach ($col2 as $tag): ?>
                                    <div class="d-flex align-items-center mb-1">
                                        <code class="merge-tag cursor-pointer me-2" data-tag="<?= e($tag['tag']) ?>">
                                            <?= e($tag['tag']) ?>
                                        </code>
                                        <small class="text-muted"><?= e($tag['descricao']) ?></small>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>

                        <!-- Toolbar do Editor -->
                        <div class="btn-toolbar mb-2" role="toolbar">
                            <div class="btn-group btn-group-sm me-2" role="group">
                                <button type="button" class="btn btn-outline-secondary editor-btn" data-tag="<h2>" data-close="</h2>">
                                    <i class="bi bi-type-h2"></i> Título
                                </button>
                                <button type="button" class="btn btn-outline-secondary editor-btn" data-tag="<h3>" data-close="</h3>">
                                    <i class="bi bi-type-h3"></i> Subtítulo
                                </button>
                                <button type="button" class="btn btn-outline-secondary editor-btn" data-tag="<p>" data-close="</p>">
                                    <i class="bi bi-paragraph"></i> Parágrafo
                                </button>
                            </div>
                            <div class="btn-group btn-group-sm me-2" role="group">
                                <button type="button" class="btn btn-outline-secondary editor-btn" data-tag="<strong>" data-close="</strong>">
                                    <i class="bi bi-type-bold"></i>
                                </button>
                                <button type="button" class="btn btn-outline-secondary editor-btn" data-tag="<em>" data-close="</em>">
                                    <i class="bi bi-type-italic"></i>
                                </button>
                                <button type="button" class="btn btn-outline-secondary editor-btn" data-tag="<u>" data-close="</u>">
                                    <i class="bi bi-type-underline"></i>
                                </button>
                            </div>
                            <div class="btn-group btn-group-sm" role="group">
                                <button type="button" class="btn btn-outline-secondary editor-btn" data-tag="<ul>\n  <li>" data-close="</li>\n</ul>">
                                    <i class="bi bi-list-ul"></i> Lista
                                </button>
                                <button type="button" class="btn btn-outline-secondary editor-btn" data-tag="<hr>">
                                    <i class="bi bi-hr"></i> Linha
                                </button>
                            </div>
                        </div>

                        <!-- Editor -->
                        <div class="mb-3">
                            <textarea name="template_contrato" id="templateContrato" class="form-control template-editor" rows="25" placeholder="Digite ou cole o template do contrato..."><?= e($template) ?></textarea>
                        </div>

                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <small class="text-muted">
                                    <i class="bi bi-info-circle"></i> 
                                    O template aceita HTML básico para formatação
                                </small>
                            </div>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save"></i> Salvar Template
                            </button>
                        </div>
                    </div>
                </div>
            </form>

            <!-- Instruções -->
            <div class="card mt-4">
                <div class="card-header">
                    <h6 class="mb-0"><i class="bi bi-question-circle"></i> Como usar</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6>1. Estrutura básica recomendada</h6>
                            <pre class="bg-light p-2 rounded"><code>&lt;h2&gt;CONTRATO DE SERVIÇOS&lt;/h2&gt;

&lt;p&gt;&lt;strong&gt;CONTRATANTE:&lt;/strong&gt; {{cliente_nome}}&lt;/p&gt;
&lt;p&gt;&lt;strong&gt;CONTRATADA:&lt;/strong&gt; {{empresa_nome}}&lt;/p&gt;

&lt;h3&gt;OBJETO&lt;/h3&gt;
&lt;p&gt;{{os_descricao}}&lt;/p&gt;

&lt;h3&gt;VALOR&lt;/h3&gt;
&lt;p&gt;R$ {{os_valor}}&lt;/p&gt;

&lt;h3&gt;ASSINATURAS&lt;/h3&gt;
&lt;p&gt;Data: {{data_atual}}&lt;/p&gt;</code></pre>
                        </div>
                        <div class="col-md-6">
                            <h6>2. Dicas importantes</h6>
                            <ul class="small">
                                <li>Use tags HTML para formatar (negrito, itálico, etc.)</li>
                                <li>As variáveis são substituídas automaticamente na geração do contrato</li>
                                <li>Sempre inclua uma seção de assinaturas</li>
                                <li>Mencione a garantia no contrato</li>
                                <li>Salve e teste o preview antes de usar</li>
                            </ul>
                            
                            <h6 class="mt-3">3. Cláusulas sugeridas</h6>
                            <ul class="small">
                                <li><strong>Garantia:</strong> Prazo e cobertura</li>
                                <li><strong>Pagamento:</strong> Formas e prazos aceitos</li>
                                <li><strong>Prazo de entrega:</strong> Estimativa de conclusão</li>
                                <li><strong>Responsabilidades:</strong> De ambas as partes</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Template Padrão (escondido) -->
<textarea id="templatePadrao" style="display: none;"><h2>CONTRATO DE PRESTAÇÃO DE SERVIÇOS</h2>

<p><strong>CONTRATANTE:</strong> {{cliente_nome}}, CPF/CNPJ: {{cliente_cpf_cnpj}}, residente em {{cliente_endereco}}, telefone: {{cliente_telefone}}.</p>

<p><strong>CONTRATADA:</strong> {{empresa_nome}}, CNPJ: {{empresa_cnpj}}, endereço: {{empresa_endereco}}, telefone: {{empresa_telefone}}.</p>

<h3>OBJETO DO CONTRATO</h3>
<p>Prestação do serviço de: <strong>{{os_servico}}</strong></p>
<p>Descrição: {{os_descricao}}</p>

<h3>VALOR E FORMA DE PAGAMENTO</h3>
<p>Valor total: R$ {{os_valor}}</p>

<h3>GARANTIA</h3>
<p>Garantia de {{os_garantia}} dias para o serviço prestado, conforme artigo 26 do CDC.</p>

<h3>DATA E ASSINATURAS</h3>
<p>Data: {{data_atual}}</p>

<table style="width: 100%; margin-top: 50px;">
<tr>
<td style="text-align: center;">_________________________________<br>Assinatura do Contratante<br>{{cliente_nome}}</td>
<td style="text-align: center;">_________________________________<br>Assinatura do Responsável<br>{{empresa_nome}}</td>
</tr>
</table></textarea>

<style>
.template-editor {
    font-family: 'Courier New', monospace;
    font-size: 0.9rem;
    line-height: 1.6;
}
.merge-tag {
    background: #e9ecef;
    padding: 2px 6px;
    border-radius: 3px;
    font-size: 0.85rem;
    transition: all 0.2s;
}
.merge-tag:hover {
    background: #0d6efd;
    color: white;
}
.cursor-pointer { cursor: pointer; }
pre code {
    font-size: 0.8rem;
}
</style>

<script>
const textarea = document.getElementById('templateContrato');

// Inserir merge tag
let lastCursorPosition = 0;
textarea.addEventListener('click', function() {
    lastCursorPosition = this.selectionStart;
});
textarea.addEventListener('keyup', function() {
    lastCursorPosition = this.selectionStart;
});

document.querySelectorAll('.merge-tag').forEach(tag => {
    tag.addEventListener('click', function() {
        const tagText = this.dataset.tag;
        insertAtCursor(textarea, tagText);
    });
});

// Toolbar buttons
document.querySelectorAll('.editor-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        const open = this.dataset.tag;
        const close = this.dataset.close || '';
        wrapSelection(textarea, open, close);
    });
});

function insertAtCursor(element, text) {
    const start = element.selectionStart;
    const end = element.selectionEnd;
    const value = element.value;
    
    element.value = value.substring(0, start) + text + value.substring(end);
    element.selectionStart = element.selectionEnd = start + text.length;
    element.focus();
}

function wrapSelection(element, open, close) {
    const start = element.selectionStart;
    const end = element.selectionEnd;
    const selectedText = element.value.substring(start, end);
    const replacement = open + selectedText + close;
    
    element.value = element.value.substring(0, start) + replacement + element.value.substring(end);
    element.selectionStart = start + open.length;
    element.selectionEnd = start + open.length + selectedText.length;
    element.focus();
}

// Resetar para padrão
document.getElementById('resetarPadrao').addEventListener('click', function() {
    if (confirm('Deseja restaurar o template padrão? Isso substituirá o conteúdo atual.')) {
        textarea.value = document.getElementById('templatePadrao').value;
    }
});
</script>
