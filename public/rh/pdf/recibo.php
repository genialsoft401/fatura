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

// Se tiver sessão de empresa, restringe por segurança.
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

// Buscar faltas no mês da folha
$reference = $dados['reference_month'];
$yearMonth = explode('-', $reference);
$firstDay = $yearMonth[0] . '-' . $yearMonth[1] . '-01';
$lastDay = date('Y-m-t', strtotime($firstDay));

$stmt = $pdo->prepare("SELECT COUNT(*) FROM attendance 
                       WHERE employee_id = ? AND company_id = ? 
                       AND type = 'falta' 
                       AND date BETWEEN ? AND ?");
$stmt->execute([
    $dados['employee_id'],
    $company_id,
    $firstDay,
    $lastDay
]);
$absences = (int)$stmt->fetchColumn();

$options = new Options();
$options->set('isRemoteEnabled', true);
$dompdf = new Dompdf($options);

$base = (float)$dados['base_salary'];
$bon  = (float)($dados['bonuses'] ?? 0);
$food = (float)($dados['food_allowance'] ?? 0);
$trans = (float)($dados['transport_allowance'] ?? 0);
$comm = (float)($dados['commissions'] ?? 0);
$sales = (float)($dados['sales'] ?? 0);
$vacPct = (int)($dados['vacation_subsidy_pct'] ?? 0);
$t13Pct = (int)($dados['thirteenth_subsidy_pct'] ?? 0);
$vacSub = $base * ($vacPct / 100);
$t13Sub = $base * ($t13Pct / 100);

$gross = $base + $bon + $food + $trans + $comm + $sales + $vacSub + $t13Sub;

// No banco, `discounts` já é o total (ausências + INSS + IRT + outros). Aqui só quebramos para exibição.
$discountsTotal = (float)($dados['discounts'] ?? 0);
$inss = (float)($dados['inss_value'] ?? 0);
$irt  = (float)($dados['irt_value'] ?? 0);
$otherDiscounts = max(0, $discountsTotal - $inss - $irt);

function kz($v){ return 'Kz ' . number_format((float)$v, 2, ',', '.'); }
function esc($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

$baseUrl = '';
if (!empty($_SERVER['HTTP_HOST'])) {
    $baseUrl = 'https://' . $_SERVER['HTTP_HOST'];
}
$logoSrc = '';
if (!empty($dados['logo_url']) && $baseUrl) {
    $logoSrc = $baseUrl . '/assets/img/companies/' . $dados['logo_url'];
}

$html = "
<style>
  @page { margin: 22px 28px; }
  body{ font-family: DejaVu Sans, Arial, Helvetica, sans-serif; font-size: 12px; color:#1f2d3d; }
  .row{ width:100%; }
  .muted{ color:#6b7280; }
  .small{ font-size: 10px; }
  .h1{ font-size: 18px; font-weight: 700; margin:0; }
  .h2{ font-size: 12px; font-weight: 700; margin:0; }
  .center{ text-align:center; }
  .right{ text-align:right; }
  .bold{ font-weight:700; }

  table{ width:100%; border-collapse:collapse; }
  .no-border td{ border:none; padding:0; }

  .box-title{ font-size: 12px; font-weight:700; margin: 14px 0 6px 0; }
  .hr{ height:1px; background:#cbd5e1; margin:4px 0 0 0; }

  .tbl th, .tbl td{ padding:7px 8px; border:1px solid #d9dee6; }
  .tbl th{ background:#334155; color:#fff; font-weight:700; }
  .tbl .zebra{ background:#f8fafc; }

  .totals{ width:45%; margin-left:auto; margin-top:10px; }
  .totals td{ border:none; padding:4px 0; }

  .signature{ margin-top:28px; }
  .signature .line{ width:70%; height:1px; background:#111827; margin: 0 auto 6px auto; }

  .footer{ position: fixed; left: 0; right: 0; bottom: -10px; font-size: 9px; color:#6b7280; }
  .footer .left{ float:left; }
  .footer .right{ float:right; }
</style>

<!-- Cabeçalho -->
<table class='no-border'>
  <tr>
    <td style='width:30%; vertical-align:top;'>
      " . ($logoSrc ? "<img src='".esc($logoSrc)."' style='width:120px; height:auto;'>" : "") . "
    </td>
    <td style='width:70%; vertical-align:top;' class='right'>
      <div class='h2'>".esc($dados['company_name'] ?? '')."</div>
      <div class='small muted'>NIF: ".esc($dados['registration_number'] ?? '')."</div>
      <div class='small muted'>".esc($dados['company_address'] ?? '')."</div>
      <div class='small muted'>Telefone: ".esc($dados['company_phone'] ?? '')."</div>
      <div class='small muted'>Email: ".esc($dados['company_email'] ?? '')."</div>
    </td>
  </tr>
</table>

<div class='center' style='margin-top:10px;'>
  <div class='h1'>RECIBO DE SALÁRIO</div>
  <div style='margin-top:4px; color:#b45309;'>Mês de Referência: ".esc($dados['reference_month'])."</div>
</div>

<!-- Dados do Funcionário -->
<div class='box-title'>DADOS DO FUNCIONÁRIO</div>
<div class='hr'></div>
<table class='no-border' style='margin-top:8px;'>
  <tr>
    <td style='width:18%;' class='small bold'>CÓDIGO:</td><td style='width:32%;' class='small'>".esc(str_pad((string)$dados['employee_id'], 5, '0', STR_PAD_LEFT))."</td>
    <td style='width:18%;' class='small bold'>NOME:</td><td style='width:32%;' class='small'>".esc($dados['employee_name'])."</td>
  </tr>
  <tr>
    <td class='small bold'>Nº DO BI:</td><td class='small'>".esc($dados['bi'] ?? '')."</td>
    <td class='small bold'>FUNÇÃO:</td><td class='small'>".esc($dados['position'] ?? '')."</td>
  </tr>
  <tr>
    <td class='small bold'>IBAN:</td><td class='small'>".esc($dados['iban'] ?? '')."</td>
    <td class='small bold'>DATA ADMISSÃO:</td><td class='small'>".esc(!empty($dados['admission_date']) ? date('d/m/Y', strtotime($dados['admission_date'])) : '')."</td>
  </tr>
</table>

<!-- Demonstrativo -->
<div class='box-title'>DEMONSTRATIVO DE PAGAMENTO</div>
<div class='hr'></div>

<table class='tbl' style='margin-top:10px;'>
  <thead>
    <tr>
      <th style='width:12%;'>Cód.</th>
      <th style='width:48%;'>Descrição</th>
      <th style='width:20%;' class='right'>Proventos</th>
      <th style='width:20%;' class='right'>Descontos</th>
    </tr>
  </thead>
  <tbody>
    <tr class='zebra'><td>001</td><td>Salário Base</td><td class='right'>".kz($base)."</td><td class='right'>-</td></tr>
    " . ($bon > 0 ? "<tr><td>101</td><td>Bónus</td><td class='right'>".kz($bon)."</td><td class='right'>-</td></tr>" : "") . "
    " . ($food > 0 ? "<tr class='zebra'><td>102</td><td>Alimentação</td><td class='right'>".kz($food)."</td><td class='right'>-</td></tr>" : "") . "
    " . ($trans > 0 ? "<tr><td>103</td><td>Transporte</td><td class='right'>".kz($trans)."</td><td class='right'>-</td></tr>" : "") . "
    " . ($comm > 0 ? "<tr class='zebra'><td>104</td><td>Comissões</td><td class='right'>".kz($comm)."</td><td class='right'>-</td></tr>" : "") . "
    " . ($sales > 0 ? "<tr><td>105</td><td>Vendas</td><td class='right'>".kz($sales)."</td><td class='right'>-</td></tr>" : "") . "
    " . ($vacSub > 0 ? "<tr class='zebra'><td>106</td><td>Subsídio de férias ({$vacPct}%)</td><td class='right'>".kz($vacSub)."</td><td class='right'>-</td></tr>" : "") . "
    " . ($t13Sub > 0 ? "<tr><td>107</td><td>Subsídio 13º ({$t13Pct}%)</td><td class='right'>".kz($t13Sub)."</td><td class='right'>-</td></tr>" : "") . "

    <tr>
      <td></td><td class='bold right'>Total Proventos</td>
      <td class='right bold'>".kz($gross)."</td>
      <td class='right'>-</td>
    </tr>

    " . ($otherDiscounts > 0 ? "<tr class='zebra'><td>501</td><td>Ausências / Outros Descontos" . ($absences > 0 ? " ({$absences} dias)" : "") . "</td><td class='right'>-</td><td class='right'>".kz($otherDiscounts)."</td></tr>" : "") . "
    " . ($inss > 0 ? "<tr><td>502</td><td>INSS (3%)</td><td class='right'>-</td><td class='right'>".kz($inss)."</td></tr>" : "") . "
    " . ($irt > 0 ? "<tr class='zebra'><td>503</td><td>IRT</td><td class='right'>-</td><td class='right'>".kz($irt)."</td></tr>" : "") . "

    <tr>
      <td></td><td class='bold right'>Total Descontos</td>
      <td class='right'>-</td>
      <td class='right bold'>".kz($discountsTotal)."</td>
    </tr>
  </tbody>
</table>

<table class='totals'>
  <tr><td class='right bold'>Salário Líquido:</td><td class='right bold'>".kz((float)$dados['net_salary'])."</td></tr>
</table>

<div class='signature'>
  <div class='line'></div>
  <div class='center bold'>Assinatura do Funcionário</div>
</div>

<div class='footer'>
  <div class='left'>Processado por computador | ".esc($dados['company_name'] ?? '')."</div>
  <div class='right'>Página 1 de 1</div>
</div>
";

$dompdf->loadHtml($html, 'UTF-8');
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();
$dompdf->stream("recibo_{$dados['id']}.pdf", ['Attachment' => false]);
