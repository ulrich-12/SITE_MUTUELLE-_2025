<?php
require_once 'includes/auth_middleware.php';

// Vérification de l'authentification et des permissions super admin
$user = checkAuth('view_system_logs', 'super_admin');

// Logger l'accès aux logs
logAction($_SESSION['user_id'], 'access_system_logs', 'Consultation des logs système');

$page_title = "Logs Système";
include 'includes/header.php';

// Paramètres de pagination et filtrage
$page = intval(isset($_GET['page']) ? $_GET['page'] : 1);
$limit = intval(isset($_GET['limit']) ? $_GET['limit'] : 50);
$filter_action = isset($_GET['action']) ? $_GET['action'] : '';
$filter_user = isset($_GET['user']) ? $_GET['user'] : '';
$filter_date = isset($_GET['date']) ? $_GET['date'] : '';
$log_source = isset($_GET['source']) ? $_GET['source'] : 'database'; // 'database' ou 'file'

$offset = ($page - 1) * $limit;

try {
    if ($log_source === 'database') {
        // Logs depuis la base de données
        $where_conditions = [];
        $params = [];

        if (!empty($filter_action)) {
            $where_conditions[] = "al.action LIKE ?";
            $params[] = "%$filter_action%";
        }

        if (!empty($filter_user)) {
            $where_conditions[] = "(u.nom LIKE ? OR u.prenom LIKE ? OR u.email LIKE ?)";
            $params[] = "%$filter_user%";
            $params[] = "%$filter_user%";
            $params[] = "%$filter_user%";
        }

        if (!empty($filter_date)) {
            $where_conditions[] = "DATE(al.created_at) = ?";
            $params[] = $filter_date;
        }

        $where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

        // Compter le total
        $count_sql = "SELECT COUNT(*) as total 
                      FROM activity_logs al 
                      LEFT JOIN users u ON al.user_id = u.id 
                      $where_clause";
        $total_logs = executeQuery($count_sql, $params)->fetch()['total'];

        // Récupérer les logs
        $sql = "SELECT 
                    al.id,
                    al.user_id,
                    al.action,
                    al.details,
                    al.ip_address,
                    al.user_agent,
                    al.created_at,
                    u.nom,
                    u.prenom,
                    u.email,
                    u.role
                FROM activity_logs al 
                LEFT JOIN users u ON al.user_id = u.id 
                $where_clause
                ORDER BY al.created_at DESC 
                LIMIT $limit OFFSET $offset";

        $logs = executeQuery($sql, $params)->fetchAll();
    } else {
        // Logs depuis le fichier JSON
        $logs = [];
        $total_logs = 0;
        
        $log_file = 'logs/actions.log';
        if (file_exists($log_file)) {
            $file_logs = file($log_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            $file_logs = array_reverse($file_logs); // Plus récents en premier
            
            // Filtrer les logs
            $filtered_logs = [];
            foreach ($file_logs as $log_line) {
                $log_data = json_decode($log_line, true);
                if ($log_data) {
                    // Appliquer les filtres
                    $include = true;
                    
                    if (!empty($filter_action) && stripos($log_data['action'], $filter_action) === false) {
                        $include = false;
                    }
                    
                    if (!empty($filter_user) && stripos($log_data['user_name'], $filter_user) === false) {
                        $include = false;
                    }
                    
                    if (!empty($filter_date) && date('Y-m-d', strtotime($log_data['timestamp'])) !== $filter_date) {
                        $include = false;
                    }
                    
                    if ($include) {
                        $filtered_logs[] = $log_data;
                    }
                }
            }
            
            $total_logs = count($filtered_logs);
            $logs = array_slice($filtered_logs, $offset, $limit);
        }
    }

    $total_pages = ceil($total_logs / $limit);

    // Statistiques rapides
    $stats = [];
    if ($log_source === 'database') {
        $stats = executeQuery("SELECT 
            COUNT(*) as total_actions,
            COUNT(DISTINCT user_id) as unique_users,
            COUNT(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR) THEN 1 END) as last_24h,
            COUNT(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY) THEN 1 END) as last_7d
            FROM activity_logs")->fetch();
    }

} catch (Exception $e) {
    error_log("Erreur logs système : " . $e->getMessage());
    $logs = [];
    $total_logs = 0;
    $total_pages = 0;
    $stats = [];
}
?>

