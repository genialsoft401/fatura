<?php

require_once '../../../app/config/db.php';
require_once '../../../app/helpers/subscription.php';

header('Content-Type: application/json');

session_start();

error_reporting(E_ALL);
ini_set('display_errors', 1);

try {

    // =========================
    // VALIDAR SESSÃO
    // =========================
    $companyIdSession = (int)($_SESSION['user']['company_id'] ?? 0);

    if (!$companyIdSession) {
        throw new Exception("Sessão inválida.");
    }

    // =========================
    // RECEBER DADOS
    // =========================
    $invoiceData = $_POST['invoice'] ?? [];
    $itemData = $_POST['items'] ?? [];

    if (empty($invoiceData)) {
        throw new Exception("Dados da fatura não enviados.");
    }

    if (empty($itemData)) {
        throw new Exception("Nenhum item enviado.");
    }

    // =========================
    // CONVERTER FORM DATA
    // =========================
    $fatura = [];

    foreach ($invoiceData as $field) {
        if (isset($field['name'], $field['value'])) {
            $fatura[$field['name']] = $field['value'];
        }
    }

    // =========================
    // DETECTAR EDIÇÃO
    // =========================
    $editInvoiceId = !empty($fatura['edit_invoice_id'])
        ? (int)$fatura['edit_invoice_id']
        : 0;

    unset($fatura['edit_invoice_id']);

    // =========================
    // INICIAR TRANSAÇÃO
    // =========================
    $pdo->beginTransaction();

    subscription_assert_active($pdo, $companyIdSession);

    if ($editInvoiceId <= 0) {
        subscription_check_limit($pdo, $companyIdSession, 'invoice');
    }

    // =========================
    // CONTACTO
    // =========================
    if (!empty($fatura['contact_id'])) {
        $contactId = (int)$fatura['contact_id'];
    } else {

        if (empty($fatura['name']) || empty($fatura['email'])) {
            throw new Exception("Nome e e-mail do contato são obrigatórios.");
        }

        $stmtContact = $pdo->prepare("
            SELECT id FROM contact
            WHERE email = ? AND company_id = ?
            LIMIT 1
        ");

        $stmtContact->execute([
            trim($fatura['email']),
            $companyIdSession
        ]);

        $existingContact = $stmtContact->fetch(PDO::FETCH_ASSOC);

        if ($existingContact) {

            $contactId = (int)$existingContact['id'];
        } else {

            $stmtInsertContact = $pdo->prepare("
                INSERT INTO contact
                (name, email, contributor, address, po_box, country, city, company_id)
                VALUES (?,?,?,?,?,?,?,?)
            ");

            $stmtInsertContact->execute([
                trim($fatura['name']),
                trim($fatura['email']),
                $fatura['contributor'] ?? null,
                $fatura['address'] ?? null,
                $fatura['po_box'] ?? null,
                $fatura['country'] ?? null,
                $fatura['city'] ?? null,
                $companyIdSession
            ]);

            $contactId = (int)$pdo->lastInsertId();
        }
    }

    foreach (
        [
            'contact_id',
            'name',
            'email',
            'telephone',
            'address',
            'contributor',
            'po_box',
            'country',
            'city'
        ] as $field
    ) {
        unset($fatura[$field]);
    }

    // =========================
    // FATURA DB
    // =========================
    $invoiceDbFields = [
        'contact_id' => $contactId,
        'company_id' => $companyIdSession,
        'user_id' => (int)($fatura['user_id'] ?? 0),
        'issue_date' => $fatura['issue_date'] ?? date('Y-m-d'),
        'due_date' => (int)($fatura['due_date'] ?? 0),
        'reference' => $fatura['reference'] ?? null,
        'observation' => $fatura['observation'] ?? null,
        'series' => $fatura['series'] ?? null,
        'retention' => (float)($fatura['retention'] ?? 0),
        'currency' => $fatura['currency'] ?? 'AOA',
        'manual_exchange_rate' => (float)($fatura['manual_exchange_rate'] ?? 1),
        'total_sum' => (float)($fatura['total_sum'] ?? 0),
        'total_discount' => (float)($fatura['total_discount'] ?? 0),
        'subtotal_without_tax' => (float)($fatura['subtotal_without_tax'] ?? 0),
        'total_tax' => (float)($fatura['total_tax'] ?? 0),
        'retention_value' => (float)($fatura['retention_value'] ?? 0),
        'final_total' => (float)($fatura['final_total'] ?? 0),
        'converted_total' => (float)($fatura['converted_total'] ?? 0),
        'status' => 1
    ];

    // =========================
    // UPDATE / INSERT FATURA
    // =========================
    if ($editInvoiceId > 0) {

        $set = [];
        $values = [];

        foreach ($invoiceDbFields as $field => $value) {
            if ($field === 'status') continue;
            $set[] = "$field = ?";
            $values[] = $value;
        }

        $values[] = $editInvoiceId;

        $stmtUpdate = $pdo->prepare("
            UPDATE invoices
            SET " . implode(',', $set) . "
            WHERE id = ?
        ");

        $stmtUpdate->execute($values);

        $invoiceId = $editInvoiceId;

        $pdo->prepare("DELETE FROM invoice_items WHERE invoice_id = ?")
            ->execute([$invoiceId]);
    } else {

        $stmtInsert = $pdo->prepare("
            INSERT INTO invoices (" . implode(',', array_keys($invoiceDbFields)) . ")
            VALUES (" . implode(',', array_fill(0, count($invoiceDbFields), '?')) . ")
        ");

        $stmtInsert->execute(array_values($invoiceDbFields));

        $invoiceId = (int)$pdo->lastInsertId();
    }

    // =========================
    // ITEMS
    // =========================
    $stmtInsertItem = $pdo->prepare("
        INSERT INTO invoice_items
        (invoice_id, item_id, quantity, unit_price, tax, discount)
        VALUES (?,?,?,?,?,?)
    ");

    $stmtCheckStock = $pdo->prepare("
        SELECT item_type, track_stock FROM items WHERE id = ?
    ");

    $stmtReduceStock = $pdo->prepare("CALL sp_reduce_stock(?,?,?,?)");

    $processed = [];

    foreach ($itemData as $item) {

        if (empty($item['id'])) continue;

        $key = md5(json_encode($item));

        if (isset($processed[$key])) continue;

        $processed[$key] = true;

        $itemId = (int)$item['id'];
        $qty = (float)$item['quantity'];
        $price = (float)$item['unit_price'];
        $tax = (float)$item['tax'];
        $discount = (float)$item['discount'];

        if ($qty <= 0) {
            throw new Exception("Quantidade inválida item $itemId");
        }

        // inserir item SEMPRE
        $stmtInsertItem->execute([
            $invoiceId,
            $itemId,
            $qty,
            $price,
            $tax,
            $discount
        ]);

        // =========================
        // STOCK (NUNCA BLOQUEIA)
        // =========================
        try {

            $stmtCheckStock->execute([$itemId]);
            $info = $stmtCheckStock->fetch(PDO::FETCH_ASSOC);

            if (
                $info &&
                (int)$info['track_stock'] === 1 &&
                $info['item_type'] !== 'service'
            ) {

                $stmtReduceStock->execute([
                    $companyIdSession,
                    $itemId,
                    $qty,
                    $invoiceId
                ]);

                while ($stmtReduceStock->nextRowset()) {
                }
                $stmtReduceStock->closeCursor();
            }
        } catch (Throwable $e) {
            error_log("Stock error item $itemId: " . $e->getMessage());
        }
    }

    // =========================
    // FINAL
    // =========================
    $pdo->commit();

    echo json_encode([
        'success' => true,
        'invoice_id' => $invoiceId
    ]);
} catch (Throwable $e) {

    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    http_response_code(500);

    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
