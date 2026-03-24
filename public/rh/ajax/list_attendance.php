<?php
require_once '../../../app/config/db.php';

$query = "
    SELECT a.*, e.name AS employee_name
    FROM attendance a
    JOIN employees e ON a.employee_id = e.id
";

$conditions = [];
$params = [];

if (!empty($_REQUEST['mes'])) {
    $conditions[] = "DATE_FORMAT(a.date, '%Y-%m') = ?";
    $params[] = $_REQUEST['mes'];
}

if (!empty($_REQUEST['funcionario'])) {
    $conditions[] = "a.employee_id = ?";
    $params[] = $_REQUEST['funcionario'];
}

if (count($conditions) > 0) {
    $query .= " WHERE " . implode(' AND ', $conditions);
}

$query .= " ORDER BY a.date DESC";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode(['data' => $data]);
