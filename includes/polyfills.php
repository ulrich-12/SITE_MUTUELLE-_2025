<?php
/**
 * Polyfills PHP pour assurer la compatibilité entre versions
 * Compatible PHP 7.4+ à 8.3+
 */

// Polyfills pour PHP 8.0+
if (!function_exists('str_contains')) {
    /**
     * Polyfill pour str_contains (PHP 8.0+)
     */
    function str_contains($haystack, $needle) {
        return $needle !== '' && mb_strpos($haystack, $needle) !== false;
    }
}

if (!function_exists('str_starts_with')) {
    /**
     * Polyfill pour str_starts_with (PHP 8.0+)
     */
    function str_starts_with($haystack, $needle) {
        return $needle !== '' && strncmp($haystack, $needle, strlen($needle)) === 0;
    }
}

if (!function_exists('str_ends_with')) {
    /**
     * Polyfill pour str_ends_with (PHP 8.0+)
     */
    function str_ends_with($haystack, $needle) {
        return $needle !== '' && substr($haystack, -strlen($needle)) === $needle;
    }
}

if (!function_exists('fdiv')) {
    /**
     * Polyfill pour fdiv (PHP 8.0+)
     */
    function fdiv($dividend, $divisor) {
        return $dividend / $divisor;
    }
}

// Polyfills pour PHP 8.1+
if (!function_exists('array_is_list')) {
    /**
     * Polyfill pour array_is_list (PHP 8.1+)
     */
    function array_is_list($array) {
        if ($array === []) {
            return true;
        }

        $current_key = 0;
        foreach ($array as $key => $noop) {
            if ($key !== $current_key) {
                return false;
            }
            ++$current_key;
        }

        return true;
    }
}

if (!function_exists('enum_exists')) {
    /**
     * Polyfill pour enum_exists (PHP 8.1+)
     */
    function enum_exists($enum, $autoload = true) {
        // Les enums n'existent pas avant PHP 8.1
        return false;
    }
}

// Polyfills pour PHP 8.2+
if (!function_exists('ini_parse_quantity')) {
    /**
     * Polyfill pour ini_parse_quantity (PHP 8.2+)
     */
    function ini_parse_quantity($shorthand) {
        $shorthand = trim($shorthand);
        $last = strtolower($shorthand[strlen($shorthand) - 1]);
        $value = (int) $shorthand;

        switch ($last) {
            case 'g':
                $value *= 1024;
                // no break
            case 'm':
                $value *= 1024;
                // no break
            case 'k':
                $value *= 1024;
        }

        return $value;
    }
}

// Polyfills pour des fonctions utiles
if (!function_exists('array_key_first')) {
    /**
     * Polyfill pour array_key_first (PHP 7.3+)
     */
    function array_key_first(array $array) {
        foreach ($array as $key => $unused) {
            return $key;
        }
        return null;
    }
}

if (!function_exists('array_key_last')) {
    /**
     * Polyfill pour array_key_last (PHP 7.3+)
     */
    function array_key_last(array $array) {
        if (!empty($array)) {
            return key(array_slice($array, -1, 1, true));
        }
        return null;
    }
}

if (!function_exists('is_countable')) {
    /**
     * Polyfill pour is_countable (PHP 7.3+)
     */
    function is_countable($value) {
        return is_array($value) || $value instanceof Countable;
    }
}

if (!function_exists('hrtime')) {
    /**
     * Polyfill pour hrtime (PHP 7.3+)
     */
    function hrtime($as_number = false) {
        $time = microtime(true);

        if ($as_number) {
            return (int) ($time * 1e9);
        }

        $seconds = (int) $time;
        $nanoseconds = (int) (($time - $seconds) * 1e9);

        return [$seconds, $nanoseconds];
    }
}

/**
 * Fonctions utilitaires pour la compatibilité
 */
class CompatibilityHelper {
    
    /**
     * Version sécurisée de json_encode avec gestion d'erreurs
     */
    public static function safe_json_encode($value, $flags = 0, $depth = 512) {
        $json = json_encode($value, $flags, $depth);

        if ($json === false) {
            $error = json_last_error();
            $message = json_last_error_msg();
            throw new InvalidArgumentException("JSON encoding failed: $message (error code: $error)");
        }

        return $json;
    }
    
    /**
     * Version sécurisée de json_decode avec gestion d'erreurs
     */
    public static function safe_json_decode($json, $associative = false, $depth = 512, $flags = 0) {
        $data = json_decode($json, $associative, $depth, $flags);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $error = json_last_error();
            $message = json_last_error_msg();
            throw new InvalidArgumentException("JSON decoding failed: $message (error code: $error)");
        }

