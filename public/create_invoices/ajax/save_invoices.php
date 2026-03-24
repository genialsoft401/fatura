<?php
require_once '../../../app/config/db.php';
require_once '../../../app/helpers/subscription.php';
header('Content-Type: application/json'); 
session_start();

// Recebe os dados da fatura e dos itens
$invoiceData = $_POST['invoice'] ?? [];
$itemData = $_POST['items'] ?? [];

// Prepara os dados da fatura
$fatura = [];
foreach ($invoiceData as $field) {
    $fatura[$field['name']] = $field['value'];
}

// Extrai o ID da fatura para edição, se existir
$editInvoiceId = 0;
if (isset($fatura['edit_invoice_id']) && !empty($fatura['edit_invoice_id'])) {
    $editInvoiceId = intval($fatura['edit_invoice_id']);
    unset($fatura['edit_invoice_id']); // Remove do array para não tentar inserir/atualizar como coluna
}

try {
    $pdo->beginTransaction();

    // Assinatura/Plano: bloquear emissão se expirado e respeitar limite mensal
    $companyIdSession = (int)($_SESSION['user']['company_id'] ?? 0);
    if ($companyIdSession) {
        subscription_assert_active($pdo, $companyIdSession);
        // apenas na criação (não bloquear edição)
        if ($editInvoiceId <= 0) {
            subscription_check_limit($pdo, $companyIdSession, 'invoice');
        }
    }

    $contactId = null;
    // Verifica se um contato existente foi selecionado (contact_id do elemento select)
    if (isset($fatura['contact_id']) && !empty($fatura['contact_id'])) {
        $contactId = intval($fatura['contact_id']);
    } else {
        // Nenhum contato existente selecionado, assume que novos detalhes de contato são fornecidos
        $newContactFields = [
            'name' => $fatura['name'] ?? null,
            'email' => $fatura['email'] ?? null,
            'contributor' => $fatura['contributor'] ?? null,
            'address' => $fatura['address'] ?? null,
            'po_box' => $fatura['po_box'] ?? null,
            'country' => $fatura['country'] ?? null,
            'city' => $fatura['city'] ?? null,
            'company_id' => $_SESSION['user']['company_id'] // Novos contatos são vinculados à empresa do usuário atual
            // Adicione outros campos de contato se eles fizerem parte do formulário de novo contato em create_invoices.php
            // e.g., 'telephone' => $fatura['telephone'] ?? null,
        ];

        // Validação básica para novo contato
        if (empty($newContactFields['name']) || empty($newContactFields['email'])) {
            throw new Exception('Nome e e-mail do novo contato são obrigatórios.');
        }

        // Verifica se um contato com este e-mail já existe para a empresa
        $stmt = $pdo->prepare("SELECT id FROM contact WHERE email = ? AND company_id = ?");
        $stmt->execute([$newContactFields['email'], $newContactFields['company_id']]);
        $existingContact = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($existingContact) {
            $contactId = $existingContact['id']; // Usa o contato existente
            // Opcionalmente, atualize os detalhes do contato existente aqui, se necessário
        } else {
            // Filtra valores nulos para inserção para evitar problemas com colunas não anuláveis
            $filteredNewContactFields = array_filter($newContactFields, function($value) { return $value !== null; });

            $insertContactSql = "INSERT INTO contact (" . implode(", ", array_keys($filteredNewContactFields)) . ") VALUES (" . implode(", ", array_fill(0, count($filteredNewContactFields), "?")) . ")";
            $stmt = $pdo->prepare($insertContactSql);
            $stmt->execute(array_values($filteredNewContactFields));
            $contactId = $pdo->lastInsertId();
        }
    }

    // Remove campos relacionados ao contato de $fatura, pois eles já foram tratados
    $contactFormFields = ['contact_id', 'name', 'email', 'telephone', 'address', 'contributor', 'po_box', 'country', 'city'];
    foreach ($contactFormFields as $field) {
        unset($fatura[$field]);
    }



    // Prepara os campos da fatura para o banco de dados
    $invoiceDbFields = [
        'contact_id' => $contactId,
        'company_id' => intval($fatura['company_id']),
        'user_id' => intval($fatura['user_id']),
        'issue_date' => $fatura['issue_date'],
        'due_date' => $fatura['due_date'], // Assumindo que este é o número de dias
        'reference' => $fatura['reference'],
        'observation' => $fatura['observation'],
        'series' => $fatura['series'],
        'retention' => floatval($fatura['retention']),
        'currency' => $fatura['currency'],
        'manual_exchange_rate' => isset($fatura['manual_exchange_rate']) && !empty($fatura['manual_exchange_rate']) ? floatval($fatura['manual_exchange_rate']) : 1.0,
        'total_sum' => floatval($fatura['total_sum']), 
        'total_discount' => floatval($fatura['total_discount'] ?? 0.0),
        'subtotal_without_tax' => floatval($fatura['subtotal_without_tax'] ?? 0.0),
        'total_tax' => floatval($fatura['total_tax'] ?? 0.0),
        'retention_value' => floatval($fatura['retention_value'] ?? 0.0),
        'final_total' => floatval($fatura['final_total'] ?? 0.0),
        'converted_total' => isset($fatura['converted_total']) && !empty($fatura['converted_total']) ? floatval($fatura['converted_total']) : 0.0,
        'status' => 1 // Status padrão para novas faturas (ex: 'Rascunho' ou 'Pendente')
    ];

    $invoiceId = $editInvoiceId; // Este será o ID para a inserção de itens

    if ($editInvoiceId > 0) {
        // ATUALIZA fatura existente
        unset($invoiceDbFields['status']); // Assume que o status não é alterado por este formulário na edição
        $updateFields = [];
        $updateValues = [];
        foreach ($invoiceDbFields as $key => $value) {
            $updateFields[] = "$key = ?";
            $updateValues[] = $value;
        }
        $updateValues[] = $editInvoiceId; // Valor da cláusula WHERE

        $updateSql = "UPDATE invoices SET " . implode(", ", $updateFields) . " WHERE id = ?";
        $stmt = $pdo->prepare($updateSql);
        $stmt->execute($updateValues);

        // Exclui os itens existentes para esta fatura
        $stmt = $pdo->prepare("DELETE FROM invoice_items WHERE invoice_id = ?");
        $stmt->execute([$editInvoiceId]);

    } else {
        // INSERE nova fatura
        $insertColumns = implode(", ", array_keys($invoiceDbFields));
        $insertPlaceholders = implode(", ", array_fill(0, count($invoiceDbFields), "?"));
        $insertValues = array_values($invoiceDbFields);

        $insertSql = "INSERT INTO invoices ($insertColumns) VALUES ($insertPlaceholders)";
        $stmt = $pdo->prepare($insertSql);
        $stmt->execute($insertValues);
        $invoiceId = $pdo->lastInsertId();
    }

    // Insere os itens (tanto para faturas novas quanto atualizadas)
    if (!empty($itemData) && $invoiceId) {
        $itemPlaceholders = [];
        $itemValues = [];

        foreach ($itemData as $item) {
            $itemPlaceholders[] = "(?, ?, ?, ?, ?, ?)";
            $itemValues = array_merge($itemValues, [
                $invoiceId,
                intval($item['id']),
                floatval($item['quantity']),
                floatval($item['unit_price']),
                floatval($item['tax']),
                floatval($item['discount'])
            ]);
        }

        $insertItemsSql = "INSERT INTO invoice_items (invoice_id, item_id, quantity, unit_price, tax, discount) VALUES " . implode(", ", $itemPlaceholders);
        $stmt = $pdo->prepare($insertItemsSql);
        $stmt->execute($itemValues);
    }

    $pdo->commit();
    echo json_encode(['success' => true]);

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log("Error saving invoice: " . $e->getMessage()); // Registra o erro para depuração
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
