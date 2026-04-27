<?php

require_once '../../../app/config/db.php';

header('Content-Type: application/json');

$invoiceId = (int)($_POST['invoice_id'] ?? 0);

try {

    $pdo->beginTransaction();

    $items = $pdo->prepare("
        SELECT ii.*, i.code, i.track_stock
        FROM invoice_items ii
        JOIN items i ON i.id = ii.item_id
        WHERE ii.invoice_id = ?
    ");

    $items->execute([$invoiceId]);
    $rows = $items->fetchAll(PDO::FETCH_ASSOC);

    foreach ($rows as $r) {

        if (strpos(strtoupper($r['code']), 'SERV') === 0) continue;

        $stmt = $pdo->prepare("CALL update_stock_quantity(?, ?, ?)");
        $stmt->execute([$r['stock_id'], $r['item_id'], $r['quantity']]);

        while ($stmt->nextRowset()) {
        }
    }

    $pdo->prepare("
        UPDATE invoices
        SET status = 0, numero_validacao = NULL, reference = NULL
        WHERE id = ?
    ")->execute([$invoiceId]);

    $pdo->commit();

    echo json_encode(['success' => true]);
} catch (Throwable $e) {

    if ($pdo->inTransaction()) $pdo->rollBack();

    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
