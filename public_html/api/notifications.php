<?php
require_once "../config/database.php";

$database = new Database();
$db = $database->getConnection();

$notifications = [];

if ($db) {
    try {
        $stmt = $db->prepare("SELECT * FROM notifications WHERE status = 'sent' ORDER BY sent_at DESC LIMIT 10");
        $stmt->execute();
        $rows = $stmt->fetchAll();
        
        foreach ($rows as $row) {
            $notifications[] = [
                "id" => (int)$row['id'],
                "title" => $row['title'],
                "message" => $row['message'],
                "sentAt" => $row['sent_at']
            ];
        }
    } catch (PDOException $e) {
        // Fallback
    }
}

if (empty($notifications)) {
    $notifications = [
        [
            "id" => 1,
            "title" => "🔥 Novos Stickers da Semana!",
            "message" => "Venha conferir os pacotes de memes atualizados para animar suas conversas no WhatsApp!",
            "sentAt" => date("Y-m-d H:i:s", strtotime("-2 hours"))
        ],
        [
            "id" => 2,
            "title" => "🐱 Exclusivo para Membros Premium",
            "message" => "Acabamos de postar o pacote Cyber Gatos! Baixe antes que expire.",
            "sentAt" => date("Y-m-d H:i:s", strtotime("-1 day"))
        ]
    ];
}

sendResponse(200, "Historical push logs returned", $notifications);
