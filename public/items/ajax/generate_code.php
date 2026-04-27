<?php
require_once '../../../app/config/db.php';
header('Content-Type: application/json');
session_start();

try {

    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        throw new Exception('Método inválido.');
    }

    if (!isset($_SESSION['user']['company_id'])) {
        throw new Exception('Sessão inválida.');
    }

    $company_id = (int) $_SESSION['user']['company_id'];
    $stock_id   = (int) ($_GET['stock_id'] ?? 0);
    $item_type  = $_GET['item_type'] ?? 'product';

    /*
    =========================================
    TYPE PREFIX
    =========================================
    */
    $typeMap = [
        'product'       => 'PROD',
        'service'       => 'SRV',
        'raw_material'  => 'RAW',
        'finished_good' => 'FIN',
        'consumable'    => 'CON'
    ];

    $typePrefix = $typeMap[$item_type] ?? 'ITM';

    /*
    =========================================
    PREFIX BASE
    =========================================
    */
    $basePrefix = 'ITM';

    // produtos usam stock
    if ($item_type === 'product') {

        if ($stock_id <= 0) {
            throw new Exception('Stock inválido.');
        }

        $stmt = $pdo->prepare("SELECT id FROM stocks WHERE id = :id AND company_id = :company_id");
        $stmt->execute([
            ':id' => $stock_id,
            ':company_id' => $company_id
        ]);

        if (!$stmt->fetch()) {
            throw new Exception('Stock não encontrado.');
        }

        $stockPrefix = 'STK' . str_pad($stock_id, 2, '0', STR_PAD_LEFT);

        $prefixLike = $stockPrefix . '-' . $typePrefix . '-%';
    } else {
        // serviços NÃO usam stock
        $stockPrefix = 'GEN';

        $prefixLike = $company_id . '-' . $typePrefix . '-%';
    }

    /*
    =========================================
    BUSCAR ÚLTIMO CÓDIGO
    =========================================
    */
    $stmt = $pdo->prepare("
        SELECT code
        FROM items
        WHERE company_id = :company_id
        AND item_type = :item_type
        AND code LIKE :prefix
        ORDER BY id DESC
        LIMIT 1
    ");

    $stmt->execute([
        ':company_id' => $company_id,
        ':item_type'  => $item_type,
        ':prefix'     => $prefixLike
    ]);

    $last = $stmt->fetch(PDO::FETCH_ASSOC);

    /*
    =========================================
    SEQUENCIAL
    =========================================
    */
    $nextNumber = 1;

    if (!empty($last['code'])) {
        $parts = explode('-', $last['code']);
        $nextNumber = ((int) end($parts)) + 1;
    }

    $sequence = str_pad($nextNumber, 6, '0', STR_PAD_LEFT);

    /*
    =========================================
    FINAL CODE
    =========================================
    */
    if ($item_type === 'product') {
        $generated_code = $stockPrefix . '-' . $typePrefix . '-' . $sequence;
    } else {
        $generated_code = $company_id . '-' . $typePrefix . '-' . $sequence;
    }

    echo json_encode([
        'success' => true,
        'generated_code' => $generated_code,
        'type_prefix' => $typePrefix,
        'sequence' => $sequence
    ]);
} catch (Exception $e) {

    http_response_code(400);

    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
