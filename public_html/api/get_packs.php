<?php
require_once "../config/database.php";

$database = new Database();
$db = $database->getConnection();

$category = isset($_GET['category']) ? $_GET['category'] : '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
$offset = ($page - 1) * $limit;

$packs = [];

if ($db) {
    try {
        $query = "SELECT sp.*, c.name as category_name, 
                  (SELECT COUNT(*) FROM stickers WHERE pack_id = sp.id) as total_stickers 
                  FROM sticker_packs sp 
                  LEFT JOIN categories c ON sp.category_id = c.id";
        
        $params = [];
        if (!empty($category)) {
            $query .= " WHERE c.name = :category";
            $params[':category'] = $category;
        }
        
        $query .= " ORDER BY sp.id DESC LIMIT :limit OFFSET :offset";
        
        $stmt = $db->prepare($query);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        foreach ($params as $key => $val) {
            $stmt->bindValue($key, $val);
        }
        
        $stmt->execute();
        $rows = $stmt->fetchAll();
        
        foreach ($rows as $row) {
            // Get stickers for this pack
            $st_stmt = $db->prepare("SELECT * FROM stickers WHERE pack_id = :pack_id ORDER BY order_index ASC");
            $st_stmt->execute([':pack_id' => $row['id']]);
            $stickers = $st_stmt->fetchAll();
            
            $stickers_list = [];
            foreach ($stickers as $st) {
                $stickers_list[] = [
                    "id" => "remote_" . $st['id'],
                    "imageUrl" => $st['image_url'],
                    "contentDescription" => $st['content_description']
                ];
            }
            
            $packs[] = [
                "id" => "pack_" . $row['id'],
                "name" => $row['name'],
                "creator" => $row['creator'],
                "category" => $row['category_name'] ?: 'General',
                "isPremium" => (bool)$row['is_premium'],
                "isExclusive" => (bool)$row['is_exclusive'],
                "coverUrl" => $row['cover_url'],
                "totalStickers" => (int)$row['total_stickers'],
                "downloads" => number_format($row['downloads_count']),
                "likes" => (int)$row['likes_count'],
                "stickers" => $stickers_list
            ];
        }
    } catch (PDOException $e) {
        // Fall back to Mock
    }
}

// Fallback to high fidelity mock if database is empty or not configured yet
if (empty($packs)) {
    $packs = [
        [
            "id" => "cyber_gatos",
            "name" => "Cyber Gatos",
            "creator" => "NeonMochi",
            "category" => "Animals",
            "isPremium" => true,
            "isExclusive" => false,
            "coverUrl" => "https://lh3.googleusercontent.com/aida-public/AB6AXuCtkSf5UeSaJUridQTq5N4vmoVsP0gexrgiYTdP0qRlbEs30ew908UhCNEi9m4LssAhXwVOg57u1e_ez-5g3TCOLjGUqA_-27lK6b3gjBo5fwN-xvC_bKUNHrOkf3ISxAXnFmmx8mWdAdYoIk0HajgPSXMuRMLYN6Jh-eRtFtqy4r1QWUPvDtru9uh3AoAcbANQjuMITfhzZ5Rkxqa6gWxcRXhsv9GA-oJYENfKNAiEofeImH6pZ-f27B90qgHjsVEMaAFiBSJb5Tg",
            "totalStickers" => 9,
            "downloads" => "1.2k",
            "likes" => 482,
            "stickers" => [
                ["id" => "cg1", "imageUrl" => "https://lh3.googleusercontent.com/aida-public/AB6AXuCtkSf5UeSaJUridQTq5N4vmoVsP0gexrgiYTdP0qRlbEs30ew908UhCNEi9m4LssAhXwVOg57u1e_ez-5g3TCOLjGUqA_-27lK6b3gjBo5fwN-xvC_bKUNHrOkf3ISxAXnFmmx8mWdAdYoIk0HajgPSXMuRMLYN6Jh-eRtFtqy4r1QWUPvDtru9uh3AoAcbANQjuMITfhzZ5Rkxqa6gWxcRXhsv9GA-oJYENfKNAiEofeImH6pZ-f27B90qgHjsVEMaAFiBSJb5Tg", "contentDescription" => "Futuristic blue neon cat"]
            ]
        ],
        [
            "id" => "retro_vibes",
            "name" => "Retro Vibes",
            "creator" => "Synthwave_Artist",
            "category" => "Gaming",
            "isPremium" => false,
            "isExclusive" => false,
            "coverUrl" => "https://lh3.googleusercontent.com/aida-public/AB6AXuDSlXNY5xsvukq1cD3BhF6kpieE4o3vgOZk8YylLlfBhEquaGujYIHv4QQ9rAub4A1DRMFUzHjM5xKHdC4OOWlgHdsfpPqOtx19A8WQpiXCfpQib90cmaTUY9Sx-DoPGsbRmU5dJHDGht4bQiF6BQJx_DnuOAOJ0QvkzKsheFIsaS6MRg__iO_2QRhKn_Eo8Yl1PaD_58ALnKDr2xow6qNf4XwSrYsK3L95amcKQHj92dp8jVKK-v13i9K328iNg706PgT3oGpFGg0",
            "totalStickers" => 6,
            "downloads" => "950",
            "likes" => 128,
            "stickers" => [
                ["id" => "rv1", "imageUrl" => "https://lh3.googleusercontent.com/aida-public/AB6AXuDSlXNY5xsvukq1cD3BhF6kpieE4o3vgOZk8YylLlfBhEquaGujYIHv4QQ9rAub4A1DRMFUzHjM5xKHdC4OOWlgHdsfpPqOtx19A8WQpiXCfpQib90cmaTUY9Sx-DoPGsbRmU5dJHDGht4bQiF6BQJx_DnuOAOJ0QvkzKsheFIsaS6MRg__iO_2QRhKn_Eo8Yl1PaD_58ALnKDr2xow6qNf4XwSrYsK3L95amcKQHj92dp8jVKK-v13i9K328iNg706PgT3oGpFGg0", "contentDescription" => "3D retro pink sunglasses"]
            ]
        ]
    ];
}

sendResponse(200, "Sticker Packs list loaded successfully", $packs);
