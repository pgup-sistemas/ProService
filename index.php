<?php
/**
 * proService - Ponto de entrada principal
 * Arquivo: /index.php
 */

// Definir constante root
define('PROSERVICE_ROOT', __DIR__);

// Carregar autoload e configurações
require_once PROSERVICE_ROOT . '/app/config/config.php';
require_once PROSERVICE_ROOT . '/app/config/helpers.php';
require_once PROSERVICE_ROOT . '/app/config/Router.php';
require_once PROSERVICE_ROOT . '/app/config/Database.php';

// Carregar controllers
require_once PROSERVICE_ROOT . '/app/controllers/Controller.php';
require_once PROSERVICE_ROOT . '/app/controllers/AuthController.php';
require_once PROSERVICE_ROOT . '/app/controllers/DashboardController.php';
require_once PROSERVICE_ROOT . '/app/controllers/ClienteController.php';
require_once PROSERVICE_ROOT . '/app/controllers/ProdutoController.php';
require_once PROSERVICE_ROOT . '/app/controllers/ServicoController.php';
require_once PROSERVICE_ROOT . '/app/controllers/TecnicoController.php';
require_once PROSERVICE_ROOT . '/app/controllers/OrdemServicoController.php';
require_once PROSERVICE_ROOT . '/app/controllers/ReciboController.php';
require_once PROSERVICE_ROOT . '/app/controllers/FinanceiroController.php';
require_once PROSERVICE_ROOT . '/app/controllers/PublicoController.php';
require_once PROSERVICE_ROOT . '/app/controllers/RelatorioController.php';
require_once PROSERVICE_ROOT . '/app/controllers/RelatorioAvancadosController.php';
require_once PROSERVICE_ROOT . '/app/controllers/ConfiguracaoController.php';
require_once PROSERVICE_ROOT . '/app/controllers/PerfilController.php';
require_once PROSERVICE_ROOT . '/app/controllers/UsuarioController.php';
require_once PROSERVICE_ROOT . '/app/controllers/LogController.php';
require_once PROSERVICE_ROOT . '/app/controllers/HelpController.php';
require_once PROSERVICE_ROOT . '/app/controllers/AssinaturaController.php';
require_once PROSERVICE_ROOT . '/app/controllers/ArquivoController.php';

// Carregar services
require_once PROSERVICE_ROOT . '/app/services/EmailService.php';
require_once PROSERVICE_ROOT . '/app/services/NotificationService.php';
require_once PROSERVICE_ROOT . '/app/services/EfiPayService.php';

// Carregar middlewares
require_once PROSERVICE_ROOT . '/app/middlewares/AuthMiddleware.php';
require_once PROSERVICE_ROOT . '/app/middlewares/PlanoMiddleware.php';

// Carregar models
require_once PROSERVICE_ROOT . '/app/models/Model.php';
require_once PROSERVICE_ROOT . '/app/models/Empresa.php';
require_once PROSERVICE_ROOT . '/app/models/Usuario.php';
require_once PROSERVICE_ROOT . '/app/models/Cliente.php';
require_once PROSERVICE_ROOT . '/app/models/Produto.php';
require_once PROSERVICE_ROOT . '/app/models/Servico.php';
require_once PROSERVICE_ROOT . '/app/models/OrdemServico.php';
require_once PROSERVICE_ROOT . '/app/models/OsLog.php';
require_once PROSERVICE_ROOT . '/app/models/Receita.php';
require_once PROSERVICE_ROOT . '/app/models/Despesa.php';
require_once PROSERVICE_ROOT . '/app/models/Recibo.php';
require_once PROSERVICE_ROOT . '/app/models/Comunicacao.php';
require_once PROSERVICE_ROOT . '/app/models/Configuracao.php';
require_once PROSERVICE_ROOT . '/app/models/Parcela.php';
require_once PROSERVICE_ROOT . '/app/models/Log.php';
require_once PROSERVICE_ROOT . '/app/models/LogSistema.php';
require_once PROSERVICE_ROOT . '/app/models/Assinatura.php';

// Iniciar sessão
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Criar router
use App\Config\Router;

$router = new Router();

// ============================================
// ROTAS PÚBLICAS (SEM AUTENTICAÇÃO)
// ============================================

// Autenticação
$router->get('login', ['AuthController', 'login']);
$router->post('login', ['AuthController', 'doLogin']);
$router->get('register', ['AuthController', 'register']);
$router->post('register', ['AuthController', 'doRegister']);
$router->get('termos', ['AuthController', 'termos']);
$router->get('privacidade', ['AuthController', 'privacidade']);
$router->get('logout', ['AuthController', 'logout']);
$router->get('forgot-password', ['AuthController', 'forgotPassword']);
$router->post('forgot-password', ['AuthController', 'doForgotPassword']);
$router->get('reset-password', ['AuthController', 'resetPassword']);
$router->post('do-reset-password', ['AuthController', 'doResetPassword']);

