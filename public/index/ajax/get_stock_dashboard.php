<?php
header('Content-Type: application/json');
require_once __DIR__ . '../../../../app/config/db.php';

$company_id = $_GET['company_id'] ?? null;

if (!$company_id) {
    echo json_encode(["success" => false]);
    exit;
}

try {

    /* =========================
       🔹 TOTAL DEPÓSITOS
    ========================= */
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as total 
        FROM stocks 
        WHERE company_id = ?
    ");
    $stmt->execute([$company_id]);
    $depots = $stmt->fetch()['total'] ?? 0;

    /* =========================
       🔹 TOTAL PRODUTOS
    ========================= */
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as total
        FROM stock_items si
        INNER JOIN stocks s ON s.id = si.stock_id
        WHERE s.company_id = ?
    ");
    $stmt->execute([$company_id]);
    $products = $stmt->fetch()['total'] ?? 0;

    /* =========================
       🔹 VALOR TOTAL
    ========================= */
    $stmt = $pdo->prepare("
        SELECT SUM(si.quantity * si.unit_price) as total
        FROM stock_items si
        INNER JOIN stocks s ON s.id = si.stock_id
        WHERE s.company_id = ?
    ");
    $stmt->execute([$company_id]);
    $total_value = $stmt->fetch()['total'] ?? 0;

    /* =========================
       🔹 STOCK BAIXO
    ========================= */
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as total
        FROM stock_items si
        INNER JOIN stocks s ON s.id = si.stock_id
        WHERE s.company_id = ?
        AND si.quantity <= si.min_quantity
    ");
    $stmt->execute([$company_id]);
    $low_stock = $stmt->fetch()['total'] ?? 0;

    /* =========================
       🔹 DEPÓSITOS DETALHE
    ========================= */
    $stmt = $pdo->prepare("
        SELECT 
            s.id,
            s.name,
            s.city,
            COUNT(si.id) as total_items,
            SUM(si.quantity * si.unit_price) as total_value
        FROM stocks s
        LEFT JOIN stock_items si ON si.stock_id = s.id
        WHERE s.company_id = ?
        GROUP BY s.id
        LIMIT 3
    ");
    $stmt->execute([$company_id]);
    $depots_list = $stmt->fetchAll(PDO::FETCH_ASSOC);

    /* =========================
       🔹 PRODUTOS STOCK BAIXO
    ========================= */
    $stmt = $pdo->prepare("
        SELECT 
            si.name,
            si.quantity,
            si.min_quantity
        FROM stock_items si
        INNER JOIN stocks s ON s.id = si.stock_id
        WHERE s.company_id = ?
        AND si.quantity <= si.min_quantity
        LIMIT 10
    ");
    $stmt->execute([$company_id]);
    $low_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

    /* =========================
       🔹 LISTA DE COMPRAS (SUGESTÃO)
    ========================= */
    $stmt = $pdo->prepare("
        SELECT 
            si.name,
            (si.min_quantity * 2 - si.quantity) as suggested_qty
        FROM stock_items si
        INNER JOIN stocks s ON s.id = si.stock_id
        WHERE s.company_id = ?
        AND si.quantity < si.min_quantity
        LIMIT 5
    ");
    $stmt->execute([$company_id]);
    $purchases = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        "success" => true,
        "data" => [
            "kpis" => [
                "depots" => (int)$depots,
                "products" => (int)$products,
                "total_value" => (float)$total_value,
                "low_stock" => (int)$low_stock
            ],
            "depots" => $depots_list,
            "low_stock" => $low_items,
            "purchases" => $purchases
        ]
    ]);

} catch (Exception $e) {
    echo json_encode([
        "success" => false,
        "error" => $e->getMessage()
    ]);
}