<?php

require_once '../../../app/config/db.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $contactId = $_POST['id'] ?? null;

    if (!$contactId) {
        echo json_encode(["success" => false, "message" => "ID do contato não fornecido."]);
        exit;
    }

    try {
        // Se existir fatura vinculada, não exclui de verdade: apenas arquiva (is_active=0)
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM invoices WHERE contact_id = :id");
        $stmt->bindParam(":id", $contactId, PDO::PARAM_INT);
        $stmt->execute();
        $hasInvoices = ((int)$stmt->fetchColumn() > 0);

        if ($hasInvoices) {
            $stmt = $pdo->prepare("UPDATE contact SET is_active = 0 WHERE id = :id");
            $stmt->bindParam(":id", $contactId, PDO::PARAM_INT);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                echo json_encode([
                    "success" => true,
                    "archived" => true,
                    "message" => "Não foi possível excluir, pois existem facturas vinculadas. O registo foi arquivado."
                ]);
            } else {
                echo json_encode(["success" => false, "message" => "Empresa não encontrada."]);
            }
            exit;
        }

        // Sem faturas vinculadas: exclui de verdade
        $stmt = $pdo->prepare("DELETE FROM contact WHERE id = :id");
        $stmt->bindParam(":id", $contactId, PDO::PARAM_INT);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            echo json_encode(["success" => true, "archived" => false]);
        } else {
            echo json_encode(["success" => false, "message" => "Empresa não encontrada."]);
        }
    } catch (PDOException $e) {
        echo json_encode(["success" => false, "message" => "Erro ao excluir Empresa: " . $e->getMessage()]);
    }
} else {
    echo json_encode(["success" => false, "message" => "Método inválido."]);
}
