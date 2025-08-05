<?php
session_start();
require_once '../includes/db.php';

// Vérification de la connexion
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Non autorisé']);
    exit;
}

// Récupération de l'ID utilisateur
$user_id = intval(isset($_GET['id']) ? $_GET['id'] : 0);

if ($user_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'ID utilisateur invalide']);
    exit;
}

try {
    $user = getUserById($user_id);
    
    if ($user) {
        // Retourner seulement les informations publiques
        echo json_encode([
            'success' => true,
            'user' => [
                'id' => $user['id'],
                'prenom' => $user['prenom'],
                'nom' => $user['nom'],
                'filiere' => $user['filiere'],
                'niveau' => $user['niveau']
            ]
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Utilisateur non trouvé']);
    }
    
} catch (Exception $e) {
    error_log("Erreur get_user : " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Erreur lors de la récupération']);
}
?>
