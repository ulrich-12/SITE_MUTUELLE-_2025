<?php
/**
 * Script de compatibilité pour MySQL/MariaDB
 * Compatible avec MySQL 5.7+, 8.0+ et MariaDB 10.3+
 */

require_once __DIR__ . '/compatibility.php';

/**
 * Vérifier la compatibilité de la base de données
 */
function checkDatabaseCompatibility($pdo) {
    $issues = [];
    $recommendations = [];
    
    try {
        // Obtenir la version de la base de données
        $stmt = $pdo->query("SELECT VERSION() as version");
        $version_info = $stmt->fetch();
        $version = $version_info['version'];
        
        // Détecter le type de base de données
        $is_mariadb = stripos($version, 'mariadb') !== false;
        $is_mysql = !$is_mariadb;
        
        // Extraire le numéro de version
        preg_match('/(\d+\.\d+\.\d+)/', $version, $matches);
        $version_number = isset($matches[1]) ? $matches[1] : '0.0.0';
        
        // Vérifications spécifiques MySQL
        if ($is_mysql) {
            if (version_compare($version_number, '5.7.0', '<')) {
                $issues[] = "MySQL version trop ancienne ($version_number). Version 5.7+ recommandée.";
            } elseif (version_compare($version_number, '8.0.0', '>=')) {
                $recommendations[] = "MySQL 8.0+ détecté. Excellent choix pour les performances.";
            }
        }
        
        // Vérifications spécifiques MariaDB
        if ($is_mariadb) {
            if (version_compare($version_number, '10.3.0', '<')) {
                $issues[] = "MariaDB version trop ancienne ($version_number). Version 10.3+ recommandée.";
            } elseif (version_compare($version_number, '10.5.0', '>=')) {
                $recommendations[] = "MariaDB 10.5+ détecté. Excellent choix pour les performances.";
            }
        }
        
        // Vérifier le charset par défaut
        $stmt = $pdo->query("SHOW VARIABLES LIKE 'character_set_server'");
        $charset_info = $stmt->fetch();
        if ($charset_info && $charset_info['Value'] !== 'utf8mb4') {
            $recommendations[] = "Charset serveur: {$charset_info['Value']}. utf8mb4 recommandé pour le support Unicode complet.";
        }
        
        // Vérifier la collation par défaut
        $stmt = $pdo->query("SHOW VARIABLES LIKE 'collation_server'");
        $collation_info = $stmt->fetch();
        if ($collation_info && !str_contains($collation_info['Value'], 'utf8mb4')) {
            $recommendations[] = "Collation serveur: {$collation_info['Value']}. utf8mb4_unicode_ci recommandé.";
        }
        
        // Vérifier le mode SQL
        $stmt = $pdo->query("SELECT @@sql_mode as sql_mode");
        $sql_mode_info = $stmt->fetch();
        $sql_mode = isset($sql_mode_info['sql_mode']) ? $sql_mode_info['sql_mode'] : '';
        
        if ($is_mysql && version_compare($version_number, '8.0.0', '>=')) {
            // MySQL 8.0+ a des modes stricts par défaut
            if (!str_contains($sql_mode, 'STRICT_TRANS_TABLES')) {
                $recommendations[] = "Mode SQL strict recommandé pour MySQL 8.0+";
            }
        }
        
        // Vérifier les paramètres de performance
        $performance_checks = [
            'innodb_buffer_pool_size' => ['min' => '128M', 'recommended' => '1G'],
            'max_connections' => ['min' => 100, 'recommended' => 200],
            'query_cache_size' => ['note' => 'Désactivé par défaut dans MySQL 8.0+']
        ];
        
        foreach ($performance_checks as $param => $config) {
            try {
                $stmt = $pdo->query("SHOW VARIABLES LIKE '$param'");
                $param_info = $stmt->fetch();
                if ($param_info) {
                    if (isset($config['note'])) {
                        $recommendations[] = "$param: {$param_info['Value']} - {$config['note']}";
                    }
                }
            } catch (Exception $e) {
                // Paramètre peut ne pas exister dans certaines versions
            }
        }
        
    } catch (Exception $e) {
        $issues[] = "Erreur lors de la vérification de compatibilité: " . $e->getMessage();
    }
    
    return [
        'version' => isset($version) ? $version : 'Inconnue',
        'is_mysql' => isset($is_mysql) ? $is_mysql : false,
        'is_mariadb' => isset($is_mariadb) ? $is_mariadb : false,
        'version_number' => isset($version_number) ? $version_number : '0.0.0',
        'issues' => $issues,
        'recommendations' => $recommendations
    ];
}

/**
 * Optimiser la configuration de la base de données
 */
function optimizeDatabaseSettings($pdo) {
    $optimizations = [];
    
    try {
        // Définir le charset pour la session
        $pdo->exec("SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci");
        $optimizations[] = "Charset de session défini sur utf8mb4";
        
        // Optimisations pour les performances
        $pdo->exec("SET SESSION sql_mode = 'STRICT_TRANS_TABLES,NO_ZERO_DATE,NO_ZERO_IN_DATE,ERROR_FOR_DIVISION_BY_ZERO'");
        $optimizations[] = "Mode SQL optimisé pour la compatibilité";
        
        // Paramètres de session pour les performances
        $pdo->exec("SET SESSION innodb_lock_wait_timeout = 50");
        $optimizations[] = "Timeout de verrous InnoDB optimisé";
        
        // Désactiver l'autocommit pour les transactions
        $pdo->exec("SET SESSION autocommit = 1");
        $optimizations[] = "Autocommit configuré";
        
    } catch (Exception $e) {
        error_log("Erreur lors de l'optimisation DB: " . $e->getMessage());
    }
    
    return $optimizations;
}

