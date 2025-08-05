<?php
/**
 * Script pour télécharger les dépendances localement
 * À exécuter en ligne de commande ou via navigateur (en mode développement)
 */

require_once __DIR__ . '/../includes/cdn_config.php';

// Vérifier si on est en mode CLI ou web
$is_cli = php_sapi_name() === 'cli';

if (!$is_cli) {
    // En mode web, vérifier les permissions
    session_start();
    if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'super_admin') {
        die('Accès refusé. Seuls les super administrateurs peuvent exécuter ce script.');
    }
    
    // Headers pour l'affichage web
    header('Content-Type: text/html; charset=utf-8');
    echo '<!DOCTYPE html><html><head><meta charset="utf-8"><title>Téléchargement des dépendances</title></head><body>';
    echo '<h1>Téléchargement des dépendances</h1>';
    echo '<pre>';
}

/**
 * Fonction pour afficher les messages
 */
function output($message, $type = 'info') {
    global $is_cli;
    
    $timestamp = date('Y-m-d H:i:s');
    $prefix = "[$timestamp] ";
    
    switch ($type) {
        case 'success':
            $prefix .= $is_cli ? "\033[32m[SUCCESS]\033[0m " : "[SUCCESS] ";
            break;
        case 'error':
            $prefix .= $is_cli ? "\033[31m[ERROR]\033[0m " : "[ERROR] ";
            break;
        case 'warning':
            $prefix .= $is_cli ? "\033[33m[WARNING]\033[0m " : "[WARNING] ";
            break;
        default:
            $prefix .= $is_cli ? "\033[36m[INFO]\033[0m " : "[INFO] ";
    }
    
    echo $prefix . $message . "\n";
    
    if (!$is_cli) {
        flush();
        ob_flush();
    }
}

/**
 * Créer les dossiers nécessaires
 */
function createDirectories() {
    $directories = [
        'assets/css',
        'assets/js',
        'assets/fonts',
        'assets/img'
    ];
    
    foreach ($directories as $dir) {
        if (!is_dir($dir)) {
            if (mkdir($dir, 0755, true)) {
                output("Dossier créé : $dir", 'success');
            } else {
                output("Erreur lors de la création du dossier : $dir", 'error');
                return false;
            }
        }
    }
    
    return true;
}

/**
 * Télécharger un fichier avec retry
 */
function downloadFile($url, $destination, $max_retries = 3) {
    for ($i = 0; $i < $max_retries; $i++) {
        $context = stream_context_create([
            'http' => [
                'timeout' => 30,
                'user_agent' => 'Mozilla/5.0 (compatible; Mutuelle UDM Downloader)'
            ]
        ]);
        
        $content = @file_get_contents($url, false, $context);
        
        if ($content !== false) {
            if (file_put_contents($destination, $content)) {
                return true;
            }
        }
        
        if ($i < $max_retries - 1) {
            output("Tentative " . ($i + 1) . " échouée pour $url, retry...", 'warning');
            sleep(1);
        }
    }
    
    return false;
}

/**
 * Télécharger Font Awesome
 */
function downloadFontAwesome() {
    output("Téléchargement de Font Awesome...");
    
    $css_url = 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css';
    $css_destination = 'assets/css/fontawesome.min.css';
    
    if (downloadFile($css_url, $css_destination)) {
        output("Font Awesome CSS téléchargé", 'success');
        
        // Télécharger les polices
        $fonts_base_url = 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/webfonts/';
        $fonts = [
            'fa-solid-900.woff2',
            'fa-regular-400.woff2',
            'fa-brands-400.woff2'
        ];
        
        foreach ($fonts as $font) {
            $font_url = $fonts_base_url . $font;
            $font_destination = 'assets/fonts/' . $font;
            
            if (downloadFile($font_url, $font_destination)) {
                output("Police téléchargée : $font", 'success');
            } else {
                output("Erreur téléchargement police : $font", 'error');
            }
        }
        
        // Modifier le CSS pour pointer vers les polices locales
        $css_content = file_get_contents($css_destination);
        $css_content = str_replace(
            'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/webfonts/',
            '../fonts/',
            $css_content
        );
        file_put_contents($css_destination, $css_content);
        
        return true;
    } else {
        output("Erreur téléchargement Font Awesome CSS", 'error');
        return false;
    }
}

/**
 * Créer des images placeholder
 */
function createPlaceholderImages() {
    output("Création des images placeholder...");
    
    $placeholders = [
        ['width' => 800, 'height' => 600, 'name' => 'placeholder-large.jpg'],
        ['width' => 400, 'height' => 300, 'name' => 'placeholder-medium.jpg'],
        ['width' => 150, 'height' => 150, 'name' => 'placeholder-small.jpg']
    ];
    
    foreach ($placeholders as $placeholder) {
        $image_url = "https://via.placeholder.com/{$placeholder['width']}x{$placeholder['height']}/2e7d32/ffffff?text=UDM";
        $destination = "assets/img/{$placeholder['name']}";
        
        if (downloadFile($image_url, $destination)) {
            output("Image placeholder créée : {$placeholder['name']}", 'success');
        } else {
            output("Erreur création placeholder : {$placeholder['name']}", 'error');
        }
    }
}

