<?php
/**
 * proService - ConfiguracoesController
 * Arquivo: /app/controllers/ConfiguracoesController.php
 * 
 * Implementa configurações da empresa conforme especificação Seção 17:
 * - Dados da empresa
 * - Identidade visual
 * - Modelos de documentos
 * - Configurações de comunicação
 * - Notificações
 * - Preferências do sistema
 * - Gestão de plano
 */

namespace App\Controllers;

use App\Models\Empresa;
use App\Models\Usuario;
use App\Models\OrdemServico;

class ConfiguracoesController extends Controller
{
    private Empresa $empresaModel;
    private Usuario $usuarioModel;

    public function __construct()
    {
        $this->empresaModel = new Empresa();
        $this->usuarioModel = new Usuario();
    }

    /**
     * Página principal de configurações
     */
    public function index(): void
    {
        $empresaId = getEmpresaId();
        $empresa = $this->empresaModel->findById($empresaId);
        $configuracoes = $this->empresaModel->getConfiguracoes($empresaId);
        
        $this->layout('main', [
            'titulo' => 'Configurações',
            'content' => $this->renderView('configuracoes/index', [
                'empresa' => $empresa,
                'configuracoes' => $configuracoes,
                'activeTab' => $_GET['tab'] ?? 'empresa'
            ])
        ]);
    }

    /**
     * Salvar dados da empresa
     */
    public function salvarEmpresa(): void
    {
        if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
            setFlash('error', 'Token de segurança inválido.');
            $this->redirect('configuracoes');
        }

        $empresaId = getEmpresaId();
        
        error_log('DEBUG salvarEmpresa - Iniciando atualização da empresa ID: ' . $empresaId);
        error_log('DEBUG salvarEmpresa - POST data: ' . json_encode($_POST));
        error_log('DEBUG salvarEmpresa - FILES data: ' . json_encode($_FILES));
        
        $data = [
            'nome_fantasia' => sanitizeInput($_POST['nome_fantasia'] ?? ''),
            'razao_social' => sanitizeInput($_POST['razao_social'] ?? ''),
            'cnpj_cpf' => sanitizeInput($_POST['cnpj_cpf'] ?? ''),
            'telefone' => sanitizeInput($_POST['telefone'] ?? ''),
            'whatsapp' => sanitizeInput($_POST['whatsapp'] ?? ''),
            'email' => sanitizeInput($_POST['email'] ?? ''),
            'cep' => sanitizeInput($_POST['cep'] ?? ''),
            'endereco' => sanitizeInput($_POST['endereco'] ?? ''),
            'numero' => sanitizeInput($_POST['numero'] ?? ''),
            'complemento' => sanitizeInput($_POST['complemento'] ?? ''),
            'bairro' => sanitizeInput($_POST['bairro'] ?? ''),
            'cidade' => sanitizeInput($_POST['cidade'] ?? ''),
            'estado' => sanitizeInput($_POST['estado'] ?? ''),
            // Dados bancários
            'banco_nome' => sanitizeInput($_POST['banco_nome'] ?? ''),
            'banco_agencia' => sanitizeInput($_POST['banco_agencia'] ?? ''),
            'banco_conta' => sanitizeInput($_POST['banco_conta'] ?? ''),
            'banco_tipo' => sanitizeInput($_POST['banco_tipo'] ?? 'corrente'),
            'chave_pix' => sanitizeInput($_POST['chave_pix'] ?? '')
        ];

        // Upload de logo
        if (!empty($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
            error_log('DEBUG salvarEmpresa - Tentando upload de logo');
            $logoPath = $this->uploadLogo($_FILES['logo'], $empresaId);
            if ($logoPath) {
                $data['logo'] = $logoPath;
                error_log('DEBUG salvarEmpresa - Logo salvo em: ' . $logoPath);
            } else {
                error_log('DEBUG salvarEmpresa - Upload de logo falhou');
            }
        } else if (!empty($_FILES['logo']) && $_FILES['logo']['error'] !== UPLOAD_ERR_NO_FILE) {
            error_log('DEBUG salvarEmpresa - Erro no upload: ' . $_FILES['logo']['error']);
        }

        error_log('DEBUG salvarEmpresa - Dados para update: ' . json_encode($data));
        
        $result = $this->empresaModel->update($empresaId, $data);
        error_log('DEBUG salvarEmpresa - Resultado do update: ' . ($result ? 'SUCESSO' : 'FALHA'));
        
        if ($result) {
            setFlash('success', 'Dados da empresa atualizados com sucesso!');
        } else {
            setFlash('error', 'Erro ao atualizar dados da empresa. Verifique os logs de erro.');
        }
        
        $this->redirect('configuracoes?tab=empresa');
    }

