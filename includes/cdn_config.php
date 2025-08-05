<?php
/**
 * Configuration des CDN et dépendances externes
 * Avec système de fallback pour assurer la disponibilité
 */

class CDNManager {
    
    private static $cdn_config = [
        'font_awesome' => [
            'primary' => 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css',
            'fallback' => 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css',
            'local' => 'assets/css/fontawesome-fallback.css',
            'integrity' => 'sha512-iecdLmaskl7CVkqkXNQ/ZH/XLlvWZOJyj7Yy7tcenmpD1ypASozpmT/E0iPtmFIB46ZmdtAc9eNBvH0H/ZpiBw==',
            'version' => '6.4.0'
        ],
        'bootstrap' => [
            'primary' => 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css',
            'fallback' => 'https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css',
            'local' => 'assets/css/bootstrap-fallback.css',
            'integrity' => 'sha384-9ndCyUa6J+K3h8c6VTODeQz7B7jIXm8+q4+UcM6JHjOxnQUtXvQP3+8oD5kTU5d0',
            'version' => '5.3.0'
        ],
        'jquery' => [
            'primary' => 'https://code.jquery.com/jquery-3.7.0.min.js',
            'fallback' => 'https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.0/jquery.min.js',
            'local' => 'assets/js/jquery-fallback.js',
            'integrity' => 'sha256-2Pmvv0kuTBOenSvLm6bvfBSSHrUJ+3A7x6P5Ebd07/g=',
            'version' => '3.7.0'
        ]
    ];
    
    /**
     * Générer le HTML pour charger une dépendance avec fallback
     */
    public static function loadCSS($library, $use_integrity = true) {
        if (!isset(self::$cdn_config[$library])) {
            return "<!-- Bibliothèque $library non configurée -->";
        }
        
        $config = self::$cdn_config[$library];
        $integrity_attr = $use_integrity && isset($config['integrity']) 
            ? 'integrity="' . $config['integrity'] . '" crossorigin="anonymous"' 
            : '';
        
        $html = "<!-- $library CSS -->\n";
        $html .= '<link rel="stylesheet" href="' . $config['primary'] . '" ' . $integrity_attr . ' ';
        $html .= 'onerror="this.onerror=null;this.href=\'' . $config['fallback'] . '\';">' . "\n";
        
        // Fallback local si disponible
        if (isset($config['local']) && file_exists($config['local'])) {
            $html .= '<noscript><link rel="stylesheet" href="' . $config['local'] . '"></noscript>' . "\n";
        }
        
        return $html;
    }
    
    /**
     * Générer le HTML pour charger un script avec fallback
     */
    public static function loadJS($library, $use_integrity = true, $test_variable = null) {
        if (!isset(self::$cdn_config[$library])) {
            return "<!-- Bibliothèque $library non configurée -->";
        }
        
        $config = self::$cdn_config[$library];
        $integrity_attr = $use_integrity && isset($config['integrity']) 
            ? 'integrity="' . $config['integrity'] . '" crossorigin="anonymous"' 
            : '';
        
        $html = "<!-- $library JS -->\n";
        $html .= '<script src="' . $config['primary'] . '" ' . $integrity_attr . '></script>' . "\n";
        
        // Test de chargement et fallback
        if ($test_variable) {
            $html .= '<script>' . "\n";
            $html .= 'if (typeof ' . $test_variable . ' === "undefined") {' . "\n";
            $html .= '    document.write(\'<script src="' . $config['fallback'] . '"><\/script>\');' . "\n";
            
            // Fallback local si disponible
            if (isset($config['local']) && file_exists($config['local'])) {
                $html .= '    if (typeof ' . $test_variable . ' === "undefined") {' . "\n";
                $html .= '        document.write(\'<script src="' . $config['local'] . '"><\/script>\');' . "\n";
                $html .= '    }' . "\n";
            }
            
            $html .= '}' . "\n";
            $html .= '</script>' . "\n";
        }
        
        return $html;
    }
    
