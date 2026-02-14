<?php

namespace App\Controllers;

use App\Middlewares\AuthMiddleware;

class HelpController extends Controller
{
    /**
     * Dados de todas as funcionalidades do sistema
     */
    private array $funcionalidades = [];

    public function __construct()
    {
        AuthMiddleware::check();
        $this->carregarFuncionalidades();
    }

    /**
     * Página principal de ajuda com busca
     */
    public function index(): void
    {
        $query = trim($_GET['q'] ?? '');
        $categoria = $_GET['categoria'] ?? '';

        $resultados = [];
        if ($query !== '') {
            $resultados = $this->buscar($query, $categoria);
        }

        // Se tem busca, mostrar resultados; senão mostrar todas categorias
        $funcionalidades = empty($resultados) && empty($query) 
            ? $this->funcionalidades 
            : $resultados;

        // Capturar conteúdo da view
        ob_start();
        $this->view('help/index', [
            'titulo' => 'Central de Ajuda - ' . APP_NAME,
            'funcionalidades' => $funcionalidades,
            'query' => $query,
            'categoria' => $categoria,
            'categorias' => $this->getCategorias(),
            'total' => $this->contarTotal($funcionalidades)
        ]);
        $content = ob_get_clean();
        
        // Renderizar com layout
        $this->layout('main', ['titulo' => 'Central de Ajuda - ' . APP_NAME, 'content' => $content]);
    }

    /**
     * Mostra detalhes de uma funcionalidade específica
     */
    public function show(string $slug): void
    {
        $item = $this->encontrarPorSlug($slug);
        
        if (!$item) {
            setFlash('error', 'Funcionalidade não encontrada.');
            $this->redirect('ajuda');
        }

        // Encontrar itens relacionados (mesma categoria)
        $relacionados = array_filter($this->funcionalidades, function($f) use ($item) {
            return $f['categoria'] === $item['categoria'] && $f['slug'] !== $item['slug'];
        });

        // Capturar conteúdo da view
        ob_start();
        $this->view('help/show', [
            'titulo' => $item['titulo'] . ' - Central de Ajuda',
            'item' => $item,
            'relacionados' => array_slice($relacionados, 0, 5)
        ]);
        $content = ob_get_clean();
        
        // Renderizar com layout
        $this->layout('main', ['titulo' => $item['titulo'] . ' - Central de Ajuda', 'content' => $content]);
    }

    /**
     * Busca funcionalidades por termo
     */
    private function buscar(string $query, string $categoria = ''): array
    {
        $resultados = [];
        $queryLower = mb_strtolower($query);

        foreach ($this->funcionalidades as $func) {
            // Filtrar por categoria se especificada
            if ($categoria && $func['categoria'] !== $categoria) {
                continue;
            }

            $score = 0;

            // Pontuação por título (maior peso)
            if (mb_strpos(mb_strtolower($func['titulo']), $queryLower) !== false) {
                $score += 10;
            }

            // Pontuação por descrição
            if (mb_strpos(mb_strtolower($func['descricao']), $queryLower) !== false) {
                $score += 5;
            }

            // Pontuação por tags
            foreach ($func['tags'] as $tag) {
                if (mb_strpos(mb_strtolower($tag), $queryLower) !== false) {
                    $score += 3;
                }
            }

            // Pontuação por conteúdo
            if (mb_strpos(mb_strtolower($func['conteudo']), $queryLower) !== false) {
                $score += 2;
            }

            // Pontuação por passos
            foreach ($func['passos'] as $passo) {
                if (mb_strpos(mb_strtolower($passo), $queryLower) !== false) {
                    $score += 1;
                }
            }

            if ($score > 0) {
                $func['score'] = $score;
                $resultados[] = $func;
            }
        }

        // Ordenar por relevância (score)
        usort($resultados, function($a, $b) {
            return $b['score'] <=> $a['score'];
        });

        return $resultados;
    }

    /**
     * Encontra funcionalidade pelo slug
     */
    private function encontrarPorSlug(string $slug): ?array
    {
        foreach ($this->funcionalidades as $func) {
            if ($func['slug'] === $slug) {
                return $func;
            }
        }
        return null;
    }

    /**
     * Retorna lista de categorias disponíveis
     */
    private function getCategorias(): array
    {
        $categorias = [];
        foreach ($this->funcionalidades as $func) {
            $cat = $func['categoria'];
            if (!isset($categorias[$cat])) {
                $categorias[$cat] = [
                    'id' => $cat,
                    'nome' => $func['categoria_nome'],
                    'icone' => $func['categoria_icone'],
                    'total' => 0
                ];
            }
            $categorias[$cat]['total']++;
        }
        return $categorias;
    }

    /**
     * Conta total de funcionalidades
     */
    private function contarTotal(array $funcs): int
    {
        return count($funcs);
    }

