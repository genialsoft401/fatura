<?php

require_once '../../../app/config/db.php';

header('Content-Type: application/json');

try {

    // =====================================================
    // VALIDAR PARÂMETROS
    // =====================================================

    $company_id = isset($_GET['company_id'])
        ? (int)$_GET['company_id']
        : 0;

    $user_id = isset($_GET['user_id'])
        ? (int)$_GET['user_id']
        : 0;

    $year = isset($_GET['year']) && is_numeric($_GET['year'])
        ? (int)$_GET['year']
        : (int)date('Y');

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

    $currentYear = (int)date('Y');

    // Intervalo do ano selecionado
    $yearStart = "{$year}-01-01";
    $yearEnd   = "{$year}-12-31";

    // Se for o ano atual, usa a data atual.
    // Caso contrário, considera o ano completo.
    if ($year === $currentYear) {

        $today = date('Y-m-d');

        $monthStart = date('Y-m-01');
        $monthEnd   = date('Y-m-t');

        $previousMonthStart = date(
            'Y-m-01',
            strtotime('first day of last month')
        );

        $previousMonthEnd = date(
            'Y-m-t',
            strtotime('last day of last month')
        );
    } else {

        $today = $yearEnd;

        // último mês do ano selecionado
        $monthStart = "{$year}-12-01";
        $monthEnd   = "{$year}-12-31";

        // novembro do ano selecionado
        $previousMonthStart = "{$year}-11-01";
        $previousMonthEnd   = "{$year}-11-30";
    }

    // =====================================================
    // ÚLTIMOS 3 MESES
    // =====================================================

    $current = [
        'start' => date(
            'Y-m-01',
            strtotime($today . ' -2 months')
        ),
        'end' => $today
    ];

    // =====================================================
    // 3 MESES ANTERIORES
    // =====================================================

    $previous = [
        'start' => date(
            'Y-m-01',
            strtotime($today . ' -5 months')
        ),
        'end' => date(
            'Y-m-t',
            strtotime($today . ' -3 months')
        )
    ];

    // =====================================================
    // TOTAL GLOBAL (STATUS 1 E 2)
    // =====================================================

    function getTotal($pdo, $start, $end, $company_id)
    {
        $sql = "
        SELECT COALESCE(SUM(final_total), 0)
        FROM invoices
        WHERE company_id = :company_id
        AND status IN (1, 2)
        AND DATE(issue_date) BETWEEN :start AND :end
    ";

        $stmt = $pdo->prepare($sql);

        $stmt->execute([
            'start' => $start,
            'end' => $end,
            'company_id' => $company_id
        ]);

        return (float) $stmt->fetchColumn();
    }



    // =====================================================
    // TOTAL LÍQUIDO (STATUS 3 E 4)
    // =====================================================

    function getTotalLiquid($pdo, $start, $end, $company_id)
    {
        $sql = "
        SELECT COALESCE(SUM(final_total), 0)
        FROM invoices
        WHERE company_id = :company_id
        AND status IN (3, 4)
        AND DATE(issue_date) BETWEEN :start AND :end
    ";

        $stmt = $pdo->prepare($sql);

        $stmt->execute([
            'start' => $start,
            'end' => $end,
            'company_id' => $company_id
        ]);

        return (float) $stmt->fetchColumn();
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
    // VOLUME Liquido
    // =====================================================

    $volumeLiquid = getTotalLiquid(
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
    SELECT 
        COUNT(DISTINCT i.id) AS total_i,

        COUNT(DISTINCT r.id) AS total_r,

        COUNT(DISTINCT c.id) AS total_c,

        (
            COUNT(DISTINCT i.id) +
            COUNT(DISTINCT r.id) +
            COUNT(DISTINCT c.id)
        ) AS total

    FROM invoices i

    LEFT JOIN receipts r
        ON r.invoice_id = i.id

    LEFT JOIN credit_notes c
        ON c.invoice_id = i.id

    WHERE i.company_id = :company_id
      AND i.issue_date >= :start
      AND i.issue_date < DATE_ADD(:end, INTERVAL 30 DAY)
");

    $documentosStmt->execute([
        ':company_id' => $company_id,
        ':start'      => $monthStart,
        ':end'        => $monthEnd
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
        AND created_at >= DATE_SUB(CURDATE(), INTERVAL 1 MONTH)
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
            DATE_FORMAT(i.issue_date, '%Y-%m') AS mes,

            ROUND(SUM(i.final_total), 2) AS total

        FROM invoices i

        WHERE i.company_id = :company_id
        AND i.status NOT IN (1, 2)
        AND i.issue_date >= :start
        AND i.issue_date < DATE_ADD(:end, INTERVAL 1 DAY)

        GROUP BY DATE_FORMAT(i.issue_date, '%Y-%m')

        ORDER BY mes ASC
    ";

    $stmtEvolucao = $pdo->prepare($evolucaoSql);

    $stmtEvolucao->execute([
        'start' => $yearStart,
        'end' => $today,
        'company_id' => $company_id
    ]);

    $evolucao = $stmtEvolucao->fetchAll(PDO::FETCH_ASSOC);


    // =========================================================
    // ANOS DA FACTURAÇÃO
    // =========================================================

    $yearsSql = "
            SELECT YEAR(i.created_at) AS ano, 
            COUNT(*) AS total FROM invoices i 
            WHERE i.company_id = :company_id
            GROUP BY YEAR(i.created_at) ORDER BY ano;
        ";

    $stmtYears = $pdo->prepare($yearsSql);

    $stmtYears->execute([
        'company_id' => $company_id
    ]);

    $yearsInvoices = $stmtYears->fetchAll(PDO::FETCH_ASSOC);


    // =====================================================
    // RESPONSE
    // =====================================================

    echo json_encode([
        'success' => true,

        'data' => [

            'top_clients' => $topClientes,

            'kpis' => [

                'volume_global' => round($volumeGlobal, 2),
                'volume_liquid' => round($volumeLiquid, 2),

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

            'evolucao' => $evolucao,
            'yearsInvoices' => $yearsInvoices
        ]
    ]);
} catch (Throwable $e) {

    http_response_code(500);

    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
