<?php
/**
 * Compatibilité pour PHP 5.4+ (version legacy)
 * Pour les environnements avec des versions PHP très anciennes
 */

// Vérification de la version PHP minimale absolue
if (version_compare(PHP_VERSION, '5.4.0', '<')) {
    die('Cette application nécessite au minimum PHP 5.4. Version actuelle : ' . PHP_VERSION);
}

// Configuration des erreurs pour PHP 5.4+
if (version_compare(PHP_VERSION, '7.0.0', '>=')) {
    error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
} else {
    error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT);
}

/**
 * Polyfills pour PHP 5.4+
 */

// Polyfill pour password_hash (PHP 5.5+)
if (!function_exists('password_hash')) {
    define('PASSWORD_DEFAULT', 1);
    define('PASSWORD_BCRYPT', 1);

    function password_hash($password, $algo, $options = array()) {
        $cost = isset($options['cost']) ? $options['cost'] : 10;
        $salt = isset($options['salt']) ? $options['salt'] : null;

        if ($salt === null) {
            // Générer un salt sécurisé
            $random_bytes = '';

            // Essayer différentes sources de randomness
            if (function_exists('openssl_random_pseudo_bytes')) {
                $random_bytes = openssl_random_pseudo_bytes(16, $strong);
                if (!$strong) {
                    $random_bytes = '';
                }
            }

            if (empty($random_bytes) && function_exists('mcrypt_create_iv') && defined('MCRYPT_DEV_URANDOM')) {
                $random_bytes = mcrypt_create_iv(16, MCRYPT_DEV_URANDOM);
            }

            if (empty($random_bytes)) {
                // Fallback basique
                for ($i = 0; $i < 16; $i++) {
                    $random_bytes .= chr(mt_rand(0, 255));
                }
            }

            $salt = '$2y$' . sprintf('%02d', $cost) . '$' . substr(str_replace('+', '.', base64_encode($random_bytes)), 0, 22);
        }

        return crypt($password, $salt);
    }
}

if (!function_exists('password_verify')) {
    function password_verify($password, $hash) {
        return hash_equals($hash, crypt($password, $hash));
    }
}

if (!function_exists('hash_equals')) {
    function hash_equals($known_string, $user_string) {
        if (strlen($known_string) !== strlen($user_string)) {
            return false;
        }
        
        $result = 0;
        for ($i = 0; $i < strlen($known_string); $i++) {
            $result |= ord($known_string[$i]) ^ ord($user_string[$i]);
        }
        
        return $result === 0;
    }
}

// Polyfill pour random_bytes (PHP 7.0+)
if (!function_exists('random_bytes')) {
    function random_bytes($length) {
        // Essayer OpenSSL si disponible
        if (function_exists('openssl_random_pseudo_bytes')) {
            $bytes = openssl_random_pseudo_bytes($length, $strong);
            if ($strong === true) {
                return $bytes;
            }
        }

        // Essayer mcrypt si disponible
        if (function_exists('mcrypt_create_iv') && defined('MCRYPT_DEV_URANDOM')) {
            return mcrypt_create_iv($length, MCRYPT_DEV_URANDOM);
        }

        // Fallback avec /dev/urandom sur Unix
        if (is_readable('/dev/urandom')) {
            $fp = fopen('/dev/urandom', 'rb');
            if ($fp !== false) {
                $bytes = fread($fp, $length);
                fclose($fp);
                if (strlen($bytes) === $length) {
                    return $bytes;
                }
            }
        }

        // Fallback très basique (non cryptographiquement sûr)
        $bytes = '';
        for ($i = 0; $i < $length; $i++) {
            $bytes .= chr(mt_rand(0, 255));
        }
        return $bytes;
    }
}

// Polyfill pour array_column (PHP 5.5+)
if (!function_exists('array_column')) {
    function array_column($array, $column_key, $index_key = null) {
        $result = array();
        foreach ($array as $row) {
            $key = $index_key === null ? null : $row[$index_key];
            $val = $row[$column_key];
            if ($index_key === null) {
                $result[] = $val;
            } else {
                $result[$key] = $val;
            }
        }
        return $result;
    }
}

// Polyfill pour boolval (PHP 5.5+)
if (!function_exists('boolval')) {
    function boolval($val) {
        return (bool) $val;
    }
}

// Polyfill pour json_last_error_msg (PHP 5.5+)
if (!function_exists('json_last_error_msg')) {
    function json_last_error_msg() {
        static $errors = array(
            JSON_ERROR_NONE => 'No error',
            JSON_ERROR_DEPTH => 'Maximum stack depth exceeded',
            JSON_ERROR_STATE_MISMATCH => 'Underflow or the modes mismatch',
            JSON_ERROR_CTRL_CHAR => 'Unexpected control character found',
            JSON_ERROR_SYNTAX => 'Syntax error, malformed JSON',
            JSON_ERROR_UTF8 => 'Malformed UTF-8 characters, possibly incorrectly encoded'
        );
        
        $error = json_last_error();
        return isset($errors[$error]) ? $errors[$error] : 'Unknown error';
    }
}

/**
 * Classe utilitaire pour PHP 5.4+
 */
class LegacyCompatibilityHelper {
    
    /**
     * Version sécurisée de json_encode
     */
    public static function safe_json_encode($value, $flags = 0, $depth = 512) {
        if (version_compare(PHP_VERSION, '5.5.0', '>=')) {
            $json = json_encode($value, $flags, $depth);
        } else {
            $json = json_encode($value, $flags);
        }
        
        if ($json === false) {
            $error = json_last_error();
            $message = function_exists('json_last_error_msg') ? json_last_error_msg() : 'JSON error';
            throw new InvalidArgumentException("JSON encoding failed: $message (error code: $error)");
        }
        
        return $json;
    }
    
