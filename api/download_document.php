<?php
session_start();
require_once '../includes/db.php';

// Vérification de la connexion
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    die('Accès non autorisé');
}

// Vérification de la méthode POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    die('Méthode non autorisée');
}

// Récupération de l'ID du document
$document_id = intval(isset($_POST['document_id']) ? $_POST['document_id'] : 0);

if ($document_id <= 0) {
    http_response_code(400);
    die('ID de document invalide');
}

try {
    // Récupération des informations du document
    $document = getDocumentById($document_id);
    
    if (!$document) {
        http_response_code(404);
        die('Document non trouvé');
    }
    
    // Chemin vers le fichier
    $file_path = '../uploads/' . $document['filename'];
    
    // Vérification de l'existence du fichier
    if (!file_exists($file_path)) {
        http_response_code(404);
        die('Fichier non trouvé sur le serveur');
    }
    
    // Incrémenter le compteur de téléchargements
    incrementDownloadCount($document_id);
    
    // Enregistrer l'activité de téléchargement (optionnel)
    // logDownloadActivity($_SESSION['user_id'], $document_id);
    
    // Préparation du téléchargement
    $file_size = filesize($file_path);
    $file_name = $document['original_filename'];
    
    // Headers pour le téléchargement
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="' . $file_name . '"');
    header('Content-Length: ' . $file_size);
    header('Cache-Control: no-cache, must-revalidate');
    header('Pragma: no-cache');
    header('Expires: 0');
    
    // Nettoyage du buffer de sortie
    if (ob_get_level()) {
        ob_end_clean();
    }
    
    // Lecture et envoi du fichier par chunks pour éviter les problèmes de mémoire
    $chunk_size = 8192; // 8KB chunks
    $handle = fopen($file_path, 'rb');
    
    if ($handle === false) {
        http_response_code(500);
        die('Erreur lors de la lecture du fichier');
    }
    
    while (!feof($handle)) {
        $chunk = fread($handle, $chunk_size);
        echo $chunk;
        flush();
    }
    
    fclose($handle);
    exit;
    
} catch (Exception $e) {
    error_log("Erreur téléchargement document ID $document_id : " . $e->getMessage());
    http_response_code(500);
    die('Erreur interne du serveur');
}

/**
 * Fonction pour enregistrer l'activité de téléchargement (optionnelle)
 */
function logDownloadActivity($user_id, $document_id) {
    try {
        $sql = "INSERT INTO download_logs (user_id, document_id, downloaded_at) VALUES (?, ?, NOW())";
        executeQuery($sql, [$user_id, $document_id]);
    } catch (Exception $e) {
        // Log silencieux, ne pas interrompre le téléchargement
        error_log("Erreur log téléchargement : " . $e->getMessage());
    }
}
?>