    /**
     * Carrega todas as funcionalidades do sistema
     */
    private function carregarFuncionalidades(): void
    {
        $this->funcionalidades = [
            // ============== DASHBOARD ==============
            [
                'slug' => 'dashboard-visao-geral',
                'titulo' => 'Dashboard - Visão Geral',
                'categoria' => 'dashboard',
                'categoria_nome' => 'Dashboard',
                'categoria_icone' => 'bi-speedometer2',
                'descricao' => 'Painel principal do sistema com visão geral da operação',
                'tags' => ['dashboard', 'início', 'home', 'resumo', 'estatísticas', 'visão geral'],
                'conteudo' => 'O Dashboard é a página inicial do sistema após o login. Ele apresenta uma visão consolidada de todas as operações da sua empresa, incluindo estatísticas de Ordens de Serviço, receitas, despesas e alertas importantes.',
                'passos' => [
                    'Faça login no sistema',
                    'Você será redirecionado automaticamente para o Dashboard',
                    'Visualize os cards de estatísticas (OS em aberto, concluídas, faturamento)',
                    'Confira a lista de OS pendentes e aprovadas',
                    'Acompanhe o gráfico de tendência de OS',
                    'Verifique alertas de estoque baixo e pagamentos pendentes'
                ],
                'dicas' => [
                    'Os dados do dashboard são atualizados em tempo real',
                    'Clique em qualquer card para ver mais detalhes',
                    'Use o dashboard para acompanhar o desempenho diário da empresa'
                ],
                'url' => 'dashboard',
                'perfis' => ['admin', 'tecnico']
            ],

            // ============== ORDENS DE SERVIÇO ==============
            [
                'slug' => 'os-lista',
                'titulo' => 'Ordens de Serviço - Lista',
                'categoria' => 'os',
                'categoria_nome' => 'Ordens de Serviço',
                'categoria_icone' => 'bi-clipboard-data',
                'descricao' => 'Gerencie todas as Ordens de Serviço do sistema',
                'tags' => ['os', 'ordem de serviço', 'lista', 'gerenciar', 'atendimentos'],
                'conteudo' => 'A lista de Ordens de Serviço permite visualizar, filtrar e gerenciar todas as OS cadastradas. Você pode buscar por cliente, status, técnico, período e muito mais.',
                'passos' => [
                    'Acesse o menu "Ordens de Serviço" > "Lista"',
                    'Use os filtros superiores para refinar a busca',
                    'Clique no botão "Nova OS" para criar uma nova ordem',
                    'Clique no ícone de visualização para ver detalhes de uma OS',
                    'Use as ações rápidas para editar, aprovar ou cancelar'
                ],
                'dicas' => [
                    'Use o filtro de status para encontrar OS rapidamente',
                    'Exporte dados usando os botões de impressão/Excel',
                    'Marque OS como prioridade para destacá-las na lista'
                ],
                'url' => 'ordens',
                'perfis' => ['admin', 'tecnico']
            ],
            [
                'slug' => 'os-nova',
                'titulo' => 'Criar Nova Ordem de Serviço',
                'categoria' => 'os',
                'categoria_nome' => 'Ordens de Serviço',
                'categoria_icone' => 'bi-clipboard-data',
                'descricao' => 'Aprenda a criar uma nova Ordem de Serviço no sistema',
                'tags' => ['os', 'nova', 'criar', 'cadastrar', 'atendimento', 'cliente'],
                'conteudo' => 'A criação de uma nova OS envolve o cadastro do cliente, definição do serviço a ser realizado, produtos/equipamentos envolvidos, técnicos responsáveis e prazos de execução.',
                'passos' => [
                    'Clique em "Nova OS" ou acesse o menu Ordens > Nova',
                    'Selecione um cliente existente ou cadastre um novo',
                    'Descreva o problema/serviço a ser realizado',
                    'Adicione equipamentos e produtos necessários',
                    'Defina o técnico responsável e prazo estimado',
                    'Escolha o tipo de garantia',
                    'Clique em "Salvar" para criar a OS'
                ],
                'dicas' => [
                    'Cadastre o cliente antes se ainda não existir',
                    'Use a calculadora de valores para estimar custos',
                    'Adicione fotos do equipamento para registro',
                    'Defina lembretes para follow-up com o cliente'
                ],
                'url' => 'ordens/create',
                'perfis' => ['admin', 'tecnico']
            ],
            [
                'slug' => 'os-calendario',
                'titulo' => 'Calendário de OS',
                'categoria' => 'os',
                'categoria_nome' => 'Ordens de Serviço',
                'categoria_icone' => 'bi-clipboard-data',
                'descricao' => 'Visualize as Ordens de Serviço em formato de calendário',
                'tags' => ['calendário', 'agenda', 'visualização', 'datas', 'planejamento'],
                'conteudo' => 'O calendário de OS permite visualizar todas as ordens de serviço organizadas por data, facilitando o planejamento e a distribuição de trabalho entre os técnicos.',
                'passos' => [
                    'Acesse "Ordens de Serviço" > "Calendário"',
                    'Navegue entre os meses usando as setas',
                    'Clique em um dia para ver as OS agendadas',
                    'Clique em uma OS para ver detalhes',
                    'Arraste OS entre datas para reagendar (se permitido)'
                ],
                'dicas' => [
                    'Use a visualização semanal para detalhes diários',
                    'Filtre por técnico para ver carga de trabalho',
                    'Cores indicam status: verde (concluída), amarela (pendente), vermelha (urgente)'
                ],
                'url' => 'ordens/calendario',
                'perfis' => ['admin', 'tecnico']
            ],

            // ============== CLIENTES ==============
            [
                'slug' => 'clientes-cadastro',
                'titulo' => 'Cadastro de Clientes',
                'categoria' => 'clientes',
                'categoria_nome' => 'Clientes',
                'categoria_icone' => 'bi-people',
                'descricao' => 'Gerencie o cadastro de todos os clientes',
                'tags' => ['clientes', 'cadastro', 'pessoas', 'contatos', 'cliente'],
                'conteudo' => 'O cadastro de clientes armazena informações completas incluindo dados pessoais, endereço, contatos, histórico de atendimentos e documentos.',
                'passos' => [
                    'Acesse o menu "Clientes" > "Lista"',
                    'Clique em "Novo Cliente"',
                    'Preencha dados pessoais (nome, CPF/CNPJ, RG)',
                    'Adicione endereço completo com CEP',
                    'Cadastre telefones e e-mails de contato',
                    'Anexe documentos se necessário',
                    'Clique em "Salvar"'
                ],
                'dicas' => [
                    'Use busca por CPF/CNPJ para evitar duplicados',
                    'Marque cliente como VIP para prioridade no atendimento',
                    'Adicione observações específicas sobre o cliente',
                    'Configure aniversário para envio automático de mensagens'
                ],
                'url' => 'clientes',
                'perfis' => ['admin', 'tecnico']
            ],
            [
                'slug' => 'clientes-detalhes',
                'titulo' => 'Ficha do Cliente',
                'categoria' => 'clientes',
                'categoria_nome' => 'Clientes',
                'categoria_icone' => 'bi-people',
                'descricao' => 'Visualize histórico completo e detalhes do cliente',
                'tags' => ['cliente', 'histórico', 'ficha', 'detalhes', 'atendimentos', 'compras'],
                'conteudo' => 'A ficha do cliente apresenta todas as informações cadastradas, histórico de Ordens de Serviço, orçamentos, comunicações e estatísticas de relacionamento.',
                'passos' => [
                    'Acesse a lista de clientes',
                    'Clique no nome do cliente ou no ícone de visualização',
                    'Confira aba "Dados" para informações cadastrais',
                    'Aba "Histórico" mostra todas as OS do cliente',
                    'Aba "Comunicações" tem ligações e mensagens',
                    'Aba "Estatísticas" mostra valores e frequência'
                ],
                'dicas' => [
                    'Use a timeline para ver cronologia de atendimentos',
                    'Exporte ficha completa em PDF',
                    'Veja sugestões de serviços baseadas no histórico'
                ],
                'url' => 'clientes',
                'perfis' => ['admin', 'tecnico']
            ],

            // ============== FINANCEIRO ==============
            [
                'slug' => 'financeiro-lancamentos',
                'titulo' => 'Lançamentos Financeiros',
                'categoria' => 'financeiro',
                'categoria_nome' => 'Financeiro',
                'categoria_icone' => 'bi-cash-stack',
                'descricao' => 'Gerencie receitas e despesas do sistema',
                'tags' => ['financeiro', 'receitas', 'despesas', 'lançamentos', 'caixa', 'fluxo'],
                'conteudo' => 'O módulo financeiro controla todas as entradas (receitas de OS) e saídas (despesas operacionais) da empresa, permitindo acompanhamento do fluxo de caixa.',
                'passos' => [
                    'Acesse "Financeiro" > "Lançamentos"',
                    'Filtre por período, tipo ou categoria',
                    'Clique em "Nova Receita" ou "Nova Despesa"',
                    'Preencha valor, data, categoria e descrição',
                    'Vincule a uma OS se for receita de atendimento',
                    'Anexe comprovantes ou notas fiscais',
                    'Confirme o lançamento'
                ],
                'dicas' => [
                    'Categorize corretamente para relatórios precisos',
                    'Use centro de custo para controle por departamento',
                    'Configure alertas de despesas acima do orçamento'
                ],
                'url' => 'financeiro',
                'perfis' => ['admin']
            ],
            [
                'slug' => 'financeiro-fluxo-caixa',
                'titulo' => 'Fluxo de Caixa',
                'categoria' => 'financeiro',
                'categoria_nome' => 'Financeiro',
                'categoria_icone' => 'bi-cash-stack',
                'descricao' => 'Acompanhe entradas, saídas e saldo em tempo real',
                'tags' => ['fluxo de caixa', 'caixa', 'saldo', 'entradas', 'saídas', 'projeção'],
                'conteudo' => 'O fluxo de caixa mostra movimentações diárias com saldo acumulado, permitindo projeções financeiras e identificação de gargalos de liquidez.',
                'passos' => [
                    'Acesse "Financeiro" > "Fluxo de Caixa"',
                    'Selecione período de análise',
                    'Visualize gráfico de entradas vs saídas',
                    'Confira tabela detalhada dia a dia',
                    'Exporte para Excel se necessário'
                ],
                'dicas' => [
                    'Monitore saldo projetado para evitar descontrole',
                    'Identifique padrões sazonais de receita',
                    'Use para planejamento de investimentos'
                ],
                'url' => 'financeiro/fluxo',
                'perfis' => ['admin']
            ],
            [
                'slug' => 'financeiro-receitas',
                'titulo' => 'Receitas - Contas a Receber',
                'categoria' => 'financeiro',
                'categoria_nome' => 'Financeiro',
                'categoria_icone' => 'bi-cash-stack',
                'descricao' => 'Gerencie todas as receitas e contas a receber',
                'tags' => ['receitas', 'contas a receber', 'faturamento', 'cobrança', 'pagamentos'],
                'conteudo' => 'Controle de todas as receitas da empresa, incluindo OS concluídas, vendas e outros recebimentos. Acompanhamento de contas a receber e inadimplência.',
                'passos' => [
                    'Acesse "Financeiro" > "Receitas"',
                    'Visualize receitas pendentes e recebidas',
                    'Clique em "Receber" para baixar um título',
                    'Registre forma de pagamento e data',
                    'Gere recibo se necessário'
                ],
                'dicas' => [
                    'Envie lembretes automáticos de pagamento',
                    'Configure multa e juros por atraso',
                    'Integre com boletos bancários'
                ],
                'url' => 'financeiro/receitas',
                'perfis' => ['admin']
            ],
            [
                'slug' => 'financeiro-despesas',
                'titulo' => 'Despesas - Contas a Pagar',
                'categoria' => 'financeiro',
                'categoria_nome' => 'Financeiro',
                'categoria_icone' => 'bi-cash-stack',
                'descricao' => 'Controle de despesas e contas a pagar',
                'tags' => ['despesas', 'contas a pagar', 'pagamentos', 'compras', 'custos'],
                'conteudo' => 'Gestão completa de despesas operacionais, compras, pagamentos a fornecedores e contas recorrentes da empresa.',
                'passos' => [
                    'Acesse "Financeiro" > "Despesas"',
                    'Cadastre nova despesa com fornecedor',
                    'Defina data de vencimento e valor',
                    'Anexe nota fiscal ou comprovante',
                    'Baixe pagamento quando efetivado'
                ],
                'dicas' => [
                    'Cadastre despesas recorrentes (aluguel, energia)',
                    'Configure alertas de vencimento',
                    'Analise relatório de despesas por categoria'
                ],
                'url' => 'financeiro/despesas',
                'perfis' => ['admin']
            ],

            // ============== RELATÓRIOS ==============
            [
                'slug' => 'relatorios-financeiros',
                'titulo' => 'Relatórios Financeiros',
                'categoria' => 'relatorios',
                'categoria_nome' => 'Relatórios',
                'categoria_icone' => 'bi-graph-up',
                'descricao' => 'Relatórios detalhados de receitas, despesas e lucratividade',
                'tags' => ['relatórios', 'financeiro', 'receitas', 'despesas', 'lucro', 'dre'],
                'conteudo' => 'Relatórios financeiros completos com análise de receitas, despesas, lucratividade por período, DRE simplificada e projeções.',
                'passos' => [
                    'Acesse "Relatórios" > "Financeiro"',
                    'Selecione período desejado',
                    'Escolha tipo de relatório (resumo, detalhado, gráfico)',
                    'Aplique filtros de categoria se necessário',
                    'Visualize na tela ou exporte para PDF/Excel'
                ],
                'dicas' => [
                    'Compare períodos para análise de crescimento',
                    'Use gráficos para apresentações',
                    'Exporte DRE para contador'
                ],
                'url' => 'relatorios/financeiro',
                'perfis' => ['admin']
            ],
            [
                'slug' => 'relatorios-os',
                'titulo' => 'Relatórios de OS',
                'categoria' => 'relatorios',
                'categoria_nome' => 'Relatórios',
                'categoria_icone' => 'bi-graph-up',
                'descricao' => 'Análise de produtividade e desempenho de OS',
                'tags' => ['relatórios', 'os', 'produtividade', 'técnicos', 'performance', 'kpi'],
                'conteudo' => 'Relatórios de performance das Ordens de Serviço, incluindo tempo médio de atendimento, taxa de aprovação, produtividade por técnico e satisfação.',
                'passos' => [
                    'Acesse "Relatórios" > "Ordens de Serviço"',
                    'Filtre por período e técnico',
                    'Visualize métricas principais (cards superiores)',
                    'Analise gráficos de produtividade',
                    'Verifique ranking de técnicos',
                    'Exporte relatório completo'
                ],
                'dicas' => [
                    'Identifique técnicos com melhor performance',
                    'Analise gargalos no tempo de atendimento',
                    'Monitore taxa de reabertura de OS'
                ],
                'url' => 'relatorios/os',
                'perfis' => ['admin', 'tecnico']
            ],
            [
                'slug' => 'relatorios-avancados',
                'titulo' => 'Dashboard Avançado',
                'categoria' => 'relatorios',
                'categoria_nome' => 'Relatórios',
                'categoria_icone' => 'bi-graph-up',
                'descricao' => 'Dashboard executivo com KPIs e análises avançadas',
                'tags' => ['dashboard', 'avançado', 'kpi', 'indicadores', 'executivo', 'analise'],
                'conteudo' => 'Dashboard executivo com indicadores-chave (KPIs), análise de clientes, tendências, comparativos e gráficos avançados para gestão estratégica.',
                'passos' => [
                    'Acesse "Relatórios" > "Dashboard Avançado"',
                    'Visualize KPIs principais no topo',
                    'Analise gráficos de tendência',
                    'Confira lista de top clientes',
                    'Verifique status de clientes (ativos/novos/perdidos)',
                    'Use filtros para análise específica'
                ],
                'dicas' => [
                    'Use para reuniões de gestão',
                    'Monitore taxa de retenção de clientes',
                    'Identifique oportunidades de crescimento'
                ],
                'url' => 'relatorios/avancados',
                'perfis' => ['admin']
            ],
            [
                'slug' => 'relatorios-estoque',
                'titulo' => 'Relatório de Estoque',
                'categoria' => 'relatorios',
                'categoria_nome' => 'Relatórios',
                'categoria_icone' => 'bi-graph-up',
                'descricao' => 'Controle de movimentação e saldo de estoque',
                'tags' => ['estoque', 'produtos', 'relatório', 'movimentação', 'saldo', 'inventário'],
                'conteudo' => 'Relatórios de posição de estoque, movimentações de entrada/saída, produtos abaixo do mínimo, curva ABC e valorização de inventário.',
                'passos' => [
                    'Acesse "Relatórios" > "Estoque"',
                    'Selecione tipo de relatório (posição, movimentação, crítico)',
                    'Aplique filtros de categoria ou local',
                    'Visualize alertas de estoque baixo',
                    'Exporte para contagem física'
                ],
                'dicas' => [
                    'Configure níveis mínimos para alertas automáticos',
                    'Use curva ABC para priorizar gestão',
                    'Monitore produtos sem movimentação'
                ],
                'url' => 'relatorios/estoque',
                'perfis' => ['admin']
            ],

            // ============== ESTOQUE ==============
            [
                'slug' => 'estoque-produtos',
                'titulo' => 'Cadastro de Produtos',
                'categoria' => 'estoque',
                'categoria_nome' => 'Estoque',
                'categoria_icone' => 'bi-box-seam',
                'descricao' => 'Gerencie produtos, serviços e equipamentos',
                'tags' => ['estoque', 'produtos', 'serviços', 'cadastro', 'itens', 'peças'],
                'conteudo' => 'Cadastro completo de produtos para venda, serviços prestados e equipamentos utilizados nas OS. Controle de preços, custos e estoque.',
                'passos' => [
                    'Acesse "Estoque" > "Produtos"',
                    'Clique em "Novo Produto"',
                    'Defina tipo (produto, serviço, equipamento)',
                    'Preencha nome, código, categoria',
                    'Cadastre preço de venda e custo',
                    'Defina quantidade em estoque (se aplicável)',
                    'Adicione fornecedor e código de barras'
                ],
                'dicas' => [
                    'Use códigos de barras para agilizar atendimento',
                    'Cadastre kits de produtos/serviços',
                    'Configure produtos com composição (matéria-prima)'
                ],
                'url' => 'produtos',
                'perfis' => ['admin', 'tecnico']
            ],
            [
                'slug' => 'estoque-movimentacao',
                'titulo' => 'Movimentação de Estoque',
                'categoria' => 'estoque',
                'categoria_nome' => 'Estoque',
                'categoria_icone' => 'bi-box-seam',
                'descricao' => 'Registre entradas, saídas e ajustes de estoque',
                'tags' => ['estoque', 'movimentação', 'entrada', 'saída', 'ajuste', 'inventário'],
                'conteudo' => 'Controle todas as entradas (compras), saídas (consumo em OS/vendas) e ajustes (inventário, perdas) de estoque.',
                'passos' => [
                    'Acesse "Estoque" > "Movimentações"',
                    'Escolha tipo: Entrada, Saída ou Ajuste',
                    'Selecione produto',
                    'Informe quantidade e motivo',
                    'Para entradas, registre fornecedor e nota fiscal',
                    'Para saídas, vincule à OS se aplicável',
                    'Confirme a movimentação'
                ],
                'dicas' => [
                    'Movimentações são registradas automaticamente ao consumir em OS',
                    'Faça ajustes periódicos após inventário físico',
                    'Rastreie lotes e validades quando necessário'
                ],
                'url' => 'estoque/movimentacoes',
                'perfis' => ['admin']
            ],

            // ============== USUÁRIOS E PERFIL ==============
            [
                'slug' => 'usuarios-gestao',
                'titulo' => 'Gestão de Usuários (Admin)',
                'categoria' => 'usuarios',
                'categoria_nome' => 'Usuários',
                'categoria_icone' => 'bi-person-gear',
                'descricao' => 'Administre técnicos e administradores do sistema',
                'tags' => ['usuários', 'admin', 'técnicos', 'gestão', 'acesso', 'permissões'],
                'conteudo' => 'Módulo exclusivo de administração para criar, editar e gerenciar acessos de todos os usuários do sistema (técnicos e administradores).',
                'passos' => [
                    'Acesse "Administração" > "Usuários"',
                    'Visualize lista de todos os usuários',
                    'Clique em "Novo Usuário" para cadastrar',
                    'Defina nome, e-mail, telefone',
                    'Escolha perfil: Admin ou Técnico',
                    'Defina senha inicial',
                    'Use ações para editar, ativar/desativar ou resetar senha'
                ],
                'dicas' => [
                    'Desative usuários ao invés de excluir para manter histórico',
                    'Resete senha para proservice123 quando necessário',
                    'Monitore último acesso de cada usuário'
                ],
                'url' => 'usuarios',
                'perfis' => ['admin']
            ],
            [
                'slug' => 'perfil-usuario',
                'titulo' => 'Meu Perfil',
                'categoria' => 'usuarios',
                'categoria_nome' => 'Usuários',
                'categoria_icone' => 'bi-person-gear',
                'descricao' => 'Gerencie seus dados pessoais e senha',
                'tags' => ['perfil', 'dados pessoais', 'senha', 'minha conta', 'configurações'],
                'conteudo' => 'Área para cada usuário atualizar seus dados pessoais (nome, e-mail, telefone) e alterar sua senha de acesso ao sistema.',
                'passos' => [
                    'Clique em "Meu Perfil" no menu lateral inferior',
                    'Atualize nome, e-mail ou telefone',
                    'Para alterar senha, preencha senha atual e nova senha',
                    'Confirme nova senha',
                    'Clique em "Salvar Dados" ou "Alterar Senha"'
                ],
                'dicas' => [
                    'Use senha forte (mínimo 8 caracteres)',
                    'Mantenha e-mail atualizado para recuperação',
                    'Adicione foto de perfil (se disponível)'
                ],
                'url' => 'perfil',
                'perfis' => ['admin', 'tecnico']
            ],
            [
                'slug' => 'recuperacao-senha',
                'titulo' => 'Recuperação de Senha por E-mail',
                'categoria' => 'usuarios',
                'categoria_nome' => 'Usuários',
                'categoria_icone' => 'bi-person-gear',
                'descricao' => 'Recupere acesso ao sistema via e-mail',
                'tags' => ['senha', 'recuperação', 'esqueci senha', 'acesso', 'login', 'e-mail'],
                'conteudo' => 'Processo de recuperação de senha enviando link seguro por e-mail. Requer configuração de SMTP nas configurações da empresa.',
                'passos' => [
                    'Na tela de login, clique em "Esqueci minha senha"',
                    'Digite o e-mail cadastrado',
                    'Clique em "Enviar instruções"',
                    'Verifique sua caixa de e-mail',
                    'Clique no link de recuperação recebido',
                    'Defina nova senha (mínimo 8 caracteres)',
                    'Confirme e faça login'
                ],
                'dicas' => [
                    'O link expira em 1 hora por segurança',
                    'Verifique pasta de spam se não receber',
                    'Configure SMTP em Configurações > Comunicação'
                ],
                'url' => 'forgot-password',
                'perfis' => ['admin', 'tecnico']
            ],

            // ============== CONFIGURAÇÕES ==============
            [
                'slug' => 'config-empresa',
                'titulo' => 'Configurações - Dados da Empresa',
                'categoria' => 'configuracoes',
                'categoria_nome' => 'Configurações',
                'categoria_icone' => 'bi-gear',
                'descricao' => 'Configure dados cadastrais e informações da empresa',
                'tags' => ['configurações', 'empresa', 'dados', 'cadastro', 'cnpj', 'logo'],
                'conteudo' => 'Cadastro de informações da empresa que aparecerão nos documentos, contratos e comunicações. Inclui logo, endereço, dados fiscais.',
                'passos' => [
                    'Acesse "Administração" > "Configurações"',
                    'Preencha razão social, nome fantasia',
                    'Cadastre CNPJ e inscrição estadual',
                    'Adicione endereço completo',
                    'Configure telefones e e-mails',
                    'Faça upload da logomarca',
                    'Salve as alterações'
                ],
                'dicas' => [
                    'Use logo em alta resolução para melhor qualidade',
                    'Mantenha dados fiscais atualizados para notas',
                    'Estas informações aparecem em relatórios e contratos'
                ],
                'url' => 'configuracoes',
                'perfis' => ['admin']
            ],
            [
                'slug' => 'config-aparencia',
                'titulo' => 'Configurações - Aparência',
                'categoria' => 'configuracoes',
                'categoria_nome' => 'Configurações',
                'categoria_icone' => 'bi-gear',
                'descricao' => 'Personalize cores e identidade visual do sistema',
                'tags' => ['configurações', 'aparência', 'cores', 'tema', 'logo', 'identidade visual'],
                'conteudo' => 'Personalização visual do sistema com cores da marca, upload de favicon e ajustes de identidade visual para clientes.',
                'passos' => [
                    'Acesse "Administração" > "Configurações" > "Aparência"',
                    'Escolha cor primária (botões principais)',
                    'Defina cor secundária (elementos de destaque)',
                    'Configure cor de fundo e texto',
                    'Faça upload do favicon (ícone do navegador)',
                    'Visualize prévia antes de salvar'
                ],
                'dicas' => [
                    'Use cores que combinem com sua marca',
                    'Mantenha contraste adequado para legibilidade',
                    'O favicon deve ser quadrado (recomendado 32x32px)'
                ],
                'url' => 'configuracoes/aparencia',
                'perfis' => ['admin']
            ],
            [
                'slug' => 'config-comunicacao',
                'titulo' => 'Configurações - Comunicação e SMTP',
                'categoria' => 'configuracoes',
                'categoria_nome' => 'Configurações',
                'categoria_icone' => 'bi-gear',
                'descricao' => 'Configure envio de e-mails e integrações',
                'tags' => ['configurações', 'smtp', 'e-mail', 'comunicação', 'notificações', 'integração'],
                'conteudo' => 'Configuração de servidor SMTP para envio de e-mails transacionais (recuperação de senha, notificações). Suporte a Gmail, Outlook e servidores personalizados.',
                'passos' => [
                    'Acesse "Administração" > "Configurações" > "Comunicação"',
                    'Ative "Usar SMTP"',
                    'Selecione provedor (Gmail, Outlook, Outro)',
                    'Configure Host (ex: smtp.gmail.com)',
                    'Defina Porta (587 para TLS, 465 para SSL)',
                    'Digite Usuário (e-mail) e Senha',
                    'Configure e-mail de envio e nome exibido',
                    'Teste enviando e-mail de teste'
                ],
                'dicas' => [
                    'Para Gmail, use "Senha de App" (não a senha normal)',
                    'Porta 587 com TLS é mais compatível',
                    'Guarde backup das configurações',
                    'Verifique logs de e-mail em caso de falha'
                ],
                'url' => 'configuracoes/comunicacao',
                'perfis' => ['admin']
            ],
            [
                'slug' => 'config-contrato',
                'titulo' => 'Configurações - Modelo de Contrato',
                'categoria' => 'configuracoes',
                'categoria_nome' => 'Configurações',
                'categoria_icone' => 'bi-gear',
                'descricao' => 'Crie e edite modelo de contrato para OS',
                'tags' => ['configurações', 'contrato', 'termos', 'modelo', 'documento', 'termo de serviço'],
                'conteudo' => 'Editor de modelo de contrato/termo de serviço usado nas Ordens de Serviço. Suporta variáveis dinâmicas que são substituídas automaticamente.',
                'passos' => [
                    'Acesse "Administração" > "Configurações" > "Contrato"',
                    'Edite o cabeçalho do documento',
                    'Configure cláusulas padrão',
                    'Use variáveis: {{cliente_nome}}, {{os_numero}}, {{data}}',
                    'Defina termos de garantia',
                    'Configure assinatura digital',
                    'Salve modelo e visualize prévia'
                ],
                'dicas' => [
                    'Consulte jurídico antes de usar contratos',
                    'Use variáveis para automatizar preenchimento',
                    'Inclua termos de responsabilidade e garantia',
                    'Teste geração de contrato em uma OS'
                ],
                'url' => 'configuracoes/contrato',
                'perfis' => ['admin']
            ],
            [
                'slug' => 'config-planos',
                'titulo' => 'Configurações - Planos e Assinatura',
                'categoria' => 'configuracoes',
                'categoria_nome' => 'Configurações',
                'categoria_icone' => 'bi-gear',
                'descricao' => 'Gerencie plano de assinatura e limites',
                'tags' => ['configurações', 'planos', 'assinatura', 'pagamento', 'limites', 'trial'],
                'conteudo' => 'Gerenciamento de plano de assinatura, acompanhamento de uso, upgrade/downgrade e controle de limites de usuários e OS.',
                'passos' => [
                    'Acesse "Administração" > "Configurações" > "Planos"',
                    'Visualize plano atual e limites',
                    'Acompanhe uso de OS do mês',
                    'Veja data de renovação/vencimento',
                    'Clique em "Alterar Plano" para upgrade',
                    'Visualize histórico de pagamentos'
                ],
                'dicas' => [
                    'Monitore aproximação de limites',
                    'Faça upgrade antes de atingir limites',
                    'Aproveite período de trial para testar'
                ],
                'url' => 'configuracoes/planos',
                'perfis' => ['admin']
            ],
            [
                'slug' => 'config-backup',
                'titulo' => 'Configurações - Backup e Exportação',
                'categoria' => 'configuracoes',
                'categoria_nome' => 'Configurações',
                'categoria_icone' => 'bi-gear',
                'descricao' => 'Exporte dados e configure backups',
                'tags' => ['configurações', 'backup', 'exportar', 'dados', 'importar', 'segurança'],
                'conteudo' => 'Ferramentas para exportação de dados em diversos formatos e configuração de backups automáticos ou manuais.',
                'passos' => [
                    'Acesse "Administração" > "Configurações" > "Backup"',
                    'Escolha tipo de exportação (clientes, OS, financeiro)',
                    'Selecione formato (Excel, CSV, PDF)',
                    'Defina período de dados',
                    'Clique em "Exportar"',
                    'Para backup completo, use "Exportar Tudo"'
                ],
                'dicas' => [
                    'Faça backups mensais de segurança',
                    'Verifique integridade dos arquivos exportados',
                    'Armazene backups em local seguro externo',
                    'Documento de backup inclui timestamp'
                ],
                'url' => 'configuracoes/backup',
                'perfis' => ['admin']
            ],

            // ============== LOGS E AUDITORIA ==============
            [
                'slug' => 'logs-sistema',
                'titulo' => 'Logs do Sistema e Auditoria',
                'categoria' => 'logs',
                'categoria_nome' => 'Logs',
                'categoria_icone' => 'bi-journal-text',
                'descricao' => 'Visualize logs de atividades e auditoria do sistema',
                'tags' => ['logs', 'auditoria', 'registros', 'atividades', 'histórico', 'rastreamento'],
                'conteudo' => 'Registro completo de todas as ações realizadas no sistema: logins, alterações de dados, criações, exclusões e eventos de segurança.',
                'passos' => [
                    'Acesse "Administração" > "Logs do Sistema"',
                    'Visualize lista cronológica de eventos',
                    'Filtre por data, usuário, módulo ou nível',
                    'Clique em um log para ver detalhes completos',
                    'Exporte logs para análise externa',
                    'Limpe logs antigos periodicamente'
                ],
                'dicas' => [
                    'Monitore falhas de login suspeitas',
                    'Use logs para investigar problemas',
                    'Configure retenção automática de logs',
                    'Logs são essenciais para compliance'
                ],
                'url' => 'logs',
                'perfis' => ['admin']
            ],

            // ============== CONTRATOS E DOCUMENTOS ==============
            [
                'slug' => 'contrato-gerar',
                'titulo' => 'Gerar Contrato de OS',
                'categoria' => 'contratos',
                'categoria_nome' => 'Contratos',
                'categoria_icone' => 'bi-file-text',
                'descricao' => 'Gere contratos e termos de serviço para OS',
                'tags' => ['contrato', 'termo', 'documento', 'os', 'assinatura', 'pdf'],
                'conteudo' => 'Geração de contratos personalizados baseados no modelo configurado, com dados da OS e cliente preenchidos automaticamente.',
                'passos' => [
                    'Abra uma OS em visualização',
                    'Clique em "Gerar Contrato"',
                    'Verifique dados preenchidos automaticamente',
                    'Edite se necessário campos específicos',
                    'Visualize prévia antes de confirmar',
                    'Gere PDF ou envie por e-mail',
                    'Registre assinatura do cliente'
                ],
                'dicas' => [
                    'Configure modelo de contrato antes de usar',
                    'Verifique dados do cliente estão completos',
                    'Salve contrato gerado no histórico',
                    'Use assinatura digital quando possível'
                ],
                'url' => 'configuracoes/contrato',
                'perfis' => ['admin', 'tecnico']
            ],

            // ============== COMUNICAÇÃO ==============
            [
                'slug' => 'comunicacao-ligacao',
                'titulo' => 'Registrar Ligação Telefônica',
                'categoria' => 'comunicacao',
                'categoria_nome' => 'Comunicação',
                'categoria_icone' => 'bi-telephone',
                'descricao' => 'Registre histórico de ligações com clientes',
                'tags' => ['comunicação', 'ligação', 'telefone', 'histórico', 'atendimento', 'follow-up'],
                'conteudo' => 'Registro de todas as ligações telefônicas com clientes para histórico de atendimento e follow-up de Ordens de Serviço.',
                'passos' => [
                    'Acesse "Comunicações" > "Ligações" ou pela OS',
                    'Clique em "Nova Ligação"',
                    'Selecione cliente',
                    'Escolha tipo: Recebida, Efetuada ou Não Atendida',
                    'Registre duração da chamada',
                    'Descreva assunto e resumo da conversa',
                    'Vincule a uma OS se relacionada',
                    'Marque para retorno se necessário'
                ],
                'dicas' => [
                    'Registre todas as ligações para histórico completo',
                    'Use campo "Retornar em" para follow-up',
                    'Registre ligações não atendidas também',
                    'Anexe gravação se disponível'
                ],
                'url' => 'comunicacoes',
                'perfis' => ['admin', 'tecnico']
            ],
            [
                'slug' => 'comunicacao-mensagem',
                'titulo' => 'Enviar Mensagens (WhatsApp/E-mail)',
                'categoria' => 'comunicacao',
                'categoria_nome' => 'Comunicação',
                'categoria_icone' => 'bi-telephone',
                'descricao' => 'Envie mensagens automáticas para clientes',
                'tags' => ['comunicação', 'mensagem', 'whatsapp', 'e-mail', 'notificação', 'sms'],
                'conteudo' => 'Envio de mensagens de status de OS, orçamentos aprovados, conclusões de serviço via WhatsApp, e-mail ou SMS.',
                'passos' => [
                    'Acesse "Comunicações" > "Mensagens"',
                    'Selecione template ou crie mensagem',
                    'Escolha cliente ou grupo',
                    'Selecione canal (WhatsApp, E-mail, SMS)',
                    'Personalize mensagem com variáveis',
                    'Visualize prévia',
                    'Confirme envio'
                ],
                'dicas' => [
                    'Use templates para agilizar envio',
                    'Respeite horários comerciais (9h às 18h)',
                    'Configure mensagens automáticas em Configurações',
                    'Monitore taxa de entrega e leitura'
                ],
                'url' => 'comunicacoes/mensagens',
                'perfis' => ['admin', 'tecnico']
            ],

            // ============== SUPORTE ==============
            [
                'slug' => 'suporte-faq',
                'titulo' => 'Perguntas Frequentes (FAQ)',
                'categoria' => 'suporte',
                'categoria_nome' => 'Suporte',
                'categoria_icone' => 'bi-question-circle',
                'descricao' => 'Respostas para dúvidas comuns',
                'tags' => ['suporte', 'faq', 'dúvidas', 'perguntas', 'ajuda', 'como fazer'],
                'conteudo' => 'Respostas rápidas para as dúvidas mais frequentes dos usuários sobre operação do sistema.',
                'passos' => [
                    'Use a busca no topo desta página',
                    'Navegue por categoria',
                    'Leia passo a passo completo',
                    'Consulte dicas adicionais'
                ],
                'dicas' => [
                    'Mantenha este guia sempre aberto para consulta',
                    'Use Ctrl+F para buscar rápido na página',
                    'Entre em contato se não encontrar resposta'
                ],
                'url' => 'ajuda',
                'perfis' => ['admin', 'tecnico']
            ],
            [
                'slug' => 'suporte-contato',
                'titulo' => 'Contato e Suporte Técnico',
                'categoria' => 'suporte',
                'categoria_nome' => 'Suporte',
                'categoria_icone' => 'bi-question-circle',
                'descricao' => 'Canais de atendimento e suporte',
                'tags' => ['suporte', 'contato', 'ajuda', 'atendimento', 'ticket', 'assistência'],
                'conteudo' => 'Canais oficiais de suporte técnico para dúvidas, problemas, sugestões e solicitações de melhorias.',
                'passos' => [
                    'E-mail: suporte@proservice.com.br',
                    'WhatsApp: (11) 99999-9999',
                    'Horário: Segunda a Sexta, 9h às 18h',
                    'Descreva problema detalhadamente',
                    'Anexe prints da tela se necessário',
                    'Informe empresa e usuário'
                ],
                'dicas' => [
                    'Descreva passos para reproduzir problema',
                    'Informe navegador e versão',
                    'Verifique se problema persiste após atualizar página',
                    'Consulte logs do sistema antes de reportar'
                ],
                'url' => 'ajuda',
                'perfis' => ['admin', 'tecnico']
            ]
        ];
    }
}
