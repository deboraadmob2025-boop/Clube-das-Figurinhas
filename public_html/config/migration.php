<?php
/**
 * DbAutoMigration - Professional Dynamic Migrations & Self-Healing Engine
 * Auto-creates tables, auto-updates columns, auto-seeds mock/production values, and writes secure logs.
 */

class DbAutoMigration {
    
    // Paths
    private static $logDir = __DIR__ . '/../logs';
    private static $logFile = __DIR__ . '/../logs/migrations.log';
    private static $uploadDir = __DIR__ . '/../uploads';

    /**
     * Executes the automatic self-healing database check and migrations.
     * Called on each DB initiation to guarantee zero-admin schema setups.
     */
    public static function run($pdo, $dbName) {
        if (!$pdo) {
            return false;
        }

        try {
            // 1. Ensure logs & uploads folders exist with professional security
            self::initDirectories();

            // 2. Fetch all existing tables
            $stmt = $pdo->query("SHOW TABLES");
            $existingTables = [];
            while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
                $existingTables[] = strtolower($row[0]);
            }

            // Defined table models, schemas and expected properties
            $tablesSchema = self::getSchemaModels();

            $createdCount = 0;
            $updatedColumnsCount = 0;

            // 3. Process each table
            foreach ($tablesSchema as $tableName => $tableData) {
                $lowTableName = strtolower($tableName);
                
                if (!in_array($lowTableName, $existingTables)) {
                    // Create missing table
                    $pdo->exec($tableData['sql']);
                    self::log("SUCCESS", "Tabela completa '{$tableName}' criada automaticamente.");
                    $createdCount++;
                    
                    // Seed this table instantly if needed
                    self::seedTable($pdo, $tableName);
                } else {
                    // Table exists - check if all fields exist inside it
                    $colsQuery = $pdo->query("DESCRIBE `{$tableName}`");
                    $existingFields = [];
                    while ($col = $colsQuery->fetch()) {
                        $existingFields[] = strtolower($col['Field']);
                    }

                    // Look for missing columns in table definition
                    foreach ($tableData['columns'] as $colName => $colTypeDef) {
                        $lowColName = strtolower($colName);
                        if (!in_array($lowColName, $existingFields)) {
                            // Column missing! Let's write the alter query safely
                            // Handling complex primary/unique declarations out of ALTER if simple
                            if (strpos(strtolower($colTypeDef), 'primary key') !== false) {
                                // Skip or adapt. Usually PK is created on start column, but if exists we skip PK mod
                                $colTypeDefClean = str_ireplace('primary key', '', $colTypeDef);
                                $alterSql = "ALTER TABLE `{$tableName}` ADD COLUMN `{$colName}` {$colTypeDefClean}";
                            } else {
                                $alterSql = "ALTER TABLE `{$tableName}` ADD COLUMN `{$colName}` {$colTypeDef}";
                            }
                            
                            $pdo->exec($alterSql);
                            self::log("WARNING", "Coluna ausente '{$colName}' inserida com sucesso em '{$tableName}'.");
                            $updatedColumnsCount++;
                        }
                    }
                }
            }

            // If changes occurred, write a log summary
            if ($createdCount > 0 || $updatedColumnsCount > 0) {
                self::log("INFO", "Migrações concluídas: {$createdCount} tabelas geradas, {$updatedColumnsCount} colunas expandidas.");
            }

            return true;
        } catch (Exception $e) {
            self::log("ERROR", "Falha na execução de migração inteligente: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Custom Smart Database Seeder
     */
    private static function seedTable($pdo, $tableName) {
        try {
            switch ($tableName) {
                case 'admins':
                    $count = $pdo->query("SELECT COUNT(*) FROM `admins`")->fetchColumn();
                    if ($count == 0) {
                        $hash = '$2y$10$yepN8b5fP4p.p9Y.D.OFeOHfT7d87m0N7d.S6y3Z0zR9tMoV9t.vK'; // 'admin'
                        $stmt = $pdo->prepare("INSERT INTO `admins` (`id`, `username`, `email`, `password_hash`, `role`) VALUES (?, ?, ?, ?, ?)");
                        $stmt->execute([1, 'admin', 'admin@stickerstore.com', $hash, 'master']);
                        self::log("SUCCESS", "Semeador: Administrador master padrão criado (admin / admin).");
                    }
                    break;

                case 'categories':
                    $count = $pdo->query("SELECT COUNT(*) FROM `categories`")->fetchColumn();
                    if ($count == 0) {
                        $cats = [
                            [1, 'Memes', 1, '😂'],
                            [2, 'Love', 2, '💖'],
                            [3, 'Anime', 3, '🌸'],
                            [4, 'Funny', 4, '🤪'],
                            [5, 'Animals', 5, '🐱'],
                            [6, 'Gaming', 6, '🎮']
                        ];
                        $stmt = $pdo->prepare("INSERT INTO `categories` (`id`, `name`, `order_index`, `icon_emoji`) VALUES (?, ?, ?, ?)");
                        foreach ($cats as $cat) {
                            $stmt->execute($cat);
                        }
                        self::log("SUCCESS", "Semeador: 6 categorias iniciais adicionadas.");
                    }
                    break;

                case 'app_config':
                    $count = $pdo->query("SELECT COUNT(*) FROM `app_config`")->fetchColumn();
                    if ($count == 0) {
                        $configs = [
                            'app_name' => 'Sticker Store Premium',
                            'logo_url' => 'ic_logo.xml',
                            'primary_color' => '#6200EE',
                            'languages_enabled' => 'pt-BR,en-US,es-ES',
                            'policy_url' => 'https://mystickerstore.com/privacy',
                            'terms_url' => 'https://mystickerstore.com/terms',
                            'share_template' => 'Confira as melhores figurinhas para WhatsApp! Baixe agora: [LINK]',
                            'share_url' => 'https://mystickerstore.page.link/download',
                            'privacy_policy' => "Nós da Sticker Store priorizamos a sua privacidade. Este aplicativo não coleta informações de uso de figurinhas de forma individualizada. As figurinhas adicionadas do WhatsApp residem no próprio dispositivo.\n\nPolítica de Privacidade atualizada em Maio de 2026.",
                            'terms_of_service' => "Ao utilizar nosso aplicativo, você se compromete a não carregar materiais ofensivos, violentos, odiosos ou protegidos por copyrights.",
                            'firebase_project_id' => 'sticker-store-fcm-fb',
                            'firebase_api_key' => 'AIzaSyAs762AksLid889Xbca9NqP8Z-xK1-q',
                            'firebase_server_key' => 'AAAA8Y90-uE:APA91bHmX-3F9x98aC7z3XkaL8_sHk7_KjG_Z_sMv3d7890N_uS7j_K8dKl_k8M7k3s_f910-UiaLp_8B7m7n8V9aX8M7J9p_L-910aM-p89aM_uS8n',
                            'firebase_messaging_sender_id' => '128394857612',
                            'firebase_app_id' => '1:128394857612:android:9d8a8c8b7b6b5a4a'
                        ];
                        $stmt = $pdo->prepare("INSERT INTO `app_config` (`key_name`, `val_value`) VALUES (?, ?)");
                        foreach ($configs as $k => $v) {
                            $stmt->execute([$k, $v]);
                        }
                        self::log("SUCCESS", "Semeador: Configurações do app inicializadas.");
                    }
                    break;

                case 'ads_config':
                    $count = $pdo->query("SELECT COUNT(*) FROM `ads_config`")->fetchColumn();
                    if ($count == 0) {
                        $ads = [
                            [1, 'Banner', 'ca-app-pub-3940256099942544/6300978111', 'ca-app-pub-3940256099942544/6300978111', 1],
                            [2, 'Interstitial', 'ca-app-pub-3940256099942544/1033173712', 'ca-app-pub-3940256099942544/1033173712', 1],
                            [3, 'Rewarded', 'ca-app-pub-3940256099942544/5224354917', 'ca-app-pub-3940256099942544/5224354917', 1],
                            [4, 'Rewarded Interstitial', 'ca-app-pub-3940256099942544/5354046379', 'ca-app-pub-3940256099942544/5354046379', 1]
                        ];
                        $stmt = $pdo->prepare("INSERT INTO `ads_config` (`id`, `ad_type`, `unit_id_test`, `unit_id_prod`, `is_active`) VALUES (?, ?, ?, ?, ?)");
                        foreach ($ads as $ad) {
                            $stmt->execute($ad);
                        }
                        self::log("SUCCESS", "Semeador: 4 canais de anúncios AdMob adicionados.");
                    }
                    break;

                case 'app_versions':
                    $count = $pdo->query("SELECT COUNT(*) FROM `app_versions`")->fetchColumn();
                    if ($count == 0) {
                        $pdo->exec("INSERT INTO `app_versions` (`platform`, `version_code`, `version_name`, `is_force_update`, `download_url`, `update_notes`) VALUES
                        ('Android', 15, '2.1.2', 1, 'https://play.google.com/store/apps/details?id=com.aistudio.stickerstore.stkwa', 'Novos pacotes exclusivos de criadores e compatibilidade total com Android 14+!'),
                        ('iOS', 12, '1.8.1', 0, 'https://apps.apple.com/app', 'Melhoria na exportação de figurinhas em alta definição.');");
                        self::log("SUCCESS", "Semeador: Versões padrão de aplicativos inseridas.");
                    }
                    break;

                case 'sticker_packs':
                    $count = $pdo->query("SELECT COUNT(*) FROM `sticker_packs`")->fetchColumn();
                    if ($count == 0) {
                        $pdo->exec("INSERT INTO `sticker_packs` (`id`, `name`, `creator`, `category_id`, `cover_url`, `is_premium`, `is_exclusive`, `downloads_count`, `likes_count`) VALUES
                        (1, 'Cyber Gatos', 'NeonMochi', 5, 'https://lh3.googleusercontent.com/aida-public/AB6AXuCtkSf5UeSaJUridQTq5N4vmoVsP0gexrgiYTdP0qRlbEs30ew908UhCNEi9m4LssAhXwVOg57u1e_ez-5g3TCOLjGUqA_-27lK6b3gjBo5fwN-xvC_bKUNHrOkf3ISxAXnFmmx8mWdAdYoIk0HajgPSXMuRMLYN6Jh-eRtFtqy4r1QWUPvDtru9uh3AoAcbANQjuMITfhzZ5Rkxqa6gWxcRXhsv9GA-oJYENfKNAiEofeImH6pZ-f27B90qgHjsVEMaAFiBSJb5Tg', 1, 0, 1250, 482),
                        (2, 'Retro Vibes', 'Synthwave_Artist', 6, 'https://lh3.googleusercontent.com/aida-public/AB6AXuDSlXNY5xsvukq1cD3BhF6kpieE4o3vgOZk8YylLlfBhEquaGujYIHv4QQ9rAub4A1DRMFUzHjM5xKHdC4OOWlgHdsfpPqOtx19A8WQpiXCfpQib90cmaTUY9Sx-DoPGsbRmU5dJHDGht4bQiF6BQJx_DnuOAOJ0QvkzKsheFIsaS6MRg__iO_2QRhKn_Eo8Yl1PaD_58ALnKDr2xow6qNf4XwSrYsK3L95amcKQHj92dp8jVKK-v13i9K328iNg706PgT3oGpFGg0', 0, 0, 950, 128);
                        
                        $pdo->exec("INSERT INTO `stickers` (`id`, `pack_id`, `image_url`, `content_description`, `order_index`) VALUES
                        (1, 1, 'https://lh3.googleusercontent.com/aida-public/AB6AXuCtkSf5UeSaJUridQTq5N4vmoVsP0gexrgiYTdP0qRlbEs30ew908UhCNEi9m4LssAhXwVOg57u1e_ez-5g3TCOLjGUqA_-27lK6b3gjBo5fwN-xvC_bKUNHrOkf3ISxAXnFmmx8mWdAdYoIk0HajgPSXMuRMLYN6Jh-eRtFtqy4r1QWUPvDtru9uh3AoAcbANQjuMITfhzZ5Rkxqa6gWxcRXhsv9GA-oJYENfKNAiEofeImH6pZ-f27B90qgHjsVEMaAFiBSJb5Tg', 'Futuristic blue neon cat', 1),
                        (2, 1, 'https://lh3.googleusercontent.com/aida-public/AB6AXuA3wfyyh4fYujNiui8ykRW7sThV2EscYZZpuXEFUI3NbIeojw_q5XIjhtJdEBbvtm3fXWgkX4UrUX1db3BoaTMLIPTva7bXJyYDpMYL7t9XPotyDJ73vhvtYxh4TTsCtHbjFwB0iHI9Z3iMQfYO3eDIFPJpWzNE6RIj54lAQanMU5km61D3nyzx6c88sHI7KXZXyTjQAcnhhZ4NQTlnqxYzhetVswujz_XcYvqXC3brpw24bx52M5QPqVMlWJq2E6CwiI5cCH3-hjI', 'Robot cat with digital hearts', 2);
                        self::log("SUCCESS", "Semeador: Pacotes e figurinhas iniciais inseridos com sucesso.");
                    }
                    break;
                case 'slides':
                    $count = $pdo->query("SELECT COUNT(*) FROM `slides`")->fetchColumn();
                    if ($count == 0) {
                        $pdo->exec("INSERT INTO `slides` (`id`, `title`, `image_url`, `redirect_url`, `order_index`, `is_active`) VALUES
                        (1, 'Especial Gatos Cibernéticos! ⭐', 'https://lh3.googleusercontent.com/aida-public/AB6AXuCtkSf5UeSaJUridQTq5N4vmoVsP0gexrgiYTdP0qRlbEs30ew908UhCNEi9m4LssAhXwVOg57u1e_ez-5g3TCOLjGUqA_-27lK6b3gjBo5fwN-xvC_bKUNHrOkf3ISxAXnFmmx8mWdAdYoIk0HajgPSXMuRMLYN6Jh-eRtFtqy4r1QWUPvDtru9uh3AoAcbANQjuMITfhzZ5Rkxqa6gWxcRXhsv9GA-oJYENfKNAiEofeImH6pZ-f27B90qgHjsVEMaAFiBSJb5Tg', 'pack:1', 1, 1),
                        (2, 'Synthwave Retrô Pack', 'https://lh3.googleusercontent.com/aida-public/AB6AXuDSlXNY5xsvukq1cD3BhF6kpieE4o3vgOZk8YylLlfBhEquaGujYIHv4QQ9rAub4A1DRMFUzHjM5xKHdC4OOWlgHdsfpPqOtx19A8WQpiXCfpQib90cmaTUY9Sx-DoPGsbRmU5dJHDGht4bQiF6BQJx_DnuOAOJ0QvkzKsheFIsaS6MRg__iO_2QRhKn_Eo8Yl1PaD_58ALnKDr2xow6qNf4XwSrYsK3L95amcKQHj92dp8jVKK-v13i9K328iNg706PgT3oGpFGg0', 'pack:2', 2, 1);");
                        self::log("SUCCESS", "Semeador: Slides e banners de destaque em exibição.");
                    }
                    break;
                case 'tags':
                    $count = $pdo->query("SELECT COUNT(*) FROM `tags`")->fetchColumn();
                    if ($count == 0) {
                        $pdo->exec("INSERT INTO `tags` (`tag_name`) VALUES ('#memes'), ('#love'), ('#anime'), ('#games'), ('#gatos');");
                        self::log("SUCCESS", "Semeador: Tags de busca padrão indexadas.");
                    }
                    break;
            }
        } catch (Exception $e) {
            self::log("ERROR", "Erro ao semear tabela '{$tableName}': " . $e->getMessage());
        }
    }

    /**
     * Set up directories and permissions automatically
     */
    private static function initDirectories() {
        if (!file_exists(self::$logDir)) {
            mkdir(self::$logDir, 0755, true);
        }
        if (!file_exists(self::$uploadDir)) {
            mkdir(self::$uploadDir, 0755, true);
            // Write simple index.html inside uploads to prevent direct directory browsing
            file_put_contents(self::$uploadDir . '/index.html', 'Access denied.');
            self::log("INFO", "Diretório de uploads criado de forma segura e automática.");
        }
    }

    /**
     * Unified dynamic system Logger
     */
    public static function log($level, $message) {
        if (!file_exists(self::$logDir)) {
            mkdir(self::$logDir, 0755, true);
        }
        $ip = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : 'CLI';
        $uri = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : 'N/A';
        $timestamp = date("Y-m-d H:i:s");
        $logLine = "[{$timestamp}] [{$level}] [IP: {$ip}] [URI: {$uri}] - {$message}" . PHP_EOL;
        
        // Write securely
        @file_put_contents(self::$logFile, $logLine, FILE_APPEND);
    }

    /**
     * Clear system log
     */
    public static function clearLogs() {
        if (file_exists(self::$logFile)) {
            @unlink(self::$logFile);
            self::log("INFO", "Histórico de logs do sistema limpo com sucesso pelo administrador.");
            return true;
        }
        return false;
    }

    /**
     * Get system schema modeling matching schema.sql inside memory space
     */
    private static function getSchemaModels() {
        return [
            'admins' => [
                'sql' => "CREATE TABLE `admins` (
                    `id` INT AUTO_INCREMENT PRIMARY KEY,
                    `username` VARCHAR(50) NOT NULL UNIQUE,
                    `email` VARCHAR(100) NOT NULL UNIQUE,
                    `password_hash` VARCHAR(255) NOT NULL,
                    `role` VARCHAR(20) DEFAULT 'editor',
                    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    `last_login` TIMESTAMP NULL DEFAULT NULL
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",
                'columns' => [
                    'id' => "INT AUTO_INCREMENT PRIMARY KEY",
                    'username' => "VARCHAR(50) NOT NULL UNIQUE",
                    'email' => "VARCHAR(100) NOT NULL UNIQUE",
                    'password_hash' => "VARCHAR(255) NOT NULL",
                    'role' => "VARCHAR(20) DEFAULT 'editor'",
                    'created_at' => "TIMESTAMP DEFAULT CURRENT_TIMESTAMP",
                    'last_login' => "TIMESTAMP NULL DEFAULT NULL"
                ]
            ],
            'users' => [
                'sql' => "CREATE TABLE `users` (
                    `id` INT AUTO_INCREMENT PRIMARY KEY,
                    `name` VARCHAR(100) NOT NULL,
                    `email` VARCHAR(100) NOT NULL UNIQUE,
                    `password_hash` VARCHAR(255) NOT NULL,
                    `avatar` VARCHAR(255) DEFAULT NULL,
                    `is_premium` TINYINT(1) DEFAULT 0,
                    `is_blocked` TINYINT(1) DEFAULT 0,
                    `device_token` VARCHAR(255) DEFAULT NULL,
                    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",
                'columns' => [
                    'id' => "INT AUTO_INCREMENT PRIMARY KEY",
                    'name' => "VARCHAR(100) NOT NULL",
                    'email' => "VARCHAR(100) NOT NULL UNIQUE",
                    'password_hash' => "VARCHAR(255) NOT NULL",
                    'avatar' => "VARCHAR(255) DEFAULT NULL",
                    'is_premium' => "TINYINT(1) DEFAULT 0",
                    'is_blocked' => "TINYINT(1) DEFAULT 0",
                    'device_token' => "VARCHAR(255) DEFAULT NULL",
                    'created_at' => "TIMESTAMP DEFAULT CURRENT_TIMESTAMP",
                    'updated_at' => "TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP"
                ]
            ],
            'categories' => [
                'sql' => "CREATE TABLE `categories` (
                    `id` INT AUTO_INCREMENT PRIMARY KEY,
                    `name` VARCHAR(50) NOT NULL UNIQUE,
                    `order_index` INT DEFAULT 0,
                    `icon_emoji` VARCHAR(20) DEFAULT '✨',
                    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",
                'columns' => [
                    'id' => "INT AUTO_INCREMENT PRIMARY KEY",
                    'name' => "VARCHAR(50) NOT NULL UNIQUE",
                    'order_index' => "INT DEFAULT 0",
                    'icon_emoji' => "VARCHAR(20) DEFAULT '✨'",
                    'created_at' => "TIMESTAMP DEFAULT CURRENT_TIMESTAMP"
                ]
            ],
            'sticker_packs' => [
                'sql' => "CREATE TABLE `sticker_packs` (
                    `id` INT AUTO_INCREMENT PRIMARY KEY,
                    `name` VARCHAR(100) NOT NULL,
                    `creator` VARCHAR(100) NOT NULL,
                    `category_id` INT,
                    `cover_url` VARCHAR(255) NOT NULL,
                    `is_premium` TINYINT(1) DEFAULT 0,
                    `is_exclusive` TINYINT(1) DEFAULT 0,
                    `downloads_count` INT DEFAULT 0,
                    `likes_count` INT DEFAULT 0,
                    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",
                'columns' => [
                    'id' => "INT AUTO_INCREMENT PRIMARY KEY",
                    'name' => "VARCHAR(100) NOT NULL",
                    'creator' => "VARCHAR(100) NOT NULL",
                    'category_id' => "INT NULL",
                    'cover_url' => "VARCHAR(255) NOT NULL",
                    'is_premium' => "TINYINT(1) DEFAULT 0",
                    'is_exclusive' => "TINYINT(1) DEFAULT 0",
                    'downloads_count' => "INT DEFAULT 0",
                    'likes_count' => "INT DEFAULT 0",
                    'created_at' => "TIMESTAMP DEFAULT CURRENT_TIMESTAMP"
                ]
            ],
            'stickers' => [
                'sql' => "CREATE TABLE `stickers` (
                    `id` INT AUTO_INCREMENT PRIMARY KEY,
                    `pack_id` INT NOT NULL,
                    `image_url` VARCHAR(255) NOT NULL,
                    `content_description` VARCHAR(255) DEFAULT '',
                    `order_index` INT DEFAULT 0,
                    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",
                'columns' => [
                    'id' => "INT AUTO_INCREMENT PRIMARY KEY",
                    'pack_id' => "INT NOT NULL",
                    'image_url' => "VARCHAR(255) NOT NULL",
                    'content_description' => "VARCHAR(255) DEFAULT ''",
                    'order_index' => "INT DEFAULT 0",
                    'created_at' => "TIMESTAMP DEFAULT CURRENT_TIMESTAMP"
                ]
            ],
            'favorites' => [
                'sql' => "CREATE TABLE `favorites` (
                    `id` INT AUTO_INCREMENT PRIMARY KEY,
                    `user_id` INT NOT NULL,
                    `pack_id` INT NOT NULL,
                    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",
                'columns' => [
                    'id' => "INT AUTO_INCREMENT PRIMARY KEY",
                    'user_id' => "INT NOT NULL",
                    'pack_id' => "INT NOT NULL",
                    'created_at' => "TIMESTAMP DEFAULT CURRENT_TIMESTAMP"
                ]
            ],
            'downloads' => [
                'sql' => "CREATE TABLE `downloads` (
                    `id` BIGINT AUTO_INCREMENT PRIMARY KEY,
                    `user_id` INT NULL,
                    `pack_id` INT NOT NULL,
                    `downloaded_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",
                'columns' => [
                    'id' => "BIGINT AUTO_INCREMENT PRIMARY KEY",
                    'user_id' => "INT NULL",
                    'pack_id' => "INT NOT NULL",
                    'downloaded_at' => "TIMESTAMP DEFAULT CURRENT_TIMESTAMP"
                ]
            ],
            'notifications' => [
                'sql' => "CREATE TABLE `notifications` (
                    `id` INT AUTO_INCREMENT PRIMARY KEY,
                    `title` VARCHAR(150) NOT NULL,
                    `message` TEXT NOT NULL,
                    `target_category` VARCHAR(50) DEFAULT 'all',
                    `sent_at` TIMESTAMP NULL DEFAULT NULL,
                    `scheduled_at` TIMESTAMP NULL DEFAULT NULL,
                    `status` VARCHAR(20) DEFAULT 'pending'
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",
                'columns' => [
                    'id' => "INT AUTO_INCREMENT PRIMARY KEY",
                    'title' => "VARCHAR(150) NOT NULL",
                    'message' => "TEXT NOT NULL",
                    'target_category' => "VARCHAR(50) DEFAULT 'all'",
                    'sent_at' => "TIMESTAMP NULL DEFAULT NULL",
                    'scheduled_at' => "TIMESTAMP NULL DEFAULT NULL",
                    'status' => "VARCHAR(20) DEFAULT 'pending'"
                ]
            ],
            'app_config' => [
                'sql' => "CREATE TABLE `app_config` (
                    `key_name` VARCHAR(50) PRIMARY KEY,
                    `val_value` TEXT NOT NULL
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",
                'columns' => [
                    'key_name' => "VARCHAR(50) PRIMARY KEY",
                    'val_value' => "TEXT NOT NULL"
                ]
            ],
            'ads_config' => [
                'sql' => "CREATE TABLE `ads_config` (
                    `id` INT AUTO_INCREMENT PRIMARY KEY,
                    `ad_type` VARCHAR(50) NOT NULL,
                    `unit_id_test` VARCHAR(150) NOT NULL,
                    `unit_id_prod` VARCHAR(150) NOT NULL,
                    `is_active` TINYINT(1) DEFAULT 1
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",
                'columns' => [
                    'id' => "INT AUTO_INCREMENT PRIMARY KEY",
                    'ad_type' => "VARCHAR(50) NOT NULL",
                    'unit_id_test' => "VARCHAR(150) NOT NULL",
                    'unit_id_prod' => "VARCHAR(150) NOT NULL",
                    'is_active' => "TINYINT(1) DEFAULT 1"
                ]
            ],
            'slides' => [
                'sql' => "CREATE TABLE `slides` (
                    `id` INT AUTO_INCREMENT PRIMARY KEY,
                    `title` VARCHAR(150) NOT NULL,
                    `image_url` VARCHAR(255) NOT NULL,
                    `redirect_url` VARCHAR(255) NULL,
                    `order_index` INT DEFAULT 0,
                    `is_active` TINYINT(1) DEFAULT 1,
                    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",
                'columns' => [
                    'id' => "INT AUTO_INCREMENT PRIMARY KEY",
                    'title' => "VARCHAR(150) NOT NULL",
                    'image_url' => "VARCHAR(255) NOT NULL",
                    'redirect_url' => "VARCHAR(255) NULL",
                    'order_index' => "INT DEFAULT 0",
                    'is_active' => "TINYINT(1) DEFAULT 1",
                    'created_at' => "TIMESTAMP DEFAULT CURRENT_TIMESTAMP"
                ]
            ],
            'app_versions' => [
                'sql' => "CREATE TABLE `app_versions` (
                    `id` INT AUTO_INCREMENT PRIMARY KEY,
                    `platform` VARCHAR(50) NOT NULL UNIQUE,
                    `version_code` INT NOT NULL,
                    `version_name` VARCHAR(50) NOT NULL,
                    `is_force_update` TINYINT(1) DEFAULT 0,
                    `download_url` VARCHAR(255) NOT NULL,
                    `update_notes` TEXT NULL,
                    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",
                'columns' => [
                    'id' => "INT AUTO_INCREMENT PRIMARY KEY",
                    'platform' => "VARCHAR(50) NOT NULL UNIQUE",
                    'version_code' => "INT NOT NULL",
                    'version_name' => "VARCHAR(50) NOT NULL",
                    'is_force_update' => "TINYINT(1) DEFAULT 0",
                    'download_url' => "VARCHAR(255) NOT NULL",
                    'update_notes' => "TEXT NULL",
                    'updated_at' => "TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP"
                ]
            ],
            'support_messages' => [
                'sql' => "CREATE TABLE `support_messages` (
                    `id` INT AUTO_INCREMENT PRIMARY KEY,
                    `sender_name` VARCHAR(100) NOT NULL,
                    `sender_email` VARCHAR(100) NOT NULL,
                    `subject` VARCHAR(150) NOT NULL,
                    `message` TEXT NOT NULL,
                    `reply_text` TEXT NULL,
                    `status` VARCHAR(20) DEFAULT 'pending', 
                    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",
                'columns' => [
                    'id' => "INT AUTO_INCREMENT PRIMARY KEY",
                    'sender_name' => "VARCHAR(100) NOT NULL",
                    'sender_email' => "VARCHAR(100) NOT NULL",
                    'subject' => "VARCHAR(150) NOT NULL",
                    'message' => "TEXT NOT NULL",
                    'reply_text' => "TEXT NULL",
                    'status' => "VARCHAR(20) DEFAULT 'pending'",
                    'created_at' => "TIMESTAMP DEFAULT CURRENT_TIMESTAMP"
                ]
            ],
            'tags' => [
                'sql' => "CREATE TABLE `tags` (
                    `id` INT AUTO_INCREMENT PRIMARY KEY,
                    `tag_name` VARCHAR(50) NOT NULL UNIQUE,
                    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",
                'columns' => [
                    'id' => "INT AUTO_INCREMENT PRIMARY KEY",
                    'tag_name' => "VARCHAR(50) NOT NULL UNIQUE",
                    'created_at' => "TIMESTAMP DEFAULT CURRENT_TIMESTAMP"
                ]
            ],
            'reports' => [
                'sql' => "CREATE TABLE `reports` (
                    `id` INT AUTO_INCREMENT PRIMARY KEY,
                    `reporter_email` VARCHAR(100) NOT NULL,
                    `pack_id` INT NOT NULL,
                    `reason` TEXT NOT NULL,
                    `status` VARCHAR(20) DEFAULT 'pending',
                    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",
                'columns' => [
                    'id' => "INT AUTO_INCREMENT PRIMARY KEY",
                    'reporter_email' => "VARCHAR(100) NOT NULL",
                    'pack_id' => "INT NOT NULL",
                    'reason' => "TEXT NOT NULL",
                    'status' => "VARCHAR(20) DEFAULT 'pending'",
                    'created_at' => "TIMESTAMP DEFAULT CURRENT_TIMESTAMP"
                ]
            ],
            'share_links' => [
                'sql' => "CREATE TABLE `share_links` (
                    `id` INT AUTO_INCREMENT PRIMARY KEY,
                    `title` VARCHAR(100) NOT NULL,
                    `original_url` VARCHAR(255) NOT NULL,
                    `short_code` VARCHAR(50) NOT NULL UNIQUE,
                    `clicks_count` INT DEFAULT 0,
                    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",
                'columns' => [
                    'id' => "INT AUTO_INCREMENT PRIMARY KEY",
                    'title' => "VARCHAR(100) NOT NULL",
                    'original_url' => "VARCHAR(255) NOT NULL",
                    'short_code' => "VARCHAR(50) NOT NULL UNIQUE",
                    'clicks_count' => "INT DEFAULT 0",
                    'created_at' => "TIMESTAMP DEFAULT CURRENT_TIMESTAMP"
                ]
            ]
        ];
    }
}
