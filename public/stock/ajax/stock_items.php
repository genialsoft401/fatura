<?php
require_once '../../../app/config/db.php';
session_start();
header('Content-Type: application/json');

$action = $_REQUEST['action'] ?? null;

switch ($action) {
    case 'list':
        listarItens($pdo);
        break;

    case 'save':
        salvarItens($pdo);
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Ação inválida']);
}

// Retorna itens + nome do estoque
function listarItens($pdo)
{
    $stock_id = (int)($_GET['stock_id'] ?? 0);

    // Busca itens
    $stmt = $pdo->prepare("SELECT si.*, c.currency, c.iso_code, c.symbol, c.`position` FROM stock_items si
                            join currencies c on c.iso_code = si.currency
                            WHERE stock_id = ? ORDER BY id ASC");
    $stmt->execute([$stock_id]);
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Busca nome do estoque
    $stmt2 = $pdo->prepare("SELECT name, color, updated_at, updated_by FROM stocks WHERE id = ?");
    $stmt2->execute([$stock_id]);
    $stock = $stmt2->fetch(PDO::FETCH_ASSOC);

    echo json_encode(['items' => $items, 'stock' => $stock]);
}

function salvarItens($pdo)
{
    $stock_id = (int)($_POST['stock_id'] ?? 0);
    $items = json_decode($_POST['items'] ?? '[]', true);
    $success = true;
    $retorno = [];

    // Atualiza o stock pai (data e usuário) ao modificar itens
    $currentUser = $_SESSION['user']['name'] ?? 'Sistema';
    $stmtUpd = $pdo->prepare("UPDATE stocks SET updated_at = NOW(), updated_by = ? WHERE id = ?");
    $stmtUpd->execute([$currentUser, $stock_id]);

    foreach ($items as $item) {
        $id = $item['id'] ?? null;
        $code = $item['code'] ?? null;
        $name = $item['name'] ?? '';
        $category = $item['category'] ?? '';
        $quantity = (int)($item['quantity'] ?? 0);
        $min_quantity = isset($item['min_quantity']) ? (int)$item['min_quantity'] : 1;
        $unit_price = (float)($item['unit_price'] ?? 0.0);
        $currency = $item['currency'] ?? 'AOA';

        if ($id) {
            // UPDATE
            $stmt = $pdo->prepare("
                UPDATE stock_items
                SET code = ?, name = ?, category = ?, quantity = ?, min_quantity = ?, unit_price = ?, updated_at = NOW(), currency = ?
                WHERE id = ? AND stock_id = ?
            ");
            $success = $stmt->execute([$code, $name, $category, $quantity, $min_quantity, $unit_price, $currency, $id, $stock_id]);

            $retorno[] = [
                'temp_id' => $id,
                'id' => $id
            ];
        } else {
            // INSERT
            $stmt = $pdo->prepare("
                INSERT INTO stock_items (stock_id, code, name, category, quantity, min_quantity, unit_price, currency)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $success = $stmt->execute([$stock_id, $code, $name, $category, $quantity, $min_quantity, $unit_price, $currency]);

            $newId = $pdo->lastInsertId();
            $retorno[] = [
                'temp_id' => null,
                'id' => $newId
            ];
        }
    }

    // Busca dados atualizados do stock para retornar ao frontend
    $stmtGet = $pdo->prepare("SELECT updated_at, updated_by FROM stocks WHERE id = ?");
    $stmtGet->execute([$stock_id]);
    $stockInfo = $stmtGet->fetch(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => $success,
        'items' => $retorno,
        'stock_update' => $stockInfo
    ]);
}
