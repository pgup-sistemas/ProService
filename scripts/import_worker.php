<?php
/**
 * Worker CLI para processar import_jobs pendentes
 * Uso (Linux cron): php scripts/import_worker.php
 * Uso (Windows Task Scheduler): php C:\path\to\proService\scripts\import_worker.php
 */

if (php_sapi_name() !== 'cli') {
    echo "Este script deve ser executado via CLI.\n";
    exit(1);
}

require_once __DIR__ . '/../app/config/config.php';
require_once __DIR__ . '/../app/config/helpers.php';
require_once __DIR__ . '/../app/config/Database.php';
require_once __DIR__ . '/../app/models/Model.php';
require_once __DIR__ . '/../app/models/ImportJob.php';
require_once __DIR__ . '/../app/models/Produto.php';
require_once __DIR__ . '/../app/models/LogSistema.php';

use App\Models\ImportJob;
use App\Models\Produto;
use App\Models\LogSistema;

$importJobModel = new ImportJob();
$produtoModel = new Produto();

$pending = $importJobModel->findPending(5);
if (empty($pending)) {
    echo date('Y-m-d H:i:s') . " - Sem jobs pendentes.\n";
    exit(0);
}

foreach ($pending as $job) {
    echo date('Y-m-d H:i:s') . " - Processando job {$job['id']} ({$job['original_filename']})\n";

    $importJobModel->update($job['id'], [
        'status' => 'processing',
        'started_at' => date('Y-m-d H:i:s')
    ]);

    $storedPath = $job['stored_path'];
    if (strpos($storedPath, UPLOAD_PATH) === 0) {
        $fullPath = $storedPath;
    } else {
        $fullPath = rtrim(UPLOAD_PATH, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . ltrim($storedPath, DIRECTORY_SEPARATOR);
    }

    if (!file_exists($fullPath)) {
        $importJobModel->update($job['id'], [
            'status' => 'failed',
            'error_text' => 'Arquivo não encontrado: ' . $fullPath,
            'finished_at' => date('Y-m-d H:i:s')
        ]);
        echo "  -> arquivo não encontrado\n";
        continue;
    }

    $ext = strtolower(pathinfo($fullPath, PATHINFO_EXTENSION));
    $total = 0;
    $processed = 0;
    $errors = [];

    $db = \App\Config\Database::getInstance();
    $db->beginTransaction();
    try {
        // Leitura/iterador
        if (in_array($ext, ['xls','xlsx'])) {
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($fullPath);
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray(null, true, true, false);
            $header = array_map(function($c){ return strtolower(trim((string)$c)); }, array_shift($rows));
            $total = count($rows);

            foreach ($rows as $i => $dataRow) {
                $processed++;
                $row = [];
                foreach ($header as $colIndex => $colName) {
                    $row[$colName] = isset($dataRow[$colIndex]) ? trim((string)$dataRow[$colIndex]) : null;
                }

                // --- Process row (mesma lógica do import CSV) ---
                if (empty($row['nome'])) {
                    $errors[] = ['line' => $i + 2, 'error' => 'Nome obrigatório'];
                    continue;
                }

                $sku = $row['codigo_sku'] ?? null;
                $quantidade = isset($row['quantidade_estoque']) && $row['quantidade_estoque'] !== '' ? floatval(str_replace(',', '.', $row['quantidade_estoque'])) : null;
                $custo = isset($row['custo_unitario']) && $row['custo_unitario'] !== '' ? floatval(str_replace(',', '.', $row['custo_unitario'])) : null;
                $preco = isset($row['preco_venda']) && $row['preco_venda'] !== '' ? floatval(str_replace(',', '.', $row['preco_venda'])) : null;

                $existing = $sku ? $produtoModel->findBy('codigo_sku', $sku) : null;

                $produtoData = [
                    'nome' => $row['nome'] ?? '',
                    'categoria' => $row['categoria'] ?? '',
                    'unidade' => $row['unidade'] ?? 'UN',
                    'quantidade_minima' => isset($row['quantidade_minima']) ? floatval(str_replace(',', '.', $row['quantidade_minima'])) : 0,
                    'custo_unitario' => $custo ?? 0,
                    'preco_venda' => $preco ?? 0,
                    'fornecedor' => $row['fornecedor'] ?? '',
                    'observacoes' => $row['observacoes'] ?? ''
                ];

                if ($existing) {
                    $produtoModel->update($existing['id'], array_merge($produtoData, ['codigo_sku' => $sku]));
                    if ($quantidade !== null && $quantidade != $existing['quantidade_estoque']) {
                        $diff = $quantidade - $existing['quantidade_estoque'];
                        if ($diff > 0) {
                            $produtoModel->entradaEstoque($existing['id'], $diff, $custo, 'Import background');
                        } else {
                            $produtoModel->saidaEstoque($existing['id'], abs($diff), 'Import background');
                        }
                    }
                } else {
                    $produtoData['codigo_sku'] = $sku ?? null;
                    $produtoData['quantidade_estoque'] = $quantidade ?? 0;
                    $newId = $produtoModel->create($produtoData);
                    if ($newId && $quantidade !== null && $quantidade > 0) {
                        $produtoModel->entradaEstoque($newId, $quantidade, $custo, 'Import background');
                    }
                }

                // atualizar progresso no job a cada 50 linhas
                if ($processed % 50 === 0) {
                    $importJobModel->update($job['id'], [
                        'processed_rows' => $processed,
                        'progress' => $total > 0 ? round(($processed / $total) * 100, 2) : 0
                    ]);
                }
            }
        } else {
            // CSV
            $handle = fopen($fullPath, 'r');
            $header = fgetcsv($handle);
            $cols = array_map(function($c) { return strtolower(trim($c)); }, $header ?: []);
            $rows = [];
            while (($data = fgetcsv($handle)) !== false) {
                $rows[] = $data;
            }
            $total = count($rows);

            foreach ($rows as $i => $data) {
                $processed++;
                $row = [];
                foreach ($cols as $ci => $col) {
                    $row[$col] = isset($data[$ci]) ? trim($data[$ci]) : null;
                }

                if (empty($row['nome'])) {
                    $errors[] = ['line' => $i + 2, 'error' => 'Nome obrigatório'];
                    continue;
                }

                $sku = $row['codigo_sku'] ?? null;
                $quantidade = isset($row['quantidade_estoque']) && $row['quantidade_estoque'] !== '' ? floatval(str_replace(',', '.', $row['quantidade_estoque'])) : null;
                $custo = isset($row['custo_unitario']) && $row['custo_unitario'] !== '' ? floatval(str_replace(',', '.', $row['custo_unitario'])) : null;
                $preco = isset($row['preco_venda']) && $row['preco_venda'] !== '' ? floatval(str_replace(',', '.', $row['preco_venda'])) : null;

                $existing = $sku ? $produtoModel->findBy('codigo_sku', $sku) : null;

                $produtoData = [
                    'nome' => $row['nome'] ?? '',
                    'categoria' => $row['categoria'] ?? '',
                    'unidade' => $row['unidade'] ?? 'UN',
                    'quantidade_minima' => isset($row['quantidade_minima']) ? floatval(str_replace(',', '.', $row['quantidade_minima'])) : 0,
                    'custo_unitario' => $custo ?? 0,
                    'preco_venda' => $preco ?? 0,
                    'fornecedor' => $row['fornecedor'] ?? '',
                    'observacoes' => $row['observacoes'] ?? ''
                ];

                if ($existing) {
                    $produtoModel->update($existing['id'], array_merge($produtoData, ['codigo_sku' => $sku]));
                    if ($quantidade !== null && $quantidade != $existing['quantidade_estoque']) {
                        $diff = $quantidade - $existing['quantidade_estoque'];
                        if ($diff > 0) {
                            $produtoModel->entradaEstoque($existing['id'], $diff, $custo, 'Import background');
                        } else {
                            $produtoModel->saidaEstoque($existing['id'], abs($diff), 'Import background');
                        }
                    }
                } else {
                    $produtoData['codigo_sku'] = $sku ?? null;
                    $produtoData['quantidade_estoque'] = $quantidade ?? 0;
                    $newId = $produtoModel->create($produtoData);
                    if ($newId && $quantidade !== null && $quantidade > 0) {
                        $produtoModel->entradaEstoque($newId, $quantidade, $custo, 'Import background');
                    }
                }

                if ($processed % 50 === 0) {
                    $importJobModel->update($job['id'], [
                        'processed_rows' => $processed,
                        'progress' => $total > 0 ? round(($processed / $total) * 100, 2) : 0
                    ]);
                }
            }
        }

        $db->commit();

        // gravar resultado
        $importJobModel->update($job['id'], [
            'status' => 'completed',
            'processed_rows' => $processed,
            'progress' => 100,
            'result_json' => json_encode(['total' => $total, 'processed' => $processed, 'errors' => $errors], JSON_UNESCAPED_UNICODE),
            'finished_at' => date('Y-m-d H:i:s')
        ]);

        // registrar log
        LogSistema::registrar('import_produtos_background', 'produtos', null, [
            'job_id' => $job['id'],
            'total' => $total,
            'processed' => $processed,
            'errors_count' => count($errors)
        ]);

        echo "  -> concluído: total={$total}, processed={$processed}, errors=" . count($errors) . "\n";
    } catch (\Throwable $e) {
        $db->rollBack();
        $importJobModel->update($job['id'], [
            'status' => 'failed',
            'error_text' => $e->getMessage(),
            'finished_at' => date('Y-m-d H:i:s')
        ]);
        echo "  -> falhou: " . $e->getMessage() . "\n";
    }
}

echo "Worker finalizado.\n";
