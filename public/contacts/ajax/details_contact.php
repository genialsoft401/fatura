<?php
require_once '../../../app/config/db.php';
header('Content-Type: application/json'); 
session_start();
try {
    $id = $_POST['id'];

    $sql = "SELECT cp.name as company_name, c.id, c.company_id, c.type, c.name, c.contributor, c.address, c.  website, c.country, c.city, c.email, concat('(+',telephone_ddi,') ', telephone) as telephone, concat('(+',cellphone_ddi,') ', cellphone) as cellphone, po_box, fax, pref_name, pref_email,  concat('(+',pref_telephone_ddi,') ', pref_telephone) as pref_telephone, concat('(+',pref_cellphone_ddi,') ', pref_cellphone) as pref_cellphone, 
                case when numberCopys = 1 then 'Original' when numberCopys=2 then 'Duplicado'  when numberCopys=3 then 'Triplicado' end as numberCopys, observations, concat(due_date,' Dias') as due_date, ct.name as language, p.name as payment_method, concat(cr.currency, ' (',cr.iso_code,')') as currency, c.created_at, c.updated_at 
            FROM contact c 
            join companies cp on cp.id = c.company_id 
            left join countries ct on ct.iso = c.language
            left join payment_methods_contacts p on p.code = c.payment_method
            left join currencies cr on cr.iso_code = c.currency 
            WHERE c.id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(":id", $id, PDO::PARAM_INT);
    $stmt->execute();

    $contact = $stmt->fetch(PDO::FETCH_ASSOC);
    echo json_encode($contact);
} catch (PDOException $e) {
    echo json_encode(["error" => "Erro ao buscar contato: " . $e->getMessage()]);
}
?>
