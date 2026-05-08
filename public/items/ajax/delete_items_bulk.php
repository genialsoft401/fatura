<?php
require_once '../../../app/config/db.php';
session_start();

header('Content-Type: application/json');

try {

    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $companyId = (int)($_SESSION['user']['company_id'] ?? 0);
    $ids = $_POST['ids'] ?? [];

    if ($companyId <= 0) {
        throw new Exception("Sessão inválida.");
    }

    if (!is_array($ids) || empty($ids)) {
        throw new Exception("Nenhum item selecionado.");
    }

    $success = [];
    $failed = [];

    foreach ($ids as $id) {

        try {

            $stmt = $pdo->prepare("CALL sp_delete_item(?, ?)");
            $stmt->execute([$companyId, (int)$id]);

            // limpar TODOS os resultsets da procedure
            while ($stmt->nextRowset()) {
            }

            $success[] = (int)$id;
        } catch (Throwable $e) {

            // NÃO deixar quebrar loop
            $failed[] = [
                "id" => (int)$id,
                "error" => cleanError($e->getMessage())
            ];
        }
    }

    echo json_encode([
        "success" => count($success) > 0,
        "message" => count($success) . " item(s) eliminados.",
        "deleted_ids" => $success,
        "failed" => $failed
    ]);
} catch (Throwable $e) {

    http_response_code(500);

    echo json_encode([
        "success" => false,
        "message" => "Erro interno ao eliminar itens.",
        "error" => cleanError($e->getMessage())
    ]);
}

/**
 * Limpa mensagens técnicas do MySQL
 */
function cleanError($msg)
{

    // remove SQLSTATE feio
    if (strpos($msg, 'SQLSTATE') !== false) {
        return "Não foi possível eliminar este item (em uso ou protegido).";
    }

    return $msg;
}
