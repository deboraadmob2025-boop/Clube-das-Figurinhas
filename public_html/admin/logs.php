<?php
ob_start();
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Redirect if not logged in
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: index.php");
    exit;
}

// Global active tab indicator
$active_tab = 'logs';

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

// Load migration controller
require_once '../config/migration.php';

// Handle Action Posts
$message = "";
$message_type = "success";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        $action = $_POST['action'];
        
        if ($action === 'clear_logs') {
            if (DbAutoMigration::clearLogs()) {
                $message = "Histórico de logs limpo com sucesso!";
            } else {
                $message = "Erro ao limpar arquivos de LOG.";
                $message_type = "danger";
            }
        }
        
        if ($action === 'force_check') {
            if ($db_connected) {
                $success = DbAutoMigration::run($conn, 'KingPixCash'); // Force running migration check
                if ($success) {
                    $message = "Varredura de integridade e auto-migrações executadas com sucesso!";
                } else {
                    $message = "Erro ao processar verificação de migrações automáticas.";
                    $message_type = "danger";
                }
            } else {
                $message = "Banco de dados desconectado. Impossível migrar.";
                $message_type = "danger";
            }
        }
    }
}

// Read log content
$log_lines = [];
$log_file_path = __DIR__ . '/../logs/migrations.log';
if (file_exists($log_file_path)) {
    $content = file_get_contents($log_file_path);
    $lines = explode(PHP_EOL, $content);
    foreach ($lines as $line) {
        if (!empty(trim($line))) {
            $log_lines[] = trim($line);
        }
    }
    // Most recent logs first
    $log_lines = array_reverse($log_lines);
}

// Dynamic server diagnostics check
$server_os = PHP_OS;
$php_version = PHP_VERSION;
$disk_free = function_exists('disk_free_space') ? round(disk_free_space(".") / (1024 * 1024 * 1024), 2) . " GB" : "N/D";
$disk_total = function_exists('disk_total_space') ? round(disk_total_space(".") / (1024 * 1024 * 1024), 2) . " GB" : "N/D";
$uploads_writable = is_writable(__DIR__ . '/../uploads') ? "Sim (Gravável)" : "Não (Somente Leitura!)";
$logs_writable = is_writable(__DIR__ . '/../logs') ? "Sim (Gravável)" : "Não (Somente Leitura!)";

// Fetch database stats if connected
$tables_status = [];
if ($db_connected) {
    try {
        $stmt = $conn->query("SHOW TABLES");
        while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
            $tbl = $row[0];
            $countStmt = $conn->query("SELECT COUNT(*) FROM `{$tbl}`");
            $rowsCount = $countStmt->fetchColumn();
            $tables_status[] = [
                'name' => $tbl,
                'count' => $rowsCount
            ];
        }
    } catch (Exception $ex) {
        $db_error = $ex->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sticker Store - Diagnósticos & Monitoramento</title>
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts Space Grotesk & Orbitron -->
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@500;700&family=Space+Grotesk:wght@400;500;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --slate-black: #080f14;
            --gradient-accent: linear-gradient(135deg, #6cf8bb 0%, #00966a 100%);
            --glow-green: 0px 0px 20px rgba(108, 248, 187, 0.25);
        }

        body {
            font-family: 'Space Grotesk', sans-serif;
            background-color: var(--slate-black);
            background: radial-gradient(circle at top right, #132420 0%, #080f14 80%);
            color: #f1f5f9;
            min-height: 100vh;
        }

        h1, h2, h3, h4, h5, h6 {
            font-family: 'Space Grotesk', sans-serif;
            letter-spacing: -0.5px;
        }

        .custom-badge-mono {
            font-family: 'JetBrains Mono', monospace;
            font-size: 11px;
            padding: 4px 8px;
            border-radius: 6px;
        }

        .console-logs {
            background-color: #03080c;
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 12px;
            padding: 18px;
            max-height: 480px;
            overflow-y: auto;
            font-family: 'JetBrains Mono', monospace;
            font-size: 13px;
            color: #c9d1d9;
        }

        .console-line {
            padding: 4px 6px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.02);
            line-height: 1.6;
        }

        .console-line:hover {
            background-color: rgba(255, 255, 255, 0.03);
        }

        .glass-panel {
            background: rgba(255, 255, 255, 0.03);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 16px;
            padding: 24px;
            transition: all 0.3s;
        }

        .glass-panel:hover {
            border-color: rgba(108, 248, 187, 0.3);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.4);
        }

        .metric-title {
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: rgba(255, 255, 255, 0.5);
            margin-bottom: 8px;
        }

        .metric-val {
            font-family: 'Orbitron', sans-serif;
            font-weight: 700;
            font-size: 24px;
        }

        /* Nav customization */
        .glass-header {
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
            background: rgba(8, 15, 20, 0.8);
            backdrop-filter: blur(15px);
            -webkit-backdrop-filter: blur(15px);
        }

        .bg-opacity-5 {
            background-color: rgba(255, 255, 255, 0.04);
        }

        .tab-success-indicator {
            color: #6cf8bb;
            text-shadow: 0 0 10px rgba(108, 248, 187, 0.5);
        }
    </style>
