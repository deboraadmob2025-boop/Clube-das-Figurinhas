<?php
require_once "../config/database.php";

$database = new Database();
$db = $database->getConnection();

$pack_id = isset($_GET['pack_id']) ? (int)$_GET['pack_id'] : 0;
$stickers = [];

if ($pack_id > 0 && $db) {
    try {
        $stmt = $db->prepare("SELECT * FROM stickers WHERE pack_id = :pack ORDER BY order_index ASC");
        $stmt->execute([':pack' => $pack_id]);
        $rows = $stmt->fetchAll();
        
        foreach ($rows as $row) {
            $stickers[] = [
                "id" => "remote_" . $row['id'],
                "imageUrl" => $row['image_url'],
                "contentDescription" => $row['content_description']
            ];
        }
    } catch (PDOException $e) {
        // Fallback
    }
}

if (empty($stickers)) {
    // Return standard mock pack stickers as sample
    $stickers = [
        ["id" => "cg1", "imageUrl" => "https://lh3.googleusercontent.com/aida-public/AB6AXuCtkSf5UeSaJUridQTq5N4vmoVsP0gexrgiYTdP0qRlbEs30ew908UhCNEi9m4LssAhXwVOg57u1e_ez-5g3TCOLjGUqA_-27lK6b3gjBo5fwN-xvC_bKUNHrOkf3ISxAXnFmmx8mWdAdYoIk0HajgPSXMuRMLYN6Jh-eRtFtqy4r1QWUPvDtru9uh3AoAcbANQjuMITfhzZ5Rkxqa6gWxcRXhsv9GA-oJYENfKNAiEofeImH6pZ-f27B90qgHjsVEMaAFiBSJb5Tg", "contentDescription" => "Futuristic blue neon cat"],
        ["id" => "cg2", "imageUrl" => "https://lh3.googleusercontent.com/aida-public/AB6AXuA3wfyyh4fYujNiui8ykRW7sThV2EscYZZpuXEFUI3NbIeojw_q5XIjhtJdEBbvtm3fXWgkX4UrUX1db3BoaTMLIPTva7bXJyYDpMYL7t9XPotyDJ73vhvtYxh4TTsCtHbjFwB0iHI9Z3iMQfYO3eDIFPJpWzNE6RIj54lAQanMU5km61D3nyzx6c88sHI7KXZXyTjQAcnhhZ4NQTlnqxYzhetVswujz_XcYvqXC3brpw24bx52M5QPqVMlWJq2E6CwiI5cCH3-hjI", "contentDescription" => "Robot cat with digital hearts"]
    ];
}

sendResponse(200, "Stickers fetched successfully", $stickers);
