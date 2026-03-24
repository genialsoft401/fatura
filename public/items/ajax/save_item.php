<?php
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../../app/config/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  echo json_encode(["status" => "Erro", "message" => "Método de requisição inválido.", "type" => "error"]);
  exit;
}

$id_company = (int)($_POST['id_company'] ?? 0);
$code = trim($_POST['codigo'] ?? '');
$description = trim($_POST['descricao'] ?? '');
$unit = trim($_POST['unidade'] ?? '');
$retention = trim($_POST['retencao'] ?? '');
$unit_price = (float)($_POST['preco'] ?? 0);
$currency = trim($_POST['currency'] ?? 'AOA');
$tax = trim($_POST['taxa'] ?? '');
$pvp = (float)($_POST['pvp'] ?? 0);

if (empty($id_company) || $code === '' || $description === '' || $unit === '' || empty($unit_price) || $tax === '' || empty($pvp)) {
  echo json_encode(["status" => "Atenção", "message" => "Todos os campos obrigatórios devem ser preenchidos.", "type" => "info"]);
  exit;
}

try {
  $stmt = $pdo->prepare(
    "INSERT INTO items (id_company, code, description, unit, retention, unit_price, currency, tax, pvp)
     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)"
  );
  $stmt->execute([$id_company, $code, $description, $unit, $retention, $unit_price, $currency, $tax, $pvp]);

  echo json_encode(["status" => "Sucesso!", "message" => "Produto/Serviço salvo com sucesso!", "type" => "success"]);
} catch (PDOException $e) {
  echo json_encode(["status" => "Erro", "message" => "Erro ao salvar o produto/serviço: " . $e->getMessage(), "type" => "error"]);
}
