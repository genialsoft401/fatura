<?php

require_once '../../../app/config/db.php';
header('Content-Type: application/json');

try {

    $company_id = isset($_GET['company_id'])
        ? (int)$_GET['company_id']
        : 0;

    $year = isset($_GET['year']) && is_numeric($_GET['year'])
        ? (int)$_GET['year']
        : (int)date('Y');

    if ($company_id <= 0) {
        throw new Exception('company_id é obrigatório');
    }

    $currentMonth = date('Y-m');
    $previousMonth = date('Y-m', strtotime('first day of last month'));

    $monthStart = date('Y-m-01');
    $monthEnd   = date('Y-m-t');

    $previousMonthStart = date('Y-m-01', strtotime('first day of last month'));
    $previousMonthEnd   = date('Y-m-t', strtotime('last day of last month'));

    function calcularCrescimento($atual, $anterior)
    {
        $atual = (float)$atual;
        $anterior = (float)$anterior;

        if ($anterior > 0) {
            return (($atual - $anterior) / $anterior) * 100;
        }

        return $atual > 0 ? 100 : 0;
    }

    // =====================================================
    // VERIFICA SE HÁ HISTÓRICO ANTES DE UMA DATA
    // Evita mostrar "-100%" quando não há dado anterior
    // suficiente para comparar.
    // =====================================================

    function hasSufficientHistory($pdo, $table, $dateColumn, $beforeDate, $company_id)
    {
        $stmt = $pdo->prepare("
            SELECT COUNT(*)
            FROM {$table}
            WHERE company_id = :company_id
            AND {$dateColumn} < :beforeDate
        ");

        $stmt->execute([
            'company_id' => $company_id,
            'beforeDate' => $beforeDate
        ]);

        return (int)$stmt->fetchColumn() > 0;
    }

    // =====================================================
    // SPARKLINE — FUNCIONÁRIOS ATIVOS (acumulado)
    // Aproximação por data de admissão: conta quantos
    // funcionários com status atual 'ativo' já tinham sido
    // admitidos até ao fim de cada mês. Não reflecte saídas
    // ocorridas no passado, apenas a tendência de admissões.
    // =====================================================

    function getSparklineEmployeesCount(
        $pdo,
        $company_id,
        $months = 6,
        $referenceDate = null
    ) {
        $referenceDate = $referenceDate ?? date('Y-m-d');

        $series = [];

        for ($i = $months - 1; $i >= 0; $i--) {

            $monthEnd = date(
                'Y-m-t',
                strtotime($referenceDate . " -{$i} months")
            );

            $stmt = $pdo->prepare("
                SELECT COUNT(*)
                FROM employees
                WHERE company_id = :company_id
                AND status = 'ativo'
                AND created_at <= :monthEnd
            ");

            $stmt->execute([
                'company_id' => $company_id,
                'monthEnd' => $monthEnd
            ]);

            $series[] = (int)$stmt->fetchColumn();
        }

        return $series;
    }

    // =====================================================
    // SPARKLINE — CUSTO SALARIAL (acumulado)
    // Mesma limitação da função anterior: soma o salário dos
    // funcionários ativos admitidos até ao fim de cada mês.
    // =====================================================

    function getSparklineSalarySum(
        $pdo,
        $company_id,
        $months = 6,
        $referenceDate = null
    ) {
        $referenceDate = $referenceDate ?? date('Y-m-d');

        $series = [];

        for ($i = $months - 1; $i >= 0; $i--) {

            $monthEnd = date(
                'Y-m-t',
                strtotime($referenceDate . " -{$i} months")
            );

            $stmt = $pdo->prepare("
                SELECT COALESCE(SUM(salary), 0)
                FROM employees
                WHERE company_id = :company_id
                AND status = 'ativo'
                AND created_at <= :monthEnd
            ");

            $stmt->execute([
                'company_id' => $company_id,
                'monthEnd' => $monthEnd
            ]);

            $series[] = round((float)$stmt->fetchColumn(), 2);
        }

        return $series;
    }

    // =====================================================
    // SPARKLINE — PAGAMENTOS PENDENTES POR MÊS
    // Conta por reference_month, status 'Pendente' (tabela
    // payroll).
    // =====================================================

    function getSparklinePendingPayroll(
        $pdo,
        $company_id,
        $months = 6,
        $referenceDate = null
    ) {
        $referenceDate = $referenceDate ?? date('Y-m-d');

        $start = date(
            'Y-m',
            strtotime($referenceDate . " -" . ($months - 1) . " months")
        );

        $stmt = $pdo->prepare("
            SELECT reference_month AS mes, COUNT(*) AS total
            FROM payroll
            WHERE company_id = :company_id
            AND status = 'Pendente'
            AND reference_month >= :start
            GROUP BY reference_month
            ORDER BY reference_month ASC
        ");

        $stmt->execute([
            'company_id' => $company_id,
            'start' => $start
        ]);

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $map = [];
        foreach ($rows as $r) {
            $map[$r['mes']] = (int)$r['total'];
        }

        $series = [];

        for ($i = $months - 1; $i >= 0; $i--) {
            $key = date('Y-m', strtotime($referenceDate . " -{$i} months"));
            $series[] = $map[$key] ?? 0;
        }

        return $series;
    }

    // =====================================================
    // SPARKLINE — FALTAS POR MÊS
    // Conta por mês da data da falta (absence_date). Ajusta o
    // nome da coluna/tabela se o teu schema for diferente.
    // =====================================================

    function getSparklineAbsences(
        $pdo,
        $company_id,
        $months = 6,
        $referenceDate = null
    ) {
        $referenceDate = $referenceDate ?? date('Y-m-d');

        $start = date(
            'Y-m-01',
            strtotime($referenceDate . " -" . ($months - 1) . " months")
        );

        $stmt = $pdo->prepare("
            SELECT DATE_FORMAT(date, '%Y-%m') AS mes, COUNT(*) AS total
            FROM attendance
            WHERE company_id = :company_id
            AND date >= :start
            AND date <= :end
            GROUP BY DATE_FORMAT(date, '%Y-%m')
            ORDER BY mes ASC
        ");

        $stmt->execute([
            'company_id' => $company_id,
            'start' => $start,
            'end' => $referenceDate
        ]);

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $map = [];
        foreach ($rows as $r) {
            $map[$r['mes']] = (int)$r['total'];
        }

        $series = [];

        for ($i = $months - 1; $i >= 0; $i--) {
            $key = date('Y-m', strtotime($referenceDate . " -{$i} months"));
            $series[] = $map[$key] ?? 0;
        }

        return $series;
    }

    // =====================================================
    // TOTAL FUNCIONÁRIOS ATIVOS
    // =====================================================

    $stmt = $pdo->prepare("
        SELECT COUNT(*)
        FROM employees
        WHERE company_id = :company_id
        AND status = 'ativo'
    ");

    $stmt->execute(['company_id' => $company_id]);

    $total_employes = (int)$stmt->fetchColumn();

    // =====================================================
    // CUSTO SALARIAL MENSAL (soma dos salários dos
    // funcionários ativos)
    // =====================================================

    $stmt = $pdo->prepare("
        SELECT COALESCE(SUM(salary), 0)
        FROM employees
        WHERE company_id = :company_id
        AND status = 'ativo'
    ");

    $stmt->execute(['company_id' => $company_id]);

    $total_salary = (float)$stmt->fetchColumn();

    // =====================================================
    // PAGAMENTOS PENDENTES (tabela payroll)
    // =====================================================

    $stmt = $pdo->prepare("
        SELECT COUNT(*)
        FROM payroll
        WHERE company_id = :company_id
        AND status = 'Pendente'
    ");

    $stmt->execute(['company_id' => $company_id]);

    $pending_payroll = (int)$stmt->fetchColumn();

    $stmt = $pdo->prepare("
        SELECT COUNT(*)
        FROM payroll
        WHERE company_id = :company_id
        AND status = 'Pendente'
        AND reference_month = :month
    ");

    $stmt->execute([
        'company_id' => $company_id,
        'month' => $currentMonth
    ]);

    $pending_payroll_current = (int)$stmt->fetchColumn();

    $stmt->execute([
        'company_id' => $company_id,
        'month' => $previousMonth
    ]);

    $pending_payroll_previous = (int)$stmt->fetchColumn();

    $temHistoricoPayroll = hasSufficientHistory(
        $pdo,
        'payroll',
        'reference_month',
        $previousMonth,
        $company_id
    );

    $crescimento_pending = calcularCrescimento(
        $pending_payroll_current,
        $pending_payroll_previous
    );


    // Lista de pagamentos pendentes (últimos 5)
    $stmt = $pdo->prepare("
        SELECT e.name, p.net_salary, e.position, p.status, p.reference_month
        FROM payroll as p
        JOIN employees as e ON e.id = p.employee_id
        WHERE p.company_id = :company_id
        AND p.status = 'Pendente'
        AND p.reference_month = :month
        ORDER BY p.reference_month DESC
        LIMIT 5
    ");

    $stmt->execute([
        'company_id' => $company_id,
        'month' => $currentMonth
    ]);

    $pending_payroll_list = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // =====================================================
    // FALTAS NO MÊS (tabela absences)
    // =====================================================

    $stmt = $pdo->prepare("
        SELECT COUNT(*)
        FROM attendance
        WHERE company_id = :company_id
        AND date >= :start
        AND date <= :end
    ");

    $stmt->execute([
        'company_id' => $company_id,
        'start' => $monthStart,
        'end' => $monthEnd
    ]);

    $absences_current = (int)$stmt->fetchColumn();

    $stmt->execute([
        'company_id' => $company_id,
        'start' => $previousMonthStart,
        'end' => $previousMonthEnd
    ]);

    $absences_previous = (int)$stmt->fetchColumn();

    $temHistoricoFaltas = hasSufficientHistory(
        $pdo,
        'attendance',
        'date',
        $previousMonthStart,
        $company_id
    );

    $crescimento_absences = calcularCrescimento(
        $absences_current,
        $absences_previous
    );

    // =====================================================
    // CUSTO SALARIAL MENSAL — evolução por ano
    // (gráfico principal do módulo RH), a partir do custo
    // real da folha de pagamento (payroll), filtrado por ano.
    // =====================================================

    $stmt = $pdo->prepare("
        SELECT
            reference_month AS mes,
            SUM(net_salary) AS total
        FROM payroll
        WHERE company_id = :company_id
        AND reference_month LIKE :year_pattern
        GROUP BY reference_month
        ORDER BY reference_month ASC
    ");

    $stmt->execute([
        'company_id' => $company_id,
        'year_pattern' => "{$year}-%"
    ]);

    $salary_evolution = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // =====================================================
    // CRESCIMENTO DE FUNCIONÁRIOS
    // =====================================================

    $stmt = $pdo->prepare("
        SELECT COUNT(*)
        FROM employees
        WHERE company_id = :company_id
        AND status = 'ativo'
        AND created_at < DATE_FORMAT(CURDATE(), '%Y-%m-01')
    ");

    $stmt->execute(['company_id' => $company_id]);

    $total_employes_prev = (int)$stmt->fetchColumn();

    $crescimento_employes = calcularCrescimento(
        $total_employes,
        $total_employes_prev
    );

    // =====================================================
    // CRESCIMENTO DO CUSTO SALARIAL
    // =====================================================

    $stmt = $pdo->prepare("
        SELECT COALESCE(SUM(salary), 0)
        FROM employees
        WHERE company_id = :company_id
        AND status = 'ativo'
        AND created_at < DATE_FORMAT(CURDATE(), '%Y-%m-01')
    ");

    $stmt->execute(['company_id' => $company_id]);

    $total_salary_prev = (float)$stmt->fetchColumn();

    $crescimento_salary = calcularCrescimento(
        $total_salary,
        $total_salary_prev
    );

    // =====================================================
    // LISTA: PAGAMENTOS PENDENTES
    // =====================================================

    $stmt = $pdo->prepare("
        SELECT
            p.id,
            e.name AS name,
            p.reference_month,
            p.net_salary,
            p.status
        FROM payroll p
        INNER JOIN employees e
            ON e.id = p.employee_id
        WHERE p.company_id = :company_id
        AND p.status = 'Pendente'
        ORDER BY p.reference_month DESC
        LIMIT 5
    ");

    $stmt->execute(['company_id' => $company_id]);

    $pending_payroll_list = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // =====================================================
    // LISTA: ÚLTIMAS FALTAS REGISTADAS
    // =====================================================

    $stmt = $pdo->prepare("
        SELECT
            a.id,
            e.name AS name,
            a.date,
            a.justification
        FROM attendance as a
        INNER JOIN employees e
            ON e.id = a.employee_id
        WHERE a.company_id = :company_id
        ORDER BY a.date DESC
        LIMIT 5
    ");

    $stmt->execute(['company_id' => $company_id]);

    $recent_absences = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // =====================================================
    // SPARKLINES (últimos 12 meses até hoje)
    // Um array por KPI, usado nos mini-gráficos dos cards
    // (canvas#spark5, #spark6, #spark7, #spark8)
    // =====================================================

    $sparkMonths = 12;
    $sparkReference = date('Y-m-d');

    $sparklines = [

        'total_employes' => getSparklineEmployeesCount(
            $pdo,
            $company_id,
            $sparkMonths,
            $sparkReference
        ),

        'total_salary' => getSparklineSalarySum(
            $pdo,
            $company_id,
            $sparkMonths,
            $sparkReference
        ),

        'pending_payroll' => getSparklinePendingPayroll(
            $pdo,
            $company_id,
            $sparkMonths,
            $sparkReference
        ),

        'absences' => getSparklineAbsences(
            $pdo,
            $company_id,
            $sparkMonths,
            $sparkReference
        ),
    ];

    echo json_encode([
        'success' => true,
        'data' => [
            'kpis' => [
                'total_employes'  => $total_employes,
                'total_salary'    => $total_salary,
                'pending_payroll' => $pending_payroll,
                'absences'        => $absences_current,

                'increase_employes' => round($crescimento_employes, 2),
                'increase_salary'   => round($crescimento_salary, 2),

                'increase_pending' => $temHistoricoPayroll
                    ? round($crescimento_pending, 2)
                    : null,

                'increase_absences' => $temHistoricoFaltas
                    ? round($crescimento_absences, 2)
                    : null,
            ],
            'pending_payroll_list' => $pending_payroll_list,
            'rh_recent_absences_list' => $recent_absences,
            'salary_evolution' => $salary_evolution,

            // séries curtas para os sparklines dos cards
            'sparklines' => $sparklines
        ]
    ]);
} catch (Throwable $e) {

    http_response_code(500);

    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
