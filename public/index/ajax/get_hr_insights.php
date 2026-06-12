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

    $startDate = "{$year}-01-01";
    $endDate   = ($year + 1) . "-01-01";

    function calcularCrescimento($atual, $anterior)
    {
        if ($anterior > 0) {
            return (($atual - $anterior) / $anterior) * 100;
        }

        return $atual > 0 ? 100 : 0;
    }

    // =====================================================
    // TOTAL FUNCIONÁRIOS
    // =====================================================

    $stmt = $pdo->prepare("
        SELECT COUNT(*) 
        FROM employees
        WHERE company_id = :company_id
    ");

    $stmt->execute([
        'company_id' => $company_id
    ]);

    $total_employes = (int)$stmt->fetchColumn();

    // =====================================================
    // TOTAL SALÁRIOS
    // =====================================================

    $stmt = $pdo->prepare("
        SELECT COALESCE(SUM(salary), 0)
        FROM employees
        WHERE company_id = :company_id
    ");

    $stmt->execute([
        'company_id' => $company_id
    ]);

    $total_salary = (float)$stmt->fetchColumn();

    // =====================================================
    // FÉRIAS PENDENTES
    // =====================================================

    $stmt = $pdo->prepare("
        SELECT COUNT(*)
        FROM vacations
        WHERE company_id = :company_id
        AND status = 'pendente'
    ");

    $stmt->execute([
        'company_id' => $company_id
    ]);

    $pending_vacations = (int)$stmt->fetchColumn();

    // =====================================================
    // FALTAS NO ANO
    // =====================================================

    $stmt = $pdo->prepare("
        SELECT COUNT(*)
        FROM attendance
        WHERE company_id = :company_id
        AND type = 'falta'
        AND date >= :start_date
        AND date < :end_date
    ");

    $stmt->execute([
        'company_id' => $company_id,
        'start_date' => $startDate,
        'end_date'   => $endDate
    ]);

    $absences_year = (int)$stmt->fetchColumn();

    // =====================================================
    // FALTAS MÊS ATUAL
    // =====================================================

    $stmt = $pdo->prepare("
        SELECT COUNT(*)
        FROM attendance
        WHERE company_id = :company_id
        AND type = 'falta'
        AND MONTH(date) = MONTH(CURDATE())
        AND YEAR(date) = YEAR(CURDATE())
    ");

    $stmt->execute([
        'company_id' => $company_id
    ]);

    $current_absences = (int)$stmt->fetchColumn();

    // =====================================================
    // FALTAS MÊS ANTERIOR
    // =====================================================

    $stmt = $pdo->prepare("
        SELECT COUNT(*)
        FROM attendance
        WHERE company_id = :company_id
        AND type = 'falta'
        AND MONTH(date) = MONTH(DATE_SUB(CURDATE(), INTERVAL 1 MONTH))
        AND YEAR(date) = YEAR(DATE_SUB(CURDATE(), INTERVAL 1 MONTH))
    ");

    $stmt->execute([
        'company_id' => $company_id
    ]);

    $previous_absences = (int)$stmt->fetchColumn();

    $absence_growth = calcularCrescimento(
        $current_absences,
        $previous_absences
    );

    // =====================================================
    // CRESCIMENTO FUNCIONÁRIOS
    // =====================================================

    $stmt = $pdo->prepare("
        SELECT COUNT(*)
        FROM employees
        WHERE company_id = :company_id
        AND created_at < DATE_FORMAT(CURDATE(), '%Y-%m-01')
    ");

    $stmt->execute([
        'company_id' => $company_id
    ]);

    $total_employes_prev = (int)$stmt->fetchColumn();

    $crescimento_employes = calcularCrescimento(
        $total_employes,
        $total_employes_prev
    );

    // =====================================================
    // CRESCIMENTO SALÁRIOS
    // =====================================================

    $stmt = $pdo->prepare("
        SELECT COALESCE(SUM(salary),0)
        FROM employees
        WHERE company_id = :company_id
        AND created_at < DATE_FORMAT(CURDATE(), '%Y-%m-01')
    ");

    $stmt->execute([
        'company_id' => $company_id
    ]);

    $total_salary_prev = (float)$stmt->fetchColumn();

    $crescimento_salary = calcularCrescimento(
        $total_salary,
        $total_salary_prev
    );

    // =====================================================
    // CRESCIMENTO FÉRIAS
    // =====================================================

    $stmt = $pdo->prepare("
        SELECT COUNT(*)
        FROM vacations
        WHERE company_id = :company_id
        AND status = 'pendente'
        AND start_date >= DATE_FORMAT(CURDATE(), '%Y-%m-01')
    ");

    $stmt->execute([
        'company_id' => $company_id
    ]);

    $current_pending_vacations = (int)$stmt->fetchColumn();

    $stmt = $pdo->prepare("
        SELECT COUNT(*)
        FROM vacations
        WHERE company_id = :company_id
        AND status = 'pendente'
        AND start_date < DATE_FORMAT(CURDATE(), '%Y-%m-01')
    ");

    $stmt->execute([
        'company_id' => $company_id
    ]);

    $pending_vacations_prev = (int)$stmt->fetchColumn();

    $crescimento_vacations = calcularCrescimento(
        $current_pending_vacations,
        $pending_vacations_prev
    );

    // =====================================================
    // ÚLTIMAS FÉRIAS
    // =====================================================

    $stmt = $pdo->prepare("
        SELECT
            v.id,
            e.name AS employee_name,
            v.start_date,
            v.end_date,
            v.status
        FROM vacations v
        INNER JOIN employees e
            ON e.id = v.employee_id
        WHERE v.company_id = :company_id
        ORDER BY v.start_date DESC
        LIMIT 5
    ");

    $stmt->execute([
        'company_id' => $company_id
    ]);

    $vacations = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // =====================================================
    // TOP FALTAS
    // =====================================================

    $stmt = $pdo->prepare("
        SELECT
            e.name,
            COUNT(*) AS total_absences
        FROM attendance a
        INNER JOIN employees e
            ON e.id = a.employee_id
        WHERE a.company_id = :company_id
        AND a.type = 'falta'
        GROUP BY a.employee_id, e.name
        ORDER BY total_absences DESC
        LIMIT 5
    ");

    $stmt->execute([
        'company_id' => $company_id
    ]);

    $top_absences = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'data' => [
            'kpis' => [
                'total_employes'     => $total_employes,
                'total_salary'       => $total_salary,
                'pending_vacations'  => $pending_vacations,
                'absences_year'      => $absences_year,
                'absences_month'     => $current_absences,
                'absence_growth'     => round($absence_growth, 2),
                'increase_employes'  => round($crescimento_employes, 2),
                'increase_salary'    => round($crescimento_salary, 2),
                'increase_vacations' => round($crescimento_vacations, 2)
            ],
            'vacations' => $vacations,
            'top_absences' => $top_absences
        ]
    ]);
} catch (Throwable $e) {

    http_response_code(500);

    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
