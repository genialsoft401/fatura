<?php
/* credit_note_public.php - Exibe a Nota de Crédito em HTML no mesmo estilo da fatura */
require_once '../../../app/config/db.php';

// ---------- utils ----------
function formatCurrency(float $value, string $currencySymbol, string $currencyPosition = 'left', bool $asCredit = false): string{
  $formatted = number_format(abs($value), 2, ',', '.');
  $prefix = $asCredit ? '-' : '';

  return $currencyPosition === 'left'
    ? "{$prefix}{$currencySymbol} {$formatted}"
    : "{$prefix}{$formatted} {$currencySymbol}";
}

function dateBr($sqlDate){
  return date('d/m/Y', strtotime($sqlDate));
}

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if(!$id){
  die('<h3>Nota de crédito não encontrada.</h3>');
}

// ---------- consulta ----------
$sql = "
SELECT cn.*,
       i.issue_date AS invoice_issue_date,
       i.id AS invoice_id,
       comp.name AS company_name,
       comp.address AS company_address,
       comp.city AS company_city,
       comp.country AS company_country,
       comp.registration_number,
       comp.email AS company_email,
       comp.phone AS company_phone,
       comp.logo_url,
       comp.vat_regime,
       comp.goods_services,
       comp.bank_details, comp.bank_name, comp.iban,
       c.name AS client_name,
       c.address AS client_address,
       c.contributor AS client_contributor,
       c.city AS client_city,
       c.country AS client_country,
       cr.symbol AS moneySymbol,
       cr.position AS moneyPos
FROM credit_notes cn
JOIN invoices i ON i.id = cn.invoice_id
JOIN companies comp ON comp.id = cn.company_id
JOIN contact c ON c.id = cn.contact_id
JOIN currencies cr ON cr.iso_code = cn.currency
WHERE cn.id = :id
LIMIT 1";

$stmt = $pdo->prepare($sql);
$stmt->execute([':id' => $id]);
$cn = $stmt->fetch(PDO::FETCH_ASSOC);
if(!$cn){
  die('<h3>Nota de crédito não encontrada.</h3>');
}

$stmt = $pdo->prepare("SELECT cni.*, it.code FROM credit_note_items cni LEFT JOIN items it ON it.id = cni.item_id WHERE cni.credit_note_id = :id");
$stmt->execute([':id' => $id]);
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

// HTML
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="utf-8">
  <title>Nota de Crédito <?= htmlspecialchars(date('Y', strtotime($cn['issue_date'])) . '/' . $cn['id']) ?></title>

  <!-- CSS base (mesmo da fatura) -->
  <?php $BASE_URL = 'https://' . ($_SERVER['HTTP_HOST'] ?? 'angola.guislab.com.br'); ?>
  <link rel="stylesheet" href="<?= $BASE_URL ?>/invoices/invoice.css">
  <link rel="stylesheet" href="<?= $BASE_URL ?>/invoices/invoice_footer.css">

  <!-- Importante: este HTML roda em iframe e também vira PDF (Dompdf).
       Não podemos depender de classes do Bootstrap (d-flex, mb-0, etc.). -->
  <style>
    body{font-family:DejaVu Sans, Arial, Helvetica, sans-serif;}

    .inv-header-table{width:100%; border-collapse:collapse;}
    .inv-header-table td{vertical-align:top;}

    .cn-logo img{width:160px; height:auto;}

    .cn-company h3{margin:0 0 6px 0; font-size:14px;}
    .cn-company p{margin:0; font-size:12px; line-height:1.25;}

    .cn-client small{font-size:11px;}
    .cn-client h4{margin:0; font-size:13px;}
    .cn-client p{margin:0; font-size:12px; line-height:1.25;}

    .mt-4mm{margin-top:4mm;}
  </style>
