<?php
/*  invoice_public.php
 *  Gera a fatura completa em HTML (modelo Personalité‑like) 
 */
require_once '../../../vendor/autoload.php';

use chillerlan\QRCode\{QRCode, QROptions};

require_once '../../../app/config/db.php';

// ---------- utils ----------
function formatCurrency(float $value, string $currencySymbol, string $currencyPosition = 'left'): string
{
  // formata 1 000 000.5 → 1.000.000,50
  $formatted = number_format($value, 2, ',', '.');

  return $currencyPosition === 'left'
    ? "{$currencySymbol} {$formatted}"
    : "{$formatted} {$currencySymbol}";
}

function dateBr($sqlDate)
{
  return $sqlDate ? date('d/m/Y', strtotime($sqlDate)) : '-';
}
function randomHash($len = 2)
{
  return substr(bin2hex(random_bytes($len)), 0, $len);
}

// ---------- valor por extenso (pt) ----------
function extenso_pt(int $n): string
{
  $n = (int)$n;
  if ($n === 0) return 'zero';

  $u = ['', 'um', 'dois', 'três', 'quatro', 'cinco', 'seis', 'sete', 'oito', 'nove'];
  $d10 = ['dez', 'onze', 'doze', 'treze', 'catorze', 'quinze', 'dezasseis', 'dezassete', 'dezoito', 'dezanove'];
  $t = ['', '', 'vinte', 'trinta', 'quarenta', 'cinquenta', 'sessenta', 'setenta', 'oitenta', 'noventa'];
  $c = ['', 'cem', 'duzentos', 'trezentos', 'quatrocentos', 'quinhentos', 'seiscentos', 'setecentos', 'oitocentos', 'novecentos'];

  $parts = [];

  $bilhao = intdiv($n, 1000000000);
  $n %= 1000000000;
  if ($bilhao) {
    $parts[] = ($bilhao === 1 ? 'um bilião' : extenso_pt($bilhao) . ' biliões');
  }

  $milhao = intdiv($n, 1000000);
  $n %= 1000000;
  if ($milhao) {
    $parts[] = ($milhao === 1 ? 'um milhão' : extenso_pt($milhao) . ' milhões');
  }

  $mil = intdiv($n, 1000);
  $n %= 1000;
  if ($mil) {
    $parts[] = ($mil === 1 ? 'mil' : extenso_pt($mil) . ' mil');
  }

  if ($n) {
    $cent = intdiv($n, 100);
    $dez = $n % 100;

    $chunk = [];
    if ($cent) {
      if ($cent === 1 && $dez > 0) {
        $chunk[] = 'cento';
      } else {
        $chunk[] = $c[$cent];
      }
    }

    if ($dez) {
      if ($dez < 10) {
        $chunk[] = $u[$dez];
      } elseif ($dez < 20) {
        $chunk[] = $d10[$dez - 10];
      } else {
        $dezenas = intdiv($dez, 10);
        $unid = $dez % 10;
        if ($unid) {
          $chunk[] = $t[$dezenas] . ' e ' . $u[$unid];
        } else {
          $chunk[] = $t[$dezenas];
        }
      }
    }

    $parts[] = implode(' e ', $chunk);
  }

  // junta com " e " apenas no último elo quando fizer sentido
  if (count($parts) === 1) return $parts[0];
  $last = array_pop($parts);
  return implode(' ', $parts) . ' e ' . $last;
}

