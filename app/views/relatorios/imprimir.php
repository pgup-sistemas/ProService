<?php
/**
 * View de Impressão de Relatórios
 * Arquivo: /app/views/relatorios/imprimir.php
 */
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Relatório - <?= e($tipo) ?> - proService</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.5;
            color: #333;
            padding: 20px;
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #333;
        }
        
        .header h1 {
            font-size: 18px;
            margin-bottom: 5px;
        }
        
        .header p {
            color: #666;
            font-size: 11px;
        }
        
        .info {
            margin-bottom: 20px;
        }
        
        .info p {
            margin: 3px 0;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        
        table th, table td {
            border: 1px solid #333;
            padding: 8px;
            text-align: left;
        }
        
        table th {
            background-color: #f0f0f0;
            font-weight: bold;
        }
        
        table td.text-right, table th.text-right {
            text-align: right;
        }
        
        table td.text-center, table th.text-center {
            text-align: center;
        }
        
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #666;
            border-top: 1px solid #ccc;
            padding-top: 15px;
        }
        
        .total-row {
            font-weight: bold;
            background-color: #f5f5f5;
        }
        
        @media print {
            body {
                padding: 10px;
            }
            
            .no-print {
                display: none;
            }
        }
        
        .btn-print {
            background: #007bff;
            color: white;
            border: none;
            padding: 10px 20px;
            cursor: pointer;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        
        .btn-print:hover {
            background: #0056b3;
        }
    </style>
</head>
<body>
    <button class="btn-print no-print" onclick="window.print()">
        <i class="bi bi-printer"></i> Imprimir
    </button>
    
    <div class="header">
        <h1>Relatório de <?= ucfirst(str_replace('_', ' ', $tipo)) ?></h1>
        <p>proService - Sistema de Gestão de Serviços</p>
        <p>Emitido em: <?= date('d/m/Y H:i:s') ?></p>
    </div>
    
    <div class="info">
        <?php if (!empty($filtros)): ?>
            <p><strong>Filtros aplicados:</strong></p>
            <?php if (!empty($filtros['data_inicio'])): ?>
                <p>Período: <?= date('d/m/Y', strtotime($filtros['data_inicio'])) ?> a <?= date('d/m/Y', strtotime($filtros['data_fim'])) ?></p>
            <?php endif; ?>
            <?php if (!empty($filtros['status'])): ?>
                <p>Status: <?= ucfirst($filtros['status']) ?></p>
            <?php endif; ?>
        <?php endif; ?>
    </div>
    
    <?php if ($tipo === 'servicos'): ?>
        <table>
            <thead>
                <tr>
                    <th>Nº OS</th>
                    <th>Data</th>
                    <th>Cliente</th>
                    <th>Serviço</th>
                    <th>Técnico</th>
                    <th>Status</th>
                    <th class="text-right">Valor</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($ordens)): ?>
                    <?php foreach ($ordens as $os): ?>
                    <tr>
                        <td>#<?= $os['numero_os'] ?></td>
                        <td><?= date('d/m/Y', strtotime($os['data_entrada'])) ?></td>
                        <td><?= e($os['cliente_nome'] ?? 'N/A') ?></td>
                        <td><?= e($os['servico_nome'] ?? 'N/A') ?></td>
                        <td><?= e($os['tecnico_nome'] ?? 'Não atribuído') ?></td>
                        <td><?= ucfirst(str_replace('_', ' ', $os['status'])) ?></td>
                        <td class="text-right">R$ <?= number_format($os['valor_total'] ?? 0, 2, ',', '.') ?></td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" class="text-center">Nenhuma ordem de serviço encontrada</td>
                    </tr>
                <?php endif; ?>
            </tbody>
            <?php if (!empty($valorTotal)): ?>
            <tfoot>
                <tr class="total-row">
                    <td colspan="6" class="text-right">Total:</td>
                    <td class="text-right">R$ <?= number_format($valorTotal, 2, ',', '.') ?></td>
                </tr>
            </tfoot>
            <?php endif; ?>
        </table>
    
    <?php elseif ($tipo === 'financeiro'): ?>
        <h3>Receitas</h3>
        <table>
            <thead>
                <tr>
                    <th>Data</th>
                    <th>Descrição</th>
                    <th>Forma Pagamento</th>
                    <th>Status</th>
                    <th class="text-right">Valor</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($receitas)): ?>
                    <?php foreach ($receitas as $r): 
                        $formaPagamento = $r['forma_pagamento'] ?? '';
                        $formaLabel = $formaPagamento === 'nao_informado' || empty($formaPagamento) ? 'Não informado' : ucfirst(str_replace('_', ' ', $formaPagamento));
                    ?>
                    <tr>
                        <td><?= date('d/m/Y', strtotime($r['data_recebimento'])) ?></td>
                        <td><?= e($r['descricao']) ?></td>
                        <td><?= $formaLabel ?></td>
                        <td><?= ucfirst($r['status']) ?></td>
                        <td class="text-right">R$ <?= number_format($r['valor'], 2, ',', '.') ?></td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" class="text-center">Nenhuma receita encontrada</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
        
        <h3 style="margin-top: 30px;">Despesas</h3>
        <table>
            <thead>
                <tr>
                    <th>Data</th>
                    <th>Descrição</th>
                    <th>Categoria</th>
                    <th>Status</th>
                    <th class="text-right">Valor</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($despesas)): ?>
                    <?php foreach ($despesas as $d): ?>
                    <tr>
                        <td><?= date('d/m/Y', strtotime($d['data_despesa'])) ?></td>
                        <td><?= e($d['descricao']) ?></td>
                        <td><?= ucfirst($d['categoria']) ?></td>
                        <td><?= ucfirst($d['status']) ?></td>
                        <td class="text-right">R$ <?= number_format($d['valor'], 2, ',', '.') ?></td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" class="text-center">Nenhuma despesa encontrada</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
        
        <?php if (!empty($totalReceitas) || !empty($totalDespesas)): ?>
        <table style="margin-top: 20px;">
            <tr class="total-row">
                <td>Total Receitas: R$ <?= number_format($totalReceitas ?? 0, 2, ',', '.') ?></td>
                <td>Total Despesas: R$ <?= number_format($totalDespesas ?? 0, 2, ',', '.') ?></td>
                <td>Lucro: R$ <?= number_format(($totalReceitas ?? 0) - ($totalDespesas ?? 0), 2, ',', '.') ?></td>
            </tr>
        </table>
        <?php endif; ?>
    
    <?php elseif ($tipo === 'estoque'): ?>
        <table>
            <thead>
                <tr>
                    <th>Produto</th>
                    <th>Código SKU</th>
                    <th class="text-center">Quantidade</th>
                    <th class="text-center">Mínima</th>
                    <th>Status</th>
                    <th class="text-right">Custo Unit.</th>
                    <th class="text-right">Custo Total</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($produtos)): ?>
                    <?php foreach ($produtos as $p): 
                        $status = $p['quantidade_estoque'] <= 0 ? 'Zerado' : 
                                 ($p['quantidade_estoque'] <= $p['quantidade_minima'] ? 'Baixo' : 'OK');
                    ?>
                    <tr>
                        <td><?= e($p['nome']) ?></td>
                        <td><?= e($p['codigo_sku'] ?? '-') ?></td>
                        <td class="text-center"><?= $p['quantidade_estoque'] ?></td>
                        <td class="text-center"><?= $p['quantidade_minima'] ?></td>
                        <td><?= $status ?></td>
                        <td class="text-right">R$ <?= number_format($p['custo_unitario'] ?? 0, 2, ',', '.') ?></td>
                        <td class="text-right">R$ <?= number_format($p['custo_total'] ?? 0, 2, ',', '.') ?></td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" class="text-center">Nenhum produto encontrado</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
        
        <?php if (!empty($custoTotalEstoque)): ?>
        <table style="margin-top: 20px;">
            <tr class="total-row">
                <td colspan="6" class="text-right">Valor Total em Estoque:</td>
                <td class="text-right">R$ <?= number_format($custoTotalEstoque, 2, ',', '.') ?></td>
            </tr>
        </table>
        <?php endif; ?>
    
    <?php elseif ($tipo === 'tecnicos'): ?>
        <table>
            <thead>
                <tr>
                    <th>Técnico</th>
                    <th class="text-center">Total OS</th>
                    <th class="text-right">Receita Gerada</th>
                    <th class="text-right">Ticket Médio</th>
                    <th class="text-center">Tempo Médio (dias)</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($desempenho)): ?>
                    <?php foreach ($desempenho as $t): ?>
                    <tr>
                        <td><?= e($t['nome']) ?></td>
                        <td class="text-center"><?= $t['total_os'] ?? 0 ?></td>
                        <td class="text-right">R$ <?= number_format($t['receita_gerada'] ?? 0, 2, ',', '.') ?></td>
                        <td class="text-right">R$ <?= number_format($t['ticket_medio'] ?? 0, 2, ',', '.') ?></td>
                        <td class="text-center"><?= number_format($t['tempo_medio'] ?? 0, 1) ?></td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" class="text-center">Nenhum dado de desempenho encontrado</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    
    <?php elseif ($tipo === 'despesas'): ?>
        <table>
            <thead>
                <tr>
                    <th>Data</th>
                    <th>Descrição</th>
                    <th>Categoria</th>
                    <th>Status</th>
                    <th class="text-right">Valor</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($despesas)): ?>
                    <?php foreach ($despesas as $d): ?>
                    <tr>
                        <td><?= date('d/m/Y', strtotime($d['data_despesa'])) ?></td>
                        <td><?= e($d['descricao']) ?></td>
                        <td><?= ucfirst($d['categoria']) ?></td>
                        <td><?= ucfirst($d['status']) ?></td>
                        <td class="text-right">R$ <?= number_format($d['valor'], 2, ',', '.') ?></td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" class="text-center">Nenhuma despesa encontrada</td>
                    </tr>
                <?php endif; ?>
            </tbody>
            <?php if (!empty($totalDespesas)): ?>
            <tfoot>
                <tr class="total-row">
                    <td colspan="4" class="text-right">Total:</td>
                    <td class="text-right">R$ <?= number_format($totalDespesas, 2, ',', '.') ?></td>
                </tr>
            </tfoot>
            <?php endif; ?>
        </table>
    <?php endif; ?>
    
    <div class="footer">
        <p>proService - Sistema de Gestão de Serviços</p>
        <p>Este relatório foi gerado automaticamente pelo sistema.</p>
    </div>
</body>
</html>
