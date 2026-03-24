<?php
require_once '../../../app/config/db.php';
require_once '../../../vendor/autoload.php';

use Dompdf\{Dompdf, Options};

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if(!$id){
  http_response_code(422);
  echo 'Nota de crédito inválida.';
  exit;
}

// Confere se existe
$stmt = $pdo->prepare('SELECT id, issue_date FROM credit_notes WHERE id = :id LIMIT 1');
$stmt->execute([':id' => $id]);
$cn = $stmt->fetch(PDO::FETCH_ASSOC);
if(!$cn){
  http_response_code(404);
  echo 'Nota de crédito não encontrada.';
  exit;
}

$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$url  = "https://{$host}/credit_notes/ajax/credit_note_public.php?id={$id}";

$ctx = stream_context_create([
  'ssl' => [
    'verify_peer' => false,
    'verify_peer_name' => false,
  ]
]);

$html = @file_get_contents($url, false, $ctx);
if(!$html){
  http_response_code(500);
  echo 'Não foi possível obter o HTML da nota de crédito.';
  exit;
}

$options = new Options();
$options->set('isRemoteEnabled', true);

$dompdf = new Dompdf($options);
$dompdf->setPaper('A4', 'portrait');
$dompdf->loadHtml($html, 'UTF-8');
$dompdf->render();

$year = date('Y', strtotime($cn['issue_date'] ?? 'now'));
$filename = "Nota_Credito_{$year}-{$id}.pdf";

$dompdf->stream($filename, ['Attachment' => true]);
