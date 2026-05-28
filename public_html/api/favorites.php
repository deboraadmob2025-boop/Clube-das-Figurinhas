<?php
require_once "../config/database.php";

$decoded = validateJWT();
if (!$decoded) {
    sendResponse(401, "Sessão inválida ou token expirado.");
}

$user_id = $decoded['sub'];
$database = new Database();
$db = $database->getConnection();

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'POST') {
    $input = json_decode(file_get_contents("php://input"), true);
    $pack_id = isset($input['pack_id']) ? (int)$input['pack_id'] : 0;
    
    if ($pack_id <= 0) {
        sendResponse(400, "ID de pacote inválido.");
    }
    
    if ($db) {
        try {
            // Check if exists
            $check = $db->prepare("SELECT id FROM favorites WHERE user_id = :u AND pack_id = :p");
            $check->execute([':u' => $user_id, ':p' => $pack_id]);
            $exists = $check->fetch();
            
            if ($exists) {
                // Delete
                $del = $db->prepare("DELETE FROM favorites WHERE user_id = :u AND pack_id = :p");
                $del->execute([':u' => $user_id, ':p' => $pack_id]);
                
                // Decrement pack likes
                $db->prepare("UPDATE sticker_packs SET likes_count = GREATEST(0, likes_count - 1) WHERE id = :id")->execute([':id' => $pack_id]);
                
                sendResponse(200, "Favorito removido com sucesso.", ["is_favorite" => false]);
            } else {
                // Insert
                $ins = $db->prepare("INSERT INTO favorites (user_id, pack_id) VALUES (:u, :p)");
                $ins->execute([':u' => $user_id, ':p' => $pack_id]);
                
                // Increment pack likes
                $db->prepare("UPDATE sticker_packs SET likes_count = likes_count + 1 WHERE id = :id")->execute([':id' => $pack_id]);
                
                sendResponse(200, "Pacote adicionado aos favoritos.", ["is_favorite" => true]);
            }
        } catch (PDOException $e) {
            sendResponse(500, "Erro ao salvar favorito no servidor.");
        }
    } else {
        sendResponse(200, "Simulado: Salvo com sucesso localmente.", ["is_favorite" => true]);
    }
} else {
    // GET user favorites
    $favorites = [];
    if ($db) {
        try {
            $query = "SELECT sp.*, c.name as category_name FROM favorites f
                      JOIN sticker_packs sp ON f.pack_id = sp.id 
                      LEFT JOIN categories c ON sp.category_id = c.id
                      WHERE f.user_id = :u";
            $stmt = $db->prepare($query);
            $stmt->execute([':u' => $user_id]);
            $rows = $stmt->fetchAll();
            
            foreach ($rows as $row) {
                $favorites[] = [
                    "id" => "pack_" . $row['id'],
                    "name" => $row['name'],
                    "creator" => $row['creator'],
                    "category" => $row['category_name'] ?: 'General',
                    "isPremium" => (bool)$row['is_premium'],
                    "coverUrl" => $row['cover_url'],
                    "downloads" => number_format($row['downloads_count'])
                ];
            }
        } catch (PDOException $e) {
            // Err
        }
    }
    
    sendResponse(200, "Favoritos do usuário recuperados com sucesso.", $favorites);
}