/**
 * Créer les tables avec compatibilité maximale
 */
function createCompatibleTables($pdo) {
    $tables_sql = [
        'users' => "
            CREATE TABLE IF NOT EXISTS users (
                id INT AUTO_INCREMENT PRIMARY KEY,
                nom VARCHAR(100) NOT NULL,
                prenom VARCHAR(100) NOT NULL,
                email VARCHAR(255) NOT NULL,
                numero_etudiant VARCHAR(20) DEFAULT NULL,
                filiere VARCHAR(100) NOT NULL,
                niveau VARCHAR(10) NOT NULL,
                password_hash VARCHAR(255) NOT NULL,
                role ENUM('etudiant', 'moderateur', 'admin', 'super_admin') DEFAULT 'etudiant',
                active TINYINT(1) DEFAULT 1,
                email_verified TINYINT(1) DEFAULT 0,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                last_login TIMESTAMP NULL,
                UNIQUE KEY unique_email (email),
                UNIQUE KEY unique_numero_etudiant (numero_etudiant),
                INDEX idx_filiere (filiere),
                INDEX idx_niveau (niveau),
                INDEX idx_role (role),
                INDEX idx_active (active)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ",
        
        'documents' => "
            CREATE TABLE IF NOT EXISTS documents (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NOT NULL,
                title VARCHAR(255) NOT NULL,
                description TEXT,
                filename VARCHAR(255) NOT NULL,
                original_filename VARCHAR(255) NOT NULL,
                file_size INT NOT NULL,
                file_type VARCHAR(100) NOT NULL,
                filiere VARCHAR(100) NOT NULL,
                niveau VARCHAR(10) NOT NULL,
                matiere VARCHAR(100) DEFAULT NULL,
                type_document ENUM('examen', 'cours', 'td', 'tp', 'autre') DEFAULT 'autre',
                downloads INT DEFAULT 0,
                active TINYINT(1) DEFAULT 1,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
                INDEX idx_filiere (filiere),
                INDEX idx_niveau (niveau),
                INDEX idx_matiere (matiere),
                INDEX idx_type (type_document),
                INDEX idx_active (active),
                INDEX idx_created_at (created_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ",
        
        'messages' => "
            CREATE TABLE IF NOT EXISTS messages (
                id INT AUTO_INCREMENT PRIMARY KEY,
                sender_id INT NOT NULL,
                receiver_id INT DEFAULT NULL,
                subject VARCHAR(255) NOT NULL,
                message TEXT NOT NULL,
                is_read TINYINT(1) DEFAULT 0,
                is_public TINYINT(1) DEFAULT 0,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE,
                FOREIGN KEY (receiver_id) REFERENCES users(id) ON DELETE CASCADE,
                INDEX idx_sender (sender_id),
                INDEX idx_receiver (receiver_id),
                INDEX idx_is_read (is_read),
                INDEX idx_is_public (is_public),
                INDEX idx_created_at (created_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        "
    ];
    
    $created_tables = [];
    $errors = [];
    
    foreach ($tables_sql as $table_name => $sql) {
        try {
            $pdo->exec($sql);
            $created_tables[] = $table_name;
        } catch (Exception $e) {
            $errors[] = "Erreur création table $table_name: " . $e->getMessage();
        }
    }
    
    return [
        'created' => $created_tables,
        'errors' => $errors
    ];
}

/**
 * Vérifier l'intégrité des données
 */
function checkDataIntegrity($pdo) {
    $checks = [];
    
    try {
        // Vérifier les contraintes de clés étrangères
        $stmt = $pdo->query("
            SELECT 
                TABLE_NAME,
                CONSTRAINT_NAME,
                REFERENCED_TABLE_NAME
            FROM information_schema.KEY_COLUMN_USAGE 
            WHERE REFERENCED_TABLE_SCHEMA = DATABASE()
            AND REFERENCED_TABLE_NAME IS NOT NULL
        ");
        
        $foreign_keys = $stmt->fetchAll();
        $checks['foreign_keys'] = count($foreign_keys);
        
        // Vérifier les index
        $stmt = $pdo->query("
            SELECT 
                TABLE_NAME,
                INDEX_NAME,
                NON_UNIQUE
            FROM information_schema.STATISTICS 
            WHERE TABLE_SCHEMA = DATABASE()
            AND INDEX_NAME != 'PRIMARY'
        ");
        
        $indexes = $stmt->fetchAll();
        $checks['indexes'] = count($indexes);
        
        // Vérifier l'encodage des tables
        $stmt = $pdo->query("
            SELECT 
                TABLE_NAME,
                TABLE_COLLATION
            FROM information_schema.TABLES 
            WHERE TABLE_SCHEMA = DATABASE()
            AND TABLE_TYPE = 'BASE TABLE'
        ");
        
        $tables = $stmt->fetchAll();
        $utf8mb4_tables = 0;
        foreach ($tables as $table) {
            if (str_contains($table['TABLE_COLLATION'], 'utf8mb4')) {
                $utf8mb4_tables++;
            }
        }
        
        $checks['tables_total'] = count($tables);
        $checks['tables_utf8mb4'] = $utf8mb4_tables;
        
    } catch (Exception $e) {
        $checks['error'] = $e->getMessage();
    }
    
    return $checks;
}

/**
 * Fonction principale de vérification
 */
function runDatabaseCompatibilityCheck($pdo) {
    $results = [
        'compatibility' => checkDatabaseCompatibility($pdo),
        'optimizations' => optimizeDatabaseSettings($pdo),
        'integrity' => checkDataIntegrity($pdo),
        'timestamp' => date('Y-m-d H:i:s')
    ];
    
    return $results;
}