</head>
<body>

    <!-- TOP Glass Navbar -->
    <header class="glass-header sticky-top py-3 mb-4">
        <div class="container d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center">
                <span class="fs-4 fw-bold text-white me-2"><i class="fa-solid fa-microchip tab-success-indicator me-2"></i>Terminal de Diagnósticos</span>
                <span class="badge bg-danger rounded-pill custom-badge-mono text-uppercase">Live Monitoring</span>
            </div>
            <div>
                <a href="dashboard.php" class="btn btn-outline-light btn-sm rounded-pill px-3 py-1fw-bold">
                    <i class="fa-solid fa-arrow-left me-1"></i> Voltar ao Painel
                </a>
            </div>
        </div>
    </header>

    <main class="container mb-5">
        
        <?php if (!empty($message)): ?>
            <div class="alert alert-<?= $message_type ?> bg-dark bg-opacity-40 border-<?= $message_type ?> text-white p-3 rounded-3 mb-4 d-flex align-items-center justify-content-between" role="alert">
                <span><i class="fa-solid <?= $message_type === 'success' ? 'fa-circle-check text-success' : 'fa-circle-exclamation text-danger' ?> me-2 fs-5"></i> <?= $message ?></span>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <!-- Server health stats widgets -->
        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <div class="glass-panel text-start">
                    <div class="metric-title"><i class="fa-solid fa-server me-1"></i> Host / Sistema Operacional</div>
                    <div class="metric-val text-white"><?= $server_os ?></div>
                    <div class="small text-white-50 mt-1">PHP Version: <code><?= $php_version ?></code></div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="glass-panel text-start">
                    <div class="metric-title"><i class="fa-solid fa-folder-closed me-1"></i> Diretório Uploads</div>
                    <div class="metric-val <?= is_writable(__DIR__ . '/../uploads') ? 'text-success' : 'text-danger' ?>"><?= is_writable(__DIR__ . '/../uploads') ? 'Online' : 'Restrito' ?></div>
                    <div class="small text-white-50 mt-1">Escrita: <?= $uploads_writable ?></div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="glass-panel text-start">
                    <div class="metric-title"><i class="fa-solid fa-database me-1"></i> Banco de Dados</div>
                    <div class="metric-val <?= $db_connected ? 'text-success' : 'text-danger' ?>">
                        <?= $db_connected ? 'Conectado' : 'Desconectado' ?>
                    </div>
                    <?php if ($db_connected): ?>
                        <div class="small text-white-50 mt-1">Engine de Tabelas: <code>InnoDB</code></div>
                    <?php else: ?>
                        <div class="small text-danger mt-1">Erro: <?= htmlspecialchars($db_error) ?></div>
                    <?php endif; ?>
                </div>
            </div>
            <div class="col-md-3">
                <div class="glass-panel text-start">
                    <div class="metric-title"><i class="fa-solid fa-hard-drive me-1"></i> Unidade de Disco</div>
                    <div class="metric-val text-info"><?= $disk_free ?></div>
                    <div class="small text-white-50 mt-1">Total alocado: <?= $disk_total ?></div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            
            <!-- Dynamic Console Log Stream (Left area) -->
            <div class="col-lg-8">
                <div class="glass-panel h-100 d-flex flex-column text-start">
                    <div class="d-flex align-items-center justify-content-between mb-3 border-bottom border-secondary pb-3">
                        <h5 class="fw-bold mb-0 text-white"><i class="fa-solid fa-terminal me-2 text-success"></i>Logs de Migrações Automáticas</h5>
                        <div class="d-flex gap-2">
                            <form action="" method="POST" class="m-0">
                                <input type="hidden" name="action" value="force_check">
                                <button type="submit" class="btn btn-sm btn-outline-success font-weight-bold" title="Executar checagens de tabelas e auto-atualização">
                                    <i class="fa-solid fa-rotate me-1"></i> Forçar Migração
                                </button>
                            </form>
                            <form action="" method="POST" class="m-0" onsubmit="return confirm('Deseja realmente limpar todo o arquivo de logs do sistema?')">
                                <input type="hidden" name="action" value="clear_logs">
                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Limpar logs">
                                    <i class="fa-solid fa-trash me-1"></i> Limpar Logs
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- Log Filters indicator -->
                    <div class="mb-3 d-flex flex-wrap gap-2">
                        <button class="btn btn-sm btn-secondary bg-opacity-5 border-secondary rounded-3 text-white filter-btn" data-filter="all">Todos (<?= count($log_lines) ?>)</button>
                        <button class="btn btn-sm btn-outline-success rounded-3 filter-btn" data-filter="SUCCESS">Success</button>
                        <button class="btn btn-sm btn-outline-warning rounded-3 filter-btn" data-filter="WARNING">Warning</button>
                        <button class="btn btn-sm btn-outline-danger rounded-3 filter-btn" data-filter="ERROR">Error</button>
                        <button class="btn btn-sm btn-outline-info rounded-3 filter-btn" data-filter="INFO">Info</button>
                    </div>

                    <!-- Console frame -->
                    <div class="console-logs flex-grow-1">
                        <?php if (empty($log_lines)): ?>
                            <div class="text-white-50 text-center py-5">
                                <i class="fa-solid fa-clipboard-question fs-2 mb-2 d-block"></i>
                                Nenhum registro de migração automática encontrado.
                            </div>
                        <?php else: ?>
                            <?php foreach ($log_lines as $line): 
                                $lineClass = "text-white-50";
                                $typeBadge = "all";
                                if (strpos($line, "[SUCCESS]") !== false) {
                                    $lineClass = "text-success";
                                    $typeBadge = "SUCCESS";
                                } elseif (strpos($line, "[WARNING]") !== false) {
                                    $lineClass = "text-warning";
                                    $typeBadge = "WARNING";
                                } elseif (strpos($line, "[ERROR]") !== false) {
                                    $lineClass = "text-danger fw-bold";
                                    $typeBadge = "ERROR";
                                } elseif (strpos($line, "[INFO]") !== false) {
                                    $lineClass = "text-info";
                                    $typeBadge = "INFO";
                                }
                            ?>
                                <div class="console-line <?= $lineClass ?> log-item" data-type="<?= $typeBadge ?>">
                                    <?= htmlspecialchars($line) ?>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                    <div class="small text-white-50 mt-3 d-flex align-items-center justify-content-between">
                        <span>Arquivo focado: `/public_html/logs/migrations.log`</span>
                        <span>Atualizado em tempo real</span>
                    </div>
                </div>
            </div>

            <!-- Database schema overview widgets (Right area) -->
            <div class="col-lg-4">
                <div class="glass-panel text-start d-flex flex-column h-100">
                    <h5 class="fw-bold mb-3 border-bottom border-secondary pb-3 text-white"><i class="fa-solid fa-list-check me-2 text-info"></i>Estado das Tabelas</h5>
                    
                    <?php if (!$db_connected): ?>
                        <div class="alert alert-danger bg-danger bg-opacity-10 border-danger text-white p-3 rounded-3 mb-0">
                            <strong>Erro de Conexão:</strong> Impossível analisar o dicionário de dados do banco de dados MySQL externo. Certifique-se de configurar a conexão corretamente em `config/database.php`.
                        </div>
                    <?php else: ?>
                        <div class="table-responsive flex-grow-1" style="max-height: 480px; overflow-y:auto;">
                            <table class="table table-dark table-hover table-sm mb-0">
                                <thead>
                                    <tr>
                                        <th class="small text-white-50 py-2">Tabela</th>
                                        <th class="small text-white-50 py-2 text-end">Registros</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($tables_status as $table): ?>
                                        <tr>
                                            <td class="small font-monospace py-2 text-white">
                                                <i class="fa-regular fa-folder text-warning me-1"></i> <?= htmlspecialchars($table['name']) ?>
                                            </td>
                                            <td class="small font-monospace py-2 text-end text-success fw-bold">
                                                <?= $table['count'] ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-3 p-3 bg-secondary bg-opacity-10 rounded-3">
                            <span class="small text-white-50 d-block mb-1">Total de Tabelas Mapeadas</span>
                            <h4 class="fw-bold text-white mb-0 font-monospace"><?= count($tables_status) ?> / 16</h4>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

        </div>

    </main>

    <!-- Bootstrap 5 Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // JS Filter for logs
        document.querySelectorAll('.filter-btn').forEach(button => {
            button.addEventListener('click', function() {
                const filter = this.getAttribute('data-filter');
                
                // Toggle active button style
                document.querySelectorAll('.filter-btn').forEach(btn => {
                    btn.classList.add('btn-outline-secondary');
                    btn.classList.remove('btn-secondary', 'bg-opacity-5', 'text-white');
                });
                
                this.classList.remove('btn-outline-secondary');
                this.classList.add('btn-secondary', 'bg-opacity-5', 'text-white');
                
                // Do the line filtering
                document.querySelectorAll('.log-item').forEach(item => {
                    if (filter === 'all' || item.getAttribute('data-type') === filter) {
                        item.style.display = 'block';
                    } else {
                        item.style.display = 'none';
                    }
                });
            });
        });
    </script>
</body>
</html>