// Acompanhamento público de OS
$router->get('acompanhar/{token}', ['PublicoController', 'acompanhar']);

// ============================================
// ROTAS PROTEGIDAS (REQUER AUTENTICAÇÃO)
// ============================================

// Dashboard
$router->get('dashboard', ['DashboardController', 'index'], 'AuthMiddleware');
$router->get('', ['DashboardController', 'index'], 'AuthMiddleware');

// Meu Perfil
$router->get('perfil', ['PerfilController', 'index'], 'AuthMiddleware');
$router->post('perfil', ['PerfilController', 'update'], 'AuthMiddleware');
$router->post('perfil/senha', ['PerfilController', 'senha'], 'AuthMiddleware');

// Clientes
$router->get('clientes', ['ClienteController', 'index'], 'AuthMiddleware');
$router->get('clientes/create', ['ClienteController', 'create'], 'AuthMiddleware');
$router->post('clientes/create', ['ClienteController', 'store'], 'AuthMiddleware');
$router->get('clientes/edit/{id}', ['ClienteController', 'edit'], 'AuthMiddleware');
$router->post('clientes/edit/{id}', ['ClienteController', 'update'], 'AuthMiddleware');
$router->post('clientes/delete/{id}', ['ClienteController', 'delete'], 'AuthMiddleware');
$router->get('api/clientes/buscar', ['ClienteController', 'buscar'], 'AuthMiddleware');
$router->get('api/clientes/{id}', ['ClienteController', 'show'], 'AuthMiddleware');

// Produtos
$router->get('produtos', ['ProdutoController', 'index'], 'AuthMiddleware');
$router->get('produtos/create', ['ProdutoController', 'create'], 'AuthMiddleware');
$router->post('produtos/create', ['ProdutoController', 'store'], 'AuthMiddleware');
$router->get('produtos/edit/{id}', ['ProdutoController', 'edit'], 'AuthMiddleware');
$router->post('produtos/edit/{id}', ['ProdutoController', 'update'], 'AuthMiddleware');
$router->post('produtos/delete/{id}', ['ProdutoController', 'delete'], 'AuthMiddleware');
$router->post('produtos/entrada/{id}', ['ProdutoController', 'entrada'], 'AuthMiddleware');
$router->get('api/produtos/buscar', ['ProdutoController', 'buscar'], 'AuthMiddleware');

// Import / Export de Produtos (CSV)
$router->get('produtos/export', ['ProdutoController', 'export'], 'AuthMiddleware');
$router->post('produtos/import/preview', ['ProdutoController', 'importPreview'], 'AuthMiddleware');
$router->post('produtos/import', ['ProdutoController', 'import'], 'AuthMiddleware');

// Serviços
$router->get('servicos', ['ServicoController', 'index'], 'AuthMiddleware');
$router->get('servicos/create', ['ServicoController', 'create'], 'AuthMiddleware');
$router->post('servicos/create', ['ServicoController', 'store'], 'AuthMiddleware');
$router->get('servicos/edit/{id}', ['ServicoController', 'edit'], 'AuthMiddleware');
$router->post('servicos/edit/{id}', ['ServicoController', 'update'], 'AuthMiddleware');
$router->post('servicos/delete/{id}', ['ServicoController', 'delete'], 'AuthMiddleware');
$router->post('servicos/duplicar/{id}', ['ServicoController', 'duplicar'], 'AuthMiddleware');
$router->get('api/servicos/buscar', ['ServicoController', 'buscar'], 'AuthMiddleware');
$router->get('api/servicos/{id}', ['ServicoController', 'show'], 'AuthMiddleware');

// Técnicos (Admin)
$router->get('tecnicos', ['TecnicoController', 'index'], 'AuthMiddleware');
$router->get('tecnicos/create', ['TecnicoController', 'create'], 'AuthMiddleware');
$router->post('tecnicos/create', ['TecnicoController', 'store'], 'AuthMiddleware');
$router->get('tecnicos/edit/{id}', ['TecnicoController', 'edit'], 'AuthMiddleware');
$router->post('tecnicos/edit/{id}', ['TecnicoController', 'update'], 'AuthMiddleware');
$router->post('tecnicos/toggle/{id}', ['TecnicoController', 'toggle'], 'AuthMiddleware');
$router->post('tecnicos/reset-senha/{id}', ['TecnicoController', 'resetSenha'], 'AuthMiddleware');

