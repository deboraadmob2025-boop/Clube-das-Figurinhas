<?php
require_once "../config/database.php";

$database = new Database();
$db = $database->getConnection();

$config = [];

if ($db) {
    try {
        $stmt = $db->prepare("SELECT * FROM app_config");
        $stmt->execute();
        $rows = $stmt->fetchAll();
        
        foreach ($rows as $row) {
            $config[$row['key_name']] = $row['val_value'];
        }
    } catch (PDOException $e) {
        // Fallback
    }
}

// Fallback if DB empty or not set up
if (empty($config)) {
    $config = [
        "app_name" => "Sticker Store Premium",
        "logo_url" => "ic_logo.xml",
        "primary_color" => "#6200EE",
        "languages_enabled" => "pt-BR,en-US,es-ES",
        "policy_url" => "https://mystickerstore.com/privacy",
        "terms_url" => "https://mystickerstore.com/terms"
    ];
}

sendResponse(200, "App config options fetched successfully", $config);
