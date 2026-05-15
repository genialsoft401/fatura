<?php
require_once '../../../app/config/db.php';
require_once '../../../vendor/autoload.php';

use Dompdf\{Dompdf, Options};

session_start();

$id = $_GET['id'] ?? null;
$company_id = $_SESSION['user']['company_id'] ?? null;

if (!$id) {
  die('Parâmetros inválidos.');
}

$sql = "SELECT 
    p.*, 
    e.id AS employee_id,
    e.name AS employee_name,
    e.position,
    e.admission_date,
    e.iban,
    e.bi,
    comp.name AS company_name,
    comp.logo_url,
    comp.phone AS company_phone,
    comp.email AS company_email,
    comp.registration_number,
    comp.address AS company_address
FROM payroll p
JOIN employees e ON e.id = p.employee_id
JOIN companies comp ON comp.id = p.company_id
WHERE p.id = ?";

$params = [$id];

if (!empty($company_id)) {
  $sql .= " AND p.company_id = ?";
  $params[] = $company_id;
}

$stmt = $pdo->prepare($sql);
$stmt->execute($params);

$dados = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$dados) {
  die('Registro não encontrado.');
}

/*
|--------------------------------------------------------------------------
| FALTAS
|--------------------------------------------------------------------------
*/

$reference = $dados['reference_month'];

$yearMonth = explode('-', $reference);

$firstDay = $yearMonth[0] . '-' . $yearMonth[1] . '-01';
$lastDay  = date('Y-m-t', strtotime($firstDay));