// Gestão de Usuários (Admin)
$router->get('usuarios', ['UsuarioController', 'index'], 'AuthMiddleware');
$router->get('usuarios/create', ['UsuarioController', 'create'], 'AuthMiddleware');
$router->post('usuarios/create', ['UsuarioController', 'store'], 'AuthMiddleware');
$router->get('usuarios/edit/{id}', ['UsuarioController', 'edit'], 'AuthMiddleware');
$router->post('usuarios/edit/{id}', ['UsuarioController', 'update'], 'AuthMiddleware');
$router->post('usuarios/toggle/{id}', ['UsuarioController', 'toggle'], 'AuthMiddleware');
$router->post('usuarios/reset-senha/{id}', ['UsuarioController', 'resetSenha'], 'AuthMiddleware');

// Recibos
$router->get('recibos', ['ReciboController', 'index'], 'AuthMiddleware');
$router->get('recibos/show/{id}', ['ReciboController', 'show'], 'AuthMiddleware');

// Ordens de Serviço
$router->get('ordens', ['OrdemServicoController', 'index'], 'AuthMiddleware');
$router->get('ordens/calendario', ['OrdemServicoController', 'calendario'], 'AuthMiddleware');
$router->get('ordens/create', ['OrdemServicoController', 'create'], 'AuthMiddleware');
$router->post('ordens/create', ['OrdemServicoController', 'store'], 'AuthMiddleware');
$router->get('ordens/show/{id}', ['OrdemServicoController', 'show'], 'AuthMiddleware');
$router->get('ordens/edit/{id}', ['OrdemServicoController', 'edit'], 'AuthMiddleware');
$router->post('ordens/update/{id}', ['OrdemServicoController', 'update'], 'AuthMiddleware');
$router->post('ordens/destroy/{id}', ['OrdemServicoController', 'destroy'], 'AuthMiddleware');
$router->post('ordens/status/{id}', ['OrdemServicoController', 'status'], 'AuthMiddleware');
$router->get('ordens/assinatura/{id}', ['OrdemServicoController', 'assinatura'], 'AuthMiddleware');
$router->post('ordens/assinatura/{id}', ['OrdemServicoController', 'salvarAssinatura'], 'AuthMiddleware');
$router->get('ordens/whatsapp/{id}', ['OrdemServicoController', 'registrarWhatsApp'], 'AuthMiddleware');
$router->get('ordens/recibo/{osId}', ['OrdemServicoController', 'verRecibo'], 'AuthMiddleware');
$router->post('ordens/foto/{id}', ['OrdemServicoController', 'uploadFoto'], 'AuthMiddleware');
$router->post('ordens/foto/{osId}/remover/{fotoId}', ['OrdemServicoController', 'removerFoto'], 'AuthMiddleware');
$router->post('ordens/produto/{id}', ['OrdemServicoController', 'adicionarProduto'], 'AuthMiddleware');
$router->post('ordens/produto/{osId}/remover/{produtoOsId}', ['OrdemServicoController', 'removerProduto'], 'AuthMiddleware');
$router->post('ordens/gerar-token/{id}', ['PublicoController', 'gerarToken'], 'AuthMiddleware');

// Financeiro
$router->get('financeiro', ['FinanceiroController', 'index'], 'AuthMiddleware');
$router->get('financeiro/receitas', ['FinanceiroController', 'receitas'], 'AuthMiddleware');
$router->post('financeiro/receitas/{id}/receber', ['FinanceiroController', 'receber'], 'AuthMiddleware');
$router->post('financeiro/receitas/{id}/parcelas', ['FinanceiroController', 'gerarParcelas'], 'AuthMiddleware');
$router->get('financeiro/despesas', ['FinanceiroController', 'despesas'], 'AuthMiddleware');
$router->post('financeiro/despesas/{id}/pagar', ['FinanceiroController', 'pagar'], 'AuthMiddleware');
$router->get('financeiro/despesas/create', ['FinanceiroController', 'createDespesa'], 'AuthMiddleware');
$router->post('financeiro/despesas/create', ['FinanceiroController', 'storeDespesa'], 'AuthMiddleware');
$router->get('financeiro/parcelas', ['FinanceiroController', 'parcelas'], 'AuthMiddleware');
$router->post('financeiro/parcelas/{id}/pagar', ['FinanceiroController', 'pagarParcela'], 'AuthMiddleware');

