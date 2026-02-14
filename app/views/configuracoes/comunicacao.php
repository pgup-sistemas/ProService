<?php
/**
 * proService - Configurações: Comunicação
 * Arquivo: /app/views/configuracoes/comunicacao.php
 */
?>

<?= breadcrumb(['Dashboard' => 'dashboard', 'Configurações' => 'configuracoes', 'Comunicação']) ?>

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
            <a class="nav-link active" href="<?= url('configuracoes/comunicacao') ?>">
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

    <!-- Templates WhatsApp -->
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="bi bi-whatsapp text-success"></i> Templates de WhatsApp</h5>
            <span class="badge bg-success">Ativo</span>
        </div>
        <div class="card-body">
            <form method="POST" action="<?= url('configuracoes/comunicacao') ?>">
                <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
                <input type="hidden" name="action" value="whatsapp">
                
                <!-- Merge Tags Disponíveis -->
                <div class="alert alert-info">
                    <h6 class="alert-heading"><i class="bi bi-magic"></i> Variáveis disponíveis (clique para copiar)</h6>
                            <div class="d-flex flex-wrap gap-2 mt-2">
                                <?php foreach ($mergeTags['whatsapp'] ?? [] as $tag): ?>
                                <code class="cursor-pointer merge-tag" data-tag="<?= e($tag['tag']) ?>" title="<?= e($tag['descricao']) ?>">
                                    <?= e($tag['tag']) ?>
                                </code>
                                <?php endforeach; ?>
                            </div>
                            <small class="d-block mt-2">Clique em qualquer variável para copiá-la. Cole no template onde desejar.</small>
                        </div>

                        <!-- Template: OS Criada -->
                        <div class="mb-4">
                            <label class="form-label fw-bold">
                                <i class="bi bi-file-plus text-primary"></i> 
                                Mensagem - OS Criada
                                <small class="text-muted fw-normal d-block">Enviada quando uma nova OS é registrada</small>
                            </label>
                            <textarea name="template_os_criada" class="form-control template-textarea" rows="6" placeholder="Digite o template..."><?= e($config['mensagem_whatsapp_os_criada'] ?? '') ?></textarea>
                            <div class="d-flex justify-content-between mt-1">
                                <small class="text-muted"><span class="char-count">0</span> caracteres</small>
                                <button type="button" class="btn btn-sm btn-outline-primary preview-btn" data-template="os_criada">
                                    <i class="bi bi-eye"></i> Preview
                                </button>
                            </div>
                        </div>

                        <!-- Template: OS Finalizada -->
                        <div class="mb-4">
                            <label class="form-label fw-bold">
                                <i class="bi bi-check-circle text-success"></i> 
                                Mensagem - OS Finalizada
                                <small class="text-muted fw-normal d-block">Enviada quando a OS muda para status finalizado</small>
                            </label>
                            <textarea name="template_os_finalizada" class="form-control template-textarea" rows="6" placeholder="Digite o template..."><?= e($config['mensagem_whatsapp_os_finalizada'] ?? '') ?></textarea>
                            <div class="d-flex justify-content-between mt-1">
                                <small class="text-muted"><span class="char-count">0</span> caracteres</small>
                                <button type="button" class="btn btn-sm btn-outline-primary preview-btn" data-template="os_finalizada">
                                    <i class="bi bi-eye"></i> Preview
                                </button>
                            </div>
                        </div>

                        <!-- Template: Recibo -->
                        <div class="mb-4">
                            <label class="form-label fw-bold">
                                <i class="bi bi-receipt text-info"></i> 
                                Mensagem - Recibo/Pagamento
                                <small class="text-muted fw-normal d-block">Enviada quando o pagamento é confirmado</small>
                            </label>
                            <textarea name="template_recibo" class="form-control template-textarea" rows="6" placeholder="Digite o template..."><?= e($config['mensagem_whatsapp_recibo'] ?? '') ?></textarea>
                            <div class="d-flex justify-content-between mt-1">
                                <small class="text-muted"><span class="char-count">0</span> caracteres</small>
                                <button type="button" class="btn btn-sm btn-outline-primary preview-btn" data-template="recibo">
                                    <i class="bi bi-eye"></i> Preview
                                </button>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end pt-3 border-top">
                            <button type="submit" class="btn btn-success">
                                <i class="bi bi-save"></i> Salvar Templates WhatsApp
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Configurações SMTP -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-envelope text-primary"></i> Configurações de E-mail (SMTP)</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="<?= url('configuracoes/comunicacao') ?>">
                        <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
                        <input type="hidden" name="action" value="smtp">
                        
                        <div class="alert alert-warning">
                            <i class="bi bi-exclamation-triangle"></i>
                            <strong>Atenção:</strong> Configure seu servidor SMTP para envio de e-mails automáticos 
                            (recuperação de senha, notificações, etc.)
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Servidor SMTP (Host)</label>
                                <input type="text" name="smtp_host" class="form-control" placeholder="smtp.gmail.com" value="<?= e($empresa['smtp_host'] ?? '') ?>">
                                <small class="text-muted">Ex: smtp.gmail.com, smtp.office365.com, mail.seudominio.com</small>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Porta</label>
                                <input type="number" name="smtp_port" class="form-control" placeholder="587" value="<?= e($empresa['smtp_port'] ?? '') ?>">
                                <small class="text-muted">Padrão: 587 (TLS) ou 465 (SSL)</small>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Criptografia</label>
                                <select name="smtp_encryption" class="form-select">
                                    <option value="tls" <?= ($empresa['smtp_encryption'] ?? 'tls') == 'tls' ? 'selected' : '' ?>>TLS</option>
                                    <option value="ssl" <?= ($empresa['smtp_encryption'] ?? '') == 'ssl' ? 'selected' : '' ?>>SSL</option>
                                    <option value="none" <?= ($empresa['smtp_encryption'] ?? '') == 'none' ? 'selected' : '' ?>>Nenhuma</option>
                                </select>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Usuário/E-mail</label>
                                <input type="text" name="smtp_user" class="form-control" placeholder="seu-email@gmail.com" value="<?= e($empresa['smtp_user'] ?? '') ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Senha</label>
                                <div class="input-group">
                                    <input type="password" name="smtp_pass" class="form-control" placeholder="Sua senha ou app password" value="<?= e($empresa['smtp_pass'] ?? '') ?>">
                                    <button type="button" class="btn btn-outline-secondary toggle-password" tabindex="-1">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                </div>
                                <small class="text-muted">Para Gmail/Outlook, use "App Password" gerado nas configurações de segurança</small>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between align-items-center pt-3 border-top">
                            <button type="button" class="btn btn-outline-info" id="testarEmail">
                                <i class="bi bi-send"></i> Enviar E-mail de Teste
                            </button>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save"></i> Salvar Configurações SMTP
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Preview -->
<div class="modal fade" id="previewModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-eye"></i> Preview da Mensagem</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="bg-light p-3 rounded">
                    <p class="mb-0" id="previewContent" style="white-space: pre-wrap;"></p>
                </div>
                <div class="mt-3">
                    <small class="text-muted">Dados de exemplo usados no preview:</small>
                    <ul class="small text-muted mb-0">
                        <li>Cliente: João Silva</li>
                        <li>OS: #1234</li>
                        <li>Serviço: Formatação de PC</li>
                        <li>Valor: R$ 250,00</li>
                    </ul>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                <a href="#" class="btn btn-success" id="linkWhatsApp" target="_blank">
                    <i class="bi bi-whatsapp"></i> Abrir no WhatsApp
                </a>
            </div>
        </div>
    </div>
