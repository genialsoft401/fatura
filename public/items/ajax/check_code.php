<?php
require_once '../../../app/config/db.php';
header('Content-Type: application/json; charset=utf-8');
session_start();
ini_set('display_errors', 0);
error_reporting(0);



if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $codigo = $_POST['codigo'];

    $stmt = $pdo->prepare("SELECT id FROM items WHERE code = :codigo and id_company = :id");
    $stmt->bindParam(':codigo', $codigo);
    $stmt->bindParam(':id', $_SESSION['user']['company_id']);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        echo json_encode(['exists' => true]);
    } else {
        echo json_encode(['exists' => false]);
    }
}