    /**
     * Vérifier la disponibilité d'un CDN
     */
    public static function checkCDNAvailability($url, $timeout = 5) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_NOBODY, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        
        $result = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        return $http_code >= 200 && $http_code < 400;
    }
    
    /**
     * Obtenir le statut de toutes les dépendances
     */
    public static function getDependenciesStatus() {
        $status = [];
        
        foreach (self::$cdn_config as $library => $config) {
            $status[$library] = [
                'primary_available' => self::checkCDNAvailability($config['primary']),
                'fallback_available' => self::checkCDNAvailability($config['fallback']),
                'local_available' => isset($config['local']) && file_exists($config['local']),
                'version' => isset($config['version']) ? $config['version'] : 'Unknown'
            ];
        }
        
        return $status;
    }
    
    /**
     * Générer un rapport de compatibilité
     */
    public static function generateCompatibilityReport() {
        $report = [
            'timestamp' => date('Y-m-d H:i:s'),
            'php_version' => PHP_VERSION,
            'dependencies' => self::getDependenciesStatus(),
            'recommendations' => []
        ];
        
        // Vérifications et recommandations
        foreach ($report['dependencies'] as $library => $status) {
            if (!$status['primary_available'] && !$status['fallback_available']) {
                $report['recommendations'][] = "CRITIQUE: $library n'est pas accessible via CDN. Utilisez la version locale.";
            } elseif (!$status['primary_available']) {
                $report['recommendations'][] = "ATTENTION: CDN principal de $library indisponible. Fallback utilisé.";
            }
            
            if (!$status['local_available']) {
                $report['recommendations'][] = "RECOMMANDATION: Téléchargez une version locale de $library pour plus de fiabilité.";
            }
        }
        
        return $report;
    }
    
    /**
     * Créer les fichiers de fallback locaux
     */
    public static function createLocalFallbacks() {
        $created = [];
        $errors = [];
        
        foreach (self::$cdn_config as $library => $config) {
            if (!isset($config['local'])) continue;
            
            $local_path = $config['local'];
            $dir = dirname($local_path);
            
            // Créer le dossier si nécessaire
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
            
            // Télécharger le fichier depuis le CDN principal
            $content = @file_get_contents($config['primary']);
            if ($content === false) {
                // Essayer le fallback
                $content = @file_get_contents($config['fallback']);
            }
            
            if ($content !== false) {
                if (file_put_contents($local_path, $content)) {
                    $created[] = $local_path;
                } else {
                    $errors[] = "Impossible d'écrire $local_path";
                }
            } else {
                $errors[] = "Impossible de télécharger $library";
            }
        }
        
        return [
            'created' => $created,
            'errors' => $errors
        ];
    }
}

/**
 * Fonctions helper pour l'utilisation dans les templates
 */
function load_font_awesome($use_integrity = true) {
    return CDNManager::loadCSS('font_awesome', $use_integrity);
}

function load_bootstrap_css($use_integrity = true) {
    return CDNManager::loadCSS('bootstrap', $use_integrity);
}

function load_jquery($use_integrity = true) {
    return CDNManager::loadJS('jquery', $use_integrity, 'jQuery');
}

/**
 * Configuration des images externes avec fallback
 */
class ImageManager {
    
    private static $image_sources = [
        'unsplash' => [
            'base_url' => 'https://images.unsplash.com/',
            'fallback_url' => 'https://picsum.photos/',
            'local_fallback' => 'assets/img/placeholder.jpg'
        ]
    ];
    
    /**
     * Générer une URL d'image avec fallback
     */
    public static function getImageUrl($source, $image_id, $width = 800, $height = 600) {
        if (!isset(self::$image_sources[$source])) {
            return 'assets/img/placeholder.jpg';
        }
        
        $config = self::$image_sources[$source];
        
        switch ($source) {
            case 'unsplash':
                return $config['base_url'] . $image_id . '?ixlib=rb-4.0.3&auto=format&fit=crop&w=' . $width . '&q=80';
            default:
                return $config['local_fallback'];
        }
    }
    
    /**
     * Générer le HTML d'une image avec fallback
     */
    public static function generateImageHTML($source, $image_id, $alt, $width = 800, $height = 600, $classes = '') {
        $primary_url = self::getImageUrl($source, $image_id, $width, $height);
        $fallback_url = isset(self::$image_sources[$source]['local_fallback']) 
            ? self::$image_sources[$source]['local_fallback'] 
            : 'assets/img/placeholder.jpg';
        
        return '<img src="' . $primary_url . '" alt="' . htmlspecialchars($alt) . '" class="' . $classes . '" ' .
               'onerror="this.onerror=null;this.src=\'' . $fallback_url . '\';" ' .
               'loading="lazy" width="' . $width . '" height="' . $height . '">';
    }
}
