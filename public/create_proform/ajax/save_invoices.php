<?php
require_once '../../../app/config/db.php';
require_once '../../../app/helpers/subscription.php';

header('Content-Type: application/json');
session_start();

// 🔥 DEBUG (remove em produção)
error_reporting(E_ALL);
ini_set('display_errors', 1);


function generateProformaReference(PDO $pdo, int $companyId): string
{
    $year = date('Y');

    $stmt = $pdo->prepare("
        SELECT MAX(
            CAST(
                SUBSTRING_INDEX(reference, '/', -1)
                AS UNSIGNED
            )
        ) as last_number
        FROM proformas
        WHERE company_id = ?
        AND reference LIKE ?
        FOR UPDATE
    ");

    $stmt->execute([
        $companyId,
        "PF {$year}/%"
    ]);

    $lastNumber = (int)($stmt->fetchColumn() ?? 0);

    $nextNumber = $lastNumber + 1;

    return sprintf(
        'PF %s/%06d',
        $year,
        $nextNumber
    );
}



// Recebe dados
$invoiceData = $_POST['invoice'] ?? [];
$itemData = $_POST['items'] ?? [];

// Converte invoice array
$fatura = [];
foreach ($invoiceData as $field) {
    $fatura[$field['name']] = $field['value'];
}

// Detecta edição
$editInvoiceId = !empty($fatura['edit_invoice_id']) ? (int)$fatura['edit_invoice_id'] : 0;
unset($fatura['edit_invoice_id']);

try {
    $pdo->beginTransaction();

    // 🔐 Validação sessão
    $companyIdSession = (int)($_SESSION['user']['company_id'] ?? 0);
    if (!$companyIdSession) {
        throw new Exception("Sessão inválida.");
    }

    subscription_assert_active($pdo, $companyIdSession);

    if ($editInvoiceId <= 0) {
        subscription_check_limit($pdo, $companyIdSession, 'invoice');
    }

    // =========================
    // 📌 CONTACTO
    // =========================
    if (!empty($fatura['contact_id'])) {
        $contactId = (int)$fatura['contact_id'];
    } else {

        if (empty($fatura['name']) || empty($fatura['email'])) {
            throw new Exception('Nome e e-mail do contato são obrigatórios.');
        }

        $stmt = $pdo->prepare("SELECT id FROM contact WHERE email = ? AND company_id = ?");
        $stmt->execute([$fatura['email'], $companyIdSession]);

        $existing = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($existing) {
            $contactId = $existing['id'];
        } else {
            $stmt = $pdo->prepare("
                INSERT INTO contact (name,email,contributor,address,po_box,country,city,company_id)
                VALUES (?,?,?,?,?,?,?,?)
            ");

            $stmt->execute([
                $fatura['name'],
                $fatura['email'],
                $fatura['contributor'] ?? null,
                $fatura['address'] ?? null,
                $fatura['po_box'] ?? null,
                $fatura['country'] ?? null,
                $fatura['city'] ?? null,
                $companyIdSession
            ]);

            $contactId = $pdo->lastInsertId();
        }
    }

    // Remove campos extras
    foreach (['contact_id', 'name', 'email', 'telephone', 'address', 'contributor', 'po_box', 'country', 'city'] as $f) {
        unset($fatura[$f]);
    }


    // =========================
    // 📌 PROFORMA
    // =========================

    $invoiceDbFields = [

        'contact_id' => $contactId,

        'company_id' => $companyIdSession,

        'user_id' => (int)$_SESSION['user']['id'],

        'status' => 1, // Rascunho

        'issue_date' => !empty($fatura['issue_date'])
            ? $fatura['issue_date']
            : date('Y-m-d'),

        'due_date' => !empty($fatura['due_date'])
            ? $fatura['due_date']
            : null,

        'reference' => $fatura['reference'] ?? null,

        'observation' => $fatura['observation'] ?? null,

        'series' => $fatura['series'] ?? 'PF',

        'currency' => $fatura['currency'] ?? 'AOA',

        'manual_exchange_rate' => (float)($fatura['manual_exchange_rate'] ?? 1),

        'total_sum' => (float)($fatura['total_sum'] ?? 0),

        'total_discount' => (float)($fatura['total_discount'] ?? 0),

        'subtotal_without_tax' => (float)($fatura['subtotal_without_tax'] ?? 0),

        'total_tax' => (float)($fatura['total_tax'] ?? 0),

        'final_total' => (float)($fatura['final_total'] ?? 0),

        'converted_invoice_id' => null
    ];


    // =========================
    // 🔢 GERAR REFERÊNCIA PF
    // =========================

    if ($editInvoiceId <= 0) {

        $year = date('Y');

        $stmtRef = $pdo->prepare("
        SELECT MAX(
            CAST(
                SUBSTRING_INDEX(reference, '/', -1)
                AS UNSIGNED
            )
        )
        FROM proformas
        WHERE company_id = ?
        AND reference LIKE ?
        FOR UPDATE
    ");

        $stmtRef->execute([
            $companyIdSession,
            "PF {$year}/%"
        ]);

        $lastNumber = (int)$stmtRef->fetchColumn();

        $nextNumber = $lastNumber + 1;

        $invoiceDbFields['reference'] = sprintf(
            'PF %s/%06d',
            $year,
            $nextNumber
        );
    }


    // =========================
    // 🧾 INSERT / UPDATE
    // =========================

    if ($editInvoiceId > 0) {

        $stmtCheck = $pdo->prepare("
        SELECT id, converted_invoice_id
        FROM proformas
        WHERE id = ?
        LIMIT 1
    ");

        $stmtCheck->execute([$editInvoiceId]);

        $existing = $stmtCheck->fetch(PDO::FETCH_ASSOC);

        if (!$existing) {
            throw new Exception("Proforma não encontrada.");
        }

        if (!empty($existing['converted_invoice_id'])) {
            throw new Exception(
                "Esta proforma já foi convertida e não pode ser alterada."
            );
        }

        $set = [];
        $values = [];

        foreach ($invoiceDbFields as $field => $value) {

            // nunca alterar a referência
            if ($field === 'reference') {
                continue;
            }

            $set[] = "{$field} = ?";
            $values[] = $value;
        }

        $values[] = $editInvoiceId;

        $stmt = $pdo->prepare("
        UPDATE proformas
        SET " . implode(',', $set) . "
        WHERE id = ?
    ");

        $stmt->execute($values);

        $invoiceId = $editInvoiceId;

        $pdo->prepare("
        DELETE FROM proforma_items
        WHERE proforma_id = ?
    ")->execute([$invoiceId]);
    } else {

        $stmt = $pdo->prepare("
        INSERT INTO proformas (
            " . implode(',', array_keys($invoiceDbFields)) . "
        )
        VALUES (
            " . implode(',', array_fill(
            0,
            count($invoiceDbFields),
            '?'
        )) . "
        )
    ");

        $stmt->execute(
            array_values($invoiceDbFields)
        );

        $invoiceId = (int)$pdo->lastInsertId();
    }

    // =========================
    // 📦 ITENS
    // =========================
    if (empty($itemData)) {
        throw new Exception("Nenhum item enviado.");
    }

    $stmt = $pdo->prepare("
        INSERT INTO proforma_items (proforma_id,item_id,quantity,unit_price,tax,discount)
        VALUES (?,?,?,?,?,?)
    ");

    foreach ($itemData as $item) {

        if (empty($item['id'])) {
            throw new Exception("Item inválido.");
        }

        $stmt->execute([
            $invoiceId,
            (int)$item['id'],
            (float)$item['quantity'],
            (float)$item['unit_price'],
            (float)$item['tax'],
            (float)$item['discount']
        ]);
    }

    $pdo->commit();

    // 🔥 IMPORTANTE: retornar ID
    echo json_encode([
        'success' => true,
        'proform_id' => $invoiceId
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