    /**
     * Salvar configurações de documentos (contrato, termos)
     */
    public function salvarDocumentos(): void
    {
        if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
            setFlash('error', 'Token de segurança inválido.');
            $this->redirect('configuracoes');
        }

        $empresaId = getEmpresaId();
        
        $data = [
            'termos_contrato' => $_POST['termos_contrato'] ?? '',
            'template_contrato' => $_POST['template_contrato'] ?? $this->getTemplateContratoPadrao()
        ];

        if ($this->empresaModel->update($empresaId, $data)) {
            setFlash('success', 'Modelos de documentos atualizados!');
        } else {
            setFlash('error', 'Erro ao salvar documentos.');
        }
        
        $this->redirect('configuracoes?tab=documentos');
    }

    /**
     * Salvar configurações de comunicação (WhatsApp, templates)
     */
    public function salvarComunicacao(): void
    {
        if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
            setFlash('error', 'Token de segurança inválido.');
            $this->redirect('configuracoes');
        }

        $empresaId = getEmpresaId();
        
        $data = [
            'mensagem_whatsapp_os_criada' => $_POST['mensagem_whatsapp_os_criada'] ?? '',
            'mensagem_whatsapp_os_finalizada' => $_POST['mensagem_whatsapp_os_finalizada'] ?? '',
            'mensagem_whatsapp_recibo' => $_POST['mensagem_whatsapp_recibo'] ?? '',
            'enviar_notificacoes_auto' => isset($_POST['enviar_notificacoes_auto']) ? 1 : 0
        ];

        if ($this->empresaModel->updateConfiguracoes($empresaId, $data)) {
            setFlash('success', 'Configurações de comunicação salvas!');
        } else {
            setFlash('error', 'Erro ao salvar configurações.');
        }
        
        $this->redirect('configuracoes?tab=comunicacao');
    }

    /**
     * Salvar preferências do sistema
     */
    public function salvarPreferencias(): void
    {
        if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
            setFlash('error', 'Token de segurança inválido.');
            $this->redirect('configuracoes');
        }

        $empresaId = getEmpresaId();
        
        $data = [
            'cor_primaria' => sanitizeInput($_POST['cor_primaria'] ?? '#1e40af'),
            'cor_sucesso' => sanitizeInput($_POST['cor_sucesso'] ?? '#059669'),
            'cor_alerta' => sanitizeInput($_POST['cor_alerta'] ?? '#ea580c')
        ];

        if ($this->empresaModel->updateConfiguracoes($empresaId, $data)) {
            setFlash('success', 'Preferências do sistema salvas!');
        } else {
            setFlash('error', 'Erro ao salvar preferências.');
        }
        
        $this->redirect('configuracoes?tab=preferencias');
    }

    /**
     * Página de gestão de plano
     */
    public function plano(): void
    {
        $empresaId = getEmpresaId();
        $empresa = $this->empresaModel->findById($empresaId);
        
        // Instanciar modelo de OS para contagem real
        $osModel = new OrdemServico();

        // Calcular uso REAL
        $usoOS = $osModel->countMesAtual();
        $limiteOS = $empresa['limite_os_mes'] ?? 20;
        $percentualOS = $limiteOS > 0 ? ($usoOS / $limiteOS) * 100 : 0;
        
        // Contar técnicos ativos
        $totalTecnicos = $this->usuarioModel->countTecnicos($empresaId);
        $limiteTecnicos = $empresa['limite_tecnicos'] ?? 1;
        
        // Dias restantes trial
        $diasTrial = 0;
        if (($empresa['plano'] === 'trial' || (empty($empresa['plano']) && !empty($empresa['data_fim_trial']))) && !empty($empresa['data_fim_trial'])) {
            $diasTrial = max(0, (strtotime($empresa['data_fim_trial']) - time()) / 86400);
            $diasTrial = (int) ceil($diasTrial); // Arredonda para cima para mostrar dia completo
        }
        
        $this->layout('main', [
            'titulo' => 'Meu Plano',
            'content' => $this->renderView('configuracoes/plano', [
                'empresa' => $empresa,
                'usoOS' => $usoOS,
                'limiteOS' => $limiteOS,
                'percentualOS' => $percentualOS,
                'totalTecnicos' => $totalTecnicos,
                'limiteTecnicos' => $limiteTecnicos,
                'diasTrial' => $diasTrial
            ])
        ]);
    }

    /**
     * Solicitar upgrade de plano
     */
    public function upgrade(): void
    {
        $novoPlano = $_POST['plano'] ?? '';
        
        if (!in_array($novoPlano, ['starter', 'pro'])) {
            setFlash('error', 'Plano inválido.');
            $this->redirect('configuracoes/plano');
        }
        
        // Aqui implementaria integração com gateway de pagamento
        // Por enquanto, apenas simulação
        
        setFlash('success', 'Solicitação de upgrade recebida! Entraremos em contato para ativação.');
        $this->redirect('configuracoes/plano');
    }

    /**
     * Página de template de contrato
     */
    public function contrato(): void
    {
        $empresaId = getEmpresaId();
        $empresa = $this->empresaModel->findById($empresaId);
        
        $template = $empresa['template_contrato'] ?? $this->getTemplateContratoPadrao();
        
        $mergeTags = [
            ['tag' => '{{cliente_nome}}', 'descricao' => 'Nome do cliente'],
            ['tag' => '{{cliente_cpf_cnpj}}', 'descricao' => 'CPF/CNPJ do cliente'],
            ['tag' => '{{cliente_endereco}}', 'descricao' => 'Endereço do cliente'],
            ['tag' => '{{cliente_telefone}}', 'descricao' => 'Telefone do cliente'],
            ['tag' => '{{empresa_nome}}', 'descricao' => 'Nome da empresa'],
            ['tag' => '{{empresa_cnpj}}', 'descricao' => 'CNPJ da empresa'],
            ['tag' => '{{empresa_endereco}}', 'descricao' => 'Endereço da empresa'],
            ['tag' => '{{empresa_telefone}}', 'descricao' => 'Telefone da empresa'],
            ['tag' => '{{os_servico}}', 'descricao' => 'Serviço da OS'],
            ['tag' => '{{os_descricao}}', 'descricao' => 'Descrição da OS'],
            ['tag' => '{{os_valor}}', 'descricao' => 'Valor da OS'],
            ['tag' => '{{os_garantia}}', 'descricao' => 'Prazo de garantia'],
            ['tag' => '{{data_atual}}', 'descricao' => 'Data atual']
        ];
        
        $this->layout('main', [
            'titulo' => 'Template de Contrato',
            'content' => $this->renderView('configuracoes/contrato', [
                'empresa' => $empresa,
                'template' => $template,
                'mergeTags' => $mergeTags
            ])
        ]);
    }

    /**
     * Testar configurações SMTP (e-mail)
     */
    public function testarEmail(): void
    {
        $email = $_POST['email_teste'] ?? '';
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->jsonResponse(['success' => false, 'message' => 'E-mail inválido']);
        }
        
        // Implementar teste de envio SMTP
        // Por enquanto, apenas simulação
        
        $this->jsonResponse(['success' => true, 'message' => 'E-mail de teste enviado para ' . $email]);
    }

    /**
     * Upload de logo
     */
    private function uploadLogo(array $file, int $empresaId): ?string
    {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        $maxSize = 2 * 1024 * 1024; // 2MB
        
        // Debug: registrar informações do arquivo
        error_log('DEBUG Logo Upload - File info: ' . json_encode($file));
        
        if (!in_array($file['type'], $allowedTypes)) {
            error_log('DEBUG Logo Upload - Tipo não permitido: ' . $file['type']);
            setFlash('error', 'Formato de imagem não suportado. Use JPG, PNG ou GIF. Tipo recebido: ' . $file['type']);
            return null;
        }
        
        if ($file['size'] > $maxSize) {
            error_log('DEBUG Logo Upload - Arquivo muito grande: ' . $file['size']);
            setFlash('error', 'Imagem muito grande. Máximo 2MB.');
            return null;
        }
        
        $uploadDir = __DIR__ . '/../../uploads/logos/';
        error_log('DEBUG Logo Upload - Diretório: ' . $uploadDir);
        
        if (!is_dir($uploadDir)) {
            if (!mkdir($uploadDir, 0755, true)) {
                error_log('DEBUG Logo Upload - Erro ao criar diretório: ' . $uploadDir);
                setFlash('error', 'Erro ao criar diretório de upload.');
                return null;
            }
        }
        
        if (!is_writable($uploadDir)) {
            error_log('DEBUG Logo Upload - Diretório sem permissão de escrita: ' . $uploadDir);
            setFlash('error', 'Diretório de upload sem permissão de escrita.');
            return null;
        }
        
        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = 'logo_empresa_' . $empresaId . '_' . time() . '.' . $ext;
        $filepath = $uploadDir . $filename;
        
        error_log('DEBUG Logo Upload - Movendo arquivo para: ' . $filepath);
        
        if (move_uploaded_file($file['tmp_name'], $filepath)) {
            error_log('DEBUG Logo Upload - Arquivo movido com sucesso');
            // Redimensionar se necessário (máximo 300x100)
            $this->resizeImage($filepath, 300, 100);
            return 'uploads/logos/' . $filename;
        }
        
        error_log('DEBUG Logo Upload - Erro move_uploaded_file. Tmp: ' . $file['tmp_name'] . ' -> ' . $filepath);
        setFlash('error', 'Erro ao fazer upload da imagem.');
        return null;
    }

    /**
     * Redimensionar imagem
     */
    private function resizeImage(string $filepath, int $maxWidth, int $maxHeight): void
    {
        error_log('DEBUG resizeImage - Iniciando redimensionamento: ' . $filepath);
        
        if (!extension_loaded('gd')) {
            error_log('DEBUG resizeImage - Extensão GD não está carregada!');
            return;
        }
        
        $imageInfo = @getimagesize($filepath);
        if ($imageInfo === false) {
            error_log('DEBUG resizeImage - Não foi possível obter informações da imagem');
            return;
        }
        
        list($width, $height, $type) = $imageInfo;
        error_log('DEBUG resizeImage - Dimensões originais: ' . $width . 'x' . $height . ', Tipo: ' . $type);
        
        if ($width <= $maxWidth && $height <= $maxHeight) {
            error_log('DEBUG resizeImage - Imagem já está dentro dos limites, pulando redimensionamento');
            return;
        }
        
        $ratio = min($maxWidth / $width, $maxHeight / $height);
        $newWidth = (int) ($width * $ratio);
        $newHeight = (int) ($height * $ratio);
        
        error_log('DEBUG resizeImage - Novas dimensões: ' . $newWidth . 'x' . $newHeight);
        
        $srcImage = match ($type) {
            IMAGETYPE_JPEG => imagecreatefromjpeg($filepath),
            IMAGETYPE_PNG => imagecreatefrompng($filepath),
            IMAGETYPE_GIF => imagecreatefromgif($filepath),
            default => null
        };
        
        if (!$srcImage) {
            error_log('DEBUG resizeImage - Erro ao criar imagem fonte');
            return;
        }
        
        $dstImage = imagecreatetruecolor($newWidth, $newHeight);
        
        if (!$dstImage) {
            error_log('DEBUG resizeImage - Erro ao criar imagem destino');
            imagedestroy($srcImage);
            return;
        }
        
        // Preservar transparência para PNG
        if ($type === IMAGETYPE_PNG) {
            imagealphablending($dstImage, false);
            imagesavealpha($dstImage, true);
        }
        
        imagecopyresampled($dstImage, $srcImage, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
        
        $result = match ($type) {
            IMAGETYPE_JPEG => imagejpeg($dstImage, $filepath, 85),
            IMAGETYPE_PNG => imagepng($dstImage, $filepath, 6),
            IMAGETYPE_GIF => imagegif($dstImage, $filepath),
            default => false
        };
        
        error_log('DEBUG resizeImage - Resultado do save: ' . ($result ? 'SUCESSO' : 'FALHA'));
        
        imagedestroy($srcImage);
        imagedestroy($dstImage);
    }

    /**
     * Template padrão de contrato
     */
    private function getTemplateContratoPadrao(): string
    {
        return <<<HTML
<h2>CONTRATO DE PRESTAÇÃO DE SERVIÇOS</h2>

<p><strong>CONTRATANTE:</strong> {{cliente_nome}}, CPF/CNPJ: {{cliente_cpf}}<br>
<strong>CONTRATADA:</strong> {{empresa_nome}}, CNPJ: {{empresa_cnpj}}</p>

<h3>CLÁUSULA 1ª - DO OBJETO</h3>
<p>O presente contrato tem por objeto a prestação de serviços de <strong>{{servico}}</strong>, 
de acordo com as especificações descritas na Ordem de Serviço nº {{numero_os}}.</p>

<h3>CLÁUSULA 2ª - DO VALOR</h3>
<p>Fica ajustado o valor total de <strong>{{valor}}</strong> pelo serviço contratado, 
a ser pago conforme forma de pagamento acordada.</p>

<h3>CLÁUSULA 3ª - DA GARANTIA</h3>
<p>A CONTRATADA garante o serviço executado pelo prazo de <strong>{{garantia}} dias</strong>, 
conforme Termos de Garantia anexos.</p>

<h3>CLÁUSULA 4ª - DO PRAZO</h3>
<p>O serviço será executado no prazo de previsão estipulado na OS, salvo imprevistos 
comunicados previamente ao CONTRATANTE.</p>

<p style="margin-top: 40px;">
{{cidade}}, {{data}}
</p>

<p style="margin-top: 60px;">
___________________________<br>
<strong>CONTRATADA</strong><br>
{{empresa_nome}}
</p>

<p style="margin-top: 40px;">
___________________________<br>
<strong>CONTRATANTE</strong><br>
{{cliente_nome}}
</p>
HTML;
    }

    private function renderView(string $view, array $data = []): string
    {
        extract($data);
        ob_start();
        $viewPath = __DIR__ . '/../views/' . $view . '.php';
        if (file_exists($viewPath)) {
            include $viewPath;
        }
        return ob_get_clean();
    }
}
