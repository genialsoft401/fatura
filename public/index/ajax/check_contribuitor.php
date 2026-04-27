<?php
require_once '../../../app/config/db.php';
header('Content-Type: application/json');
session_start();


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $registration_number = $_POST['registration_number'];

    $stmt = $pdo->prepare("SELECT id FROM companies WHERE registration_number = :registration_number");
    $stmt->bindParam(':registration_number', $registration_number);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        echo json_encode(['exists' => true]);
    } else {
        echo json_encode(['exists' => false]);
    }
}
