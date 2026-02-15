<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($titulo) ?></title>
    <link rel="icon" type="image/svg+xml" href="<?= asset('favicon.svg') ?>">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #1e40af 0%, #059669 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .error-card {
            background: white;
            border-radius: 16px;
            padding: 40px;
            text-align: center;
            max-width: 400px;
        }
    </style>
</head>
<body>
    <div class="error-card">
        <i class="bi bi-question-circle fs-1 text-muted"></i>
        <h3 class="mt-3">OS não encontrada</h3>
        <p class="text-muted">A Ordem de Serviço que você está procurando não existe ou o link está incorreto.</p>
        <a href="<?= url() ?>" class="btn btn-primary">Voltar para o início</a>
    </div>
</body>
</html>
