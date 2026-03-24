<?php
require_once '../../../app/config/db.php';
require_once '../../../vendor/autoload.php'; // Garante que dompdf esteja incluso
session_start();

use Dompdf\Dompdf;
use Dompdf\Options;

$mes = $_GET['mes'] ?? date('Y-m');
$company_id = $_SESSION['user']['company_id'];

if (!$mes || !$company_id) {
    die('Parâmetros inválidos.');
}

$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Buscar dados da empresa
$stmt = $pdo->prepare("SELECT * FROM companies WHERE id = ?");
$stmt->execute([$company_id]);
$empresa = $stmt->fetch(PDO::FETCH_ASSOC);

// Buscar todos os registros da folha
$sql = "SELECT p.*, e.name AS employee_name, e.document_type AS document, e.birth_date, e.position, e.contract_type, e.admission_date, e.iban
        FROM payroll p
        JOIN employees e ON e.id = p.employee_id
        WHERE p.company_id = ? AND p.reference_month = ?
        ORDER BY e.name";

$stmt = $pdo->prepare($sql);
$stmt->execute([$company_id, $mes]);
$folhas = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Geração do HTML para PDF
$html = "
<h2 style='text-align: center'>Folha de Pagamento - {$mes}</h2>
<h4 style='text-align: center'>{$empresa['name']}</h4>
<hr>";

