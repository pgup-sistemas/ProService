<?= breadcrumb(['Dashboard' => 'dashboard', 'Ordens de Serviço' => 'ordens', 'OS #' . str_pad($os['numero_os'], 4, '0', STR_PAD_LEFT)]) ?>

<div class="d-flex align-items-center mb-4">
   
    <div>
        <h4 class="mb-0">OS #<?= str_pad($os['numero_os'], 4, '0', STR_PAD_LEFT) ?></h4>
        <p class="mb-0 text-muted"><?= e($os['cliente_nome']) ?></p>
    </div>
    <div class="ms-auto d-flex gap-2">
        <!-- Botões de Ação -->
        <?php if (!in_array($os['status'], ['cancelada', 'paga'])): ?>
            <a href="<?= url('ordens/edit/' . $os['id']) ?>" class="btn btn-outline-primary">
                <i class="bi bi-pencil"></i> Editar
            </a>
        <?php endif; ?>
        
        <?php if (isAdmin() && !in_array($os['status'], ['paga'])): ?>
            <form method="POST" action="<?= url('ordens/destroy/' . $os['id']) ?>" class="d-inline" onsubmit="return confirm('Tem certeza que deseja excluir esta OS? Esta ação não pode ser desfeita.');">
                <?= csrfField() ?>
                <button type="submit" class="btn btn-outline-danger">
                    <i class="bi bi-trash"></i> Excluir
                </button>
            </form>
        <?php endif; ?>
        
        <span class="badge <?= getStatusClass($os['status']) ?> fs-6 d-flex align-items-center">
            <?= getStatusLabel($os['status']) ?>
        </span>
    </div>
</div>