// Relatórios
$router->get('relatorios', ['RelatorioController', 'index'], 'AuthMiddleware');
$router->get('relatorios/servicos', ['RelatorioController', 'servicos'], 'AuthMiddleware');
$router->get('relatorios/financeiro', ['RelatorioController', 'financeiro'], 'AuthMiddleware');
$router->get('relatorios/estoque', ['RelatorioController', 'estoque'], 'AuthMiddleware');
$router->get('relatorios/tecnicos', ['RelatorioController', 'tecnicos'], 'AuthMiddleware');
$router->get('relatorios/despesas', ['RelatorioController', 'despesas'], 'AuthMiddleware');
$router->get('relatorios/exportar', ['RelatorioController', 'exportar'], 'AuthMiddleware');
$router->get('relatorios/imprimir', ['RelatorioController', 'imprimir'], 'AuthMiddleware');
$router->get('relatorios/avancados', ['RelatorioAvancadosController', 'index'], 'AuthMiddleware');

// Configurações
$router->get('configuracoes', ['ConfiguracaoController', 'index'], 'AuthMiddleware');
$router->get('configuracoes/empresa', ['ConfiguracaoController', 'empresa'], 'AuthMiddleware');
$router->post('configuracoes/empresa', ['ConfiguracaoController', 'empresa'], 'AuthMiddleware');
$router->get('configuracoes/remover-logo', ['ConfiguracaoController', 'removerLogo'], 'AuthMiddleware');
$router->get('configuracoes/aparencia', ['ConfiguracaoController', 'aparencia'], 'AuthMiddleware');
$router->post('configuracoes/aparencia', ['ConfiguracaoController', 'aparencia'], 'AuthMiddleware');
$router->get('configuracoes/comunicacao', ['ConfiguracaoController', 'comunicacao'], 'AuthMiddleware');
$router->post('configuracoes/comunicacao', ['ConfiguracaoController', 'comunicacao'], 'AuthMiddleware');
$router->post('configuracoes/testar-email', ['ConfiguracaoController', 'testarEmail'], 'AuthMiddleware');
$router->get('configuracoes/contrato', ['ConfiguracaoController', 'contrato'], 'AuthMiddleware');
$router->post('configuracoes/contrato', ['ConfiguracaoController', 'contrato'], 'AuthMiddleware');
$router->get('configuracoes/preview-contrato', ['ConfiguracaoController', 'previewContrato'], 'AuthMiddleware');
$router->get('configuracoes/gerar-contrato/{id}', ['ConfiguracaoController', 'gerarContratoOS'], 'AuthMiddleware');
$router->get('configuracoes/plano', ['ConfiguracaoController', 'plano'], 'AuthMiddleware');
$router->post('configuracoes/plano', ['ConfiguracaoController', 'plano'], 'AuthMiddleware');
$router->get('configuracoes/backup', ['ConfiguracaoController', 'backup'], 'AuthMiddleware');

// Logs do Sistema (Admin)
$router->get('logs', ['LogController', 'index'], 'AuthMiddleware');
$router->post('logs/limpar', ['LogController', 'limpar'], 'AuthMiddleware');

// Central de Ajuda
$router->get('ajuda', ['HelpController', 'index'], 'AuthMiddleware');
$router->get('ajuda/{slug}', ['HelpController', 'show'], 'AuthMiddleware');

// Assinaturas e Pagamentos (EfiPay)
$router->get('assinaturas', ['AssinaturaController', 'index'], 'AuthMiddleware');
$router->get('assinaturas/efipay-checkout/{planoId}', ['AssinaturaController', 'efipayCheckout'], 'AuthMiddleware');
$router->get('assinaturas/retorno', ['AssinaturaController', 'retorno'], 'AuthMiddleware');
$router->get('assinaturas/gerenciar', ['AssinaturaController', 'gerenciar'], 'AuthMiddleware');
$router->post('assinaturas/cancelar', ['AssinaturaController', 'cancelar'], 'AuthMiddleware');

// Webhook EfiPay (público - vem da API)
$router->post('webhook/efipay', ['AssinaturaController', 'webhook']);

// API - Gerar link WhatsApp
$router->get('api/whatsapp/gerar-link', ['ConfiguracaoController', 'gerarLinkWhatsApp'], 'AuthMiddleware');

// API - Busca Global (Ctrl+K)
$router->get('api/busca', ['DashboardController', 'busca'], 'AuthMiddleware');

// API - Onboarding
$router->post('api/onboarding/pular', ['DashboardController', 'onboardingPular'], 'AuthMiddleware');
$router->post('api/onboarding/finalizar', ['DashboardController', 'onboardingFinalizar'], 'AuthMiddleware');

// Arquivos protegidos (uploads sensíveis)
$router->get('files/assinatura/{id}', ['ArquivoController', 'assinatura'], 'AuthMiddleware');
$router->get('files/comprovante/{id}', ['ArquivoController', 'comprovante'], 'AuthMiddleware');

// Executar roteamento
$router->dispatch();
