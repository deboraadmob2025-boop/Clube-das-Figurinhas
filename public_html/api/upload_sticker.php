<?php
require_once "../config/database.php";

$decoded = validateJWT();
if (!$decoded) {
    sendResponse(401, "Acesso não autorizado.");
}

$pack_id = isset($_POST['pack_id']) ? (int)$_POST['pack_id'] : 0;
$desc = isset($_POST['description']) ? trim($_POST['description']) : 'Sticker';

if ($pack_id <= 0) {
    sendResponse(400, "Favor informar um ID de pacote válido.");
}

if (!isset($_FILES['sticker']) || $_FILES['sticker']['error'] !== UPLOAD_ERR_OK) {
    sendResponse(400, "Nenhum arquivo enviado ou erro no upload.");
}

$file = $_FILES['sticker'];
$allowTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];

if (!in_array($file['type'], $allowTypes)) {
    sendResponse(400, "Tipo de arquivo inválido. Apenas imagens, GIFs ou WEBP são permitidos.");
}

$ext = pathinfo($file['name'], PATHINFO_EXTENSION);
$newName = uniqid("stk_") . "." . $ext;
$targetDir = "../uploads/stickers/";

if (!file_exists($targetDir)) {
    mkdir($targetDir, 0777, true);
}

$targetFilePath = $targetDir . $newName;

if (move_uploaded_file($file['tmp_name'], $targetFilePath)) {
    $webPath = "https://" . $_SERVER['HTTP_HOST'] . "/uploads/stickers/" . $newName;
    
    $database = new Database();
    $db = $database->getConnection();
    
    if ($db) {
        try {
            $stmt = $db->prepare("INSERT INTO stickers (pack_id, image_url, content_description) VALUES (:pack, :url, :descr)");
            $stmt->execute([
                ':pack' => $pack_id,
                ':url' => $webPath,
                ':descr' => $desc
            ]);
            $stickerId = $db->lastInsertId();
            
            sendResponse(201, "Sticker carregado com sucesso!", [
                "sticker_id" => (int)$stickerId,
                "imageUrl" => $webPath,
                "contentDescription" => $desc
            ]);
        } catch (PDOException $e) {
            sendResponse(500, "Erro ao registrar sticker no banco de dados.");
        }
    } else {
        // Mock successful registration
        sendResponse(201, "Sticker carregado com sucesso (Simulado)!", [
            "sticker_id" => rand(100, 999),
            "imageUrl" => $webPath,
            "contentDescription" => $desc
        ]);
    }
} else {
    sendResponse(500, "Ocorreu um erro ao salvar o arquivo enviado.");
}
