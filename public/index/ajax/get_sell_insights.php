<?php

require_once '../../../app/config/db.php';
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

    /*
    =====================================================
    DATAS
    =====================================================
    */

    $today = date('Y-m-d');

    // início do ano até hoje
    $yearStart = date('Y-01-01');

    // mês atual
    $monthStart = date('Y-m-01');
    $monthEnd   = date('Y-m-t');

    // últimos 3 meses (comparação)
    $current = [
        'start' => date('Y-m-01', strtotime('-2 months')),
        'end'   => date('Y-m-t')
    ];

    // 3 meses anteriores
    $previous = [
        'start' => date('Y-m-01', strtotime('-5 months')),
        'end'   => date('Y-m-t', strtotime('-3 months'))
    ];

    /*
    =====================================================
    FUNÇÃO TOTAL
    =====================================================
    */

    function getTotal($pdo, $start, $end, $company_id)
    {
        $sql = "
            SELECT COALESCE(SUM(final_total), 0)
            FROM invoices
            WHERE issue_date BETWEEN :start AND :end
            AND company_id = :company_id
        ";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            'start' => $start . ' 00:00:00',
            'end' => $end . ' 23:59:59',
            'company_id' => $company_id
        ]);

        return (float) $stmt->fetchColumn();
    }

    /*
    =====================================================
    TOP CLIENTES
    =====================================================
    */

    $topClientesSql = "
        SELECT
            c.id,
            c.name AS cliente,
            SUM(i.final_total) AS total_faturado,
            COUNT(i.id) AS total_faturas
        FROM invoices i
        INNER JOIN contact c
            ON c.id = i.contact_id
        WHERE i.company_id = :company_id
        GROUP BY c.id, c.name
        ORDER BY total_faturado DESC
        LIMIT 10
    ";

    $stmtTop = $pdo->prepare($topClientesSql);
    $stmtTop->execute([
        'company_id' => $company_id
    ]);

    $topClientes = $stmtTop->fetchAll(PDO::FETCH_ASSOC);

    /*
    =====================================================
    VOLUME GLOBAL (ANO ATUAL)
    =====================================================
    */

    $volumeGlobal = getTotal(
        $pdo,
        $yearStart,
        $today,
        $company_id
    );

    /*
    =====================================================
    VENDA DO PERÍODO (MÊS ATUAL)
    =====================================================
    */

    $vendaPeriodo = getTotal(
        $pdo,
        $monthStart,
        $monthEnd,
        $company_id
    );

    /*
=====================================================
VENDA DO PERÍODO (MÊS ATUAL)
+
CRESCIMENTO FACE AO MÊS ANTERIOR
=====================================================
*/

    /*
MÊS ATUAL
*/

    $vendaPeriodo = getTotal(
        $pdo,
        $monthStart,
        $monthEnd,
        $company_id
    );


    /*
MÊS ANTERIOR
*/

    $previousMonthStart = date(
        'Y-m-01',
        strtotime('first day of last month')
    );

    $previousMonthEnd = date(
        'Y-m-t',
        strtotime('last day of last month')
    );

    $vendaPeriodoAnterior = getTotal(
        $pdo,
        $previousMonthStart,
        $previousMonthEnd,
        $company_id
    );


    /*
CRESCIMENTO DO PERÍODO (%)
*/

    if ($vendaPeriodoAnterior > 0) {
        $vendaPeriodoGrowth =
            (
                ($vendaPeriodo - $vendaPeriodoAnterior)
                / $vendaPeriodoAnterior
            ) * 100;
    } else {
        /*
    evita divisão por zero
    */
        $vendaPeriodoGrowth = $vendaPeriodo > 0 ? 100 : 0;
    }

    $vendaPeriodoGrowth = round($vendaPeriodoGrowth, 1);


    /*
    =====================================================
    MÉDIA MENSAL
    baseada no volume global
    =====================================================
    */

    $currentMonthNumber = (int) date('n'); // 1 a 12

    $mediaMensal = $currentMonthNumber > 0
        ? ($volumeGlobal / $currentMonthNumber)
        : 0;

    /*
    =====================================================
    COMPARAÇÃO DE MÉDIA (últimos 3 meses)
    =====================================================
    */

    $volumeAtual = getTotal(
        $pdo,
        $current['start'],
        $current['end'],
        $company_id
    );

    $volumeAnterior = getTotal(
        $pdo,
        $previous['start'],
        $previous['end'],
        $company_id
    );

    $mediaAtual = $volumeAtual / 3;
    $mediaAnterior = $volumeAnterior / 3;

    if ($mediaAnterior > 0) {
        $mediaMensal_dif =
            (($mediaAtual - $mediaAnterior) / $mediaAnterior) * 100;
    } else {
        $mediaMensal_dif = $mediaAtual > 0 ? 100 : 0;
    }

    /*
    =====================================================
    CRESCIMENTO GERAL (%)
    =====================================================
    */

    if ($volumeAnterior > 0) {
        $crescimento =
            (($volumeAtual - $volumeAnterior) / $volumeAnterior) * 100;
    } else {
        $crescimento = $volumeAtual > 0 ? 100 : 0;
    }

    $crescimento = round($crescimento, 1);

    /*
    =====================================================
    CLIENTES ATIVOS
    clientes pagantes no mês atual
    =====================================================
    */

    $clientesAtivosStmt = $pdo->prepare("
        SELECT COUNT(DISTINCT contact_id) AS total
        FROM invoices
        WHERE company_id = :company_id
        AND issue_date BETWEEN :start AND :end
    ");

    $clientesAtivosStmt->execute([
        'company_id' => $company_id,
        'start' => $monthStart . ' 00:00:00',
        'end' => $monthEnd . ' 23:59:59'
    ]);

    $clientesAtivos = (int) $clientesAtivosStmt->fetchColumn();

    /*
    =====================================================
    TOTAL DE CLIENTES
    =====================================================
    */

    $clientesStmt = $pdo->prepare("
        SELECT COUNT(*) AS total
        FROM contact
        WHERE company_id = :company_id
    ");

    $clientesStmt->execute([
        'company_id' => $company_id
    ]);

    $clientes = (int) $clientesStmt->fetchColumn();

    /*
    =====================================================
    DOCUMENTOS
    =====================================================
    */

    $documentosStmt = $pdo->prepare("
        SELECT COUNT(*) AS total
        FROM invoices
        WHERE company_id = :company_id
    ");

    $documentosStmt->execute([
        'company_id' => $company_id
    ]);

    $documentos = (int) $documentosStmt->fetchColumn();

    /*
    =====================================================
    NOVOS CLIENTES (últimos 3 meses)
    =====================================================
    */

    $novosClientesStmt = $pdo->prepare("
        SELECT COUNT(*) AS total
        FROM contact
        WHERE company_id = :company_id
        AND created_at >= DATE_SUB(CURDATE(), INTERVAL 3 MONTH)
    ");

    $novosClientesStmt->execute([
        'company_id' => $company_id
    ]);

    $novosClientes = (int) $novosClientesStmt->fetchColumn();

    /*
    =====================================================
    NOVOS DOCUMENTOS (últimos 3 meses)
    =====================================================
    */

    $novosDocumentosStmt = $pdo->prepare("
        SELECT COUNT(*) AS total
        FROM invoices
        WHERE company_id = :company_id
        AND created_at >= DATE_SUB(CURDATE(), INTERVAL 3 MONTH)
    ");

    $novosDocumentosStmt->execute([
        'company_id' => $company_id
    ]);

    $novosDocumentos = (int) $novosDocumentosStmt->fetchColumn();

    /*
    =====================================================
    EVOLUÇÃO MENSAL
    =====================================================
    */

    $evolucaoSql = "
        SELECT
            DATE_FORMAT(issue_date, '%Y-%m') AS mes,
            SUM(final_total) AS total
        FROM invoices
        WHERE issue_date BETWEEN :start AND :end
        AND company_id = :company_id
        GROUP BY mes
        ORDER BY mes ASC
    ";

    $stmt = $pdo->prepare($evolucaoSql);
    $stmt->execute([
        'start' => $yearStart,
        'end' => $today,
        'company_id' => $company_id
    ]);

    $evolucao = $stmt->fetchAll(PDO::FETCH_ASSOC);

    /*
    =====================================================
    RESPONSE
    =====================================================
    */

    echo json_encode([
        'success' => true,
        'data' => [
            'top_clients' => $topClientes,

            'kpis' => [
                'volume_global' => round($volumeGlobal, 2),
                'media_mensal' => round($mediaMensal, 2),
                'media_mensal_dif' => round($mediaMensal_dif, 1),

                'venda_periodo' => round($vendaPeriodo, 2),
                'venda_periodo_growth' => $vendaPeriodoGrowth,

                'clientes' => $clientes,
                'clientes_ativos' => $clientesAtivos,
                'crescimento_clientes' => $novosClientes,

                'documentos' => $documentos,
                'crescimento_documentos' => $novosDocumentos,

                'crescimento' => $crescimento
            ],

            'comparacao' => [
                'atual' => round($volumeAtual, 2),
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
