<?php
/**
 * Test de syntaxe pour vérifier que tous les fichiers sont compatibles PHP 5.4
 */

echo "=== Test de Syntaxe PHP 5.4 ===\n";
echo "PHP Version: " . PHP_VERSION . "\n\n";

// Liste des fichiers critiques à tester
$files_to_test = array(
    'login.php',
    'register.php',
    'dashboard.php',
    'index.php',
    'includes/db.php',
    'includes/legacy_compatibility.php',
    'api/add_user.php',
    'api/send_message.php'
);

$errors = 0;
$success = 0;

foreach ($files_to_test as $file) {
    if (!file_exists($file)) {
        echo "⚠ $file - Fichier non trouvé\n";
        continue;
    }
    
    // Test de syntaxe basique
    $output = array();
    $return_code = 0;
    exec("php -l \"$file\" 2>&1", $output, $return_code);
    
    if ($return_code === 0) {
        echo "✓ $file - Syntaxe OK\n";
        $success++;
    } else {
        echo "✗ $file - Erreur de syntaxe:\n";
        foreach ($output as $line) {
            echo "  $line\n";
        }
        $errors++;
    }
}

echo "\n=== Résumé ===\n";
echo "Fichiers testés: " . ($success + $errors) . "\n";
echo "Succès: $success\n";
echo "Erreurs: $errors\n";

if ($errors === 0) {
    echo "\n🎉 Tous les fichiers sont compatibles PHP 5.4 !\n";
} else {
    echo "\n⚠ Certains fichiers ont encore des erreurs de syntaxe.\n";
}

// Test d'inclusion des fichiers critiques
echo "\n=== Test d'inclusion ===\n";

try {
    require_once 'includes/db.php';
    echo "✓ includes/db.php inclus avec succès\n";
} catch (Exception $e) {
    echo "✗ Erreur includes/db.php: " . $e->getMessage() . "\n";
}

echo "\n=== Test terminé ===\n";
?>
