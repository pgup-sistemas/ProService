<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($titulo ?? APP_NAME) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        :root {
            --primary-color: #1e40af;
            --success-color: #059669;
            --warning-color: #ea580c;
        }
        
        body {
            background-color: #f8f9fa;
            min-height: 100vh;
        }
        
        .sidebar {
            background: linear-gradient(135deg, #1e40af 0%, #059669 100%);
            min-height: 100vh;
            color: white;
        }
        
        .sidebar .nav-link {
            color: rgba(255,255,255,0.9);
            padding: 12px 20px;
            border-radius: 8px;
            margin-bottom: 4px;
            transition: all 0.3s;
        }
        
        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            background: rgba(255,255,255,0.15);
            color: white;
        }
        
        .sidebar .nav-link i {
            width: 24px;
            text-align: center;
            margin-right: 8px;
        }
        
        .main-content {
            padding: 20px;
        }
        
        .card {
            border: none;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            border-radius: 12px;
        }
        
        .card-header {
            background: white;
            border-bottom: 1px solid #eee;
            padding: 16px 20px;
            border-radius: 12px 12px 0 0 !important;
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        .btn-success {
            background-color: var(--success-color);
            border-color: var(--success-color);
        }
        
        .stat-card {
            padding: 20px;
            border-radius: 12px;
            color: white;
            position: relative;
            overflow: hidden;
        }
        
        .stat-card::after {
            content: '';
            position: absolute;
            top: -20px;
            right: -20px;
            width: 80px;
            height: 80px;
            background: rgba(255,255,255,0.1);
            border-radius: 50%;
        }
        
        .stat-card.blue { background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%); }
        .stat-card.green { background: linear-gradient(135deg, #059669 0%, #10b981 100%); }
        .stat-card.orange { background: linear-gradient(135deg, #ea580c 0%, #f97316 100%); }
        .stat-card.red { background: linear-gradient(135deg, #dc2626 0%, #ef4444 100%); }
        .stat-card.gray { background: linear-gradient(135deg, #475569 0%, #64748b 100%); }
        
        .stat-card h3 {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 4px;
        }
        
        .stat-card p {
            opacity: 0.9;
            margin: 0;
        }
        
        .stat-card i {
            font-size: 2rem;
            opacity: 0.3;
            position: absolute;
            right: 20px;
            bottom: 20px;
        }
        
        .table th {
            font-weight: 600;
            color: #6b7280;
            border-top: none;
            background: #f9fafb;
        }
        
        .badge {
            padding: 6px 12px;
            font-weight: 500;
        }
        
        .alert {
            border: none;
            border-radius: 10px;
        }
        
        .form-control, .form-select {
            border-radius: 8px;
            border-color: #e5e7eb;
        }
        
        .form-control:focus, .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(30, 64, 175, 0.15);
        }
        
        .navbar-mobile {
            display: none;
        }
        
        /* Global Search Styles */
        .search-modal .modal-content {
            background: transparent;
            border: none;
        }
        .search-modal .modal-body {
            padding: 0;
        }
        .search-box {
            background: white;
            border-radius: 12px;
            box-shadow: 0 20px 50px rgba(0,0,0,0.3);
            overflow: hidden;
        }
        .search-input {
            border: none;
            padding: 20px 25px;
            font-size: 1.2rem;
            outline: none;
        }
        .search-input:focus {
            box-shadow: none;
        }
        .search-shortcut {
            background: #e9ecef;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.75rem;
            color: #6c757d;
        }
        .search-results {
            max-height: 400px;
            overflow-y: auto;
        }
        .search-result-item {
            padding: 12px 25px;
            border-bottom: 1px solid #f0f0f0;
            cursor: pointer;
            transition: background 0.2s;
        }
        .search-result-item:hover,
        .search-result-item.active {
            background: #f8f9fa;
        }
        .search-result-item i {
            width: 24px;
            text-align: center;
            margin-right: 12px;
            color: #6c757d;
        }
        
        @media (max-width: 768px) {
            .sidebar {
                display: none;
            }
            
            .sidebar.mobile-open {
                display: block;
                position: fixed;
                z-index: 1000;
                width: 260px;
            }
            
            .navbar-mobile {
                display: flex;
                background: linear-gradient(135deg, #1e40af 0%, #059669 100%);
                padding: 12px 16px;
                color: white;
                align-items: center;
            }
            
            .main-content {
                padding: 16px;
            }
        }
    </style>
</head>
<body>
    <!-- Navbar Mobile -->
    <nav class="navbar-mobile">
        <button class="btn btn-link text-white" onclick="toggleSidebar()">
            <i class="bi bi-list fs-4"></i>
        </button>
        <span class="fw-bold">⚡ProService</span>
        <div class="dropdown ms-auto">
            <button class="btn btn-link text-white dropdown-toggle" data-bs-toggle="dropdown">
                <i class="bi bi-person-circle fs-5"></i>
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
                <li><a class="dropdown-item" href="<?= url('logout') ?>"><i class="bi bi-box-arrow-right"></i> Sair</a></li>
            </ul>
        </div>
    </nav>

    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <nav class="col-md-3 col-lg-2 sidebar p-0" id="sidebar">
                <div class="p-4">
                    <h4 class="mb-4 fw-bold">⚡ProService</h4>
                    
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link <?= str_contains($_SERVER['REQUEST_URI'], 'dashboard') ? 'active' : '' ?>" href="<?= url('dashboard') ?>">
                                <i class="bi bi-house"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= str_contains($_SERVER['REQUEST_URI'], 'ordens') ? 'active' : '' ?>" href="#" data-bs-toggle="collapse" data-bs-target="#menu-ordens">
                                <i class="bi bi-clipboard-data"></i> Ordens de Serviço
                            </a>
                            <div class="collapse <?= str_contains($_SERVER['REQUEST_URI'], 'ordens') ? 'show' : '' ?>" id="menu-ordens">
                                <ul class="nav flex-column ps-4">
                                    <li class="nav-item">
                                        <a class="nav-link py-1" href="<?= url('ordens') ?>">
                                            <small>Lista de OS</small>
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link py-1 <?= str_contains($_SERVER['REQUEST_URI'], 'calendario') ? 'active' : '' ?>" href="<?= url('ordens/calendario') ?>">
                                            <small><i class="bi bi-calendar3"></i> Calendário</small>
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= str_contains($_SERVER['REQUEST_URI'], 'clientes') ? 'active' : '' ?>" href="<?= url('clientes') ?>">
                                <i class="bi bi-people"></i> Clientes
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= str_contains($_SERVER['REQUEST_URI'], 'produtos') ? 'active' : '' ?>" href="<?= url('produtos') ?>">
                                <i class="bi bi-box-seam"></i> Produtos / Estoque
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= str_contains($_SERVER['REQUEST_URI'], 'servicos') ? 'active' : '' ?>" href="<?= url('servicos') ?>">
                                <i class="bi bi-tools"></i> Serviços
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= str_contains($_SERVER['REQUEST_URI'], 'financeiro') ? 'active' : '' ?>" href="<?= url('financeiro') ?>">
                                <i class="bi bi-cash-stack"></i> Financeiro
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= str_contains($_SERVER['REQUEST_URI'], 'recibos') ? 'active' : '' ?>" href="<?= url('recibos') ?>">
                                <i class="bi bi-receipt"></i> Recibos
                            </a>
                        </li>
                        
                        <?php if (isAdmin()): ?>
                        <li class="nav-item mt-3">
                            <small class="text-white-50 px-3">ADMINISTRAÇÃO</small>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= str_contains($_SERVER['REQUEST_URI'], 'usuarios') ? 'active' : '' ?>" href="<?= url('usuarios') ?>">
                                <i class="bi bi-people-fill"></i> Usuários
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= str_contains($_SERVER['REQUEST_URI'], 'tecnicos') ? 'active' : '' ?>" href="<?= url('tecnicos') ?>">
                                <i class="bi bi-person-badge"></i> Técnicos
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= str_contains($_SERVER['REQUEST_URI'], 'relatorios') ? 'active' : '' ?>" href="#" data-bs-toggle="collapse" data-bs-target="#menu-relatorios">
                                <i class="bi bi-graph-up"></i> Relatórios
                            </a>
                            <div class="collapse <?= str_contains($_SERVER['REQUEST_URI'], 'relatorios') ? 'show' : '' ?>" id="menu-relatorios">
                                <ul class="nav flex-column ps-4">
                                    <li class="nav-item">
                                        <a class="nav-link py-1" href="<?= url('relatorios') ?>">
                                            <small>Relatórios Gerais</small>
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link py-1 <?= str_contains($_SERVER['REQUEST_URI'], 'avancados') ? 'active' : '' ?>" href="<?= url('relatorios/avancados') ?>">
                                            <small><i class="bi bi-graph-up-arrow"></i> Dashboard Avançado</small>
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= str_contains($_SERVER['REQUEST_URI'], 'configuracoes') ? 'active' : '' ?>" href="<?= url('configuracoes') ?>">
                                <i class="bi bi-gear"></i> Configurações
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= str_contains($_SERVER['REQUEST_URI'], 'logs') ? 'active' : '' ?>" href="<?= url('logs') ?>">
                                <i class="bi bi-journal-text"></i> Logs do Sistema
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= str_contains($_SERVER['REQUEST_URI'], 'assinaturas') ? 'active' : '' ?>" href="<?= url('assinaturas') ?>">
                                <i class="bi bi-credit-card"></i> Assinatura
                            </a>
                        </li>
                        <?php endif; ?>
                        
                        <li class="nav-item mt-3">
                            <a class="nav-link <?= str_contains($_SERVER['REQUEST_URI'], 'ajuda') ? 'active' : '' ?>" href="<?= url('ajuda') ?>">
                                <i class="bi bi-question-circle"></i> Central de Ajuda
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= str_contains($_SERVER['REQUEST_URI'], 'perfil') ? 'active' : '' ?>" href="<?= url('perfil') ?>">
                                <i class="bi bi-person-circle"></i> Meu Perfil
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= url('logout') ?>">
                                <i class="bi bi-box-arrow-right"></i> Sair
                            </a>
                        </li>
                    </ul>
                </div>
                
                <!-- Info do usuário -->
                <div class="mt-auto p-4 border-top border-white-20">
                    <small class="text-white-50">Logado como</small>
                    <div class="fw-medium"><?= e(getUsuarioNome()) ?></div>
                    <small class="text-white-50"><?= e(getEmpresaDados()['nome_fantasia'] ?? '') ?></small>
                </div>
            </nav>

            <!-- Main Content -->
            <main class="col-md-9 col-lg-10 main-content">
                <?php if ($flash = getFlash()): ?>
                    <div class="alert alert-<?= ($flash['type'] ?? '') === 'error' ? 'danger' : e($flash['type'] ?? 'info') ?> alert-dismissible fade show" role="alert">
                        <?= e($flash['message'] ?? '') ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>
                <?= $content ?>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Global Search Modal -->
    <div class="modal fade search-modal" id="globalSearchModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-body">
                    <div class="search-box">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-search text-muted ms-3"></i>
                            <input type="text" class="form-control search-input" id="searchInput" placeholder="Buscar OS, cliente, produto..." autocomplete="off">
                            <span class="search-shortcut me-3">ESC</span>
                        </div>
                        <div class="search-results" id="searchResults"></div>
                        <div class="p-2 bg-light border-top text-muted small">
                            <div class="d-flex justify-content-between">
                                <span><i class="bi bi-arrow-up-down"></i> Navegar</span>
                                <span><i class="bi bi-return"></i> Selecionar</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Global Search
        let searchModal = new bootstrap.Modal(document.getElementById('globalSearchModal'));
        let searchInput = document.getElementById('searchInput');
        let searchResults = document.getElementById('searchResults');
        let selectedIndex = -1;
        let results = [];

        // Abrir com Ctrl+K
        document.addEventListener('keydown', function(e) {
            if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
                e.preventDefault();
                searchModal.show();
                setTimeout(() => searchInput.focus(), 100);
            }
            if (e.key === 'Escape' && searchModal._isShown) {
                searchModal.hide();
            }
        });

        // Busca em tempo real
        let searchTimeout;
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => performSearch(this.value), 300);
        });

        // Navegação com teclado
        searchInput.addEventListener('keydown', function(e) {
            if (e.key === 'ArrowDown') {
                e.preventDefault();
                selectedIndex = Math.min(selectedIndex + 1, results.length - 1);
                updateSelection();
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                selectedIndex = Math.max(selectedIndex - 1, -1);
                updateSelection();
            } else if (e.key === 'Enter' && selectedIndex >= 0) {
                e.preventDefault();
                window.location.href = results[selectedIndex].url;
            }
        });

        function performSearch(query) {
            if (query.length < 2) {
                searchResults.innerHTML = '';
                return;
            }

            fetch('<?= url('api/busca?q=') ?>' + encodeURIComponent(query))
                .then(response => response.json())
                .then(data => {
                    results = data.results || [];
                    selectedIndex = -1;
                    renderResults();
                })
                .catch(error => console.error('Erro na busca:', error));
        }

        function renderResults() {
            if (results.length === 0) {
                searchResults.innerHTML = '<div class="p-4 text-center text-muted">Nenhum resultado encontrado</div>';
                return;
            }

            let html = '';
            results.forEach((item, index) => {
                html += `
                    <div class="search-result-item ${index === selectedIndex ? 'active' : ''}" data-index="${index}" data-url="${item.url}">
                        <div class="d-flex align-items-center">
                            <i class="bi ${item.icon}"></i>
                            <div>
                                <div class="fw-medium">${item.title}</div>
                                <small class="text-muted">${item.subtitle}</small>
                            </div>
                        </div>
                    </div>
                `;
            });
            searchResults.innerHTML = html;

            // Click handlers
            document.querySelectorAll('.search-result-item').forEach(item => {
                item.addEventListener('click', function() {
                    window.location.href = this.dataset.url;
                });
            });
        }

        function updateSelection() {
            document.querySelectorAll('.search-result-item').forEach((el, i) => {
                el.classList.toggle('active', i === selectedIndex);
            });
        }

        // Limpar ao fechar
        document.getElementById('globalSearchModal').addEventListener('hidden.bs.modal', function() {
            searchInput.value = '';
            searchResults.innerHTML = '';
            selectedIndex = -1;
        });

        function toggleSidebar() {
            document.getElementById('sidebar').classList.toggle('mobile-open');
        }

        // Formatação automática de campos monetários (formato brasileiro)
        document.addEventListener('DOMContentLoaded', function() {
            // Seleciona todos os campos de valor monetário
            const moneyInputs = document.querySelectorAll('input[name="custo_unitario"], input[name="preco_venda"], input[name="valor_padrao"], input[name="valor"], input[name="quantidade_estoque"], input[name="quantidade_minima"], input[name="custo_unitario_entrada"], input[name="valor_servico"], input[name="taxas_adicionais"], input[name="desconto"]');
            
            moneyInputs.forEach(function(input) {
                // Formata ao perder o foco (blur)
                input.addEventListener('blur', function() {
                    let value = this.value.trim();
                    if (value === '') {
                        this.value = '0,00';
                        return;
                    }
                    
                    // Remove caracteres não numéricos exceto vírgula e ponto
                    value = value.replace(/[^\d.,]/g, '');
                    
                    // Se tem vírgula, assume formato brasileiro
                    if (value.includes(',')) {
                        // Remove pontos (milhar) e mantém vírgula (decimal)
                        value = value.replace(/\./g, '');
                    } else {
                        // Se não tem vírgula, é número inteiro ou formato US
                        // Converte para formato brasileiro
                        value = parseFloat(value.replace(/\./g, '').replace(',', '.')) || 0;
                        value = value.toFixed(2).replace('.', ',');
                    }
                    
                    // Formata com separadores de milhar
                    let parts = value.split(',');
                    let integerPart = parts[0];
                    let decimalPart = parts[1] || '00';
                    
                    // Adiciona pontos de milhar
                    integerPart = integerPart.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
                    
                    this.value = integerPart + ',' + decimalPart;
                });
                
                // Limpa ao ganhar foco se for zero
                input.addEventListener('focus', function() {
                    if (this.value === '0,00' || this.value === '0.00') {
                        this.value = '';
                    }
                });
            });
        });
    </script>

    <!-- Footer Profissional -->
    <footer class="bg-light border-top py-3 mt-auto">
        <div class="container-fluid px-4">
            <div class="row align-items-center text-center text-md-start">
                <div class="col-md-4 mb-2 mb-md-0">
                    <small class="text-muted">
                        <i class="bi bi-c-circle me-1"></i>2026 <?= APP_NAME ?> <span class="badge bg-secondary">v<?= APP_VERSION ?></span>
                    </small>
                </div>
                <div class="col-md-4 mb-2 mb-md-0 text-md-center">
                    <small class="text-muted">
                        Desenvolvido por <strong class="text-primary">Pageup Sistemas</strong>
                    </small>
                </div>
                <div class="col-md-4 text-md-end">
                    <a href="https://wa.me/5569993882222" target="_blank" class="text-success text-decoration-none" title="Suporte via WhatsApp - (69) 99388-2222">
                        <i class="bi bi-whatsapp fs-6"></i>
                        <small class="ms-1 d-none d-md-inline">(69) 99388-2222</small>
                    </a>
                </div>
            </div>
        </div>
    </footer>
</body>
</html>