<main class="main-content">
    <div class="container" style="padding: 2rem 0;">
        <!-- En-tête -->
        <div style="background: linear-gradient(135deg, #607d8b 0%, #90a4ae 100%); color: white; padding: 2rem; border-radius: 10px; margin-bottom: 2rem;">
            <div style="display: grid; grid-template-columns: 1fr auto; gap: 2rem; align-items: center;">
                <div>
                    <h1><i class="fas fa-file-alt"></i> Logs Système</h1>
                    <p style="font-size: 1.1rem; margin: 0.5rem 0;">Consultation et analyse de l'activité système</p>
                    <p><i class="fas fa-database"></i> Source : <?php echo $log_source === 'database' ? 'Base de données' : 'Fichiers logs'; ?></p>
                </div>
                <div style="text-align: center;">
                    <div style="background: rgba(255,255,255,0.2); padding: 1rem; border-radius: 10px;">
                        <div style="font-size: 2rem; font-weight: bold;"><?php echo number_format($total_logs); ?></div>
                        <div style="font-size: 0.9rem; opacity: 0.9;">Entrées totales</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistiques rapides -->
        <?php if (!empty($stats) && $log_source === 'database'): ?>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-bottom: 2rem;">
            <div class="stat-card">
                <div class="stat-number"><?php echo number_format($stats['total_actions']); ?></div>
                <div class="stat-label">Actions totales</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo number_format($stats['unique_users']); ?></div>
                <div class="stat-label">Utilisateurs actifs</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo number_format($stats['last_24h']); ?></div>
                <div class="stat-label">Dernières 24h</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo number_format($stats['last_7d']); ?></div>
                <div class="stat-label">7 derniers jours</div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Filtres et contrôles -->
        <div style="background: white; padding: 1.5rem; border-radius: 10px; box-shadow: var(--shadow); margin-bottom: 2rem;">
            <form method="GET" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; align-items: end;">
                <div>
                    <label style="display: block; margin-bottom: 0.5rem; font-weight: 600;">Source des logs :</label>
                    <select name="source" style="width: 100%; padding: 0.5rem; border: 1px solid var(--border-color); border-radius: 5px;">
                        <option value="database" <?php echo $log_source === 'database' ? 'selected' : ''; ?>>Base de données</option>
                        <option value="file" <?php echo $log_source === 'file' ? 'selected' : ''; ?>>Fichiers logs</option>
                    </select>
                </div>
                
                <div>
                    <label style="display: block; margin-bottom: 0.5rem; font-weight: 600;">Action :</label>
                    <input type="text" name="action" value="<?php echo htmlspecialchars($filter_action); ?>" 
                           placeholder="Filtrer par action..." 
                           style="width: 100%; padding: 0.5rem; border: 1px solid var(--border-color); border-radius: 5px;">
                </div>
                
                <div>
                    <label style="display: block; margin-bottom: 0.5rem; font-weight: 600;">Utilisateur :</label>
                    <input type="text" name="user" value="<?php echo htmlspecialchars($filter_user); ?>" 
                           placeholder="Nom, email..." 
                           style="width: 100%; padding: 0.5rem; border: 1px solid var(--border-color); border-radius: 5px;">
                </div>
                
                <div>
                    <label style="display: block; margin-bottom: 0.5rem; font-weight: 600;">Date :</label>
                    <input type="date" name="date" value="<?php echo htmlspecialchars($filter_date); ?>" 
                           style="width: 100%; padding: 0.5rem; border: 1px solid var(--border-color); border-radius: 5px;">
                </div>
                
                <div>
                    <label style="display: block; margin-bottom: 0.5rem; font-weight: 600;">Limite :</label>
                    <select name="limit" style="width: 100%; padding: 0.5rem; border: 1px solid var(--border-color); border-radius: 5px;">
                        <option value="25" <?php echo $limit == 25 ? 'selected' : ''; ?>>25</option>
                        <option value="50" <?php echo $limit == 50 ? 'selected' : ''; ?>>50</option>
                        <option value="100" <?php echo $limit == 100 ? 'selected' : ''; ?>>100</option>
                        <option value="200" <?php echo $limit == 200 ? 'selected' : ''; ?>>200</option>
                    </select>
                </div>
                
                <div style="display: flex; gap: 0.5rem;">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search"></i> Filtrer
                    </button>
                    <a href="system_logs.php" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Reset
                    </a>
                </div>
            </form>
        </div>

        <!-- Actions rapides -->
        <div style="background: white; padding: 1rem; border-radius: 10px; box-shadow: var(--shadow); margin-bottom: 2rem;">
            <div style="display: flex; gap: 1rem; flex-wrap: wrap; align-items: center;">
                <button onclick="exportLogs()" class="btn btn-secondary">
                    <i class="fas fa-download"></i> Exporter
                </button>
                <button onclick="clearOldLogs()" class="btn btn-outline">
                    <i class="fas fa-trash"></i> Nettoyer anciens logs
                </button>
                <button onclick="refreshLogs()" class="btn btn-outline">
                    <i class="fas fa-sync"></i> Actualiser
                </button>
                <div style="margin-left: auto; color: var(--text-light); font-size: 0.9rem;">
                    Page <?php echo $page; ?> sur <?php echo $total_pages; ?> 
                    (<?php echo number_format($total_logs); ?> entrées)
                </div>
            </div>
        </div>

        <!-- Tableau des logs -->
        <div style="background: white; border-radius: 10px; box-shadow: var(--shadow); overflow: hidden;">
            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse;">
                    <thead style="background: #f8f9fa;">
                        <tr>
                            <th style="padding: 1rem; text-align: left; border-bottom: 2px solid var(--border-color); font-weight: 600;">
                                <i class="fas fa-clock"></i> Date/Heure
                            </th>
                            <th style="padding: 1rem; text-align: left; border-bottom: 2px solid var(--border-color); font-weight: 600;">
                                <i class="fas fa-user"></i> Utilisateur
                            </th>
                            <th style="padding: 1rem; text-align: left; border-bottom: 2px solid var(--border-color); font-weight: 600;">
                                <i class="fas fa-cog"></i> Action
                            </th>
                            <th style="padding: 1rem; text-align: left; border-bottom: 2px solid var(--border-color); font-weight: 600;">
                                <i class="fas fa-info-circle"></i> Détails
                            </th>
                            <th style="padding: 1rem; text-align: left; border-bottom: 2px solid var(--border-color); font-weight: 600;">
                                <i class="fas fa-network-wired"></i> IP
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($logs)): ?>
                            <?php foreach ($logs as $log): ?>
                                <tr style="border-bottom: 1px solid var(--border-color); transition: background-color 0.2s ease;">
                                    <!-- Date/Heure -->
                                    <td style="padding: 1rem; vertical-align: top;">
                                        <div style="font-weight: 600; color: var(--text-dark);">
                                            <?php
                                            $timestamp = $log_source === 'database' ? $log['created_at'] : $log['timestamp'];
                                            echo date('d/m/Y', strtotime($timestamp));
                                            ?>
                                        </div>
                                        <div style="font-size: 0.9rem; color: var(--text-light);">
                                            <?php echo date('H:i:s', strtotime($timestamp)); ?>
                                        </div>
                                    </td>

                                    <!-- Utilisateur -->
                                    <td style="padding: 1rem; vertical-align: top;">
                                        <div style="display: flex; align-items: center; gap: 0.5rem;">
                                            <div style="width: 32px; height: 32px; background: var(--primary-color); color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 0.8rem; flex-shrink: 0;">
                                                <?php
                                                if ($log_source === 'database') {
                                                    echo $log['prenom'] ? strtoupper(substr($log['prenom'], 0, 1)) : '?';
                                                } else {
                                                    $name_parts = explode(' ', $log['user_name']);
                                                    echo isset($name_parts[0]) ? strtoupper(substr($name_parts[0], 0, 1)) : '?';
                                                }
                                                ?>
                                            </div>
                                            <div>
                                                <div style="font-weight: 600; color: var(--text-dark);">
                                                    <?php
                                                    if ($log_source === 'database') {
                                                        echo htmlspecialchars((isset($log['prenom']) ? $log['prenom'] : '') . ' ' . (isset($log['nom']) ? $log['nom'] : ''));
                                                    } else {
                                                        echo htmlspecialchars(isset($log['user_name']) ? $log['user_name'] : 'Inconnu');
                                                    }
                                                    ?>
                                                </div>
                                                <div style="font-size: 0.8rem; color: var(--text-light);">
                                                    <?php
                                                    if ($log_source === 'database') {
                                                        echo htmlspecialchars(isset($log['role']) ? $log['role'] : 'inconnu');
                                                    } else {
                                                        echo htmlspecialchars(isset($log['user_role']) ? $log['user_role'] : 'inconnu');
                                                    }
                                                    ?>
                                                </div>
                                            </div>
                                        </div>
                                    </td>

                                    <!-- Action -->
                                    <td style="padding: 1rem; vertical-align: top;">
                                        <div style="display: flex; align-items: center; gap: 0.5rem;">
                                            <?php
                                            $action = $log_source === 'database' ? $log['action'] : $log['action'];
                                            $action_colors = [
                                                'login' => '#4caf50',
                                                'logout' => '#ff9800',
                                                'access' => '#2196f3',
                                                'create' => '#9c27b0',
                                                'update' => '#ff5722',
                                                'delete' => '#f44336',
                                                'upload' => '#607d8b',
                                                'download' => '#795548',
                                                'backup' => '#3f51b5',
                                                'error' => '#f44336'
                                            ];

                                            $color = '#616161'; // Couleur par défaut
                                            foreach ($action_colors as $key => $action_color) {
                                                if (stripos($action, $key) !== false) {
                                                    $color = $action_color;
                                                    break;
                                                }
                                            }
                                            ?>
                                            <span style="background: <?php echo $color; ?>; color: white; padding: 0.25rem 0.75rem; border-radius: 15px; font-size: 0.8rem; font-weight: 600;">
                                                <?php echo htmlspecialchars($action); ?>
                                            </span>
                                            <?php if ($log_source === 'database' && !empty($log['id'])): ?>
                                                <button onclick="showLogDetail(<?php echo $log['id']; ?>)"
                                                        class="btn-detail"
                                                        title="Voir les détails">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </td>

                                    <!-- Détails -->
                                    <td style="padding: 1rem; vertical-align: top;">
                                        <div style="max-width: 300px; word-wrap: break-word;">
                                            <?php
                                            $details = $log_source === 'database' ? $log['details'] : $log['details'];
                                            if (!empty($details)) {
                                                echo '<div style="color: var(--text-dark); margin-bottom: 0.5rem;">' . htmlspecialchars($details) . '</div>';
                                            }
                                            ?>

                                            <!-- User Agent (tronqué) -->
                                            <?php
                                            $user_agent = $log_source === 'database' ? $log['user_agent'] : $log['user_agent'];
                                            if (!empty($user_agent)):
                                            ?>
                                                <div style="font-size: 0.8rem; color: var(--text-light); margin-top: 0.5rem;">
                                                    <i class="fas fa-desktop"></i>
                                                    <?php
                                                    // Extraire le navigateur principal
                                                    if (strpos($user_agent, 'Chrome') !== false) {
                                                        echo 'Chrome';
                                                    } elseif (strpos($user_agent, 'Firefox') !== false) {
                                                        echo 'Firefox';
                                                    } elseif (strpos($user_agent, 'Safari') !== false) {
                                                        echo 'Safari';
                                                    } elseif (strpos($user_agent, 'Edge') !== false) {
                                                        echo 'Edge';
                                                    } else {
                                                        echo 'Autre';
                                                    }

                                                    // Détecter mobile
                                                    if (strpos($user_agent, 'Mobile') !== false || strpos($user_agent, 'Android') !== false) {
                                                        echo ' (Mobile)';
                                                    }
                                                    ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </td>

                                    <!-- IP -->
                                    <td style="padding: 1rem; vertical-align: top;">
                                        <div style="font-family: monospace; font-size: 0.9rem; color: var(--text-dark);">
                                            <?php
                                            $ip = $log_source === 'database' ? $log['ip_address'] : $log['ip'];
                                            echo htmlspecialchars(isset($ip) ? $ip : 'N/A');
                                            ?>
                                        </div>
                                        <?php if (!empty($ip) && $ip !== '::1' && $ip !== '127.0.0.1'): ?>
                                            <div style="font-size: 0.8rem; color: var(--text-light);">
                                                <i class="fas fa-globe"></i> Externe
                                            </div>
                                        <?php else: ?>
                                            <div style="font-size: 0.8rem; color: var(--text-light);">
                                                <i class="fas fa-home"></i> Local
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" style="padding: 3rem; text-align: center; color: var(--text-light);">
                                    <i class="fas fa-search" style="font-size: 3rem; margin-bottom: 1rem; opacity: 0.3;"></i>
                                    <p>Aucun log trouvé avec les critères sélectionnés</p>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
        <div style="background: white; padding: 1rem; border-radius: 10px; box-shadow: var(--shadow); margin-top: 1rem;">
            <div style="display: flex; justify-content: center; align-items: center; gap: 0.5rem; flex-wrap: wrap;">
                <!-- Première page -->
                <?php if ($page > 1): ?>
                    <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => 1])); ?>"
                       class="pagination-btn">
                        <i class="fas fa-angle-double-left"></i>
                    </a>
                    <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page - 1])); ?>"
                       class="pagination-btn">
                        <i class="fas fa-angle-left"></i>
                    </a>
                <?php endif; ?>

                <!-- Pages autour de la page actuelle -->
                <?php
                $start_page = max(1, $page - 2);
                $end_page = min($total_pages, $page + 2);

                for ($i = $start_page; $i <= $end_page; $i++):
                ?>
                    <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $i])); ?>"
                       class="pagination-btn <?php echo $i == $page ? 'active' : ''; ?>">
                        <?php echo $i; ?>
                    </a>
                <?php endfor; ?>

                <!-- Dernière page -->
                <?php if ($page < $total_pages): ?>
                    <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page + 1])); ?>"
                       class="pagination-btn">
                        <i class="fas fa-angle-right"></i>
                    </a>
                    <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $total_pages])); ?>"
                       class="pagination-btn">
                        <i class="fas fa-angle-double-right"></i>
                    </a>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</main>

