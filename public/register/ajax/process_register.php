<?php
require_once '../../../app/config/db.php';


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $phone = trim($_POST['phone']);
    $gender = trim($_POST['gender']);
    $created_at = date('Y-m-d H:i:s');

    $company_name = trim($_POST['company_name']);
    $registration_number = trim($_POST['registration_number']);
    $company_phone = trim($_POST['company_phone']);
    $website = trim($_POST['website']);

    try {
        $pdo->beginTransaction();

        // Inserir empresa (plano trial 30 dias)
        $plan_code = 'BXPERT_BAZA';
        $plan_started_at = date('Y-m-d');
        $plan_expires_at = date('Y-m-d', strtotime('+30 days'));

        $stmt = $pdo->prepare("INSERT INTO companies (name, registration_number, phone, website, created_at, plan_code, plan_started_at, plan_expires_at, plan_status, is_active, blocked) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'active', 1, 0)");
        $stmt->execute([$company_name, $registration_number, $company_phone, $website, $created_at, $plan_code, $plan_started_at, $plan_expires_at]);
        $company_id = $pdo->lastInsertId();

        // Inserir usuário
        $stmt = $pdo->prepare("INSERT INTO users (username, name, email, password, phone, gender, created_at) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$username, $name, $email, $password, $phone, $gender, $created_at]);
        $user_id = $pdo->lastInsertId();

        // Associar usuário à empresa
        $stmt = $pdo->prepare("INSERT INTO company_has_user (company_id, user_id, role, created_at) VALUES (?, ?, ?, ?)");
        $stmt->execute([$company_id, $user_id, 3, $created_at]);

        $pdo->commit();

        echo json_encode(["success" => true]);
    } catch (Exception $e) {
        $pdo->rollBack();
        echo json_encode(["success" => false, "message" => "Erro ao registrar: " . $e->getMessage()]);
    }
}
