<?php
/**
 * Central de Ajuda - Página de Busca
 * @var array $funcionalidades Lista de funcionalidades por categoria ou resultados da busca
 * @var string $query Termo de busca (se houver)
 * @var string $categoria Filtro de categoria (se houver)
 * @var array $categorias Lista de categorias disponíveis
 * @var int $total Total de itens encontrados
 */

// Função helper para destacar termos de busca
function highlightTerm(string $text, string $term): string {
    if (empty($term)) return $text;
    return preg_replace('/(' . preg_quote($term, '/') . ')/iu', '<mark class="bg-warning px-1 rounded">$1</mark>', $text);
}
?>

<?= breadcrumb(['Dashboard' => 'dashboard', 'Central de Ajuda']) ?>

<div class="container-fluid py-4">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center mb-3">
                        <div class="bg-primary bg-opacity-10 rounded-circle p-3 me-3">
                            <i class="bi bi-question-circle-fill text-primary fs-3"></i>
                        </div>
                        <div>
                            <h1 class="h3 mb-1">Central de Ajuda</h1>
                            <p class="text-muted mb-0">Encontre guias, tutoriais e respostas para todas as funcionalidades do <?= APP_NAME ?></p>
                        </div>
                    </div>

                    <!-- Search Box -->
                    <form method="GET" action="<?= url('ajuda') ?>" class="mt-4">
                        <div class="input-group input-group-lg">
                            <span class="input-group-text bg-white border-end-0">
                                <i class="bi bi-search text-muted"></i>
                            </span>
                            <input type="text" 
                                   name="q" 
                                   class="form-control border-start-0" 
                                   placeholder="Busque por funcionalidade, configuração, dúvida... (Ex: 'criar OS', 'configurar SMTP', 'relatório financeiro')"
                                   value="<?= htmlspecialchars($query ?? '') ?>"
                                   autofocus>
                            <?php if ($categoria): ?>
                                <input type="hidden" name="categoria" value="<?= htmlspecialchars($categoria) ?>">
                            <?php endif; ?>
                            <button type="submit" class="btn btn-primary px-4">
                                <i class="bi bi-search me-2"></i>Buscar
                            </button>
                            <?php if ($query || $categoria): ?>
                                <a href="<?= url('ajuda') ?>" class="btn btn-outline-secondary">
                                    <i class="bi bi-x-lg"></i> Limpar
                                </a>
                            <?php endif; ?>
                        </div>
                    </form>

                    <!-- Quick Links -->
                    <div class="mt-3 d-flex flex-wrap gap-2">
                        <span class="text-muted small">Buscas populares:</span>
                        <a href="<?= url('ajuda?q=nova+OS') ?>" class="badge bg-light text-dark text-decoration-none">Nova OS</a>
                        <a href="<?= url('ajuda?q=cliente') ?>" class="badge bg-light text-dark text-decoration-none">Cadastrar cliente</a>
                        <a href="<?= url('ajuda?q=SMTP') ?>" class="badge bg-light text-dark text-decoration-none">Configurar e-mail</a>
                        <a href="<?= url('ajuda?q=receita') ?>" class="badge bg-light text-dark text-decoration-none">Lançar receita</a>
                        <a href="<?= url('ajuda?q=relatório') ?>" class="badge bg-light text-dark text-decoration-none">Gerar relatório</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Sidebar com Categorias -->
        <div class="col-lg-3 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="mb-0"><i class="bi bi-folder me-2"></i>Categorias</h5>
                </div>
                <div class="list-group list-group-flush">
                    <a href="<?= url('ajuda') ?>" 
                       class="list-group-item list-group-item-action d-flex justify-content-between align-items-center <?= !$categoria ? 'active' : '' ?>">
                        <span><i class="bi bi-grid-3x3-gap me-2"></i>Todas</span>
                        <span class="badge <?= $categoria ? 'bg-secondary' : 'bg-primary' ?> rounded-pill"><?= array_sum(array_column($categorias, 'total')) ?></span>
                    </a>
                    <?php foreach ($categorias as $cat): ?>
                        <a href="<?= url('ajuda?categoria=' . $cat['id']) ?>" 
                           class="list-group-item list-group-item-action d-flex justify-content-between align-items-center <?= $categoria === $cat['id'] ? 'active' : '' ?>">
                            <span><i class="bi <?= $cat['icone'] ?> me-2"></i><?= $cat['nome'] ?></span>
                            <span class="badge <?= $categoria === $cat['id'] ? 'bg-primary' : 'bg-secondary' ?> rounded-pill"><?= $cat['total'] ?></span>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Card de Suporte -->
            <div class="card border-0 shadow-sm mt-3">
                <div class="card-body">
                    <h6 class="card-title"><i class="bi bi-headset me-2 text-primary"></i>Precisa de ajuda?</h6>
                    <p class="card-text small text-muted">
                        Não encontrou o que procurava? Entre em contato com nosso suporte.
                    </p>
                    <a href="mailto:suporte@proservice.com.br" class="btn btn-outline-primary btn-sm w-100">
                        <i class="bi bi-envelope me-1"></i>Contatar Suporte
                    </a>
                </div>
            </div>
        </div>

        <!-- Conteúdo Principal -->
        <div class="col-lg-9">
            <!-- Resultados da Busca -->
            <?php if ($query): ?>
                <div class="alert alert-info d-flex align-items-center mb-4">
                    <i class="bi bi-search me-2 fs-5"></i>
                    <div>
                        <strong>Resultados para "<?= htmlspecialchars($query) ?>"</strong>
                        <span class="text-muted">— <?= $total ?> <?= $total === 1 ? 'funcionalidade encontrada' : 'funcionalidades encontradas' ?></span>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ($categoria && !$query): ?>
                <?php $catInfo = $categorias[$categoria] ?? null; ?>
                <?php if ($catInfo): ?>
                    <div class="d-flex align-items-center mb-4">
                        <i class="bi <?= $catInfo['icone'] ?> fs-2 text-primary me-3"></i>
                        <div>
                            <h4 class="mb-0"><?= $catInfo['nome'] ?></h4>
                            <p class="text-muted mb-0"><?= $catInfo['total'] ?> <?= $catInfo['total'] === 1 ? 'guia disponível' : 'guias disponíveis' ?></p>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endif; ?>

            <!-- Lista de Funcionalidades -->
            <?php if (empty($funcionalidades)): ?>
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center py-5">
                        <i class="bi bi-inbox display-1 text-muted"></i>
                        <h5 class="mt-3 text-muted">Nenhum resultado encontrado</h5>
                        <p class="text-muted">Tente buscar com outros termos ou navegue pelas categorias.</p>
                        <a href="<?= url('ajuda') ?>" class="btn btn-primary">
                            <i class="bi bi-grid me-2"></i>Ver todas as funcionalidades
                        </a>
                    </div>
                </div>
            <?php else: ?>
                <?php if (!$query && !$categoria): ?>
                    <!-- Agrupado por Categoria -->
                    <?php 
                    $currentCat = '';
                    foreach ($funcionalidades as $func): 
                        if ($currentCat !== $func['categoria']):
                            if ($currentCat !== '') echo '</div></div></div>';
                            $currentCat = $func['categoria'];
                    ?>
                        <div class="card border-0 shadow-sm mb-4">
                            <div class="card-header bg-white border-0 py-3">
                                <h5 class="mb-0">
                                    <i class="bi <?= $func['categoria_icone'] ?> me-2 text-primary"></i>
                                    <?= $func['categoria_nome'] ?>
                                </h5>
                            </div>
                            <div class="card-body p-0">
                                <div class="list-group list-group-flush">
                    <?php endif; ?>
                    
                                    <a href="<?= url('ajuda/' . $func['slug']) ?>" class="list-group-item list-group-item-action p-3">
                                        <div class="d-flex w-100 justify-content-between align-items-start">
                                            <div class="flex-grow-1">
                                                <h6 class="mb-1"><?= $func['titulo'] ?></h6>
                                                <p class="mb-1 text-muted small"><?= $func['descricao'] ?></p>
                                                <div class="d-flex flex-wrap gap-1 mt-2">
                                                    <?php foreach (array_slice($func['tags'], 0, 4) as $tag): ?>
                                                        <span class="badge bg-light text-dark border"><?= $tag ?></span>
                                                    <?php endforeach; ?>
                                                    <?php if (count($func['tags']) > 4): ?>
                                                        <span class="badge bg-light text-dark border">+<?= count($func['tags']) - 4 ?></span>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                            <i class="bi bi-chevron-right text-muted ms-3"></i>
                                        </div>
                                    </a>

                    <?php endforeach; ?>
                    <?php if ($currentCat !== '') echo '</div></div></div>'; ?>

                <?php else: ?>
                    <!-- Resultados de Busca ou Categoria Específica -->
                    <div class="card border-0 shadow-sm">
                        <div class="list-group list-group-flush">
                            <?php foreach ($funcionalidades as $func): ?>
                                <a href="<?= url('ajuda/' . $func['slug']) ?>" class="list-group-item list-group-item-action p-3">
                                    <div class="d-flex w-100 justify-content-between align-items-start">
                                        <div class="flex-grow-1">
                                            <div class="d-flex align-items-center mb-1">
                                                <i class="bi <?= $func['categoria_icone'] ?> text-primary me-2"></i>
                                                <span class="badge bg-light text-dark border me-2"><?= $func['categoria_nome'] ?></span>
                                                <?php if (isset($func['score']) && $func['score'] >= 10): ?>
                                                    <span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>Melhor correspondência</span>
                                                <?php endif; ?>
                                            </div>
                                            <h6 class="mb-1"><?= highlightTerm($func['titulo'], $query) ?></h6>
                                            <p class="mb-1 text-muted small"><?= highlightTerm($func['descricao'], $query) ?></p>
                                            <div class="d-flex flex-wrap gap-1 mt-2">
                                                <?php foreach (array_slice($func['tags'], 0, 5) as $tag): ?>
                                                    <?php $isMatch = $query && mb_strpos(mb_strtolower($tag), mb_strtolower($query)) !== false; ?>
                                                    <span class="badge <?= $isMatch ? 'bg-primary' : 'bg-light text-dark border' ?>"><?= $tag ?></span>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>
                                        <i class="bi bi-chevron-right text-muted ms-3"></i>
                                    </div>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
.list-group-item-action:hover {
    background-color: #f8f9fa;
}
.list-group-item-action:active {
    background-color: #e9ecef;
}
mark {
    padding: 0.125rem 0.25rem;
}
</style>
