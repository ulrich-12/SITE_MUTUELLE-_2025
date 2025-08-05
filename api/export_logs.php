<?php
session_start();
require_once '../includes/db.php';

// Vérification de l'authentification et des permissions
if (!isset($_SESSION['user_id']) || !hasPermission($_SESSION['user_id'], 'view_system_logs')) {
    http_response_code(403);
    echo "Accès refusé";
    exit;
}

try {
    // Récupérer les paramètres de filtrage
    $filter_action = isset($_GET['action']) ? $_GET['action'] : '';
    $filter_user = isset($_GET['user']) ? $_GET['user'] : '';
    $filter_date = isset($_GET['date']) ? $_GET['date'] : '';
    $log_source = isset($_GET['source']) ? $_GET['source'] : 'database';

    $logs = [];

    if ($log_source === 'database') {
        // Export depuis la base de données
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
                ORDER BY al.created_at DESC";

        $logs = executeQuery($sql, $params)->fetchAll();
    } else {
        // Export depuis le fichier JSON
        $log_file = '../logs/actions.log';
        if (file_exists($log_file)) {
            $file_logs = file($log_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            $file_logs = array_reverse($file_logs);
            
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
                        $logs[] = $log_data;
                    }
                }
            }
        }
    }

    // Définir les en-têtes pour le téléchargement CSV
    $filename = 'export_logs_' . date('Y-m-d_H-i-s') . '.csv';
    
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
    header('Pragma: public');

    // Créer le fichier CSV
    $output = fopen('php://output', 'w');

    // Ajouter le BOM UTF-8 pour Excel
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

    // En-têtes du CSV
    $headers = [
        'ID',
        'Date/Heure',
        'Utilisateur',
        'Email',
        'Rôle',
        'Action',
        'Détails',
        'Adresse IP',
        'Navigateur',
        'Source'
    ];
    
    fputcsv($output, $headers, ';');

    // Données des logs
    foreach ($logs as $log) {
        if ($log_source === 'database') {
            $row = [
                isset($log['id']) ? $log['id'] : '',
                isset($log['created_at']) ? $log['created_at'] : '',
                (isset($log['prenom']) ? $log['prenom'] : '') . ' ' . (isset($log['nom']) ? $log['nom'] : ''),
                isset($log['email']) ? $log['email'] : '',
                isset($log['role']) ? $log['role'] : '',
                isset($log['action']) ? $log['action'] : '',
                isset($log['details']) ? $log['details'] : '',
                isset($log['ip_address']) ? $log['ip_address'] : '',
                extractBrowser(isset($log['user_agent']) ? $log['user_agent'] : ''),
                'Base de données'
            ];
        } else {
            $row = [
                '',
                isset($log['timestamp']) ? $log['timestamp'] : '',
                isset($log['user_name']) ? $log['user_name'] : '',
                '',
                isset($log['user_role']) ? $log['user_role'] : '',
                isset($log['action']) ? $log['action'] : '',
                isset($log['details']) ? $log['details'] : '',
                isset($log['ip']) ? $log['ip'] : '',
                extractBrowser(isset($log['user_agent']) ? $log['user_agent'] : ''),
                'Fichier log'
            ];
        }
        
        fputcsv($output, $row, ';');
    }

    fclose($output);

    // Logger l'action
    logAction($_SESSION['user_id'], 'export_logs', "Export de " . count($logs) . " logs (source: $log_source)");

} catch (Exception $e) {
    error_log("Erreur export logs : " . $e->getMessage());
    http_response_code(500);
    echo "Erreur lors de l'export";
}

/**
 * Fonction pour extraire le navigateur principal du User Agent
 */
function extractBrowser($user_agent) {
    if (empty($user_agent)) return 'Inconnu';
    
    if (strpos($user_agent, 'Chrome') !== false) {
        $browser = 'Chrome';
    } elseif (strpos($user_agent, 'Firefox') !== false) {
        $browser = 'Firefox';
    } elseif (strpos($user_agent, 'Safari') !== false) {
        $browser = 'Safari';
    } elseif (strpos($user_agent, 'Edge') !== false) {
        $browser = 'Edge';
    } else {
        $browser = 'Autre';
    }
    
    // Détecter mobile
    if (strpos($user_agent, 'Mobile') !== false || strpos($user_agent, 'Android') !== false) {
        $browser .= ' (Mobile)';
    }
    
    return $browser;
}
?>