function moneyToWords(float $value, string $currencyIso = 'AOA'): string
{
  $value = round($value, 2);
  $int = (int)floor($value);
  $cents = (int)round(($value - $int) * 100);

  // Se intl estiver disponível no PHP-FPM, usa NumberFormatter.
  // Caso contrário, usa fallback local (evita página "cortar" no meio por Fatal error).
  if (class_exists('NumberFormatter')) {
    $locale = 'pt';
    $fmt = new NumberFormatter($locale, NumberFormatter::SPELLOUT);
    $intWords = trim((string)$fmt->format($int));
    $centWords = $cents > 0 ? trim((string)$fmt->format($cents)) : '';
  } else {
    $intWords = extenso_pt($int);
    $centWords = $cents > 0 ? extenso_pt($cents) : '';
  }

  // moeda (AOA -> Kz)
  $currencyName = match (strtoupper($currencyIso)) {
    'AOA' => 'Kz',
    'BRL' => ($int === 1 ? 'real' : 'reais'),
    'EUR' => ($int === 1 ? 'euro' : 'euros'),
    'USD' => ($int === 1 ? 'dólar' : 'dólares'),
    default => 'unidades',
  };

  $centName = ($cents === 1 ? 'centavo' : 'centavos');

  $out = $intWords . ' ' . $currencyName;
  if ($cents > 0) {
    $out .= ' e ' . $centWords . ' ' . $centName;
  }

  // primeira letra maiúscula
  $out = mb_strtoupper(mb_substr($out, 0, 1)) . mb_substr($out, 1);
  return $out;
}
// ---------- input ----------
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if (!$id) {
  die('<h3>Fatura não encontrada.</h3>');
}

// ---------- consulta fatura ----------
$sql = "
SELECT  i.*,
        comp.id   AS company_id,
        comp.name AS company_name,   comp.address AS company_address,
        comp.city AS company_city,   comp.country AS company_country,
        comp.registration_number,    comp.email   AS company_email,
        comp.phone AS company_phone, comp.logo_url,
        comp.vat_regime, comp.goods_services, comp.bank_details, comp.bank_name, comp.iban,
        c.name    AS client_name,    c.address  AS client_address,
        c.contributor AS client_contributor, c.city AS client_city,
        c.country AS client_country,
        ivs.name  AS status_invoice, ivs.color, ivs.text_color,
        cr2.symbol AS moneySymbol,   cr2.position AS moneyPos
FROM      invoices i
JOIN      companies   comp ON comp.id = i.company_id
JOIN      contact         c ON c.id    = i.contact_id
JOIN      invoice_status ivs ON ivs.id = i.status
JOIN      currencies    cr2 ON cr2.iso_code = i.currency
WHERE i.id = :id";
$stmt = $pdo->prepare($sql);
$stmt->execute(['id' => $id]);
$inv = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$inv) {
  die('<h3>Fatura não encontrada.</h3>');
}

