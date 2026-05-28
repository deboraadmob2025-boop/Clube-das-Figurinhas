-- Database Schema for Sticker Store Admin Ecosystem
-- Compatible with MySQL 5.7 and 8.0+

CREATE DATABASE IF NOT EXISTS `sticker_store` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `sticker_store`;

-- 1. Admins Table
CREATE TABLE IF NOT EXISTS `admins` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `username` VARCHAR(50) NOT NULL UNIQUE,
    `email` VARCHAR(100) NOT NULL UNIQUE,
    `password_hash` VARCHAR(255) NOT NULL,
    `role` VARCHAR(20) DEFAULT 'editor', -- master, editor, admin
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `last_login` TIMESTAMP NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Users Table
CREATE TABLE IF NOT EXISTS `users` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `email` VARCHAR(100) NOT NULL UNIQUE,
    `password_hash` VARCHAR(255) NOT NULL,
    `avatar` VARCHAR(255) DEFAULT NULL,
    `is_premium` TINYINT(1) DEFAULT 0,
    `is_blocked` TINYINT(1) DEFAULT 0,
    `device_token` VARCHAR(255) DEFAULT NULL, -- FCM Token
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Categories Table
CREATE TABLE IF NOT EXISTS `categories` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(50) NOT NULL UNIQUE,
    `order_index` INT DEFAULT 0,
    `icon_emoji` VARCHAR(20) DEFAULT '✨',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Sticker Packs Table (Sticker Packs uploaded by Administrator or Users)
CREATE TABLE IF NOT EXISTS `sticker_packs` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `creator` VARCHAR(100) NOT NULL,
    `category_id` INT,
    `cover_url` VARCHAR(255) NOT NULL,
    `is_premium` TINYINT(1) DEFAULT 0,
    `is_exclusive` TINYINT(1) DEFAULT 0,
    `downloads_count` INT DEFAULT 0,
    `likes_count` INT DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. Stickers Table (Individual image assets inside a Sticker Pack)
CREATE TABLE IF NOT EXISTS `stickers` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `pack_id` INT NOT NULL,
    `image_url` VARCHAR(255) NOT NULL,
    `content_description` VARCHAR(255) DEFAULT '',
    `order_index` INT DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`pack_id`) REFERENCES `sticker_packs`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. Favorites Table
