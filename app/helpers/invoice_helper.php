<?php
require_once '../../../app/config/db.php';
require_once __DIR__ . '/document_helper.php';

/**
 * ==================================================
 * HELPER DE CONTROLO DE NUMERAÇÃO DE FATURAS
 * ==================================================
 *
 * ✔ Corrigido:
 * - Sem duplicação
 * - Com lock (FOR UPDATE)
 * - Com verificação de existência
 * - Compatível com concorrência
 * - Ajustado para regras fiscais AGT
 */

/**
 * Mapeamento de status
 */
function invoice_status_map(): array
{
    return [
        'Rascunho'   => 1,
        'Pago'       => 4,
        'Cancelado'  => 2,
        'Finalizado' => 3, // corrigido
        'Pendente'   => 5,
    ];
}

/**
 * Verifica se deve gerar número
 */
function invoice_should_generate_number(string $status, ?string $reference): bool
{
    $allowed = ['Pendente', 'Pago', 'Finalizado'];

    return in_array($status, $allowed, true)
        && empty($reference);
}

/**
 * AGT: NÃO apagar referência
 */
function invoice_should_clear_number(string $status): bool
{
    return false;
}

/**
 * Verifica se referência já existe
 */
function reference_exists(PDO $pdo, string $reference): bool
{
    $stmt = $pdo->prepare("
        SELECT COUNT(*) 
        FROM invoices 
        WHERE reference = ?
    ");
    $stmt->execute([$reference]);

    return $stmt->fetchColumn() > 0;
}

/**
 * Gera próximo número seguro
 */
function generate_next_invoice_reference(PDO $pdo, int $companyId, ?string $series = 'FT'): string
{
    $year = date('Y');
    $prefix = strtoupper(trim($series ?: 'FT'));
    $like = $prefix . ' ' . $year . '/%';

    // LOCK na sequência
    $stmt = $pdo->prepare("
        SELECT MAX(CAST(SUBSTRING_INDEX(reference, '/', -1) AS UNSIGNED)) as last_number
        FROM invoices
        WHERE company_id = ?
          AND reference LIKE ?
        FOR UPDATE
    ");

    $stmt->execute([$companyId, $like]);
    $lastNumber = (int) $stmt->fetchColumn();

    $nextNumber = $lastNumber + 1;

    // Garantir unicidade
    do {
        $reference = sprintf('%s %s/%06d', $prefix, $year, $nextNumber);
        $exists = reference_exists($pdo, $reference);

        if ($exists) {
            $nextNumber++;
        }
    } while ($exists);

    return $reference;
}

/**
 * Aplicar regras de numeração
 */
function apply_invoice_number_rules(
    PDO $pdo,
    int $companyId,
    string $status,
    ?string $currentReference,
    string $documentType = 'FT'
): array {

    //  Normalizar tipo de documento
    $documentType = strtoupper(trim($documentType));

    //  Cancelado → mantém número (regra AGT)
    if ($status === 'Cancelado') {
        return [
            'reference' => $currentReference,
            'status' => $status,
        ];
    }

    //  Só gera número se:
    // - status válido
    // - ainda não tem referência
    if (invoice_should_generate_number($status, $currentReference)) {

        $reference = generate_document_number(
            $pdo,
            $companyId,
            $documentType
        );

        return [
            'reference' => $reference,
            'status' => $status,
        ];
    }

    //  Caso padrão → mantém o que já existe
    return [
        'reference' => $currentReference,
        'status' => $status,
    ];
}
