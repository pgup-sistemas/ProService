<?php
/**
 * Central de Ajuda - Detalhe da Funcionalidade
 * @var array $item Dados da funcionalidade
 * @var array $relacionados Itens relacionados
 */
?>

<?= breadcrumb(['Dashboard' => 'dashboard', 'Central de Ajuda' => 'ajuda', $item['titulo']]) ?>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-lg-8">
            <!-- Header da Funcionalidade -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center mb-3">
                        <a href="<?= url('ajuda?categoria=' . $item['categoria']) ?>" class="text-decoration-none">
                            <span class="badge bg-light text-dark border me-2">
                                <i class="bi <?= $item['categoria_icone'] ?> me-1"></i><?= $item['categoria_nome'] ?>
                            </span>
                        </a>
                        <?php if ($item['url']): ?>
                            <span class="badge bg-success">
                                <i class="bi bi-check-circle me-1"></i>Disponível no sistema
                            </span>
                        <?php endif; ?>
                    </div>

                    <h1 class="h2 mb-3"><?= $item['titulo'] ?></h1>
                    <p class="lead text-muted"><?= $item['descricao'] ?></p>

                    <!-- Tags -->
                    <div class="d-flex flex-wrap gap-2 mb-3">
                        <?php foreach ($item['tags'] as $tag): ?>
                            <a href="<?= url('ajuda?q=' . urlencode($tag)) ?>" class="badge bg-light text-dark border text-decoration-none">
                                #<?= $tag ?>
                            </a>
                        <?php endforeach; ?>
                    </div>

                    <?php if ($item['url']): ?>
                        <a href="<?= url($item['url']) ?>" class="btn btn-primary">
                            <i class="bi bi-box-arrow-in-right me-2"></i>Acessar Funcionalidade
                        </a>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Descrição Completa -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0"><i class="bi bi-file-text me-2 text-primary"></i>Sobre</h5>
                </div>
                <div class="card-body">
                    <p class="mb-0"><?= nl2br($item['conteudo']) ?></p>
                </div>
            </div>

            <!-- Passo a Passo -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0"><i class="bi bi-list-ol me-2 text-primary"></i>Passo a Passo</h5>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        <?php foreach ($item['passos'] as $index => $passo): ?>
                            <div class="list-group-item d-flex">
                                <span class="badge bg-primary rounded-pill me-3" style="min-width: 28px;"><?= $index + 1 ?></span>
                                <span><?= $passo ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- Dicas -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0"><i class="bi bi-lightbulb me-2 text-warning"></i>Dicas e Boas Práticas</h5>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled mb-0">
                        <?php foreach ($item['dicas'] as $dica): ?>
                            <li class="mb-2 d-flex">
                                <i class="bi bi-check-circle-fill text-success me-2 mt-1"></i>
                                <span><?= $dica ?></span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>

            <!-- Navegação Inferior -->
            <div class="d-flex justify-content-between">
                <a href="<?= url('ajuda') ?>" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-2"></i>Voltar para Central
                </a>
                <?php if ($item['url']): ?>
                    <a href="<?= url($item['url']) ?>" class="btn btn-primary">
                        <i class="bi bi-box-arrow-in-right me-2"></i>Abrir no Sistema
                    </a>
                <?php endif; ?>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Acesso Rápido -->
            <?php if ($item['url']): ?>
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-primary text-white py-3">
                        <h6 class="mb-0"><i class="bi bi-lightning me-2"></i>Acesso Rápido</h6>
                    </div>
                    <div class="card-body">
                        <p class="small text-muted">Acesse diretamente esta funcionalidade no sistema:</p>
                        <a href="<?= url($item['url']) ?>" class="btn btn-outline-primary w-100">
                            <i class="bi bi-box-arrow-in-right me-2"></i>Abrir <?= $item['categoria_nome'] ?>
                        </a>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Informações -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h6 class="mb-0"><i class="bi bi-info-circle me-2"></i>Informações</h6>
                </div>
                <ul class="list-group list-group-flush">
                    <li class="list-group-item d-flex justify-content-between">
                        <span class="text-muted">Categoria</span>
                        <span class="fw-medium">
                            <i class="bi <?= $item['categoria_icone'] ?> me-1"></i><?= $item['categoria_nome'] ?>
                        </span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between">
                        <span class="text-muted">Perfis com acesso</span>
                        <span>
                            <?php foreach ($item['perfis'] as $perfil): ?>
                                <span class="badge bg-light text-dark border">
                                    <?= $perfil === 'admin' ? 'Administrador' : 'Técnico' ?>
                                </span>
                            <?php endforeach; ?>
                        </span>
                    </li>
                </ul>
            </div>

            <!-- Relacionados -->
            <?php if (!empty($relacionados)): ?>
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white py-3">
                        <h6 class="mb-0"><i class="bi bi-diagram-3 me-2"></i>Relacionados</h6>
                    </div>
                    <div class="list-group list-group-flush">
                        <?php foreach ($relacionados as $rel): ?>
                            <a href="<?= url('ajuda/' . $rel['slug']) ?>" class="list-group-item list-group-item-action">
                                <div class="d-flex align-items-center">
                                    <i class="bi bi-arrow-return-right text-muted me-2"></i>
                                    <div>
                                        <div class="small fw-medium"><?= $rel['titulo'] ?></div>
                                        <div class="small text-muted"><?= $rel['descricao'] ?></div>
                                    </div>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Precisa de Ajuda -->
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h6 class="card-title"><i class="bi bi-headset me-2 text-primary"></i>Ainda com dúvidas?</h6>
                    <p class="card-text small text-muted">
                        Se esta documentação não resolveu sua questão, nosso suporte está disponível.
                    </p>
                    <a href="mailto:suporte@proservice.com.br?subject=Dúvida sobre: <?= urlencode($item['titulo']) ?>" 
                       class="btn btn-outline-primary btn-sm w-100">
                        <i class="bi bi-envelope me-1"></i>Falar com Suporte
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.list-group-item-action:hover {
    background-color: #f8f9fa;
}
mark {
    padding: 0.125rem 0.25rem;
    background-color: #fff3cd;
}
</style>