// ---------- itens ----------
$stmt = $pdo->prepare("
SELECT it.code, it.description, ii.quantity, ii.unit_price,
       ii.tax, ii.discount
FROM   invoice_items ii
JOIN   items it ON it.id = ii.item_id
WHERE  ii.invoice_id = :id");
$stmt->execute(['id' => $id]);
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ---------- taxas ----------
$stmt = $pdo->prepare("
SELECT  ii.tax AS tax_rate,
        ROUND(SUM(ii.unit_price * ii.quantity * (1 - (ii.discount/100))),2) AS tax_base,
SUM(ii.unit_price * ii.quantity * (1 - (ii.discount/100)) * (ii.tax/100)) AS tax_value
FROM   invoice_items ii
WHERE  ii.invoice_id = :id
GROUP BY ii.tax");
$stmt->execute(['id' => $id]);
$taxes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ---------- QR ----------
$hash = randomHash();
$qrData = "https://teusite.com/invoice_public.php?id={$inv['id']}";

$opts = new QROptions([
  'outputType'   => QRCode::OUTPUT_IMAGE_PNG,
  'eccLevel'     => QRCode::ECC_L,
  'scale'        => 3,
  'addQuietzone'   => false,
  'imageBase64'  => true,   // a lib já põe 'data:image/png;base64,...'
]);

$qrSrc = (new QRCode($opts))->render($qrData); // já vem pronta

/* ================================================================
 * 1. DEPOIS de buscar a fatura ($inv) calcule quanto já foi pago
 * --------------------------------------------------------------- */
$stmt = $pdo->prepare("
    SELECT  COALESCE(SUM(amount_paid),0) AS paid_total   
    FROM    receipts
    WHERE   invoice_id = :id
");
$stmt->execute(['id' => $id]);
$paid_total = (float)$stmt->fetchColumn();

$saldo = max(0, $inv['final_total'] - $paid_total);

/*  define um rótulo para exibir no cabeçalho  ------------------- */
if ($paid_total >= $inv['final_total']) {  // quitada
  $payLabel = 'Pago';
  $payClass = 'pago';       // usa .badge.pago (verde) que já existe
} elseif ($paid_total > 0) {                      // parcial
  $payLabel = 'Pago parcial';
  $payClass = 'pendente';   // amarelo – já tinha no CSS
} else {                                        // nada pago
  $payLabel = 'Pendente';
  $payClass = 'pendente';
}


// ---------- HTML ----------
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
  <meta charset="utf-8">
  <title>Fatura <?= htmlspecialchars($inv['codigo']) ?></title>
  <link rel="stylesheet" href="invoices/invoice.css">
  <link rel="stylesheet" href="invoices/invoice_footer.css">

</head>

<body>
  <main class="invoice-page">

    <!-- ===== HEADER (100% flex) ===== -->
    <div class="inv-header d-flex justify-content-between pb-2">

      <!-- ESQUERDA: logo + dados empresa -->
      <div class="inv-left d-flex flex-column align-items-start">
        <div id="invoice_logoCompanies" class="me-3">
          <img src="/assets/img/companies/<?= htmlspecialchars($inv['logo_url']) ?>" class="logo" alt="Logo">
        </div>

        <div id="company-info">
          <!-- PHP ancora o conteúdo aqui -->
          <h6 class="fw-bold mb-1"><?= htmlspecialchars($inv['company_name']) ?></h6>
          <p class="mb-0"><?= nl2br(htmlspecialchars($inv['company_address'])) ?><br>
            <?= htmlspecialchars("{$inv['company_city']} - {$inv['company_country']}") ?></p>
          <p class="mb-0">Tel: <?= htmlspecialchars($inv['company_phone']) ?></p>
          <p class="mb-0">E-mail: <?= htmlspecialchars($inv['company_email']) ?></p>
          <p class="mb-0">Contribuinte: <?= htmlspecialchars($inv['registration_number']) ?></p>
        </div>
      </div>

      <!-- DIREITA: QR + dados cliente -->
      <div class="inv-right d-flex flex-column align-items-start justify-content-end">
        <div id="invoice-qr" class="mb-1">
          <img src="<?= $qrSrc ?>" alt="QR" style="width:95px;height:95px">

        </div>

        <div id="client-info" class="text-start">
          <small>Exmo.(s) Sr.(s)</small>
          <h6 class="fw-bold mb-0"><?= htmlspecialchars($inv['client_name']) ?></h6>
          <p class="mb-0">
            <?= nl2br(htmlspecialchars($inv['client_address'])) ?><br>
            <?= htmlspecialchars("{$inv['client_city']} - {$inv['client_country']}") ?>
          </p>
        </div>
      </div>

    </div><!-- /inv-header -->


    <?php
    $issueBr = dateBr($inv['issue_date']);
    $dueDays = (int)$inv['due_date'];
    $dueBr = dateBr((new DateTime($inv['issue_date']))
      ->modify("+{$dueDays} days")
      ->format('Y-m-d'));
    ?>
    <!-- ===== META ===== -->
    <section class="inv-meta pt-2">

      <div class="d-flex justify-content-between">
        <span class="d-block">Original</span>
        <?php if ($paid_total > 0): ?>
          <span class="badge <?= $saldo > 0 ? 'parcial' : 'pago' ?>">
            <?= $saldo > 0 ? 'Pago parcial' : 'Pago' ?>
          </span>
        <?php endif; ?>
      </div>

      <span class="d-block title-line">
        Factura n.º <?= date('Y', strtotime($inv['issue_date'])) ?>/<?= $inv['id'] ?>
      </span>

      <!-- Bloco flex com 2 colunas -->
      <div class="meta-row">

        <!-- ===== COLUNA ESQUERDA – DATAS & REF ===== -->
        <div class="meta-mini">

          <div class="head">
            <span>Data de emissão</span><span>Contribuinte</span>
          </div>
          <div class="vals">
            <span><?= $issueBr ?></span><span><?= $inv['client_contributor'] ?></span>
          </div>

          <div class="head top">
            <span>Vencimento</span><span>V/ Ref.</span>
          </div>
          <div class="vals">
            <span><?= $dueBr ?></span><span><?= $inv['reference'] ?></span>
          </div>

        </div>

        <!-- ===== COLUNA DIREITA – OBSERVAÇÕES ===== -->
        <div class="meta-obs">
          <div class="head">
            <span>Observações</span>
          </div>
          <div class="vals">
            <span><?= $inv['observation'] ? htmlspecialchars($inv['observation']) : '-' ?></span>
          </div>
        </div>

      </div>
    </section>


    <!-- ===== ITENS (sem <table>) ===== -->
    <div class="items-grid mt-2 title-line pb-5">

      <!-- cabeçalho -->
      <div class="items-row items-head">
        <span>Código</span>
        <span>Descrição</span>
        <span>Preço&nbsp;Uni.</span>
        <span>Qtd.</span>
        <span>Taxa/IVA&nbsp;%</span>
        <span>Desc.&nbsp;%</span>
        <span>Total</span>
      </div>

      <!-- linhas dinâmicas -->
      <?php foreach ($items as $it):
        $base = $it['unit_price'] * $it['quantity'];
        $discount = $base * ($it['discount'] / 100);
        $tax = ($base - $discount) * ($it['tax'] / 100);
        $total = $base - $discount + $tax; ?>
        <div class="items-row">
          <span><?= htmlspecialchars($it['code']) ?></span>
          <span><?= htmlspecialchars($it['description']) ?></span>
          <span class="right">
            <?= formatCurrency($it['unit_price'], $inv['moneySymbol'], $inv['moneyPos']) ?>
          </span>
          <span class="center"><?= $it['quantity'] ?></span>
          <span class="center"><?= $it['tax'] ?>%</span>
          <span class="center"><?= $it['discount'] ?>%</span>
          <span class="right">
            <?= formatCurrency($total, $inv['moneySymbol'], $inv['moneyPos']) ?>
          </span>
        </div>
      <?php endforeach; ?>
    </div>


    <!-- ===== TAXAS & RESUMO (sem <table>) ===== -->
    <div class="totals-wrap">

      <!-- ===== ESQUERDA – Impostos/IVA ===== -->
      <div class="tax-grid">

        <!-- cabeçalho -->
        <div class="tax-head">
          <span>Imposto/IVA</span>
          <span class="right">Incidência</span>
          <span class="right">Valor</span>
        </div>

        <!-- linhas dinâmicas -->
        <?php foreach ($taxes as $tx): ?>
          <div class="tax-row">
            <span><?= $tx['tax_rate'] ? $tx['tax_rate'] . '%' : 'Isento (0%)' ?></span>
            <span class="right">
              <?= formatCurrency($tx['tax_base'], $inv['moneySymbol'], $inv['moneyPos']) ?>
            </span>
            <span class="right">
              <?= formatCurrency($tx['tax_value'], $inv['moneySymbol'], $inv['moneyPos']) ?>
            </span>
          </div>
        <?php endforeach; ?>

        <!-- retenção -->
        <div class="tax-row">
          <span>Retenção (<?= $inv['retention'] ?? '0' ?>%)</span>
          <span></span>
          <span class="right">
            <?= formatCurrency($inv['retention_value'], $inv['moneySymbol'], $inv['moneyPos']) ?>
          </span>
        </div>

      </div><!-- /.tax-grid -->


      <!-- ===== DIREITA – Sumário ===== -->
      <div class="sum-grid">

        <div class="sum-head">Sumário</div>

        <div class="sum-row">
          <span>Total líquido:</span>
          <span class="right"><?= formatCurrency($inv['total_sum'], $inv['moneySymbol'], $inv['moneyPos']) ?></span>
        </div>

        <div class="sum-row">
          <span>Desconto:</span>
          <span class="right"><?= formatCurrency($inv['total_discount'], $inv['moneySymbol'], $inv['moneyPos']) ?></span>
        </div>

        <div class="sum-row">
          <span>Sem Imposto/IVA c Desc.:</span>
          <span class="right">
            <?= formatCurrency($inv['total_sum'] - $inv['total_discount'], $inv['moneySymbol'], $inv['moneyPos']) ?>
          </span>
        </div>

        <div class="sum-row">
          <span>Imposto/IVA:</span>
          <span class="right"><?= formatCurrency($inv['total_tax'], $inv['moneySymbol'], $inv['moneyPos']) ?></span>
        </div>

        <div class="sum-row">
          <span>Retenção:</span>
          <span class="right"><?= formatCurrency($inv['retention_value'], $inv['moneySymbol'], $inv['moneyPos']) ?></span>
        </div>

        <!-- separador grosso -->
        <div class="sum-row sep"></div>

        <div class="sum-row bold">
          <span>Total:</span>
          <span class="right"><?= formatCurrency($inv['final_total'], $inv['moneySymbol'], $inv['moneyPos']) ?></span>
        </div>

        <?php $totalPagar = (float)$inv['final_total'] - (float)$inv['retention_value']; ?>
        <div class="sum-row bold">
          <span>Total a pagar:</span>
          <span class="right">
            <?= formatCurrency($totalPagar, $inv['moneySymbol'], $inv['moneyPos']) ?>
          </span>
        </div>
        <div class="sum-row" style="margin-top:2px;">
          <span style="grid-column:1 / -1; font-size:.72rem;">
            <?= htmlspecialchars(moneyToWords($totalPagar, $inv['currency'] ?? 'AOA')) ?>
          </span>
        </div>

        <!-- barra inferior grossa -->
        <div class="sum-bottom"></div>

        <!-- …antes do separador grosso -->
        <?php if ($paid_total > 0): ?>
          <div class="sum-row">
            <span>Pago:</span>
            <span class="right">
              <?= formatCurrency($paid_total, $inv['moneySymbol'], $inv['moneyPos']) ?>
            </span>
          </div>

          <?php if ($saldo > 0): // só mostra saldo se ainda houver 
          ?>
            <div class="sum-row">
              <span>Saldo:</span>
              <span class="right">
                <?= formatCurrency($saldo, $inv['moneySymbol'], $inv['moneyPos']) ?>
              </span>
            </div>
          <?php endif; ?>
        <?php endif; ?>


      </div><!-- /.sum-grid -->

    </div><!-- /.totals-wrap -->

    <!-- ===== REGIME IVA / BENS E SERVIÇOS / DADOS BANCÁRIOS ===== -->
    <div class="tax-notes">
      <div class="tax-notes-row">
        <span class="lbl">Regime de IVA</span>
        <span class="val">-</span>
      </div>
      <div class="tax-notes-row">
        <span class="lbl">Bens e serviços</span>
        <span class="val">Os bens e serviços foram colocados à disposição do adquirente na data do documento.</span>
      </div>
      <div class="tax-notes-row">
        <span class="lbl">Dados bancários</span>
        <span class="val">-</span>
      </div>
    </div>

    <!-- ===== RODAPÉ (dados da empresa emissora) ===== -->
    <footer class="inv-footer">
      <div class="inv-footer-line">
        <span class="inv-footer-name"><?= htmlspecialchars($inv['company_name']) ?></span>
        <span class="inv-footer-sep">|</span>
        <span><?= htmlspecialchars(trim((string)$inv['company_address'])) ?></span>
        <span class="inv-footer-sep">|</span>
        <span><?= htmlspecialchars("{$inv['company_city']} - {$inv['company_country']}") ?></span>
        <span class="inv-footer-sep">|</span>
        <span>Tel: <?= htmlspecialchars($inv['company_phone']) ?></span>
      </div>

      <div class="inv-footer-line">
        <span>Processado por programa validado n.º XXXXXXXXXX | BXpert</span>
      </div>
    </footer>

    <!-- numeração de página no PDF (Dompdf) -->
    <script type="text/php">
      if (isset($pdf)) {
      $font = $fontMetrics->get_font("Helvetica", "normal");
      $pdf->page_text(520, 820, "{PAGE_NUM} / {PAGE_COUNT}", $font, 8, array(0,0,0));
    }
  </script>

  </main>
</body>

</html>