<!-- Modal pour les détails d'un log -->
<div id="logDetailModal" class="modal" style="display: none;">
    <div class="modal-content" style="max-width: 800px;">
        <div class="modal-header">
            <h3><i class="fas fa-info-circle"></i> Détails du Log</h3>
            <span class="modal-close">&times;</span>
        </div>
        <div class="modal-body" id="logDetailContent">
            <div style="text-align: center; padding: 2rem;">
                <i class="fas fa-spinner fa-spin" style="font-size: 2rem; color: var(--primary-color);"></i>
                <p>Chargement des détails...</p>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

<style>
.stat-card {
    background: white;
    padding: 1.5rem;
    border-radius: 10px;
    box-shadow: var(--shadow);
    text-align: center;
    border-left: 4px solid var(--primary-color);
}

.stat-number {
    font-size: 2rem;
    font-weight: bold;
    color: var(--primary-color);
    margin-bottom: 0.5rem;
}

.stat-label {
    color: var(--text-light);
    font-size: 0.9rem;
}

.pagination-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 40px;
    height: 40px;
    background: white;
    border: 1px solid var(--border-color);
    color: var(--text-dark);
    text-decoration: none;
    border-radius: 5px;
    transition: all 0.3s ease;
}

.pagination-btn:hover {
    background: var(--primary-color);
    color: white;
    text-decoration: none;
}

