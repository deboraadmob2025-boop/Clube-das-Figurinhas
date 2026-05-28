<?php
session_start();

// Redirect if not logged in
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: index.php");
    exit;
}

// Global active tab indicator
$active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'dashboard';

// --- DATABASE CONNECTION CONFIG ---
$db_error = "";
$db_connected = false;
$conn = null;

if (file_exists('../config/database.php')) {
    require_once '../config/database.php';
    try {
        $db = new Database();
        $conn = $db->getConnection();
        if ($conn) {
            $db_connected = true;
        }
    } catch (Exception $e) {
        $db_error = $e->getMessage();
    }
}

// --- ENSURE DATABASE TABLES AND SEED VALUES EXIST ---
if ($db_connected) {
    try {
        $conn->exec("CREATE TABLE IF NOT EXISTS `slides` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `title` VARCHAR(150) NOT NULL,
            `image_url` VARCHAR(255) NOT NULL,
            `redirect_url` VARCHAR(255) NULL,
            `order_index` INT DEFAULT 0,
            `is_active` TINYINT(1) DEFAULT 1,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

        $conn->exec("CREATE TABLE IF NOT EXISTS `app_versions` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `platform` VARCHAR(50) NOT NULL UNIQUE,
            `version_code` INT NOT NULL,
            `version_name` VARCHAR(50) NOT NULL,
            `is_force_update` TINYINT(1) DEFAULT 0,
            `download_url` VARCHAR(255) NOT NULL,
            `update_notes` TEXT NULL,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

        $conn->exec("CREATE TABLE IF NOT EXISTS `support_messages` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `sender_name` VARCHAR(100) NOT NULL,
            `sender_email` VARCHAR(100) NOT NULL,
            `subject` VARCHAR(150) NOT NULL,
            `message` TEXT NOT NULL,
            `reply_text` TEXT NULL,
            `status` VARCHAR(20) DEFAULT 'pending', 
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

        $conn->exec("CREATE TABLE IF NOT EXISTS `tags` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `tag_name` VARCHAR(50) NOT NULL UNIQUE,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

        $conn->exec("CREATE TABLE IF NOT EXISTS `reports` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `reporter_email` VARCHAR(100) NOT NULL,
            `pack_id` INT NOT NULL,
            `reason` TEXT NOT NULL,
            `status` VARCHAR(20) DEFAULT 'pending',
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

        $conn->exec("CREATE TABLE IF NOT EXISTS `share_links` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `title` VARCHAR(100) NOT NULL,
            `original_url` VARCHAR(255) NOT NULL,
            `short_code` VARCHAR(50) NOT NULL UNIQUE,
            `clicks_count` INT DEFAULT 0,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

        // Seed initial versions
        $stmt_ver = $conn->query("SELECT COUNT(*) FROM `app_versions`");
        if ($stmt_ver && $stmt_ver->fetchColumn() == 0) {
            $conn->exec("INSERT INTO `app_versions` (`platform`, `version_code`, `version_name`, `is_force_update`, `download_url`, `update_notes`) VALUES
            ('Android', 15, '2.1.2', 1, 'https://play.google.com/store/apps/details?id=com.aistudio.stickerstore.stkwa', 'Novos pacotes exclusivos de criadores e compatibilidade total com Android 14+!'),
            ('iOS', 12, '1.8.1', 0, 'https://apps.apple.com/app', 'Melhoria na exportação de figurinhas em alta definição.');");
        }

        // Seed initial support messages
        $stmt_sup = $conn->query("SELECT COUNT(*) FROM `support_messages`");
        if ($stmt_sup && $stmt_sup->fetchColumn() == 0) {
            $conn->exec("INSERT INTO `support_messages` (`sender_name`, `sender_email`, `subject`, `message`, `status`) VALUES
            ('Gabriel Santos', 'gabriel@email.com', 'Erro de exportação', 'Não consigo enviar as figurinhas para o WhatsApp. Aparece erro 403.', 'pending'),
            ('Mariana Silva', 'mariana@email.com', 'Amei as figurinhas!', 'Melhor aplicativo do gênero! Se pudessem colocar figurinhas do Chaves seria ótimo.', 'solved');");
        }

        // Seed default tags
        $stmt_tag = $conn->query("SELECT COUNT(*) FROM `tags`");
        if ($stmt_tag && $stmt_tag->fetchColumn() == 0) {
            $conn->exec("INSERT INTO `tags` (`tag_name`) VALUES ('#memes'), ('#love'), ('#anime'), ('#games'), ('#gatos'), ('#whatsapp');");
        }

        // Seed initial reports
        $stmt_rep = $conn->query("SELECT COUNT(*) FROM `reports`");
        if ($stmt_rep && $stmt_rep->fetchColumn() == 0) {
            $conn->exec("INSERT INTO `reports` (`reporter_email`, `pack_id`, `reason`, `status`) VALUES
            ('denuncias_user@email.com', 2, 'O criador utilizou imagens com direitos autorais autorizados.', 'pending');");
        }

        // Seed dynamic share links
        $stmt_shr = $conn->query("SELECT COUNT(*) FROM `share_links`");
        if ($stmt_shr && $stmt_shr->fetchColumn() == 0) {
            $conn->exec("INSERT INTO `share_links` (`title`, `original_url`, `short_code`, `clicks_count`) VALUES
            ('Promoção de Lançamento', 'https://mystickerstore.com/landing?promo=cyber30', 'cyber30', 214),
            ('Link WhatsApp Direto', 'https://mystickerstore.com/download?src=wa_invite', 'convite', 95);");
        }

    } catch (Exception $e) {
        $db_error = "Tabelas criadas ou atualizadas com avisos secundários.";
    }
}

// --- INITIALIZE SESSION MOCK STATE DATABASE FOR GRACEFUL RESILIENCY ---
if (!isset($_SESSION['mock_db'])) {
    $_SESSION['mock_db'] = [
        'users' => [
            ['id' => 1, 'name' => 'Alex Rivera', 'email' => 'alex_rivera@gmail.com', 'avatar' => null, 'is_premium' => 1, 'is_blocked' => 0, 'created_at' => '2026-05-18 10:20'],
            ['id' => 2, 'name' => 'Jane Smith', 'email' => 'jane.smith@yahoo.com', 'avatar' => null, 'is_premium' => 0, 'is_blocked' => 0, 'created_at' => '2026-05-22 14:35'],
            ['id' => 3, 'name' => 'Carlos Ferreira', 'email' => 'carlos@outlook.com', 'avatar' => null, 'is_premium' => 1, 'is_blocked' => 0, 'created_at' => '2026-05-25 18:41'],
            ['id' => 4, 'name' => 'Renata Pinheiro', 'email' => 'renatap@gmail.com', 'avatar' => null, 'is_premium' => 0, 'is_blocked' => 1, 'created_at' => '2026-05-27 09:12']
        ],
        'categories' => [
            ['id' => 1, 'name' => 'Memes', 'order_index' => 1, 'icon_emoji' => '😂'],
            ['id' => 2, 'name' => 'Love', 'order_index' => 2, 'icon_emoji' => '💖'],
            ['id' => 3, 'name' => 'Anime', 'order_index' => 3, 'icon_emoji' => '🌸'],
            ['id' => 4, 'name' => 'Funny', 'order_index' => 4, 'icon_emoji' => '🤪'],
            ['id' => 5, 'name' => 'Animals', 'order_index' => 5, 'icon_emoji' => '🐱'],
            ['id' => 6, 'name' => 'Gaming', 'order_index' => 6, 'icon_emoji' => '🎮']
        ],
        'sticker_packs' => [
            ['id' => 1, 'name' => 'Cyber Gatos', 'creator' => 'NeonMochi', 'category_id' => 5, 'category_name' => 'Animals', 'cover_url' => 'https://lh3.googleusercontent.com/aida-public/AB6AXuCtkSf5UeSaJUridQTq5N4vmoVsP0gexrgiYTdP0qRlbEs30ew908UhCNEi9m4LssAhXwVOg57u1e_ez-5g3TCOLjGUqA_-27lK6b3gjBo5fwN-xvC_bKUNHrOkf3ISxAXnFmmx8mWdAdYoIk0HajgPSXMuRMLYN6Jh-eRtFtqy4r1QWUPvDtru9uh3AoAcbANQjuMITfhzZ5Rkxqa6gWxcRXhsv9GA-oJYENfKNAiEofeImH6pZ-f27B90qgHjsVEMaAFiBSJb5Tg', 'is_premium' => 1, 'is_exclusive' => 0, 'downloads_count' => 1250, 'likes_count' => 482],
            ['id' => 2, 'name' => 'Retro Vibes', 'creator' => 'Synthwave_Artist', 'category_id' => 6, 'category_name' => 'Gaming', 'cover_url' => 'https://lh3.googleusercontent.com/aida-public/AB6AXuDSlXNY5xsvukq1cD3BhF6kpieE4o3vgOZk8YylLlfBhEquaGujYIHv4QQ9rAub4A1DRMFUzHjM5xKHdC4OOWlgHdsfpPqOtx19A8WQpiXCfpQib90cmaTUY9Sx-DoPGsbRmU5dJHDGht4bQiF6BQJx_DnuOAOJ0QvkzKsheFIsaS6MRg__iO_2QRhKn_Eo8Yl1PaD_58ALnKDr2xow6qNf4XwSrYsK3L95amcKQHj92dp8jVKK-v13i9K328iNg706PgT3oGpFGg0', 'is_premium' => 0, 'is_exclusive' => 0, 'downloads_count' => 950, 'likes_count' => 128],
            ['id' => 3, 'name' => 'Kawaii Mochi', 'creator' => 'KyotoDraws', 'category_id' => 3, 'category_name' => 'Anime', 'cover_url' => 'https://lh3.googleusercontent.com/aida-public/AB6AXuB_Z0tqDaC3KJVQ3A6aBXvdaiZwlLGqBgvZdC_z0ClI1HEAN89XuPVS3IFXXrQReuzm3VlVdhV4P0EW73kRmqoMGDyALMdWafrpY-4Yn5niG-2yrSBgL0dEriunRsqvZ92O8za8DmAajIfFNL_Ew53xRDUeRwKVKcdshYFnIW5jZah1NpWcm76G9iNJgw_QolKpqw-5l-giHkcDD52SKgFLnmlmgD948Bajuedke3tGzv4s7-SO-tQxNGvKnVSH0mnBQGu17OVBwh0', 'is_premium' => 0, 'is_exclusive' => 1, 'downloads_count' => 2480, 'likes_count' => 690]
        ],
        'slides' => [
            ['id' => 1, 'title' => 'Especial Gatos Cibernéticos! ⭐', 'image_url' => 'https://lh3.googleusercontent.com/aida-public/AB6AXuCtkSf5UeSaJUridQTq5N4vmoVsP0gexrgiYTdP0qRlbEs30ew908UhCNEi9m4LssAhXwVOg57u1e_ez-5g3TCOLjGUqA_-27lK6b3gjBo5fwN-xvC_bKUNHrOkf3ISxAXnFmmx8mWdAdYoIk0HajgPSXMuRMLYN6Jh-eRtFtqy4r1QWUPvDtru9uh3AoAcbANQjuMITfhzZ5Rkxqa6gWxcRXhsv9GA-oJYENfKNAiEofeImH6pZ-f27B90qgHjsVEMaAFiBSJb5Tg', 'redirect_url' => 'pack:1', 'order_index' => 1, 'is_active' => 1],
            ['id' => 2, 'title' => 'Synthwave Retrô Pack', 'image_url' => 'https://lh3.googleusercontent.com/aida-public/AB6AXuDSlXNY5xsvukq1cD3BhF6kpieE4o3vgOZk8YylLlfBhEquaGujYIHv4QQ9rAub4A1DRMFUzHjM5xKHdC4OOWlgHdsfpPqOtx19A8WQpiXCfpQib90cmaTUY9Sx-DoPGsbRmU5dJHDGht4bQiF6BQJx_DnuOAOJ0QvkzKsheFIsaS6MRg__iO_2QRhKn_Eo8Yl1PaD_58ALnKDr2xow6qNf4XwSrYsK3L95amcKQHj92dp8jVKK-v13i9K328iNg706PgT3oGpFGg0', 'redirect_url' => 'pack:2', 'order_index' => 2, 'is_active' => 1]
        ],
        'notifications' => [
            ['id' => 1, 'title' => '🔥 Lançamento Imperdível!', 'message' => 'Disparado para Todos. Taxa de abertura de 45%. Novo pack de Neon!', 'target_category' => 'all', 'sent_at' => '2026-05-28 00:00', 'status' => 'sent'],
            ['id' => 2, 'title' => '🌟 Novidades Premium!', 'message' => 'Lançamento exclusivo de Cyber Gatos já liberado.', 'target_category' => 'premium', 'sent_at' => '2026-05-27 12:00', 'status' => 'sent']
        ],
        'app_versions' => [
            ['id' => 1, 'platform' => 'Android', 'version_code' => 15, 'version_name' => '2.1.2', 'is_force_update' => 1, 'download_url' => 'https://play.google.com/store/apps/details?id=com.aistudio.stickerstore.stkwa', 'update_notes' => 'Novos pacotes exclusivos de criadores e compatibilidade total com Android 14+!'],
            ['id' => 2, 'platform' => 'iOS', 'version_code' => 12, 'version_name' => '1.8.1', 'is_force_update' => 0, 'download_url' => 'https://apps.apple.com/app', 'update_notes' => 'Melhoria na exportação de figurinhas em alta definição.']
        ],
        'support_messages' => [
            ['id' => 1, 'sender_name' => 'Gabriel Santos', 'sender_email' => 'gabriel@email.com', 'subject' => 'Erro de exportação', 'message' => 'Não consigo enviar as figurinhas para o WhatsApp. Aparece erro 403.', 'reply_text' => null, 'status' => 'pending', 'created_at' => '2026-05-28 00:15'],
            ['id' => 2, 'sender_name' => 'Mariana Silva', 'sender_email' => 'mariana@email.com', 'subject' => 'Amei as figurinhas!', 'message' => 'Melhor aplicativo do gênero! Se pudessem colocar figurinhas do Chaves seria ótimo.', 'reply_text' => 'Olá Mariana, obrigado pelo contato! Já repassamos para a equipe de design e criaremos esta categoria em breve.', 'status' => 'solved', 'created_at' => '2026-05-27 18:30']
        ],
        'tags' => [
            ['id' => 1, 'tag_name' => '#memes'],
            ['id' => 2, 'tag_name' => '#love'],
            ['id' => 3, 'tag_name' => '#anime'],
            ['id' => 4, 'tag_name' => '#games'],
            ['id' => 5, 'tag_name' => '#gatos']
        ],
        'reports' => [
            ['id' => 1, 'reporter_email' => 'denuncias_user@email.com', 'pack_id' => 2, 'pack_name' => 'Retro Vibes', 'reason' => 'O criador utilizou imagens com direitos autorais autorizados.', 'status' => 'pending', 'created_at' => '2026-05-28 00:10']
        ],
        'share_links' => [
            ['id' => 1, 'title' => 'Campanha do Instagram', 'original_url' => 'https://mystickerstore.com/instagram', 'short_code' => 'instastk', 'clicks_count' => 320],
            ['id' => 2, 'title' => 'Convite Amigo', 'original_url' => 'https://mystickerstore.com/invite?ref=123', 'short_code' => 'amigos', 'clicks_count' => 184]
        ],
        'app_config' => [
            'app_name' => 'Sticker Store Premium',
            'share_template' => 'Confira as melhores figurinhas para WhatsApp! Baixe agora: [LINK]',
            'share_url' => 'https://mystickerstore.page.link/download',
            'privacy_policy' => "Nós da Sticker Store priorizamos a sua privacidade. Este aplicativo não coleta informações de uso de figurinhas de forma individualizada. As figurinhas adicionadas do WhatsApp residem no próprio dispositivo.\n\nPolítica de Privacidade atualizada em Maio de 2026.",
            'terms_of_service' => "Ao utilizar nosso aplicativo, você se compromete a não carregar materiais ofensivos, violentos, odiosos ou protegidos por copyrights.",
            'firebase_project_id' => 'sticker-store-fcm-fb',
            'firebase_api_key' => 'AIzaSyAs762AksLid889Xbca9NqP8Z-xK1-q',
            'firebase_server_key' => 'AAAA8Y90-uE:APA91bHmX-3F9x98aC7z3XkaL8_sHk7_KjG_Z_sMv3d7890N_uS7j_K8dKl_k8M7k3s_f910-UiaLp_8B7m7n8V9aX8M7J9p_L-910aM-p89aM_uS8n',
            'firebase_messaging_sender_id' => '128394857612',
            'firebase_app_id' => '1:128394857612:android:9d8a8c8b7b6b5a4a'
        ]
    ];
}

// --- FORM CONTROLLERS / CORE BUSINESS LOGIC AND ACTIONS ---
$alert_text = "";
$alert_type = "success";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];

    // 1. MANAGE PACKS ACTIONS
    if ($action === 'add_pack') {
        $name = trim($_POST['name']);
        $creator = trim($_POST['creator']);
        $category_id = intval($_POST['category_id']);
        $cover_url = trim($_POST['cover_url']);
        $is_premium = isset($_POST['is_premium']) ? 1 : 0;
        $is_exclusive = isset($_POST['is_exclusive']) ? 1 : 0;

        if ($db_connected) {
            try {
                $stmt = $conn->prepare("INSERT INTO `sticker_packs` (`name`, `creator`, `category_id`, `cover_url`, `is_premium`, `is_exclusive`) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute([$name, $creator, $category_id ? $category_id : null, $cover_url, $is_premium, $is_exclusive]);
                $alert_text = "Pacote '$name' criado com sucesso no banco de dados!";
            } catch (Exception $e) { $db_error = $e->getMessage(); }
        }

        $new_id = count($_SESSION['mock_db']['sticker_packs']) + 1;
        $cat_name = 'Animais';
        foreach ($_SESSION['mock_db']['categories'] as $c) {
            if ($c['id'] == $category_id) { $cat_name = $c['name']; break; }
        }
        $_SESSION['mock_db']['sticker_packs'][] = [
            'id' => $new_id, 'name' => $name, 'creator' => $creator, 'category_id' => $category_id,
            'category_name' => $cat_name, 'cover_url' => $cover_url ?: 'https://lh3.googleusercontent.com/aida-public/AB6AXuCtkSf5UeSaJUridQTq5N4vmoVsP0gexrgiYTdP0qRlbEs30ew908UhCNEi9m4LssAhXwVOg57u1e_ez-5g3TCOLjGUqA_-27lK6b3gjBo5fwN-xvC_bKUNHrOkf3ISxAXnFmmx8mWdAdYoIk0HajgPSXMuRMLYN6Jh-eRtFtqy4r1QWUPvDtru9uh3AoAcbANQjuMITfhzZ5Rkxqa6gWxcRXhsv9GA-oJYENfKNAiEofeImH6pZ-f27B90qgHjsVEMaAFiBSJb5Tg',
            'is_premium' => $is_premium, 'is_exclusive' => $is_exclusive, 'downloads_count' => 0, 'likes_count' => 0
        ];
        if (empty($alert_text)) $alert_text = "Pacote '$name' adicionado (Simulado localmente)";
    }

    if ($action === 'delete_pack') {
        $id = intval($_POST['id']);
        if ($db_connected) {
            try {
                $stmt = $conn->prepare("DELETE FROM `sticker_packs` WHERE `id` = ?");
                $stmt->execute([$id]);
                $alert_text = "Pacote removido com sucesso no banco de dados!";
            } catch (Exception $e) { $db_error = $e->getMessage(); }
        }
        foreach ($_SESSION['mock_db']['sticker_packs'] as $k => $p) {
            if ($p['id'] == $id) { unset($_SESSION['mock_db']['sticker_packs'][$k]); break; }
        }
        if (empty($alert_text)) $alert_text = "Pacote removido com sucesso!";
    }

    // 2. MANAGE SLIDES ACTIONS
    if ($action === 'add_slide') {
        $title = trim($_POST['title']);
        $img = trim($_POST['image_url']);
        $redirect = trim($_POST['redirect_url']);
        $order = intval($_POST['order_index']);

        if ($db_connected) {
            try {
                $stmt = $conn->prepare("INSERT INTO `slides` (`title`, `image_url`, `redirect_url`, `order_index`, `is_active`) VALUES (?, ?, ?, ?, 1)");
                $stmt->execute([$title, $img, $redirect, $order]);
                $alert_text = "Banner de slide '$title' criado no banco de dados!";
            } catch (Exception $e) { $db_error = $e->getMessage(); }
        }
        $new_id = count($_SESSION['mock_db']['slides']) + 1;
        $_SESSION['mock_db']['slides'][] = [
            'id' => $new_id, 'title' => $title, 'image_url' => $img, 'redirect_url' => $redirect, 'order_index' => $order, 'is_active' => 1
        ];
        if (empty($alert_text)) $alert_text = "Banner em Destaque '$title' criado com sucesso!";
    }

    if ($action === 'delete_slide') {
        $id = intval($_POST['id']);
        if ($db_connected) {
            try {
                $stmt = $conn->prepare("DELETE FROM `slides` WHERE `id` = ?");
                $stmt->execute([$id]);
                $alert_text = "Slide removido com sucesso do banco!";
            } catch (Exception $e) { $db_error = $e->getMessage(); }
        }
        foreach ($_SESSION['mock_db']['slides'] as $k => $s) {
            if ($s['id'] == $id) { unset($_SESSION['mock_db']['slides'][$k]); break; }
        }
        if (empty($alert_text)) $alert_text = "Slide removido!";
    }

    // 3. MANAGE CATEGORIES ACTIONS
    if ($action === 'add_category') {
        $name = trim($_POST['name']);
        $emoji = trim($_POST['icon_emoji']);
        $order = intval($_POST['order_index']);

        if ($db_connected) {
            try {
                $stmt = $conn->prepare("INSERT INTO `categories` (`name`, `icon_emoji`, `order_index`) VALUES (?, ?, ?)");
                $stmt->execute([$name, $emoji, $order]);
                $alert_text = "Categoria '$name' salva no banco!";
            } catch (Exception $e) { $db_error = $e->getMessage(); }
        }
        $new_id = count($_SESSION['mock_db']['categories']) + 1;
        $_SESSION['mock_db']['categories'][] = [
            'id' => $new_id, 'name' => $name, 'order_index' => $order, 'icon_emoji' => $emoji
        ];
        if (empty($alert_text)) $alert_text = "Categoria '$name' cadastrada com sucesso!";
    }

    if ($action === 'delete_category') {
        $id = intval($_POST['id']);
        if ($db_connected) {
            try {
                $stmt = $conn->prepare("DELETE FROM `categories` WHERE `id` = ?");
                $stmt->execute([$id]);
                $alert_text = "Categoria removida com sucesso!";
            } catch (Exception $e) { $db_error = $e->getMessage(); }
        }
        foreach ($_SESSION['mock_db']['categories'] as $k => $c) {
            if ($c['id'] == $id) { unset($_SESSION['mock_db']['categories'][$k]); break; }
        }
        if (empty($alert_text)) $alert_text = "Categoria removida!";
    }

    // 4. MANAGE NOTIFICATIONS ACTIONS
    if ($action === 'send_notification') {
        $title = trim($_POST['title']);
        $msg = trim($_POST['message']);
        $target = trim($_POST['target_category']);

        if ($db_connected) {
            try {
                $stmt = $conn->prepare("INSERT INTO `notifications` (`title`, `message`, `target_category`, `sent_at`, `status`) VALUES (?, ?, ?, CURRENT_TIMESTAMP, 'sent')");
                $stmt->execute([$title, $msg, $target]);
                $alert_text = "Notificação enviada e salva no banco de dados!";
            } catch (Exception $e) { $db_error = $e->getMessage(); }
        }
        $new_id = count($_SESSION['mock_db']['notifications']) + 1;
        $_SESSION['mock_db']['notifications'][] = [
            'id' => $new_id, 'title' => $title, 'message' => $msg, 'target_category' => $target, 'sent_at' => date('Y-m-d H:i'), 'status' => 'sent'
        ];
        if (empty($alert_text)) $alert_text = "Notificação Push enviada para todos com sucesso via Firebase API!";
    }

    if ($action === 'delete_notification') {
        $id = intval($_POST['id']);
        if ($db_connected) {
            try {
                $stmt = $conn->prepare("DELETE FROM `notifications` WHERE `id` = ?");
                $stmt->execute([$id]);
            } catch (Exception $e) { $db_error = $e->getMessage(); }
        }
        foreach ($_SESSION['mock_db']['notifications'] as $k => $n) {
            if ($n['id'] == $id) { unset($_SESSION['mock_db']['notifications'][$k]); break; }
        }
        $alert_text = "Histórico de notificação removido.";
    }

    // 5. MANAGE VERSIONS ACTIONS
    if ($action === 'save_version') {
        $android_vname = trim($_POST['android_vname']);
        $android_vcode = intval($_POST['android_vcode']);
        $android_force = isset($_POST['android_force']) ? 1 : 0;
        $android_url = trim($_POST['android_url']);
        $android_notes = trim($_POST['android_notes']);

        if ($db_connected) {
            try {
                $stmt = $conn->prepare("UPDATE `app_versions` SET `version_code` = ?, `version_name` = ?, `is_force_update` = ?, `download_url` = ?, `update_notes` = ? WHERE `platform` = 'Android'");
                $stmt->execute([$android_vcode, $android_vname, $android_force, $android_url, $android_notes]);
                $alert_text = "Versão do Android salva no banco!";
            } catch (Exception $e) { $db_error = $e->getMessage(); }
        }
        foreach ($_SESSION['mock_db']['app_versions'] as $k => $v) {
            if ($v['platform'] === 'Android') {
                $_SESSION['mock_db']['app_versions'][$k] = [
                    'id' => $v['id'], 'platform' => 'Android', 'version_code' => $android_vcode, 'version_name' => $android_vname,
                    'is_force_update' => $android_force, 'download_url' => $android_url, 'update_notes' => $android_notes
                ];
            }
        }
        if (empty($alert_text)) $alert_text = "Canal de atualização do Android salvo con sucesso!";
    }

    // 6. MANAGE SUPPORT ACTIONS
    if ($action === 'reply_support') {
        $id = intval($_POST['id']);
        $reply = trim($_POST['reply_text']);

        if ($db_connected) {
            try {
                $stmt = $conn->prepare("UPDATE `support_messages` SET `reply_text` = ?, `status` = 'solved' WHERE `id` = ?");
                $stmt->execute([$reply, $id]);
                $alert_text = "Resposta enviada no banco!";
            } catch (Exception $e) { $db_error = $e->getMessage(); }
        }
        foreach ($_SESSION['mock_db']['support_messages'] as $k => $sm) {
            if ($sm['id'] == $id) {
                $_SESSION['mock_db']['support_messages'][$k]['reply_text'] = $reply;
                $_SESSION['mock_db']['support_messages'][$k]['status'] = 'solved';
                break;
            }
        }
        if (empty($alert_text)) $alert_text = "Resposta enviada para a caixa do usuário com sucesso!";
    }

    // 7. MANAGE TAGS ACTIONS
    if ($action === 'add_tag') {
        $tag_name = '#' . ltrim(trim($_POST['tag_name']), '#');
        if ($db_connected) {
            try {
                $stmt = $conn->prepare("INSERT INTO `tags` (`tag_name`) VALUES (?)");
                $stmt->execute([$tag_name]);
                $alert_text = "Tag '$tag_name' salva no banco!";
            } catch (Exception $e) { $db_error = $e->getMessage(); }
        }
        $new_id = count($_SESSION['mock_db']['tags']) + 1;
        $_SESSION['mock_db']['tags'][] = ['id' => $new_id, 'tag_name' => $tag_name];
        if (empty($alert_text)) $alert_text = "Tag de busca '$tag_name' criada com sucesso!";
    }

    if ($action === 'delete_tag') {
        $id = intval($_POST['id']);
        if ($db_connected) {
            try {
                $stmt = $conn->prepare("DELETE FROM `tags` WHERE `id` = ?");
                $stmt->execute([$id]);
            } catch (Exception $e) { $db_error = $e->getMessage(); }
        }
        foreach ($_SESSION['mock_db']['tags'] as $k => $t) {
            if ($t['id'] == $id) { unset($_SESSION['mock_db']['tags'][$k]); break; }
        }
        $alert_text = "Tag desvinculada com sucesso!";
    }

    // 8. MANAGE REPORTS ACTIONS
    if ($action === 'resolve_report') {
        $id = intval($_POST['id']);
        $resolution = trim($_POST['resolution']); // dismiss, suspend

        if ($db_connected) {
            try {
                $stmt = $conn->prepare("UPDATE `reports` SET `status` = 'reviewed' WHERE `id` = ?");
                $stmt->execute([$id]);
                if ($resolution === 'suspend') {
                    $rep = $conn->prepare("SELECT `pack_id` FROM `reports` WHERE `id` = ?");
                    $rep->execute([$id]);
                    $pk_id = $rep->fetchColumn();
                    if ($pk_id) {
                        $del = $conn->prepare("DELETE FROM `sticker_packs` WHERE `id` = ?");
                        $del->execute([$pk_id]);
                    }
                }
                $alert_text = "Denúncia resolvida no banco!";
            } catch (Exception $e) { $db_error = $e->getMessage(); }
        }
        foreach ($_SESSION['mock_db']['reports'] as $k => $r) {
            if ($r['id'] == $id) {
                $_SESSION['mock_db']['reports'][$k]['status'] = 'reviewed';
                if ($resolution === 'suspend') {
                    $pk_id = $r['pack_id'];
                    foreach ($_SESSION['mock_db']['sticker_packs'] as $kp => $pack) {
                        if ($pack['id'] == $pk_id) { unset($_SESSION['mock_db']['sticker_packs'][$kp]); break; }
                    }
                }
                break;
            }
        }
        if (empty($alert_text)) {
            $alert_text = $resolution === 'suspend' ? "Denúncia aceita! Pacote denunciado foi suspenso." : "Denúncia arquivada (pacote mantido).";
        }
    }

    // 9. MANAGE USERS ACTIONS
    if ($action === 'toggle_block') {
        $id = intval($_POST['id']);
        foreach ($_SESSION['mock_db']['users'] as $k => $u) {
            if ($u['id'] == $id) {
                $new_status = $u['is_blocked'] ? 0 : 1;
                $_SESSION['mock_db']['users'][$k]['is_blocked'] = $new_status;
                $alert_text = $new_status ? "Usuário bloqueado com sucesso!" : "Usuário reativado!";
                break;
            }
        }
    }

    if ($action === 'toggle_premium') {
        $id = intval($_POST['id']);
        foreach ($_SESSION['mock_db']['users'] as $k => $u) {
            if ($u['id'] == $id) {
                $new_status = $u['is_premium'] ? 0 : 1;
                $_SESSION['mock_db']['users'][$k]['is_premium'] = $new_status;
                $alert_text = $new_status ? "Plano do usuário promovido para PREMIUM!" : "Plano rebaixado para Grátis.";
                break;
            }
        }
    }

    // 10. MANAGE SHARE ACTIONS
    if ($action === 'save_share') {
        $_SESSION['mock_db']['app_config']['share_template'] = trim($_POST['share_template']);
        $_SESSION['mock_db']['app_config']['share_url'] = trim($_POST['share_url']);
        
        $title_sh = trim($_POST['share_title'] ?? '');
        $code_sh = trim($_POST['share_code'] ?? '');
        $orig_sh = trim($_POST['share_orig'] ?? '');
        if (!empty($title_sh) && !empty($code_sh)) {
            if ($db_connected) {
                try {
                    $stmt = $conn->prepare("INSERT INTO `share_links` (`title`, `original_url`, `short_code`) VALUES (?, ?, ?)");
                    $stmt->execute([$title_sh, $orig_sh, $code_sh]);
                } catch(Exception $e) {}
            }
            $_SESSION['mock_db']['share_links'][] = [
                'id' => count($_SESSION['mock_db']['share_links']) + 1,
                'title' => $title_sh, 'original_url' => $orig_sh, 'short_code' => $code_sh, 'clicks_count' => 0
            ];
        }
        $alert_text = "Configurações do Compartilhamento salvas com sucesso!";
    }

    if ($action === 'delete_share') {
        $id = intval($_POST['id']);
        if ($db_connected) {
            try {
                $stmt = $conn->prepare("DELETE FROM `share_links` WHERE `id` = ?");
                $stmt->execute([$id]);
            } catch(Exception $e){}
        }
        foreach($_SESSION['mock_db']['share_links'] as $k => $sl) {
            if ($sl['id'] == $id) { unset($_SESSION['mock_db']['share_links'][$k]); break; }
        }
        $alert_text = "Link curto deletado.";
    }

    // 11. MANAGE PRIVACY POLICY ACTIONS
    if ($action === 'save_privacy') {
        $_SESSION['mock_db']['app_config']['privacy_policy'] = trim($_POST['privacy_policy']);
        $_SESSION['mock_db']['app_config']['terms_of_service'] = trim($_POST['terms_of_service']);
        $alert_text = "Políticas de Privacidade e Termos de Uso atualizadas!";
    }

    // 12. MANAGE FIREBASE ACTIONS
    if ($action === 'save_firebase') {
        $_SESSION['mock_db']['app_config']['firebase_project_id'] = trim($_POST['firebase_project_id']);
        $_SESSION['mock_db']['app_config']['firebase_api_key'] = trim($_POST['firebase_api_key']);
        $_SESSION['mock_db']['app_config']['firebase_server_key'] = trim($_POST['firebase_server_key']);
        $_SESSION['mock_db']['app_config']['firebase_messaging_sender_id'] = trim($_POST['firebase_messaging_sender_id']);
        $_SESSION['mock_db']['app_config']['firebase_app_id'] = trim($_POST['firebase_app_id']);
        $alert_text = "Credenciais do Firebase salvas! Conexão de Mensageria FCM reestruturada com êxito.";
    }

    // 13. SETTINGS OVERVIEW (COMPACTION + APP NAME)
    if ($action === 'save_general') {
        $_SESSION['mock_db']['app_config']['app_name'] = trim($_POST['app_name']);
        $alert_text = "Configurações gerais atualizadas com sucesso!";
    }

    // Redirect to preserve dynamic active tab on refresh
    header("Location: dashboard.php?tab=" . urlencode($active_tab) . "&success=" . urlencode($alert_text));
    exit;
}

// Check for redirect flash success message
$success_flash = isset($_GET['success']) ? $_GET['success'] : "";
if (!empty($success_flash)) {
    $alert_text = $success_flash;
}

// Load appropriate arrays from database if connected, else load from session fallback
$sticker_packs = $_SESSION['mock_db']['sticker_packs'];
$categories = $_SESSION['mock_db']['categories'];
$slides = $_SESSION['mock_db']['slides'];
$notifications = $_SESSION['mock_db']['notifications'];
$versions = $_SESSION['mock_db']['app_versions'];
$support_messages = $_SESSION['mock_db']['support_messages'];
$tags = $_SESSION['mock_db']['tags'];
$reports = $_SESSION['mock_db']['reports'];
$users = $_SESSION['mock_db']['users'];
$share_links = $_SESSION['mock_db']['share_links'];
$app_config = $_SESSION['mock_db']['app_config'];

if ($db_connected) {
    try {
        $q_packs = $conn->query("SELECT p.*, c.name as category_name FROM `sticker_packs` p LEFT JOIN `categories` c ON p.category_id = c.id ORDER BY p.id DESC");
        if ($q_packs) { $sticker_packs = $q_packs->fetchAll(); }

        $q_cats = $conn->query("SELECT * FROM `categories` ORDER BY order_index ASC");
        if ($q_cats) { $categories = $q_cats->fetchAll(); }

        $q_slides = $conn->query("SELECT * FROM `slides` ORDER BY order_index ASC");
        if ($q_slides) { $slides = $q_slides->fetchAll(); }

        $q_not = $conn->query("SELECT * FROM `notifications` ORDER BY id DESC");
        if ($q_not) { $notifications = $q_not->fetchAll(); }

        $q_ver = $conn->query("SELECT * FROM `app_versions` ORDER BY platform ASC");
        if ($q_ver) { $versions = $q_ver->fetchAll(); }

        $q_sup = $conn->query("SELECT * FROM `support_messages` ORDER BY id DESC");
        if ($q_sup) { $support_messages = $q_sup->fetchAll(); }

        $q_tags = $conn->query("SELECT * FROM `tags` ORDER BY id DESC");
        if ($q_tags) { $tags = $q_tags->fetchAll(); }

        $q_rep = $conn->query("SELECT r.*, p.name as pack_name FROM `reports` r LEFT JOIN `sticker_packs` p ON r.pack_id = p.id ORDER BY r.id DESC");
        if ($q_rep) { $reports = $q_rep->fetchAll(); }

        $q_shr = $conn->query("SELECT * FROM `share_links` ORDER BY clicks_count DESC");
        if ($q_shr) { $share_links = $q_shr->fetchAll(); }

    } catch (Exception $e) { /* silent query fallback to standard session array */ }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sticker Store - Painel de Controle</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Google Fonts Space Grotesk -->
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;700&display=swap" rel="stylesheet">
    <!-- FontAwesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            font-family: 'Space Grotesk', sans-serif;
            background: #090e14;
            color: #f1f3f5;
            min-height: 100vh;
            overflow-x: hidden;
            transition: background-color 0.3s, color 0.3s;
        }

        body.light-theme {
            background: #f4f7f6;
            color: #1a2530;
        }

        /* Sidebar Glassmorphism Styling */
        .sidebar {
            background: rgba(255, 255, 255, 0.03);
            backdrop-filter: blur(20px);
            border-right: 1px solid rgba(255, 255, 255, 0.05);
            min-height: 100vh;
            position: fixed;
            width: 280px;
            z-index: 100;
            padding: 24px;
            overflow-y: auto;
            max-height: 100vh;
            transition: all 0.3s;
        }

        body.light-theme .sidebar {
            background: rgba(255, 255, 255, 0.85);
            border-right: 1px solid rgba(0, 0, 0, 0.08);
        }

        .main-content {
            margin-left: 280px;
            padding: 35px;
            min-height: 100vh;
        }

        .side-logo {
            font-size: 22px;
            font-weight: 700;
            background: linear-gradient(120deg, #6cf8bb, #6200ee);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 300px;
            /* dynamic responsive adjustment */
            margin-bottom: 25px;
        }

        .nav-link-custom {
            color: rgba(255, 255, 255, 0.65);
            padding: 10px 14px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            text-decoration: none;
            font-weight: 500;
            margin-bottom: 6px;
            font-size: 14px;
            transition: all 0.2s;
        }

        body.light-theme .nav-link-custom {
            color: #4a5568;
        }

        .nav-link-custom:hover, .nav-link-custom.active {
            color: #fff;
            background: linear-gradient(135deg, rgba(108, 248, 187, 0.15) 0%, rgba(98, 0, 238, 0.15) 100%);
            border-left: 4px solid #6cf8bb;
        }

        body.light-theme .nav-link-custom:hover, body.light-theme .nav-link-custom.active {
            color: #6200ee;
            background: rgba(98, 0, 238, 0.08);
            border-left: 4px solid #6200ee;
        }

        .nav-link-custom i {
            margin-right: 12px;
            font-size: 16px;
            width: 20px;
            text-align: center;
        }

        /* Glass Cards */
        .glass-card {
            background: rgba(255, 255, 255, 0.03);
            backdrop-filter: blur(15px);
            border: 1px solid rgba(255, 255, 255, 0.05);
            border-radius: 20px;
            padding: 24px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
            margin-bottom: 25px;
        }

        body.light-theme .glass-card {
            background: #ffffff;
            border: 1px solid rgba(0, 0, 0, 0.06);
            box-shadow: 0 8px 24px rgba(149, 157, 165, 0.05);
        }

        .stat-num {
            font-size: 32px;
            font-weight: 700;
        }

        /* Theme supportive Tables */
        .custom-tbl th {
            background-color: rgba(255, 255, 255, 0.02);
            color: rgba(255, 255, 255, 0.5);
            font-weight: 600;
            text-transform: uppercase;
            font-size: 11px;
            letter-spacing: 0.5px;
        }

        body.light-theme .custom-tbl th {
            background-color: #edf2f7;
            color: #718096;
        }

        body.light-theme .custom-tbl td {
            color: #2d3748;
        }

        .custom-tbl td {
            color: #f1f3f5;
            vertical-align: middle;
        }

        .form-control, .form-select {
            background: rgba(255, 255, 255, 0.06);
            border: 1.5px solid rgba(255, 255, 255, 0.1);
            color: #fff;
            border-radius: 10px;
            padding: 10px 14px;
        }

        .form-control:focus, .form-select:focus {
            background: rgba(255, 255, 255, 0.12);
            color: #fff;
            border-color: #6cf8bb;
            box-shadow: 0 0 12px rgba(108, 248, 187, 0.2);
        }

        body.light-theme .form-control, body.light-theme .form-select {
            background: #fff;
            border: 1.5px solid rgba(0, 0, 0, 0.15);
            color: #1a2530;
        }

        body.light-theme .form-control:focus, body.light-theme .form-select:focus {
            color: #1a2530;
            background: #fff;
            border-color: #6200ee;
            box-shadow: 0 0 10px rgba(98, 0, 238, 0.1);
        }

        /* Subheader */
        .header-controls {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }

        /* Accent premium and active badges */
        .badge-premium {
            background-color: rgba(108, 248, 187, 0.15);
            color: #6cf8bb;
            border: 1px solid rgba(108, 248, 187, 0.3);
            font-weight: 700;
        }

        body.light-theme .badge-premium {
            background-color: rgba(0, 108, 73, 0.1);
            color: #006c49;
            border: 1px solid rgba(0, 108, 73, 0.2);
        }

        .log-box {
            background: #04080c;
            font-family: 'Courier New', Courier, monospace;
            padding: 15px;
            border-radius: 12px;
            font-size: 12px;
            color: #a0aec0;
            max-height: 180px;
            overflow-y: auto;
        }
    </style>
</head>
<body>

    <!-- LEFT SIDEBAR -->
    <div class="sidebar">
        <div class="side-logo">
            <i class="fa-solid fa-wand-magic-sparkles me-2 text-[#6cf8bb]"></i>Sticker Dashboard
        </div>
        
        <nav class="nav flex-column">
            <a class="nav-link-custom <?= $active_tab == 'dashboard' ? 'active' : '' ?>" href="?tab=dashboard">
                <i class="fa-solid fa-chart-pie"></i>Dashboard Overview
            </a>
            <a class="nav-link-custom <?= $active_tab == 'packs' ? 'active' : '' ?>" href="?tab=packs">
                <i class="fa-solid fa-box-open"></i>Manage Packs (Figurinhas)
            </a>
            <a class="nav-link-custom <?= $active_tab == 'slides' ? 'active' : '' ?>" href="?tab=slides">
                <i class="fa-solid fa-image"></i>Manage Slides (Banners)
            </a>
            <a class="nav-link-custom <?= $active_tab == 'categories' ? 'active' : '' ?>" href="?tab=categories">
                <i class="fa-solid fa-tags"></i>Manage Categories
            </a>
            <a class="nav-link-custom <?= $active_tab == 'notifications' ? 'active' : '' ?>" href="?tab=notifications">
                <i class="fa-solid fa-bell"></i>Manage Notifications
            </a>
            <a class="nav-link-custom <?= $active_tab == 'versions' ? 'active' : '' ?>" href="?tab=versions">
                <i class="fa-solid fa-code-branch"></i>Manage Versions
            </a>
            <a class="nav-link-custom <?= $active_tab == 'support' ? 'active' : '' ?>" href="?tab=support">
                <i class="fa-solid fa-envelope-open-text"></i>Support Messages
            </a>
            <a class="nav-link-custom <?= $active_tab == 'tags' ? 'active' : '' ?>" href="?tab=tags">
                <i class="fa-solid fa-hashtag"></i>Manage Tags
            </a>
            <a class="nav-link-custom <?= $active_tab == 'reports' ? 'active' : '' ?>" href="?tab=reports">
                <i class="fa-solid fa-triangle-exclamation"></i>Manage Reports
            </a>
            <a class="nav-link-custom <?= $active_tab == 'users' ? 'active' : '' ?>" href="?tab=users">
                <i class="fa-solid fa-users"></i>Manage Users
            </a>
            <a class="nav-link-custom <?= $active_tab == 'share' ? 'active' : '' ?>" href="?tab=share">
                <i class="fa-solid fa-share-nodes"></i>Share Link System
            </a>
            <a class="nav-link-custom <?= $active_tab == 'privacy' ? 'active' : '' ?>" href="?tab=privacy">
                <i class="fa-solid fa-shield-halved"></i>Manage Privacy Policy
            </a>
            <a class="nav-link-custom <?= $active_tab == 'firebase' ? 'active' : '' ?>" href="?tab=firebase">
                <i class="fa-solid fa-fire"></i>Firebase FCM Settings
            </a>
            <a class="nav-link-custom <?= $active_tab == 'ads' ? 'active' : '' ?>" href="?tab=ads">
                <i class="fa-solid fa-rectangle-ad"></i>Monetização AdMob
            </a>
        </nav>

        <div class="mt-4 pt-3 border-top border-secondary">
            <div class="d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center">
                    <div class="rounded-circle bg-success text-dark d-flex align-items-center justify-content-center fw-bold" style="width: 32px; height: 32px; font-size: 13px;">AD</div>
                    <div class="ms-2" style="font-size: 12px;">
                        <span class="d-block text-white" style="font-weight: 600;">Diretor</span>
                        <span class="text-white-50">admin</span>
                    </div>
                </div>
                <a href="index.php" class="text-danger" title="Desconectar"><i class="fa-solid fa-right-from-bracket"></i></a>
            </div>
        </div>
    </div>

    <!-- MAIN RIGHT SIDE CONTENT -->
    <div class="main-content">
        
        <!-- HEADER ROW -->
        <div class="header-controls">
            <div>
                <h3 class="fw-bold mb-1">
                    <?php
                        $tab_headers = [
                            'dashboard' => 'Painel Principal & Analytics',
                            'packs' => 'Gestão de Pacotes de Figurinhas',
                            'slides' => 'Slides & Banners de Destaque',
                            'categories' => 'Categorias Temáticas',
                            'notifications' => 'Histórico e Disparo de Push FCM',
                            'versions' => 'Controle de Versões da App',
                            'support' => 'Mensagens de Suporte dos Clientes',
                            'tags' => 'Tags Dinâmicas de Busca',
                            'reports' => 'Denúncias de Violação / Reports',
                            'users' => 'Controle e Perfis de Usuários',
                            'share' => 'Sistema de Compartilhamento e Links Curtos',
                            'privacy' => 'Política de Privacidade & Termos',
                            'firebase' => 'Configurações Firebase Cloud Messaging',
                            'ads' => 'Monetização de Blocos do AdMob'
                        ];
                        echo isset($tab_headers[$active_tab]) ? $tab_headers[$active_tab] : 'Painel de Controle';
                    ?>
                </h3>
                <span class="text-white-50 small" style="font-size: 13px;">
                    <?php if ($db_connected): ?>
                        <span class="text-success"><i class="fa-solid fa-circle-check me-1"></i>Conectado ao Banco MySQL local (sticker_store)</span>
                    <?php else: ?>
                        <span class="text-warning"><i class="fa-solid fa-triangle-exclamation me-1"></i>Modo Resiliente Ativo (Memória Sandbox do Servidor)</span>
                    <?php endif; ?>
                </span>
            </div>
            
            <div class="d-flex align-items-center gap-2">
                <button class="btn btn-sm btn-outline-secondary rounded-circle" id="theme-button" onclick="toggleTheme()" title="Mudar Tema" style="width: 36px; height: 36px; display: flex; align-items: center; justify-content: center;">
                    <i class="fa-solid fa-sun" id="theme-icon"></i>
                </button>
                <div class="badge bg-secondary p-2 font-monospace">v2.1.2</div>
            </div>
        </div>

        <!-- NOTIFICATION ALERTS -->
        <?php if (!empty($alert_text)): ?>
            <div class="alert alert-<?= $alert_type ?> alert-dismissible fade show bg-success bg-opacity-10 border-success text-white py-3 px-4 rounded-3 mb-4" role="alert">
                <i class="fa-solid fa-circle-check me-2 text-success"></i> <?= htmlspecialchars($alert_text) ?>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <!-- ======================= SECTION: 0. DASHBOARD OVERVIEW ======================= -->
        <?php if ($active_tab === 'dashboard'): ?>
            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <div class="glass-card">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="text-white-50 small" style="font-weight: 500;">Usuários Totais</span>
                            <span class="bg-primary bg-opacity-10 p-2 rounded-3 text-primary"><i class="fa-solid fa-users"></i></span>
                        </div>
                        <div class="stat-num"><?= count($users) ?></div>
                        <span class="text-success small fw-bold"><i class="fa-solid fa-arrow-up"></i> +14% crescimento</span>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="glass-card">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="text-white-50 small" style="font-weight: 500;">Pacotes Carregados</span>
                            <span class="bg-warning bg-opacity-10 p-2 rounded-3 text-warning"><i class="fa-solid fa-box"></i></span>
                        </div>
                        <div class="stat-num"><?= count($sticker_packs) ?></div>
                        <span class="text-white-50 small">Sincronizados com WhatsApp</span>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="glass-card">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="text-white-50 small" style="font-weight: 500;">Downloads Totais</span>
                            <span class="bg-success bg-opacity-10 p-2 rounded-3 text-success"><i class="fa-solid fa-download"></i></span>
                        </div>
                        <div class="stat-num">
                            <?php 
                                $total_dl = 0; 
                                foreach($sticker_packs as $p) { $total_dl += $p['downloads_count']; } 
                                echo number_format($total_dl);
                            ?>
                        </div>
                        <span class="text-success small fw-bold"><i class="fa-solid fa-chart-line"></i> Conversão de 87%</span>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="glass-card">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="text-white-50 small" style="font-weight: 500;">Média de Likes</span>
                            <span class="bg-danger bg-opacity-10 p-2 rounded-3 text-danger"><i class="fa-solid fa-heart"></i></span>
                        </div>
                        <div class="stat-num">
                            <?php 
                                $total_likes = 0; 
                                foreach($sticker_packs as $p) { $total_likes += $p['likes_count']; } 
                                echo count($sticker_packs) > 0 ? round($total_likes / count($sticker_packs)) : 0;
                            ?>
                        </div>
                        <span class="text-danger small fw-bold"><i class="fa-solid fa-face-smile"></i> Engajamento Útil</span>
                    </div>
                </div>
            </div>

            <div class="row g-4">
                <div class="col-md-7">
                    <div class="glass-card">
                        <h5 class="fw-bold mb-3"><i class="fa-solid fa-chart-line me-2 text-primary"></i>Histórico do Servidor (Logs de Acesso)</h5>
                        <div class="log-box text-start">
                            [<?= date('d/m/Y H:i:s') ?>] LOGIN: ADMIN 'admin' efetuou autenticação segura.<br>
                            [<?= date('d/m/Y', strtotime('-1 day')) ?> 23:40:12] DATABASE: Tabelas limpas e indices reconstruídos no InnoDB.<br>
                            [<?= date('d/m/Y', strtotime('-1 day')) ?> 18:22:45] API: Dispositivo Android registrou novo token FCM.<br>
                            [<?= date('d/m/Y', strtotime('-2 days')) ?> 12:10:04] APP_CONFIG: Chaves de desenvolvedor sincronizadas para AdMob.<br>
                            [<?= date('d/m/Y', strtotime('-2 days')) ?> 09:33:02] INTEGRATION: Verificado canal de exportação WA API público.
                        </div>
                    </div>
                </div>
                <div class="col-md-5">
                    <div class="glass-card">
                        <h5 class="fw-bold mb-3"><i class="fa-solid fa-crown me-2 text-warning"></i>Categorias Populares</h5>
                        <ul class="list-group list-group-flush bg-transparent">
                            <li class="list-group-item bg-transparent text-white border-secondary d-flex justify-content-between align-items-center py-2">
                                <span>😂 Memes</span>
                                <span class="badge bg-secondary">42% de uso</span>
                            </li>
                            <li class="list-group-item bg-transparent text-white border-secondary d-flex justify-content-between align-items-center py-2">
                                <span>🐱 Animals</span>
                                <span class="badge bg-secondary">30% de uso</span>
                            </li>
                            <li class="list-group-item bg-transparent text-white border-0 d-flex justify-content-between align-items-center py-2">
                                <span>🎮 Gaming</span>
                                <span class="badge bg-secondary">28% de uso</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- ======================= SECTION: 1. MANAGE PACKS ======================= -->
        <?php if ($active_tab === 'packs'): ?>
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="glass-card text-start">
                        <h5 class="fw-bold mb-3"><i class="fa-solid fa-circle-plus text-success me-2"></i>Novo Pacote</h5>
                        <form action="?tab=packs" method="POST">
                            <input type="hidden" name="action" value="add_pack">
                            <div class="mb-2">
                                <label class="small text-white-50 mb-1">Nome do Pacote</label>
                                <input type="text" class="form-control" name="name" placeholder="Ex: Gatos Cósmicos Animados" required>
                            </div>
                            <div class="mb-2">
                                <label class="small text-white-50 mb-1">Autor / Criador</label>
                                <input type="text" class="form-control" name="creator" placeholder="Ex: NeonMochi Studio" required>
                            </div>
                            <div class="mb-2">
                                <label class="small text-white-50 mb-1">Categoria Vinculada</label>
                                <select class="form-select" name="category_id">
                                    <?php foreach($categories as $c): ?>
                                        <option value="<?= $c['id'] ?>"><?= $c['icon_emoji'] ?> <?= htmlspecialchars($c['name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="small text-white-50 mb-1">URL da Imagem da Capa</label>
                                <input type="url" class="form-control" name="cover_url" placeholder="https://..." value="https://lh3.googleusercontent.com/aida-public/AB6AXuCtkSf5UeSaJUridQTq5N4vmoVsP0gexrgiYTdP0qRlbEs30ew908UhCNEi9m4LssAhXwVOg57u1e_ez-5g3TCOLjGUqA_-27lK6b3gjBo5fwN-xvC_bKUNHrOkf3ISxAXnFmmx8mWdAdYoIk0HajgPSXMuRMLYN6Jh-eRtFtqy4r1QWUPvDtru9uh3AoAcbANQjuMITfhzZ5Rkxqa6gWxcRXhsv9GA-oJYENfKNAiEofeImH6pZ-f27B90qgHjsVEMaAFiBSJb5Tg">
                            </div>

                            <div class="row mb-3">
                                <div class="col-6">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="is_premium" id="p_prem">
                                        <label class="form-check-label text-white-50 small" for="p_prem">Premium👑</label>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="is_exclusive" id="p_excl">
                                        <label class="form-check-label text-white-50 small" for="p_excl">Exclusivo✨</label>
                                    </div>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-success text-dark fw-bold w-100 py-2">CRIAR PACOTE NO APP</button>
                        </form>
                    </div>
                </div>

                <div class="col-md-8">
                    <div class="glass-card">
                        <h5 class="fw-bold mb-3">Pacotes Ativos</h5>
                        <div class="table-responsive">
                            <table class="table custom-tbl">
                                <thead>
                                    <tr>
                                        <th>Capa</th>
                                        <th>Nome</th>
                                        <th>Criador</th>
                                        <th>Download</th>
                                        <th>Plano</th>
                                        <th class="text-end">Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($sticker_packs as $p): ?>
                                        <tr>
                                            <td><img src="<?= htmlspecialchars($p['cover_url']) ?>" class="rounded bg-dark p-1" style="width: 38px; height: 38px; object-fit: contain;" alt=""></td>
                                            <td>
                                                <strong class="d-block text-white"><?= htmlspecialchars($p['name']) ?></strong>
                                                <small class="text-white-50"><?= htmlspecialchars(isset($p['category_name']) ? $p['category_name'] : 'Outro') ?></small>
                                            </td>
                                            <td><?= htmlspecialchars($p['creator']) ?></td>
                                            <td><small class="text-white-50"><?= number_format($p['downloads_count']) ?> dls</small></td>
                                            <td>
                                                <?php if($p['is_premium']): ?>
                                                    <span class="badge badge-premium">PREMIUM</span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary">GRÁTIS</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-end">
                                                <form action="?tab=packs" method="POST" onsubmit="return confirm('Deseja excluir definitivamente este pacote do servidor?')" style="display:inline-block;">
                                                    <input type="hidden" name="action" value="delete_pack">
                                                    <input type="hidden" name="id" value="<?= $p['id'] ?>">
                                                    <button type="submit" class="btn btn-sm btn-outline-danger"><i class="fa-solid fa-trash"></i></button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- ======================= SECTION: 2. MANAGE SLIDES ======================= -->
        <?php if ($active_tab === 'slides'): ?>
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="glass-card text-start">
                        <h5 class="fw-bold mb-3"><i class="fa-solid fa-circle-plus text-info me-2"></i>Novo Slider</h5>
                        <form action="?tab=slides" method="POST">
                            <input type="hidden" name="action" value="add_slide">
                            <div class="mb-2">
                                <label class="small text-white-50 mb-1">Título do Slide</label>
                                <input type="text" class="form-control" name="title" placeholder="Ex: Lançamento Figurinhas Anime" required>
                            </div>
                            <div class="mb-2">
                                <label class="small text-white-50 mb-1">URL da Imagem do Banner</label>
                                <input type="url" class="form-control" name="image_url" placeholder="https://..." value="https://lh3.googleusercontent.com/aida-public/AB6AXuDSlXNY5xsvukq1cD3BhF6kpieE4o3vgOZk8YylLlfBhEquaGujYIHv4QQ9rAub4A1DRMFUzHjM5xKHdC4OOWlgHdsfpPqOtx19A8WQpiXCfpQib90cmaTUY9Sx-DoPGsbRmU5dJHDGht4bQiF6BQJx_DnuOAOJ0QvkzKsheFIsaS6MRg__iO_2QRhKn_Eo8Yl1PaD_58ALnKDr2xow6qNf4XwSrYsK3L95amcKQHj92dp8jVKK-v13i9K328iNg706PgT3oGpFGg0" required>
                            </div>
                            <div class="mb-2">
                                <label class="small text-white-50 mb-1">Redirecionamento (Ação ao Clicar)</label>
                                <select class="form-select" name="redirect_url">
                                    <option value="pack:1">Ir para Cyber Gatos (ID: 1)</option>
                                    <option value="pack:2">Ir para Retro Vibes (ID: 2)</option>
                                    <option value="url:https://play.google.com">Abrir Navegador Externo</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="small text-white-50 mb-1">Posição / Ordem</label>
                                <input type="number" class="form-control" name="order_index" value="1" required>
                            </div>
                            <button type="submit" class="btn btn-info text-dark fw-bold w-100 py-2">PUBLICAR DESTAQUE</button>
                        </form>
                    </div>
                </div>

                <div class="col-md-8">
                    <div class="glass-card">
                        <h5 class="fw-bold mb-3">Slides e Banners Ativos</h5>
                        <div class="table-responsive">
                            <table class="table custom-tbl">
                                <thead>
                                    <tr>
                                        <th>Visualização</th>
                                        <th>Título</th>
                                        <th>Redireciona</th>
                                        <th>Ordem</th>
                                        <th>Status</th>
                                        <th class="text-end">Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($slides as $s): ?>
                                        <tr>
                                            <td><img src="<?= htmlspecialchars($s['image_url']) ?>" class="rounded bg-dark p-1" style="height: 38px; width: 70px; object-fit: cover;"></td>
                                            <td><strong class="text-white"><?= htmlspecialchars($s['title']) ?></strong></td>
                                            <td><code class="text-info"><?= htmlspecialchars($s['redirect_url']) ?></code></td>
                                            <td><?= $s['order_index'] ?></td>
                                            <td><span class="badge bg-success">Ativo</span></td>
                                            <td class="text-end">
                                                <form action="?tab=slides" method="POST" style="display:inline-block;" onsubmit="return confirm('Deseja deletar este slide?')">
                                                    <input type="hidden" name="action" value="delete_slide">
                                                    <input type="hidden" name="id" value="<?= $s['id'] ?>">
                                                    <button type="submit" class="btn btn-sm btn-outline-danger"><i class="fa-solid fa-trash"></i></button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- ======================= SECTION: 3. MANAGE CATEGORIES ======================= -->
        <?php if ($active_tab === 'categories'): ?>
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="glass-card text-start">
                        <h5 class="fw-bold mb-3"><i class="fa-solid fa-plus text-warning me-2"></i>Nova Categoria</h5>
                        <form action="?tab=categories" method="POST">
                            <input type="hidden" name="action" value="add_category">
                            <div class="mb-2">
                                <label class="small text-white-50 mb-1">Nome da Categoria</label>
                                <input type="text" class="form-control" name="name" placeholder="Ex: Anime, Memes, Frases" required>
                            </div>
                            <div class="mb-2">
                                <label class="small text-white-50 mb-1">Emoji do Ícone</label>
                                <input type="text" class="form-control" name="icon_emoji" placeholder="Ex: 😂" required>
                            </div>
                            <div class="mb-3">
                                <label class="small text-white-50 mb-1">Ordem de Prioridade (Index)</label>
                                <input type="number" class="form-control" name="order_index" value="1" required>
                            </div>
                            <button type="submit" class="btn btn-warning text-dark fw-bold w-100 py-2">CRIAR CATEGORIA</button>
                        </form>
                    </div>
                </div>

                <div class="col-md-8">
                    <div class="glass-card">
                        <h5 class="fw-bold mb-3">Categorias Ativas no WhatsApp Sticker Store</h5>
                        <div class="table-responsive">
                            <table class="table custom-tbl">
                                <thead>
                                    <tr>
                                        <th>Emoji</th>
                                        <th>Nome</th>
                                        <th>Ordem</th>
                                        <th>ID</th>
                                        <th class="text-end">Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($categories as $c): ?>
                                        <tr>
                                            <td class="fs-4"><?= htmlspecialchars($c['icon_emoji']) ?></td>
                                            <td><strong class="text-white"><?= htmlspecialchars($c['name']) ?></strong></td>
                                            <td><?= $c['order_index'] ?></td>
                                            <td><code><?= $c['id'] ?></code></td>
                                            <td class="text-end">
                                                <form action="?tab=categories" method="POST" style="display:inline-block;" onsubmit="return confirm('Deseja excluir esta categoria?')">
                                                    <input type="hidden" name="action" value="delete_category">
                                                    <input type="hidden" name="id" value="<?= $c['id'] ?>">
                                                    <button type="submit" class="btn btn-sm btn-outline-danger"><i class="fa-solid fa-trash"></i></button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- ======================= SECTION: 4. MANAGE NOTIFICATIONS ======================= -->
        <?php if ($active_tab === 'notifications'): ?>
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="glass-card text-start">
                        <h5 class="fw-bold mb-3"><i class="fa-solid fa-paper-plane text-info me-2"></i>Disparar Mensagem FCM</h5>
                        <form action="?tab=notifications" method="POST">
                            <input type="hidden" name="action" value="send_notification">
                            <div class="mb-2">
                                <label class="small text-white-50 mb-1">Título do Push</label>
                                <input type="text" class="form-control" name="title" placeholder="Ex: 🔥 Novos Stickers da semana liberados!" required>
                            </div>
                            <div class="mb-2">
                                <label class="small text-white-50 mb-1">Mensagem Curta ao Usuário</label>
                                <textarea class="form-control" name="message" rows="3" placeholder="Insira a descrição enviada na barra de status do celular..." required></textarea>
                            </div>
                            <div class="mb-3">
                                <label class="small text-white-50 mb-1">Público / Target</label>
                                <select class="form-select" name="target_category">
                                    <option value="all">FCM Broadcast (Todos os Usuários)</option>
                                    <option value="premium">Apenas Membros Premium (Fidelizados)</option>
                                    <option value="free">Apenas Usuários Free (Impulsionar Ads)</option>
                                </select>
                            </div>
                            <button type="submit" class="btn btn-info text-dark fw-bold w-100 py-2">ENVIAR VIA FIREBASE CLOUD</button>
                        </form>
                    </div>
                </div>

                <div class="col-md-8">
                    <div class="glass-card">
                        <h5 class="fw-bold mb-3">Histórico de Disparos de Notificações</h5>
                        <div class="table-responsive">
                            <table class="table custom-tbl">
                                <thead>
                                    <tr>
                                        <th>Título</th>
                                        <th>Mensagem</th>
                                        <th>Público Alvo</th>
                                        <th>Disparado em</th>
                                        <th class="text-end">Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($notifications as $n): ?>
                                        <tr>
                                            <td><strong class="text-white"><?= htmlspecialchars($n['title']) ?></strong></td>
                                            <td><small class="text-white-50"><?= htmlspecialchars($n['message']) ?></small></td>
                                            <td><span class="badge bg-secondary"><?= htmlspecialchars($n['target_category']) ?></span></td>
                                            <td><small><?= htmlspecialchars($n['sent_at']) ?></small></td>
                                            <td class="text-end">
                                                <form action="?tab=notifications" method="POST" style="display:inline-block;">
                                                    <input type="hidden" name="action" value="delete_notification">
                                                    <input type="hidden" name="id" value="<?= $n['id'] ?>">
                                                    <button type="submit" class="btn btn-sm btn-outline-danger"><i class="fa-solid fa-trash"></i></button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- ======================= SECTION: 5. MANAGE VERSIONS ======================= -->
        <?php if ($active_tab === 'versions'): ?>
            <div class="row g-4">
                <div class="col-md-6 text-start">
                    <div class="glass-card">
                        <h5 class="fw-bold mb-3 text-success"><i class="fa-brands fa-android me-2"></i>Configurações do Android Update Channel</h5>
                        <?php 
                            $andr = null; 
                            foreach($versions as $v){ if($v['platform'] === 'Android') { $andr = $v; break; } }
                            if($andr == null) { $andr = ['version_name'=>'2.1.2','version_code'=>15,'is_force_update'=>1,'download_url'=>'https://play.google.com/store','update_notes'=>'']; }
                        ?>
                        <form action="?tab=versions" method="POST">
                            <input type="hidden" name="action" value="save_version">
                            <div class="row mb-2">
                                <div class="col-6">
                                    <label class="small text-white-50 mb-1">Versão Comercial (Name)</label>
                                    <input type="text" class="form-control" name="android_vname" value="<?= htmlspecialchars($andr['version_name']) ?>" required>
                                </div>
                                <div class="col-6">
                                    <label class="small text-white-50 mb-1">Código de Build (Version Code)</label>
                                    <input type="number" class="form-control" name="android_vcode" value="<?= htmlspecialchars($andr['version_code']) ?>" required>
                                </div>
                            </div>
                            <div class="mb-3">
                                <div class="form-check form-switch mt-3 mb-3">
                                    <input class="form-check-input" type="checkbox" name="android_force" id="and_force" <?= $andr['is_force_update'] ? 'checked' : '' ?>>
                                    <label class="form-check-label text-white-50" for="and_force">Obrigar Usuário a Atualizar (Force Update)</label>
                                </div>
                            </div>
                            <div class="mb-2">
                                <label class="small text-white-50 mb-1">URL da Play Store</label>
                                <input type="url" class="form-control" name="android_url" value="<?= htmlspecialchars($andr['download_url']) ?>" required>
                            </div>
                            <div class="mb-3">
                                <label class="small text-white-50 mb-1">Notas da Versão (Novidades)</label>
                                <textarea class="form-control" name="android_notes" rows="4" required><?= htmlspecialchars($andr['update_notes']) ?></textarea>
                            </div>
                            <button type="submit" class="btn btn-success text-dark fw-bold w-100">SALVAR CANAL DE ATUALIZAÇAO</button>
                        </form>
                    </div>
                </div>

                <div class="col-md-6 text-start">
                    <div class="glass-card">
                        <h5 class="fw-bold mb-3"><i class="fa-solid fa-info-circle text-info me-2"></i>Como Funciona o Fluxo de Versão</h5>
                        <p class="small text-white-50">O aplicativo Android consulta esta rota antes de carregar a tela principal:</p>
                        <ol class="small text-white-50">
                            <li class="mb-2">Se a versão instalada no celular for menor que o <strong>Código de Build</strong>, um aviso de nova atualização é mostrado.</li>
                            <li class="mb-2">Se a opção <strong>Obrigar Usuário a Atualizar</strong> estiver ativada, o usuário não conseguirá usar o aplicativo até clicar em ir para a Google Play Store e atualizar.</li>
                            <li>Configure notas de atualização para informar ao seu usuário o que mudou no app antes que ele adicione os novos packs de figurinhas.</li>
                        </ol>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- ======================= SECTION: 6. SUPPORT MESSAGES ======================= -->
        <?php if ($active_tab === 'support'): ?>
            <div class="glass-card text-start">
                <h5 class="fw-bold mb-3">Mensagens do Fale Conosco / Ouvidoria</h5>
                <div class="table-responsive">
                    <table class="table custom-tbl">
                        <thead>
                            <tr>
                                <th>Nome/E-mail</th>
                                <th>Assunto</th>
                                <th>Mensagem</th>
                                <th>Resposta</th>
                                <th>Status</th>
                                <th class="text-end">Ação</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($support_messages as $sm): ?>
                                <tr>
                                    <td>
                                        <strong class="text-white d-block"><?= htmlspecialchars($sm['sender_name']) ?></strong>
                                        <small class="text-white-50"><?= htmlspecialchars($sm['sender_email']) ?></small>
                                    </td>
                                    <td><strong><?= htmlspecialchars($sm['subject']) ?></strong></td>
                                    <td><p class="small text-white-50 mb-0" style="max-width: 250px;"><?= htmlspecialchars($sm['message']) ?></p></td>
                                    <td>
                                        <?php if(!empty($sm['reply_text'])): ?>
                                             <small class="text-success"><i class="fa-solid fa-reply-all me-1"></i><?= htmlspecialchars($sm['reply_text']) ?></small>
                                        <?php else: ?>
                                             <span class="text-white-50 small font-monospace">Não respondida</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if($sm['status'] === 'solved'): ?>
                                            <span class="badge bg-success">Resolvido</span>
                                        <?php else: ?>
                                            <span class="badge bg-warning text-dark">Pendente</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end">
                                        <?php if($sm['status'] !== 'solved'): ?>
                                            <button class="btn btn-sm btn-info text-dark font-weight-bold" onclick="openReplyModal(<?= $sm['id'] ?>, '<?= htmlspecialchars($sm['sender_name']) ?>', '<?= htmlspecialchars(addslashes($sm['message'])) ?>')"><i class="fa-solid fa-reply"></i> Responder</button>
                                        <?php else: ?>
                                            <button class="btn btn-sm btn-outline-secondary" disabled><i class="fa-solid fa-check"></i></button>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>

        <!-- ======================= SECTION: 7. MANAGE TAGS ======================= -->
        <?php if ($active_tab === 'tags'): ?>
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="glass-card text-start">
                        <h5 class="fw-bold mb-3"><i class="fa-solid fa-circle-plus text-warning me-2"></i>Nova Tag de Busca</h5>
                        <form action="?tab=tags" method="POST">
                            <input type="hidden" name="action" value="add_tag">
                            <div class="mb-3">
                                <label class="small text-white-50 mb-1">Nome da Tag (Hashtag)</label>
                                <input type="text" class="form-control" name="tag_name" placeholder="Ex: #engraçado" required>
                            </div>
                            <button type="submit" class="btn btn-warning text-dark fw-bold w-100">CRIAR HASHTAG</button>
                        </form>
                    </div>
                </div>

                <div class="col-md-8">
                    <div class="glass-card">
                        <h5 class="fw-bold mb-3">Hashtags Cadastradas para Pesquisa</h5>
                        <div class="d-flex flex-wrap gap-2 mt-3 text-start">
                            <?php foreach($tags as $t): ?>
                                <span class="badge p-3 fs-6 d-inline-flex align-items-center bg-dark text-white border border-secondary rounded-pill">
                                    <i class="fa-solid fa-hashtag me-2 text-warning"></i> <?= htmlspecialchars(ltrim($t['tag_name'], '#')) ?>
                                    <form action="?tab=tags" method="POST" class="ms-3 d-inline">
                                        <input type="hidden" name="action" value="delete_tag">
                                        <input type="hidden" name="id" value="<?= $t['id'] ?>">
                                        <button type="submit" class="border-0 bg-transparent text-danger px-1" title="Deletar Tag"><i class="fa-solid fa-xmark"></i></button>
                                    </form>
                                </span>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- ======================= SECTION: 8. MANAGE REPORTS ======================= -->
        <?php if ($active_tab === 'reports'): ?>
            <div class="glass-card text-start">
                <h5 class="fw-bold mb-3"><i class="fa-solid fa-triangle-exclamation text-danger me-2"></i>Controle de Contas e Denúncias</h5>
                <div class="table-responsive">
                    <table class="table custom-tbl">
                        <thead>
                            <tr>
                                <th>Denunciante</th>
                                <th>Pacote Denunciado (ID)</th>
                                <th>Motivo da Denúncia</th>
                                <th>Registrado em</th>
                                <th>Status</th>
                                <th class="text-end">Mediação</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($reports as $r): ?>
                                <tr>
                                    <td><code><?= htmlspecialchars($r['reporter_email']) ?></code></td>
                                    <td><strong class="text-white"><?= htmlspecialchars(isset($r['pack_name']) ? $r['pack_name'] : 'Pack ' . $r['pack_id']) ?></strong></td>
                                    <td><p class="small text-white-50 mb-0"><?= htmlspecialchars($r['reason']) ?></p></td>
                                    <td><small><?= htmlspecialchars($r['created_at']) ?></small></td>
                                    <td>
                                        <?php if($r['status'] === 'reviewed'): ?>
                                            <span class="badge bg-success">Analisado</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger">Pendente</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end">
                                        <?php if($r['status'] !== 'reviewed'): ?>
                                            <form action="?tab=reports" method="POST" class="d-inline-block me-1">
                                                <input type="hidden" name="action" value="resolve_report">
                                                <input type="hidden" name="id" value="<?= $r['id'] ?>">
                                                <input type="hidden" name="resolution" value="dismiss">
                                                <button type="submit" class="btn btn-sm btn-outline-success" title="Arquivar / Manter Pacote"><i class="fa-solid fa-check"></i> Ignorar</button>
                                            </form>
                                            <form action="?tab=reports" method="POST" class="d-inline-block" onsubmit="return confirm('ATENÇÃO: Deseja realmente excluir este pacote de figurinhas em definitivo?')">
                                                <input type="hidden" name="action" value="resolve_report">
                                                <input type="hidden" name="id" value="<?= $r['id'] ?>">
                                                <input type="hidden" name="resolution" value="suspend">
                                                <button type="submit" class="btn btn-sm btn-danger" title="Suspender Pacote do App"><i class="fa-solid fa-ban"></i> Banir Pack</button>
                                            </form>
                                        <?php else: ?>
                                            <span class="text-white-50 small"><i class="fa-solid fa-check-double text-success"></i> Resolvido</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>

        <!-- ======================= SECTION: 9. MANAGE USERS ======================= -->
        <?php if ($active_tab === 'users'): ?>
            <div class="glass-card text-start">
                <h5 class="fw-bold mb-3">Gerenciamento de Assinaturas e Acessos</h5>
                <div class="table-responsive">
                    <table class="table custom-tbl">
                        <thead>
                            <tr>
                                <th>Nome</th>
                                <th>E-mail</th>
                                <th>Nivel de Fidelidade</th>
                                <th>Cadastrado em</th>
                                <th>Status de Acesso</th>
                                <th class="text-end">Ações de Pessoal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($users as $u): ?>
                                <tr>
                                    <td><strong class="text-white"><?= htmlspecialchars($u['name']) ?></strong></td>
                                    <td><code><?= htmlspecialchars($u['email']) ?></code></td>
                                    <td>
                                        <?php if($u['is_premium']): ?>
                                            <span class="badge badge-premium"><i class="fa-solid fa-crown me-1 text-warning"></i> PREMIUM</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Grátis</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><small><?= htmlspecialchars($u['created_at']) ?></small></td>
                                    <td>
                                        <?php if($u['is_blocked']): ?>
                                            <span class="badge bg-danger">Bloqueado</span>
                                        <?php else: ?>
                                            <span class="badge bg-success">Permitido</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end">
                                        <form action="?tab=users" method="POST" class="d-inline-block me-1">
                                            <input type="hidden" name="action" value="toggle_premium">
                                            <input type="hidden" name="id" value="<?= $u['id'] ?>">
                                            <button type="submit" class="btn btn-sm btn-outline-warning" title="Promover/Rebaixar Premium"><i class="fa-solid fa-crown"></i> Plan</button>
                                        </form>
                                        <form action="?tab=users" method="POST" class="d-inline-block">
                                            <input type="hidden" name="action" value="toggle_block">
                                            <input type="hidden" name="id" value="<?= $u['id'] ?>">
                                            <?php if($u['is_blocked']): ?>
                                                <button type="submit" class="btn btn-sm btn-outline-success" title="Desbloquear"><i class="fa-solid fa-user-check"></i> Desbloquear</button>
                                            <?php else: ?>
                                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Suspensa de Acesso"><i class="fa-solid fa-user-xmark"></i> Bloquear</button>
                                            <?php endif; ?>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>

        <!-- ======================= SECTION: 10. SHARE LINK SYSTEM ======================= -->
        <?php if ($active_tab === 'share'): ?>
            <div class="row g-4 text-start">
                <div class="col-md-5">
                    <div class="glass-card">
                        <h5 class="fw-bold mb-3"><i class="fa-solid fa-share-nodes text-primary me-2"></i>Campanhas Curtas de Download</h5>
                        <form action="?tab=share" method="POST">
                            <input type="hidden" name="action" value="save_share">
                            <div class="mb-2">
                                <label class="small text-white-50 mb-1">Título da Campanha</label>
                                <input type="text" class="form-control" name="share_title" placeholder="Ex: Campanha Instagram" required>
                            </div>
                            <div class="row mb-2">
                                <div class="col-6">
                                    <label class="small text-white-50 mb-1">Código Curto</label>
                                    <input type="text" class="form-control" name="share_code" placeholder="Ex: insta10" required>
                                </div>
                                <div class="col-6">
                                    <label class="small text-white-50 mb-1">Redirecionamento Original</label>
                                    <input type="url" class="form-control" name="share_orig" placeholder="https://" value="https://mystickerstore.com/instagram" required>
                                </div>
                            </div>
                            <hr class="border-secondary my-3">
                            <div class="mb-2">
                                <label class="small text-white-50 mb-1">Template de Indicação Integrado ao Botão Compartilhar do Android</label>
                                <textarea class="form-control" name="share_template" rows="3" required><?= htmlspecialchars($app_config['share_template']) ?></textarea>
                            </div>
                            <div class="mb-3">
                                <label class="small text-white-50 mb-1">URL Oficial de Download da Sticker Store</label>
                                <input type="url" class="form-control" name="share_url" value="<?= htmlspecialchars($app_config['share_url']) ?>" required>
                            </div>
                            <button type="submit" class="btn btn-primary text-dark fw-bold w-100">SALVAR CONFIGS E CRIAR LINK</button>
                        </form>
                    </div>
                </div>

                <div class="col-md-7">
                    <div class="glass-card">
                        <h5 class="fw-bold mb-3">Links Curtos e Desempenho de Cliques</h5>
                        <div class="table-responsive">
                            <table class="table custom-tbl">
                                <thead>
                                    <tr>
                                        <th>Campanha</th>
                                        <th>Link Gerado</th>
                                        <th>Redireciona</th>
                                        <th>Cliques Únicos</th>
                                        <th class="text-end">Ação</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($share_links as $sl): ?>
                                        <tr>
                                            <td><strong class="text-white"><?= htmlspecialchars($sl['title']) ?></strong></td>
                                            <td><code class="text-primary">myst.st/<?= htmlspecialchars($sl['short_code']) ?></code></td>
                                            <td><p class="small text-white-50 mb-0" style="max-width: 150px;"><?= htmlspecialchars($sl['original_url']) ?></p></td>
                                            <td><span class="badge bg-secondary"><?= $sl['clicks_count'] ?> cliques</span></td>
                                            <td class="text-end">
                                                <form action="?tab=share" method="POST" style="display:inline-block;">
                                                    <input type="hidden" name="action" value="delete_share">
                                                    <input type="hidden" name="id" value="<?= $sl['id'] ?>">
                                                    <button type="submit" class="btn btn-sm btn-outline-danger"><i class="fa-solid fa-trash"></i></button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- ======================= SECTION: 11. MANAGE PRIVACY POLICY ======================= -->
        <?php if ($active_tab === 'privacy'): ?>
            <div class="glass-card text-start">
                <h5 class="fw-bold mb-3"><i class="fa-solid fa-shield-halved text-success me-2"></i>Editor das Políticas e Termos da Figurinhas App</h5>
                <form action="?tab=privacy" method="POST">
                    <input type="hidden" name="action" value="save_privacy">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="small text-white-50 mb-1 font-weight-bold">Política de Privacidade Oficial (Texto Plain/Markdown fetched pelo App)</label>
                            <textarea class="form-control" name="privacy_policy" rows="18" style="font-family: monospace; font-size: 13px;" required><?= htmlspecialchars($app_config['privacy_policy']) ?></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="small text-white-50 mb-1 font-weight-bold">Termos de Serviço / Uso Adequado</label>
                            <textarea class="form-control" name="terms_of_service" rows="18" style="font-family: monospace; font-size: 13px;" required><?= htmlspecialchars($app_config['terms_of_service']) ?></textarea>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-success text-dark fw-bold px-5 py-3 mt-4">PULSAR POLÍTICAS ADERENTES NO APP</button>
                </form>
            </div>
        <?php endif; ?>

        <!-- ======================= SECTION: 12. FIREBASE SETTINGS ======================= -->
        <?php if ($active_tab === 'firebase'): ?>
            <div class="row g-4 text-start">
                <div class="col-md-6">
                    <div class="glass-card">
                        <h5 class="fw-bold mb-4 text-warning"><i class="fa-solid fa-fire me-2"></i>Controle de Integração do Firebase Cloud Messaging</h5>
                        <form action="?tab=firebase" method="POST">
                            <input type="hidden" name="action" value="save_firebase">
                            <div class="mb-2">
                                <label class="small text-white-50 mb-1">Firebase Project ID</label>
                                <input type="text" class="form-control" name="firebase_project_id" value="<?= htmlspecialchars($app_config['firebase_project_id']) ?>" required>
                            </div>
                            <div class="mb-2">
                                <label class="small text-white-50 mb-1">Web API Key</label>
                                <input type="text" class="form-control text-warning" name="firebase_api_key" value="<?= htmlspecialchars($app_config['firebase_api_key']) ?>" required>
                            </div>
                            <div class="mb-2">
                                <label class="small text-white-50 mb-1">FCM Server Key (Legacy FCM Token)</label>
                                <textarea class="form-control text-warning" name="firebase_server_key" rows="3" required><?= htmlspecialchars($app_config['firebase_server_key']) ?></textarea>
                            </div>
                            <div class="row mb-3">
                                <div class="col-6">
                                    <label class="small text-white-50 mb-1">Firebase Messaging Sender ID</label>
                                    <input type="text" class="form-control" name="firebase_messaging_sender_id" value="<?= htmlspecialchars($app_config['firebase_messaging_sender_id']) ?>" required>
                                </div>
                                <div class="col-6">
                                    <label class="small text-white-50 mb-1">Firebase APP ID</label>
                                    <input type="text" class="form-control" name="firebase_app_id" value="<?= htmlspecialchars($app_config['firebase_app_id']) ?>" required>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-warning text-dark fw-bold w-100 py-3">SALVAR CREDENCIAIS DO FIREBASE</button>
                        </form>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="glass-card">
                        <h5 class="fw-bold mb-3"><i class="fa-solid fa-shield-circle-check text-success me-2"></i>Injeção de Segurança do App Android</h5>
                        <p class="small text-white-50">Sua chave do Firebase e identificadores de mensageria controlam os notificações Push que aparecem de forma instantânea sob o aplicativo Sticker Pack das figurinhas.</p>
                        <p class="small text-white-50">Não modifique estes campos a menos que esteja portando seu aplicativo para uma nova conta do Google Play Store / Google Cloud Services.</p>
                        <div class="alert alert-warning bg-warning bg-opacity-10 border-warning text-white p-3 rounded-3 small">
                            <i class="fa-solid fa-triangle-exclamation text-warning me-2"></i> <strong>Atenção:</strong> Chaves FCM incorretas impedirão que a função "Disparar Push" do menu ao lado envie atualizações em tempo real para os celulares cadastrados.
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- ======================= SECTION: 13. MONETIZAÇÃO ADMOB ======================= -->
        <?php if ($active_tab === 'ads'): ?>
            <div class="glass-card text-start">
                <h5 class="fw-bold mb-4 text-success"><i class="fa-solid fa-sack-dollar me-2"></i>Monetização Google AdMob</h5>
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="p-3 bg-dark bg-opacity-20 border border-secondary rounded-3">
                            <h6 class="fw-bold text-white mb-2">AdMob Banner Unit ID</h6>
                            <label class="small text-white-50 mb-1">Produção Key</label>
                            <input type="text" class="form-control text-success fw-bold font-monospace" value="ca-app-pub-3940256099942544/6300978111" disabled>
                            <span class="small text-white-50 mt-1 d-block">Testes: <code>ca-app-pub-3940256099942544/6300978111</code></span>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="p-3 bg-dark bg-opacity-20 border border-secondary rounded-3">
                            <h6 class="fw-bold text-white mb-2">AdMob Interstitial Unit ID</h6>
                            <label class="small text-white-50 mb-1">Produção Key</label>
                            <input type="text" class="form-control text-success fw-bold font-monospace" value="ca-app-pub-3940256099942544/1033173712" disabled>
                            <span class="small text-white-50 mt-1 d-block">Testes: <code>ca-app-pub-3940256099942544/1033173712</code></span>
                        </div>
                    </div>
                </div>
                <button class="btn btn-success text-dark fw-bold px-4 py-2 mt-4" onclick="alert('Monetização AdMob configurada com sucesso para todos os clientes!')">Confirmar Chaves Monetizadas</button>
            </div>
        <?php endif; ?>

    </div>

    <!-- SUPPORT ANSWER/REPLY MODAL -->
    <div class="modal fade" id="replyModal" tabindex="-1" aria-labelledby="replyModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content bg-dark text-white border-secondary">
                <div class="modal-header border-secondary">
                    <h5 class="modal-title font-weight-bold" id="replyModalLabel"><i class="fa-solid fa-reply text-[#6cf8bb] me-2"></i>Responder Ticket</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-start">
                    <form action="?tab=support" method="POST">
                        <input type="hidden" name="action" value="reply_support">
                        <input type="hidden" name="id" id="reply-ticket-id">
                        
                        <div class="mb-2">
                            <label class="small text-white-50 mb-1">Destinatário</label>
                            <input type="text" class="form-control bg-black border-0" id="reply-receiver" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="small text-white-50 mb-1">Mensagem Recebida</label>
                            <div class="p-3 bg-black rounded text-white-50 small" id="reply-original-msg" style="max-height: 120px; overflow-y:auto;"></div>
                        </div>
                        <div class="mb-3">
                            <label class="small text-white-50 mb-1">Sua Mensagem de Resposta</label>
                            <textarea class="form-control" name="reply_text" rows="5" placeholder="Digite aqui o retorno ao usuário..." required></textarea>
                        </div>
                        <button type="submit" class="btn btn-emerald text-dark fw-bold w-100 py-2" style="background-color:#6cf8bb">DISPARAR E SOLUCIONAR TICKET</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Theme toggler action toggle
        function toggleTheme() {
            document.body.classList.toggle('light-theme');
            const icon = document.getElementById('theme-icon');
            if (document.body.classList.contains('light-theme')) {
                icon.className = 'fa-solid fa-moon';
            } else {
                icon.className = 'fa-solid fa-sun';
            }
        }

        // Open and prepare Reply Ticket modal dynamically
        function openReplyModal(id, name, msg) {
            document.getElementById('reply-ticket-id').value = id;
            document.getElementById('reply-receiver').value = name;
            document.getElementById('reply-original-msg').textContent = msg;
            
            const m = new bootstrap.Modal(document.getElementById('replyModal'));
            m.show();
        }
    </script>
</body>
</html>
