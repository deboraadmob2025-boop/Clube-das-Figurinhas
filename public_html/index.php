<?php
// Intelligent Device Detection & App Landing Gateway
require_once "config/database.php";

$app_name = "Sticker Store Premium";
$policy_url = "https://mystickerstore.com/privacy";
$terms_url = "https://mystickerstore.com/terms";

// Attempt to read from DB for live updates
$database = new Database();
$db = $database->getConnection();
if ($db) {
    try {
        $stmt = $db->prepare("SELECT * FROM app_config");
        $stmt->execute();
        $rows = $stmt->fetchAll();
        foreach ($rows as $row) {
            if ($row['key_name'] === 'app_name') $app_name = $row['val_value'];
            if ($row['key_name'] === 'policy_url') $policy_url = $row['val_value'];
            if ($row['key_name'] === 'terms_url') $terms_url = $row['val_value'];
        }
    } catch (PDOException $e) {}
}

$user_agent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
$is_android = stripos($user_agent, 'Android') !== false;
$is_mobile = stripos($user_agent, 'Mobi') !== false || stripos($user_agent, 'Tablet') !== false || $is_android;
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($app_name) ?> - Baixar Figurinhas WhatsApp</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;700&display=swap" rel="stylesheet">
    <!-- FontAwesome Elements -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --primary: #6cf8bb;
            --primary-dark: #006c49;
            --purple: #6200ee;
            --dark-bg: #090e14;
            --card-glass: rgba(255, 255, 255, 0.03);
            --border-glass: rgba(255, 255, 255, 0.08);
            --neon-glow: 0 0 20px rgba(108, 248, 187, 0.3);
        }

        body {
            font-family: 'Space Grotesk', sans-serif;
            background: #06090e;
            background: radial-gradient(circle at 10% 20%, #030a10 0%, #0c0818 100%);
            color: #f8f9fa;
            min-height: 100vh;
            overflow-x: hidden;
            position: relative;
        }

        /* Ambient neon lights background */
        .aura {
            position: absolute;
            border-radius: 50%;
            filter: blur(140px);
            opacity: 0.16;
            pointer-events: none;
            z-index: 0;
        }
        .aura-1 {
            width: 450px;
            height: 450px;
            top: 5%;
            left: -150px;
            background: var(--primary);
        }
        .aura-2 {
            width: 550px;
            height: 550px;
            top: 45%;
            right: -150px;
            background: var(--purple);
        }

        /* Glassmorphism Styling */
        .glass-nav {
            background: rgba(9, 14, 20, 0.7);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-bottom: 1px solid var(--border-glass);
            z-index: 1000;
        }

        .navbar-brand {
            font-weight: 700;
            font-size: 24px;
            background: linear-gradient(120deg, var(--primary), var(--purple));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .glass-card {
            background: rgba(255, 255, 255, 0.02);
            backdrop-filter: blur(25px);
            -webkit-backdrop-filter: blur(25px);
            border: 1px solid var(--border-glass);
            border-radius: 24px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .glass-card:hover {
            transform: translateY(-6px);
            border-color: rgba(108, 248, 187, 0.25);
            box-shadow: 0 15px 35px rgba(108, 248, 187, 0.08);
        }

        /* Search wrap styling */
        .search-area {
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid var(--border-glass);
            border-radius: 20px;
            padding: 8px 16px;
            display: flex;
            align-items: center;
            max-width: 540px;
            margin: 0 auto;
            transition: border-color 0.3s;
        }

        .search-area:focus-within {
            border-color: var(--primary);
            box-shadow: var(--neon-glow);
        }

        .search-area input {
            background: transparent;
            border: none;
            color: #fff;
            outline: none;
            width: 100%;
            margin-left: 12px;
            font-size: 15px;
        }

        /* Tabs styling */
        .nav-tabs-custom {
            border: none !important;
            display: flex;
            justify-content: center;
            gap: 12px;
            margin-bottom: 40px;
        }

        .nav-tabs-custom .nav-link {
            background: rgba(255, 255, 255, 0.02) !important;
            border: 1px solid var(--border-glass) !important;
            color: rgba(255, 255, 255, 0.6) !important;
            border-radius: 16px !important;
            padding: 12px 24px !important;
            font-weight: 500;
            transition: all 0.2s;
        }

        .nav-tabs-custom .nav-link.active {
            background: linear-gradient(135deg, rgba(108, 248, 187, 0.1) 0%, rgba(98, 0, 238, 0.1) 100%) !important;
            color: var(--primary) !important;
            border-color: var(--primary) !important;
            box-shadow: var(--neon-glow);
        }

        .category-pill {
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid var(--border-glass);
            color: #fff;
            padding: 8px 18px;
            border-radius: 24px;
            cursor: pointer;
            transition: all 0.3s;
            font-size: 14px;
        }

        .category-pill.active, .category-pill:hover {
            background: var(--primary);
            color: #0c0818;
            border-color: var(--primary);
            font-weight: 500;
        }

        /* 3D Smartphone Simulator */
        .smartphone-mockup {
            width: 320px;
            height: 640px;
            background: #0a0f18;
            border: 12px solid #232b35;
            border-radius: 44px;
            box-shadow: 0 35px 70px rgba(0,0,0,0.6), inset 0 0 12px rgba(255,255,255,0.06);
            position: relative;
            margin: 0 auto;
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }

        .smartphone-mockup::after {
            content: '';
            position: absolute;
            top: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 130px;
            height: 24px;
            background: #232b35;
            border-bottom-left-radius: 16px;
            border-bottom-right-radius: 16px;
            z-index: 999;
        }

        .app-screen {
            flex: 1;
            padding: 24px 16px 16px;
            overflow-y: auto;
            font-size: 13px;
            background: #090e14;
            display: flex;
            flex-direction: column;
            gap: 16px;
            position: relative;
        }

        .app-screen::-webkit-scrollbar { display: none; }

        .screen-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid rgba(255,255,255,0.05);
            padding-bottom: 10px;
            margin-top: 8px;
        }

        .screen-categories {
            display: flex;
            gap: 8px;
            overflow-x: auto;
        }
        .screen-categories::-webkit-scrollbar { display: none; }

        .screen-chip {
            background: rgba(255,255,255,0.06);
            border: 1px solid rgba(255,255,255,0.05);
            padding: 4px 10px;
            border-radius: 20px;
            white-space: nowrap;
            font-size: 11px;
        }
        .screen-chip.active {
            background: var(--primary);
            color: #000;
            font-weight: 700;
        }

        .screen-pack-card {
            background: rgba(255,255,255,0.02);
            border-radius: 14px;
            padding: 10px;
            border: 1px solid rgba(255,255,255,0.04);
            text-align: left;
        }

        .stickers-grid-preview {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 6px;
            margin: 8px 0;
        }

        .stk-preview-img {
            background: rgba(255,255,255,0.04);
            border-radius: 8px;
            aspect-ratio: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: transform 0.2s;
        }
        .stk-preview-img img {
            width: 80%;
            height: 80%;
            object-fit: contain;
        }

        /* Buttons style */
        .btn-premium-accent {
            background: linear-gradient(135deg, rgb(108, 248, 187) 0%, rgb(0, 108, 73) 100%);
            border: none;
            color: #002113 !important;
            font-weight: 700;
            padding: 14px 28px;
            border-radius: 16px;
            box-shadow: 0 8px 24px rgba(108, 248, 187, 0.3);
            transition: all 0.3s;
        }

        .btn-premium-accent:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 30px rgba(108, 248, 187, 0.45);
        }

        /* Blurry Block Overlay */
        .restriced-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(9, 14, 20, 0.85);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            z-index: 1100;
            display: none;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .glow-num {
            font-size: 38px;
            font-weight: 700;
            color: var(--primary);
            text-shadow: 0 0 15px rgba(108, 248, 187, 0.4);
        }

        /* Lateral Floating QR and Recommendation box */
        .side-promo-banner {
            background: linear-gradient(135deg, rgba(98,0,238,0.15) 0%, rgba(108,248,187,0.05) 100%);
            border: 1px solid var(--border-glass);
            border-radius: 20px;
            padding: 24px;
            text-align: center;
            margin-top: 30px;
        }

        .floating-qr-widget {
            position: fixed;
            bottom: 30px;
            left: 30px;
            background: rgba(9, 14, 20, 0.9);
            backdrop-filter: blur(15px);
            border: 1px solid var(--border-glass);
            border-radius: 24px;
            width: 250px;
            padding: 24px;
            box-shadow: 0 15px 40px rgba(0,0,0,0.5);
            z-index: 999;
            text-align: center;
        }

        /* Sticker single image hover animation */
        .pack-grid-item {
            aspect-ratio: 1;
            background: rgba(255, 255, 255, 0.01);
            border: 1px solid rgba(255,255,255,0.03);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 10px;
            transition: transform 0.2s, background 0.2s;
        }

        .pack-grid-item:hover {
            transform: scale(1.1) rotate(4deg);
            background: rgba(108, 248, 187, 0.05);
            border-color: var(--primary);
        }

        .app-smart-banner {
            background: linear-gradient(90deg, #090e14, #12092c);
            border-bottom: 2px solid var(--primary);
            color: #fff;
            padding: 10px 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 13px;
            z-index: 1010;
            position: relative;
        }

        /* Auto-floating CTA button */
        .mobile-floating-install-cta {
            position: fixed;
            bottom: 24px;
            left: 20px;
            right: 20px;
            z-index: 1000;
            display: none;
        }

        @media (max-width: 768px) {
            .mobile-floating-install-cta {
                display: block;
            }
            .floating-qr-widget {
                display: none !important;
            }
        }
    </style>
</head>
<body class="<?= $is_mobile ? 'mode-mobile' : 'mode-desktop' ?>">

    <div class="aura aura-1"></div>
    <div class="aura aura-2"></div>

    <!-- SMART BANNER -->
    <div class="app-smart-banner" id="app-smart-banner">
        <div class="d-flex align-items-center">
            <button class="btn btn-link text-white-50 p-1 me-3 border-0 text-decoration-none" onclick="dismissSmartBanner()"><i class="fa-solid fa-xmark"></i></button>
            <div class="bg-primary text-dark p-2 rounded-3 me-3" style="width: 38px; height: 38px; display: flex; align-items:center; justify-content:center; font-weight:700;">★</div>
            <div>
                <strong class="d-block" style="font-size:14px;"><?= htmlspecialchars($app_name) ?></strong>
                <span class="text-white-50" style="font-size:12px;">Instalação direta e atualizações instantâneas no WhatsApp. 4.9★</span>
            </div>
        </div>
        <button class="btn btn-premium-accent py-1 px-3 fs-6" onclick="triggerAppDownload()">INSTALAR APP</button>
    </div>

    <!-- NAVBAR -->
    <nav class="navbar navbar-expand-lg glass-nav py-3 sticky-top">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="#">
                <i class="fa-solid fa-wand-magic-sparkles me-2 text-primary"></i> <?= htmlspecialchars($app_name) ?>
            </a>
            <button class="navbar-toggler border-secondary text-white" type="button" data-bs-toggle="collapse" data-bs-target="#navContent">
                <span class="navbar-toggler-icon" style="filter: invert(1);"></span>
            </button>
            <div class="collapse navbar-collapse" id="navContent">
                <ul class="navbar-nav ms-auto gap-2">
                    <li class="nav-item">
                        <a class="nav-link text-white-50" href="#browse-section">Explorar</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white-50" href="#features-section">Vantagens</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white-50" href="#reviews-section">Avaliações</a>
                    </li>
                    <li class="nav-item ms-lg-3">
                        <button class="btn btn-premium-accent py-2" onclick="triggerAppDownload()"><i class="fa-brands fa-android me-2"></i>OBTER APLICATIVO</button>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- HERO PRESENTATION -->
    <section class="py-5">
        <div class="container">
            <div class="row align-items-center g-5">
                <div class="col-lg-6 text-lg-start text-center">
                    <div class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-20 px-3 py-2 rounded-pill mb-3">
                        🔋 COMPATÍVEL COM CELULAR E TABLET ANDROID
                    </div>
                    <h1 class="display-4 fw-bold lh-sm mb-4">
                        O Aplicativo de Figurinhas Mais <span class="text-primary">Completo e Moderno</span> do Brasil!
                    </h1>
                    <p class="lead text-white-50 mb-5">
                        Baixe milhares de figurinhas animadas em HD, crie seus próprios pacotes no celular com IA, e envie para o WhatsApp com apenas um clique. Sem enrolação de navegador!
                    </p>

                    <!-- Down links badging -->
                    <div class="d-flex flex-wrap justify-content-lg-start justify-content-center gap-3 mb-5">
                        <a href="https://play.google.com/store" target="_blank" class="btn btn-dark border-secondary px-4 py-3 rounded-4 d-flex align-items-center" onclick="triggerStoreDownload()">
                            <i class="fa-brands fa-google-play fs-2 me-3 text-success"></i>
                            <div class="text-start">
                                <span class="text-white-50 small d-block" style="font-size:10px;">DISPONÍVEL NA</span>
                                <strong style="font-size:15px;">Google Play Store</strong>
                            </div>
                        </a>
                        <button class="btn btn-premium-accent px-4 py-3 rounded-4 d-flex align-items-center" onclick="triggerAppDownload()">
                            <i class="fa-solid fa-circle-down fs-2 me-3 text-dark"></i>
                            <div class="text-start">
                                <span class="text-dark small d-block" style="font-size:10px; opacity:0.8;">BAIXAR APK</span>
                                <strong>Download Direto</strong>
                            </div>
                        </button>
                    </div>

                    <!-- Statistics list count -->
                    <div class="row g-4 justify-content-center justify-content-lg-start">
                        <div class="col-4">
                            <div class="glow-num" id="hero-num-downloads">42.5k</div>
                            <span class="small text-white-50">Instalações ativas</span>
                        </div>
                        <div class="col-4">
                            <div class="glow-num" id="hero-num-packs">1,200+</div>
                            <span class="small text-white-50">Packs de figurinhas</span>
                        </div>
                        <div class="col-4">
                            <div class="glow-num">4.9 ★</div>
                            <span class="small text-white-50">Nota na Play Store</span>
                        </div>
                    </div>
                </div>

                <!-- Smartphone simulator column -->
                <div class="col-lg-6">
                    <div class="smartphone-mockup shadow-lg">
                        <div class="app-screen">
                            <div class="d-flex justify-content-between text-white-50" style="font-size:10px; margin-top:6px;">
                                <span>12:30 <i class="fa-solid fa-wifi ms-1"></i></span>
                                <div><i class="fa-solid fa-battery-full"></i></div>
                            </div>

                            <div class="screen-header">
                                <div class="fw-bold text-white d-flex align-items-center">
                                    <i class="fa-solid fa-wand-magic-sparkles text-primary me-2"></i>Sticker Store
                                </div>
                                <div class="text-white-50"><i class="fa-solid fa-user"></i></div>
                            </div>

                            <div class="screen-categories">
                                <span class="screen-chip active" onclick="interceptBrowse('Simulador: Categoria Todos')">Todos</span>
                                <span class="screen-chip" onclick="interceptBrowse('Simulador: Categoria Memes')">😂 Memes</span>
                                <span class="screen-chip" onclick="interceptBrowse('Simulador: Categoria Romance')">💖 Love</span>
                                <span class="screen-chip" onclick="interceptBrowse('Simulador: Categoria Anime')">🌸 Anime</span>
                            </div>

                            <div class="screen-pack-card" onclick="interceptBrowse('Simulador: Pack Cyber Gatos')">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <b class="d-block">Cyber Gatos 🐈</b>
                                        <span class="text-white-50" style="font-size:10px;">9 figurinhas prontas</span>
                                    </div>
                                    <span class="badge bg-primary text-dark py-1 px-2 fw-bold" style="font-size:10px;">ADD</span>
                                </div>
                                <div class="stickers-grid-preview">
                                    <div class="stk-preview-img"><img src="https://lh3.googleusercontent.com/aida-public/AB6AXuCtkSf5UeSaJUridQTq5N4vmoVsP0gexrgiYTdP0qRlbEs30ew908UhCNEi9m4LssAhXwVOg57u1e_ez-5g3TCOLjGUqA_-27lK6b3gjBo5fwN-xvC_bKUNHrOkf3ISxAXnFmmx8mWdAdYoIk0HajgPSXMuRMLYN6Jh-eRtFtqy4r1QWUPvDtru9uh3AoAcbANQjuMITfhzZ5Rkxqa6gWxcRXhsv9GA-oJYENfKNAiEofeImH6pZ-f27B90qgHjsVEMaAFiBSJb5Tg"></div>
                                    <div class="stk-preview-img"><img src="https://lh3.googleusercontent.com/aida-public/AB6AXuA3wfyyh4fYujNiui8ykRW7sThV2EscYZZpuXEFUI3NbIeojw_q5XIjhtJdEBbvtm3fXWgkX4UrUX1db3BoaTMLIPTva7bXJyYDpMYL7t9XPotyDJ73vhvtYxh4TTsCtHbjFwB0iHI9Z3iMQfYO3eDIFPJpWzNE6RIj54lAQanMU5km61D3nyzx6c88sHI7KXZXyTjQAcnhhZ4NQTlnqxYzhetVswujz_XcYvqXC3brpw24bx52M5QPqVMlWJq2E6CwiI5cCH3-hjI"></div>
                                    <div class="stk-preview-img"><img src="https://lh3.googleusercontent.com/aida-public/AB6AXuDD7tu1cGmRMHMaEKcw5RrpUj3EwSleP1nyuecTI2gqx-s1i6gw1p1zcCj9s0zdJhSWEbAzRK-CHR86zaV24La9Vhsh_5q1TEJtMYmJ_gNxQ1ZGzHnOG8hHd_PWTY04YjQCRu6MhXmGgfn7KNMmzhOdlf_7LDWx8rQa8r80ig7z5cCADJZ5iVwMqj180g7fqDuRSI2ZH4Xlyno0L1Y_NjPBMmm9Dn7V3ZYJcr1R2Tt5I6-rk5NbEk90_ZvI_JbVwypgKrZkQFcFJcw"></div>
                                </div>
                            </div>

                            <div class="screen-pack-card" onclick="interceptBrowse('Simulador: Pack Retro Vibes')">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <b class="d-block">Retro Vibes 📽️</b>
                                        <span class="text-white-50" style="font-size:10px;">6 figurinhas nostalgia</span>
                                    </div>
                                    <span class="badge bg-primary text-dark py-1 px-2 fw-bold" style="font-size:10px;">ADD</span>
                                </div>
                                <div class="stickers-grid-preview">
                                    <div class="stk-preview-img"><img src="https://lh3.googleusercontent.com/aida-public/AB6AXuDSlXNY5xsvukq1cD3BhF6kpieE4o3vgOZk8YylLlfBhEquaGujYIHv4QQ9rAub4A1DRMFUzHjM5xKHdC4OOWlgHdsfpPqOtx19A8WQpiXCfpQib90cmaTUY9Sx-DoPGsbRmU5dJHDGht4bQiF6BQJx_DnuOAOJ0QvkzKsheFIsaS6MRg__iO_2QRhKn_Eo8Yl1PaD_58ALnKDr2xow6qNf4XwSrYsK3L95amcKQHj92dp8jVKK-v13i9K328iNg706PgT3oGpFGg0"></div>
                                    <div class="stk-preview-img"><img src="https://lh3.googleusercontent.com/aida-public/AB6AXuDCEhsusl_vHLmq-btvaoNk1RDEPzfyw71BI-3r_cnoWB-90By9kBKV6r5LO1ztM91Re5YPVDne5hUFsdWTGu-Rz-hGJPmFvffVsZrzyZ1BRTz0C9H-XLvSEHeJ1PIBy_p5I7u1V9RDezS6GKLziKOCN4R_4yIxyymTGy3qGOfU7_UMj7d8x0UiewTNVcLSQ5w2JcGXmn9RJbQ1Kp5nWHcYpO9SW9dEsF-ydCoomKB_I6lbpnO_2PFxzq2Q841Mh7jFk6sLDAO8XmI"></div>
                                    <div class="stk-preview-img"><i class="fa-solid fa-sparkles text-primary fs-5"></i></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- MAIN WEB INTERACTIVE BROWSER SECTION (For PC, full navigation | For Mobile, intercepts) -->
    <section class="py-5 bg-black bg-opacity-30 border-top border-secondary border-opacity-10" id="browse-section">
        <div class="container text-center">
            
            <h2 class="fw-bold mb-3">Navegue e Descubra os Pacotes</h2>
            <p class="text-white-50 mb-5 max-width-600 mx-auto">
                No computador você pode pesquisar, filtrar categorias e abrir pacotes normalmente. No celular, use o nosso aplicativo nativo para adicionar ao WhatsApp!
            </p>

            <!-- SEARCH ENGINE BUTTON -->
            <div class="search-area mb-5">
                <i class="fa-solid fa-magnifying-glass text-white-50"></i>
                <input type="text" id="live-search-input" placeholder="Buscar figurinhas, pacotes ou criadores..." onkeyup="performLiveSearch()">
            </div>

            <!-- TABS CONTROLS (PACKS, CATEGORIES, FAVORITES, DOWNLOADS) -->
            <ul class="nav nav-tabs nav-tabs-custom" id="interactive-tabs" role="tablist">
                <li class="nav-item">
                    <button class="nav-link active" id="tab-packs" data-bs-toggle="tab" data-bs-target="#tab-content-packs" onclick="interceptTab('Pacotes')"><i class="fa-solid fa-box-open me-2"></i>Pacotes (Packs)</button>
                </li>
                <li class="nav-item">
                    <button class="nav-link" id="tab-categories" data-bs-toggle="tab" data-bs-target="#tab-content-cats" onclick="interceptTab('Categorias')"><i class="fa-solid fa-tags me-2"></i>Categorias</button>
                </li>
                <li class="nav-item">
                    <button class="nav-link" id="tab-favorites" data-bs-toggle="tab" data-bs-target="#tab-content-favs" onclick="interceptTab('Favoritos')"><i class="fa-solid fa-star me-2"></i>Favoritos</button>
                </li>
                <li class="nav-item">
                    <button class="nav-link" id="tab-downloads" data-bs-toggle="tab" data-bs-target="#tab-content-downs" onclick="interceptTab('Downloads')"><i class="fa-solid fa-cloud-arrow-down me-2"></i>Downloads Recentes</button>
                </li>
            </ul>

            <!-- CONTENT SLOTS -->
            <div class="tab-content" id="interactive-tabs-content">
                
                <!-- TAB 1: STICKER PACKS -->
                <div class="tab-pane fade show active" id="tab-content-packs" role="tabpanel">
                    
                    <div class="d-flex flex-wrap justify-content-center gap-2 mb-4" id="category-pills-bar">
                        <span class="category-pill active" onclick="filterByCat('Todos')">Todos</span>
                        <span class="category-pill" onclick="filterByCat('Memes')">😂 Memes</span>
                        <span class="category-pill" onclick="filterByCat('Animais')">🐱 Animais</span>
                        <span class="category-pill" onclick="filterByCat('Anime')">🌸 Anime</span>
                        <span class="category-pill" onclick="filterByCat('Frases')">💬 Frases</span>
                    </div>

                    <div class="row g-4 text-start" id="packages-web-list">
                        <!-- Pack 1 -->
                        <div class="col-lg-4 col-md-6 pack-card-item" data-cat="Animais" data-title="Cyber Gatos">
                            <div class="glass-card p-4 h-100" style="cursor:pointer;" onclick="openStickerPack('Cyber Gatos', 'NeonMochi', 'Animais', ['https://lh3.googleusercontent.com/aida-public/AB6AXuCtkSf5UeSaJUridQTq5N4vmoVsP0gexrgiYTdP0qRlbEs30ew908UhCNEi9m4LssAhXwVOg57u1e_ez-5g3TCOLjGUqA_-27lK6b3gjBo5fwN-xvC_bKUNHrOkf3ISxAXnFmmx8mWdAdYoIk0HajgPSXMuRMLYN6Jh-eRtFtqy4r1QWUPvDtru9uh3AoAcbANQjuMITfhzZ5Rkxqa6gWxcRXhsv9GA-oJYENfKNAiEofeImH6pZ-f27B90qgHjsVEMaAFiBSJb5Tg', 'https://lh3.googleusercontent.com/aida-public/AB6AXuA3wfyyh4fYujNiui8ykRW7sThV2EscYZZpuXEFUI3NbIeojw_q5XIjhtJdEBbvtm3fXWgkX4UrUX1db3BoaTMLIPTva7bXJyYDpMYL7t9XPotyDJ73vhvtYxh4TTsCtHbjFwB0iHI9Z3iMQfYO3eDIFPJpWzNE6RIj54lAQanMU5km61D3nyzx6c88sHI7KXZXyTjQAcnhhZ4NQTlnqxYzhetVswujz_XcYvqXC3brpw24bx52M5QPqVMlWJq2E6CwiI5cCH3-hjI', 'https://lh3.googleusercontent.com/aida-public/AB6AXuDD7tu1cGmRMHMaEKcw5RrpUj3EwSleP1nyuecTI2gqx-s1i6gw1p1zcCj9s0zdJhSWEbAzRK-CHR86zaV24La9Vhsh_5q1TEJtMYmJ_gNxQ1ZGzHnOG8hHd_PWTY04YjQCRu6MhXmGgfn7KNMmzhOdlf_7LDWx8rQa8r80ig7z5cCADJZ5iVwMqj180g7fqDuRSI2ZH4Xlyno0L1Y_NjPBMmm9Dn7V3ZYJcr1R2Tt5I6-rk5NbEk90_ZvI_JbVwypgKrZkQFcFJcw'])">
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <img src="https://lh3.googleusercontent.com/aida-public/AB6AXuCtkSf5UeSaJUridQTq5N4vmoVsP0gexrgiYTdP0qRlbEs30ew908UhCNEi9m4LssAhXwVOg57u1e_ez-5g3TCOLjGUqA_-27lK6b3gjBo5fwN-xvC_bKUNHrOkf3ISxAXnFmmx8mWdAdYoIk0HajgPSXMuRMLYN6Jh-eRtFtqy4r1QWUPvDtru9uh3AoAcbANQjuMITfhzZ5Rkxqa6gWxcRXhsv9GA-oJYENfKNAiEofeImH6pZ-f27B90qgHjsVEMaAFiBSJb5Tg" class="rounded-4 mb-3" style="width: 70px; height: 70px; object-fit: cover;" alt="Capa">
                                    <span class="badge bg-warning text-dark py-1 px-3 rounded-pill fw-bold" style="font-size: 11px;">PRÓ</span>
                                </div>
                                <h4 class="fw-bold m-0">Cyber Gatos 🐾</h4>
                                <p class="text-white-50 small mb-3">Por NeonMochi • 9 stickers</p>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="small text-white-50"><i class="fa-solid fa-download me-1"></i>12.4k downloads</span>
                                    <strong class="text-primary small">ABRIR PACOTE <i class="fa-solid fa-arrow-right"></i></strong>
                                </div>
                            </div>
                        </div>

                        <!-- Pack 2 -->
                        <div class="col-lg-4 col-md-6 pack-card-item" data-cat="Memes" data-title="Memes Clássicos">
                            <div class="glass-card p-4 h-100" style="cursor:pointer;" onclick="openStickerPack('Memes Clássicos', 'MemeGenerator', 'Memes', ['https://lh3.googleusercontent.com/aida-public/AB6AXuDSlXNY5xsvukq1cD3BhF6kpieE4o3vgOZk8YylLlfBhEquaGujYIHv4QQ9rAub4A1DRMFUzHjM5xKHdC4OOWlgHdsfpPqOtx19A8WQpiXCfpQib90cmaTUY9Sx-DoPGsbRmU5dJHDGht4bQiF6BQJx_DnuOAOJ0QvkzKsheFIsaS6MRg__iO_2QRhKn_Eo8Yl1PaD_58ALnKDr2xow6qNf4XwSrYsK3L95amcKQHj92dp8jVKK-v13i9K328iNg706PgT3oGpFGg0', 'https://lh3.googleusercontent.com/aida-public/AB6AXuDCEhsusl_vHLmq-btvaoNk1RDEPzfyw71BI-3r_cnoWB-90By9kBKV6r5LO1ztM91Re5YPVDne5hUFsdWTGu-Rz-hGJPmFvffVsZrzyZ1BRTz0C9H-XLvSEHeJ1PIBy_p5I7u1V9RDezS6GKLziKOCN4R_4yIxyymTGy3qGOfU7_UMj7d8x0UiewTNVcLSQ5w2JcGXmn9RJbQ1Kp5nWHcYpO9SW9dEsF-ydCoomKB_I6lbpnO_2PFxzq2Q841Mh7jFk6sLDAO8XmI'])">
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <img src="https://lh3.googleusercontent.com/aida-public/AB6AXuDSlXNY5xsvukq1cD3BhF6kpieE4o3vgOZk8YylLlfBhEquaGujYIHv4QQ9rAub4A1DRMFUzHjM5xKHdC4OOWlgHdsfpPqOtx19A8WQpiXCfpQib90cmaTUY9Sx-DoPGsbRmU5dJHDGht4bQiF6BQJx_DnuOAOJ0QvkzKsheFIsaS6MRg__iO_2QRhKn_Eo8Yl1PaD_58ALnKDr2xow6qNf4XwSrYsK3L95amcKQHj92dp8jVKK-v13i9K328iNg706PgT3oGpFGg0" class="rounded-4 mb-3" style="width: 70px; height: 70px; object-fit: cover;" alt="Capa">
                                    <span class="badge bg-secondary text-white py-1 px-3 rounded-pill fw-bold" style="font-size: 11px;">GRÁTIS</span>
                                </div>
                                <h4 class="fw-bold m-0">Memes Clássicos 😂</h4>
                                <p class="text-white-50 small mb-3">Por MemeGenerator • 15 stickers</p>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="small text-white-50"><i class="fa-solid fa-download me-1"></i>3.2k downloads</span>
                                    <strong class="text-primary small">ABRIR PACOTE <i class="fa-solid fa-arrow-right"></i></strong>
                                </div>
                            </div>
                        </div>

                        <!-- Pack 3 -->
                        <div class="col-lg-4 col-md-6 pack-card-item" data-cat="Anime" data-title="Sakura & Friends">
                            <div class="glass-card p-4 h-100" style="cursor:pointer;" onclick="openStickerPack('Sakura & Friends', 'ChibiPixel', 'Anime', ['https://lh3.googleusercontent.com/aida-public/AB6AXuB_Z0tqDaC3KJVQ3A6aBXvdaiZwlLGqBgvZdC_z0ClI1HEAN89XuPVS3IFXXrQReuzm3VlVdhV4P0EW73kRmqoMGDyALMdWafrpY-4Yn5niG-2yrSBgL0dEriunRsqvZ92O8za8DmAajIfFNL_Ew53xRDUeRwKVKcdshYFnIW5jZah1NpWcm76G9iNJgw_QolKpqw-5l-giHkcDD52SKgFLnmlmgD948Bajuedke3tGzv4s7-SO-tQxNGvKnVSH0mnBQGu17OVBwh0'])">
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <img src="https://lh3.googleusercontent.com/aida-public/AB6AXuB_Z0tqDaC3KJVQ3A6aBXvdaiZwlLGqBgvZdC_z0ClI1HEAN89XuPVS3IFXXrQReuzm3VlVdhV4P0EW73kRmqoMGDyALMdWafrpY-4Yn5niG-2yrSBgL0dEriunRsqvZ92O8za8DmAajIfFNL_Ew53xRDUeRwKVKcdshYFnIW5jZah1NpWcm76G9iNJgw_QolKpqw-5l-giHkcDD52SKgFLnmlmgD948Bajuedke3tGzv4s7-SO-tQxNGvKnVSH0mnBQGu17OVBwh0" class="rounded-4 mb-3" style="width: 70px; height: 70px; object-fit: cover;" alt="Capa">
                                    <span class="badge bg-warning text-dark py-1 px-3 rounded-pill fw-bold" style="font-size: 11px;">PRÓ</span>
                                </div>
                                <h4 class="fw-bold m-0">Sakura & Friends 🌸</h4>
                                <p class="text-white-50 small mb-3">Por ChibiPixel • 6 stickers</p>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="small text-white-50"><i class="fa-solid fa-download me-1"></i>4k downloads</span>
                                    <strong class="text-primary small">ABRIR PACOTE <i class="fa-solid fa-arrow-right"></i></strong>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- TAB 2: CATEGORY DIRECTORY -->
                <div class="tab-pane fade" id="tab-content-cats" role="tabpanel">
                    <div class="row g-4 text-start">
                        <div class="col-md-3">
                            <div class="glass-card p-4 text-center" style="cursor:pointer;" onclick="filterByCatAndSwitch('Memes')">
                                <span class="fs-1 d-block mb-3">😂</span>
                                <h5 class="fw-bold">Memes</h5>
                                <span class="text-white-50 small">120 Pacotes vinculados</span>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="glass-card p-4 text-center" style="cursor:pointer;" onclick="filterByCatAndSwitch('Animais')">
                                <span class="fs-1 d-block mb-3">🐱</span>
                                <h5 class="fw-bold">Animais</h5>
                                <span class="text-white-50 small">84 Pacotes vinculados</span>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="glass-card p-4 text-center" style="cursor:pointer;" onclick="filterByCatAndSwitch('Anime')">
                                <span class="fs-1 d-block mb-3">🌸</span>
                                <h5 class="fw-bold">Anime</h5>
                                <span class="text-white-50 small">65 Pacotes vinculados</span>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="glass-card p-4 text-center" style="cursor:pointer;" onclick="filterByCatAndSwitch('Frases')">
                                <span class="fs-1 d-block mb-3">💬</span>
                                <h5 class="fw-bold">Frases</h5>
                                <span class="text-white-50 small">45 Pacotes vinculados</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- TAB 3: FAVORITES SIMULATED -->
                <div class="tab-pane fade" id="tab-content-favs" role="tabpanel">
                    <div class="glass-card p-5 text-center d-flex flex-column align-items-center justify-content-center" style="min-height: 250px;">
                        <i class="fa-solid fa-star text-warning mb-3" style="font-size: 50px;"></i>
                        <h4 class="fw-bold">Seus Favoritos Sincronizados</h4>
                        <p class="text-white-50 small max-width-600 mb-4">
                            Para sincronizar seus pacotes de figurinhas favoritos entre todos os dispositivos, use o aplicativo oficial e mantenha sua conta logada com segurança.
                        </p>
                        <button class="btn btn-premium-accent" onclick="triggerAppDownload()"><i class="fa-brands fa-android me-2"></i>OBTER APP PREMIUM</button>
                    </div>
                </div>

                <!-- TAB 4: RECENT DOWNLOAD HISTORY -->
                <div class="tab-pane fade" id="tab-content-downs" role="tabpanel">
                    <div class="glass-card p-5 text-center d-flex flex-column align-items-center justify-content-center" style="min-height: 250px;">
                        <i class="fa-solid fa-clock-rotate-left text-info mb-3" style="font-size: 50px;"></i>
                        <h4 class="fw-bold">Histórico de Downloads</h4>
                        <p class="text-white-50 small max-width-600 mb-4">
                            Consulte os pacotes que você adicionou recentemente no WhatsApp. Disponível com segurança offline e backup na nuvem através do app Android.
                        </p>
                        <button class="btn btn-premium-accent" onclick="triggerAppDownload()">DOWNLOAD COMPLETO NO CELULAR</button>
                    </div>
                </div>

            </div>

            <!-- LATERAL DESKTOP AD BANNER inside browser -->
            <div class="side-promo-banner mx-auto mt-5" style="max-width: 800px;">
                <div class="row align-items-center g-3">
                    <div class="col-md-8 text-md-start text-center">
                        <strong class="d-block text-warning small mb-1"><i class="fa-solid fa-circle-check me-2"></i>BANNER RECOMENDADO</strong>
                        <h4 class="fw-bold text-white mb-2">Envie Figurinhas diretamente do Navegador para o Android!</h4>
                        <p class="text-white-50 small mb-0">Instale o aplicativo de figurinhas para permitir integração direta com WhatsApp Web e manter suas criações seguras na nuvem.</p>
                    </div>
                    <div class="col-md-4 text-md-end text-center">
                        <button class="btn btn-premium-accent" onclick="triggerAppDownload()"><i class="fa-solid fa-bolt me-2"></i>VINCULAR COM WHATSAPP</button>
                    </div>
                </div>
            </div>

        </div>
    </section>

    <!-- SECTION ADVANTAGES -->
    <section id="features-section" class="py-5 bg-black bg-opacity-20 border-top border-secondary border-opacity-10">
        <div class="container text-center">
            <h2 class="fw-bold mb-3">Vantagens Exclusivas do Nosso App Android</h2>
            <p class="text-white-50 mb-5 max-width-600 mx-auto">Porque usar o aplicativo nativo em vez de navegar de forma manual</p>
            
            <div class="row g-4 text-start justify-content-center">
                <div class="col-md-4">
                    <div class="glass-card p-4 h-100">
                        <span class="fs-2 text-primary mb-3 d-inline-block"><i class="fa-solid fa-bolt"></i></span>
                        <h4 class="fw-bold">Velocidade Máxima</h4>
                        <p class="text-white-50 mb-0">Adicione qualquer pack de sticker em menos de 1 segundo diretamente nas pastas locais do WhatsApp.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="glass-card p-4 h-100">
                        <span class="fs-2 text-primary mb-3 d-inline-block"><i class="fa-solid fa-rectangle-ad text-danger"></i></span>
                        <h4 class="fw-bold text-white">Sem Anúncios Abusivos</h4>
                        <p class="text-white-50 mb-0">Exibição de propaganda controlada de forma saudável pela rede oficial do Google AdMob, sem vírus!</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="glass-card p-4 h-100">
                        <span class="fs-2 text-primary mb-3 d-inline-block"><i class="fa-solid fa-wand-magic-sparkles text-info"></i></span>
                        <h4 class="fw-bold">Packs 100% Exclusivos</h4>
                        <p class="text-white-50 mb-0">Acesso livre a packs premium criados por artistas licenciados e designers sob assinatura.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- REVIEWS SECTION -->
    <section id="reviews-section" class="py-5">
        <div class="container text-center">
            <h2 class="fw-bold mb-3">Avaliações Positivas</h2>
            <p class="text-white-50 mb-5 max-width-600 mx-auto">Mais de 4.9 estrelas verificadas na comunidade</p>
            
            <div class="row g-4 text-start">
                <div class="col-md-4">
                    <div class="glass-card p-4 h-100">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span class="fw-bold text-white small">Carlos Magno</span>
                            <span class="text-warning small">★★★★★</span>
                        </div>
                        <p class="text-white-50 small mb-0">"O melhor aplicativo de figurinhas para o WhatsApp que já testei! O visual é muito bonito, não tem anúncios abusivos de outros sites e o envio pro WhatsApp é instantâneo."</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="glass-card p-4 h-100">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span class="fw-bold text-white small">Amanda Souza</span>
                            <span class="text-warning small">★★★★★</span>
                        </div>
                        <p class="text-white-50 small mb-0">"O layout com neon e dark mode é maravilhoso. Adicionei os Cyber Gatos e meus amigos do grupo amaram, são super engraçados e funcionam no teclado do WhatsApp perfeitamente."</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="glass-card p-4 h-100">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span class="fw-bold text-white small">Rodrigo Dev</span>
                            <span class="text-warning small">★★★★★</span>
                        </div>
                        <p class="text-white-50 small mb-0">"Funciona super rápido, os links de download direto são limpos. Trabalho excelente com as tecnologias de renderização de stickers webe. Super recomendo a todos o download."</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- FOOTER -->
    <footer class="py-5 border-top border-secondary border-opacity-10 text-center text-white-50" style="background:#04060a;">
        <div class="container">
            <h5 class="navbar-brand mb-4 d-inline-block"><i class="fa-solid fa-wand-magic-sparkles me-2"></i><?= htmlspecialchars($app_name) ?></h5>
            <div class="d-flex justify-content-center gap-4 mb-4 small">
                <a href="<?= htmlspecialchars($policy_url) ?>" class="text-white-50 text-decoration-none">Políticas de Privacidade</a>
                <a href="<?= htmlspecialchars($terms_url) ?>" class="text-white-50 text-decoration-none">Termos de Uso</a>
                <a href="admin/" class="text-white-50 text-decoration-none">Acesso Administrator</a>
            </div>
            <p class="small mb-0">&copy; 2026 <?= htmlspecialchars($app_name) ?>. Desenvolvido para a melhor experiência mobile.</p>
        </div>
    </footer>

    <!-- INTERACTIVE FLOATING QR WIDGET (DESKTOP MODE) -->
    <div class="floating-qr-widget" id="desktop-qr">
        <strong class="text-white d-block mb-2"><i class="fa-solid fa-qrcode text-primary me-2"></i>Instalar no Celular</strong>
        <p class="text-white-50 small mb-3">Aponte a câmera do seu celular Android para escanear e carregar o APK agora!</p>
        
        <div class="bg-white p-3 rounded-4 d-inline-block mb-3">
            <svg width="120" height="120" viewBox="0 0 100 100">
                <rect x="0" y="0" width="30" height="30" fill="#000" />
                <rect x="5" y="5" width="20" height="20" fill="#fff" />
                <rect x="10" y="10" width="10" height="10" fill="#000" />
                
                <rect x="70" y="0" width="30" height="30" fill="#000" />
                <rect x="75" y="5" width="20" height="20" fill="#fff" />
                <rect x="80" y="10" width="10" height="10" fill="#000" />
                
                <rect x="0" y="70" width="30" height="30" fill="#000" />
                <rect x="5" y="75" width="20" height="20" fill="#fff" />
                <rect x="10" y="80" width="10" height="10" fill="#000" />
                
                <rect x="40" y="10" width="10" height="10" fill="#000" />
                <rect x="50" y="20" width="10" height="10" fill="#000" />
                <rect x="40" y="40" width="20" height="20" fill="#000" />
                <rect x="80" y="40" width="10" height="10" fill="#000" />
                <rect x="50" y="70" width="15" height="15" fill="#000" />
                <rect x="80" y="80" width="15" height="15" fill="#000" />
            </svg>
        </div>
        <span class="badge bg-primary text-dark fw-bold w-100 py-2">✓ 100% SEGURO E VERIFICADO</span>
    </div>

    <!-- FLOATING FIXED MOBILE CTA BUTTON (FOR MOBILE) -->
    <div class="mobile-floating-install-cta">
        <button class="btn btn-premium-accent w-100 py-3 rounded-pill shadow-lg d-flex align-items-center justify-content-center" onclick="triggerAppDownload()">
            <i class="fa-brands fa-android fs-4 me-2"></i>BAIXAR FIGURINHAS AGORA
        </button>
    </div>

    <!-- RESTRICTION BLUR / POPUP MODAL -->
    <div class="restriced-overlay" id="access-lock-overlay">
        <div class="glass-card p-4 text-center w-100" style="max-width:450px;">
            <div class="mb-3 text-warning"><i class="fa-solid fa-cloud-arrow-down" style="font-size:48px;"></i></div>
            
            <h3 class="fw-bold text-white mb-2" id="block-modal-title">Obter Aplicativo Android</h3>
            <p class="text-white-50 small mb-4" id="block-modal-desc">
                Para desbloquear o acesso total a pacotes de figurinhas dinâmicos, buscar termos, abrir pacotes e sincronizar downloads com o WhatsApp, instale o aplicativo oficial.
            </p>

            <ul class="list-group list-group-flush bg-transparent border-0 text-start mb-4" style="font-size:12px;">
                <li class="list-group-item bg-transparent text-white-50 border-secondary border-opacity-20 px-0 d-flex align-items-center">
                    <i class="fa-solid fa-circle-check text-success me-2"></i> Sem anúncios excessivos do navegador
                </li>
                <li class="list-group-item bg-transparent text-white-50 border-secondary border-opacity-20 px-0 d-flex align-items-center">
                    <i class="fa-solid fa-circle-check text-success me-2"></i> Sincronização direta com o WhatsApp em 1 clique
                </li>
                <li class="list-group-item bg-transparent text-white-50 border-secondary border-opacity-20 px-0 d-flex align-items-center">
                    <i class="fa-solid fa-circle-check text-success me-2"></i> Biblioteca premium com mais de 832 figurinhas
                </li>
                <li class="list-group-item bg-transparent text-white border-0 px-0 d-flex align-items-center">
                    <i class="fa-solid fa-circle-check text-success me-2"></i> Atualizações semanais automáticas na nuvem
                </li>
            </ul>

            <div class="d-flex flex-column gap-2">
                <button class="btn btn-premium-accent w-100 py-3 rounded-3" onclick="triggerAppDownload()"><i class="fa-brands fa-android me-2"></i>BAIXAR AGORA</button>
                <button class="btn btn-outline-secondary w-100 border-0 text-white-50 small" onclick="dismissRestrictionLock()">CONTINUAR NO SITE</button>
            </div>
        </div>
    </div>

    <!-- DETAILED LIVE STICKER PACK DETAILS DIALOG MODAL (FOR PC/DESKTOP) -->
    <div class="modal fade" id="packDetailsModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content bg-dark text-white border-secondary rounded-4">
                <div class="modal-header border-secondary">
                    <h5 class="modal-title font-weight-bold" id="detail-modal-pack-name">Sticker Pack</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center">
                    <div class="mb-3">
                        <span class="badge bg-primary text-dark me-2" id="detail-modal-pack-cat">Categoria</span>
                        <span class="text-white-50 small" id="detail-modal-pack-creator">Por Autor</span>
                    </div>

                    <b class="d-block mb-3 text-start small text-white-50">Stickers inclusos neste pacote (toque para salvar):</b>
                    <div class="row g-3 row-cols-3 justify-content-center mb-4" id="detail-modal-stickers-grid">
                        <!-- Stickers loaded in javascript dynamically -->
                    </div>

                    <div class="p-3 rounded-4 d-flex justify-content-between align-items-center mb-3" style="background: rgba(255,255,255,0.02); border: 1px solid var(--border-glass)">
                        <div class="text-start">
                            <strong class="d-block text-white" style="font-size: 13px;">📲 Adicionar ao WhatsApp?</strong>
                            <span class="text-white-50 small">Clique abaixo no botão para carregar e injetar o pack instantaneamente.</span>
                        </div>
                    </div>

                    <button class="btn btn-premium-accent w-100 py-3 rounded-3" onclick="triggerAppDownload()"><i class="fa-brands fa-android me-2"></i>ADICIONAR STICKERS AO WHATSAPP</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        const isMobileDevice = <?= $is_mobile ? 'true' : 'false' ?>;
        
        let dismissCount = parseInt(localStorage.getItem('alert_dismiss_count') || '0');
        let snoozeTimestamp = parseInt(localStorage.getItem('alert_snooze_time') || '0');
        let installed = localStorage.getItem('app_installed') === 'true';

        window.addEventListener('load', () => {
            animateEntryCounters();

            // SQUISH BANNER ON LOAD
            if (isMobileDevice) {
                document.getElementById('app-smart-banner').style.display = 'flex';
            } else {
                document.getElementById('app-smart-banner').style.display = 'none';
            }

            // AUTO TRIGGER DELIGHT DESKTOP POPUP after 5 seconds on desktop
            if (!isMobileDevice && shouldShowRecommendationPopup()) {
                setTimeout(() => {
                    triggerSubtleDesktopPopup();
                }, 5000);
            }
        });

        function animateEntryCounters() {
            let downloadsObj = document.getElementById('hero-num-downloads');
            let packsObj = document.getElementById('hero-num-packs');

            if(downloadsObj) {
                animateCounter(downloadsObj, 1000, 42500, 'k+ Instalações');
            }
            if(packsObj) {
                animateCounter(packsObj, 10, 1200, '+ Packs');
            }
        }

        function animateCounter(obj, start, end, suffix) {
            let current = start;
            let range = end - start;
            let increment = Math.ceil(range / 60);
            let timer = setInterval(() => {
                current += increment;
                if (current >= end) {
                    current = end;
                    clearInterval(timer);
                }
                obj.innerText = formatNumber(current) + suffix;
            }, 30);
        }

        function formatNumber(num) {
            if (num >= 1000) {
                return (num / 1000).toFixed(1);
            }
            return num;
        }

        // DISMISS COUNTING FOR SMART PROMPTS (Snooze after 3 declines)
        function registerDismiss() {
            dismissCount = parseInt(localStorage.getItem('alert_dismiss_count') || '0') + 1;
            localStorage.setItem('alert_dismiss_count', dismissCount.toString());
            console.log('Action dismiss registered. Total count: ' + dismissCount);
            if (dismissCount >= 3) {
                // Snooze for 7 days
                localStorage.setItem('alert_snooze_time', Date.now().toString());
            }
        }

        function shouldShowRecommendationPopup() {
            if (installed) return false;
            let currentTime = Date.now();
            if (snoozeTimestamp > 0 && (currentTime - snoozeTimestamp) < (7 * 24 * 3600 * 1000)) {
                return false;
            }
            if (dismissCount >= 3) {
                return false;
            }
            return true;
        }

        function dismissSmartBanner() {
            document.getElementById('app-smart-banner').style.display = 'none';
            registerDismiss();
        }

        function triggerAppDownload() {
            localStorage.setItem('app_installed', 'true');
            alert('Excelente escolha! Iniciando download direto do arquivo APK otimizado para o seu dispositivo...');
            dismissRestrictionLock();
        }

        function triggerStoreDownload() {
            localStorage.setItem('app_installed', 'true');
            console.log('Redirecting to Google Play store location.');
        }

        // COMPORTAMENTO MOBILE (SE FOR CELL/TABLET ANDROID)
        // Bloquear algumas ações ao clicar em: Categorias, Packs, Favoritos, Downloads, Buscar, Abrir pacote
        function interceptTab(tabName) {
            if (isMobileDevice) {
                event.preventDefault();
                event.stopPropagation();
                
                showRestrictionLock(
                    'Instalar para Acessar ' + tabName,
                    'A página de ' + tabName + ' requer que as informações estejam carregadas localmente no seu dispositivo móvel. Use o app para uma experiência impecável.'
                );
            }
        }

        function interceptBrowse(label) {
            if (isMobileDevice) {
                showRestrictionLock(
                    'Recurso Bloqueado no Celular',
                    'Aproveitar o recurso de ' + label + ' é exclusivo para usuários do aplicativo oficial Sticker Store no Android.'
                );
            }
        }

        function performLiveSearch() {
            if (isMobileDevice) {
                showRestrictionLock(
                    'Pesquisa Exclusiva do App',
                    'Mecanismo inteligente de busca global acelerado necessita de indexação de banco de dados nativo SQLite. Descubra tudo instalando o app.'
                );
                document.getElementById('live-search-input').value = '';
                return;
            }
            performDesktopSearch();
        }

        function filterByCat(catName) {
            if (isMobileDevice) {
                showRestrictionLock(
                    'Filtrar Categorias em Alta',
                    'O filtro de categoria "' + catName + '" requer conexão segura síncrona habilitada apenas dentro da plataforma oficial no aplicativo.'
                );
                return;
            }
            
            // Desktop normal browse: filter rows
            const pills = document.querySelectorAll('.category-pill');
            pills.forEach(p => p.classList.remove('active'));
            event.target.classList.add('active');

            const cards = document.querySelectorAll('.pack-card-item');
            cards.forEach(card => {
                if (catName === 'Todos' || card.getAttribute('data-cat') === catName) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
        }

        function filterByCatAndSwitch(catName) {
            // Switch tab to packs & trigger filter
            const packTab = new bootstrap.Tab(document.getElementById('tab-packs'));
            packTab.show();
            
            setTimeout(() => {
                const pills = document.querySelectorAll('.category-pill');
                pills.forEach(p => {
                    if (p.innerText.includes(catName)) {
                        p.click();
                    }
                });
            }, 100);
        }

        function performDesktopSearch() {
            let val = document.getElementById('live-search-input').value.toLowerCase().trim();
            const cards = document.querySelectorAll('.pack-card-item');
            cards.forEach(card => {
                let title = card.getAttribute('data-title').toLowerCase();
                let cat = card.getAttribute('data-cat').toLowerCase();
                if (title.includes(val) || cat.includes(val)) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
        }

        function openStickerPack(name, creator, category, stickerUrls) {
            if (isMobileDevice) {
                showRestrictionLock(
                    'Exibir Figurinhas do Pacote',
                    'Você tentou acessar o pacote completo de "' + name + '". Instale o app oficial Android para visualizar e adicionar esses adesivos direto no teclado.'
                );
                return;
            }

            // Normal Desktop navigation: Show a gorgeous, beautiful modern listing pop-up detailing the content!
            document.getElementById('detail-modal-pack-name').innerText = name + ' ✦ Pack Detalhes';
            document.getElementById('detail-modal-pack-creator').innerText = 'Criador: ' + creator;
            document.getElementById('detail-modal-pack-cat').innerText = 'Categoria: ' + category;

            const grid = document.getElementById('detail-modal-stickers-grid');
            grid.innerHTML = '';
            
            stickerUrls.forEach(url => {
                let div = document.createElement('div');
                div.className = 'col pack-grid-item';
                div.innerHTML = '<img src="' + url + '" style="max-width: 100%; max-height:80px; object-fit: contain;">';
                grid.appendChild(div);
            });

            // Trigger modal popup
            let detailModal = new bootstrap.Modal(document.getElementById('packDetailsModal'));
            detailModal.show();
        }

        // DESKTOP UNIQUE: Automatic subtle popup trigger
        function triggerSubtleDesktopPopup() {
            showRestrictionLock(
                '✦ Instale no seu WhatsApp Celular',
                'Preparamos novidades incríveis de alta performance para sincronizar esses stickers diretamente com os seus grupos no Android!'
            );
        }

        function showRestrictionLock(title, description) {
            document.getElementById('block-modal-title').innerText = title;
            document.getElementById('block-modal-desc').innerText = description;
            document.getElementById('access-lock-overlay').style.display = 'flex';
        }

        function dismissRestrictionLock() {
            document.getElementById('access-lock-overlay').style.display = 'none';
            registerDismiss();
        }
    </script>
</body>
</html>