.pagination-btn.active {
    background: var(--primary-color);
    color: white;
    border-color: var(--primary-color);
}

.btn-detail {
    background: none;
    border: 1px solid var(--border-color);
    color: var(--text-light);
    padding: 0.25rem 0.5rem;
    border-radius: 3px;
    cursor: pointer;
    transition: all 0.3s ease;
    font-size: 0.8rem;
}

.btn-detail:hover {
    background: var(--primary-color);
    color: white;
    border-color: var(--primary-color);
}

/* Hover effect pour les lignes du tableau */
tbody tr:hover {
    background-color: #f8f9fa !important;
}

/* Responsive */
@media (max-width: 768px) {
    .container {
        padding: 1rem 0 !important;
    }

    table {
        font-size: 0.9rem;
    }

    th, td {
        padding: 0.75rem !important;
    }

    .stat-card {
        padding: 1rem;
    }

    .stat-number {
        font-size: 1.5rem;
    }
}
</style>

<script>
// Fonction pour exporter les logs
function exportLogs() {
    const params = new URLSearchParams(window.location.search);
    params.set('export', '1');
    window.open('api/export_logs.php?' + params.toString(), '_blank');
    showNotification('Export en cours...', 'info');
}

// Fonction pour nettoyer les anciens logs
function clearOldLogs() {
    if (confirm('Êtes-vous sûr de vouloir supprimer les logs de plus de 90 jours ?')) {
        fetch('api/clear_old_logs.php', {
            method: 'POST'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification(`${data.deleted} logs supprimés`, 'success');
                setTimeout(() => window.location.reload(), 1000);
            } else {
                showNotification(data.message || 'Erreur lors du nettoyage', 'error');
            }
        })
        .catch(error => {
            console.error('Erreur:', error);
            showNotification('Erreur lors du nettoyage', 'error');
        });
    }
}

