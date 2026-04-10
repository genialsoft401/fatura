<?php
require_once __DIR__ . '../../../../app/config/db.php';
header('Content-Type: application/json');

try {

    $company_id = isset($_GET['company_id']) ? (int) $_GET['company_id'] : null;
    $user_id    = isset($_GET['user_id']) ? (int) $_GET['user_id'] : null;

    if (!$company_id || !$user_id) {
        echo json_encode([
            'success' => false,
            'error' => 'company_id e user_id são obrigatórios'
        ]);
        exit;
    }

    // Range de datas por meses
    function getDateRange($startOffset, $endOffset)
    {
        return [
            'start' => date('Y-m-01', strtotime("$startOffset months")),
            'end'   => date('Y-m-t', strtotime("$endOffset months"))
        ];
    }

    $current = [
        'start' => date('Y-m-01', strtotime('-2 months')),
        'end'   => date('Y-m-t')
    ];

    $previous = [
        'start' => date('Y-m-01', strtotime('-5 months')),
        'end'   => date('Y-m-t', strtotime('-3 months'))
    ];

    // Soma total com filtro de empresa
    function getTotal($pdo, $start, $end, $company_id)
    {
        $sql = "SELECT COALESCE(SUM(final_total),0)
            FROM invoices
            WHERE issue_date BETWEEN :start AND :end
            AND company_id = :company_id";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            'start' => $start . ' 00:00:00',
            'end'   => $end . ' 23:59:59',
            'company_id' => $company_id
        ]);

        return (float) $stmt->fetchColumn();
    }

    // Top clientes (filtrado por empresa)
    $topClientesSql = "SELECT 
                        c.id,
                        c.name AS cliente,
                        SUM(i.final_total) AS total_faturado,
                        COUNT(i.id) AS total_faturas
                    FROM invoices i
                    INNER JOIN contact c ON c.id = i.contact_id
                    WHERE i.company_id = :company_id
                    GROUP BY c.id, c.name
                    ORDER BY total_faturado DESC
                    LIMIT 10";

    $stmtTop = $pdo->prepare($topClientesSql);
    $stmtTop->execute(['company_id' => $company_id]);
    $topClientes = $stmtTop->fetchAll(PDO::FETCH_ASSOC);

    // KPIs principais
    $volumeAtual    = getTotal($pdo, $current['start'], $current['end'], $company_id);
    $volumeAnterior = getTotal($pdo, $previous['start'], $previous['end'], $company_id);

    // Média mensal (evitar divisão por zero)
    $mediaMensal = $volumeAtual > 0 ? $volumeAtual / 3 : 0;

    # Crescimento da media mensal (%)
    $mediaAtual = $volumeAtual / 3;
    $mediaAnterior = $volumeAnterior / 3;

    $mediaMensal_dif = (($mediaAtual - $mediaAnterior) / $mediaAnterior) * 100;


    // Crescimento (%)
    if ($volumeAnterior > 0) {
        $crescimento = (($volumeAtual - $volumeAnterior) / $volumeAnterior) * 100;
        $crescimento = max(0, min($crescimento, 100));
    } else {
        $crescimento = $volumeAtual > 0 ? 100 : 0;
    }

    // Total de clientes (por empresa)
    $clientesStmt = $pdo->prepare("
        SELECT COUNT(DISTINCT contact_id) AS total
        FROM invoices
        WHERE company_id = :company_id
    ");
    $clientesStmt->execute(['company_id' => $company_id]);
    $clientes = (int) $clientesStmt->fetchColumn();

    // Total de documentos (por empresa)
    $documentosStmt = $pdo->prepare("
        SELECT COUNT(*) AS total
        FROM invoices
        WHERE company_id = :company_id
    ");
    $documentosStmt->execute(['company_id' => $company_id]);
    $documentos = (int) $documentosStmt->fetchColumn();


    // Novos clientes no período atual
    $novosClientesStmt = $pdo->prepare("
        SELECT COUNT(DISTINCT c.id) AS total
        FROM contact c
        WHERE c.company_id = :company_id
        AND c.created_at >= DATE_SUB(CURDATE(), INTERVAL 3 MONTH);
    ");

    $novosClientesStmt->execute([
        'company_id' => $company_id
    ]);

    $novosClientes = (int) $novosClientesStmt->fetchColumn();


    // Novos documentos emitidos no período atual
    $novosDocumentosStmt = $pdo->prepare("
        SELECT COUNT(DISTINCT i.contact_id) AS total
        FROM invoices i
        WHERE i.company_id = :company_id
        AND i.created_at >= DATE_SUB(CURDATE(), INTERVAL 3 MONTH);
    ");

    $novosDocumentosStmt->execute([
        'company_id' => $company_id
    ]);

    $novosDocumentos = (int) $novosDocumentosStmt->fetchColumn();


    // Evolução mensal
    $evolucaoSql = "SELECT 
                        DATE_FORMAT(issue_date, '%Y-%m') AS mes,
                        SUM(final_total) AS total
                    FROM invoices
                    WHERE issue_date BETWEEN :start AND :end
                    AND company_id = :company_id
                    GROUP BY mes
                    ORDER BY mes ASC";

    $stmt = $pdo->prepare($evolucaoSql);
    $stmt->execute([
        'start' => $current['start'],
        'end' => $current['end'],
        'company_id' => $company_id
    ]);

    $evolucao = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'data' => [
            'top_clients' => $topClientes,
            'kpis' => [
                'volume_trimestral' => round($volumeAtual, 2),
                'media_mensal'      => round($mediaMensal, 2),
                'media_mensal_dif'  => round($mediaMensal_dif, 1),
                'clientes'          => $clientes,
                'crescimento_clientes'    => $novosClientes,
                'documentos'        => $documentos,
                'crescimento_documentos'  => $novosDocumentos,
                'crescimento'       => round($crescimento, 1)
            ],
            'comparacao' => [
                'atual'    => round($volumeAtual, 2),
                'anterior' => round($volumeAnterior, 2)
            ],
            'evolucao' => $evolucao
        ]
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
