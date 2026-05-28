<?php
require_once "../config/database.php";

$database = new Database();
$db = $database->getConnection();

$ads = [];

if ($db) {
    try {
        $stmt = $db->prepare("SELECT * FROM ads_config");
        $stmt->execute();
        $rows = $stmt->fetchAll();
        
        foreach ($rows as $row) {
            $ads[] = [
                "adType" => $row['ad_type'],
                "unitIdTest" => $row['unit_id_test'],
                "unitIdProd" => $row['unit_id_prod'],
                "isActive" => (bool)$row['is_active']
            ];
        }
    } catch (PDOException $e) {
        // Fallback
    }
}

if (empty($ads)) {
    // Config values fallback
    $ads = [
        [
            "adType" => "Banner",
            "unitIdTest" => "ca-app-pub-3940256099942544/6300978111",
            "unitIdProd" => "ca-app-pub-3940256099942544/6300978111",
            "isActive" => true
        ],
        [
            "adType" => "Interstitial",
            "unitIdTest" => "ca-app-pub-3940256099942544/1033173712",
            "unitIdProd" => "ca-app-pub-3940256099942544/1033173712",
            "isActive" => true
        ],
        [
            "adType" => "Rewarded",
            "unitIdTest" => "ca-app-pub-3940256099942544/5224354917",
            "unitIdProd" => "ca-app-pub-3940256099942544/5224354917",
            "isActive" => true
        ],
        [
            "adType" => "Rewarded Interstitial",
            "unitIdTest" => "ca-app-pub-3940256099942544/5354046379",
            "unitIdProd" => "ca-app-pub-3940256099942544/5354046379",
            "isActive" => true
        ]
    ];
}

sendResponse(200, "Ad configurations fetched successfully", $ads);
