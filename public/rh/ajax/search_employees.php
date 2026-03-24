<?php
require_once '../../../app/config/db.php';
session_start();

$company_id = $_SESSION['user']['company_id'];
$term = $_GET['term'] ?? '';

$sql = "SELECT id, name, salary, position FROM employees 
        WHERE company_id = ? AND status = 'ativo' AND name LIKE ? 
        ORDER BY name ASC LIMIT 20";

$stmt = $pdo->prepare($sql);
$stmt->execute([$company_id, "%$term%"]);

$results = [];
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $results[] = [
        'id' => $row['id'],
        'text' => $row['name'],
        'salary' => $row['salary'],
        'position' => $row['position']
    ];
}

echo json_encode($results);
