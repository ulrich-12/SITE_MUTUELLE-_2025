<?php
echo "=== Test de Compatibilité Mutuelle UDM ===\n";
echo "PHP Version: " . PHP_VERSION . "\n";

// Test du système de détection automatique
try {
    require_once 'includes/db.php';
    echo "✓ Système de compatibilité chargé avec succès\n";

    if (defined('LEGACY_PHP_MODE')) {
        echo "✓ Mode Legacy PHP activé (PHP < 7.4)\n";

        // Test des fonctions legacy
        if (class_exists('LegacyCompatibilityHelper')) {
            echo "✓ LegacyCompatibilityHelper disponible\n";

            $compatibility = LegacyCompatibilityHelper::check_compatibility();
            echo "✓ Vérification de compatibilité effectuée\n";
            echo "  - Compatible: " . ($compatibility['compatible'] ? 'Oui' : 'Non') . "\n";

            if (!empty($compatibility['issues'])) {
                echo "  - Problèmes: " . implode(', ', $compatibility['issues']) . "\n";
            }

            if (!empty($compatibility['warnings'])) {
                echo "  - Avertissements: " . implode(', ', $compatibility['warnings']) . "\n";
            }

            // Test des fonctions
            $uuid = LegacyCompatibilityHelper::generate_uuid();
            echo "✓ UUID généré: " . $uuid . "\n";

            $size = LegacyCompatibilityHelper::format_bytes(1048576);
            echo "✓ Format bytes: " . $size . "\n";

        } else {
            echo "✗ LegacyCompatibilityHelper non disponible\n";
        }

    } else {
        echo "✓ Mode PHP moderne activé (PHP 7.4+)\n";

        if (class_exists('CompatibilityHelper')) {
            echo "✓ CompatibilityHelper disponible\n";
        } else {
            echo "✗ CompatibilityHelper non disponible\n";
        }
    }

    // Test des polyfills
    echo "\nTest des polyfills:\n";

    if (function_exists('password_hash')) {
        $hash = password_hash('test123', PASSWORD_DEFAULT);
        echo "✓ password_hash fonctionne\n";

        if (password_verify('test123', $hash)) {
            echo "✓ password_verify fonctionne\n";
        } else {
            echo "✗ password_verify échoué\n";
        }
    } else {
        echo "✗ password_hash non disponible\n";
    }

    if (function_exists('random_bytes')) {
        $bytes = random_bytes(16);
        echo "✓ random_bytes fonctionne (" . strlen($bytes) . " bytes)\n";
    } else {
        echo "✗ random_bytes non disponible\n";
    }

} catch (Exception $e) {
    echo "✗ Erreur: " . $e->getMessage() . "\n";
}

echo "\n=== Fin du test ===\n";
?>
