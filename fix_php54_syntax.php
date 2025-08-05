<?php
/**
 * Script pour corriger la syntaxe PHP 5.4
 * Remplace les opérateurs null coalescing (??) par isset() ternary
 */

function fixNullCoalescingInFile($filename) {
    if (!file_exists($filename)) {
        echo "Fichier non trouvé: $filename\n";
        return false;
    }
    
    $content = file_get_contents($filename);
    $original_content = $content;
    
    // Pattern pour détecter les opérateurs null coalescing
    // isset($var) ? $var : 'default' devient isset($var) ? $var : 'default'
    $patterns = array(
        // Pattern pour isset($_POST['key']) ? $_POST['key'] : 'value'
        '/(\$_POST\[[^\]]+\])\s*\?\?\s*([^;,\)\n]+)/' => 'isset($1) ? $1 : $2',

        // Pattern pour isset($_GET['key']) ? $_GET['key'] : 'value'
        '/(\$_GET\[[^\]]+\])\s*\?\?\s*([^;,\)\n]+)/' => 'isset($1) ? $1 : $2',

        // Pattern pour isset($_SESSION['key']) ? $_SESSION['key'] : 'value'
        '/(\$_SESSION\[[^\]]+\])\s*\?\?\s*([^;,\)\n]+)/' => 'isset($1) ? $1 : $2',

        // Pattern pour isset($array['key']) ? $array['key'] : 'value'
        '/(\$[a-zA-Z_][a-zA-Z0-9_]*\[[^\]]+\])\s*\?\?\s*([^;,\)\n]+)/' => 'isset($1) ? $1 : $2',

        // Pattern simple: isset($var) ? $var : 'value'
        '/(\$[a-zA-Z_][a-zA-Z0-9_]*)\s*\?\?\s*([^;,\)\n]+)/' => 'isset($1) ? $1 : $2',

        // Pattern avec propriétés: isset($obj->prop) ? $obj->prop : 'value'
        '/(\$[a-zA-Z_][a-zA-Z0-9_]*->[a-zA-Z_][a-zA-Z0-9_]*)\s*\?\?\s*([^;,\)\n]+)/' => 'isset($1) ? $1 : $2'
    );
    
    $changes = 0;
    foreach ($patterns as $pattern => $replacement) {
        $new_content = preg_replace($pattern, $replacement, $content);
        if ($new_content !== $content) {
            $changes += substr_count($content, '??') - substr_count($new_content, '??');
            $content = $new_content;
        }
    }
    
    if ($content !== $original_content) {
        file_put_contents($filename, $content);
        echo "✓ $filename corrigé ($changes changements)\n";
        return true;
    } else {
        echo "- $filename déjà compatible\n";
        return false;
    }
}

echo "=== Correction de la syntaxe PHP 5.4 ===\n";

// Fonction pour trouver tous les fichiers PHP
function findPHPFiles($directory = '.') {
    $files = array();
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory));

    foreach ($iterator as $file) {
        if ($file->isFile() && $file->getExtension() === 'php') {
            $relativePath = str_replace('\\', '/', $file->getPathname());
            $relativePath = ltrim(str_replace(getcwd(), '', $relativePath), '/\\');

            // Exclure certains dossiers/fichiers
            if (strpos($relativePath, 'vendor/') === false &&
                strpos($relativePath, 'node_modules/') === false &&
                strpos($relativePath, '.git/') === false) {
                $files[] = $relativePath;
            }
        }
    }

    return $files;
}

// Trouver tous les fichiers PHP
$files_to_fix = findPHPFiles();

echo "Fichiers PHP trouvés: " . count($files_to_fix) . "\n";

$total_changes = 0;
foreach ($files_to_fix as $file) {
    if (fixNullCoalescingInFile($file)) {
        $total_changes++;
    }
}

echo "\n=== Résumé ===\n";
echo "Fichiers corrigés: $total_changes\n";
echo "Votre code devrait maintenant être compatible avec PHP 5.4+\n";
?>
