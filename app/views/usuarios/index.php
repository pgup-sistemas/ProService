<?php
/**
 * proService - Lista de Usuários
 */
?>

<?= breadcrumb(['Dashboard' => 'dashboard', 'Gestão de Usuários' => 'usuarios', 'Lista']) ?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-0"><i class="bi bi-people-fill text-primary"></i> Gestão de Usuários</h2>
            <p class="text-muted mb-0">Administre todos os usuários do sistema</p>
        </div>
        <div class="d-flex gap-2">
            <a href="<?= url('usuarios/create') ?>" class="btn btn-primary">
                <i class="bi bi-plus-lg"></i> Novo Usuário
            </a>
        </div>
    </div>

    <!-- Filtros -->
    <div class="card mb-4">
        <div class="card-body py-3">
            <form method="GET" action="<?= url('usuarios') ?>" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label">Perfil</label>
                    <select name="perfil" class="form-select">
                        <option value="">Todos</option>
                        <option value="admin" <?= $filtroPerfil === 'admin' ? 'selected' : '' ?>>Administradores</option>
                        <option value="tecnico" <?= $filtroPerfil === 'tecnico' ? 'selected' : '' ?>>Técnicos</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-outline-primary">
                        <i class="bi bi-search"></i> Filtrar
                    </button>
                    <?php if ($filtroPerfil): ?>
                        <a href="<?= url('usuarios') ?>" class="btn btn-outline-secondary">
                            <i class="bi bi-x-lg"></i>
                        </a>
                    <?php endif; ?>
                </div>
                <div class="col-md-7 text-end">
                    <span class="badge bg-success"><?= $totalAtivos ?> Ativos</span>
                    <span class="badge bg-secondary"><?= $totalInativos ?> Inativos</span>
                </div>
            </form>
        </div>
    </div>

    <!-- Tabela -->
    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Nome</th>
                        <th>E-mail</th>
                        <th>Telefone</th>
                        <th>Perfil</th>
                        <th>Status</th>
                        <th>Último Acesso</th>
                        <th class="text-end">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($usuarios)): ?>
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">
                                <i class="bi bi-inbox fs-1"></i>
                                <p class="mb-0">Nenhum usuário encontrado</p>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($usuarios as $usuario): ?>
                            <tr>
                                <td>
                                    <strong><?= e($usuario['nome']) ?></strong>
                                    <?php if ((int) $usuario['id'] === getUsuarioId()): ?>
                                        <span class="badge bg-info ms-1">Você</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= e($usuario['email']) ?></td>
                                <td><?= formatPhone($usuario['telefone']) ?></td>
                                <td>
                                    <?php if ($usuario['perfil'] === 'admin'): ?>
                                        <span class="badge bg-danger"><i class="bi bi-shield-fill"></i> Admin</span>
                                    <?php else: ?>
                                        <span class="badge bg-primary"><i class="bi bi-tools"></i> Técnico</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($usuario['ativo']): ?>
                                        <span class="badge bg-success">Ativo</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Inativo</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?= $usuario['ultimo_acesso'] ? formatDateTime($usuario['ultimo_acesso']) : '<span class="text-muted">Nunca</span>' ?>
                                </td>
                                <td class="text-end">
                                    <?php if ((int) $usuario['id'] !== getUsuarioId()): ?>
                                        <a href="<?= url('usuarios/edit/' . $usuario['id']) ?>" class="btn btn-sm btn-outline-primary" title="Editar">
                                            <i class="bi bi-pencil"></i>
                                        </a>

                                        <form method="POST" action="<?= url('usuarios/toggle/' . $usuario['id']) ?>" class="d-inline">
                                            <?= csrfField() ?>
                                            <?php if ($usuario['ativo']): ?>
                                                <button type="submit" class="btn btn-sm btn-outline-warning" title="Desativar" onclick="return confirm('Desativar este usuário?')">
                                                    <i class="bi bi-pause-circle"></i>
                                                </button>
                                            <?php else: ?>
                                                <button type="submit" class="btn btn-sm btn-outline-success" title="Ativar" onclick="return confirm('Ativar este usuário?')">
                                                    <i class="bi bi-play-circle"></i>
                                                </button>
                                            <?php endif; ?>
                                        </form>

                                        <form method="POST" action="<?= url('usuarios/reset-senha/' . $usuario['id']) ?>" class="d-inline">
                                            <?= csrfField() ?>
                                            <button type="submit" class="btn btn-sm btn-outline-info" title="Resetar Senha" onclick="return confirm('Resetar senha para \"proservice123\"?')">
                                                <i class="bi bi-key"></i>
                                            </button>
                                        </form>
                                    <?php else: ?>
                                        <a href="<?= url('perfil') ?>" class="btn btn-sm btn-outline-secondary" title="Editar no Meu Perfil">
                                            <i class="bi bi-person-circle"></i>
                                        </a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Paginação -->
        <?php if ($paginacao['total_pages'] > 1): ?>
            <div class="card-footer">
                <nav>
                    <ul class="pagination justify-content-center mb-0">
                        <?php if ($paginacao['has_previous']): ?>
                            <li class="page-item">
                                <a class="page-link" href="<?= url('usuarios?page=' . ($paginacao['current_page'] - 1) . ($filtroPerfil ? '&perfil=' . $filtroPerfil : '')) ?>">Anterior</a>
                            </li>
                        <?php endif; ?>

                        <?php for ($i = 1; $i <= $paginacao['total_pages']; $i++): ?>
                            <li class="page-item <?= $i === $paginacao['current_page'] ? 'active' : '' ?>">
                                <a class="page-link" href="<?= url('usuarios?page=' . $i . ($filtroPerfil ? '&perfil=' . $filtroPerfil : '')) ?>"><?= $i ?></a>
                            </li>
                        <?php endfor; ?>

                        <?php if ($paginacao['has_next']): ?>
                            <li class="page-item">
                                <a class="page-link" href="<?= url('usuarios?page=' . ($paginacao['current_page'] + 1) . ($filtroPerfil ? '&perfil=' . $filtroPerfil : '')) ?>">Próximo</a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </nav>
            </div>
        <?php endif; ?>
    </div>
</div>