<div class="row g-3">
    <!-- Informações Principais -->
    <div class="col-lg-8">
        <div class="card mb-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0"><i class="bi bi-info-circle"></i> Informações</h6>
                <div class="btn-group">
                    <?php if (in_array($os['status'], ['aberta', 'em_orcamento', 'aprovada', 'em_execucao', 'pausada'])): ?>
                        <?php if ($os['status'] !== 'em_execucao'): ?>
                        <form method="POST" action="<?= url('ordens/status/' . $os['id']) ?>" class="d-inline">
                            <?= csrfField() ?>
                            <input type="hidden" name="status" value="em_execucao">
                            <button type="submit" class="btn btn-sm btn-warning">Iniciar Execução</button>
                        </form>
                        <?php endif; ?>
                        <?php if ($os['status'] === 'em_execucao'): ?>
                        <form method="POST" action="<?= url('ordens/status/' . $os['id']) ?>" class="d-inline">
                            <?= csrfField() ?>
                            <input type="hidden" name="status" value="finalizada">
                            <button type="submit" class="btn btn-sm btn-success">Finalizar</button>
                        </form>
                        <?php endif; ?>
                    <?php endif; ?>
                    <?php if ($os['status'] === 'finalizada'): ?>
                    <form method="POST" action="<?= url('ordens/status/' . $os['id']) ?>" class="d-inline">
                        <?= csrfField() ?>
                        <input type="hidden" name="status" value="paga">
                        <button type="submit" class="btn btn-sm btn-success">Marcar como Paga</button>
                    </form>
                    <?php endif; ?>
                </div>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="text-muted small">Cliente</label>
                        <p class="fw-medium mb-1"><?= e($os['cliente_nome']) ?></p>
                        <?php if ($os['cliente_telefone']): ?>
                        <p class="mb-0 small"><i class="bi bi-telephone"></i> <?= formatPhone($os['cliente_telefone']) ?></p>
                        <?php endif; ?>
                    </div>
                    <div class="col-md-6">
                        <label class="text-muted small">Serviço</label>
                        <p class="fw-medium"><?= e($os['servico_nome'] ?? '-') ?></p>
                    </div>
                    <div class="col-md-4">
                        <label class="text-muted small">Técnico</label>
                        <p class="fw-medium"><?= e($os['tecnico_nome'] ?? '-') ?></p>
                    </div>
                    <div class="col-md-4">
                        <label class="text-muted small">Prioridade</label>
                        <p><span class="badge <?= getPrioridadeClass($os['prioridade']) ?>"><?= getPrioridadeLabel($os['prioridade']) ?></span></p>
                    </div>
                    <div class="col-md-4">
                        <label class="text-muted small">Data de Entrada</label>
                        <p class="fw-medium"><?= formatDate($os['data_entrada']) ?></p>
                    </div>
                    <?php if ($os['previsao_entrega']): ?>
                    <div class="col-md-4">
                        <label class="text-muted small">Previsão de Entrega</label>
                        <p class="fw-medium <?= $os['previsao_entrega'] < date('Y-m-d') && !in_array($os['status'], ['finalizada', 'paga', 'cancelada']) ? 'text-danger' : '' ?>">
                            <?= formatDate($os['previsao_entrega']) ?>
                        </p>
                    </div>
                    <?php endif; ?>
                    <?php if ($os['garantia_dias']): ?>
                    <div class="col-md-4">
                        <label class="text-muted small">Garantia</label>
                        <p class="fw-medium"><?= $os['garantia_dias'] ?> dias</p>
                    </div>
                    <?php endif; ?>
                </div>
                
                <?php if ($os['descricao']): ?>
                <hr>
                <label class="text-muted small">Descrição</label>
                <p><?= nl2br(e($os['descricao'])) ?></p>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Produtos -->
        <div class="card mb-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0"><i class="bi bi-box-seam"></i> Produtos Utilizados</h6>
                <?php if (!in_array($os['status'], ['finalizada', 'paga', 'cancelada'])): ?>
                    <a href="<?= url('ordens/edit/' . $os['id']) ?>" class="btn btn-sm btn-outline-primary">
                        <i class="bi bi-pencil"></i> Editar Produtos
                    </a>
                <?php endif; ?>
            </div>
            <div class="card-body">
                <?php if (empty($produtos)): ?>
                    <p class="text-muted mb-0">Nenhum produto adicionado</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Produto</th>
                                    <th>Qtd</th>
                                    <th>Preço Unit.</th>
                                    <th>Preço Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($produtos as $p): ?>
                                <tr>
                                    <td><?= e($p['produto_nome']) ?></td>
                                    <td><?= $p['quantidade'] ?></td>
                                    <td><?= formatMoney($p['preco_unitario'] ?? $p['custo_unitario']) ?></td>
                                    <td><?= formatMoney($p['preco_total'] ?? $p['custo_total']) ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Fotos / Galeria -->
        <div class="card mb-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0"><i class="bi bi-camera"></i> Fotos</h6>
                <?php if (!in_array($os['status'], ['cancelada'])): ?>
                <button class="btn btn-sm btn-outline-primary" type="button" data-bs-toggle="collapse" data-bs-target="#uploadFoto">
                    <i class="bi bi-plus-lg"></i> Adicionar Foto
                </button>
                <?php endif; ?>
            </div>
            <div class="card-body">
                <!-- Formulário de Upload -->
                <div class="collapse mb-3" id="uploadFoto">
                    <form method="POST" action="<?= url('ordens/foto/' . $os['id']) ?>" enctype="multipart/form-data">
                        <?= csrfField() ?>
                        <div class="row g-2">
                            <div class="col-md-6">
                                <label class="form-label">Tipo</label>
                                <select name="tipo_foto" class="form-select">
                                    <option value="antes">Antes do Serviço</option>
                                    <option value="durante">Durante o Serviço</option>
                                    <option value="depois">Depois do Serviço</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Foto</label>
                                <input type="file" name="foto" class="form-control" accept="image/jpeg,image/png" required>
                                <div class="form-text">Max 5MB (JPG/PNG)</div>
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary btn-sm">
                                    <i class="bi bi-upload"></i> Enviar Foto
                                </button>
                            </div>
                        </div>
                    </form>
                    <hr>
                </div>
                
                <!-- Galeria de Fotos -->
                <?php if (!empty($fotos)): ?>
                    <div class="row g-2">
                        <?php foreach ($fotos as $foto): ?>
                        <div class="col-6 col-md-4 col-lg-3">
                            <div class="position-relative">
                                <a href="<?= url('public/' . $foto['arquivo']) ?>" target="_blank" class="d-block">
                                    <img src="<?= url('public/' . $foto['arquivo']) ?>" class="img-fluid rounded" style="width: 100%; height: 120px; object-fit: cover;">
                                </a>
                                <span class="position-absolute top-0 start-0 badge bg-<?= $foto['tipo'] === 'depois' ? 'success' : ($foto['tipo'] === 'durante' ? 'warning' : 'secondary') ?> m-1">
                                    <?= ucfirst($foto['tipo']) ?>
                                </span>
                                <form method="POST" action="<?= url('ordens/foto/' . $os['id'] . '/remover/' . $foto['id']) ?>" class="position-absolute top-0 end-0 m-1">
                                    <?= csrfField() ?>
                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Remover esta foto?')">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p class="text-muted mb-0">Nenhuma foto adicionada ainda.</p>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Valores -->
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0"><i class="bi bi-cash"></i> Valores</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-sm">
                            <tr>
                                <td>Valor do Serviço:</td>
                                <td class="text-end"><?= formatMoney($os['valor_servico']) ?></td>
                            </tr>
                            <tr>
                                <td>Taxas Adicionais:</td>
                                <td class="text-end"><?= formatMoney($os['taxas_adicionais']) ?></td>
                            </tr>
                            <?php if (!empty($produtos)): ?>
                                <?php foreach ($produtos as $p): ?>
                                <tr>
                                    <td><i class="bi bi-box-seam text-muted"></i> <?= e($p['produto_nome']) ?> (<?= $p['quantidade'] ?>x):</td>
                                    <td class="text-end"><?= formatMoney($p['preco_total'] ?? $p['custo_total']) ?></td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                            <tr>
                                <td>Desconto:</td>
                                <td class="text-end text-danger">- <?= formatMoney($os['desconto']) ?></td>
                            </tr>
                            <tr class="table-primary">
                                <td class="fw-bold">Valor Total:</td>
                                <td class="text-end fw-bold"><?= formatMoney($os['valor_total']) ?></td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-sm">
                            <tr>
                                <td>Custo dos Produtos:</td>
                                <td class="text-end"><?= formatMoney($os['custo_produtos']) ?></td>
                            </tr>
                            <tr class="table-success">
                                <td class="fw-bold">Lucro Real:</td>
                                <td class="text-end fw-bold"><?= formatMoney($os['lucro_real']) ?></td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Fluxo de Status / Workflow -->
        <div class="card mt-3 mb-3">
            <div class="card-header mb-3">
                <h6 class="mb-0"><i class="bi bi-arrow-repeat"></i> Fluxo de Trabalho</h6>
            </div>
            <div class="card-body">
                <div class="d-flex flex-wrap gap-2">
                    <?php
                    // Definir ações disponíveis baseadas no status atual
                    $acoes = [];
                    
                    switch ($os['status']) {
                        case 'aberta':
                            $acoes = [
                                ['status' => 'em_orcamento', 'label' => 'Enviar Orçamento', 'class' => 'btn-info', 'icon' => 'bi-calculator'],
                                ['status' => 'em_execucao', 'label' => 'Iniciar Execução', 'class' => 'btn-warning', 'icon' => 'bi-play-fill'],
                                ['status' => 'cancelada', 'label' => 'Cancelar OS', 'class' => 'btn-secondary', 'icon' => 'bi-x-circle']
                            ];
                            break;
                        case 'em_orcamento':
                            $acoes = [
                                ['status' => 'aprovada', 'label' => 'Aprovar Orçamento', 'class' => 'btn-success', 'icon' => 'bi-check-circle'],
                                ['status' => 'aberta', 'label' => 'Voltar para Aberta', 'class' => 'btn-outline-secondary', 'icon' => 'bi-arrow-left'],
                                ['status' => 'cancelada', 'label' => 'Cancelar OS', 'class' => 'btn-secondary', 'icon' => 'bi-x-circle']
                            ];
                            break;
                        case 'aprovada':
                            $acoes = [
                                ['status' => 'em_execucao', 'label' => 'Iniciar Execução', 'class' => 'btn-warning', 'icon' => 'bi-play-fill'],
                                ['status' => 'aberta', 'label' => 'Voltar para Aberta', 'class' => 'btn-outline-secondary', 'icon' => 'bi-arrow-left'],
                                ['status' => 'cancelada', 'label' => 'Cancelar OS', 'class' => 'btn-secondary', 'icon' => 'bi-x-circle']
                            ];
                            break;
                        case 'em_execucao':
                            $acoes = [
                                ['status' => 'pausada', 'label' => 'Pausar', 'class' => 'btn-outline-warning', 'icon' => 'bi-pause-fill'],
                                ['status' => 'finalizada', 'label' => 'Finalizar', 'class' => 'btn-success', 'icon' => 'bi-check-lg'],
                                ['status' => 'aberta', 'label' => 'Voltar para Aberta', 'class' => 'btn-outline-secondary', 'icon' => 'bi-arrow-left']
                            ];
                            break;
                        case 'pausada':
                            $acoes = [
                                ['status' => 'em_execucao', 'label' => 'Retomar Execução', 'class' => 'btn-warning', 'icon' => 'bi-play-fill'],
                                ['status' => 'finalizada', 'label' => 'Finalizar', 'class' => 'btn-success', 'icon' => 'bi-check-lg'],
                                ['status' => 'aberta', 'label' => 'Voltar para Aberta', 'class' => 'btn-outline-secondary', 'icon' => 'bi-arrow-left']
                            ];
                            break;
                        case 'finalizada':
                            $acoes = [
                                ['status' => 'paga', 'label' => 'Marcar como Paga', 'class' => 'btn-success', 'icon' => 'bi-cash-coin'],
                                ['status' => 'em_execucao', 'label' => 'Voltar para Execução', 'class' => 'btn-outline-warning', 'icon' => 'bi-arrow-left'],
                                ['status' => 'cancelada', 'label' => 'Cancelar OS', 'class' => 'btn-secondary', 'icon' => 'bi-x-circle']
                            ];
                            break;
                        case 'paga':
                            $acoes = [
                                ['status' => 'finalizada', 'label' => 'Voltar para Finalizada', 'class' => 'btn-outline-secondary', 'icon' => 'bi-arrow-left']
                            ];
                            break;
                        case 'cancelada':
                            $acoes = [
                                ['status' => 'aberta', 'label' => 'Reabrir OS', 'class' => 'btn-primary', 'icon' => 'bi-arrow-counter-clockwise']
                            ];
                            break;
                    }
                    
                    foreach ($acoes as $acao): ?>
                        <form method="POST" action="<?= url('ordens/status/' . $os['id']) ?>" class="d-inline">
                            <?= csrfField() ?>
                            <input type="hidden" name="status" value="<?= $acao['status'] ?>">
                            <button type="submit" class="btn <?= $acao['class'] ?>">
                                <i class="bi <?= $acao['icon'] ?>"></i> <?= $acao['label'] ?>
                            </button>
                        </form>
                    <?php endforeach; ?>
                    
                    <?php
                    // Buscar assinaturas da OS
                    $assinaturaModel = new \App\Models\Assinatura();
                    $statusAssinaturas = $assinaturaModel->statusAssinaturas($os['id']);
                    ?>
                    
                    <?php if (!$statusAssinaturas['autorizacao']): ?>
                        <a href="<?= url('ordens/assinatura/' . $os['id']) ?>" class="btn btn-outline-dark">
                            <i class="bi bi-pen"></i> Assinatura de Autorização
                        </a>
                    <?php endif; ?>
                    
                    <?php if (in_array($os['status'], ['finalizada', 'paga']) && !$statusAssinaturas['conformidade']): ?>
                        <a href="<?= url('ordens/assinatura/' . $os['id']) ?>" class="btn btn-outline-dark">
                            <i class="bi bi-pen"></i> Assinatura de Conformidade
                        </a>
                    <?php endif; ?>
                    
                    <!-- Badges de status das assinaturas -->
                    <?php if ($statusAssinaturas['autorizacao']): ?>
                        <span class="badge bg-primary"><i class="bi bi-check-square"></i> Autorização OK</span>
                    <?php endif; ?>
                    
                    <?php if ($statusAssinaturas['conformidade']): ?>
                        <span class="badge bg-success"><i class="bi bi-check-circle-fill"></i> Conformidade OK</span>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Histórico de Comunicações -->
        <?php if (!empty($comunicacoes)): ?>
        <div class="card mb-3">
            <div class="card-header">
                <h6 class="mb-0"><i class="bi bi-chat-left-text"></i> Comunicações</h6>
            </div>
            <div class="card-body">
                <div class="list-group list-group-flush">
                    <?php foreach ($comunicacoes as $c): 
                        $tipoLabel = $c['tipo'] === 'whatsapp' ? 'WhatsApp' : ($c['tipo'] === 'email' ? 'E-mail' : ucfirst($c['tipo']));
                        $icone = $c['tipo'] === 'whatsapp' ? 'bi-whatsapp text-success' : 'bi-envelope';
                    ?>
                    <div class="list-group-item d-flex gap-3 px-0">
                        <div class="flex-shrink-0">
                            <i class="bi <?= $icone ?> fs-5"></i>
                        </div>
                        <div class="flex-grow-1 small">
                            <div class="d-flex justify-content-between align-items-start mb-1">
                                <span class="badge bg-light text-dark"><?= e($c['template_usado']) ?></span>
                                <span class="text-muted"><?= date('d/m/Y H:i', strtotime($c['created_at'])) ?></span>
                            </div>
                            <p class="mb-1 text-muted"><?= $tipoLabel ?> - <?= e($c['cliente_nome'] ?? 'Cliente') ?></p>
                            <span class="badge bg-<?= $c['status'] === 'enviado' || $c['status'] === 'disparo_auto' ? 'success' : ($c['status'] === 'falha' ? 'danger' : 'secondary') ?>"><?= $c['status'] === 'disparo_auto' ? 'Disparo automático' : e($c['status']) ?></span>
                            <?php if (!empty($c['mensagem_enviada'])): ?>
                            <details class="mt-1">
                                <summary class="text-primary cursor-pointer">Ver mensagem</summary>
                                <pre class="small bg-light p-2 rounded mt-1 mb-0" style="white-space: pre-wrap; max-height: 120px; overflow-y: auto;"><?= e($c['mensagem_enviada']) ?></pre>
                            </details>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Histórico / Timeline -->
        <?php if (!empty($logs)): ?>
        <div class="card mb-3">
            <div class="card-header">
                <h6 class="mb-0"><i class="bi bi-clock-history"></i> Histórico de Alterações</h6>
            </div>
            <div class="card-body">
                <div class="timeline">
                    <?php foreach ($logs as $log): 
                        $tipoAcao = $log['tipo_acao'] ?? 'outro';
                        $icone = \App\Models\OsLog::getIconePorTipo($tipoAcao);
                        $cor = \App\Models\OsLog::getCorPorTipo($tipoAcao);
                    ?>
                    <div class="d-flex mb-3">
                        <div class="flex-shrink-0">
                            <div class="bg-<?= $cor ?> text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                                <i class="bi <?= $icone ?> small"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <p class="mb-1"><?= e($log['acao']) ?></p>
                                    <small class="text-muted">
                                        <i class="bi bi-person"></i> <?= e($log['usuario_nome'] ?? 'Sistema') ?>
                                        <span class="mx-1">|</span>
                                        <i class="bi bi-clock"></i> <?= date('d/m/Y H:i', strtotime($log['created_at'])) ?>
                                    </small>
                                </div>
                                <span class="badge bg-light text-dark border"><?= $tipoAcao ?></span>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
    
    <!-- Coluna Lateral -->
    <div class="col-lg-4">
        <!-- Link Público -->
        <div class="card mb-3">
            <div class="card-header">
                <h6 class="mb-0"><i class="bi bi-link-45deg"></i> Link de Acompanhamento</h6>
            </div>
            <div class="card-body">
                <div class="input-group mb-2">
                    <input type="text" class="form-control form-control-sm" value="<?= url('acompanhar/' . $os['token_publico']) ?>" readonly id="link-publico">
                    <button class="btn btn-sm btn-outline-secondary" type="button" onclick="copiarLink()">
                        <i class="bi bi-clipboard"></i>
                    </button>
                </div>
                <small class="text-muted">Envie este link para o cliente acompanhar o status da OS</small>
            </div>
        </div>
        
        <!-- Gerar Contrato -->
        <div class="card mb-3">
            <div class="card-header">
                <h6 class="mb-0"><i class="bi bi-file-text text-primary"></i> Contrato</h6>
            </div>
            <div class="card-body">
                <a href="<?= url('configuracoes/gerar-contrato/' . $os['id']) ?>" class="btn btn-outline-primary w-100" target="_blank">
                    <i class="bi bi-file-earmark-text"></i> Gerar Contrato
                </a>
                <small class="text-muted d-block mt-2">Gera contrato preenchido com dados desta OS</small>
            </div>
        </div>
        
        <!-- Envio WhatsApp -->
        <div class="card mb-3">
            <div class="card-header">
                <h6 class="mb-0"><i class="bi bi-whatsapp text-success"></i> Enviar WhatsApp</h6>
            </div>
            <div class="card-body">
                <?php
                $variaveis = [
                    'cliente_nome' => $os['cliente_nome'],
                    'numero_os' => str_pad($os['numero_os'], 4, '0', STR_PAD_LEFT),
                    'servico' => $os['servico_nome'] ?? $os['descricao'] ?? 'Serviço',
                    'valor' => formatMoney($os['valor_total']),
                    'previsao' => $os['previsao_entrega'] ? date('d/m/Y', strtotime($os['previsao_entrega'])) : 'A definir',
                    'link_acompanhamento' => url('acompanhar/' . $os['token_publico']),
                    'link_recibo' => $recibo ? url('recibos/show/' . $recibo['id']) : ''
                ];
                
                $templates = \App\Models\Comunicacao::$templates;
                ?>
                
                <div class="d-grid gap-2">
                    <a href="<?= \App\Models\Comunicacao::gerarLinkWhatsApp($os['cliente_telefone'], \App\Models\Comunicacao::processarTemplate('os_criada', $variaveis)) ?>" 
                       target="_blank" class="btn btn-outline-success btn-sm" 
                       onclick="registrarWhatsApp('os_criada')">
                        <i class="bi bi-chat-dots"></i> OS Criada
                    </a>
                    
                    <?php if (in_array($os['status'], ['em_orcamento', 'aprovada'])): ?>
                    <a href="<?= \App\Models\Comunicacao::gerarLinkWhatsApp($os['cliente_telefone'], \App\Models\Comunicacao::processarTemplate('orcamento_enviado', $variaveis)) ?>" 
                       target="_blank" class="btn btn-outline-success btn-sm"
                       onclick="registrarWhatsApp('orcamento_enviado')">
                        <i class="bi bi-calculator"></i> Orçamento
                    </a>
                    <?php endif; ?>
                    
                    <?php if (in_array($os['status'], ['finalizada', 'paga'])): ?>
                    <a href="<?= \App\Models\Comunicacao::gerarLinkWhatsApp($os['cliente_telefone'], \App\Models\Comunicacao::processarTemplate('os_finalizada', $variaveis)) ?>" 
                       target="_blank" class="btn btn-outline-success btn-sm"
                       onclick="registrarWhatsApp('os_finalizada')">
                        <i class="bi bi-check-lg"></i> OS Finalizada
                    </a>
                    <?php endif; ?>
                    
                    <?php if ($os['status'] === 'paga'): ?>
                    <a href="<?= \App\Models\Comunicacao::gerarLinkWhatsApp($os['cliente_telefone'], \App\Models\Comunicacao::processarTemplate('pagamento_recebido', $variaveis)) ?>" 
                       target="_blank" class="btn btn-outline-success btn-sm"
                       onclick="registrarWhatsApp('pagamento_recebido')">
                        <i class="bi bi-cash-coin"></i> Pagamento
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Receita -->
        <?php if ($receita): ?>
        <div class="card mb-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0"><i class="bi bi-cash-coin"></i> Receita</h6>
                <?php if ($recibo): ?>
                <a href="<?= url('ordens/recibo/' . $os['id']) ?>" class="btn btn-sm btn-outline-success" target="_blank">
                    <i class="bi bi-receipt"></i> Ver Recibo
                </a>
                <?php endif; ?>
            </div>
            <div class="card-body">
                <p class="mb-1">Status: <span class="badge bg-<?= $receita['status'] === 'recebido' ? 'success' : ($receita['status'] === 'pendente' ? 'warning' : 'danger') ?>"><?= $receita['status'] === 'recebido' ? 'Recebido' : ($receita['status'] === 'pendente' ? 'Pendente' : 'Cancelado') ?></span></p>
                <?php if ($receita['data_recebimento']): ?>
                <p class="mb-0 small text-muted">Recebido em: <?= formatDate($receita['data_recebimento']) ?></p>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Observações -->
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0"><i class="bi bi-sticky"></i> Observações</h6>
            </div>
            <div class="card-body">
                <?php if ($os['observacoes_internas']): ?>
                <label class="text-muted small">Internas</label>
                <p class="small"><?= nl2br(e($os['observacoes_internas'])) ?></p>
                <?php endif; ?>
                <?php if ($os['observacoes_cliente']): ?>
                <label class="text-muted small">Para Cliente</label>
                <p class="small"><?= nl2br(e($os['observacoes_cliente'])) ?></p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
function copiarLink() {
    const input = document.getElementById('link-publico');
    input.select();
    document.execCommand('copy');
    alert('Link copiado!');
}

function registrarWhatsApp(template) {
    // Fazer requisição AJAX para registrar o envio
    fetch('<?= url('ordens/whatsapp/' . $os['id']) ?>?template=' + template)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                console.log('WhatsApp registrado com sucesso');
            }
        })
        .catch(error => console.error('Erro ao registrar WhatsApp:', error));
}
</script>
