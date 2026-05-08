<?php
require_once '../../../app/config/db.php';
require_once '../../../app/helpers/document_helper.php';

header('Content-Type: application/json');

$invoice_id     = (int)($_POST['invoice_id'] ?? 0);
$amount         = (float)($_POST['amount'] ?? 0);
$pay_date       = $_POST['pay_date'] ?? '';
$payment_method = trim($_POST['payment_method'] ?? '');
$notes          = trim($_POST['notes'] ?? '');

if (!$invoice_id || $amount <= 0) {
  http_response_code(422);
  exit(json_encode(['error' => 'Dados inválidos.']));
}

try {

  $pdo->beginTransaction();

  /* --------------------------------------------------------------------------
       1) BLOQUEAR FATURA
    --------------------------------------------------------------------------*/
  $sql = "
        SELECT final_total, paid_total, company_id
        FROM invoices
        WHERE id = :inv
        FOR UPDATE
    ";

  $st = $pdo->prepare($sql);
  $st->execute([':inv' => $invoice_id]);
  $inv = $st->fetch(PDO::FETCH_ASSOC);

  if (!$inv) {
    throw new Exception('Fatura não encontrada.', 404);
  }

  $paidTotal = $inv['paid_total'] ?? 0;
  $saldo = $inv['final_total'] - $paidTotal;

  if ($amount > $saldo + 0.01) {
    throw new Exception("Valor {$amount} excede saldo {$saldo}.", 422);
  }

  /* --------------------------------------------------------------------------
       2) GERAR NÚMERO PROFISSIONAL (FR)
    --------------------------------------------------------------------------*/
  $reference = generate_document_number(
    $pdo,
    (int)$inv['company_id'],
    'FR' // Fatura-Recibo
  );

  /* --------------------------------------------------------------------------
       3) INSERIR RECIBO
    --------------------------------------------------------------------------*/
  $sql = "
        INSERT INTO receipts
        (invoice_id, reference, pay_date, amount_paid, payment_method, notes)
        VALUES
        (:inv, :ref, :dt, :am, :pm, :no)
    ";

  $pdo->prepare($sql)->execute([
    ':inv' => $invoice_id,
    ':ref' => $reference,
    ':dt'  => $pay_date,
    ':am'  => $amount,
    ':pm'  => $payment_method,
    ':no'  => $notes
  ]);

  $receipt_id = $pdo->lastInsertId();

  /* --------------------------------------------------------------------------
       4) ATUALIZAR FATURA
    --------------------------------------------------------------------------*/
  $novoTotalPago = $paidTotal + $amount;
  $novoSaldo = $inv['final_total'] - $novoTotalPago;

  $status = $novoSaldo <= 0.009 ? 4 : 5; // pago ou parcial

  $pdo->prepare("
        UPDATE invoices
        SET paid_total = :paid,
            status = :st
        WHERE id = :inv
    ")->execute([
    ':paid' => $novoTotalPago,
    ':st'   => $status,
    ':inv'  => $invoice_id
  ]);

  $pdo->commit();

  echo json_encode([
    'success' => true,
    'receipt_id' => $receipt_id,
    'reference' => $reference
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
