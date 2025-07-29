<?php
session_start();
require_once '../includes/db.php';

// Vérification de l'authentification et des permissions
if (!isset($_SESSION['user_id']) || !hasPermission($_SESSION['user_id'], 'view_advanced_stats')) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Accès refusé']);
    exit;
}

try {
    // Statistiques des utilisateurs
    $stats_users = executeQuery("SELECT 
        COUNT(*) as total_users,
        COUNT(CASE WHEN role = 'etudiant' THEN 1 END) as etudiants,
        COUNT(CASE WHEN role = 'moderateur' THEN 1 END) as moderateurs,
        COUNT(CASE WHEN role = 'admin' THEN 1 END) as admins,
        COUNT(CASE WHEN role = 'super_admin' THEN 1 END) as super_admins,
        COUNT(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) THEN 1 END) as new_users_month,
        COUNT(CASE WHEN last_login >= DATE_SUB(NOW(), INTERVAL 24 HOUR) THEN 1 END) as active_today
        FROM users")->fetch();

    // Statistiques des documents
    $stats_docs = executeQuery("SELECT 
        COUNT(*) as total_documents,
        COUNT(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) THEN 1 END) as new_docs_month,
        COUNT(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY) THEN 1 END) as new_docs_week,
        SUM(download_count) as total_downloads,
        SUM(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) THEN download_count ELSE 0 END) as downloads_month
        FROM documents")->fetch();

    // Statistiques des messages
    $stats_messages = executeQuery("SELECT 
        COUNT(*) as total_messages,
        COUNT(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) THEN 1 END) as new_messages_month,
        COUNT(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY) THEN 1 END) as new_messages_week,
        COUNT(CASE WHEN is_public = 1 THEN 1 END) as public_messages,
        COUNT(CASE WHEN is_public = 0 THEN 1 END) as private_messages
        FROM messages")->fetch();

    // Statistiques système
    $memory_usage = memory_get_usage(true);
    $memory_peak = memory_get_peak_usage(true);
    $memory_limit = ini_get('memory_limit');
    
    // Convertir la limite mémoire en bytes
    $memory_limit_bytes = convertToBytes($memory_limit);
    $memory_percent = round(($memory_usage / $memory_limit_bytes) * 100, 1);

    // Espace disque
    $disk_free = disk_free_space('.');
    $disk_total = disk_total_space('.');
    $disk_used_percent = round((($disk_total - $disk_free) / $disk_total) * 100, 1);

    // Activité récente (dernières 24h)
    $recent_activity_count = executeQuery("SELECT COUNT(*) as count FROM activity_logs WHERE created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)")->fetch()['count'];

    // Top 5 des documents les plus téléchargés
    $top_documents = executeQuery("SELECT nom, download_count FROM documents ORDER BY download_count DESC LIMIT 5")->fetchAll();

    // Répartition par filière
    $filieres = executeQuery("SELECT filiere, COUNT(*) as count FROM users WHERE filiere IS NOT NULL AND filiere != '' GROUP BY filiere ORDER BY count DESC LIMIT 10")->fetchAll();

    echo json_encode([
        'success' => true,
        'timestamp' => time(),
        'users' => $stats_users,
        'documents' => $stats_docs,
        'messages' => $stats_messages,
        'system' => [
            'memory_usage' => round($memory_usage / 1024 / 1024, 2),
            'memory_peak' => round($memory_peak / 1024 / 1024, 2),
            'memory_limit' => $memory_limit,
            'memory_percent' => $memory_percent,
            'disk_used_percent' => $disk_used_percent,
            'disk_free_gb' => round($disk_free / 1024 / 1024 / 1024, 2),
            'php_version' => PHP_VERSION,
            'server_time' => date('Y-m-d H:i:s')
        ],
        'activity' => [
            'recent_count' => $recent_activity_count
        ],
        'top_documents' => $top_documents,
        'filieres' => $filieres
    ]);

} catch (Exception $e) {
    error_log("Erreur récupération stats admin : " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Erreur lors de la récupération des statistiques']);
}

/**
 * Convertit une valeur de mémoire PHP en bytes
 */
function convertToBytes($value) {
    $value = trim($value);
    $last = strtolower($value[strlen($value)-1]);
    $value = (int) $value;
    
    switch($last) {
        case 'g':
            $value *= 1024;
        case 'm':
            $value *= 1024;
        case 'k':
            $value *= 1024;
    }
    
    return $value;
}
?>