foreach ($folhas as $f) {
    $faltas = 0;
    $firstDay = $mes . "-01";
    $lastDay = date('Y-m-t', strtotime($firstDay));

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM attendance WHERE employee_id = ? AND company_id = ? AND type = 'falta' AND date BETWEEN ? AND ?");
    $stmt->execute([$f['employee_id'], $company_id, $firstDay, $lastDay]);
    $faltas = $stmt->fetchColumn();

    $valorFaltas = ($f['base_salary'] / 30) * $faltas;

    $vacSub = (float)$f['base_salary'] * ((int)($f['vacation_subsidy_pct'] ?? 0) / 100);
    $t13Sub = (float)$f['base_salary'] * ((int)($f['thirteenth_subsidy_pct'] ?? 0) / 100);

    $gross = (float)$f['base_salary']
        + (float)($f['bonuses'] ?? 0)
        + (float)($f['food_allowance'] ?? 0)
        + (float)($f['transport_allowance'] ?? 0)
        + (float)($f['commissions'] ?? 0)
        + $vacSub
        + $t13Sub;

    $inss = $gross * 0.03;
    $irtBase = max(0, $gross - $inss);

    $calcIrt = function($base) {
        $b = (float)$base;
        if ($b <= 150000) return 0.0;
        $brackets = [
            [150001,   200000,    12500,   0.16],
            [200001,   300000,    31250,   0.18],
            [300001,   500000,    49250,   0.19],
            [500001,   1000000,   87250,   0.20],
            [1000001,  1500000,   187250,  0.21],
            [1500001,  2000000,   292250,  0.22],
            [2000001,  2500000,   402250,  0.23],
            [2500001,  5000000,   517250,  0.24],
            [5000001,  10000000,  1117250, 0.245],
            [10000001, PHP_INT_MAX,2342250, 0.25],
        ];
        foreach ($brackets as $br) {
            [$min, $max, $fixed, $rate] = $br;
            if ($b >= $min && $b <= $max) {
                $lower = ($min === 150001) ? 150000 : ($min - 1);
                $excess = max(0, $b - $lower);
                return (float)$fixed + ($rate * $excess);
            }
        }
        return 0.0;
    };

    $irt = $calcIrt($irtBase);
    $descontos = (float)($f['discounts'] ?? 0); // já inclui manuais + faltas + INSS + IRT no save_payroll
    $liquido = $gross - $descontos;

    $html .= "
    <div style='margin-top: 20px;'>
        <strong>Funcionário:</strong> {$f['employee_name']}<br>
        <strong>IBAN:</strong> {$f['iban']}<br>
        <strong>Documento:</strong> {$f['document']}<br>
        <strong>Data de Nascimento:</strong> " . date('d/m/Y', strtotime($f['birth_date'])) . "<br>
        <strong>Função:</strong> {$f['position']}<br>
        <strong>Tipo de Contrato:</strong> {$f['contract_type']}<br>
        <strong>Admissão:</strong> " . date('d/m/Y', strtotime($f['admission_date'])) . "
    </div>
    <table border='1' cellspacing='0' cellpadding='5' width='100%' style='margin-top: 10px; font-size: 12px'>
        <thead>
            <tr>
                <th>Cód</th>
                <th>Descrição</th>
                <th>Referência</th>
                <th>Proventos</th>
                <th>Descontos</th>
            </tr>
        </thead>
        <tbody>
            <tr><td>001</td><td>SALÁRIO BASE</td><td></td><td align='right'>Kz " . number_format($f['base_salary'], 2, ',', '.') . "</td><td></td></tr>
            " . ((float)$f['bonuses'] > 0 ? "<tr><td>400</td><td>BÔNUS</td><td></td><td align='right'>Kz " . number_format($f['bonuses'], 2, ',', '.') . "</td><td></td></tr>" : "") . "
            " . ((float)($f['food_allowance'] ?? 0) > 0 ? "<tr><td>410</td><td>SUBS. ALIMENTAÇÃO</td><td></td><td align='right'>Kz " . number_format($f['food_allowance'], 2, ',', '.') . "</td><td></td></tr>" : "") . "
            " . ((float)($f['transport_allowance'] ?? 0) > 0 ? "<tr><td>411</td><td>SUBS. TRANSPORTE</td><td></td><td align='right'>Kz " . number_format($f['transport_allowance'], 2, ',', '.') . "</td><td></td></tr>" : "") . "
            " . ((float)($f['commissions'] ?? 0) > 0 ? "<tr><td>412</td><td>COMISSÕES</td><td></td><td align='right'>Kz " . number_format($f['commissions'], 2, ',', '.') . "</td><td></td></tr>" : "") . "
            " . ($vacSub > 0 ? "<tr><td>413</td><td>SUBS. FÉRIAS</td><td>" . ((int)($f['vacation_subsidy_pct'] ?? 0)) . "%</td><td align='right'>Kz " . number_format($vacSub, 2, ',', '.') . "</td><td></td></tr>" : "") . "
            " . ($t13Sub > 0 ? "<tr><td>414</td><td>SUBS. 13º</td><td>" . ((int)($f['thirteenth_subsidy_pct'] ?? 0)) . "%</td><td align='right'>Kz " . number_format($t13Sub, 2, ',', '.') . "</td><td></td></tr>" : "") . "

            <tr><td>420</td><td>INSS (3%)</td><td></td><td></td><td align='right'>Kz " . number_format($inss, 2, ',', '.') . "</td></tr>
            <tr><td>421</td><td>IRT (Tabela)</td><td></td><td></td><td align='right'>Kz " . number_format($irt, 2, ',', '.') . "</td></tr>
            <tr><td>422</td><td>Faltas ($faltas)</td><td></td><td></td><td align='right'>Kz " . number_format($valorFaltas, 2, ',', '.') . "</td></tr>

            <tr><td colspan='3'><strong>Totais</strong></td>
                <td align='right'><strong>Kz " . number_format($gross, 2, ',', '.') . "</strong></td>
                <td align='right'><strong>Kz " . number_format($descontos, 2, ',', '.') . "</strong></td></tr>
            <tr><td colspan='4'><strong>Salário Líquido</strong></td>
                <td align='right'><strong>Kz " . number_format($liquido, 2, ',', '.') . "</strong></td></tr>
        </tbody>
    </table>
    <hr>";
}

// Geração do PDF
$options = new Options();
$options->set('isRemoteEnabled', true);
$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();
$dompdf->stream("folha_geral_{$mes}.pdf", ['Attachment' => 0]);
