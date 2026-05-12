<?php
require_once '../../../app/config/db.php';
require_once '../../../app/helpers/subscription.php';

header('Content-Type: application/json');

session_start();

// DEBUG (remover em produção)
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

        if (
            isset($field['name']) &&
            isset($field['value'])
        ) {
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

    // =========================
    // VALIDAR SUBSCRIÇÃO
    // =========================
    subscription_assert_active($pdo, $companyIdSession);

    if ($editInvoiceId <= 0) {
        subscription_check_limit(
            $pdo,
            $companyIdSession,
            'invoice'
        );
    }

    // =========================
    // CONTACTO
    // =========================
    if (!empty($fatura['contact_id'])) {

        $contactId = (int)$fatura['contact_id'];
    } else {

        if (
            empty($fatura['name']) ||
            empty($fatura['email'])
        ) {
            throw new Exception(
                "Nome e e-mail do contato são obrigatórios."
            );
        }

        // verificar contacto existente
        $stmtContact = $pdo->prepare("
            SELECT id
            FROM contact
            WHERE email = ?
            AND company_id = ?
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
                (
                    name,
                    email,
                    contributor,
                    address,
                    po_box,
                    country,
                    city,
                    company_id
                )
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

    // =========================
    // REMOVER CAMPOS EXTRAS
    // =========================
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
    // DADOS DA FATURA
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
    // UPDATE FATURA
    // =========================
    if ($editInvoiceId > 0) {

        // =========================
        // DEVOLVER STOCK ANTIGO
        // =========================
        $stmtOldItems = $pdo->prepare("
            SELECT
                item_id,
                quantity
            FROM invoice_items
            WHERE invoice_id = ?
        ");

        $stmtOldItems->execute([$editInvoiceId]);

        $oldItems = $stmtOldItems->fetchAll(PDO::FETCH_ASSOC);

        $stmtCheckItem = $pdo->prepare("
            SELECT item_type, track_stock
            FROM items
            WHERE id = ?
        ");

        $stmtIncreaseStock = $pdo->prepare("
            CALL sp_increase_stock(?,?,?,?)
        ");

        foreach ($oldItems as $oldItem) {

            $stmtCheckItem->execute([
                (int)$oldItem['item_id']
            ]);

            $oldInfo = $stmtCheckItem->fetch(PDO::FETCH_ASSOC);

            $stmtCheckItem->closeCursor();

            if (
                $oldInfo &&
                (int)$oldInfo['track_stock'] === 1 &&
                $oldInfo['item_type'] !== 'service'
            ) {

                $stmtIncreaseStock->execute([
                    $companyIdSession,
                    (int)$oldItem['item_id'],
                    (float)$oldItem['quantity'],
                    $editInvoiceId
                ]);

                while ($stmtIncreaseStock->nextRowset()) {
                }

                $stmtIncreaseStock->closeCursor();
            }
        }

        // =========================
        // ATUALIZAR FATURA
        // =========================
        $set = [];
        $values = [];

        foreach ($invoiceDbFields as $field => $value) {

            if ($field === 'status') {
                continue;
            }

            $set[] = "{$field} = ?";
            $values[] = $value;
        }

        $values[] = $editInvoiceId;

        $stmtUpdateInvoice = $pdo->prepare("
            UPDATE invoices
            SET " . implode(',', $set) . "
            WHERE id = ?
        ");

        $stmtUpdateInvoice->execute($values);

        $invoiceId = $editInvoiceId;

        // remover itens antigos
        $stmtDeleteItems = $pdo->prepare("
            DELETE FROM invoice_items
            WHERE invoice_id = ?
        ");

        $stmtDeleteItems->execute([$invoiceId]);
    }

    // =========================
    // NOVA FATURA
    // =========================
    else {

        $stmtInsertInvoice = $pdo->prepare("
            INSERT INTO invoices
            (
                " . implode(',', array_keys($invoiceDbFields)) . "
            )
            VALUES
            (
                " . implode(',', array_fill(0, count($invoiceDbFields), '?')) . "
            )
        ");

        $stmtInsertInvoice->execute(
            array_values($invoiceDbFields)
        );

        $invoiceId = (int)$pdo->lastInsertId();
    }

    // =========================
    // INSERIR NOVOS ITENS
    // =========================
    $processedItems = [];

    $stmtInsertItem = $pdo->prepare("
        INSERT INTO invoice_items
        (
            invoice_id,
            item_id,
            quantity,
            unit_price,
            tax,
            discount
        )
        VALUES (?,?,?,?,?,?)
    ");

    $stmtCheckStock = $pdo->prepare("
        SELECT
            item_type,
            track_stock
        FROM items
        WHERE id = ?
    ");

    $stmtReduceStock = $pdo->prepare("
        CALL sp_reduce_stock(?,?,?,?)
    ");

    foreach ($itemData as $item) {

        if (empty($item['id'])) {
            throw new Exception("Item inválido.");
        }

        $itemId = (int)$item['id'];
        $quantity = (float)($item['quantity'] ?? 0);
        $unitPrice = (float)($item['unit_price'] ?? 0);
        $tax = (float)($item['tax'] ?? 0);
        $discount = (float)($item['discount'] ?? 0);

        if ($quantity <= 0) {
            throw new Exception(
                "Quantidade inválida para o item ID {$itemId}."
            );
        }

        // evitar duplicação
        $uniqueKey = md5(json_encode([
            $itemId,
            $quantity,
            $unitPrice,
            $tax,
            $discount
        ]));

        if (isset($processedItems[$uniqueKey])) {
            continue;
        }

        $processedItems[$uniqueKey] = true;

        // inserir item
        $stmtInsertItem->execute([
            $invoiceId,
            $itemId,
            $quantity,
            $unitPrice,
            $tax,
            $discount
        ]);

        // verificar stock
        $stmtCheckStock->execute([$itemId]);

        $stockInfo = $stmtCheckStock->fetch(PDO::FETCH_ASSOC);

        $stmtCheckStock->closeCursor();

        // reduzir stock
        if (
            $stockInfo &&
            (int)$stockInfo['track_stock'] === 1 &&
            $stockInfo['item_type'] !== 'service'
        ) {

            $stmtReduceStock->execute([
                $companyIdSession,
                $itemId,
                $quantity,
                $invoiceId
            ]);

            while ($stmtReduceStock->nextRowset()) {
            }

            $stmtReduceStock->closeCursor();
        }
    }

    // =========================
    // FINALIZAR
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

    error_log($e->getMessage());

    http_response_code(500);

    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
