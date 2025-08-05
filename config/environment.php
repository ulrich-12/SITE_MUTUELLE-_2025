<?php
/**
 * Configuration d'environnement pour la Mutuelle UDM
 * Gestion des environnements : development, staging, production
 */

// Détecter l'environnement automatiquement
function detectEnvironment() {
    // Vérifier les variables d'environnement
    if (isset($_ENV['APP_ENV'])) {
        return $_ENV['APP_ENV'];
    }
    
    if (isset($_SERVER['APP_ENV'])) {
        return $_SERVER['APP_ENV'];
    }
    
    // Détecter par nom de domaine
    $host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'localhost';
    
    if (strpos($host, 'localhost') !== false || strpos($host, '127.0.0.1') !== false) {
        return 'development';
    }
    
    if (strpos($host, 'staging') !== false || strpos($host, 'test') !== false) {
        return 'staging';
    }
    
    return 'production';
}

// Définir l'environnement
$environment = detectEnvironment();
define('APP_ENV', $environment);

/**
 * Configuration par environnement
 */
$config = [];

switch ($environment) {
    case 'development':
        $config = [
            'debug' => true,
            'display_errors' => true,
            'log_errors' => true,
            'error_reporting' => E_ALL,
            'database' => [
                'host' => 'localhost',
                'name' => 'mutuelle_udm_dev',
                'user' => 'root',
                'pass' => '',
                'charset' => 'utf8mb4',
                'options' => [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]
            ],
            'cache' => [
                'enabled' => false,
                'driver' => 'file',
                'ttl' => 3600
            ],
            'session' => [
                'lifetime' => 3600,
                'secure' => false,
                'httponly' => true,
                'samesite' => 'Lax'
            ],
            'mail' => [
                'driver' => 'log', // Ne pas envoyer d'emails en dev
                'from' => 'dev@mutuelle-udm.local'
            ],
            'cdn' => [
                'use_local' => true,
                'use_integrity' => false
            ],
            'features' => [
                'registration' => true,
                'file_upload' => true,
                'messaging' => true,
                'admin_panel' => true
            ]
        ];
        break;
        
    case 'staging':
        $config = [
            'debug' => true,
            'display_errors' => false,
            'log_errors' => true,
            'error_reporting' => E_ALL & ~E_NOTICE,
            'database' => [
                'host' => 'localhost',
                'name' => 'mutuelle_udm_staging',
                'user' => 'staging_user',
                'pass' => 'staging_password_2025',
                'charset' => 'utf8mb4',
                'options' => [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]
            ],
            'cache' => [
                'enabled' => true,
                'driver' => 'file',
                'ttl' => 1800
            ],
            'session' => [
                'lifetime' => 7200,
                'secure' => true,
                'httponly' => true,
                'samesite' => 'Strict'
            ],
            'mail' => [
                'driver' => 'smtp',
                'host' => 'smtp.gmail.com',
                'port' => 587,
                'username' => 'staging@mutuelle-udm.ma',
                'password' => 'staging_mail_password',
                'from' => 'staging@mutuelle-udm.ma'
            ],
            'cdn' => [
                'use_local' => false,
                'use_integrity' => true
            ],
            'features' => [
                'registration' => true,
                'file_upload' => true,
                'messaging' => true,
                'admin_panel' => true
            ]
        ];
        break;
        
    case 'production':
    default:
        $config = [
            'debug' => false,
            'display_errors' => false,
            'log_errors' => true,
            'error_reporting' => E_ERROR | E_WARNING | E_PARSE,
            'database' => [
                'host' => 'localhost',
                'name' => 'mutuelle_udm',
                'user' => 'prod_user',
                'pass' => 'secure_production_password_2025',
                'charset' => 'utf8mb4',
                'options' => [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]
            ],
            'cache' => [
                'enabled' => true,
                'driver' => 'file',
                'ttl' => 3600
            ],
            'session' => [
                'lifetime' => 3600,
                'secure' => true,
                'httponly' => true,
                'samesite' => 'Strict'
            ],
            'mail' => [
                'driver' => 'smtp',
                'host' => 'smtp.gmail.com',
                'port' => 587,
                'username' => 'contact@mutuelle-udm.ma',
                'password' => 'production_mail_password',
                'from' => 'contact@mutuelle-udm.ma'
            ],
            'cdn' => [
                'use_local' => false,
                'use_integrity' => true
            ],
            'features' => [
                'registration' => true,
                'file_upload' => true,
                'messaging' => true,
                'admin_panel' => true
            ]
        ];
        break;
}

// Définir les constantes globales
define('DEBUG_MODE', $config['debug']);
define('DEVELOPMENT_MODE', $environment === 'development');

// Appliquer la configuration PHP
ini_set('display_errors', $config['display_errors'] ? '1' : '0');
ini_set('log_errors', $config['log_errors'] ? '1' : '0');
error_reporting($config['error_reporting']);

// Configuration des sessions
if (isset($config['session'])) {
    ini_set('session.gc_maxlifetime', $config['session']['lifetime']);
    ini_set('session.cookie_lifetime', 0);
    ini_set('session.cookie_httponly', $config['session']['httponly'] ? '1' : '0');
    ini_set('session.cookie_secure', $config['session']['secure'] ? '1' : '0');
    ini_set('session.cookie_samesite', $config['session']['samesite']);
}

/**
 * Classe de configuration globale
 */
class Config {
    private static $config = [];
    
    public static function init($config) {
        self::$config = $config;
    }
    
    public static function get($key, $default = null) {
        $keys = explode('.', $key);
        $value = self::$config;
        
        foreach ($keys as $k) {
            if (!isset($value[$k])) {
                return $default;
            }
            $value = $value[$k];
        }
        
        return $value;
    }
    
    public static function set($key, $value) {
        $keys = explode('.', $key);
        $config = &self::$config;
        
        foreach ($keys as $k) {
            if (!isset($config[$k])) {
                $config[$k] = [];
            }
            $config = &$config[$k];
        }
        
        $config = $value;
    }
    
    public static function all() {
        return self::$config;
    }
    
    public static function getEnvironment() {
        return APP_ENV;
    }
    
    public static function isDevelopment() {
        return APP_ENV === 'development';
    }
    
    public static function isProduction() {
        return APP_ENV === 'production';
    }
    
    public static function isStaging() {
        return APP_ENV === 'staging';
    }
    
    public static function isDebug() {
        return self::get('debug', false);
    }
}

// Initialiser la configuration
Config::init($config);

/**
 * Fonctions helper pour la configuration
 */
function config($key, $default = null) {
    return Config::get($key, $default);
}

function env($key, $default = null) {
    $value = getenv($key);
    if ($value === false) {
        $value = isset($_ENV[$key]) ? $_ENV[$key] : (isset($_SERVER[$key]) ? $_SERVER[$key] : $default);
    }
    
    // Convertir les valeurs booléennes
    if (is_string($value)) {
        switch (strtolower($value)) {
            case 'true':
            case '1':
            case 'yes':
            case 'on':
                return true;
            case 'false':
            case '0':
            case 'no':
            case 'off':
                return false;
        }
    }
    
    return $value;
}

function is_development() {
    return Config::isDevelopment();
}

function is_production() {
    return Config::isProduction();
}

function is_debug() {
    return Config::isDebug();
}

// Charger les fichiers de configuration spécifiques à l'environnement
$env_config_file = __DIR__ . "/environments/{$environment}.php";
if (file_exists($env_config_file)) {
    require_once $env_config_file;
}
