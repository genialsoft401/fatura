<?php
require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../app/config/db.php';

use Dompdf\Dompdf;
use Dompdf\Options;

$receiptId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$receiptId) {
  die('Recibo não encontrado.');
}

// ================= SQL =================
$sql = "
SELECT 
  r.id AS receipt_id,
  r.serie, r.number, r.pay_date, r.amount_paid, r.payment_method, r.notes,

  i.id AS invoice_id,
  i.issue_date,
  i.reference AS invoice_reference,
  i.final_total,
  i.currency AS invoice_currency,

  cr.symbol AS moneySymbol,
  cr.position AS moneyPos,

  comp.name AS company_name,
  comp.address AS company_address,
  comp.city AS company_city,
  comp.country AS company_country,
  comp.phone AS company_phone,
  comp.email AS company_email,
  comp.registration_number AS company_nif,
  comp.logo_url,

  c.name AS client_name,
  c.email AS client_email,
  c.contributor AS client_contributor

FROM receipts r
JOIN invoices i ON i.id = r.invoice_id
JOIN companies comp ON comp.id = i.company_id
JOIN contact c ON c.id = i.contact_id
JOIN currencies cr ON cr.iso_code = i.currency
WHERE r.id = :rid
";

$st = $pdo->prepare($sql);
$st->execute(['rid' => $receiptId]);
$d = $st->fetch(PDO::FETCH_ASSOC);

if (!$d) {
  die('Recibo não encontrado.');
}

// ================= HELPERS =================
function h($s)
{
  return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
}

function formatMoney(float $value, string $symbol, string $pos): string
{
  $formatted = number_format($value, 2, ',', '.');
  return ($pos === 'left')
    ? ($symbol . ' ' . $formatted)
    : ($formatted . ' ' . $symbol);
}

// ================= BASE URL =================
$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? '';
$baseUrl = $host ? ($scheme . '://' . $host) : '';

// ✔ LOGO CORRIGIDO
$logo = (!empty($d['logo_url']) && $baseUrl)
  ? $baseUrl . '/assets/img/companies/' . $d['logo_url']
  : '';

// ================= HTML =================
$html = '<!doctype html><html><head><meta charset="utf-8">';

$html .= '
<style>
  body{
    font-family: Arial, Helvetica, sans-serif;
    font-size: 12px;
    color: #111;
    margin: 20px;
  }

  /* HEADER TABLE (compatível DomPDF) */
  .header{
    width: 100%;
    border-bottom: 2px solid #111;
    padding-bottom: 10px;
    margin-bottom: 15px;
  }

  .header td{
    vertical-align: top;
  }

  .title{
    font-size: 18px;
    font-weight: 700;
  }

  .subtitle{
    font-size: 12px;
    color: #6b7280;
    margin-top: 4px;
  }

  .logo{
    max-width: 140px;
    max-height: 60px;
  }

  .top-grid{
    width: 100%;
    margin-top: 10px;
  }

  .box{
    width: 48%;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    padding: 10px;
    vertical-align: top;
  }

  .box h3{
    margin: 0 0 8px 0;
    font-size: 13px;
    border-bottom: 1px solid #eee;
    padding-bottom: 5px;
  }

  .muted{
    color: #6b7280;
    margin: 3px 0;
  }

  .info{
    margin-top: 20px;
  }

  .info table{
    width: 100%;
    border-collapse: collapse;
  }

  .info td{
    padding: 8px 0;
    border-bottom: 1px dashed #ddd;
  }

  .info td:first-child{
    color: #374151;
  }

  .info td:last-child{
    text-align: right;
    font-weight: 600;
  }

  .footer{
    position: fixed;
    left: 20px;
    right: 20px;
    bottom: 15px;
    border-top: 1px solid #ddd;
    padding-top: 8px;
    font-size: 10px;
    color: #6b7280;
    text-align: center;
  }
</style>';

$html .= '</head><body>';

// ================= HEADER =================
$html .= '
<table class="header">
  <tr>
    <td style="width:70%;">
      <div class="title">RECIBO</div>
      <div class="subtitle">Nº ' . $d['serie'] . '/' . $d['number'] . '</div>
    </td>

    <td style="width:30%; text-align:right;">
      ' . (!empty($logo)
  ? '<img class="logo" src="' . h($logo) . '">'
  : '') . '
    </td>
  </tr>
</table>
';

// ================= TOP BOXES =================
$html .= '
<table class="top-grid">
  <tr>

    <td class="box">
      <h3>Emitente</h3>
      <div>' . h($d['company_name']) . '</div>
      <div class="muted">' . h($d['company_address']) . '</div>
      <div class="muted">' . h($d['company_city'] . ' - ' . $d['company_country']) . '</div>
      <div class="muted">Tel: ' . h($d['company_phone']) . '</div>
      <div class="muted">Email: ' . h($d['company_email']) . '</div>
      <div class="muted">NIF: ' . h($d['company_nif']) . '</div>
    </td>

    <td style="width:4%"></td>

    <td class="box">
      <h3>Cliente</h3>
      <div>' . h($d['client_name']) . '</div>
      <div class="muted">Email: ' . h($d['client_email']) . '</div>
      <div class="muted">Contribuinte: ' . h($d['client_contributor']) . '</div>
    </td>

  </tr>
</table>
';

// ================= INFO =================
$html .= '<div class="info">';
$html .= '<table>';
$html .= '<tr><td>Data do pagamento</td><td>' . h($d['pay_date']) . '</td></tr>';
$html .= '<tr><td>Valor pago</td><td>' . h(formatMoney((float)$d['amount_paid'], (string)$d['moneySymbol'], (string)$d['moneyPos'])) . '</td></tr>';
$html .= '<tr><td>Meio de pagamento</td><td>' . h($d['payment_method']) . '</td></tr>';
$html .= '<tr><td>Fatura referência</td><td>' . h($d['invoice_reference']) . '</td></tr>';
$html .= '</table>';
$html .= '</div>';

// ================= OBS =================
if (!empty($d['notes'])) {
  $html .= '<p style="margin-top:10px;color:#6b7280;">Obs: ' . h($d['notes']) . '</p>';
}

// ================= FOOTER =================
$html .= '<div class="footer">'
  . h($d['company_name']) . ' | '
  . h($d['company_city'] . ' - ' . $d['company_country']) .
  ' | Recibo gerado pelo Sistema'
  . '</div>';

$html .= '</body></html>';

// ================= PDF =================
$opt = new Options();
$opt->set('isRemoteEnabled', true);

$dompdf = new Dompdf($opt);
$dompdf->setPaper('A4', 'portrait');
$dompdf->loadHtml($html, 'UTF-8');
$dompdf->render();

$dompdf->stream(
  'Recibo_' . $d['serie'] . '-' . $d['number'] . '.pdf',
  ['Attachment' => 1]
);
