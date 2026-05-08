<?php
require_once '../../../app/config/db.php';


/**
 * Tipos válidos
 */
function document_types(): array
{
    return ['FT', 'FR', 'NC', 'ND'];
}

/**
 * Gera número de documento seguro
 */
function generate_document_number(PDO $pdo, int $companyId, string $type): string
{
    $year = date('Y');
    $type = strtoupper(trim($type));

    $stmt = $pdo->prepare("
        SELECT id, last_number
        FROM document_sequences
        WHERE company_id = ?
          AND document_type = ?
          AND year = ?
        FOR UPDATE
    ");

    $stmt->execute([$companyId, $type, $year]);
    $sequence = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$sequence) {

        $insert = $pdo->prepare("
            INSERT INTO document_sequences (company_id, document_type, year, last_number)
            VALUES (?, ?, ?, 1)
        ");
        $insert->execute([$companyId, $type, $year]);

        $number = 1;

    } else {

        $number = $sequence['last_number'] + 1;

        $update = $pdo->prepare("
            UPDATE document_sequences
            SET last_number = ?
            WHERE id = ?
        ");
        $update->execute([$number, $sequence['id']]);
    }

    return sprintf('%s %s/%06d', $type, $year, $number);
}
