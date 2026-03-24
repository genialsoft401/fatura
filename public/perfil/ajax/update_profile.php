<?php
require_once '../../../app/config/db.php';
session_start();


if (!isset($_SESSION['user']['id'])) {
    echo json_encode(["status" => "error", "message" => "Sessão expirada. Faça login novamente."]);
    exit;
}

$userId = (int)$_SESSION['user']['id']; // ID do usuário logado
$updates = [];
$params = [];

// Whitelist de campos permitidos (evita tentar atualizar colunas inexistentes e evita updates indevidos)
$allowedKeys = [
    'name',
    'phone',
    'gender',
    'identification',
    'address',
    'address_number',
    'country',
    'address_district',
    'lang'
];

foreach ($_POST as $key => $value) {
    if ($key === 'image') continue;
    if (!in_array($key, $allowedKeys, true)) continue;

    // permite limpar campos (string vazia) se quiserem depois; por agora segue lógica original de não atualizar vazio
    if ($value === '' || $value === null) continue;

    $updates[] = "$key = :$key";
    $params[":$key"] = $value;
}

// Se houver uma imagem na sessão, atualiza o banco
if (!empty($_SESSION['user']['image'])) {
    $updates[] = "image = :image";
    $params[":image"] = $_SESSION['user']['image'];
}

if (empty($updates)) {
    echo json_encode(["status" => "error", "message" => "Nenhum dado válido para atualizar."]);
    exit;
}

// Monta a query de atualização
$sql = "UPDATE users SET " . implode(', ', $updates) . " WHERE id = :id";
$stmt = $pdo->prepare($sql);
$params[":id"] = $userId;

// Executa a query
if ($stmt->execute($params)) {
   

    // Atualiza os dados da sessão com os novos valores (somente whitelist)
    foreach ($_POST as $key => $value) {
        if ($key === 'image') continue;
        if (!in_array($key, $allowedKeys, true)) continue;
        if ($value === '' || $value === null) continue;
        $_SESSION['user'][$key] = $value;
    }

    echo json_encode(["status" => "success"]);
} else {
    echo json_encode(["status" => "error", "message" => "Erro ao atualizar perfil."]);
}
?>