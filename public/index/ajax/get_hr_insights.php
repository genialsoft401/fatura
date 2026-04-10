<?php
require_once __DIR__ . '../../../../app/config/db.php';
header('Content-Type: application/json');

try {

    $company_id = isset($_GET['company_id']) ? (int) $_GET['company_id'] : null;

    if (!$company_id) {
        echo json_encode([
            'success' => false,
            'error' => 'company_id é obrigatório'
        ]);
        exit;
    }

    function calcularCrescimento($atual, $anterior)
    {
        if ($anterior > 0) {
            return (($atual - $anterior) / $anterior) * 100;
        }
        return $atual > 0 ? 100 : 0;
    }

    // =========================
    // 🔹 TOTAL FUNCIONÁRIOS
    // =========================
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as total 
        FROM employees 
        WHERE company_id = :company_id
    ");
    $stmt->execute(['company_id' => $company_id]);
    $total_employes = (int) $stmt->fetch(PDO::FETCH_ASSOC)['total'];

    // =========================
    // 🔹 SALÁRIO TOTAL
    // =========================
    $stmt = $pdo->prepare("
        SELECT SUM(salary) as total_salary 
        FROM employees 
        WHERE company_id = :company_id
    ");
    $stmt->execute(['company_id' => $company_id]);
    $total_salary = (float) ($stmt->fetch(PDO::FETCH_ASSOC)['total_salary'] ?? 0);

    // =========================
    // 🔹 FÉRIAS PENDENTES
    // =========================
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as total 
        FROM vacations 
        WHERE company_id = :company_id 
        AND status = 'pendente'
    ");
    $stmt->execute(['company_id' => $company_id]);
    $pending_vacations = (int) $stmt->fetch(PDO::FETCH_ASSOC)['total'];

    // =========================
    // 🔹 FALTAS TOTAL (attendance)
    // =========================
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as total 
        FROM attendance 
        WHERE company_id = :company_id 
        AND type = 'falta'
        AND YEAR(date) = YEAR(CURRENT_DATE())
    ");
    $stmt->execute(['company_id' => $company_id]);
    $absences_year = (int) $stmt->fetch(PDO::FETCH_ASSOC)['total'];


    // =========================
    // 🔹 FALTAS DO MÊS (attendance)
    // =========================
    $stmt = $pdo->prepare("
    SELECT COUNT(*) as total 
    FROM attendance 
    WHERE company_id = :company_id 
    AND type = 'falta'
    AND MONTH(date) = MONTH(CURRENT_DATE())
    AND YEAR(date) = YEAR(CURRENT_DATE())
");
    $stmt->execute(['company_id' => $company_id]);
    $current_absences = (int) $stmt->fetch(PDO::FETCH_ASSOC)['total'];

    $stmt = $pdo->prepare("
    SELECT COUNT(*) as total 
    FROM attendance 
    WHERE company_id = :company_id 
    AND type = 'falta'
    AND MONTH(date) = MONTH(DATE_SUB(CURRENT_DATE(), INTERVAL 1 MONTH))
    AND YEAR(date) = YEAR(DATE_SUB(CURRENT_DATE(), INTERVAL 1 MONTH))
");
    $stmt->execute(['company_id' => $company_id]);
    $previous_absences = (int) $stmt->fetch(PDO::FETCH_ASSOC)['total'];

    $absence_growth = $previous_absences > 0
        ? (($current_absences - $previous_absences) / $previous_absences) * 100
        : ($current_absences > 0 ? 100 : 0);


    // =========================
    // 🔹 LISTA DE FÉRIAS
    // =========================
    $stmt = $pdo->prepare("
        SELECT 
            e.name, 
            v.start_date, 
            v.end_date, 
            v.status
        FROM vacations v
        JOIN employees e ON e.id = v.employee_id
        WHERE v.company_id = :company_id
        ORDER BY v.start_date DESC
        LIMIT 5
    ");
    $stmt->execute(['company_id' => $company_id]);
    $vacations = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // =========================
    // 🔹 TOP FALTAS (attendance)
    // =========================
    $stmt = $pdo->prepare("
        SELECT 
            e.name, 
            COUNT(a.id) as total_absences,
            CASE 
                WHEN LOWER(a.justification) LIKE '%medic%' THEN 'Doença'
                WHEN LOWER(a.justification) LIKE '%injust%' THEN 'Injustificada'
                ELSE 'Outros'
            END as tipo_falta
        FROM attendance a
        JOIN employees e ON e.id = a.employee_id
        WHERE a.company_id = :company_id
        AND a.type = 'falta'
        GROUP BY e.id
        ORDER BY total_absences DESC
        LIMIT 5
    ");
    $stmt->execute(['company_id' => $company_id]);
    $top_absences = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // =========================
    // 🔹 Cresimento employee
    // =========================

    // Funcionários mês atual
    $stmt = $pdo->prepare("
    SELECT COUNT(*) 
    FROM employees 
    WHERE company_id = :company_id
");
    $stmt->execute(['company_id' => $company_id]);
    $total_employes = (int)$stmt->fetchColumn();

    // Funcionários mês anterior (exemplo simples)
    $stmt = $pdo->prepare("
    SELECT COUNT(*) 
    FROM employees 
    WHERE company_id = :company_id
    AND created_at < DATE_FORMAT(CURDATE(), '%Y-%m-01')
");
    $stmt->execute(['company_id' => $company_id]);
    $total_employes_prev = (int)$stmt->fetchColumn();

    $crescimento_employes = calcularCrescimento($total_employes, $total_employes_prev);


    // =========================
    // 🔹 Crescimento Salary
    // =========================


    $stmt = $pdo->prepare("
    SELECT SUM(salary) 
    FROM employees 
    WHERE company_id = :company_id
    ");
    $stmt->execute(['company_id' => $company_id]);
    $total_salary = (float)$stmt->fetchColumn();

    $total_salary_prev = $total_salary;

    $crescimento_salary = calcularCrescimento($total_salary, $total_salary_prev);


    // =========================
    // 🔹 Feria increase
    // =========================

    $stmt = $pdo->prepare("
        SELECT COUNT(*) 
        FROM vacations 
        WHERE company_id = :company_id 
        AND status = 'pendente'
        AND start_date >= DATE_FORMAT(CURDATE(), '%Y-%m-01')
    ");


    $stmt->execute(['company_id' => $company_id]);
    $pending_vacations = (int)$stmt->fetchColumn();

    // Anterior
    $stmt = $pdo->prepare("
    SELECT COUNT(*) 
    FROM vacations 
    WHERE company_id = :company_id 
    AND status = 'pendente'
    AND start_date < DATE_FORMAT(CURDATE(), '%Y-%m-01')
");
    $stmt->execute(['company_id' => $company_id]);
    $pending_vacations_prev = (int)$stmt->fetchColumn();

    $crescimento_vacations = calcularCrescimento($pending_vacations, $pending_vacations_prev);



    // =========================
    // 🔹 RESPONSE FINAL
    // =========================
    echo json_encode([
        'success' => true,
        'data' => [
            'kpis' => [
                'total_employes' => $total_employes,
                'total_salary' => $total_salary,
                'pending_vacations' => $pending_vacations,
                'absences_year' => $absences_year,
                'absences_month' => $absence_growth,
                'increase_employes' => $crescimento_employes,
                'increase_salary' => $crescimento_salary,
                'increase_vacations' => $crescimento_vacations
            ],
            'vacations' => $vacations,
            'top_absences' => $top_absences
        ]
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
