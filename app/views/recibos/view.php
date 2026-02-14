<?php
/**
 * View para Recibo de Pagamento (com tipos de assinatura)
 */

// Buscar assinatura de conformidade (para recibo)
$assinaturaModel = new \App\Models\Assinatura();
$assinaturaConformidade = $assinaturaModel->getUltima($recibo['os_id'], 'conformidade');
$assinaturaAutorizacao = $assinaturaModel->getUltima($recibo['os_id'], 'autorizacao');
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recibo de Pagamento #<?= str_pad($recibo['numero_recibo'], 6, '0', STR_PAD_LEFT) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        @media print {
            .no-print { display: none !important; }
            body { background: white; }
            .recibo-container { box-shadow: none !important; border: 1px solid #ddd !important; }
        }
        
        .recibo-container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 40px;
            border: 2px solid #333;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        
        .recibo-header {
            text-align: center;
            border-bottom: 3px double #333;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        
        .recibo-titulo {
            font-size: 2rem;
            font-weight: bold;
            letter-spacing: 5px;
            margin-bottom: 10px;
        }
        
        .recibo-numero {
            font-size: 1.2rem;
            color: #666;
        }
        
        .recibo-corpo {
            font-size: 1.1rem;
            line-height: 1.8;
            text-align: justify;
        }
        
        .valor-extenso {
            font-style: italic;
            text-transform: uppercase;
            font-weight: 500;
        }
        
        .assinatura-area {
            margin-top: 60px;
            text-align: center;
        }
        
        .assinatura-linha {
            border-top: 1px solid #333;
            width: 300px;
            margin: 0 auto;
            padding-top: 10px;
        }
        
        .carimbo {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-15deg);
            border: 4px solid #059669;
            color: #059669;
            padding: 15px 40px;
            font-size: 1.5rem;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 3px;
            opacity: 0.7;
            z-index: 0;
        }
        
        .recibo-content {
            position: relative;
            z-index: 1;
        }
    </style>
</head>
<body class="bg-light">
    <div class="container py-4">
        <!-- Botões de ação -->
        <div class="no-print mb-4 d-flex gap-2 justify-content-center">
            <a href="<?= url('ordens/show/' . $recibo['os_id']) ?>" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Voltar para OS
            </a>
            <button onclick="window.print()" class="btn btn-primary">
                <i class="bi bi-printer"></i> Imprimir Recibo
            </button>
            <a href="https://wa.me/?text=<?= urlencode('Segue o recibo de pagamento: ' . url('recibo/' . $recibo['id'])) ?>" target="_blank" class="btn btn-success">
                <i class="bi bi-whatsapp"></i> Enviar WhatsApp
            </a>
        </div>

        <!-- Recibo -->
        <div class="recibo-container">
            <div class="carimbo">PAGO</div>
            
            <div class="recibo-content">
                <!-- Cabeçalho -->
                <div class="recibo-header">
                    <div class="recibo-titulo">RECIBO</div>
                    <div class="recibo-numero">Nº <?= str_pad($recibo['numero_recibo'] ?? $recibo['id'], 6, '0', STR_PAD_LEFT) ?></div>
                    <div class="mt-3">
                        <strong><?= e($recibo['empresa_nome'] ?? 'Empresa') ?></strong><br>
                        <small class="text-muted">
                            <?= e($recibo['empresa_cnpj'] ?? '') ?><br>
                            <?= e($recibo['empresa_endereco'] ?? '') ?><br>
                            Tel: <?= e($recibo['empresa_telefone'] ?? '') ?>
                        </small>
                    </div>
                </div>

                <!-- Corpo do Recibo -->
                <div class="recibo-corpo">
                    <p class="mb-4">
                        Eu, <strong><?= e($recibo['cliente_nome'] ?? 'Cliente') ?></strong>, 
                        inscrito(a) no CPF/CNPJ <strong><?= e($recibo['cliente_cpf_cnpj'] ?? '---') ?></strong>,
                        declaro que RECEBI de <strong><?= e($recibo['empresa_nome'] ?? 'Empresa') ?></strong>
                        a quantia de:
                    </p>

                    <div class="text-center my-4 p-3 bg-light border rounded">
                        <h2 class="mb-2"><strong>R$ <?= number_format($recibo['valor'] ?? 0, 2, ',', '.') ?></strong></h2>
                        <p class="valor-extenso mb-0">(<?= \App\Models\Recibo::valorPorExtenso($recibo['valor'] ?? 0) ?>)</p>
                    </div>

                    <p class="mb-4">
                        Referente a: <strong>Ordem de Serviço Nº <?= str_pad($recibo['numero_os'] ?? '---', 4, '0', STR_PAD_LEFT) ?></strong><br>
                        Serviço: <?= e($recibo['os_descricao'] ?? 'Serviço prestado') ?>
                    </p>

                    <p class="mb-4">
                        Forma de Pagamento: <strong><?= ucfirst($recibo['forma_pagamento'] ?? 'dinheiro') ?></strong><br>
                        Data do Pagamento: <strong><?= $recibo['data_pagamento'] ? date('d/m/Y', strtotime($recibo['data_pagamento'])) : date('d/m/Y') ?></strong>
                    </p>

                    <p class="mb-4">
                        Para maior clareza, firmo o presente recibo, dando quitação geral e irrevogável 
                        do valor recebido.
                    </p>
                </div>

                <!-- Assinaturas -->
                <div class="assinatura-area">
                    <?php if ($assinaturaConformidade): ?>
                    <div class="text-center mb-4">
                        <div class="mb-2">
                            <span class="badge bg-success"><i class="bi bi-check-circle-fill"></i> Assinatura de Conformidade</span>
                        </div>
                        <img src="<?= url('files/assinatura/' . $assinaturaConformidade['id']) ?>" 
                             alt="Assinatura de Conformidade" 
                             style="max-height: 100px; max-width: 300px; border-bottom: 1px solid #333; padding-bottom: 10px;">
                        <p class="mb-0 mt-2"><strong><?= e($assinaturaConformidade['assinante_nome']) ?></strong></p>
                        <small class="text-muted">Confirmo recebimento do serviço em <?= date('d/m/Y', strtotime($assinaturaConformidade['created_at'])) ?></small>
                    </div>
                    <?php elseif ($assinaturaAutorizacao): ?>
                    <div class="text-center mb-4">
                        <div class="mb-2">
                            <span class="badge bg-primary"><i class="bi bi-check-square"></i> Assinatura de Autorização</span>
                        </div>
                        <img src="<?= url('files/assinatura/' . $assinaturaAutorizacao['id']) ?>" 
                             alt="Assinatura de Autorização" 
                             style="max-height: 100px; max-width: 300px; border-bottom: 1px solid #333; padding-bottom: 10px;">
                        <p class="mb-0 mt-2"><strong><?= e($assinaturaAutorizacao['assinante_nome']) ?></strong></p>
                        <small class="text-muted">Autorização em <?= date('d/m/Y', strtotime($assinaturaAutorizacao['created_at'])) ?></small>
                    </div>
                    <?php else: ?>
                    <div class="mt-5">
                        <p><?= e($recibo['empresa_nome'] ?? 'Empresa') ?></p>
                        <div class="assinatura-linha">
                            Assinatura e Carimbo
                        </div>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Rodapé -->
                <div class="text-center mt-5 pt-3 border-top">
                    <small class="text-muted">
                        Documento gerado eletronicamente em <?= date('d/m/Y \à\s H:i', strtotime($recibo['created_at'])) ?><br>
                        proService - Gestão Profissional de Serviços
                    </small>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