</head>
<body>
  <main class="invoice-page">

    <!-- HEADER (sem Bootstrap) -->
    <table class="inv-header-table" style="margin-bottom:4mm;">
      <tr>
        <td style="width:62%;">
          <div class="cn-logo" style="margin-bottom:6px;">
            <img src="<?= $BASE_URL ?>/assets/img/companies/<?= htmlspecialchars($cn['logo_url']) ?>" alt="Logo">
          </div>
          <div class="cn-company">
            <h3><?= htmlspecialchars($cn['company_name']) ?></h3>
            <p><?= nl2br(htmlspecialchars($cn['company_address'])) ?><br>
              <?= htmlspecialchars("{$cn['company_city']} - {$cn['company_country']}") ?></p>
            <p>Tel: <?= htmlspecialchars($cn['company_phone']) ?></p>
            <p>E-mail: <?= htmlspecialchars($cn['company_email']) ?></p>
            <p>Contribuinte: <?= htmlspecialchars($cn['registration_number']) ?></p>
          </div>
        </td>
        <td style="width:38%; text-align:left;">
          <div class="cn-client" style="padding-top:90px;"> <!-- alinha com o bloco da empresa -->
            <small>Exmo.(s) Sr.(s)</small>
            <h4><?= htmlspecialchars($cn['client_name']) ?></h4>
            <p><?= nl2br(htmlspecialchars($cn['client_address'])) ?><br>
              <?= htmlspecialchars("{$cn['client_city']} - {$cn['client_country']}") ?></p>
          </div>
        </td>
      </tr>
    </table>

    <!-- META (sem Bootstrap) -->
    <div class="inv-meta" style="margin-top:2mm;">
      <div class="title-line" style="margin-bottom:3mm;">
        Nota de Crédito n.º <?= date('Y', strtotime($cn['issue_date'])) ?>/<?= $cn['id'] ?>
      </div>

      <table style="width:100%; border-collapse:collapse; font-size:.8rem;">
        <tr>
          <td style="width:45%; vertical-align:top; padding-right:10px;">
            <div style="border-bottom:1px solid #000; padding-bottom:2px; margin-bottom:4px;">
              <span style="display:inline-block; width:49%;">Data de emissão</span>
              <span style="display:inline-block; width:49%;">Contribuinte</span>
            </div>
            <div style="margin-bottom:6px;">
              <span style="display:inline-block; width:49%; white-space:nowrap;"><?= dateBr($cn['issue_date']) ?></span>
              <span style="display:inline-block; width:49%; white-space:nowrap;"><?= htmlspecialchars($cn['client_contributor'] ?? '-') ?></span>
            </div>

            <div style="border-bottom:1px solid #000; padding-bottom:2px; margin-bottom:4px;">
              <span style="display:inline-block; width:49%;">Fatura origem</span>
              <span style="display:inline-block; width:49%;">Data fatura</span>
            </div>
            <div>
              <span style="display:inline-block; width:49%; white-space:nowrap;"><?= date('Y', strtotime($cn['invoice_issue_date'])) ?>/<?= (int)$cn['invoice_id'] ?></span>
              <span style="display:inline-block; width:49%; white-space:nowrap;"><?= dateBr($cn['invoice_issue_date']) ?></span>
            </div>
          </td>

          <td style="width:55%; vertical-align:top;">
            <div style="border-bottom:1px solid #000; padding-bottom:2px; margin-bottom:4px;">Motivo</div>
            <div><?= nl2br(htmlspecialchars($cn['reason'] ?? '-')) ?></div>
          </td>
        </tr>
      </table>
    </div>

    <!-- ITENS (table para Dompdf) -->
    <table style="width:100%; border-collapse:collapse; font-size:.8rem; margin-top:4mm;">
      <thead>
        <tr style="border-top:2px solid #000; border-bottom:1px solid #000;">
          <th style="padding:4px; text-align:center; width:10%;">Código</th>
          <th style="padding:4px; text-align:left;  width:34%;">Descrição</th>
          <th style="padding:4px; text-align:right; width:14%;">Preço Uni.</th>
          <th style="padding:4px; text-align:center; width:8%;">Qtd.</th>
          <th style="padding:4px; text-align:center; width:10%;">Taxa/IVA %</th>
          <th style="padding:4px; text-align:center; width:10%;">Desc. %</th>
          <th style="padding:4px; text-align:right; width:14%;">Total</th>
        </tr>
      </thead>
      <tbody>
      <?php foreach($items as $it):
        $qty = (float)($it['quantity'] ?? 0);
        $price = (float)($it['unit_price'] ?? 0);
        $tax = is_numeric($it['tax'] ?? null) ? (float)$it['tax'] : 0;
        $disc = (float)($it['discount'] ?? 0);
        $line = ($qty * $price);
      ?>
        <tr>
          <td style="padding:3px; text-align:center; white-space:nowrap;"><?= htmlspecialchars($it['code'] ?? '-') ?></td>
          <td style="padding:3px; text-align:left;">
            <?= htmlspecialchars($it['description'] ?? '') ?>
          </td>
          <td style="padding:3px; text-align:right; white-space:nowrap;"><?= formatCurrency($price, $cn['moneySymbol'], $cn['moneyPos'], true) ?></td>
          <td style="padding:3px; text-align:center; white-space:nowrap;"><?= htmlspecialchars($qty) ?></td>
          <td style="padding:3px; text-align:center; white-space:nowrap;"><?= htmlspecialchars($tax) ?></td>
          <td style="padding:3px; text-align:center; white-space:nowrap;"><?= htmlspecialchars($disc) ?></td>
          <td style="padding:3px; text-align:right; white-space:nowrap;"><?= formatCurrency($line, $cn['moneySymbol'], $cn['moneyPos'], true) ?></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>

    <!-- SUMÁRIO (table) -->
    <table style="width:100%; border-collapse:collapse; margin-top:6mm; font-size:.8rem;">
      <tr>
        <td style="width:60%;"></td>
        <td style="width:40%;">
          <div style="border-bottom:1px solid #000; font-weight:700; padding-bottom:3px; margin-bottom:4px;">Sumário</div>
          <table style="width:100%; border-collapse:collapse;">
            <tr style="font-weight:700;">
              <td>Total a creditar:</td>
              <td style="text-align:right; white-space:nowrap;"><?= formatCurrency((float)$cn['final_total'], $cn['moneySymbol'], $cn['moneyPos'], true) ?></td>
            </tr>
          </table>
        </td>
      </tr>
    </table>

    <div class="tax-notes">
      <div class="tax-notes-row"><span class="lbl">Observação</span><span class="val">Documento associado à fatura <?= date('Y', strtotime($cn['invoice_issue_date'])) ?>/<?= (int)$cn['invoice_id'] ?></span></div>
    </div>

    <footer class="inv-footer">
      <div class="inv-footer-line">
        <span class="inv-footer-name"><?= htmlspecialchars($cn['company_name']) ?></span>
        <span class="inv-footer-sep">|</span>
        <span><?= htmlspecialchars(trim((string)$cn['company_address'])) ?></span>
        <span class="inv-footer-sep">|</span>
        <span><?= htmlspecialchars("{$cn['company_city']} - {$cn['company_country']}") ?></span>
        <span class="inv-footer-sep">|</span>
        <span>Tel: <?= htmlspecialchars($cn['company_phone']) ?></span>
      </div>
      <div class="inv-footer-line">
        <span>Processado por programa validado n.º XXXXXXXXXX | BXpert</span>
      </div>
    </footer>

  </main>
</body>
</html>
