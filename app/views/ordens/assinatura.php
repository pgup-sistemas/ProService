<div class="d-flex align-items-center mb-4">
    <a href="<?= url('ordens/show/' . $os['id']) ?>" class="btn btn-outline-secondary btn-sm me-3">
        <i class="bi bi-arrow-left"></i>
    </a>
    <div>
        <h4 class="mb-0"><i class="bi bi-pen"></i> Assinatura Digital</h4>
        <p class="text-muted mb-0">OS #<?= str_pad($os['numero_os'], 4, '0', STR_PAD_LEFT) ?> - <?= e($os['cliente_nome']) ?></p>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-md-8 col-lg-6">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0"><i class="bi bi-pen-fill"></i> Toque ou clique para assinar</h6>
            </div>
            <div class="card-body text-center">
                <!-- Área de assinatura -->
                <div class="position-relative d-inline-block border rounded" style="background: #f8f9fa;">
                    <canvas id="signature-pad" class="rounded" width="500" height="250" style="touch-action: none; cursor: crosshair;"></canvas>
                    
                    <!-- Overlay quando vazio -->
                    <div id="assinatura-placeholder" class="position-absolute top-50 start-50 translate-middle text-muted pointer-events-none">
                        <i class="bi bi-pen fs-1 d-block mb-2"></i>
                        <small>Assinatura do Cliente</small>
                    </div>
                </div>
                
                <!-- Instruções -->
                <div class="mt-3 text-muted small">
                    <p class="mb-1"><i class="bi bi-info-circle"></i> Use o dedo (mobile) ou mouse para assinar</p>
                    <p class="mb-0">A assinatura tem validade legal conforme Lei 14.063/2020</p>
                </div>
                
                <!-- Seleção do tipo de assinatura -->
                <div class="alert alert-info mt-3">
                    <label class="form-label fw-bold mb-2">
                        <i class="bi bi-question-circle"></i> Tipo de Assinatura:
                    </label>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="tipo_assinatura" id="tipo_autorizacao" value="autorizacao" required>
                        <label class="form-check-label" for="tipo_autorizacao">
                            <strong>Autorização</strong> - Aprovo o orçamento e autorizo a execução do serviço
                        </label>
                    </div>
                    <div class="form-check mt-2">
                        <input class="form-check-input" type="radio" name="tipo_assinatura" id="tipo_conformidade" value="conformidade" checked>
                        <label class="form-check-label" for="tipo_conformidade">
                            <strong>Conformidade</strong> - Confirmo que recebi o serviço executado conforme contratado
                        </label>
                    </div>
                </div>
                
                <!-- Formulário -->
                <form method="POST" action="<?= url('ordens/assinatura/' . $os['id']) ?>" id="form-assinatura" class="mt-3">
                    <?= csrfField() ?>
                    <input type="hidden" name="assinatura" id="assinatura-input">
                    <input type="hidden" name="tipo" id="tipo-input" value="conformidade">
                    
                    <!-- Nome do assinante -->
                    <div class="mb-3 text-start">
                        <label class="form-label fw-bold">Nome completo do assinante:</label>
                        <input type="text" name="assinante_nome" class="form-control" value="<?= e($os['cliente_nome']) ?>" required>
                    </div>
                    
                    <div class="d-flex gap-2 justify-content-center">
                        <button type="button" class="btn btn-outline-secondary" id="btn-limpar">
                            <i class="bi bi-eraser"></i> Limpar
                        </button>
                        <button type="submit" class="btn btn-primary" id="btn-salvar" disabled>
                            <i class="bi bi-check-lg"></i> Confirmar Assinatura
                        </button>
                    </div>
                </form>
                
                <!-- Preview da assinatura (opcional) -->
                <div id="preview-container" class="mt-3 d-none">
                    <p class="small text-muted mb-2">Preview:</p>
                    <img id="assinatura-preview" class="border rounded" style="max-width: 100%; max-height: 150px;">
                </div>
            </div>
        </div>
        
        <!-- Informações da OS -->
        <div class="card mt-3">
            <div class="card-header">
                <h6 class="mb-0"><i class="bi bi-clipboard"></i> Detalhes da OS</h6>
            </div>
            <div class="card-body">
                <table class="table table-sm table-borderless mb-0">
                    <tr>
                        <td class="text-muted">Cliente:</td>
                        <td class="fw-medium"><?= e($os['cliente_nome']) ?></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Serviço:</td>
                        <td><?= e($os['servico_nome'] ?? '-') ?></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Valor Total:</td>
                        <td class="fw-bold"><?= formatMoney($os['valor_total']) ?></td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
