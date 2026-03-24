<?php
require_once '../../../app/config/db.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['id'];
    $id_company = $_POST['id_company'];
    $code = $_POST['codigo'];
    $description = $_POST['descricao'];
    $unit = $_POST['unidade'];
    $retention = $_POST['retencao'];
    $unit_price = $_POST['preco'];
    $currency = $_POST['currency'];
    $tax = $_POST['taxa'];
    $pvp = $_POST['pvp'];

    $query = "UPDATE items SET id_company = :id_company, code = :code, description = :description, unit = :unit, retention = :retention, unit_price = :unit_price, currency = :currency, tax = :tax, pvp = :pvp WHERE id = :id";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':id', $id);
    $stmt->bindParam(':id_company', $id_company);
    $stmt->bindParam(':code', $code);
    $stmt->bindParam(':description', $description);
    $stmt->bindParam(':unit', $unit);
    $stmt->bindParam(':retention', $retention);
    $stmt->bindParam(':unit_price', $unit_price);
    $stmt->bindParam(':currency', $currency);
    $stmt->bindParam(':tax', $tax);
    $stmt->bindParam(':pvp', $pvp);

    if ($stmt->execute()) {
        echo json_encode(["success" => true]);
    } else {
        echo json_encode(["success" => false]);
    }
}
?>
