<?php

/**
 * Proxy para o serviço de IA (Node/reportService) que gera o relatório mensal.
 * Mantém a URL interna do serviço fora do browser e evita problemas de CORS,
 * já que a chamada é feita aqui no servidor (cURL), não no JS do cliente.
 *
 * GET index/ajax/get_monthly_report.php?company_id=1&year=2026&month=5
 * (year e month são opcionais; por omissão usam o mês/ano atuais)
 */

declare(strict_types=1);

session_start();
header('Content-Type: application/json; charset=utf-8');

// Nunca deixar um erro PHP (fatal, warning, notice) imprimir HTML e
// corromper o JSON — qualquer que seja a causa.
ini_set('display_errors', '0');
error_reporting(E_ALL);

register_shutdown_function(function () {
    $error = error_get_last();
    if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR], true)) {
        if (!headers_sent()) {
            http_response_code(500);
            header('Content-Type: application/json; charset=utf-8');
        }
        echo json_encode([
            'success' => false,
            'error'   => 'Erro interno no servidor',
            'detail'  => $error['message'] . ' em ' . $error['file'] . ':' . $error['line'],
        ]);
    }
});

// --- Autenticação básica ---
if (empty($_SESSION['user']['id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Não autenticado']);
    exit;
}

// Verificação explícita em vez de deixar rebentar num Fatal error
// pouco claro — diz exatamente o que falta ativar no php.ini.
if (!function_exists('curl_init')) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error'   => 'A extensão cURL do PHP não está ativa.',
        'detail'  => 'Ativa "extension=curl" no php.ini e reinicia o Apache.',
    ]);
    exit;
}

// --- Config: URL base do serviço de IA ---
// TODO: mover para variável de ambiente / config.php quando for para produção
const AI_AGENT_BASE_URL = 'http://localhost:3000';

$companyId = (int)($_GET['company_id'] ?? $_SESSION['user']['company_id'] ?? 0);
$year      = (int)($_GET['year']  ?? date('Y'));
$month     = (int)($_GET['month'] ?? date('n'));

if ($companyId <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'company_id inválido']);
    exit;
}

// Segurança: só permite consultar dados da empresa ativa na sessão do utilizador
$sessionCompanyId = (int)($_SESSION['user']['company_id'] ?? 0);
if ($sessionCompanyId > 0 && $companyId !== $sessionCompanyId) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Sem permissão para esta empresa']);
    exit;
}

$url = AI_AGENT_BASE_URL . '/api/reports/monthly?' . http_build_query([
    'company_id' => $companyId,
    'year'       => $year,
    'month'      => $month,
]);

$ch = curl_init($url);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT        => 8,
    CURLOPT_HTTPHEADER     => ['Accept: application/json'],
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlErr  = curl_error($ch);
curl_close($ch);

if ($response === false || $httpCode >= 400) {
    http_response_code(502);
    echo json_encode([
        'success' => false,
        'error'   => 'Serviço de IA indisponível',
        'detail'  => $curlErr ?: "HTTP $httpCode",
    ]);
    exit;
}

$data = json_decode($response, true);
if (!is_array($data)) {
    http_response_code(502);
    echo json_encode(['success' => false, 'error' => 'Resposta inválida do serviço de IA']);
    exit;
}

// Metadados úteis ao front-end
$data['success']     = true;
$data['company_id']  = $companyId;
$data['year']        = $year;
$data['month']       = $month;
// Usado para montar os links de export (que o backend Node devolve como paths relativos)
$data['ai_base_url'] = AI_AGENT_BASE_URL;

echo json_encode($data);