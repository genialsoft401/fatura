<?php
require_once '../../../app/config/db.php';
header('Content-Type: application/json'); 
session_start();

try { 
    $sql = "SELECT i.id, i.company_id, ivs.name as status_invoice, ivs.color, ivs.text_color,
     concat(YEAR(i.issue_date),'/',i.id) as codigo, c.name as cliente, i.issue_date, 
     i.due_date, case when i.converted_total > 0 then round(i.converted_total,2) else i.final_total end as final_total,
      cr.symbol, cr.position, concat(cr.currency, ' (',cr.iso_code,')') as currency 
    FROM invoices i
    join contact c on c.id = i.contact_id
    join currencies cr on cr.iso_code = i.currency
    join invoice_status ivs on ivs.id = i.status
    where i.company_id = :company_id 
    order by i.id DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(":company_id", $_SESSION['user']['company_id'], PDO::PARAM_INT);
    $stmt->execute();

    $invoices = $stmt->fetchAll(PDO::FETCH_ASSOC);
 
    $formattedInvoices = array_map(function ($invoice) {
        $issueDate = new DateTime($invoice['issue_date']);
        $dueDate = clone $issueDate;
        $dueDate->modify('+' . intval($invoice['due_date']) . ' days');
        $invoice['due_date'] = $dueDate->format('Y-m-d');
        return $invoice;
    }, $invoices);
 
    echo json_encode($formattedInvoices);
} catch (Exception $e) { 
    echo json_encode(['error' => 'Erro ao buscar dados: ' . $e->getMessage()]);
}
