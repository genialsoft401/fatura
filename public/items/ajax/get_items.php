<?php

header('Content-Type: application/json');
require_once '../../../app/config/db.php'; 
session_start(); 

try {
    $query = "SELECT i.id, i.id_company, i.code,  i.description, 
    case when i.unit = 'service' then 'Serviço' when i.unit = 'unit' then 'Unidade' else i.unit end as unit, 
    i.unit as unit2, 
    case when i.retention = 'apply' then 'Aplicar' when i.retention = 'do_not_apply' then 'Não Aplicar' else i.retention end as retention, 
    i.retention as retention2, i.unit_price, 
    case when i.tax = '14' then '14% - Taxa14' when i.tax = 'exempt' then 'Isento' else i.tax end as tax, 
    i.tax as tax2, i.pvp, i.created_at, cr.symbol, cr.position, i.currency
                FROM items i
              JOIN companies c ON c.id = i.id_company
              JOIN company_has_user chu ON chu.company_id = c.id 
              JOIN users u ON u.id = chu.user_id
              join currencies cr on cr.iso_code = i.currency
              WHERE u.id = :user_id and c.id = :company_id";

    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':user_id', $_SESSION['user']['id'], PDO::PARAM_INT);
    $stmt->bindParam(':company_id', $_SESSION['user']['company_id'], PDO::PARAM_INT);
    $stmt->execute();

    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // description pode vir em HTML (editor). Para listagens, adiciona texto puro.
    foreach ($items as &$it) {
        if (isset($it['description']) && is_string($it['description'])) {
            $plain = strip_tags($it['description']);
            $plain = preg_replace('/\s+/u', ' ', $plain);
            $it['description_plain'] = trim($plain);
        }
    }
    unset($it);

    echo json_encode($items);
} catch (Exception $e) {
    echo json_encode(["error" => $e->getMessage()]);
}
