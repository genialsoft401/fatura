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

$stmt = $pdo->prepare("SELECT name, logo_url, registration_number FROM companies WHERE id = ?");
$stmt->execute([$company_id]);
$empresa = $stmt->fetch(PDO::FETCH_ASSOC);

$stmt = $pdo->prepare("SELECT name, bi, position, salary, status, iban FROM employees WHERE company_id = ? ORDER BY status DESC, name ASC");
$stmt->execute([$company_id]);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

function h($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

// Logo (assets/img/companies/<logo_url>)
$logoHtml = '';
if (!empty($empresa['logo_url'])) {
    $logoPath = trim($empresa['logo_url']);
    $publicRoot = realpath(__DIR__ . '/../../'); // /public
    $candidate = ltrim($logoPath, '/');
    if (strpos($candidate, '/') === false && strpos($candidate, '\\') === false) {
        $candidate = 'assets/img/companies/' . $candidate;
    }
    $full = realpath($publicRoot . '/' . $candidate);
    if ($full && str_starts_with($full, $publicRoot) && is_file($full)) {
        $data = base64_encode(file_get_contents($full));
        $ext = strtolower(pathinfo($full, PATHINFO_EXTENSION));
        $mime = $ext === 'jpg' || $ext === 'jpeg' ? 'image/jpeg' : ($ext === 'webp' ? 'image/webp' : 'image/png');
        $logoHtml = "<img src=\"data:$mime;base64,$data\" style=\"height:45px; object-fit:contain;\">";
    }
}

$html = "<!doctype html><html><head><meta charset='utf-8'>
<style>
body{ font-family: DejaVu Sans, Arial, sans-serif; font-size:12px; }
.header{ width:100%; display:table; }
.header .l{ display:table-cell; width:70%; }
.header .r{ display:table-cell; width:30%; text-align:right; }
.small{ color:#555; font-size:11px; }
table{ width:100%; border-collapse:collapse; margin-top:10px; }
th,td{ border:1px solid #ddd; padding:6px; }
th{ background:#f2f2f2; }
</style>
</head><body>
<div class='header'>
 <div class='l'>
  <h2 style='margin:0'>Lista de Funcionários</h2>
  <div class='small'>Empresa: " . h($empresa['name'] ?? '') . "</div>
  <div class='small'>Gerado em: " . date('d/m/Y H:i') . "</div>
 </div>
 <div class='r'>$logoHtml</div>
</div>

<table>
<thead><tr>
  <th>Nome</th>
  <th>BI</th>
  <th>Cargo</th>
  <th>Salário</th>
  <th>Status</th>
  <th>IBAN</th>
</tr></thead>
<tbody>";

foreach ($rows as $r) {
    $html .= "<tr>
      <td>" . h($r['name']) . "</td>
      <td>" . h($r['bi']) . "</td>
      <td>" . h($r['position']) . "</td>
      <td>Kz " . number_format((float)$r['salary'], 2, ',', '.') . "</td>
      <td>" . h($r['status']) . "</td>
      <td>" . h($r['iban']) . "</td>
    </tr>";
}

$html .= "</tbody></table></body></html>";

$options = new Options();
$options->set('isRemoteEnabled', true);
$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();
$dompdf->stream('funcionarios.pdf', ['Attachment' => 0]);
