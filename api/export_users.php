<?php
session_start();
require_once '../includes/db.php';

// Vérification de l'authentification et des permissions
if (!isset($_SESSION['user_id']) || !hasPermission($_SESSION['user_id'], 'manage_users')) {
    http_response_code(403);
    echo "Accès refusé";
    exit;
}

try {
    // Récupérer tous les utilisateurs
    $sql = "SELECT 
                id,
                nom,
                prenom,
                email,
                role,
                filiere,
                niveau,
                created_at,
                last_login
            FROM users 
            ORDER BY created_at DESC";
    
    $users = executeQuery($sql)->fetchAll();

    // Définir les en-têtes pour le téléchargement CSV
    $filename = 'export_utilisateurs_' . date('Y-m-d_H-i-s') . '.csv';
    
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
        'Nom',
        'Prénom',
        'Email',
        'Rôle',
        'Filière',
        'Niveau',
        'Date d\'inscription',
        'Dernière connexion'
    ];
    
    fputcsv($output, $headers, ';');

    // Données des utilisateurs
    foreach ($users as $user) {
        $row = [
            $user['id'],
            $user['nom'],
            $user['prenom'],
            $user['email'],
            $user['role'],
            $user['filiere'] ?: 'Non spécifiée',
            $user['niveau'] ?: 'Non spécifié',
            $user['created_at'] ? date('d/m/Y H:i', strtotime($user['created_at'])) : '',
            $user['last_login'] ? date('d/m/Y H:i', strtotime($user['last_login'])) : 'Jamais'
        ];
        
        fputcsv($output, $row, ';');
    }

    fclose($output);

    // Logger l'action
    logAction($_SESSION['user_id'], 'export_users', "Export de " . count($users) . " utilisateurs");

} catch (Exception $e) {
    error_log("Erreur export utilisateurs : " . $e->getMessage());
    http_response_code(500);
    echo "Erreur lors de l'export";
}
?>
