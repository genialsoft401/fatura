<?php
require_once '../../../app/config/db.php';
require_once '../../../vendor/autoload.php';

session_start();

use Dompdf\Dompdf;
use Dompdf\Options;

$company_id = $_SESSION['user']['company_id'] ?? null;

if (!$company_id) {
    die('Empresa não identificada.');
}

/*
|--------------------------------------------------------------------------
| EMPRESA
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT 
        name,
        logo_url,
        registration_number,
        email,
        phone,
        address
    FROM companies
    WHERE id = ?
");

$stmt->execute([$company_id]);

$empresa = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$empresa) {
    die('Empresa não encontrada.');
}

/*
|--------------------------------------------------------------------------
| FUNCIONÁRIOS
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT 
        name,
        bi,
        position,
        salary,
        status,
        iban
    FROM employees
    WHERE company_id = ?
    ORDER BY status DESC, name ASC
");

$stmt->execute([$company_id]);

$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

/*
|--------------------------------------------------------------------------
| HELPERS
|--------------------------------------------------------------------------
*/

function h($s)
{
    return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
}

function kz($v)
{
    return 'Kz ' . number_format((float)$v, 2, ',', '.');
}

/*
|--------------------------------------------------------------------------
| LOGO BASE64
|--------------------------------------------------------------------------
*/

$logoHtml = '';

if (!empty($empresa['logo_url'])) {

    $logoPath = trim($empresa['logo_url']);

    $publicRoot = realpath(__DIR__ . '/../../');

    $candidate = ltrim($logoPath, '/');

    if (
        strpos($candidate, '/') === false &&
        strpos($candidate, '\\') === false
    ) {
        $candidate = 'assets/img/companies/' . $candidate;
    }

    $full = realpath($publicRoot . '/' . $candidate);

    if (
        $full &&
        str_starts_with($full, $publicRoot) &&
        is_file($full)
    ) {

        $data = base64_encode(file_get_contents($full));

        $ext = strtolower(pathinfo($full, PATHINFO_EXTENSION));

        $mime =
            $ext === 'jpg' || $ext === 'jpeg'
            ? 'image/jpeg'
            : ($ext === 'webp'
                ? 'image/webp'
                : 'image/png');

        $logoHtml = "
            <img 
                src='data:$mime;base64,$data'
                style='height:75px; object-fit:contain; border-radius:10px;'
            >
        ";
    }
}

/*
|--------------------------------------------------------------------------
| TOTALS
|--------------------------------------------------------------------------
*/

$totalEmployees = count($rows);

$totalSalary = 0;

foreach ($rows as $r) {
    $totalSalary += (float)$r['salary'];
}

/*
|--------------------------------------------------------------------------
| HTML
|--------------------------------------------------------------------------
*/

$html = "
<!doctype html>
<html>

<head>
<meta charset='utf-8'>

<style>

@page{
    margin: 22px 25px;
}

body{
    font-family: DejaVu Sans, Arial, sans-serif;
    font-size:11px;
    color:#1e293b;
}

/*
|--------------------------------------------------------------------------
| HEADER
|--------------------------------------------------------------------------
*/

.header{
    width:100%;
    border-bottom:2px solid #dbeafe;
    padding-bottom:15px;
    margin-bottom:20px;
}

.header-table{
    width:100%;
    border-collapse:collapse;
}

.header-left{
    width:70%;
    vertical-align:top;
}

.header-right{
    width:30%;
    text-align:right;
    vertical-align:top;
}

.company-name{
    font-size:24px;
    font-weight:bold;
    color:#0f172a;
    margin-bottom:6px;
}

.report-title{
    font-size:30px;
    font-weight:bold;
    color:#2563eb;
    margin-bottom:5px;
    text-transform:uppercase;
}

.company-info{
    color:#64748b;
    font-size:11px;
    line-height:1.6;
}

.badge{
    display:inline-block;
    background:#eff6ff;
    color:#2563eb;
    padding:5px 12px;
    border-radius:20px;
    font-size:10px;
    font-weight:bold;
    margin-top:10px;
}

/*
|--------------------------------------------------------------------------
| SUMMARY
|--------------------------------------------------------------------------
*/

.summary{
    width:100%;
    margin-bottom:18px;
}

.summary-box{
    width:48%;
    display:inline-block;
    background:#f8fafc;
    border:1px solid #e2e8f0;
    border-radius:10px;
    padding:14px;
    box-sizing:border-box;
}

.summary-title{
    color:#64748b;
    font-size:11px;
    margin-bottom:6px;
}