$stmt = $pdo->prepare("
    SELECT COUNT(*) 
    FROM attendance
    WHERE employee_id = ?
    AND company_id = ?
    AND type = 'falta'
    AND date BETWEEN ? AND ?
");

$stmt->execute([
  $dados['employee_id'],
  $company_id,
  $firstDay,
  $lastDay
]);

$absences = (int)$stmt->fetchColumn();

/*
|--------------------------------------------------------------------------
| DOMPDF
|--------------------------------------------------------------------------
*/

$options = new Options();
$options->set('isRemoteEnabled', true);

$dompdf = new Dompdf($options);

/*
|--------------------------------------------------------------------------
| VALORES
|--------------------------------------------------------------------------
*/

$base  = (float)$dados['base_salary'];
$bon   = (float)($dados['bonuses'] ?? 0);
$food  = (float)($dados['food_allowance'] ?? 0);
$trans = (float)($dados['transport_allowance'] ?? 0);
$comm  = (float)($dados['commissions'] ?? 0);
$sales = (float)($dados['sales'] ?? 0);

$vacPct = (int)($dados['vacation_subsidy_pct'] ?? 0);
$t13Pct = (int)($dados['thirteenth_subsidy_pct'] ?? 0);

$vacSub = $base * ($vacPct / 100);
$t13Sub = $base * ($t13Pct / 100);

$gross = $base + $bon + $food + $trans + $comm + $sales + $vacSub + $t13Sub;

$discountsTotal = (float)($dados['discounts'] ?? 0);

$inss = (float)($dados['inss_value'] ?? 0);
$irt  = (float)($dados['irt_value'] ?? 0);

$otherDiscounts = max(0, $discountsTotal - $inss - $irt);

/*
|--------------------------------------------------------------------------
| HELPERS
|--------------------------------------------------------------------------
*/

function kz($v)
{
  return 'Kz ' . number_format((float)$v, 2, ',', '.');
}

function esc($s)
{
  return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
}

/*
|--------------------------------------------------------------------------
| LOGO
|--------------------------------------------------------------------------
*/

$baseUrl = '';

if (!empty($_SERVER['HTTP_HOST'])) {
  $baseUrl = 'http://' . $_SERVER['HTTP_HOST'];
}

$logoSrc = '';

if (!empty($dados['logo_url']) && $baseUrl) {
  $logoSrc = $baseUrl . '/assets/img/companies/' . $dados['logo_url'];
}

/*
|--------------------------------------------------------------------------
| HTML
|--------------------------------------------------------------------------
*/

$html = "
<style>

@page{
    margin:25px;
}

body{
    font-family: DejaVu Sans, sans-serif;
    color:#1f2937;
    font-size:11px;
}

.container{
    border:1px solid #d1d5db;
    padding:25px;
}

.header{
    width:100%;
    margin-bottom:18px;
}

.header td{
    vertical-align:top;
}

.logo{
    width:85px;
    height:85px;
    border-radius:50%;
}

.company{
    text-align:right;
}

.company h1{
    margin:0;
    font-size:28px;
    color:#1d4ed8;
}

.company h2{
    margin:0;
    font-size:16px;
    color:#111827;
}

.company p{
    margin:2px 0;
    color:#6b7280;
    font-size:10px;
}

.separator{
    border-bottom:2px solid #111827;
    margin:10px 0 18px 0;
}

.employee{
    width:100%;
    margin-bottom:20px;
}

.employee td{
    padding:4px 0;
    font-size:11px;
}

.label{
    width:140px;
    font-weight:bold;
    color:#374151;
}

.section{
    margin-top:18px;
    margin-bottom:8px;
    font-size:14px;
    font-weight:bold;
}

.table{
    width:100%;
    border-collapse:collapse;
}

.table th{
    background:#5b7ecb;
    color:#fff;
    padding:8px;
    border:1px solid #d1d5db;
    font-size:11px;
}

.table td{
    padding:8px;
    border:1px solid #e5e7eb;
    font-size:11px;
}

.right{
    text-align:right;
}

.total{
    background:#f3f4f6;
    font-weight:bold;
}

.net-box{
    margin-top:20px;
    background:#eef2ff;
    border:1px solid #c7d2fe;
    padding:12px;
}

.net-title{
    font-size:13px;
    color:#1d4ed8;
    font-weight:bold;
}

.net-value{
    font-size:22px;
    font-weight:bold;
    margin-top:5px;
}

.signature{
    margin-top:60px;
    width:250px;
    float:right;
    text-align:center;
}

.signature-line{
    border-top:1px solid #111827;
    padding-top:6px;
}

.footer{
    margin-top:80px;
    text-align:center;
    color:#6b7280;
    font-size:10px;
}

</style>

<div class='container'>

    <!-- HEADER -->
    <table class='header'>
        <tr>

            <td width='20%'>

                " . (
  $logoSrc
  ? "<img src='" . esc($logoSrc) . "' class='logo'>"
  : "<div style='width:85px;height:85px;border-radius:50%;background:#4f6fbf;color:#fff;text-align:center;line-height:85px;'>LOGO</div>"
) . "

            </td>

            <td width='80%' class='company'>

                <h2>" . esc($dados['company_name']) . "</h2>

                <h1>RECÍBO DE SALÁRIO</h1>

                <p>NIF: " . esc($dados['registration_number']) . "</p>
                <p>" . esc($dados['company_address']) . "</p>
                <p>" . esc($dados['company_phone']) . "</p>
                <p>" . esc($dados['company_email']) . "</p>

            </td>

        </tr>
    </table>

    <div class='separator'></div>

    <!-- EMPLOYEE -->
    <table class='employee'>

        <tr>
            <td class='label'>Mês:</td>
            <td>" . esc($dados['reference_month']) . "</td>

            <td class='label'>Funcionário:</td>
            <td>" . esc($dados['employee_name']) . "</td>
        </tr>

        <tr>
            <td class='label'>Funcionário ID:</td>
            <td>" . esc(str_pad($dados['employee_id'], 5, '0', STR_PAD_LEFT)) . "</td>

            <td class='label'>Função:</td>
            <td>" . esc($dados['position']) . "</td>
        </tr>

        <tr>
            <td class='label'>BI:</td>
            <td>" . esc($dados['bi']) . "</td>

            <td class='label'>IBAN:</td>
            <td>" . esc($dados['iban']) . "</td>
        </tr>

        <tr>
            <td class='label'>Admissão:</td>
            <td>" . (
  !empty($dados['admission_date'])
  ? date('d/m/Y', strtotime($dados['admission_date']))
  : ''
) . "</td>

            <td class='label'>Pagamento:</td>
            <td>Transferência Bancária</td>
        </tr>

    </table>

    <!-- EARNINGS -->
    <div class='section'>Proventos</div>

    <table class='table'>

        <thead>
            <tr>
                <th>Descrição</th>
                <th width='180'>Valor</th>
            </tr>
        </thead>

        <tbody>

            <tr>
                <td>Salário Base</td>
                <td class='right'>" . kz($base) . "</td>
            </tr>

            " . ($bon > 0 ? "
            <tr>
                <td>Bónus</td>
                <td class='right'>" . kz($bon) . "</td>
            </tr>
            " : "") . "

            " . ($food > 0 ? "
            <tr>
                <td>Subsídio de Alimentação</td>
                <td class='right'>" . kz($food) . "</td>
            </tr>
            " : "") . "

            " . ($trans > 0 ? "
            <tr>
                <td>Subsídio de Transporte</td>
                <td class='right'>" . kz($trans) . "</td>
            </tr>
            " : "") . "

            " . ($comm > 0 ? "
            <tr>
                <td>Comissões</td>
                <td class='right'>" . kz($comm) . "</td>
            </tr>
            " : "") . "

            " . ($sales > 0 ? "
            <tr>
                <td>Vendas</td>
                <td class='right'>" . kz($sales) . "</td>
            </tr>
            " : "") . "

            " . ($vacSub > 0 ? "
            <tr>
                <td>Subsídio de Férias ({$vacPct}%)</td>
                <td class='right'>" . kz($vacSub) . "</td>
            </tr>
            " : "") . "

            " . ($t13Sub > 0 ? "
            <tr>
                <td>Subsídio 13º ({$t13Pct}%)</td>
                <td class='right'>" . kz($t13Sub) . "</td>
            </tr>
            " : "") . "

            <tr class='total'>
                <td>Total de Proventos</td>
                <td class='right'>" . kz($gross) . "</td>
            </tr>

        </tbody>

    </table>

    <!-- DESCONTOS -->
    <div class='section'>Descontos</div>

    <table class='table'>

        <thead>
            <tr>
                <th>Descrição</th>
                <th width='180'>Valor</th>
            </tr>
        </thead>

        <tbody>

            " . ($otherDiscounts > 0 ? "
            <tr>
                <td>
                    Ausências / Outros Descontos
                    " . ($absences > 0 ? "({$absences} dias)" : "") . "
                </td>
                <td class='right'>" . kz($otherDiscounts) . "</td>
            </tr>
            " : "") . "

            " . ($inss > 0 ? "
            <tr>
                <td>INSS</td>
                <td class='right'>" . kz($inss) . "</td>
            </tr>
            " : "") . "

            " . ($irt > 0 ? "
            <tr>
                <td>IRT</td>
                <td class='right'>" . kz($irt) . "</td>
            </tr>
            " : "") . "

            <tr class='total'>
                <td>Total de Descontos</td>
                <td class='right'>" . kz($discountsTotal) . "</td>
            </tr>

        </tbody>

    </table>

    <!-- NET -->
    <div class='net-box'>

        <div class='net-title'>
            Salário Líquido
        </div>

        <div class='net-value'>
            " . kz($dados['net_salary']) . "
        </div>

    </div>

    <!-- SIGN -->
    <div class='signature'>

        <div class='signature-line'></div>

        Finance Manager - Company

    </div>

    <div style='clear:both'></div>

    <!-- FOOTER -->
    <div class='footer'>
        Documento processado automaticamente por sistema informático
    </div>

</div>
";

/*
|--------------------------------------------------------------------------
| RENDER PDF
|--------------------------------------------------------------------------
*/

$dompdf->loadHtml($html, 'UTF-8');

$dompdf->setPaper('A4', 'portrait');

$dompdf->render();

$dompdf->stream(
  "recibo_salario_" . $dados['id'] . ".pdf",
  ['Attachment' => false]
);
