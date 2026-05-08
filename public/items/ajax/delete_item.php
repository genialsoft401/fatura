<?php
require_once '../../../app/config/db.php';

header('Content-Type: application/json');
session_start();

try {

    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception("Método inválido.");
    }

    $companyId = (int)($_SESSION['user']['company_id'] ?? 0);
    $itemId    = (int)($_POST['id'] ?? 0);

    if ($companyId <= 0 || $itemId <= 0) {
        throw new Exception("Dados inválidos.");
    }

    $pdo->beginTransaction();

    try {

        $stmt = $pdo->prepare("CALL sp_delete_item(?, ?)");
        $stmt->execute([$companyId, $itemId]);

        // limpar todos os resultsets da procedure
        while ($stmt->nextRowset()) {}

    } catch (Throwable $e) {

        // IMPORTANTÍSSIMO: não deixar estourar 500 técnico
        throw new Exception(cleanError($e->getMessage()));
    }

    $pdo->commit();

    echo json_encode([
        "success" => true,
        "message" => "Item removido com sucesso"
    ]);

} catch (Throwable $e) {

    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    http_response_code(200); // evita “500 falso” no frontend

    echo json_encode([
        "success" => false,
        "message" => $e->getMessage()
    ]);
}

/**
 * Remove mensagens técnicas do MySQL
 */
function cleanError($msg) {

    if (strpos($msg, 'SQLSTATE') !== false ||
        strpos($msg, '1644') !== false) {
        return "Não é possível eliminar este item (está a ser utilizado no sistema).";
    }

    return "Erro ao eliminar item.";
}