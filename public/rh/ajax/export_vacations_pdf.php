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

$start = $_GET['start'] ?? null; // YYYY-MM-DD
$end = $_GET['end'] ?? null;     // YYYY-MM-DD
$employee_id = $_GET['employee_id'] ?? null;

if (!$start || !$end) {
    // fallback: mês atual
    $mes = $_GET['mes'] ?? date('Y-m');
    $start = $mes . '-01';
    $end = date('Y-m-t', strtotime($start));
}

// valida datas
$startDt = date('Y-m-d', strtotime($start));
$endDt = date('Y-m-d', strtotime($end));

// Empresa
$stmt = $pdo->prepare('SELECT name, logo_url, registration_number, address, city, country FROM companies WHERE id = ?');
$stmt->execute([$company_id]);
$empresa = $stmt->fetch(PDO::FETCH_ASSOC);

$params = [$company_id, $endDt, $startDt];
$sql = "SELECT v.*, e.name AS employee_name
        FROM vacations v
        JOIN employees e ON e.id = v.employee_id
        WHERE v.company_id = ?
          AND (v.start_date <= ? AND v.end_date >= ?)";

if (!empty($employee_id)) {
    $sql .= " AND v.employee_id = ?";
    $params[] = (int)$employee_id;
}

$sql .= " ORDER BY e.name, v.start_date";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

function h($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

// Logo (mesma lógica: assets/img/companies/<logo_url> quando for só nome)
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
        $logoHtml = "<img src=\"data:$mime;base64,$data\" style=\"height:50px; object-fit:contain;\">";
    }
}

$title = "Lista de Férias e Licenças";
$periodLabel = date('d/m/Y', strtotime($startDt)) . ' a ' . date('d/m/Y', strtotime($endDt));

$html = "
<!doctype html>
<html><head><meta charset='utf-8'>
<style>
    body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 12px; color:#111; }
    .header { display: table; width: 100%; margin-bottom: 10px; }
    .header .left { display: table-cell; width: 60%; vertical-align: top; }
    .header .right { display: table-cell; width: 40%; text-align: right; vertical-align: top; }
    h2 { margin: 0 0 4px 0; }
    .muted { color:#555; font-size: 11px; }
    table { width: 100%; border-collapse: collapse; margin-top: 10px; }
    th, td { border: 1px solid #ddd; padding: 6px; }
    th { background: #f2f2f2; text-align:left; }
    .badge { padding: 2px 6px; border-radius: 10px; font-size: 11px; }
    .aprovado { background:#d1e7dd; }
    .pendente { background:#fff3cd; }
    .rejeitado { background:#f8d7da; }
</style>
</head><body>
<div class='header'>
  <div class='left'>
    <h2>" . h($title) . "</h2>
    <div class='muted'>Período: " . h($periodLabel) . "</div>
    <div class='muted'>Empresa: " . h($empresa['name'] ?? '') . "</div>
  </div>
  <div class='right'>" . $logoHtml . "</div>
</div>
";

if (!$rows) {
    $html .= "<p>Nenhum registro encontrado para o período.</p>";
} else {
    $html .= "<table>
      <thead>
        <tr>
          <th>Funcionário</th>
          <th>Tipo</th>
          <th>Início</th>
          <th>Fim</th>
          <th>Status</th>
        </tr>
      </thead>
      <tbody>";

    foreach ($rows as $r) {
        $st = $r['status'] ?? 'Pendente';
        $cls = strtolower($st);
        $badge = "<span class='badge $cls'>" . h($st) . "</span>";
        $html .= "<tr>
          <td>" . h($r['employee_name']) . "</td>
          <td>" . h($r['type']) . "</td>
          <td>" . date('d/m/Y', strtotime($r['start_date'])) . "</td>
          <td>" . date('d/m/Y', strtotime($r['end_date'])) . "</td>
          <td>" . $badge . "</td>
        </tr>";
    }

    $html .= "</tbody></table>";
}

$html .= "</body></html>";

$options = new Options();
$options->set('isRemoteEnabled', true);
$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();
$dompdf->stream('ferias_licencas_' . str_replace('-', '', $startDt) . '_' . str_replace('-', '', $endDt) . '.pdf', ['Attachment' => 0]);
