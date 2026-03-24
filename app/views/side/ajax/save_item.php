<?php
require '../../../config/db.php'; // Certifique-se de ter um arquivo de conexão com o banco

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_company = $_POST['id_company'] ?? 0;
    $code = $_POST['codigo'] ?? '';
    $description = $_POST['descricao'] ?? '';
    $unit = $_POST['unidade'] ?? '';
    $retention = $_POST['retencao'] ?? '';
    $unit_price = $_POST['preco'] ?? 0;
    $currency = $_POST['currency'] ?? 'AOA';
    $tax = $_POST['taxa'] ?? '';
    $pvp = $_POST['pvp'] ?? 0;

    if (empty($id_company) || empty($code) || empty($description) || empty($unit) || empty($unit_price) || empty($tax) || empty($pvp)) {
        echo json_encode(["status" => "Atenção", "message" => "Todos os campos obrigatórios devem ser preenchidos.", "type" => 'info']);
        exit;
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO items (id_company, code, description, unit, retention, unit_price, currency, tax, pvp) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$id_company, $code, $description, $unit, $retention, $unit_price, $currency, $tax, $pvp]);

        echo json_encode(["status" => "Sucesso!", "message" => "Produto/Serviço salvo com sucesso!", "type" => 'success']);
    } catch (PDOException $e) {
        echo json_encode(["status" => "Erro", "message" => "Erro ao salvar o produto/serviço: " . $e->getMessage(), "type" => 'error']);
    }
} else {
    echo json_encode(["status" => "Erro", "message" => "Método de requisição inválido.", "type" => 'error']);
}
