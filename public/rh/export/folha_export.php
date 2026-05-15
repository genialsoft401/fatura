<?php
session_start();
require_once '../../../app/config/db.php';
require_once '../../../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

$company_id = $_SESSION['user']['company_id'] ?? null;

if (!$company_id) {
    die('Empresa não definida na sessão.');
}

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// ================= HEADER PROFISSIONAL =================
$headers = [
    'Funcionário',
    'Referência',
    'Salário Base',
    'Bónus',
    'Descontos',
    'Salário Líquido',
    'Status',
    'Data de Pagamento'
];

$sheet->fromArray($headers, null, 'A1');

// Estilo do cabeçalho
$sheet->getStyle('A1:H1')->applyFromArray([
    'font' => [
        'bold' => true,
        'color' => ['rgb' => 'FFFFFF']
    ],
    'fill' => [
        'fillType' => Fill::FILL_SOLID,
        'startColor' => ['rgb' => '2F5597']
    ],
    'alignment' => [
        'horizontal' => Alignment::HORIZONTAL_CENTER
    ],
    'borders' => [
        'allBorders' => [
            'borderStyle' => Border::BORDER_THIN
        ]
    ]
]);

// Freeze header
$sheet->freezePane('A2');

// ================= DADOS =================
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
        (float)$linha['base_salary'],
        (float)$linha['bonuses'],
        (float)$linha['discounts'],
        (float)$linha['net_salary'],
        $linha['status'],
        $linha['payment_date'] ? date('d/m/Y', strtotime($linha['payment_date'])) : ''
    ], null, "A{$row}");

    $row++;
}

// ================= FORMATAÇÃO MONETÁRIA =================
$sheet->getStyle("C2:F{$row}")
    ->getNumberFormat()
    ->setFormatCode('#,##0.00');

// ================= ESTILO LINHAS =================
$sheet->getStyle("A2:H{$row}")->applyFromArray([
    'borders' => [
        'allBorders' => [
            'borderStyle' => Border::BORDER_THIN
        ]
    ],
    'alignment' => [
        'vertical' => Alignment::VERTICAL_CENTER
    ]
]);

// ================= AUTO WIDTH =================
foreach (range('A', 'H') as $col) {
    $sheet->getColumnDimension($col)->setAutoSize(true);
}

// ================= NOME DO FICHEIRO =================
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="Folha_Pagamento_Profissional.xlsx"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
