<?php

/**
 * ==================================================
 * HELPER DE CONTROLO DE NUMERAÇÃO DE FATURAS
 * ==================================================
 *
 * Regras:
 *
 * 1. Toda nova fatura nasce como RASCUNHO
 *    - sem número fiscal
 *    - sem número de série final
 *
 * 2. Só recebe numeração quando o status mudar para:
 *    - pendente
 *    - pago
 *    - finalizada
 *
 * 3. Se for ANULADA:
 *    - status muda para anulada
 *    - número fiscal removido
 *    - referência removida
 *
 * 4. Nunca gerar nova numeração se já existir.
 */

function invoice_status_map(): array
{
    return [
        'draft' => 0,
        'pending' => 1,
        'paid' => 2,
        'cancelled' => 3,
        'finalized' => 4,
    ];
}

/**
 * Verifica se a fatura precisa receber numeração
 */
function invoice_should_generate_number(string $status, ?string $reference): bool
{
    $allowed = ['pending', 'paid', 'finalized'];

    return in_array($status, $allowed, true)
        && empty($reference);
}

/**
 * Se anulada → remove número fiscal
 */
function invoice_should_clear_number(string $status): bool
{
    return $status === 'cancelled';
}

/**
 * Gera próximo número da fatura
 * Exemplo:
 * FT 2026/000123
 */
function generate_next_invoice_reference(PDO $pdo, int $companyId, ?string $series = 'FT'): string
{
    $year = date('Y');
    $prefix = strtoupper(trim($series ?: 'FT'));

    $stmt = $pdo->prepare("
        SELECT reference
        FROM invoices
        WHERE company_id = ?
          AND reference IS NOT NULL
          AND reference <> ''
          AND reference LIKE ?
        ORDER BY id DESC
        LIMIT 1
    ");

    $like = $prefix . ' ' . $year . '/%';

    $stmt->execute([
        $companyId,
        $like
    ]);

    $last = $stmt->fetchColumn();

    $nextNumber = 1;

    if ($last && preg_match('/\/(\d+)$/', $last, $match)) {
        $nextNumber = ((int)$match[1]) + 1;
    }

    return sprintf('%s %s/%06d', $prefix, $year, $nextNumber);
}

/**
 * Aplicar regra de status + numeração
 */
function apply_invoice_number_rules(
    PDO $pdo,
    int $companyId,
    string $status,
    ?string $currentReference,
    ?string $series = 'FT'
): array {
    $reference = $currentReference;

    if (invoice_should_clear_number($status)) {
        $reference = null;
    }

    if (invoice_should_generate_number($status, $reference)) {
        $reference = generate_next_invoice_reference(
            $pdo,
            $companyId,
            $series
        );
    }

    return [
        'reference' => $reference,
        'status' => $status,
    ];
}