/**
 * Créer un fichier de fallback CSS minimal
 */
function createCSSFallbacks() {
    output("Création des fallbacks CSS...");
    
    $minimal_css = "
/* Fallback CSS minimal pour la compatibilité */
.container { max-width: 1200px; margin: 0 auto; padding: 0 20px; }
.btn { display: inline-block; padding: 0.5rem 1rem; text-decoration: none; border-radius: 4px; }
.btn-primary { background-color: #2e7d32; color: white; }
.text-center { text-align: center; }
.d-none { display: none; }
.d-block { display: block; }
.mt-3 { margin-top: 1rem; }
.mb-3 { margin-bottom: 1rem; }
.p-3 { padding: 1rem; }
.alert { padding: 1rem; margin: 1rem 0; border-radius: 4px; }
.alert-success { background-color: #d4edda; color: #155724; }
.alert-danger { background-color: #f8d7da; color: #721c24; }
.alert-warning { background-color: #fff3cd; color: #856404; }
";
    
    if (file_put_contents('assets/css/fallback.min.css', $minimal_css)) {
        output("Fallback CSS créé", 'success');
        return true;
    } else {
        output("Erreur création fallback CSS", 'error');
        return false;
    }
}

/**
 * Créer un fichier JavaScript de fallback
 */
function createJSFallbacks() {
    output("Création des fallbacks JavaScript...");
    
    $minimal_js = "
// Fallback JavaScript minimal
if (!window.console) {
    window.console = {
        log: function() {},
        error: function() {},
        warn: function() {}
    };
}

// Polyfill addEventListener pour IE8
if (!Element.prototype.addEventListener) {
    Element.prototype.addEventListener = function(event, handler) {
        this.attachEvent('on' + event, handler);
    };
}

// Utilitaires de base
window.UDMUtils = {
    addClass: function(element, className) {
        if (element.classList) {
            element.classList.add(className);
        } else {
            element.className += ' ' + className;
        }
    },
    removeClass: function(element, className) {
        if (element.classList) {
            element.classList.remove(className);
        } else {
            element.className = element.className.replace(new RegExp('\\\\b' + className + '\\\\b', 'g'), '');
        }
    }
};
";
    
    if (file_put_contents('assets/js/fallback.min.js', $minimal_js)) {
        output("Fallback JavaScript créé", 'success');
        return true;
    } else {
        output("Erreur création fallback JavaScript", 'error');
        return false;
    }
}

/**
 * Générer un rapport de téléchargement
 */
function generateReport() {
    $report = [
        'timestamp' => date('Y-m-d H:i:s'),
        'files_downloaded' => [],
        'files_created' => [],
        'errors' => [],
        'total_size' => 0
    ];
    
    // Analyser les fichiers téléchargés
    $directories = ['assets/css', 'assets/js', 'assets/fonts', 'assets/img'];
    
    foreach ($directories as $dir) {
        if (is_dir($dir)) {
            $files = glob($dir . '/*');
            foreach ($files as $file) {
                if (is_file($file)) {
                    $size = filesize($file);
                    $report['files_downloaded'][] = [
                        'file' => $file,
                        'size' => $size,
                        'size_human' => formatBytes($size)
                    ];
                    $report['total_size'] += $size;
                }
            }
        }
    }
    
    $report['total_size_human'] = formatBytes($report['total_size']);
    
    // Sauvegarder le rapport
    file_put_contents('assets/download_report.json', json_encode($report, JSON_PRETTY_PRINT));
    
    return $report;
}

/**
 * Formater les tailles de fichiers
 */
function formatBytes($size, $precision = 2) {
    $units = ['B', 'KB', 'MB', 'GB'];
    $base = log($size, 1024);
    return round(pow(1024, $base - floor($base)), $precision) . ' ' . $units[floor($base)];
}

// Exécution principale
output("=== Début du téléchargement des dépendances ===");

if (!createDirectories()) {
    output("Erreur lors de la création des dossiers", 'error');
    exit(1);
}

$success = true;

// Télécharger Font Awesome
if (!downloadFontAwesome()) {
    $success = false;
}

// Créer les images placeholder
createPlaceholderImages();

// Créer les fallbacks
if (!createCSSFallbacks()) {
    $success = false;
}

if (!createJSFallbacks()) {
    $success = false;
}

// Générer le rapport
$report = generateReport();

output("=== Téléchargement terminé ===");
output("Fichiers téléchargés : " . count($report['files_downloaded']));
output("Taille totale : " . $report['total_size_human']);

if ($success) {
    output("Toutes les dépendances ont été téléchargées avec succès !", 'success');
} else {
    output("Certaines dépendances n'ont pas pu être téléchargées", 'warning');
}

if (!$is_cli) {
    echo '</pre>';
    echo '<h2>Rapport détaillé</h2>';
    echo '<pre>' . json_encode($report, JSON_PRETTY_PRINT) . '</pre>';
    echo '</body></html>';
}
?>
