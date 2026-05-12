<?php

require_once '../../../app/config/db.php';

header('Content-Type: application/json');

try {

    // =====================================================
    // VALIDAR PARÂMETROS
    // =====================================================

    $company_id = isset($_GET['company_id'])
        ? (int) $_GET['company_id']
        : 0;

    $user_id = isset($_GET['user_id'])
        ? (int) $_GET['user_id']
        : 0;

    if ($company_id <= 0 || $user_id <= 0) {

        echo json_encode([
            'success' => false,
            'error' => 'company_id e user_id são obrigatórios'
        ]);

        exit;
    }

    // =====================================================
    // DATAS
    // =====================================================

    $today = date('Y-m-d');

    // ano atual
    $yearStart = date('Y-01-01');

    // mês atual
    $monthStart = date('Y-m-01');
    $monthEnd = date('Y-m-t');

    // mês anterior
    $previousMonthStart = date(
        'Y-m-01',
        strtotime('first day of last month')
    );

    $previousMonthEnd = date(
        'Y-m-t',
        strtotime('last day of last month')
    );

    // últimos 3 meses
    $current = [
        'start' => date('Y-m-01', strtotime('-2 months')),
        'end' => $today
    ];

    // 3 meses anteriores
    $previous = [
        'start' => date('Y-m-01', strtotime('-5 months')),
        'end' => date('Y-m-t', strtotime('-3 months'))
    ];

    // =====================================================
    // FUNÇÃO TOTAL
    // =====================================================

    function getTotal($pdo, $start, $end, $company_id)
    {
        $sql = "
            SELECT COALESCE(SUM(final_total), 0)
            FROM invoices
            WHERE company_id = :company_id
            AND status NOT IN (1,2)
            AND DATE(issue_date) BETWEEN :start AND :end
        ";

        $stmt = $pdo->prepare($sql);

        $stmt->execute([
            'start' => $start,
            'end' => $end,
            'company_id' => $company_id
        ]);

        return (float)$stmt->fetchColumn();
    }

    // =====================================================
    // FUNÇÃO CRESCIMENTO
    // =====================================================

    function calcGrowth($current, $previous)
    {
        $current = (float)$current;
        $previous = (float)$previous;

        if ($previous <= 0) {

            if ($current > 0) {
                return 100;
            }

            return 0;
        }

        return round(
            (($current - $previous) / $previous) * 100,
            1
        );
    }

    // =====================================================
    // TOP CLIENTES
    // =====================================================

    $topClientesSql = "
        SELECT
            c.id,
            c.name AS cliente,
            ROUND(SUM(i.final_total), 2) AS total_faturado,
            COUNT(i.id) AS total_faturas
        FROM invoices i
        INNER JOIN contact c
            ON c.id = i.contact_id
        WHERE i.company_id = :company_id
        AND i.status NOT IN (1,2)
        GROUP BY c.id, c.name
        ORDER BY total_faturado DESC
        LIMIT 10
    ";

    $stmtTop = $pdo->prepare($topClientesSql);

    $stmtTop->execute([
        'company_id' => $company_id
    ]);

    $topClientes = $stmtTop->fetchAll(PDO::FETCH_ASSOC);

    // =====================================================
    // VOLUME GLOBAL
    // =====================================================

    $volumeGlobal = getTotal(
        $pdo,
        $yearStart,
        $today,
        $company_id
    );

    // =====================================================
    // VENDA PERÍODO
    // =====================================================

    $vendaPeriodo = getTotal(
        $pdo,
        $monthStart,
        $monthEnd,
        $company_id
    );

    // =====================================================
    // VENDA PERÍODO ANTERIOR
    // =====================================================

    $vendaPeriodoAnterior = getTotal(
        $pdo,
        $previousMonthStart,
        $previousMonthEnd,
        $company_id
    );

    // =====================================================
    // CRESCIMENTO PERÍODO
    // =====================================================

    $vendaPeriodoGrowth = calcGrowth(
        $vendaPeriodo,
        $vendaPeriodoAnterior
    );

    // =====================================================
    // MÉDIA MENSAL
    // =====================================================

    $currentMonthNumber = max(
        1,
        (int)date('n')
    );

    $mediaMensal = round(
        $volumeGlobal / $currentMonthNumber,
        2
    );

    // =====================================================
    // COMPARAÇÃO 3 MESES
    // =====================================================

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

    $mediaMensal_dif = calcGrowth(
        $mediaAtual,
        $mediaAnterior
    );

    // =====================================================
    // CRESCIMENTO GERAL
    // =====================================================

    $crescimento = calcGrowth(
        $volumeAtual,
        $volumeAnterior
    );

    // =====================================================
    // CLIENTES ATIVOS
    // =====================================================

    $clientesAtivosStmt = $pdo->prepare("
        SELECT COUNT(DISTINCT contact_id) AS total
        FROM invoices
        WHERE company_id = :company_id
        AND status NOT IN (1,2)
        AND DATE(issue_date) BETWEEN :start AND :end
    ");

    $clientesAtivosStmt->execute([
        'company_id' => $company_id,
        'start' => $monthStart,
        'end' => $monthEnd
    ]);

    $clientesAtivos = (int)$clientesAtivosStmt->fetchColumn();

    // =====================================================
    // TOTAL CLIENTES
    // =====================================================

    $clientesStmt = $pdo->prepare("
        SELECT COUNT(*) AS total
        FROM contact
        WHERE company_id = :company_id
    ");

    $clientesStmt->execute([
        'company_id' => $company_id
    ]);

    $clientes = (int)$clientesStmt->fetchColumn();

    // =====================================================
    // DOCUMENTOS
    // =====================================================

    $documentosStmt = $pdo->prepare("
        SELECT COUNT(*) AS total
        FROM invoices
        WHERE company_id = :company_id
        AND status NOT IN (1,2)
        AND DATE(issue_date) BETWEEN :start AND :end
    ");

    $documentosStmt->execute([
        'company_id' => $company_id,
        'start' => $monthStart,
        'end' => $monthEnd
    ]);

    $documentos = (int)$documentosStmt->fetchColumn();

    // =====================================================
    // NOVOS CLIENTES
    // =====================================================

    $novosClientesStmt = $pdo->prepare("
        SELECT COUNT(*) AS total
        FROM contact
        WHERE company_id = :company_id
        AND created_at >= DATE_SUB(CURDATE(), INTERVAL 3 MONTH)
    ");

    $novosClientesStmt->execute([
        'company_id' => $company_id
    ]);

    $novosClientes = (int)$novosClientesStmt->fetchColumn();

    // =====================================================
    // NOVOS DOCUMENTOS
    // =====================================================

    $novosDocumentosStmt = $pdo->prepare("
        SELECT COUNT(*) AS total
        FROM invoices
        WHERE company_id = :company_id
        AND status NOT IN (1,2)
        AND created_at >= DATE_SUB(CURDATE(), INTERVAL 3 MONTH)
    ");

    $novosDocumentosStmt->execute([
        'company_id' => $company_id
    ]);

    $novosDocumentos = (int)$novosDocumentosStmt->fetchColumn();

    // =====================================================
    // EVOLUÇÃO MENSAL
    // =====================================================

    $evolucaoSql = "
        SELECT
            DATE_FORMAT(issue_date, '%Y-%m') AS mes,
            ROUND(SUM(final_total), 2) AS total
        FROM invoices
        WHERE company_id = :company_id
        AND status NOT IN (1,2)
        AND DATE(issue_date) BETWEEN :start AND :end
        GROUP BY mes
        ORDER BY mes ASC
    ";

    $stmtEvolucao = $pdo->prepare($evolucaoSql);

    $stmtEvolucao->execute([
        'start' => $yearStart,
        'end' => $today,
        'company_id' => $company_id
    ]);

    $evolucao = $stmtEvolucao->fetchAll(PDO::FETCH_ASSOC);

    // =====================================================
    // RESPONSE
    // =====================================================

    echo json_encode([
        'success' => true,

        'data' => [

            'top_clients' => $topClientes,

            'kpis' => [

                'volume_global' => round($volumeGlobal, 2),

                'media_mensal' => round($mediaMensal, 2),

                'media_mensal_dif' => round(
                    $mediaMensal_dif,
                    1
                ),

                'venda_periodo' => round(
                    $vendaPeriodo,
                    2
                ),

                'venda_periodo_growth' => round(
                    $vendaPeriodoGrowth,
                    1
                ),

                'clientes' => $clientes,

                'clientes_ativos' => $clientesAtivos,

                'crescimento_clientes' => $novosClientes,

                'documentos' => $documentos,

                'crescimento_documentos' => $novosDocumentos,

                'crescimento' => round(
                    $crescimento,
                    1
                )
            ],

            'comparacao' => [

                'atual' => round(
                    $volumeAtual,
                    2
                ),

                'anterior' => round(
                    $volumeAnterior,
                    2
                )
            ],

            'evolucao' => $evolucao
        ]
    ]);
} catch (Throwable $e) {

    http_response_code(500);

    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
