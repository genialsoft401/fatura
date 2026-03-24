<?php
require_once '../../../app/config/db.php';


if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $contactId = isset($_POST["id"]) ? intval($_POST["id"]) : 0;
    unset($_POST["id"]); // Remove o ID para não entrar na query
    unset($_POST["undefined"]); // Remove esse ser inutil

    if ($contactId === 0) {
        echo json_encode(["status" => "error", "message" => "ID inválido"]);
        exit;
    }
 
    $fieldsToUpdate = [];
    $params = [];

    foreach ($_POST as $field => $value) {
        $fieldsToUpdate[] = "`$field` = :$field";
        $params[":$field"] = $value;
    }

    if (empty($fieldsToUpdate)) {
        echo json_encode(["status" => "error", "message" => "Nenhuma alteração detectada"]);
        exit;
    }

    $query = "UPDATE contact SET " . implode(", ", $fieldsToUpdate) . " WHERE id = :id";

    $params[":id"] = $contactId;
 
    try {
        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        echo json_encode(["status" => "success", "message" => "Contato atualizado com sucesso"]);
    } catch (PDOException $e) {
        echo json_encode(["status" => "error", "message" => "Erro ao atualizar contato: " . $e->getMessage()]);
    }
}
?>
