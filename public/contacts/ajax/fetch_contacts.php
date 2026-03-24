<?php
require_once '../../../app/config/db.php';
header('Content-Type: application/json'); 
session_start();


try {
    $sql = "SELECT id, name, email, telephone, country, city FROM contact WHERE company_id = :company_id AND (is_active = 1)";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(":company_id", $_SESSION['user']['company_id'], PDO::PARAM_INT);
    $stmt->execute();
    
    $contacts = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($contacts);
} catch (PDOException $e) {
    echo json_encode(["error" => "Erro ao buscar contatos: " . $e->getMessage()]);
}
?>
