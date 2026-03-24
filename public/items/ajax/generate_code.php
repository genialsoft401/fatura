<?php
require_once '../../../app/config/db.php';
header('Content-Type: application/json'); 
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $company_id = $_SESSION['user']['company_id'];

    // Pega o nome da empresa
    $stmt = $pdo->prepare("SELECT name FROM companies WHERE id = :id");
    $stmt->bindParam(':id', $company_id);
    $stmt->execute();

    $company = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($company) {
        // Pega a primeira letra do nome da empresa
        $first_letter = strtoupper(substr($company['name'], 0, 1));
        $unique_code = null;

        do {
            // Gera um número de 5 dígitos
            $random_number = str_pad(rand(0, 99999), 5, '0', STR_PAD_LEFT);
            $unique_code = $first_letter. '-' . $random_number;

            // Verifica se o código já existe no banco de dados
            $check_stmt = $pdo->prepare("SELECT id FROM items WHERE code = :code and id_company = :id");
            $check_stmt->bindParam(':code', $unique_code);
            $check_stmt->bindParam(':id', $company_id);
            $check_stmt->execute();
        } while ($check_stmt->rowCount() > 0); // Continua gerando até encontrar um código único

        echo json_encode(['generated_code' => $unique_code]);
    } else {
        echo json_encode(['error' => 'Empresa não encontrada.']);
    }
}
?>
