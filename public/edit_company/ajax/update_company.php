<?php
require_once '../../../app/config/db.php';

header('Content-Type: application/json');

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Método inválido.');
    }

    // Verifica se o ID da empresa foi enviado via POST
    if (!isset($_POST['id']) || empty($_POST['id'])) {
        throw new Exception('ID da empresa não fornecido.');
    }

    $companyId = $_POST['id'];
    $name = $_POST['name'] ?? null;
    $registration_number = $_POST['registration_number'] ?? null;
    $phone = $_POST['phone'] ?? null;
    $email = $_POST['email'] ?? null;
    $website = $_POST['website'] ?? null;
    $address = $_POST['address'] ?? null;
    $country = $_POST['country'] ?? null;
    $city = $_POST['city'] ?? null;
    $zip_code = $_POST['zip_code'] ?? null;
    $currency = $_POST['currency'] ?? null;

    // campos extras (fatura)
    $vat_regime = $_POST['vat_regime'] ?? null;
    $goods_services = $_POST['goods_services'] ?? null;
    $bank_name = $_POST['bank_name'] ?? null;
    $iban = $_POST['iban'] ?? null;
    $bank_details = $_POST['bank_details'] ?? null;

    $logoPath = null;

    // Verifica se um novo logo foi enviado
    if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['logo']['tmp_name'];

        // Validação básica do arquivo
        $originalName = (string)($_FILES['logo']['name'] ?? 'logo');
        $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        $allowed = ['png','jpg','jpeg','webp','gif'];
        if (!in_array($ext, $allowed, true)) {
            throw new Exception('Formato de logo inválido. Envie PNG/JPG/JPEG/WEBP/GIF.');
        }

        $fileName = uniqid('logo_', true) . '.' . $ext;
        $uploadDir = realpath(__DIR__ . '/../../assets/img/companies');
        if ($uploadDir === false) {
            throw new Exception('Diretório de upload não encontrado.');
        }

        $destPath = $uploadDir . DIRECTORY_SEPARATOR . $fileName;
        $logoPath = $fileName;

        // Move o arquivo para o diretório de upload
        if (!move_uploaded_file($fileTmpPath, $destPath)) {
            throw new Exception('Erro ao salvar o logo.');
        }
    }

    $query = "UPDATE companies SET 
                name = :name,
                registration_number = :registration_number,
                phone = :phone,
                email = :email,
                website = :website,
                address = :address,
                country = :country,
                city = :city,
                zip_code = :zip_code,
                currency = :currency,
                vat_regime = :vat_regime,
                goods_services = :goods_services,
                bank_name = :bank_name,
                iban = :iban,
                bank_details = :bank_details";

    if ($logoPath) {
        $query .= ", logo_url = :logo_url";
    }

    $query .= " WHERE id = :company_id";

    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':name', $name);
    $stmt->bindParam(':registration_number', $registration_number);
    $stmt->bindParam(':phone', $phone);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':website', $website);
    $stmt->bindParam(':address', $address);
    $stmt->bindParam(':country', $country);
    $stmt->bindParam(':city', $city);
    $stmt->bindParam(':zip_code', $zip_code);
    $stmt->bindParam(':currency', $currency);
    $stmt->bindParam(':vat_regime', $vat_regime);
    $stmt->bindParam(':goods_services', $goods_services);
    $stmt->bindParam(':bank_name', $bank_name);
    $stmt->bindParam(':iban', $iban);
    $stmt->bindParam(':bank_details', $bank_details);

    if ($logoPath) {
        $stmt->bindParam(':logo_url', $logoPath);
    }
    $stmt->bindParam(':company_id', $companyId, PDO::PARAM_INT);

    if ($stmt->execute()) {
        $payload = ['success' => true];
        if ($logoPath) {
            $payload['data'] = ['logo_url' => $logoPath];
        }
        echo json_encode($payload);
    } else {
        throw new Exception('Erro ao atualizar os dados da empresa.');
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
