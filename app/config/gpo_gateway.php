<?php
// app/helpers/gpo_gateway.php
require_once __DIR__ . '/../config/gpo.php';

/**
 * Cria uma cobrança na EMIS GPO.
 * method = 'GPO'  -> Multicaixa Express (push para o telemóvel)
 * method = 'REF'  -> Referência Multicaixa (entidade + referência p/ ATM/Internet Banking)
 *
 * IMPORTANTE: ajuste o endpoint/payload exatamente conforme a documentação
 * que a EMIS entregou no contrato do vosso POS — isto segue o formato
 * público típico da API GPO (frameToken), mas os nomes de campos podem
 * variar por versão/contrato.
 */
function gpo_create_charge(float $amount, string $method, ?string $phone, string $description): array
{
    $payload = [
        'reference'   => 'ORD-' . time() . '-' . bin2hex(random_bytes(2)),
        'amount'      => number_format($amount, 2, '.', ''),
        'currency'    => 'AOA',
        'posID'       => GPO_POS_ID,
        'description' => $description,
    ];

    if ($method === 'GPO') {
        $payload['mobile'] = $phone; // push Multicaixa Express
    }
    // método REF não precisa de telefone: a EMIS devolve entidade+referência

    $ch = curl_init(GPO_BASE_URL . '/frameToken');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($payload),
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'Authorization: Bearer ' . GPO_TOKEN,
        ],
        CURLOPT_TIMEOUT => 15,
    ]);

    $raw = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $err = curl_error($ch);
    curl_close($ch);

    if ($raw === false || $httpCode >= 400) {
        throw new Exception('Falha ao comunicar com a EMIS GPO: ' . ($err ?: "HTTP $httpCode"));
    }

    $data = json_decode($raw, true) ?: [];

    return [
        'merchantTransactionId' => $data['id'] ?? $data['token'] ?? $payload['reference'],
        'entity'                => $data['entity'] ?? null,
        'referenceNumber'       => $data['reference'] ?? $payload['reference'],
        'dueDate'               => $data['dueDate'] ?? (new DateTime('+2 days'))->format(DATE_ATOM),
        'raw'                   => $data,
    ];
}

/**
 * Consulta o status de uma transação na EMIS GPO.
 * Devolve algo como: 'pending' | 'success' | 'failed' | 'canceled'
 */
function gpo_get_status(string $merchantTransactionId): string
{
    $ch = curl_init(GPO_BASE_URL . '/transactionStatus/' . urlencode($merchantTransactionId));
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => ['Authorization: Bearer ' . GPO_TOKEN],
        CURLOPT_TIMEOUT => 10,
    ]);
    $raw = curl_exec($ch);
    curl_close($ch);

    if ($raw === false) return 'pending';

    $data = json_decode($raw, true) ?: [];
    return strtolower($data['status'] ?? 'pending');
}
