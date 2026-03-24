<?php
session_start();
require_once '../../../app/config/db.php';
require_once '../../../vendor/autoload.php'; // PhpSpreadsheet

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

header('Content-Type: application/octet-stream');

$company_id = $_SESSION['user']['company_id'] ?? null;

if (!$company_id) {
    die('Empresa não definida na sessão.');
}

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// Cabeçalhos
$sheet->fromArray(
    ['Funcionário', 'Referência', 'Salário Base', 'Bônus', 'Descontos', 'Salário Líquido', 'Status', 'Data de Pagamento'],
    null,
    'A1'
);

// Busca dados
$sql = "SELECT p.*, e.name AS employee_name
        FROM payroll p
        JOIN employees e ON e.id = p.employee_id
        WHERE p.company_id = ?
        ORDER BY p.reference_month DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute([$company_id]);

$dados = $stmt->fetchAll(PDO::FETCH_ASSOC);

$row = 2;
foreach ($dados as $linha) {
    $sheet->fromArray([
        $linha['employee_name'],
        $linha['reference_month'],
        number_format($linha['base_salary'], 2, ',', '.'),
        number_format($linha['bonuses'], 2, ',', '.'),
        number_format($linha['discounts'], 2, ',', '.'),
        number_format($linha['net_salary'], 2, ',', '.'),
        $linha['status'],
        $linha['payment_date'] ? date('d/m/Y', strtotime($linha['payment_date'])) : ''
    ], null, "A{$row}");

    $row++;
}

// Forçar download
header('Content-Disposition: attachment; filename="folha_pagamento.xlsx"');
$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
