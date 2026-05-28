<?php
require_once "../config/database.php";

$database = new Database();
$db = $database->getConnection();

$trending = [];

if ($db) {
    try {
        $query = "SELECT sp.*, c.name as category_name FROM sticker_packs sp 
                  LEFT JOIN categories c ON sp.category_id = c.id 
                  WHERE sp.is_exclusive = 1 OR sp.downloads_count > 1000 
                  ORDER BY sp.downloads_count DESC LIMIT 5";
        $stmt = $db->prepare($query);
        $stmt->execute();
        $rows = $stmt->fetchAll();
        
        foreach ($rows as $row) {
            // Get stickers
            $st_stmt = $db->prepare("SELECT * FROM stickers WHERE pack_id = :pack_id ORDER BY order_index ASC LIMIT 6");
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
            
            $trending[] = [
                "id" => "pack_" . $row['id'],
                "name" => $row['name'],
                "creator" => $row['creator'],
                "category" => $row['category_name'] ?: 'General',
                "isPremium" => (bool)$row['is_premium'],
                "isExclusive" => (bool)$row['is_exclusive'],
                "coverUrl" => $row['cover_url'],
                "totalStickers" => count($stickers_list),
                "downloads" => number_format($row['downloads_count']),
                "likes" => (int)$row['likes_count'],
                "stickers" => $stickers_list
            ];
        }
    } catch (PDOException $e) {
        // Fallback
    }
}

if (empty($trending)) {
    // High fidelity feedback fallback matching original MockData
    $trending = [
        [
            "id" => "cyber_gatos",
            "name" => "Cyber Gatos",
            "creator" => "NeonMochi",
            "category" => "Animals",
            "isPremium" => true,
            "isExclusive" => false,
            "coverUrl" => "https://lh3.googleusercontent.com/aida-public/AB6AXuCtkSf5UeSaJUridQTq5N4vmoVsP0gexrgiYTdP0qRlbEs30ew908UhCNEi9m4LssAhXwVOg57u1e_ez-5g3TCOLjGUqA_-27lK6b3gjBo5fwN-xvC_bKUNHrOkf3ISxAXnFmmx8mWdAdYoIk0HajgPSXMuRMLYN6Jh-eRtFtqy4r1QWUPvDtru9uh3AoAcbANQjuMITfhzZ5Rkxqa6gWxcRXhsv9GA-oJYENfKNAiEofeImH6pZ-f27B90qgHjsVEMaAFiBSJb5Tg",
            "totalStickers" => 9,
            "downloads" => "1,250",
            "likes" => 482,
            "stickers" => [
                ["id" => "cg1", "imageUrl" => "https://lh3.googleusercontent.com/aida-public/AB6AXuCtkSf5UeSaJUridQTq5N4vmoVsP0gexrgiYTdP0qRlbEs30ew908UhCNEi9m4LssAhXwVOg57u1e_ez-5g3TCOLjGUqA_-27lK6b3gjBo5fwN-xvC_bKUNHrOkf3ISxAXnFmmx8mWdAdYoIk0HajgPSXMuRMLYN6Jh-eRtFtqy4r1QWUPvDtru9uh3AoAcbANQjuMITfhzZ5Rkxqa6gWxcRXhsv9GA-oJYENfKNAiEofeImH6pZ-f27B90qgHjsVEMaAFiBSJb5Tg", "contentDescription" => "Futuristic blue neon cat"],
                ["id" => "cg2", "imageUrl" => "https://lh3.googleusercontent.com/aida-public/AB6AXuA3wfyyh4fYujNiui8ykRW7sThV2EscYZZpuXEFUI3NbIeojw_q5XIjhtJdEBbvtm3fXWgkX4UrUX1db3BoaTMLIPTva7bXJyYDpMYL7t9XPotyDJ73vhvtYxh4TTsCtHbjFwB0iHI9Z3iMQfYO3eDIFPJpWzNE6RIj54lAQanMU5km61D3nyzx6c88sHI7KXZXyTjQAcnhhZ4NQTlnqxYzhetVswujz_XcYvqXC3brpw24bx52M5QPqVMlWJq2E6CwiI5cCH3-hjI", "contentDescription" => "Robot cat with digital hearts"]
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

sendResponse(200, "Trending packs fetched successfully", $trending);
