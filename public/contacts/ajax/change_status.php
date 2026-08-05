<?php
require_once '../../../app/config/db.php';


if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $contactId = isset($_POST["id"]) ? intval($_POST["id"]) : 0;
    $status = isset($_POST["status"]) ? intval($_POST["status"]) : 0;
    unset($_POST["id"]); // Remove o ID para não entrar na query
    unset($_POST["undefined"]); // Remove esse ser inutil

    $query = "UPDATE contact SET is_active = :status WHERE id = :id";

    $params[":id"] = $contactId;
    $params[":status"] = $status;

    try {
        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        echo json_encode(["status" => "success", "message" =>  $status == 1 ? "Contato arquivado!" : "Contato reativado!"]);
    } catch (PDOException $e) {
        echo json_encode(["status" => "error", "message" => "Erro ao arquivar contato: " . $e->getMessage()]);
    }
}