</div>

<style>
.merge-tag {
    background: #e9ecef;
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 0.85rem;
    cursor: pointer;
    transition: all 0.2s;
}
.merge-tag:hover {
    background: #0d6efd;
    color: white;
}
.template-textarea {
    font-family: 'Courier New', monospace;
    font-size: 0.9rem;
}
.cursor-pointer { cursor: pointer; }
</style>

<script>
// Contador de caracteres
document.querySelectorAll('.template-textarea').forEach(textarea => {
    const updateCount = () => {
        const counter = textarea.parentElement.querySelector('.char-count');
        if (counter) counter.textContent = textarea.value.length;
    };
    textarea.addEventListener('input', updateCount);
    updateCount();
});

// Copiar merge tag
let lastFocusedTextarea = null;
document.querySelectorAll('.template-textarea').forEach(t => {
    t.addEventListener('focus', function() { lastFocusedTextarea = this; });
});

document.querySelectorAll('.merge-tag').forEach(tag => {
    tag.addEventListener('click', function() {
        const tagText = this.dataset.tag;
        
        if (lastFocusedTextarea) {
            const start = lastFocusedTextarea.selectionStart;
            const end = lastFocusedTextarea.selectionEnd;
            const text = lastFocusedTextarea.value;
            
            lastFocusedTextarea.value = text.substring(0, start) + tagText + text.substring(end);
            lastFocusedTextarea.selectionStart = lastFocusedTextarea.selectionEnd = start + tagText.length;
            lastFocusedTextarea.focus();
        } else {
            navigator.clipboard.writeText(tagText);
            // Toast notification would go here
        }
    });
});

// Preview de templates
const dadosExemplo = {
    '{{cliente_nome}}': 'João Silva',
    '{{os_numero}}': '1234',
    '{{os_link}}': 'https://proservice.com/os/abc123',
    '{{empresa_nome}}': 'Tech Assistência',
    '{{empresa_telefone}}': '(11) 99999-9999',
    '{{valor_total}}': '250,00',
    '{{servico_nome}}': 'Formatação de PC'
};

document.querySelectorAll('.preview-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        const templateType = this.dataset.template;
        const textarea = this.closest('.mb-4').querySelector('.template-textarea');
        let content = textarea.value;
        
        // Substitui tags
        for (const [tag, valor] of Object.entries(dadosExemplo)) {
            content = content.replace(new RegExp(tag.replace(/[{}]/g, '\\$&'), 'g'), valor);
        }
        
        document.getElementById('previewContent').textContent = content;
        
        // Gera link WhatsApp
        const telefone = '5511999999999';
        const link = `https://wa.me/${telefone}?text=${encodeURIComponent(content)}`;
        document.getElementById('linkWhatsApp').href = link;
        
        new bootstrap.Modal(document.getElementById('previewModal')).show();
    });
});

// Toggle password
document.querySelector('.toggle-password').addEventListener('click', function() {
    const input = this.previousElementSibling;
    const icon = this.querySelector('i');
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.replace('bi-eye', 'bi-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.replace('bi-eye-slash', 'bi-eye');
    }
});

// Testar e-mail
document.getElementById('testarEmail')?.addEventListener('click', function() {
    const email = prompt('Digite o e-mail para receber o teste:');
    if (!email) return;

    const formData = new FormData();
    formData.append('csrf_token', '<?= generateCsrfToken() ?>');
    formData.append('email_teste', email);

    fetch('<?= url('configuracoes/testar-email') ?>', {
        method: 'POST',
        body: formData
    })
    .then(async (res) => {
        const data = await res.json().catch(() => ({}));
        if (res.ok && data.success) {
            alert(data.message || 'E-mail de teste enviado!');
            return;
        }
        alert((data && data.message) ? data.message : 'Erro ao enviar e-mail de teste.');
    })
    .catch(() => {
        alert('Erro ao enviar e-mail de teste.');
    });
});
</script>
