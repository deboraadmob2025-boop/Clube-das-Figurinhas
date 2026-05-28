<?php
require_once "../config/database.php";

$database = new Database();
$db = $database->getConnection();

$categories = [];

if ($db) {
    try {
        $query = "SELECT * FROM categories ORDER BY order_index ASC";
        $stmt = $db->prepare($query);
        $stmt->execute();
        $rows = $stmt->fetchAll();
        
        foreach ($rows as $row) {
            $categories[] = [
                "id" => (int)$row['id'],
                "name" => $row['name'],
                "order_index" => (int)$row['order_index'],
                "icon_emoji" => $row['icon_emoji']
            ];
        }
    } catch (PDOException $e) {
        // Fallback below
    }
}

if (empty($categories)) {
    $categories = [
        ["id" => 1, "name" => "Memes", "order_index" => 1, "icon_emoji" => "😂"],
        ["id" => 2, "name" => "Love", "order_index" => 2, "icon_emoji" => "💖"],
        ["id" => 3, "name" => "Anime", "order_index" => 3, "icon_emoji" => "🌸"],
        ["id" => 4, "name" => "Funny", "order_index" => 4, "icon_emoji" => "🤪"],
        ["id" => 5, "name" => "Animals", "order_index" => 5, "icon_emoji" => "🐱"],
        ["id" => 6, "name" => "Gaming", "order_index" => 6, "icon_emoji" => "🎮"]
    ];
}

sendResponse(200, "Categories fetched successfully", $categories);