// Canvas para assinatura
const canvas = document.getElementById('signature-pad');
const ctx = canvas.getContext('2d');
const btnLimpar = document.getElementById('btn-limpar');
const btnSalvar = document.getElementById('btn-salvar');
const formAssinatura = document.getElementById('form-assinatura');
const assinaturaInput = document.getElementById('assinatura-input');
const assinaturaPreview = document.getElementById('assinatura-preview');
const previewContainer = document.getElementById('preview-container');
const placeholder = document.getElementById('assinatura-placeholder');

let isDrawing = false;
let hasSignature = false;

// Configurações do canvas
ctx.strokeStyle = '#000';
ctx.lineWidth = 2;
ctx.lineCap = 'round';
ctx.lineJoin = 'round';

// Ajustar canvas para retina
function resizeCanvas() {
    const rect = canvas.getBoundingClientRect();
    const dpr = window.devicePixelRatio || 1;
    
    canvas.width = rect.width * dpr;
    canvas.height = rect.height * dpr;
    
    ctx.scale(dpr, dpr);
    ctx.strokeStyle = '#000';
    ctx.lineWidth = 2;
    ctx.lineCap = 'round';
    ctx.lineJoin = 'round';
}

// Inicializar
document.addEventListener('DOMContentLoaded', function() {
    resizeCanvas();
    window.addEventListener('resize', resizeCanvas);
});

// Funções de desenho
function getPos(e) {
    const rect = canvas.getBoundingClientRect();
    const clientX = e.clientX || (e.touches && e.touches[0] ? e.touches[0].clientX : 0);
    const clientY = e.clientY || (e.touches && e.touches[0] ? e.touches[0].clientY : 0);
    
    return {
        x: clientX - rect.left,
        y: clientY - rect.top
    };
}

function startDrawing(e) {
    e.preventDefault();
    isDrawing = true;
    const pos = getPos(e);
    ctx.beginPath();
    ctx.moveTo(pos.x, pos.y);
}

function draw(e) {
    if (!isDrawing) return;
    e.preventDefault();
    
    const pos = getPos(e);
    ctx.lineTo(pos.x, pos.y);
    ctx.stroke();
    
    hasSignature = true;
    placeholder.classList.add('d-none');
    btnSalvar.disabled = false;
}

function stopDrawing(e) {
    if (!isDrawing) return;
    e.preventDefault();
    isDrawing = false;
    ctx.closePath();
}

// Eventos do mouse
canvas.addEventListener('mousedown', startDrawing);
canvas.addEventListener('mousemove', draw);
canvas.addEventListener('mouseup', stopDrawing);
canvas.addEventListener('mouseout', stopDrawing);

// Eventos touch (mobile)
canvas.addEventListener('touchstart', startDrawing, { passive: false });
canvas.addEventListener('touchmove', draw, { passive: false });
canvas.addEventListener('touchend', stopDrawing);

// Sincronizar tipo de assinatura
const tipoRadios = document.querySelectorAll('input[name="tipo_assinatura"]');
const tipoInput = document.getElementById('tipo-input');

tipoRadios.forEach(radio => {
    radio.addEventListener('change', function() {
        tipoInput.value = this.value;
    });
});

// Limpar assinatura
btnLimpar.addEventListener('click', function() {
    ctx.clearRect(0, 0, canvas.width, canvas.height);
    hasSignature = false;
    placeholder.classList.remove('d-none');
    btnSalvar.disabled = true;
    previewContainer.classList.add('d-none');
    assinaturaInput.value = '';
});

// Salvar assinatura
formAssinatura.addEventListener('submit', function(e) {
    if (!hasSignature) {
        e.preventDefault();
        alert('Por favor, faça sua assinatura primeiro.');
        return;
    }
    
    // Converter para base64
    const dataURL = canvas.toDataURL('image/png');
    assinaturaInput.value = dataURL;
    
    // Mostrar preview
    assinaturaPreview.src = dataURL;
    previewContainer.classList.remove('d-none');
});
</script>

<style>
/* Prevenir seleção durante a assinatura */
#signature-pad {
    -webkit-touch-callout: none;
    -webkit-user-select: none;
    -khtml-user-select: none;
    -moz-user-select: none;
    -ms-user-select: none;
    user-select: none;
}

.pointer-events-none {
    pointer-events: none;
}
</style>