// Fonction pour actualiser les logs
function refreshLogs() {
    window.location.reload();
}

// Fonction pour afficher les détails d'un log
function showLogDetail(logId) {
    const modal = document.getElementById('logDetailModal');
    const content = document.getElementById('logDetailContent');

    // Afficher le modal avec le loader
    modal.style.display = 'flex';
    content.innerHTML = `
        <div style="text-align: center; padding: 2rem;">
            <i class="fas fa-spinner fa-spin" style="font-size: 2rem; color: var(--primary-color);"></i>
            <p>Chargement des détails...</p>
        </div>
    `;

    // Récupérer les détails du log
    fetch(`api/log_detail.php?id=${logId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayLogDetail(data.log);
            } else {
                content.innerHTML = `
                    <div style="text-align: center; padding: 2rem; color: var(--text-light);">
                        <i class="fas fa-exclamation-triangle" style="font-size: 2rem; margin-bottom: 1rem;"></i>
                        <p>Erreur lors du chargement : ${data.message}</p>
                    </div>
                `;
            }
        })
        .catch(error => {
            console.error('Erreur:', error);
            content.innerHTML = `
                <div style="text-align: center; padding: 2rem; color: var(--text-light);">
                    <i class="fas fa-exclamation-triangle" style="font-size: 2rem; margin-bottom: 1rem;"></i>
                    <p>Erreur de connexion</p>
                </div>
            `;
        });
}

// Fonction pour afficher les détails du log
function displayLogDetail(log) {
    const content = document.getElementById('logDetailContent');

    content.innerHTML = `
        <div style="display: grid; gap: 1.5rem;">
            <!-- Informations principales -->
            <div style="background: #f8f9fa; padding: 1.5rem; border-radius: 8px;">
                <h4 style="color: var(--primary-color); margin-bottom: 1rem;">
                    <i class="fas fa-info-circle"></i> Informations Principales
                </h4>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
                    <div>
                        <strong>ID :</strong> ${log.id}
                    </div>
                    <div>
                        <strong>Date/Heure :</strong><br>
                        ${new Date(log.created_at).toLocaleString('fr-FR')}
                    </div>
                    <div>
                        <strong>Action :</strong><br>
                        <span style="background: var(--primary-color); color: white; padding: 0.25rem 0.5rem; border-radius: 3px; font-size: 0.9rem;">
                            ${log.action}
                        </span>
                    </div>
                    <div>
                        <strong>Adresse IP :</strong><br>
                        <code>${log.ip_address || 'N/A'}</code>
                    </div>
                </div>
                ${log.details ? `
                    <div style="margin-top: 1rem;">
                        <strong>Détails :</strong><br>
                        <div style="background: white; padding: 1rem; border-radius: 5px; margin-top: 0.5rem;">
                            ${log.details}
                        </div>
                    </div>
                ` : ''}
            </div>

            <!-- Informations utilisateur -->
            <div style="background: #f8f9fa; padding: 1.5rem; border-radius: 8px;">
                <h4 style="color: var(--primary-color); margin-bottom: 1rem;">
                    <i class="fas fa-user"></i> Utilisateur
                </h4>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
                    <div>
                        <strong>Nom :</strong><br>
                        ${log.user.prenom || ''} ${log.user.nom || 'Utilisateur inconnu'}
                    </div>
                    <div>
                        <strong>Email :</strong><br>
                        ${log.user.email || 'N/A'}
                    </div>
                    <div>
                        <strong>Rôle :</strong><br>
                        <span style="background: var(--accent-color); color: white; padding: 0.25rem 0.5rem; border-radius: 3px; font-size: 0.9rem;">
                            ${log.user.role || 'inconnu'}
                        </span>
                    </div>
                    <div>
                        <strong>Filière :</strong><br>
                        ${log.user.filiere || 'Non spécifiée'}
                    </div>
                </div>
            </div>

            <!-- Informations techniques -->
            <div style="background: #f8f9fa; padding: 1.5rem; border-radius: 8px;">
                <h4 style="color: var(--primary-color); margin-bottom: 1rem;">
                    <i class="fas fa-desktop"></i> Informations Techniques
                </h4>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
                    <div>
                        <strong>Navigateur :</strong><br>
                        ${log.user_agent_info.browser} ${log.user_agent_info.version}
                    </div>
                    <div>
                        <strong>Système :</strong><br>
                        ${log.user_agent_info.os}
                    </div>
                    <div>
                        <strong>Appareil :</strong><br>
                        ${log.user_agent_info.device}
                    </div>
                </div>
                ${log.user_agent ? `
                    <div style="margin-top: 1rem;">
                        <strong>User Agent complet :</strong><br>
                        <div style="background: white; padding: 1rem; border-radius: 5px; margin-top: 0.5rem; font-family: monospace; font-size: 0.8rem; word-break: break-all;">
                            ${log.user_agent}
                        </div>
                    </div>
                ` : ''}
            </div>

            <!-- Logs connexes -->
            ${log.related_logs && log.related_logs.length > 0 ? `
                <div style="background: #f8f9fa; padding: 1.5rem; border-radius: 8px;">
                    <h4 style="color: var(--primary-color); margin-bottom: 1rem;">
                        <i class="fas fa-history"></i> Activité Connexe (±1h)
                    </h4>
                    <div style="max-height: 200px; overflow-y: auto;">
                        ${log.related_logs.map(relatedLog => `
                            <div style="background: white; padding: 0.75rem; border-radius: 5px; margin-bottom: 0.5rem; border-left: 3px solid var(--accent-color);">
                                <div style="display: flex; justify-content: space-between; align-items: center;">
                                    <strong>${relatedLog.action}</strong>
                                    <span style="font-size: 0.8rem; color: var(--text-light);">
                                        ${new Date(relatedLog.created_at).toLocaleTimeString('fr-FR')}
                                    </span>
                                </div>
                                ${relatedLog.details ? `<div style="font-size: 0.9rem; color: var(--text-light); margin-top: 0.25rem;">${relatedLog.details}</div>` : ''}
                            </div>
                        `).join('')}
                    </div>
                </div>
            ` : ''}
        </div>
    `;
}

// Auto-refresh toutes les 30 secondes si on est sur la première page
<?php if ($page == 1 && empty($filter_action) && empty($filter_user) && empty($filter_date)): ?>
setInterval(function() {
    // Actualiser seulement si l'utilisateur n'a pas interagi récemment
    if (document.hidden === false) {
        const lastActivity = localStorage.getItem('lastActivity');
        const now = Date.now();

        if (!lastActivity || (now - parseInt(lastActivity)) > 30000) {
            window.location.reload();
        }
    }
}, 30000);

// Tracker l'activité utilisateur
document.addEventListener('click', function() {
    localStorage.setItem('lastActivity', Date.now());
});

document.addEventListener('keypress', function() {
    localStorage.setItem('lastActivity', Date.now());
});
<?php endif; ?>

// Highlight des termes de recherche
document.addEventListener('DOMContentLoaded', function() {
    const searchTerms = [
        '<?php echo addslashes($filter_action); ?>',
        '<?php echo addslashes($filter_user); ?>'
    ].filter(term => term.length > 0);

    if (searchTerms.length > 0) {
        const tableBody = document.querySelector('tbody');
        if (tableBody) {
            searchTerms.forEach(term => {
                const regex = new RegExp(`(${term})`, 'gi');
                tableBody.innerHTML = tableBody.innerHTML.replace(regex, '<mark>$1</mark>');
            });
        }
    }
});
</script>
