<?php
/**
 * Fichier de compatibilité pour assurer le bon fonctionnement
 * sur différentes versions de PHP (7.4+ à 8.3+)
 */

// Inclure les polyfills
require_once __DIR__ . '/polyfills.php';

// Vérification de la version PHP
if (version_compare(PHP_VERSION, '7.4.0', '<')) {
    die('Cette application nécessite PHP 7.4 ou supérieur. Version actuelle : ' . PHP_VERSION);
}

// Configuration des erreurs selon la version PHP
if (version_compare(PHP_VERSION, '8.0.0', '>=')) {
    // PHP 8.0+ : Gestion des nouvelles erreurs
    error_reporting(E_ALL & ~E_DEPRECATED);
} else {
    // PHP 7.4 : Configuration standard
    error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
}

// Note: Les polyfills sont maintenant dans polyfills.php

/**
 * Fonction pour vérifier la compatibilité de l'environnement
 */
function checkEnvironmentCompatibility() {
    $issues = [];
    
    // Vérifier PHP
    if (version_compare(PHP_VERSION, '7.4.0', '<')) {
        $issues[] = 'PHP 7.4+ requis (version actuelle : ' . PHP_VERSION . ')';
    }
    
    // Vérifier les extensions PHP requises
    $required_extensions = [
        'pdo',
        'pdo_mysql',
        'mbstring',
        'json',
        'session',
        'filter',
        'hash'
    ];
    
    foreach ($required_extensions as $ext) {
        if (!extension_loaded($ext)) {
            $issues[] = "Extension PHP manquante : $ext";
        }
    }
    
    // Vérifier les fonctions critiques
    $required_functions = [
        'password_hash',
        'password_verify',
        'random_bytes',
        'bin2hex',
        'htmlspecialchars',
        'filter_var'
    ];
    
    foreach ($required_functions as $func) {
        if (!function_exists($func)) {
            $issues[] = "Fonction PHP manquante : $func";
        }
    }
    
    return $issues;
}

/**
 * Configuration sécurisée des sessions
 */
function configureSecureSessions() {
    // Configuration sécurisée des sessions
    if (session_status() === PHP_SESSION_NONE) {
        // Paramètres de sécurité
        ini_set('session.cookie_httponly', 1);
        ini_set('session.cookie_secure', isset($_SERVER['HTTPS']) ? 1 : 0);
        ini_set('session.use_strict_mode', 1);
        ini_set('session.cookie_samesite', 'Strict');
        
        // Durée de vie des sessions
        ini_set('session.gc_maxlifetime', 3600); // 1 heure
        ini_set('session.cookie_lifetime', 0); // Jusqu'à fermeture du navigateur
        
        // Régénération de l'ID de session
        if (version_compare(PHP_VERSION, '7.3.0', '>=')) {
            ini_set('session.sid_length', 48);
            ini_set('session.sid_bits_per_character', 6);
        }
    }
}

/**
 * Configuration des en-têtes de sécurité
 */
function setSecurityHeaders() {
    // Protection XSS
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: DENY');
    header('X-XSS-Protection: 1; mode=block');
    
    // CSP basique
    header("Content-Security-Policy: default-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdnjs.cloudflare.com https://images.unsplash.com; img-src 'self' data: https:; font-src 'self' https://cdnjs.cloudflare.com;");
    
    // Autres en-têtes de sécurité
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('Permissions-Policy: geolocation=(), microphone=(), camera=()');
}

/**
 * Fonction pour nettoyer les données d'entrée
 */
function sanitizeInput($data, $type = 'string') {
    if (is_array($data)) {
        return array_map(function($item) use ($type) {
            return sanitizeInput($item, $type);
        }, $data);
    }
    
    switch ($type) {
        case 'email':
            return filter_var(trim($data), FILTER_SANITIZE_EMAIL);
        case 'int':
            return filter_var($data, FILTER_SANITIZE_NUMBER_INT);
        case 'float':
            return filter_var($data, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
        case 'url':
            return filter_var(trim($data), FILTER_SANITIZE_URL);
        case 'string':
        default:
            return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
    }
}

/**
 * Fonction pour valider les données
 */
function validateInput($data, $type = 'string', $options = []) {
    switch ($type) {
        case 'email':
            return filter_var($data, FILTER_VALIDATE_EMAIL) !== false;
        case 'int':
            $min = isset($options['min']) ? $options['min'] : null;
            $max = isset($options['max']) ? $options['max'] : null;
            if ($min !== null || $max !== null) {
                $range = [];
                if ($min !== null) $range['min_range'] = $min;
                if ($max !== null) $range['max_range'] = $max;
                return filter_var($data, FILTER_VALIDATE_INT, ['options' => $range]) !== false;
            }
            return filter_var($data, FILTER_VALIDATE_INT) !== false;
        case 'float':
            return filter_var($data, FILTER_VALIDATE_FLOAT) !== false;
        case 'url':
            return filter_var($data, FILTER_VALIDATE_URL) !== false;
        case 'string':
            $min_length = isset($options['min_length']) ? $options['min_length'] : 0;
            $max_length = isset($options['max_length']) ? $options['max_length'] : PHP_INT_MAX;
            $length = mb_strlen($data);
            return $length >= $min_length && $length <= $max_length;
        default:
            return true;
    }
}

/**
 * Fonction pour générer un token CSRF sécurisé
 */
function generateCSRFToken() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    
    return $_SESSION['csrf_token'];
}

/**
 * Fonction pour vérifier un token CSRF
 */
function verifyCSRFToken($token) {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Configuration automatique au chargement du fichier
 */
configureSecureSessions();

// Vérifier la compatibilité en mode développement
if (defined('DEVELOPMENT_MODE') && DEVELOPMENT_MODE === true) {
    $compatibility_issues = checkEnvironmentCompatibility();
    if (!empty($compatibility_issues)) {
        error_log('Problèmes de compatibilité détectés : ' . implode(', ', $compatibility_issues));
    }
}
