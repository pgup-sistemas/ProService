<?php
/**
 * proService - Preview do Contrato
 * Arquivo: /app/views/configuracoes/preview_contrato.php
 */
?>

<?= breadcrumb(['Dashboard' => 'dashboard', 'Configurações' => 'configuracoes', 'Template de Contrato' => 'configuracoes/contrato', 'Preview']) ?>

<div class="container-fluid">
    <div class="mb-4">
        <h2 class="mb-0"><i class="bi bi-eye text-primary"></i> Preview do Contrato</h2>
        <p class="text-muted">Visualização do contrato com dados de exemplo</p>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-file-text"></i> Contrato Preenchido</h5>
                    <div>
                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="window.print()">
                            <i class="bi bi-printer"></i> Imprimir
                        </button>
                        <a href="<?= url('configuracoes/contrato') ?>" class="btn btn-sm btn-outline-secondary">
                            <i class="bi bi-arrow-left"></i> Voltar ao Editor
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Aviso -->
                    <div class="alert alert-warning mb-4">
                        <i class="bi bi-exclamation-triangle"></i> 
                        <strong>Atenção:</strong> Este é um preview com dados de exemplo. 
                        Na OS real, os dados serão substituídos automaticamente.
                    </div>

                    <!-- Contrato -->
                    <div class="contrato-content p-4 border rounded bg-white" style="min-height: 600px;">
                        <?= $contrato ?>
                    </div>

                    <!-- Dados de Exemplo -->
                    <div class="mt-4 p-3 bg-light rounded">
                        <h6 class="text-muted mb-2"><i class="bi bi-info-circle"></i> Dados de exemplo usados neste preview:</h6>
                        <div class="row small text-muted">
                            <div class="col-md-4">
                                <strong>Empresa:</strong> Minha Empresa LTDA<br>
                                <strong>CNPJ:</strong> 00.000.000/0001-00<br>
                                <strong>Endereço:</strong> Rua Exemplo, 123 - Centro
                            </div>
                            <div class="col-md-4">
                                <strong>Cliente:</strong> Cliente Exemplo<br>
                                <strong>CPF:</strong> 123.456.789-00<br>
                                <strong>Endereço:</strong> Av. Teste, 456 - Bairro
                            </div>
                            <div class="col-md-4">
                                <strong>OS:</strong> #1234<br>
                                <strong>Serviço:</strong> Formatação de Computador<br>
                                <strong>Valor:</strong> R$ 250,00
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
@media print {
    .card-header .btn,
    .alert,
    .bg-light.rounded {
        display: none !important;
    }
    
    .card {
        border: none !important;
    }
    
    .contrato-content {
        border: none !important;
    }
}

.contrato-content h2 {
    text-align: center;
    margin-bottom: 30px;
    border-bottom: 2px solid #333;
    padding-bottom: 15px;
}

.contrato-content h3 {
    margin-top: 25px;
    margin-bottom: 15px;
    color: #333;
    font-size: 1.2rem;
}

.contrato-content p {
    margin-bottom: 10px;
    line-height: 1.6;
    text-align: justify;
}

.contrato-content table {
    margin-top: 40px;
}
</style>
