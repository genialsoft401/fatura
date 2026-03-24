<?php
require_once '../../../app/config/db.php';
header('Content-Type: application/json'); 
session_start();


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
?>
