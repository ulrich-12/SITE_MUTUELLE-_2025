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

try {
    $sender_id = $_SESSION['user_id'];
    $subject = trim(isset($_POST['subject']) ? $_POST['subject'] : '');
    $message = trim(isset($_POST['message']) ? $_POST['message'] : '');
    $is_public = isset($_POST['is_public']) && $_POST['is_public'] === '1';
    $recipient_id = $is_public ? null : intval(isset($_POST['recipient_id']) ? $_POST['recipient_id'] : 0);

    // Validation des données
    if (empty($subject)) {
        echo json_encode(['success' => false, 'message' => 'Le sujet est obligatoire']);
        exit;
    }

    if (empty($message)) {
        echo json_encode(['success' => false, 'message' => 'Le message est obligatoire']);
        exit;
    }

    if (!$is_public && $recipient_id <= 0) {
        echo json_encode(['success' => false, 'message' => 'Destinataire obligatoire pour un message privé']);
        exit;
    }

    // Vérifier que le destinataire existe (pour les messages privés)
    if (!$is_public) {
        $recipient = getUserById($recipient_id);
        if (!$recipient) {
            echo json_encode(['success' => false, 'message' => 'Destinataire introuvable']);
            exit;
        }
    }

    // Envoyer le message
    $result = sendMessage($sender_id, $recipient_id, $subject, $message, $is_public);

    if ($result) {
        echo json_encode([
            'success' => true,
            'message' => $is_public ? 'Annonce publiée avec succès' : 'Message envoyé avec succès'
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Erreur lors de l\'envoi du message']);
    }

} catch (Exception $e) {
    error_log("Erreur send_message : " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Erreur interne du serveur']);
}
?>