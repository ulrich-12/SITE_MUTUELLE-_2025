<?php
session_start();
require_once '../includes/db.php';

// Vérification de la connexion
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Non autorisé']);
    exit;
}

// Vérification de la méthode POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
    exit;
}

// Récupération des données JSON
$input = json_decode(file_get_contents('php://input'), true);
$message_id = intval(isset($input['message_id']) ? $input['message_id'] : 0);

if ($message_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'ID de message invalide']);
    exit;
}

try {
    $user_id = $_SESSION['user_id'];
    
    // Vérifier que le message appartient à l'utilisateur
    $message = getMessageById($message_id, $user_id);
    
    if (!$message) {
        echo json_encode(['success' => false, 'message' => 'Message non trouvé']);
        exit;
    }
    
    if ($message['sender_id'] != $user_id) {
        echo json_encode(['success' => false, 'message' => 'Vous ne pouvez supprimer que vos propres messages']);
        exit;
    }
    
    // Supprimer le message
    $result = deleteMessage($message_id, $user_id);
    
    if ($result) {
        echo json_encode(['success' => true, 'message' => 'Message supprimé avec succès']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Erreur lors de la suppression']);
    }
    
} catch (Exception $e) {
    error_log("Erreur delete_message : " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Erreur interne du serveur']);
}
?>
