<?php

require_once '../../../app/config/db.php';
require_once '../../../app/helpers/document_helper.php';

header('Content-Type: application/json');

$invoice_id     = (int)($_POST['invoice_id'] ?? 0);
$serie          = (int)($_POST['serie'] ?? 0);
$amount         = round((float)($_POST['amount'] ?? 0), 2);
$pay_date       = $_POST['pay_date'] ?? '';
$payment_method = trim($_POST['payment_method'] ?? '');
$notes          = trim($_POST['notes'] ?? '');

if (!$invoice_id || $amount <= 0) {
  http_response_code(422);
  exit(json_encode(['error' => 'Dados inválidos.']));
}

try {

  $pdo->beginTransaction();

  // =========================
  // FATURA
  // =========================
  $st = $pdo->prepare("
    SELECT final_total, paid_total, company_id
    FROM invoices
    WHERE id = ?
    FOR UPDATE
  ");

  $st->execute([$invoice_id]);
  $inv = $st->fetch(PDO::FETCH_ASSOC);

  if (!$inv) {
    throw new Exception('Fatura não encontrada.');
  }

  $paidTotal = (float)$inv['paid_total'];
  $finalTotal = (float)$inv['final_total'];

  $prefix = 'FR';

  // =========================
  // ✔ NUMBER CORRETO (INT)
  // =========================
  $stmt = $pdo->prepare("
    SELECT COALESCE(MAX(number), 0) + 1
    FROM receipts
    FOR UPDATE
  ");

  $stmt->execute();
  $number = (int)$stmt->fetchColumn();

  // =========================
  // reference (continua separado)
  // =========================
  $reference = $prefix . '-' . str_pad($number, 6, '0', STR_PAD_LEFT);

  // =========================
  // INSERT RECEIPT
  // =========================
  $stmt = $pdo->prepare("
    INSERT INTO receipts
    (invoice_id, number, reference, serie, pay_date, amount_paid, payment_method, notes)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?)
  ");

  $stmt->execute([
    $invoice_id,
    $number,
    $reference,
    $serie,
    $pay_date,
    $amount,
    $payment_method,
    $notes
  ]);

  $receipt_id = (int)$pdo->lastInsertId();

  // =========================
  // UPDATE FATURA
  // =========================
  $novoTotalPago = $paidTotal + $amount;
  $novoSaldo = $finalTotal - $novoTotalPago;

  $status = ($novoSaldo <= 0.01) ? 4 : 5;

  $upd = $pdo->prepare("
    UPDATE invoices
    SET paid_total = ?, status = ?
    WHERE id = ?
  ");

  $upd->execute([
    $novoTotalPago,
    $status,
    $invoice_id
  ]);

  $pdo->commit();

  echo json_encode([
    'success' => true,
    'receipt_id' => $receipt_id,
    'number' => $number,
    'reference' => $reference,
    'serie' => $serie
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