.summary-value{
    font-size:22px;
    font-weight:bold;
    color:#0f172a;
}

/*
|--------------------------------------------------------------------------
| TABLE
|--------------------------------------------------------------------------
*/

.table{
    width:100%;
    border-collapse:collapse;
    margin-top:10px;
}

.table thead th{
    background:#2563eb;
    color:#fff;
    padding:10px;
    border:1px solid #dbeafe;
    font-size:11px;
    text-transform:uppercase;
}

.table tbody td{
    border:1px solid #e2e8f0;
    padding:9px;
    font-size:11px;
}

.table tbody tr:nth-child(even){
    background:#f8fafc;
}

.right{
    text-align:right;
}

.center{
    text-align:center;
}

.status-active{
    background:#dcfce7;
    color:#166534;
    padding:4px 10px;
    border-radius:20px;
    font-size:10px;
    font-weight:bold;
}

.status-inactive{
    background:#fee2e2;
    color:#991b1b;
    padding:4px 10px;
    border-radius:20px;
    font-size:10px;
    font-weight:bold;
}

/*
|--------------------------------------------------------------------------
| FOOTER
|--------------------------------------------------------------------------
*/

.footer{
    margin-top:30px;
    border-top:1px solid #e2e8f0;
    padding-top:10px;
    text-align:center;
    color:#64748b;
    font-size:10px;
}

</style>
</head>

<body>

<!-- HEADER -->
<div class='header'>

    <table class='header-table'>

        <tr>

            <td class='header-left'>

                <div class='company-name'>
                    " . h($empresa['name']) . "
                </div>

                <div class='report-title'>
                    Funcionários
                </div>

                <div class='company-info'>

                    NIF: " . h($empresa['registration_number'] ?? '-') . "<br>

                    " . h($empresa['address'] ?? '-') . "<br>

                    Tel: " . h($empresa['phone'] ?? '-') . "<br>

                    Email: " . h($empresa['email'] ?? '-') . "

                </div>

                <div class='badge'>
                    Gerado em " . date('d/m/Y H:i') . "
                </div>

            </td>

            <td class='header-right'>
                {$logoHtml}
            </td>

        </tr>

    </table>

</div>

<!-- SUMMARY -->
<div class='summary'>

    <div class='summary-box'>

        <div class='summary-title'>
            Total de Funcionários
        </div>

        <div class='summary-value'>
            {$totalEmployees}
        </div>

    </div>

    <div class='summary-box' style='float:right;'>

        <div class='summary-title'>
            Massa Salarial
        </div>

        <div class='summary-value'>
            " . kz($totalSalary) . "
        </div>

    </div>

</div>

<!-- TABLE -->
<table class='table'>

    <thead>

        <tr>
            <th width='24%'>Nome</th>
            <th width='16%'>BI</th>
            <th width='20%'>Cargo</th>
            <th width='15%'>Salário</th>
            <th width='10%'>Status</th>
            <th width='15%'>IBAN</th>
        </tr>

    </thead>

    <tbody>
";

/*
|--------------------------------------------------------------------------
| ROWS
|--------------------------------------------------------------------------
*/

foreach ($rows as $r) {

    $status = strtolower(trim($r['status'] ?? ''));

    $statusClass =
        $status === 'ativo' ||
        $status === 'active'
        ? 'status-active'
        : 'status-inactive';

    $html .= "
    <tr>

        <td>
            " . h($r['name']) . "
        </td>

        <td class='center'>
            " . h($r['bi']) . "
        </td>

        <td>
            " . h($r['position']) . "
        </td>

        <td class='right'>
            " . kz($r['salary']) . "
        </td>

        <td class='center'>
            <span class='{$statusClass}'>
                " . h($r['status']) . "
            </span>
        </td>

        <td>
            " . h($r['iban']) . "
        </td>

    </tr>
    ";
}

/*
|--------------------------------------------------------------------------
| FINAL HTML
|--------------------------------------------------------------------------
*/

$html .= "

    </tbody>

</table>

<div class='footer'>

    Documento processado automaticamente • 
    " . h($empresa['name']) . "

</div>

</body>
</html>
";

/*
|--------------------------------------------------------------------------
| PDF
|--------------------------------------------------------------------------
*/

$options = new Options();

$options->set('isRemoteEnabled', true);

$dompdf = new Dompdf($options);

$dompdf->loadHtml($html, 'UTF-8');

$dompdf->setPaper('A4', 'portrait');

$dompdf->render();

$dompdf->stream(
    'funcionarios.pdf',
    ['Attachment' => false]
);
