<?php
session_start();
require_once '../includes/db.php';

// Vérification de la connexion
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Non autorisé']);
    exit;
}

// Récupération de la requête de recherche
$query = trim(isset($_GET['q']) ? $_GET['q'] : '');

if (strlen($query) < 2) {
    echo json_encode(['success' => false, 'message' => 'Requête trop courte']);
    exit;
}

try {
    $users = searchUsers($query, $_SESSION['user_id']);
    
    echo json_encode([
        'success' => true,
        'users' => $users,
        'count' => count($users)
    ]);
    
} catch (Exception $e) {
    error_log("Erreur search_users : " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Erreur lors de la recherche']);
}
?>
