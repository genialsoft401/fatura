<?php
require_once '../../../app/config/db.php';
session_start();

date_default_timezone_set($_SESSION["timezone"]);
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Lista de campos obrigatórios
    $requiredFields = ["company_id", "name", "email", "type", "country", "city", "telephone", "telephone_ddi", "contributor", "address"];

    // Verifica se algum campo obrigatório está vazio
    foreach ($requiredFields as $field) {
        if (!isset($_POST[$field]) || trim($_POST[$field]) === "") {
            echo json_encode(["status" => "error", "message" => "O campo '$field' é obrigatório."]);
            exit;
        }
    }

    $fields = [];
    $placeholders = [];
    $values = [];

    foreach ($_POST as $field => $value) {
        $fields[] = "`$field`";
        $placeholders[] = ":$field";
        $values[":$field"] = $value;
    }

    if (empty($fields)) {
        echo json_encode(["status" => "error", "message" => "Nenhum dado enviado"]);
        exit;
    }

    $query = "INSERT INTO contact (" . implode(", ", $fields) . ") VALUES (" . implode(", ", $placeholders) . ")";

    try {
        $stmt = $pdo->prepare($query);
        $stmt->execute($values);
        echo json_encode(["status" => "success", "message" => "Usuário cadastrado com sucesso"]);
    } catch (PDOException $e) {
        echo json_encode(["status" => "error", "message" => "Erro ao salvar usuário: " . $e->getMessage()]);
    }
}
?>
