<?php
session_start();
require_once '../../../app/config/db.php';
require_once '../../../vendor/autoload.php'; // PhpSpreadsheet

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

header('Content-Type: application/octet-stream');

// Verifica se é uma consulta de progresso
if (isset($_GET['status'])) {
    echo json_encode(['progress' => $_SESSION['export_progress'] ?? 0]);
    exit;
}

// Reseta o progresso
$_SESSION['export_progress'] = 0;

// Verifica o formato solicitado
$formato = isset($_GET['formato']) ? $_GET['formato'] : 'excel';

try {
    $_SESSION['export_progress'] = 10; // Progresso inicial

    // Consulta para buscar as faturas
    $sql = "SELECT 
                p.id AS proforma_id,
                concat(YEAR(p.issue_date), '/', p.id) as codigo,
                p.issue_date,
                p.due_date,
                p.final_total,
                p.total_tax,
                p.subtotal_without_tax,
                c.name as client_name,
                comp.name as company_name
            FROM proformas p
            JOIN companies comp ON comp.id = p.company_id
            JOIN contact c ON c.id = p.contact_id";

    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $invoices = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $_SESSION['export_progress'] = 30; // Progresso após a consulta

    if ($formato === 'excel') {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Definir cabeçalhos
        $sheet->fromArray(
            ['Número da Fatura', 'Data Emissão', 'Data Vencimento', 'Cliente', 'Empresa', 'Total Líquido', 'Total Imposto', 'Total Final', 'Produtos/Serviços da Fatura'],
            NULL,
            'A1'
        );

        $row = 2;
        $totalFaturas = count($invoices);
        $faturaAtual = 0;

        foreach ($invoices as $invoice) {
            $faturaAtual++;

            // Buscar itens da fatura
            $sqlItems = "SELECT 
                            it.description,
                            ii.quantity,
                            ii.unit_price,
                            ii.tax
                        FROM proforma_items ii
                        JOIN items it ON it.id = ii.item_id
                        WHERE ii.proforma_id = :proformaId";

            $stmtItems = $pdo->prepare($sqlItems);
            $stmtItems->bindParam(':proformaId', $invoice['proforma_id'], PDO::PARAM_INT);
            $stmtItems->execute();
            $items = $stmtItems->fetchAll(PDO::FETCH_ASSOC);

            // Concatenar os itens em uma única string
            $itemsString = implode(', ', array_map(function ($item) {
                return "{$item['description']} ({$item['quantity']}x " . number_format($item['unit_price'], 2, ',', '.') . " - {$item['tax']}% IVA)";
            }, $items));

            // Adicionar os dados na planilha
            $sheet->fromArray(
                [$invoice['codigo'], $invoice['issue_date'], $invoice['due_date'], $invoice['client_name'], $invoice['company_name'], 
                number_format($invoice['subtotal_without_tax'], 2, ',', '.'), number_format($invoice['total_tax'], 2, ',', '.'), 
                number_format($invoice['final_total'], 2, ',', '.'), $itemsString], NULL, "A$row"
            );

            $row++;

            // Atualiza o progresso proporcionalmente
            $_SESSION['export_progress'] = 30 + intval(($faturaAtual / $totalFaturas) * 50);
        }

        $_SESSION['export_progress'] = 90; // Finalizando a exportação

        // Forçar o download do arquivo
        header('Content-Disposition: attachment; filename="faturas.xlsx"');
        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');

        $_SESSION['export_progress'] = 100; // Concluído

    } elseif ($formato === 'csv') {
        $_SESSION['export_progress'] = 50;

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="faturas.csv"');

        $output = fopen('php://output', 'w');
        fputcsv($output, ['Número da Fatura', 'Data Emissão', 'Data Vencimento', 'Cliente', 'Empresa', 'Total Líquido', 'Total Imposto', 'Total Final', 'Produtos/Serviços da Fatura']);

        $faturaAtual = 0;

        foreach ($invoices as $invoice) {
            $faturaAtual++;

            // Buscar itens da fatura
            $stmtItems = $pdo->prepare($sqlItems);
            $stmtItems->bindParam(':proformaId', $invoice['proforma_id'], PDO::PARAM_INT);
            $stmtItems->execute();
            $items = $stmtItems->fetchAll(PDO::FETCH_ASSOC);

            // Concatenar os itens
            $itemsString = implode(', ', array_map(function ($item) {
                return "{$item['description']} ({$item['quantity']}x " . number_format($item['unit_price'], 2, ',', '.') . " - {$item['tax']}% IVA)";
            }, $items));

            fputcsv($output, [$invoice['codigo'], $invoice['issue_date'], $invoice['due_date'], $invoice['client_name'], $invoice['company_name'],
                number_format($invoice['subtotal_without_tax'], 2, ',', '.'), number_format($invoice['total_tax'], 2, ',', '.'), 
                number_format($invoice['final_total'], 2, ',', '.'), $itemsString]);

            $_SESSION['export_progress'] = 50 + intval(($faturaAtual / $totalFaturas) * 50);
        }

        fclose($output);
        $_SESSION['export_progress'] = 100;
    }

    exit;

} catch (Exception $e) {
    echo json_encode(['error' => 'Erro ao gerar o arquivo: ' . $e->getMessage()]);
    exit;
}
