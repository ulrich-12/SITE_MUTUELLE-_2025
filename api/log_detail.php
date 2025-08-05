<?php
session_start();
require_once '../includes/db.php';

// Vérification de l'authentification et des permissions
if (!isset($_SESSION['user_id']) || !hasPermission($_SESSION['user_id'], 'view_system_logs')) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Accès refusé']);
    exit;
}

$log_id = intval(isset($_GET['id']) ? $_GET['id'] : 0);

if ($log_id <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'ID de log invalide']);
    exit;
}

try {
    // Récupérer le log détaillé
    $sql = "SELECT 
                al.*,
                u.nom,
                u.prenom,
                u.email,
                u.role,
                u.filiere,
                u.niveau
            FROM activity_logs al 
            LEFT JOIN users u ON al.user_id = u.id 
            WHERE al.id = ?";
    
    $log = executeQuery($sql, [$log_id])->fetch();

    if (!$log) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Log non trouvé']);
        exit;
    }

    // Analyser le User Agent pour plus de détails
    $user_agent_info = parseUserAgent($log['user_agent']);

    // Récupérer les logs connexes (même utilisateur, même session)
    $related_logs = executeQuery("SELECT 
                                    action, 
                                    details, 
                                    created_at 
                                  FROM activity_logs 
                                  WHERE user_id = ? 
                                    AND created_at BETWEEN DATE_SUB(?, INTERVAL 1 HOUR) AND DATE_ADD(?, INTERVAL 1 HOUR)
                                    AND id != ?
                                  ORDER BY created_at DESC 
                                  LIMIT 10", 
                                  [$log['user_id'], $log['created_at'], $log['created_at'], $log_id])->fetchAll();

    echo json_encode([
        'success' => true,
        'log' => [
            'id' => $log['id'],
            'user_id' => $log['user_id'],
            'action' => $log['action'],
            'details' => $log['details'],
            'ip_address' => $log['ip_address'],
            'user_agent' => $log['user_agent'],
            'created_at' => $log['created_at'],
            'user' => [
                'nom' => $log['nom'],
                'prenom' => $log['prenom'],
                'email' => $log['email'],
                'role' => $log['role'],
                'filiere' => $log['filiere'],
                'niveau' => $log['niveau']
            ],
            'user_agent_info' => $user_agent_info,
            'related_logs' => $related_logs
        ]
    ]);

} catch (Exception $e) {
    error_log("Erreur détail log : " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Erreur lors de la récupération du log']);
}

/**
 * Fonction pour analyser le User Agent
 */
function parseUserAgent($user_agent) {
    if (empty($user_agent)) {
        return [
            'browser' => 'Inconnu',
            'version' => '',
            'os' => 'Inconnu',
            'device' => 'Desktop'
        ];
    }

    $info = [
        'browser' => 'Autre',
        'version' => '',
        'os' => 'Inconnu',
        'device' => 'Desktop'
    ];

    // Détecter le navigateur
    if (preg_match('/Chrome\/([0-9.]+)/', $user_agent, $matches)) {
        $info['browser'] = 'Chrome';
        $info['version'] = $matches[1];
    } elseif (preg_match('/Firefox\/([0-9.]+)/', $user_agent, $matches)) {
        $info['browser'] = 'Firefox';
        $info['version'] = $matches[1];
    } elseif (preg_match('/Safari\/([0-9.]+)/', $user_agent, $matches)) {
        $info['browser'] = 'Safari';
        $info['version'] = $matches[1];
    } elseif (preg_match('/Edge\/([0-9.]+)/', $user_agent, $matches)) {
        $info['browser'] = 'Edge';
        $info['version'] = $matches[1];
    }

    // Détecter l'OS
    if (strpos($user_agent, 'Windows NT 10.0') !== false) {
        $info['os'] = 'Windows 10';
    } elseif (strpos($user_agent, 'Windows NT 6.3') !== false) {
        $info['os'] = 'Windows 8.1';
    } elseif (strpos($user_agent, 'Windows NT 6.1') !== false) {
        $info['os'] = 'Windows 7';
    } elseif (strpos($user_agent, 'Mac OS X') !== false) {
        $info['os'] = 'macOS';
    } elseif (strpos($user_agent, 'Linux') !== false) {
        $info['os'] = 'Linux';
    } elseif (strpos($user_agent, 'Android') !== false) {
        $info['os'] = 'Android';
        $info['device'] = 'Mobile';
    } elseif (strpos($user_agent, 'iPhone') !== false) {
        $info['os'] = 'iOS';
        $info['device'] = 'Mobile';
    }

    // Détecter le type d'appareil
    if (strpos($user_agent, 'Mobile') !== false || strpos($user_agent, 'Android') !== false) {
        $info['device'] = 'Mobile';
    } elseif (strpos($user_agent, 'Tablet') !== false || strpos($user_agent, 'iPad') !== false) {
        $info['device'] = 'Tablette';
    }

    return $info;
}
?>