        return $data;
    }
    
    /**
     * Fonction pour nettoyer les chaînes de caractères
     */
    public static function clean_string($string) {
        // Supprimer les caractères de contrôle
        $string = preg_replace('/[\x00-\x1F\x7F]/', '', $string);

        // Normaliser les espaces
        $string = preg_replace('/\s+/', ' ', $string);

        // Trim
        return trim($string);
    }

    /**
     * Fonction pour valider et nettoyer les emails
     */
    public static function validate_email($email) {
        $email = self::clean_string($email);
        $email = filter_var($email, FILTER_VALIDATE_EMAIL);

        return $email !== false ? $email : null;
    }
    
    /**
     * Fonction pour générer des mots de passe sécurisés
     */
    public static function generate_password($length = 12) {
        if (function_exists('random_bytes')) {
            $bytes = random_bytes($length);
            return substr(base64_encode($bytes), 0, $length);
        } else {
            // Fallback pour les anciennes versions
            $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*';
            $password = '';
            for ($i = 0; $i < $length; $i++) {
                $password .= $characters[mt_rand(0, strlen($characters) - 1)];
            }
            return $password;
        }
    }
    
    /**
     * Fonction pour hasher les mots de passe de manière sécurisée
     */
    public static function hash_password($password) {
        if (function_exists('password_hash')) {
            return password_hash($password, PASSWORD_DEFAULT);
        } else {
            // Fallback très basique (non recommandé en production)
            return hash('sha256', $password . 'salt_udm_2025');
        }
    }

    /**
     * Fonction pour vérifier les mots de passe
     */
    public static function verify_password($password, $hash) {
        if (function_exists('password_verify')) {
            return password_verify($password, $hash);
        } else {
            // Fallback pour l'ancien système
            return hash_equals($hash, hash('sha256', $password . 'salt_udm_2025'));
        }
    }

    /**
     * Fonction pour échapper les données HTML
     */
    public static function escape_html($string) {
        return htmlspecialchars($string, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

    /**
     * Fonction pour échapper les données pour JavaScript
     */
    public static function escape_js($string) {
        return json_encode($string, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);
    }

    /**
     * Fonction pour vérifier si une extension PHP est chargée
     */
    public static function check_extension($extension) {
        return extension_loaded($extension);
    }
    
    /**
     * Fonction pour obtenir des informations sur l'environnement
     */
    public static function get_environment_info() {
        return [
            'php_version' => PHP_VERSION,
            'php_sapi' => php_sapi_name(),
            'os' => PHP_OS,
            'extensions' => get_loaded_extensions(),
            'memory_limit' => ini_get('memory_limit'),
            'max_execution_time' => ini_get('max_execution_time'),
            'upload_max_filesize' => ini_get('upload_max_filesize'),
            'post_max_size' => ini_get('post_max_size'),
            'timezone' => date_default_timezone_get(),
            'charset' => ini_get('default_charset')
        ];
    }

    /**
     * Fonction pour créer un UUID simple
     */
    public static function generate_uuid() {
        if (function_exists('random_bytes')) {
            $data = random_bytes(16);
            $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
            $data[8] = chr(ord($data[8]) & 0x3f | 0x80);
            return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
        } else {
            // Fallback basique
            return sprintf(
                '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
                mt_rand(0, 0xffff), mt_rand(0, 0xffff),
                mt_rand(0, 0xffff),
                mt_rand(0, 0x0fff) | 0x4000,
                mt_rand(0, 0x3fff) | 0x8000,
                mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
            );
        }
    }
    
    /**
     * Fonction pour formater les tailles de fichiers
     */
    public static function format_bytes($size, $precision = 2) {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $size > 1024 && $i < count($units) - 1; $i++) {
            $size /= 1024;
        }

        return round($size, $precision) . ' ' . $units[$i];
    }

    /**
     * Fonction pour vérifier la compatibilité de l'environnement
     */
    public static function check_compatibility() {
        $issues = [];
        $warnings = [];
        
        // Vérifier PHP
        if (version_compare(PHP_VERSION, '7.4.0', '<')) {
            $issues[] = 'PHP 7.4+ requis (version actuelle : ' . PHP_VERSION . ')';
        }
        
        // Vérifier les extensions critiques
        $required_extensions = ['pdo', 'pdo_mysql', 'mbstring', 'json'];
        foreach ($required_extensions as $ext) {
            if (!self::check_extension($ext)) {
                $issues[] = "Extension PHP manquante : $ext";
            }
        }
        
        // Vérifier les extensions recommandées
        $recommended_extensions = ['curl', 'gd', 'zip', 'xml'];
        foreach ($recommended_extensions as $ext) {
            if (!self::check_extension($ext)) {
                $warnings[] = "Extension PHP recommandée manquante : $ext";
            }
        }
        
        // Vérifier les paramètres PHP
        if (ini_get('memory_limit') !== '-1' && ini_parse_quantity(ini_get('memory_limit')) < 128 * 1024 * 1024) {
            $warnings[] = 'memory_limit recommandé : 128M ou plus';
        }
        
        return [
            'issues' => $issues,
            'warnings' => $warnings,
            'compatible' => empty($issues)
        ];
    }
}

// Initialisation automatique
if (!defined('POLYFILLS_LOADED')) {
    define('POLYFILLS_LOADED', true);
    
    // Vérifier la compatibilité en mode développement
    if (defined('DEVELOPMENT_MODE') && DEVELOPMENT_MODE === true) {
        $compatibility = CompatibilityHelper::check_compatibility();
        if (!empty($compatibility['issues'])) {
            error_log('Problèmes de compatibilité critiques : ' . implode(', ', $compatibility['issues']));
        }
        if (!empty($compatibility['warnings'])) {
            error_log('Avertissements de compatibilité : ' . implode(', ', $compatibility['warnings']));
        }
    }
}
