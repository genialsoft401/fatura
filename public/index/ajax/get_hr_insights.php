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
    $absences_month = (int) $stmt->fetch(PDO::FETCH_ASSOC)['total'];

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
    // 🔹 RESPONSE FINAL
    // =========================
    echo json_encode([
        'success' => true,
        'data' => [
            'kpis' => [
                'total_employes' => $total_employes,
                'total_salary' => $total_salary,
                'pending_vacations' => $pending_vacations,
                'absences_month' => $absences_month
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