CREATE TABLE IF NOT EXISTS `favorites` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `pack_id` INT NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `user_pack_fav` (`user_id`, `pack_id`),
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`pack_id`) REFERENCES `sticker_packs`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7. Downloads Table
CREATE TABLE IF NOT EXISTS `downloads` (
    `id` BIGINT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NULL,
    `pack_id` INT NOT NULL,
    `downloaded_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`pack_id`) REFERENCES `sticker_packs`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 8. Notifications / Push messaging history Log table
CREATE TABLE IF NOT EXISTS `notifications` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `title` VARCHAR(150) NOT NULL,
    `message` TEXT NOT NULL,
    `target_category` VARCHAR(50) DEFAULT 'all', -- 'all' or categories name
    `sent_at` TIMESTAMP NULL DEFAULT NULL,
    `scheduled_at` TIMESTAMP NULL DEFAULT NULL,
    `status` VARCHAR(20) DEFAULT 'pending' -- pending, sent, cancelled
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 9. General App Settings configurations
CREATE TABLE IF NOT EXISTS `app_config` (
    `key_name` VARCHAR(50) PRIMARY KEY,
    `val_value` TEXT NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 10. Ads Configurations setup
CREATE TABLE IF NOT EXISTS `ads_config` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `ad_type` VARCHAR(50) NOT NULL, -- Banner, Interstitial, Rewarded, Rewarded Interstitial
    `unit_id_test` VARCHAR(150) NOT NULL,
    `unit_id_prod` VARCHAR(150) NOT NULL,
    `is_active` TINYINT(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- --- SEED SEED DATA TO EASILY TEST AND CONFIGURE ---

INSERT INTO `admins` (`id`, `username`, `email`, `password_hash`, `role`) VALUES
(1, 'admin', 'admin@stickerstore.com', '$2y$10$yepN8b5fP4p.p9Y.D.OFeOHfT7d87m0N7d.S6y3Z0zR9tMoV9t.vK', 'master'); -- Default password: 'admin'

INSERT INTO `categories` (`id`, `name`, `order_index`, `icon_emoji`) VALUES
(1, 'Memes', 1, '😂'),
(2, 'Love', 2, '💖'),
(3, 'Anime', 3, '🌸'),
(4, 'Funny', 4, '🤪'),
(5, 'Animals', 5, '🐱'),
(6, 'Gaming', 6, '🎮');

INSERT INTO `sticker_packs` (`id`, `name`, `creator`, `category_id`, `cover_url`, `is_premium`, `is_exclusive`, `downloads_count`, `likes_count`) VALUES
(1, 'Cyber Gatos', 'NeonMochi', 5, 'https://lh3.googleusercontent.com/aida-public/AB6AXuCtkSf5UeSaJUridQTq5N4vmoVsP0gexrgiYTdP0qRlbEs30ew908UhCNEi9m4LssAhXwVOg57u1e_ez-5g3TCOLjGUqA_-27lK6b3gjBo5fwN-xvC_bKUNHrOkf3ISxAXnFmmx8mWdAdYoIk0HajgPSXMuRMLYN6Jh-eRtFtqy4r1QWUPvDtru9uh3AoAcbANQjuMITfhzZ5Rkxqa6gWxcRXhsv9GA-oJYENfKNAiEofeImH6pZ-f27B90qgHjsVEMaAFiBSJb5Tg', 1, 0, 1250, 482),
(2, 'Retro Vibes', 'Synthwave_Artist', 6, 'https://lh3.googleusercontent.com/aida-public/AB6AXuDSlXNY5xsvukq1cD3BhF6kpieE4o3vgOZk8YylLlfBhEquaGujYIHv4QQ9rAub4A1DRMFUzHjM5xKHdC4OOWlgHdsfpPqOtx19A8WQpiXCfpQib90cmaTUY9Sx-DoPGsbRmU5dJHDGht4bQiF6BQJx_DnuOAOJ0QvkzKsheFIsaS6MRg__iO_2QRhKn_Eo8Yl1PaD_58ALnKDr2xow6qNf4XwSrYsK3L95amcKQHj92dp8jVKK-v13i9K328iNg706PgT3oGpFGg0', 0, 0, 950, 128),
(3, 'Happy Hamsters', 'Hammy & Friends', 5, 'https://lh3.googleusercontent.com/aida-public/AB6AXuB_Z0tqDaC3KJVQ3A6aBXvdaiZwlLGqBgvZdC_z0ClI1HEAN89XuPVS3IFXXrQReuzm3VlVdhV4P0EW73kRmqoMGDyALMdWafrpY-4Yn5niG-2yrSBgL0dEriunRsqvZ92O8za8DmAajIfFNL_Ew53xRDUeRwKVKcdshYFnIW5jZah1NpWcm76G9iNJgw_QolKpqw-5l-giHkcDD52SKgFLnmlmgD948Bajuedke3tGzv4s7-SO-tQxNGvKnVSH0mnBQGu17OVBwh0', 0, 0, 1800, 310),
(4, 'Emoji On Fire', 'FireDesign Studio', 4, 'https://lh3.googleusercontent.com/aida-public/AB6AXuBvjckPmaEJe33G5FxRwVtQ60fWzDPZz2GrZsArtNmrbGN_yVsnTBAjbtugbpkODuG7cRtLujjI-FTJo5ZxEw9J0sGBHmt03d0VzRZIN8jvj2P-OEViUE1Wu0qt2OjHKfsbByVw_sKdcnt-viCgj1WN9prXDsMQa6h_2uINZr7-bDmWO9RsNsOeOh3hGjCJNMZPokDKCgPYL3YZHqDtf7QvirxzP_NxVsdqW7O0K7iSjEpBK3Azu_5aoD8yZ9-zzcOFyRVs4vHzR3U', 0, 1, 2400, 680);

INSERT INTO `stickers` (`id`, `pack_id`, `image_url`, `content_description`, `order_index`) VALUES
(1, 1, 'https://lh3.googleusercontent.com/aida-public/AB6AXuCtkSf5UeSaJUridQTq5N4vmoVsP0gexrgiYTdP0qRlbEs30ew908UhCNEi9m4LssAhXwVOg57u1e_ez-5g3TCOLjGUqA_-27lK6b3gjBo5fwN-xvC_bKUNHrOkf3ISxAXnFmmx8mWdAdYoIk0HajgPSXMuRMLYN6Jh-eRtFtqy4r1QWUPvDtru9uh3AoAcbANQjuMITfhzZ5Rkxqa6gWxcRXhsv9GA-oJYENfKNAiEofeImH6pZ-f27B90qgHjsVEMaAFiBSJb5Tg', 'Futuristic blue neon cat', 1),
(2, 1, 'https://lh3.googleusercontent.com/aida-public/AB6AXuA3wfyyh4fYujNiui8ykRW7sThV2EscYZZpuXEFUI3NbIeojw_q5XIjhtJdEBbvtm3fXWgkX4UrUX1db3BoaTMLIPTva7bXJyYDpMYL7t9XPotyDJ73vhvtYxh4TTsCtHbjFwB0iHI9Z3iMQfYO3eDIFPJpWzNE6RIj54lAQanMU5km61D3nyzx6c88sHI7KXZXyTjQAcnhhZ4NQTlnqxYzhetVswujz_XcYvqXC3brpw24bx52M5QPqVMlWJq2E6CwiI5cCH3-hjI', 'Robot cat with digital hearts', 2),
(3, 1, 'https://lh3.googleusercontent.com/aida-public/AB6AXuDD7tu1cGmRMHMaEKcw5RrpUj3EwSleP1nyuecTI2gqx-s1i6gw1p1zcCj9s0zdJhSWEbAzRK-CHR86zaV24La9Vhsh_5q1TEJtMYmJ_gNxQ1ZGzHnOG8hHd_PWTY04YjQCRu6MhXmGgfn7KNMmzhOdlf_7LDWx8rQa8r80ig7z5cCADJZ5iVwMqj180g7fqDuRSI2ZH4Xlyno0L1Y_NjPBMmm9Dn7V3ZYJcr1R2Tt5I6-rk5NbEk90_ZvI_JbVwypgKrZkQFcFJcw', 'Tearing laughing futuristic cat', 3),
(4, 2, 'https://lh3.googleusercontent.com/aida-public/AB6AXuDSlXNY5xsvukq1cD3BhF6kpieE4o3vgOZk8YylLlfBhEquaGujYIHv4QQ9rAub4A1DRMFUzHjM5xKHdC4OOWlgHdsfpPqOtx19A8WQpiXCfpQib90cmaTUY9Sx-DoPGsbRmU5dJHDGht4bQiF6BQJx_DnuOAOJ0QvkzKsheFIsaS6MRg__iO_2QRhKn_Eo8Yl1PaD_58ALnKDr2xow6qNf4XwSrYsK3L95amcKQHj92dp8jVKK-v13i9K328iNg706PgT3oGpFGg0', '3D retro pink sunglasses', 1),
(5, 2, 'https://lh3.googleusercontent.com/aida-public/AB6AXuDCEhsusl_vHLmq-btvaoNk1RDEPzfyw71BI-3r_cnoWB-90By9kBKV6r5LO1ztM91Re5YPVDne5hUFsdWTGu-Rz-hGJPmFvffVsZrzyZ1BRTz0C9H-XLvSEHeJ1PIBy_p5I7u1V9RDezS6GKLziKOCN4R_4yIxyymTGy3qGOfU7_UMj7d8x0UiewTNVcLSQ5w2JcGXmn9RJbQ1Kp5nWHcYpO9SW9dEsF-ydCoomKB_I6lbpnO_2PFxzq2Q841Mh7jFk6sLDAO8XmI', 'Neon custom music cassette', 2);

INSERT INTO `app_config` (`key_name`, `val_value`) VALUES
('app_name', 'Sticker Store Premium'),
('logo_url', 'ic_logo.xml'),
('primary_color', '#6200EE'),
('languages_enabled', 'pt-BR,en-US,es-ES'),
('policy_url', 'https://mystickerstore.com/privacy'),
('terms_url', 'https://mystickerstore.com/terms');

INSERT INTO `ads_config` (`id`, `ad_type`, `unit_id_test`, `unit_id_prod`, `is_active`) VALUES
(1, 'Banner', 'ca-app-pub-3940256099942544/6300978111', 'ca-app-pub-3940256099942544/6300978111', 1),
(2, 'Interstitial', 'ca-app-pub-3940256099942544/1033173712', 'ca-app-pub-3940256099942544/1033173712', 1),
(3, 'Rewarded', 'ca-app-pub-3940256099942544/5224354917', 'ca-app-pub-3940256099942544/5224354917', 1),
(4, 'Rewarded Interstitial', 'ca-app-pub-3940256099942544/5354046379', 'ca-app-pub-3940256099942544/5354046379', 1);
