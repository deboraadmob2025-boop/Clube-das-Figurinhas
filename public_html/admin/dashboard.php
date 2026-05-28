<?php
session_start();
// Redirect if not logged in
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: index.php");
    exit;
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
    <!-- FontAwesome Vector Icons -->
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

        /* Glassmorphism sidebar */
        .sidebar {
            background: rgba(255, 255, 255, 0.03);
            backdrop-filter: blur(20px);
            border-right: 1px solid rgba(255, 255, 255, 0.05);
            min-height: 100vh;
            position: fixed;
            width: 260px;
            z-index: 100;
            padding: 24px;
            transition: all 0.3s;
        }

        body.light-theme .sidebar {
            background: rgba(255, 255, 255, 0.85);
            border-right: 1px solid rgba(0, 0, 0, 0.08);
        }

        .main-content {
            margin-left: 260px;
            padding: 40px;
            min-height: 100vh;
        }

        .side-logo {
            font-size: 24px;
            font-weight: 700;
            background: linear-gradient(120deg, #6cf8bb, #6200ee);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 40px;
        }

        .nav-link-custom {
            color: rgba(255, 255, 255, 0.65);
            padding: 12px 18px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            text-decoration: none;
            font-weight: 500;
            margin-bottom: 8px;
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
            margin-right: 15px;
            font-size: 18px;
        }

        /* Glass Cards */
        .glass-card {
            background: rgba(255, 255, 255, 0.03);
            backdrop-filter: blur(15px);
            border: 1px solid rgba(255, 255, 255, 0.05);
            border-radius: 20px;
            padding: 24px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
            transition: transform 0.3s;
        }

        body.light-theme .glass-card {
            background: #ffffff;
            border: 1px solid rgba(0, 0, 0, 0.06);
            box-shadow: 0 8px 24px rgba(149, 157, 165, 0.1);
        }

        .glass-card:hover {
            transform: translateY(-4px);
        }

        .stat-num {
            font-size: 32px;
            font-weight: 700;
        }

        /* Table custom styling */
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

        .custom-tbl td {
            color: inherit;
            vertical-align: middle;
        }

        /* DropZone Drag & Drop simulator styling */
        .dropzone-box {
            border: 2px dashed rgba(255, 255, 255, 0.15);
            border-radius: 16px;
            background: rgba(255, 255, 255, 0.01);
            transition: all 0.3s;
            cursor: pointer;
        }

        body.light-theme .dropzone-box {
            border: 2px dashed rgba(0, 0, 0, 0.15);
        }

        .dropzone-box:hover {
            border-color: #6cf8bb;
            background: rgba(108, 248, 187, 0.03);
        }

        /* Header controls */
        .header-controls {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 40px;
        }

        /* Badge design */
        .badge-premium {
            background-color: rgba(108, 248, 187, 0.1);
            color: #6cf8bb;
            border: 1px solid rgba(108, 248, 187, 0.2);
        }

        body.light-theme .badge-premium {
            background-color: rgba(0, 108, 73, 0.1);
            color: #006c49;
        }

        /* Toggle Button styling */
        .form-switch .form-check-input {
            width: 3em;
            height: 1.5em;
        }

        /* Premium active logs panel */
        .log-box {
            background: #03070b;
            font-family: 'Courier New', Courier, monospace;
            padding: 16px;
            border-radius: 12px;
            font-size: 12px;
            color: #a0aec0;
            max-height: 200px;
            overflow-y: auto;
        }
    </style>
</head>
<body>

    <!-- SIDEBAR -->
    <div class="sidebar">
        <div class="side-logo">
            <i class="fa-solid fa-wand-magic-sparkles me-2 text-[#6cf8bb]"></i>StickerStore
        </div>
        
        <nav class="nav flex-column">
            <a class="nav-link-custom active" href="#" onclick="showSection('dashboard', this)">
                <i class="fa-solid fa-chart-pie"></i>Dashboard
            </a>
            <a class="nav-link-custom" href="#" onclick="showSection('packs', this)">
                <i class="fa-solid fa-box-open"></i>Pacotes
            </a>
            <a class="nav-link-custom" href="#" onclick="showSection('categories', this)">
                <i class="fa-solid fa-tags"></i>Categorias
            </a>
            <a class="nav-link-custom" href="#" onclick="showSection('users', this)">
                <i class="fa-solid fa-users"></i>Usuários
            </a>
            <a class="nav-link-custom" href="#" onclick="showSection('push', this)">
                <i class="fa-solid fa-paper-plane"></i>Notificações Push
            </a>
            <a class="nav-link-custom" href="#" onclick="showSection('ads', this)">
                <i class="fa-solid fa-rectangle-ad"></i>Monetização AdMob
            </a>
            <a class="nav-link-custom" href="#" onclick="showSection('settings', this)">
                <i class="fa-solid fa-sliders"></i>Configurações
            </a>
        </nav>

        <div style="position: absolute; bottom: 24px; left: 24px; right: 24px;">
            <hr class="border-secondary">
            <div class="d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center">
                    <img src="https://lh3.googleusercontent.com/aida-public/AB6AXuC9ceainRre_8dWZ_Pyjjgy3svsrxKmotvJhGWt0NM7a4AsqBV9eNHOcIbnq2nWzbocBh-FR_O29iCzwQCGqKyC0-LWj9b3MnKbWxG97tKrzcJ4hG0co1ooyshCUzotds7vcXWGdtfmGlFKR7EcOnfNVkQW5vgZ1cRG-UQf4r7PNy9XvLEsJc2YhuT6CXNiyFVklSGlEMod8Qg790QESXP8_fNwquBCzmKKApJf7Xe40ypwp0joP26AY6zY7c6F3DxddF1V1Ttdk_s" class="rounded-circle me-2" width="36" alt="Avatar">
                    <div style="font-size: 13px;">
                        <strong class="d-block">Diretor Master</strong>
                        <span class="text-white-50">admin</span>
                    </div>
                </div>
                <a href="index.php" class="text-danger" title="Desconectar"><i class="fa-solid fa-right-from-bracket"></i></a>
            </div>
        </div>
    </div>

    <!-- MAIN CONTENT -->
    <div class="main-content">
        
        <!-- HEADER -->
        <div class="header-controls">
            <div>
                <h2 class="fw-bold mb-1" id="section-title">Dashboard Principal</h2>
                <span class="text-white-50" id="section-subtitle">Estatísticas gerais de downloads e interatividade.</span>
            </div>
            
            <div class="d-flex align-items-center gap-3">
                <!-- Theme toggle -->
                <button class="btn btn-outline-secondary rounded-circle" id="theme-button" onclick="toggleTheme()" title="Trocar Tema">
                    <i class="fa-solid fa-sun-bright" id="theme-icon">🔆</i>
                </button>
                
                <div class="btn-group">
                    <button type="button" class="btn btn-success d-flex align-items-center text-dark fw-bold" onclick="executeBackup()">
                        <i class="fa-solid fa-cloud-arrow-down me-2"></i>Backup Auto
                    </button>
                </div>
            </div>
        </div>

        <!-- ======================= SECTION: DASHBOARD (HOME) ======================= -->
        <div class="admin-section" id="section-dashboard">
            <!-- 4 Stat metrics -->
            <div class="row g-4 mb-4">
                <div class="col-md-3">
                    <div class="glass-card text-start">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <span class="text-white-50 small font-weight-bold">Total Usuários</span>
                            <span class="bg-primary bg-opacity-10 p-2 rounded-circle text-primary"><i class="fa-solid fa-user-group"></i></span>
                        </div>
                        <div class="stat-num text-white" id="stat-total-users">4,812</div>
                        <span class="text-success small fw-bold"><i class="fa-solid fa-arrow-up me-1"></i>+12% este mês</span>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="glass-card text-start">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <span class="text-white-50 small font-weight-bold">Downloads Realizados</span>
                            <span class="bg-success bg-opacity-10 p-2 rounded-circle text-success"><i class="fa-solid fa-download"></i></span>
                        </div>
                        <div class="stat-num text-white" id="stat-total-downloads">12,450</div>
                        <span class="text-success small fw-bold"><i class="fa-solid fa-arrow-up me-1"></i>+18.3% hoje</span>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="glass-card text-start">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <span class="text-white-50 small font-weight-bold">Pacotes de Figurinhas</span>
                            <span class="bg-warning bg-opacity-10 p-2 rounded-circle text-warning"><i class="fa-solid fa-box"></i></span>
                        </div>
                        <div class="stat-num text-white">42</div>
                        <span class="text-white-50 small">Ativos na loja</span>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="glass-card text-start">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <span class="text-white-50 small font-weight-bold">Figurinhas Totais</span>
                            <span class="bg-info bg-opacity-10 p-2 rounded-circle text-info"><i class="fa-solid fa-face-smile"></i></span>
                        </div>
                        <div class="stat-num text-white">832</div>
                        <span class="text-info small fw-bold"><i class="fa-solid fa-circle-check me-1"></i>webp otimizados</span>
                    </div>
                </div>
            </div>

            <!-- Graphs Container row -->
            <div class="row g-4 mb-4">
                <div class="col-md-8">
                    <div class="glass-card h-100">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h5 class="fw-bold m-0"><i class="fa-solid fa-chart-line me-2 text-primary"></i>Downloads Temporais</h5>
                            <select class="form-select form-select-sm w-auto bg-transparent border-secondary text-white">
                                <option value="7">Últimos 7 dias</option>
                                <option value="30">Últimos 30 dias</option>
                            </select>
                        </div>
                        <div style="height: 250px;" class="d-flex align-items-end justify-content-between pt-3 px-2">
                            <!-- Visual placeholder column simulation of a modern graph chart bar -->
                            <div class="text-center w-100">
                                <div class="bg-primary opacity-25 rounded-top" style="height: 48px; width: 30px; margin: 0 auto 8px;"></div>
                                <span class="small text-white-50">Seg</span>
                            </div>
                            <div class="text-center w-100">
                                <div class="bg-primary opacity-50 rounded-top" style="height: 82px; width: 30px; margin: 0 auto 8px;"></div>
                                <span class="small text-white-50">Ter</span>
                            </div>
                            <div class="text-center w-100">
                                <div class="bg-primary opacity-25 rounded-top" style="height: 110px; width: 30px; margin: 0 auto 8px;"></div>
                                <span class="small text-white-50">Qua</span>
                            </div>
                            <div class="text-center w-100">
                                <div class="bg-primary opacity-75 rounded-top" style="height: 140px; width: 30px; margin: 0 auto 8px;"></div>
                                <span class="small text-white-50">Qui</span>
                            </div>
                            <div class="text-center w-100">
                                <div class="bg-primary opacity-50 rounded-top" style="height: 120px; width: 30px; margin: 0 auto 8px;"></div>
                                <span class="small text-white-50">Sex</span>
                            </div>
                            <div class="text-center w-100">
                                <div class="bg-primary rounded-top" style="height: 184px; width: 30px; margin: 0 auto 8px;"></div>
                                <span class="small text-white-50">Sab</span>
                            </div>
                            <div class="text-center w-100">
                                <div class="bg-success rounded-top" style="height: 210px; width: 30px; margin: 0 auto 8px;"></div>
                                <span class="small text-success fw-bold">Dom</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="glass-card h-100">
                        <h5 class="fw-bold mb-4"><i class="fa-solid fa-circle-notch me-2 text-success"></i>Categorias Populares</h5>
                        <ul class="list-group list-group-flush bg-transparent border-0 mt-3 text-start">
                            <li class="list-group-item bg-transparent text-white border-secondary d-flex justify-content-between py-2 align-items-center">
                                <span>😂 Memes</span>
                                <span class="badge bg-secondary">42%</span>
                            </li>
                            <li class="list-group-item bg-transparent text-white border-secondary d-flex justify-content-between py-2 align-items-center">
                                <span>🐱 Animais</span>
                                <span class="badge bg-secondary">28%</span>
                            </li>
                            <li class="list-group-item bg-transparent text-white border-secondary d-flex justify-content-between py-2 align-items-center">
                                <span>🎮 Games</span>
                                <span class="badge bg-secondary">15%</span>
                            </li>
                            <li class="list-group-item bg-transparent text-white border-0 d-flex justify-content-between py-2 align-items-center">
                                <span>💖 Romântico</span>
                                <span class="badge bg-secondary">15%</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Tables log metrics -->
            <div class="row g-4">
                <div class="col-md-6 text-start">
                    <div class="glass-card">
                        <h5 class="fw-bold mb-3">Últimos Pacotes Cadastrados</h5>
                        <table class="table custom-tbl text-white">
                            <thead>
                                <tr>
                                    <th>Capa</th>
                                    <th>Nome</th>
                                    <th>Visualizações</th>
                                    <th>Privilégio</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><img src="https://lh3.googleusercontent.com/aida-public/AB6AXuCtkSf5UeSaJUridQTq5N4vmoVsP0gexrgiYTdP0qRlbEs30ew908UhCNEi9m4LssAhXwVOg57u1e_ez-5g3TCOLjGUqA_-27lK6b3gjBo5fwN-xvC_bKUNHrOkf3ISxAXnFmmx8mWdAdYoIk0HajgPSXMuRMLYN6Jh-eRtFtqy4r1QWUPvDtru9uh3AoAcbANQjuMITfhzZ5Rkxqa6gWxcRXhsv9GA-oJYENfKNAiEofeImH6pZ-f27B90qgHjsVEMaAFiBSJb5Tg" class="rounded" width="40"></td>
                                    <td><strong>Cyber Gatos</strong><br><small class="text-white-50">Animals</small></td>
                                    <td>1,250 downloads</td>
                                    <td><span class="badge bg-warning text-dark">PREMIUM</span></td>
                                </tr>
                                <tr>
                                    <td><img src="https://lh3.googleusercontent.com/aida-public/AB6AXuDSlXNY5xsvukq1cD3BhF6kpieE4o3vgOZk8YylLlfBhEquaGujYIHv4QQ9rAub4A1DRMFUzHjM5xKHdC4OOWlgHdsfpPqOtx19A8WQpiXCfpQib90cmaTUY9Sx-DoPGsbRmU5dJHDGht4bQiF6BQJx_DnuOAOJ0QvkzKsheFIsaS6MRg__iO_2QRhKn_Eo8Yl1PaD_58ALnKDr2xow6qNf4XwSrYsK3L95amcKQHj92dp8jVKK-v13i9K328iNg706PgT3oGpFGg0" class="rounded" width="40"></td>
                                    <td><strong>Retro Vibes</strong><br><small class="text-white-50">Gaming</small></td>
                                    <td>950 downloads</td>
                                    <td><span class="badge bg-secondary">GRÁTIS</span></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="col-md-6 text-start">
                    <div class="glass-card">
                        <h5 class="fw-bold mb-3">Últimos Logs Administrativos</h5>
                        <div class="log-box text-start">
                            [28/05/2026 00:02:11] ADMIN admin: Login efetuado com sucesso (IP 192.168.1.18)<br>
                            [28/05/2026 00:05:42] ADMIN admin: Atualizou AdMob IDs para produção.<br>
                            [28/05/2026 00:10:01] NOTIFICATION: FCM push 'Bons dias!' disparado com sucesso para todos os usuários.<br>
                            [28/05/2026 00:18:25] DATABASE: Backup periódico realizado e salvo em /database/backups/
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ======================= SECTION: PACKS GERENCIAMENTO ======================= -->
        <div class="admin-section d-none text-start" id="section-packs">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4 class="fw-bold m-0">Pacotes de Figurinhas</h4>
                <button class="btn btn-primary text-dark fw-bold" data-bs-toggle="modal" data-bs-target="#packModal">
                    <i class="fa-solid fa-plus me-2"></i>Novo Pacote
                </button>
            </div>

            <div class="glass-card">
                <table class="table custom-tbl text-white">
                    <thead>
                        <tr>
                            <th>Capa</th>
                            <th>Nome do Pacote</th>
                            <th>Criador</th>
                            <th>Categoria</th>
                            <th>Stickers</th>
                            <th>Downloads</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody id="packs-tbody">
                        <tr>
                            <td><img src="https://lh3.googleusercontent.com/aida-public/AB6AXuCtkSf5UeSaJUridQTq5N4vmoVsP0gexrgiYTdP0qRlbEs30ew908UhCNEi9m4LssAhXwVOg57u1e_ez-5g3TCOLjGUqA_-27lK6b3gjBo5fwN-xvC_bKUNHrOkf3ISxAXnFmmx8mWdAdYoIk0HajgPSXMuRMLYN6Jh-eRtFtqy4r1QWUPvDtru9uh3AoAcbANQjuMITfhzZ5Rkxqa6gWxcRXhsv9GA-oJYENfKNAiEofeImH6pZ-f27B90qgHjsVEMaAFiBSJb5Tg" class="rounded" width="48"></td>
                            <td><span class="fw-bold">Cyber Gatos</span> <span class="badge bg-warning text-dark ms-2">PREMIUM</span></td>
                            <td>NeonMochi</td>
                            <td>Animals</td>
                            <td>9 figurinhas</td>
                            <td>1250</td>
                            <td>
                                <button class="btn btn-sm btn-outline-info me-2" onclick="alert('Navegar para gerenciador de stickers individuais do pacote!')"><i class="fa-solid fa-images"></i></button>
                                <button class="btn btn-sm btn-outline-danger" onclick="deleteRow(this)"><i class="fa-solid fa-trash"></i></button>
                            </td>
                        </tr>
                        <tr>
                            <td><img src="https://lh3.googleusercontent.com/aida-public/AB6AXuDSlXNY5xsvukq1cD3BhF6kpieE4o3vgOZk8YylLlfBhEquaGujYIHv4QQ9rAub4A1DRMFUzHjM5xKHdC4OOWlgHdsfpPqOtx19A8WQpiXCfpQib90cmaTUY9Sx-DoPGsbRmU5dJHDGht4bQiF6BQJx_DnuOAOJ0QvkzKsheFIsaS6MRg__iO_2QRhKn_Eo8Yl1PaD_58ALnKDr2xow6qNf4XwSrYsK3L95amcKQHj92dp8jVKK-v13i9K328iNg706PgT3oGpFGg0" class="rounded" width="48"></td>
                            <td><span class="fw-bold">Retro Vibes</span></td>
                            <td>Synthwave_Artist</td>
                            <td>Gaming</td>
                            <td>6 figurinhas</td>
                            <td>950</td>
                            <td>
                                <button class="btn btn-sm btn-outline-info me-2" onclick="alert('Manage stickers!')"><i class="fa-solid fa-images"></i></button>
                                <button class="btn btn-sm btn-outline-danger" onclick="deleteRow(this)"><i class="fa-solid fa-trash"></i></button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- ======================= SECTION: CATEGORIES GERENCIAMENTO ======================= -->
        <div class="admin-section d-none text-start" id="section-categories">
            <div class="row">
                <div class="col-md-5">
                    <div class="glass-card">
                        <h5 class="fw-bold mb-3"><i class="fa-solid fa-tag me-2 text-warning"></i>Criar Categoria</h5>
                        <form id="category-form" onsubmit="addCategory(event)">
                            <div class="mb-3">
                                <label class="form-label text-white-50 small mb-1">Nome da Categoria</label>
                                <input type="text" class="form-control" id="cat-name" placeholder="Ex: Anime, Esportes" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label text-white-50 small mb-1">Emoji do Ícone</label>
                                <input type="text" class="form-control" id="cat-emoji" placeholder="Ex: 😂, 🎮" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label text-white-50 small mb-1">Posição / Ordem</label>
                                <input type="number" class="form-control" id="cat-order" value="1" required>
                            </div>
                            <button type="submit" class="btn btn-warning w-100 fw-bold">SALVAR CATEGORIA</button>
                        </form>
                    </div>
                </div>

                <div class="col-md-7">
                    <div class="glass-card">
                        <h5 class="fw-bold mb-3">Categorias Ativas</h5>
                        <table class="table custom-tbl text-white">
                            <thead>
                                <tr>
                                    <th>Ícone</th>
                                    <th>Categoria</th>
                                    <th>Ordenação</th>
                                    <th>Packs vinculados</th>
                                    <th>Ação</th>
                                </tr>
                            </thead>
                            <tbody id="cat-tbody">
                                <tr>
                                    <td>😂</td>
                                    <td><strong>Memes</strong></td>
                                    <td>1</td>
                                    <td>12 packs</td>
                                    <td><button class="btn btn-sm btn-outline-danger" onclick="deleteRow(this)"><i class="fa-solid fa-trash"></i></button></td>
                                </tr>
                                <tr>
                                    <td>🐱</td>
                                    <td><strong>Animals</strong></td>
                                    <td>2</td>
                                    <td>8 packs</td>
                                    <td><button class="btn btn-sm btn-outline-danger" onclick="deleteRow(this)"><i class="fa-solid fa-trash"></i></button></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- ======================= SECTION: USERS MANAGEMENT ======================= -->
        <div class="admin-section d-none text-start" id="section-users">
            <h4 class="fw-bold mb-4">Gerenciamento de Usuários</h4>
            <div class="glass-card">
                <table class="table custom-tbl text-white">
                    <thead>
                        <tr>
                            <th>Avatar</th>
                            <th>Nome Completo</th>
                            <th>E-mail</th>
                            <th>Assinatura</th>
                            <th>Status de Login</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><img src="https://lh3.googleusercontent.com/aida-public/AB6AXuC9ceainRre_8dWZ_Pyjjgy3svsrxKmotvJhGWt0NM7a4AsqBV9eNHOcIbnq2nWzbocBh-FR_O29iCzwQCGqKyC0-LWj9b3MnKbWxG97tKrzcJ4hG0co1ooyshCUzotds7vcXWGdtfmGlFKR7EcOnfNVkQW5vgZ1cRG-UQf4r7PNy9XvLEsJc2YhuT6CXNiyFVklSGlEMod8Qg790QESXP8_fNwquBCzmKKApJf7Xe40ypwp0joP26AY6zY7c6F3DxddF1V1Ttdk_s" class="rounded-circle" width="40"></td>
                            <td><strong>Alex Rivera</strong></td>
                            <td>alex_rivera@gmail.com</td>
                            <td><span class="badge bg-warning text-dark"><i class="fa-solid fa-crown me-1"></i>PREMIUM</span></td>
                            <td><span class="badge bg-success">Ativo</span></td>
                            <td>
                                <button class="btn btn-sm btn-outline-warning me-2" onclick="alert('Nível de assinatura alterado com sucesso!')"><i class="fa-solid fa-crown"></i></button>
                                <button class="btn btn-sm btn-outline-danger" onclick="blockUser(this)"><i class="fa-solid fa-ban"></i></button>
                            </td>
                        </tr>
                        <tr>
                            <td><div class="rounded-circle bg-secondary d-flex align-items-center justify-content-center text-white" style="width:40px; height:40px;">JS</div></td>
                            <td><strong>Jane Smith</strong></td>
                            <td>jane.smith@yahoo.com</td>
                            <td><span class="badge bg-secondary">Grátis</span></td>
                            <td><span class="badge bg-success">Ativo</span></td>
                            <td>
                                <button class="btn btn-sm btn-outline-warning me-2" onclick="alert('Assinatura promovida!')"><i class="fa-solid fa-crown"></i></button>
                                <button class="btn btn-sm btn-outline-danger" onclick="blockUser(this)"><i class="fa-solid fa-ban"></i></button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- ======================= SECTION: PUSH NOTIFICATIONS ======================= -->
        <div class="admin-section d-none text-start" id="section-push">
            <div class="row">
                <div class="col-md-6">
                    <div class="glass-card">
                        <h5 class="fw-bold mb-4"><i class="fa-solid fa-paper-plane me-2 text-info"></i>Disparar Mensagem FCM</h5>
                        <form onsubmit="sendPush(event)">
                            <div class="mb-3">
                                <label class="form-label text-white-50 small mb-1">Título do Push</label>
                                <input type="text" class="form-control" id="push-title" placeholder="Ex: 🔥 Figurinhas novas de Anime!" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label text-white-50 small mb-1">Mensagem Curta</label>
                                <textarea class="form-control" id="push-msg" rows="4" placeholder="Escreva a descrição da notificação que aparecerá na tela de bloqueio do celular..." required></textarea>
                            </div>
                            <div class="mb-3">
                                <label class="form-label text-white-50 small mb-1">Filtro por Categoria de Usuário</label>
                                <select class="form-select bg-dark border-secondary text-white" id="push-target">
                                    <option value="all">Sincronizar com Todos</option>
                                    <option value="premium">Membros Premuim apenas</option>
                                    <option value="Memes">Interessados em Memes</option>
                                </select>
                            </div>
                            <button type="submit" class="btn btn-info w-100 text-dark fw-bold">DISPARAR COMUNICAÇÃO IMEDIATA</button>
                        </form>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="glass-card">
                        <h5 class="fw-bold mb-3">Histórico recente de Notificações</h5>
                        <ul class="list-group list-group-flush bg-transparent">
                            <li class="list-group-item bg-transparent text-white border-secondary py-3 text-start">
                                <div class="d-flex justify-content-between">
                                    <strong>🔥 Novos Stickers da Semana!</strong>
                                    <span class="small text-white-50">Há 2 horas</span>
                                </div>
                                <p class="small text-white-50 mt-1 mb-0">Disparado para Todos. Taxa de abertura de 42%</p>
                            </li>
                            <li class="list-group-item bg-transparent text-white border-secondary py-3 text-start">
                                <div class="d-flex justify-content-between">
                                    <strong>🐱 Cyber Gatos já disponível</strong>
                                    <span class="small text-white-50">Ontem</span>
                                </div>
                                <p class="small text-white-50 mt-1 mb-0">Disparado para Membros Premium. Taxa de abertura de 78%</p>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- ======================= SECTION: MONETIZAÇÃO ADMOB ======================= -->
        <div class="admin-section d-none text-start" id="section-ads">
            <h4 class="fw-bold mb-4">Gerenciador de Anúncios AdMob</h4>
            <div class="row g-4 text-start">
                <style>
                    .ad-card {
                        background: rgba(255, 255, 255, 0.02);
                        border: 1px solid rgba(255, 255, 255, 0.05);
                        border-radius: 16px;
                        padding: 20px;
                    }
                </style>
                <div class="col-md-6">
                    <div class="ad-card">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="fw-bold m-0 text-success">1. AdMob Banner</h5>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" role="switch" checked>
                            </div>
                        </div>
                        <div class="mb-2">
                            <label class="small text-white-50 mb-1">ID de Teste</label>
                            <input type="text" class="form-control" value="ca-app-pub-3940256099942544/6300978111" disabled>
                        </div>
                        <div>
                            <label class="small text-white-50 mb-1">ID de Produção</label>
                            <input type="text" class="form-control" value="ca-app-pub-3940256099942544/6300978111">
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="ad-card">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="fw-bold m-0 text-success">2. Interstitial Ad</h5>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" role="switch" checked>
                            </div>
                        </div>
                        <div class="mb-2">
                            <label class="small text-white-50 mb-1">ID de Teste</label>
                            <input type="text" class="form-control" value="ca-app-pub-3940256099942544/1033173712" disabled>
                        </div>
                        <div>
                            <label class="small text-white-50 mb-1">ID de Produção</label>
                            <input type="text" class="form-control" value="ca-app-pub-3940256099942544/1033173712">
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="ad-card">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="fw-bold m-0 text-success">3. Rewarded Video Ad</h5>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" role="switch" checked>
                            </div>
                        </div>
                        <div class="mb-2">
                            <label class="small text-white-50 mb-1">ID de Teste</label>
                            <input type="text" class="form-control" value="ca-app-pub-3940256099942544/5224354917" disabled>
                        </div>
                        <div>
                            <label class="small text-white-50 mb-1">ID de Produção</label>
                            <input type="text" class="form-control" value="ca-app-pub-3940256099942544/5224354917">
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="ad-card">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="fw-bold m-0 text-success">4. Rewarded Interstitial</h5>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" role="switch" checked>
                            </div>
                        </div>
                        <div class="mb-2">
                            <label class="small text-white-50 mb-1">ID de Teste</label>
                            <input type="text" class="form-control" value="ca-app-pub-3940256099942544/5354046379" disabled>
                        </div>
                        <div>
                            <label class="small text-white-50 mb-1">ID de Produção</label>
                            <input type="text" class="form-control" value="ca-app-pub-3940256099942544/5354046379">
                        </div>
                    </div>
                </div>
            </div>
            
            <button class="btn btn-emerald text-dark fw-bold px-5 py-3 mt-4" style="background-color: #6cf8bb" onclick="alert('Monetização AdMob salva com sucesso! O aplicativo Android receberá as atualizações na próxima reconexão de rede.')">SALVAR CHAVES ADMOB</button>
        </div>

        <!-- ======================= SECTION: CONFIGURAÇÕES GERAIS ======================= -->
        <div class="admin-section d-none text-start" id="section-settings">
            <h4 class="fw-bold mb-4">Configurações Gerais do Aplicativo</h4>
            <div class="glass-card mb-4 text-start">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label text-white-50 small mb-1">Nome Oficial do Aplicativo</label>
                        <input type="text" class="form-control" id="app-name-set" value="Sticker Store Premium">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-white-50 small mb-1">URL da Política de Privacidade</label>
                        <input type="text" class="form-control" value="https://mystickerstore.com/privacy-policy">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-white-50 small mb-1">Idioma Padrão do Sistema</label>
                        <select class="form-select bg-dark border-secondary text-white">
                            <option value="pt">Português (Brasil)</option>
                            <option value="en">English (US)</option>
                            <option value="es">Español (España)</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-white-50 small mb-1">Nível de Compactação de Imagens</label>
                        <select class="form-select bg-dark border-secondary text-white">
                            <option value="high">Altíssima (WEBP comprimido para WhatsApp - Recomendado)</option>
                            <option value="med">Média</option>
                            <option value="none">Sem compactar (Original)</option>
                        </select>
                    </div>
                </div>
            </div>
            
            <button class="btn btn-primary text-dark fw-bold px-4 py-2" onclick="alert('Configurações salvas e aplicadas!')">SALVAR TUDO</button>
        </div>

    </div>

    <!-- PACK MANAGEMENT MODAL (CREATION) -->
    <div class="modal fade" id="packModal" tabindex="-1" aria-labelledby="packModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content bg-dark text-white border-secondary">
                <div class="modal-header border-secondary">
                    <h5 class="modal-title font-weight-bold" id="packModalLabel"><i class="fa-solid fa-folder-plus text-[#6cf8bb] me-2"></i>Cadastrar Pacote</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-start">
                    <form onsubmit="savePack(event)" autocomplete="off">
                        <div class="mb-3">
                            <label class="form-label text-white-50 small mb-1">Nome do Pacote de Figurinhas</label>
                            <input type="text" class="form-control" id="form-pack-name" placeholder="Ex: Gatos Cósmicos" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-white-50 small mb-1">Autor / Criador</label>
                            <input type="text" class="form-control" id="form-pack-creator" placeholder="Ex: MochiArt" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-white-50 small mb-1">Categoria de Vinculação</label>
                            <select class="form-select bg-black border-secondary text-white" id="form-pack-cat">
                                <option value="Animals">Animais</option>
                                <option value="Memes">Memes</option>
                                <option value="Gaming">Games</option>
                            </select>
                        </div>
                        
                        <!-- Drag and Drop Dropzone simulator -->
                        <div class="mb-3">
                            <label class="form-label text-white-50 small mb-1">Upload de Capa e Figurinhas (WEBP, GIF, PNG)</label>
                            <div class="dropzone-box text-center p-4" onclick="document.getElementById('file-trigger').click()">
                                <i class="fa-solid fa-cloud-arrow-up text-white-50 fs-2 mb-2"></i>
                                <p class="small text-white-50 mb-0">Arraste seus stickers ou clique aqui para selecionar arquivos de mídia.</p>
                                <input type="file" class="d-none" id="file-trigger" multiple onchange="simulatedUpload(this)">
                            </div>
                            <span class="small text-white-50 mt-1 d-block" id="upload-status">Mínimo 3 figurinhas - Máximo 30 figurinhas por pack.</span>
                        </div>

                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="form-pack-premium">
                                <label class="form-check-label text-white-50 small" for="form-pack-premium">Define como pacote PREMIUM</label>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-success w-100 fw-bold py-2 mt-2">DIFUNDIR NO APLICATIVO ANDROID</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Section switching router logic
        function showSection(sectionId, element) {
            document.querySelectorAll('.admin-section').forEach(s => s.classList.add('d-none'));
            document.getElementById('section-' + sectionId).classList.remove('d-none');
            
            document.querySelectorAll('.nav-link-custom').forEach(l => l.classList.remove('active'));
            element.classList.add('active');

            // Set Title Header contextually
            const titles = {
                dashboard: ['Dashboard Principal', 'Estatísticas gerais de downloads e interatividade.'],
                packs: ['Pacotes de Figurinhas', 'Crie novos sticker packs ou ordene as imagens do inventário.'],
                categories: ['Categorias', 'Organize os packs por tópicos correspondentes.'],
                users: ['Gerenciador de Usuários', 'Visualize o perfil de assinantes e controle restrições.'],
                push: ['Disparo de Push', 'Notificações instantâneas do Firebase Cloud Messaging.'],
                ads: ['Serviço de Anúncios', 'Controle os canais de ads mostrados no WhatsApp Sticker Store.'],
                settings: ['Configurações Gerais', 'Configuração de política de cookies, backup e logo.']
            };
            
            document.getElementById('section-title').textContent = titles[sectionId][0];
            document.getElementById('section-subtitle').textContent = titles[sectionId][1];
        }

        // Dark/Light toggle
        function toggleTheme() {
            document.body.classList.toggle('light-theme');
            const bt = document.getElementById('theme-button');
            const icon = document.getElementById('theme-icon');
            if (document.body.classList.contains('light-theme')) {
                icon.textContent = '🌑';
            } else {
                icon.textContent = '🔆';
            }
        }

        // Drop action simulator
        function simulatedUpload(input) {
            const count = input.files.length;
            document.getElementById('upload-status').innerHTML = `<strong class="text-success">${count} figurinhas selecionadas!</strong> Prontas para compressão e upload.`;
        }

        // Save actions
        function savePack(e) {
            e.preventDefault();
            const name = document.getElementById('form-pack-name').value;
            const creator = document.getElementById('form-pack-creator').value;
            const cat = document.getElementById('form-pack-cat').value;
            const isPremium = document.getElementById('form-pack-premium').checked;

            const premiumBadge = isPremium ? '<span class="badge bg-warning text-dark ms-2">PREMIUM</span>' : '';

            const tbody = document.getElementById('packs-tbody');
            const newRow = `
                <tr>
                    <td><div class="bg-secondary rounded p-2 text-center text-white" style="width:48px;height:48px;"><i class="fa-solid fa-image"></i></div></td>
                    <td><span class="fw-bold">${name}</span> ${premiumBadge}</td>
                    <td>${creator}</td>
                    <td>${cat}</td>
                    <td>3 carregados</td>
                    <td>0</td>
                    <td>
                        <button class="btn btn-sm btn-outline-info me-2" onclick="alert('Manage stickers!')"><i class="fa-solid fa-images"></i></button>
                        <button class="btn btn-sm btn-outline-danger" onclick="deleteRow(this)"><i class="fa-solid fa-trash"></i></button>
                    </td>
                </tr>
            `;

            tbody.insertAdjacentHTML('afterbegin', newRow);

            // Hide Modal
            const modalElement = document.getElementById('packModal');
            const modalInstance = Bootstrap.Modal.getInstance(modalElement);
            modalInstance.hide();

            alert('Pacote criado de forma otimizada para o WhatsApp! Pronto para consumo do app Android.');
        }

        function addCategory(e) {
            e.preventDefault();
            const name = document.getElementById('cat-name').value;
            const emoji = document.getElementById('cat-emoji').value;
            const order = document.getElementById('cat-order').value;

            const tbody = document.getElementById('cat-tbody');
            const row = `
                <tr>
                    <td>${emoji}</td>
                    <td><strong>${name}</strong></td>
                    <td>${order}</td>
                    <td>0 packs</td>
                    <td><button class="btn btn-sm btn-outline-danger" onclick="deleteRow(this)"><i class="fa-solid fa-trash"></i></button></td>
                </tr>
            `;
            tbody.insertAdjacentHTML('beforeend', row);
            document.getElementById('category-form').reset();
            alert('Categoria criada com sucesso!');
        }

        function deleteRow(btn) {
            if (confirm('Deseja realmente excluir este registro? Esta operação removerá definitivamente do celular do cliente também.')) {
                btn.closest('tr').remove();
            }
        }

        function blockUser(btn) {
            const statusBadge = btn.closest('tr').querySelector('.badge.bg-success');
            if (statusBadge) {
                statusBadge.className = 'badge bg-danger';
                statusBadge.textContent = 'Bloqueado';
                btn.innerHTML = '<i class="fa-solid fa-user-check"></i>';
                btn.className = 'btn btn-sm btn-outline-success me-1';
                alert('O acesso do usuário ao aplicativo foi suspenso temporariamente.');
            } else {
                const dangerBadge = btn.closest('tr').querySelector('.badge.bg-danger');
                dangerBadge.className = 'badge bg-success';
                dangerBadge.textContent = 'Ativo';
                btn.innerHTML = '<i class="fa-solid fa-ban"></i>';
                btn.className = 'btn btn-sm btn-outline-danger me-1';
                alert('Acesso do usuário restaurado!');
            }
        }

        function sendPush(e) {
            e.preventDefault();
            const title = document.getElementById('push-title').value;
            const msg = document.getElementById('push-msg').value;
            alert(`Sincronização Push enviada via Firebase Cloud Messaging:\n\nTítulo: ${title}\nMensagem: ${msg}`);
            document.getElementById('push-title').value = '';
            document.getElementById('push-msg').value = '';
        }

        function executeBackup() {
            alert('Backup executado com sucesso e encriptado com chaves AES256. Versão salva em: /public_html/database/backups/');
        }
    </script>
</body>
</html>
