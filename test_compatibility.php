<?php
/**
 * Script de test pour vérifier la compatibilité
 */

echo "=== Test de Compatibilité Mutuelle UDM ===\n";
echo "PHP Version: " . PHP_VERSION . "\n";
echo "Date: " . date('Y-m-d H:i:s') . "\n\n";

// Test 1: Chargement des fichiers de compatibilité
echo "1. Test de chargement des fichiers de compatibilité...\n";
try {
    require_once 'includes/polyfills.php';
    echo "   ✓ polyfills.php chargé avec succès\n";
} catch (Exception $e) {
    echo "   ✗ Erreur polyfills.php: " . $e->getMessage() . "\n";
}

try {
    require_once 'includes/compatibility.php';
    echo "   ✓ compatibility.php chargé avec succès\n";
} catch (Exception $e) {
    echo "   ✗ Erreur compatibility.php: " . $e->getMessage() . "\n";
}

// Test 2: Test des polyfills
echo "\n2. Test des polyfills...\n";

// Test str_contains
if (function_exists('str_contains')) {
    $test = str_contains('Hello World', 'World');
    echo "   ✓ str_contains fonctionne: " . ($test ? 'true' : 'false') . "\n";
} else {
    echo "   ✗ str_contains non disponible\n";
}

// Test str_starts_with
if (function_exists('str_starts_with')) {
    $test = str_starts_with('Hello World', 'Hello');
    echo "   ✓ str_starts_with fonctionne: " . ($test ? 'true' : 'false') . "\n";
} else {
    echo "   ✗ str_starts_with non disponible\n";
}

// Test str_ends_with
if (function_exists('str_ends_with')) {
    $test = str_ends_with('Hello World', 'World');
    echo "   ✓ str_ends_with fonctionne: " . ($test ? 'true' : 'false') . "\n";
} else {
    echo "   ✗ str_ends_with non disponible\n";
}

// Test 3: Test des fonctions de la classe CompatibilityHelper
echo "\n3. Test de CompatibilityHelper...\n";

if (class_exists('CompatibilityHelper')) {
    try {
        $info = CompatibilityHelper::get_environment_info();
        echo "   ✓ get_environment_info fonctionne\n";
        echo "     - PHP: " . $info['php_version'] . "\n";
        echo "     - OS: " . $info['os'] . "\n";
        echo "     - SAPI: " . $info['php_sapi'] . "\n";
    } catch (Exception $e) {
        echo "   ✗ Erreur get_environment_info: " . $e->getMessage() . "\n";
    }
    
    try {
        $compatibility = CompatibilityHelper::check_compatibility();
        echo "   ✓ check_compatibility fonctionne\n";
        echo "     - Compatible: " . ($compatibility['compatible'] ? 'Oui' : 'Non') . "\n";
        if (!empty($compatibility['issues'])) {
            echo "     - Problèmes: " . implode(', ', $compatibility['issues']) . "\n";
        }
        if (!empty($compatibility['warnings'])) {
            echo "     - Avertissements: " . implode(', ', $compatibility['warnings']) . "\n";
        }
    } catch (Exception $e) {
        echo "   ✗ Erreur check_compatibility: " . $e->getMessage() . "\n";
    }
    
    try {
        $uuid = CompatibilityHelper::generate_uuid();
        echo "   ✓ generate_uuid fonctionne: " . $uuid . "\n";
    } catch (Exception $e) {
        echo "   ✗ Erreur generate_uuid: " . $e->getMessage() . "\n";
    }
    
    try {
        $size = CompatibilityHelper::format_bytes(1024 * 1024);
        echo "   ✓ format_bytes fonctionne: " . $size . "\n";
    } catch (Exception $e) {
        echo "   ✗ Erreur format_bytes: " . $e->getMessage() . "\n";
    }
} else {
    echo "   ✗ Classe CompatibilityHelper non trouvée\n";
}

// Test 4: Test de la base de données (si disponible)
echo "\n4. Test de la base de données...\n";
try {
    if (file_exists('includes/db.php')) {
        require_once 'includes/db.php';
        echo "   ✓ db.php chargé avec succès\n";
        
        if (isset($pdo) && $pdo instanceof PDO) {
            echo "   ✓ Connexion PDO établie\n";
            
            // Test simple
            $stmt = $pdo->query("SELECT 1 as test");
            $result = $stmt->fetch();
            if ($result && $result['test'] == 1) {
                echo "   ✓ Test de requête réussi\n";
            } else {
                echo "   ✗ Test de requête échoué\n";
            }
        } else {
            echo "   ✗ Connexion PDO non établie\n";
        }
    } else {
        echo "   - db.php non trouvé (normal en test)\n";
    }
} catch (Exception $e) {
    echo "   ✗ Erreur base de données: " . $e->getMessage() . "\n";
}

// Test 5: Extensions PHP
echo "\n5. Test des extensions PHP...\n";
$required_extensions = ['pdo', 'pdo_mysql', 'mbstring', 'json'];
foreach ($required_extensions as $ext) {
    if (extension_loaded($ext)) {
        echo "   ✓ Extension $ext chargée\n";
    } else {
        echo "   ✗ Extension $ext manquante\n";
    }
}

$recommended_extensions = ['curl', 'gd', 'zip', 'xml'];
foreach ($recommended_extensions as $ext) {
    if (extension_loaded($ext)) {
        echo "   ✓ Extension recommandée $ext chargée\n";
    } else {
        echo "   - Extension recommandée $ext manquante\n";
    }
}

echo "\n=== Fin du test ===\n";
echo "Si tous les tests sont ✓, votre environnement est compatible !\n";
?>
