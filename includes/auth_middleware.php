<?php
/**
 * Middleware d'authentification et de contrôle d'accès
 */

require_once 'db.php';

/**
 * Fonction pour vérifier l'authentification et les permissions
 */
function checkAuth($requiredPermission = null, $requiredRole = null, $redirectUrl = 'login.php') {
    // Démarrer la session si elle n'est pas déjà démarrée
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    // Vérifier si l'utilisateur est connecté
    if (!isset($_SESSION['user_id'])) {
        header("Location: $redirectUrl?error=not_logged_in");
        exit;
    }
    
    $userId = $_SESSION['user_id'];
    
    // Vérifier si l'utilisateur existe toujours et est actif
    $user = getUserById($userId);
    if (!$user || !$user['active']) {
        session_destroy();
        header("Location: $redirectUrl?error=account_disabled");
        exit;
    }
    
    // Mettre à jour les variables de session si nécessaire
    if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== $user['role']) {
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_name'] = $user['prenom'] . ' ' . $user['nom'];
        $_SESSION['user_filiere'] = $user['filiere'];
        $_SESSION['user_niveau'] = $user['niveau'];
    }
    
    // Vérifier les permissions si requises
    if ($requiredPermission && !hasPermission($userId, $requiredPermission)) {
        header("Location: index.php?error=access_denied&permission=" . urlencode($requiredPermission));
        exit;
    }
    
    // Vérifier le rôle si requis
    if ($requiredRole && !hasRole($userId, $requiredRole)) {
        header("Location: index.php?error=insufficient_role&required=" . urlencode($requiredRole));
        exit;
    }
    
    return $user;
}

/**
 * Fonction pour afficher les erreurs d'accès
 */
function displayAccessError() {
    if (isset($_GET['error'])) {
        $error = $_GET['error'];
        $message = '';
        $type = 'error';
        
        switch ($error) {
            case 'not_logged_in':
                $message = 'Vous devez être connecté pour accéder à cette page.';
                break;
            case 'account_disabled':
                $message = 'Votre compte a été désactivé. Contactez l\'administration.';
                break;
            case 'access_denied':
                $permission = isset($_GET['permission']) ? $_GET['permission'] : 'inconnue';
                $message = "Accès refusé. Permission requise : $permission";
                break;
            case 'insufficient_role':
                $required = isset($_GET['required']) ? $_GET['required'] : 'inconnu';
                $message = "Rôle insuffisant. Rôle requis : $required";
                break;
            default:
                $message = 'Erreur d\'accès inconnue.';
        }
        
        if ($message) {
            echo "<div class='alert alert-$type' style='background: #ffebee; color: #d32f2f; padding: 1rem; border-radius: 5px; margin: 1rem 0; border-left: 4px solid #f44336;'>";
            echo "<strong>⚠️ Erreur d'accès :</strong> $message";
            echo "</div>";
        }
    }
}

/**
 * Fonction pour obtenir le badge de rôle HTML
 */
function getRoleBadge($role) {
    $roleConfig = [
        'etudiant' => ['name' => 'Étudiant', 'color' => '#2196f3'],
        'moderateur' => ['name' => 'Modérateur', 'color' => '#ff9800'],
        'admin' => ['name' => 'Administrateur', 'color' => '#4caf50'],
        'super_admin' => ['name' => 'Super Admin', 'color' => '#f44336']
    ];
    
    $config = isset($roleConfig[$role]) ? $roleConfig[$role] : $roleConfig['etudiant'];
    
    return "<span style='background: {$config['color']}; color: white; padding: 0.25rem 0.5rem; border-radius: 10px; font-size: 0.8rem; font-weight: bold;'>{$config['name']}</span>";
}

/**
 * Fonction pour vérifier si l'utilisateur peut accéder à une fonctionnalité
 */
function canAccess($feature) {
    if (!isset($_SESSION['user_id'])) {
        return false;
    }
    
    $featurePermissions = [
        'dashboard' => 'access_dashboard',
        'upload' => 'upload_documents',
        'role_management' => 'modify_roles',
        'advanced_stats' => 'view_advanced_stats',
        'system_logs' => 'view_system_logs',
        'public_announcements' => 'create_public_announcements',
        'moderate_content' => 'moderate_messages'
    ];
    
    $permission = isset($featurePermissions[$feature]) ? $featurePermissions[$feature] : null;
    
    if ($permission) {
        return hasPermission($_SESSION['user_id'], $permission);
    }
    
    return true; // Si aucune permission spécifique n'est requise
}

/**
 * Fonction pour afficher un menu conditionnel basé sur les rôles
 */
