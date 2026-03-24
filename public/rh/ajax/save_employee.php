<?php
require_once '../../../app/config/db.php';
session_start();

$company_id = $_SESSION['user']['company_id'];
$name = $_POST['name'] ?? '';
$bi = $_POST['bi'] ?? '';
$position = $_POST['position'] ?? '';
$salary = $_POST['salary'] ?? 0;
$status = $_POST['status'] ?? 'ativo';
$id = $_POST['id'] ?? null;
$document_type = $_POST['document_type'] ?? null;
$birth_date = $_POST['birth_date'] ?? null;
$marital_status = $_POST['marital_status'] ?? null;
$academic_level = $_POST['academic_level'] ?? null;
$contract_type = $_POST['contract_type'] ?? null;
$admission_date = $_POST['admission_date'] ?? null;
$iban = $_POST['iban'] ?? null;

// Uploads
$uploadImgDir = __DIR__ . '/../../assets/img/employees/';
$uploadDocDir = __DIR__ . '/../../assets/docs/employees/';
if (!is_dir($uploadImgDir)) @mkdir($uploadImgDir, 0775, true);
if (!is_dir($uploadDocDir)) @mkdir($uploadDocDir, 0775, true);

function saveUpload($fileKey, $destDir, $allowedExts) {
    if (!isset($_FILES[$fileKey]) || $_FILES[$fileKey]['error'] !== UPLOAD_ERR_OK) return null;

    $tmp = $_FILES[$fileKey]['tmp_name'];
    $orig = (string)($_FILES[$fileKey]['name'] ?? 'file');
    $ext = strtolower(pathinfo($orig, PATHINFO_EXTENSION));
    if (!in_array($ext, $allowedExts, true)) {
        throw new Exception("Formato inválido para $fileKey");
    }

    $fileName = uniqid($fileKey . '_', true) . '.' . $ext;
    $dest = rtrim($destDir, '/') . '/' . $fileName;
    if (!move_uploaded_file($tmp, $dest)) {
        throw new Exception("Falha ao salvar upload: $fileKey");
    }
    return $fileName;
}

try {
    $photo = saveUpload('photo', $uploadImgDir, ['png','jpg','jpeg','webp','gif']);
    $doc1 = saveUpload('doc1', $uploadDocDir, ['pdf','png','jpg','jpeg','webp','gif']);
    $doc2 = saveUpload('doc2', $uploadDocDir, ['pdf','png','jpg','jpeg','webp','gif']);

    if ($id) {
        // mantém arquivos antigos se não vier novo upload
        $stmtOld = $pdo->prepare("SELECT photo_url, doc1_url, doc2_url FROM employees WHERE id = ? AND company_id = ?");
        $stmtOld->execute([$id, $company_id]);
        $old = $stmtOld->fetch(PDO::FETCH_ASSOC) ?: [];

        $photoUrl = $photo ?: ($old['photo_url'] ?? null);
        $doc1Url  = $doc1  ?: ($old['doc1_url'] ?? null);
        $doc2Url  = $doc2  ?: ($old['doc2_url'] ?? null);

        // Se substituiu por novo arquivo, remove o antigo do disco
        if ($photo && !empty($old['photo_url'])) {
            $p = $uploadImgDir . basename($old['photo_url']);
            if (is_file($p)) @unlink($p);
        }
        if ($doc1 && !empty($old['doc1_url'])) {
            $p = $uploadDocDir . basename($old['doc1_url']);
            if (is_file($p)) @unlink($p);
        }
        if ($doc2 && !empty($old['doc2_url'])) {
            $p = $uploadDocDir . basename($old['doc2_url']);
            if (is_file($p)) @unlink($p);
        }

        $stmt = $pdo->prepare("UPDATE employees SET 
            name = ?, bi = ?, position = ?, salary = ?, status = ?, document_type = ?, birth_date = ?, marital_status = ?, academic_level = ?, contract_type = ?, admission_date = ?, iban = ?, photo_url = ?, doc1_url = ?, doc2_url = ?
            WHERE id = ? AND company_id = ?");
        $stmt->execute([
            $name, $bi, $position, $salary, $status, $document_type, $birth_date,
            $marital_status, $academic_level,
            $contract_type, $admission_date, $iban,
            $photoUrl, $doc1Url, $doc2Url,
            $id, $company_id
        ]);
    } else {
        $stmt = $pdo->prepare("INSERT INTO employees (
            company_id, name, bi, position, salary, status, document_type, birth_date, marital_status, academic_level, contract_type, admission_date, iban, photo_url, doc1_url, doc2_url
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $company_id, $name, $bi, $position, $salary, $status, $document_type, $birth_date,
            $marital_status, $academic_level,
            $contract_type, $admission_date, $iban,
            $photo, $doc1, $doc2
        ]);
    }

    echo 'ok';
} catch (Exception $e) {
    http_response_code(400);
    echo $e->getMessage();
}
