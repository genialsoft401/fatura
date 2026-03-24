<?php
require_once '../../../app/config/db.php';
header('Content-Type: application/json');
session_start();

if (!isset($_GET['id'])) {
    echo json_encode(['success' => false, 'message' => 'ID da empresa não informado.']);
    exit;
}

$companyId = intval($_GET['id']);

$query = "SELECT * FROM companies WHERE id = :companyId";
$stmt = $pdo->prepare($query);
$stmt->bindParam(':companyId', $companyId, PDO::PARAM_INT);
$stmt->execute();
$company = $stmt->fetch(PDO::FETCH_ASSOC);

if ($company) {
    echo json_encode(['success' => true, 'data' => $company]);
} else {
    echo json_encode(['success' => false, 'message' => 'Empresa não encontrada.']);
}