function renderRoleBasedMenu() {
    if (!isset($_SESSION['user_id'])) {
        return '';
    }
    
    $menu = '';
    
    // Menu pour tous les utilisateurs connectés
    $menu .= '<li class="nav-item"><a href="bank.php" class="nav-link">Banque d\'épreuves</a></li>';
    $menu .= '<li class="nav-item"><a href="results.php" class="nav-link">Résultats</a></li>';
    $menu .= '<li class="nav-item"><a href="messages.php" class="nav-link">Messages</a></li>';
    
    // Menu pour modérateurs et plus
    if (canAccess('dashboard')) {
        $menu .= '<li class="nav-item"><a href="dashboard.php" class="nav-link">Tableau de bord</a></li>';
    }
    
    if (canAccess('upload')) {
        $menu .= '<li class="nav-item"><a href="upload_document.php" class="nav-link">Upload</a></li>';
    }
    
    // Menu pour administrateurs
    if (canAccess('role_management') || canAccess('system_logs')) {
        $menu .= '<li class="nav-item dropdown">';
        $menu .= '<a href="#" class="nav-link dropdown-toggle">Administration</a>';
        $menu .= '<ul class="dropdown-menu">';
        if (canAccess('role_management')) {
            $menu .= '<li><a href="manage_roles.php" class="dropdown-link">Gestion des rôles</a></li>';
        }
        if (canAccess('system_logs')) {
            $menu .= '<li><a href="system_logs.php" class="dropdown-link">Logs système</a></li>';
        }
        $menu .= '</ul>';
        $menu .= '</li>';
    }
    
    return $menu;
}

/**
 * Fonction pour logger les actions sensibles
 */
function logAction($userId, $action, $details = null) {
    try {
        $user = getUserById($userId);
        $userName = $user ? $user['prenom'] . ' ' . $user['nom'] : 'Utilisateur inconnu';
        $userRole = $user ? $user['role'] : 'inconnu';
        
        $logEntry = [
            'timestamp' => date('Y-m-d H:i:s'),
            'user_id' => $userId,
            'user_name' => $userName,
            'user_role' => $userRole,
            'action' => $action,
            'details' => $details,
            'ip' => isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : 'unknown',
            'user_agent' => isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : 'unknown'
        ];
        
        // Pour l'instant, on log dans un fichier. Plus tard on pourra créer une table dédiée
        $logFile = __DIR__ . '/../logs/actions.log';
        $logDir = dirname($logFile);
        
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        
        file_put_contents($logFile, json_encode($logEntry) . "\n", FILE_APPEND | LOCK_EX);
        
    } catch (Exception $e) {
        error_log("Erreur lors du logging : " . $e->getMessage());
    }
}

/**
 * Fonction pour vérifier les tentatives de connexion suspectes
 */
function checkSuspiciousActivity($userId) {
    // TODO: Implémenter la détection d'activité suspecte
    // - Connexions depuis plusieurs IP différentes
    // - Tentatives d'accès à des pages non autorisées
    // - Actions répétées en peu de temps
    return false;
}

/**
 * Fonction pour obtenir les permissions d'un utilisateur
 */
function getUserPermissions($userId) {
    $user = getUserById($userId);
    if (!$user) {
        return [];
    }
    
    $role = isset($user['role']) ? $user['role'] : 'etudiant';
    
    $allPermissions = [
        'etudiant' => [
            'view_bank' => 'Consulter la banque d\'épreuves',
            'download_documents' => 'Télécharger des documents',
            'view_results' => 'Consulter ses résultats',
            'use_messaging' => 'Utiliser la messagerie',
            'view_public_announcements' => 'Voir les annonces publiques'
        ],
        'moderateur' => [
            'upload_documents' => 'Uploader des documents',
            'access_dashboard' => 'Accéder au tableau de bord',
            'create_public_announcements' => 'Créer des annonces publiques',
            'moderate_messages' => 'Modérer les messages'
        ],
        'admin' => [
            'manage_users' => 'Gérer les utilisateurs',
            'view_advanced_stats' => 'Voir les statistiques avancées',
            'manage_database' => 'Gérer la base de données',
            'delete_content' => 'Supprimer du contenu'
        ],
        'super_admin' => [
            'modify_roles' => 'Modifier les rôles',
            'manage_admins' => 'Gérer les autres administrateurs',
            'view_system_logs' => 'Voir les logs système',
            'modify_config' => 'Modifier la configuration',
            'backup_restore' => 'Sauvegarder/Restaurer',
            'root_access' => 'Accès root au système'
        ]
    ];
    
    $userPermissions = [];
    
    // Ajouter les permissions selon la hiérarchie
    $roleHierarchy = ['etudiant', 'moderateur', 'admin', 'super_admin'];
    $userRoleIndex = array_search($role, $roleHierarchy);
    
    if ($userRoleIndex !== false) {
        for ($i = 0; $i <= $userRoleIndex; $i++) {
            $currentRole = $roleHierarchy[$i];
            if (isset($allPermissions[$currentRole])) {
                $userPermissions = array_merge($userPermissions, $allPermissions[$currentRole]);
            }
        }
    }
    
    return $userPermissions;
}
?>
