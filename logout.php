<?php
/**
 * Script de déconnexion sécurisée
 */

session_start();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    // Déjà déconnecté, rediriger vers la page de connexion
    header('Location: login.php?message=already_logged_out');
    exit;
}

// Logger la déconnexion si les fonctions sont disponibles
if (function_exists('logAction')) {
    logAction($_SESSION['user_id'], 'logout', 'Déconnexion utilisateur');
}

// Sauvegarder l'ID utilisateur pour le message
$user_id = $_SESSION['user_id'];

// Détruire toutes les variables de session
$_SESSION = array();

// Détruire le cookie de session si il existe
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Détruire la session
session_destroy();

// Supprimer les cookies "Remember Me" si ils existent
if (isset($_COOKIE['remember_token'])) {
    setcookie('remember_token', '', time() - 3600, '/');
    setcookie('remember_user', '', time() - 3600, '/');
}

// Rediriger vers la page de connexion avec un message de confirmation
header('Location: login.php?message=logged_out');
exit;
?>
