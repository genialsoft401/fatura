<?php

header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../../app/config/db.php';

ini_set('display_errors', 0);
error_reporting(E_ALL);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  echo json_encode([
    "status" => "error",
    "message" => "Método inválido"
  ]);
  exit;
}

try {

  // =========================
  // INPUTS
  // =========================

  $company_id = (int)($_POST['id_company'] ?? 0);
  $stock_id   = !empty($_POST['stock_id'])
    ? (int)$_POST['stock_id']
    : null;

  $code        = trim($_POST['codigo'] ?? '');
  $name        = trim($_POST['name'] ?? '');
  $description = trim($_POST['descricao'] ?? '');

  $item_type    = trim($_POST['item_type'] ?? 'product');
  $unit_measure = trim($_POST['unit_measure'] ?? 'unit');

  /**
   * Seu HTML usa:
   * name="subcategory"
   * e não name="category"
   */
  $category = !empty($_POST['subcategory'])
    ? trim($_POST['subcategory'])
    : null;

  $currency = trim($_POST['currency'] ?? 'AOA');

  /**
   * IVA
   * Se vier vazio por causa do readonly ou JS,
   * define 14 como fallback.
   */
  $tax = isset($_POST['tax']) && $_POST['tax'] !== ''
    ? (float)$_POST['tax']
    : 14.00;

  /**
   * Preços
   */
  $unit_price = isset($_POST['unit_price']) && $_POST['unit_price'] !== ''
    ? (float)$_POST['unit_price']
    : 0;

  $cost_price = isset($_POST['cost_price']) && $_POST['cost_price'] !== ''
    ? (float)$_POST['cost_price']
    : 0;

  $sale_price = isset($_POST['sale_price']) && $_POST['sale_price'] !== ''
    ? (float)$_POST['sale_price']
    : 0;

  $pvp = isset($_POST['pvp']) && $_POST['pvp'] !== ''
    ? (float)$_POST['pvp']
    : 0;

  /**
   * Se unit_price vier vazio,
   * usa cost_price como fallback
   */
  if ($unit_price <= 0 && $cost_price > 0) {
    $unit_price = $cost_price;
  }

  /**
   * Retenção
   */
  $retention = ($_POST['retention'] ?? 0);

  /**
   * Stock
   */
  $quantity = isset($_POST['quantidade'])
    ? (int)$_POST['quantidade']
    : 0;

  $min_quantity = isset($_POST['min_stock'])
    ? (int)$_POST['min_stock']
    : 1;

  // =========================
  // VALIDAÇÕES
  // =========================

  if ($company_id <= 0) {
    throw new Exception("Empresa inválida.");
  }

  if (empty($name)) {
    throw new Exception("O nome do item é obrigatório.");
  }

  if (empty($code)) {
    throw new Exception("O código do item é obrigatório.");
  }

  // DEBUG TEMPORÁRIO (remover depois)
  error_log("IVA RECEBIDO: " . print_r($_POST['tax'] ?? null, true));
  error_log("IVA FINAL: " . $tax);

  // =========================
  // PROCEDURE
  // =========================

  $sql = "CALL sp_create_item_with_stock(
        ?,?,?,?,?,?,?,?,?,?,
        ?,?,?,?,?,?,?
    )";

  $stmt = $pdo->prepare($sql);

  $stmt->execute([
    $company_id,
    $stock_id,
    $code,
    $name,
    $description,
    $item_type,
    $unit_measure,
    $category,
    $currency,
    $tax,

    $unit_price,
    $cost_price,
    $sale_price,
    $pvp,

    $retention,
    $quantity,
    $min_quantity
  ]);

  $result = $stmt->fetch(PDO::FETCH_ASSOC);

  /**
   * IMPORTANTE:
   * MySQL procedure às vezes mantém cursor aberto
   */
  while ($stmt->nextRowset()) {
    // limpa rowsets restantes
  }

  echo json_encode([
    "status"  => "success",
    "message" => "Item criado com sucesso",
    "item_id" => $result['item_id'] ?? null,
    "tax"     => $tax
  ]);
} catch (Throwable $e) {

  http_response_code(500);

  echo json_encode([
    "status"  => "error",
    "message" => $e->getMessage()
  ]);
}
