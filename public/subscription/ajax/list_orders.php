<?php
require_once __DIR__ . '/../../../app/config/db.php';
require_once __DIR__ . '/../../../app/helpers/subscription.php';

session_start();
header('Content-Type: application/json; charset=utf-8');

function out(array $p, int $code=200): void {
  http_response_code($code);
  echo json_encode($p, JSON_UNESCAPED_UNICODE);
  exit;
}

$company_id = (int)($_SESSION['user']['company_id'] ?? 0);
if (!$company_id) out(['success'=>false,'message'=>'Empresa inválida (sessão)'], 400);

$requester_id = (int)($_SESSION['user']['id'] ?? 0);
$stmt = $pdo->prepare("SELECT role FROM company_has_user WHERE user_id = ? AND company_id = ? LIMIT 1");
$stmt->execute([$requester_id, $company_id]);
$role = $stmt->fetchColumn();
if (!$role) out(['success'=>false,'message'=>'Acesso negado'], 403);

$st = $pdo->prepare("SELECT id, plan_code, period_months, amount, status, gateway, payment_method, merchant_transaction_id, gateway_charge_id, gateway_status, payment_ref, created_at, paid_at FROM subscription_orders WHERE company_id=? ORDER BY id DESC LIMIT 30");
$st->execute([$company_id]);
$rows = $st->fetchAll(PDO::FETCH_ASSOC);

out(['success'=>true,'orders'=>$rows]);