    /**
     * Version sécurisée de json_decode
     */
    public static function safe_json_decode($json, $associative = false, $depth = 512, $flags = 0) {
        if (version_compare(PHP_VERSION, '5.4.0', '>=')) {
            $data = json_decode($json, $associative, $depth, $flags);
        } else {
            $data = json_decode($json, $associative, $depth);
        }
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            $error = json_last_error();
            $message = function_exists('json_last_error_msg') ? json_last_error_msg() : 'JSON error';
            throw new InvalidArgumentException("JSON decoding failed: $message (error code: $error)");
        }
        
        return $data;
    }
    
    /**
     * Fonction pour nettoyer les chaînes
     */
    public static function clean_string($string) {
        $string = preg_replace('/[\x00-\x1F\x7F]/', '', $string);
        $string = preg_replace('/\s+/', ' ', $string);
        return trim($string);
    }
    
    /**
     * Fonction pour valider les emails
     */
    public static function validate_email($email) {
        $email = self::clean_string($email);
        $email = filter_var($email, FILTER_VALIDATE_EMAIL);
        return $email !== false ? $email : null;
    }
    
    /**
     * Générer un mot de passe sécurisé
     */
    public static function generate_password($length = 12) {
        if (function_exists('random_bytes')) {
            $bytes = random_bytes($length);
            return substr(base64_encode($bytes), 0, $length);
        } else {
            $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*';
            $password = '';
            for ($i = 0; $i < $length; $i++) {
                $password .= $characters[mt_rand(0, strlen($characters) - 1)];
            }
            return $password;
        }
    }
    
    /**
     * Hasher un mot de passe
     */
    public static function hash_password($password) {
        if (function_exists('password_hash')) {
            return password_hash($password, PASSWORD_DEFAULT);
        } else {
            return hash('sha256', $password . 'salt_udm_legacy');
        }
    }
    
    /**
     * Vérifier un mot de passe
     */
    public static function verify_password($password, $hash) {
        if (function_exists('password_verify')) {
            return password_verify($password, $hash);
        } else {
            return hash_equals($hash, hash('sha256', $password . 'salt_udm_legacy'));
        }
    }
    
    /**
     * Échapper pour HTML
     */
    public static function escape_html($string) {
        return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
    }
    
    /**
     * Générer un UUID simple
     */
    public static function generate_uuid() {
        return sprintf(
            '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );
    }
    
    /**
     * Formater les tailles de fichiers
     */
    public static function format_bytes($size, $precision = 2) {
        $units = array('B', 'KB', 'MB', 'GB', 'TB');
        
        for ($i = 0; $size > 1024 && $i < count($units) - 1; $i++) {
            $size /= 1024;
        }
        
        return round($size, $precision) . ' ' . $units[$i];
    }
    
    /**
     * Vérifier la compatibilité
     */
    public static function check_compatibility() {
        $issues = array();
        $warnings = array();
        
        // Vérifier PHP
        if (version_compare(PHP_VERSION, '5.4.0', '<')) {
            $issues[] = 'PHP 5.4+ requis (version actuelle : ' . PHP_VERSION . ')';
        } elseif (version_compare(PHP_VERSION, '7.4.0', '<')) {
            $warnings[] = 'PHP 7.4+ recommandé pour de meilleures performances (version actuelle : ' . PHP_VERSION . ')';
        }
        
        // Vérifier les extensions critiques
        $required_extensions = array('pdo', 'pdo_mysql', 'mbstring', 'json');
        foreach ($required_extensions as $ext) {
            if (!extension_loaded($ext)) {
                $issues[] = "Extension PHP manquante : $ext";
            }
        }
        
        // Vérifier les extensions recommandées
        $recommended_extensions = array('curl', 'gd', 'zip', 'xml', 'openssl');
        foreach ($recommended_extensions as $ext) {
            if (!extension_loaded($ext)) {
                $warnings[] = "Extension PHP recommandée manquante : $ext";
            }
        }
        
        return array(
            'issues' => $issues,
            'warnings' => $warnings,
            'compatible' => empty($issues)
        );
    }
    
    /**
     * Obtenir les informations sur l'environnement
     */
    public static function get_environment_info() {
        return array(
            'php_version' => PHP_VERSION,
            'php_sapi' => php_sapi_name(),
            'os' => PHP_OS,
            'extensions' => get_loaded_extensions(),
            'memory_limit' => ini_get('memory_limit'),
            'max_execution_time' => ini_get('max_execution_time'),
            'upload_max_filesize' => ini_get('upload_max_filesize'),
            'post_max_size' => ini_get('post_max_size'),
            'timezone' => date_default_timezone_get()
        );
    }
}

/**
 * Configuration sécurisée des sessions pour PHP 5.4+
 */
function configureLegacySessions() {
    if (session_status() === PHP_SESSION_NONE) {
        ini_set('session.cookie_httponly', 1);
        ini_set('session.use_strict_mode', 1);
        ini_set('session.gc_maxlifetime', 3600);
        ini_set('session.cookie_lifetime', 0);
        
        // Paramètres de sécurité disponibles selon la version
        if (version_compare(PHP_VERSION, '5.2.0', '>=')) {
            ini_set('session.cookie_secure', isset($_SERVER['HTTPS']) ? 1 : 0);
        }
    }
}

// Initialisation automatique
configureLegacySessions();

// Définir une constante pour indiquer que le mode legacy est actif
define('LEGACY_PHP_MODE', true);
define('DEVELOPMENT_MODE', true); // Pour éviter l'erreur dans compatibility.php
