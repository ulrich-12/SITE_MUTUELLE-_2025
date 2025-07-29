<?php
/**
 * Script de vérification du déploiement
 * Vérifie que tous les fichiers essentiels sont présents et fonctionnels
 */

session_start();
require_once 'includes/auth_middleware.php';

// Vérification des permissions (Admin ou Super Admin)
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_role'], ['admin', 'super_admin'])) {
    header('Location: access_denied.php');
    exit;
}

$page_title = "Vérification du Déploiement";
include 'includes/header.php';

// Tests de vérification
$tests = [];

// 1. Vérifier les fichiers essentiels
$essential_files = [
    'index.php', 'login.php', 'logout.php', 'register.php', 'dashboard.php',
    'bank.php', 'results.php', 'messages.php', 'upload_document.php',
    'manage_roles.php', 'system_logs.php', 'access_denied.php'
];

foreach ($essential_files as $file) {
    $tests[] = [
        'name' => "Fichier $file",
        'status' => file_exists($file),
        'message' => file_exists($file) ? 'Présent' : 'MANQUANT'
    ];
}

// 2. Vérifier les dossiers essentiels
$essential_dirs = ['includes', 'assets', 'api', 'uploads', 'logs'];

foreach ($essential_dirs as $dir) {
    $tests[] = [
        'name' => "Dossier $dir/",
        'status' => is_dir($dir),
        'message' => is_dir($dir) ? 'Présent' : 'MANQUANT'
    ];
}

// 3. Vérifier les APIs essentielles
$essential_apis = [
    'api/add_user.php', 'api/delete_message.php', 'api/download_document.php',
    'api/export_logs.php', 'api/export_users.php', 'api/fetch_results.php',
    'api/get_admin_stats.php', 'api/send_message.php', 'api/upload_documents.php'
];

foreach ($essential_apis as $api) {
    $tests[] = [
        'name' => "API $api",
        'status' => file_exists($api),
        'message' => file_exists($api) ? 'Présent' : 'MANQUANT'
    ];
}

// 4. Vérifier les permissions
$writable_dirs = ['uploads', 'logs'];

foreach ($writable_dirs as $dir) {
    $tests[] = [
        'name' => "Permissions $dir/",
        'status' => is_writable($dir),
        'message' => is_writable($dir) ? 'Écriture OK' : 'ÉCRITURE IMPOSSIBLE'
    ];
}

// 5. Vérifier la base de données
try {
    require_once 'includes/db.php';
    $db_test = $pdo->query("SELECT 1")->fetch();
    $tests[] = [
        'name' => 'Connexion Base de Données',
        'status' => true,
        'message' => 'Connexion OK'
    ];
} catch (Exception $e) {
    $tests[] = [
        'name' => 'Connexion Base de Données',
        'status' => false,
        'message' => 'ERREUR: ' . $e->getMessage()
    ];
}

// 6. Vérifier que les fichiers optionnels ne sont pas accessibles
$optional_files = [
    'manuel_utilisateur.php', 'guide_demarrage.php', 'api/backup_database.php',
    'api/populate_test_data.php', 'MANUEL_UTILISATEUR.md'
];

foreach ($optional_files as $file) {
    $tests[] = [
        'name' => "Fichier optionnel $file (doit être absent)",
        'status' => !file_exists($file),
        'message' => !file_exists($file) ? 'Correctement déplacé' : 'ENCORE PRÉSENT'
    ];
}

// Calculer le score
$passed = array_filter($tests, function($test) { return $test['status']; });
$total = count($tests);
$score = count($passed);
$percentage = round(($score / $total) * 100);
?>

<main class="main-content">
    <div class="container" style="padding: 2rem 0;">
        <div style="background: linear-gradient(135deg, var(--primary-color), var(--accent-color)); color: white; padding: 2rem; border-radius: 10px; margin-bottom: 2rem; text-align: center;">
            <h1><i class="fas fa-check-circle"></i> Vérification du Déploiement</h1>
            <p style="font-size: 1.2rem; margin: 0.5rem 0;">Contrôle de l'intégrité de la plateforme</p>
            <div style="margin-top: 1rem;">
                <span style="font-size: 2rem; font-weight: bold;"><?= $score ?>/<?= $total ?></span>
                <span style="font-size: 1.2rem; margin-left: 1rem;">(<?= $percentage ?>%)</span>
            </div>
        </div>

        <div style="background: white; padding: 2rem; border-radius: 10px; box-shadow: var(--shadow); margin-bottom: 2rem;">
            <h2><i class="fas fa-info-circle"></i> Résumé</h2>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
                <div style="background: <?= $percentage >= 90 ? '#e8f5e8' : ($percentage >= 70 ? '#fff3cd' : '#ffebee') ?>; padding: 1rem; border-radius: 8px; text-align: center;">
                    <h3 style="margin: 0; color: <?= $percentage >= 90 ? '#4caf50' : ($percentage >= 70 ? '#ff9800' : '#f44336') ?>;">
                        <?= $percentage >= 90 ? '✅ Excellent' : ($percentage >= 70 ? '⚠️ Attention' : '❌ Problèmes') ?>
                    </h3>
                    <p style="margin: 0.5rem 0;">Score: <?= $percentage ?>%</p>
                </div>
                <div style="background: #f8f9fa; padding: 1rem; border-radius: 8px; text-align: center;">
                    <h3 style="margin: 0; color: var(--primary-color);">Tests Réussis</h3>
                    <p style="margin: 0.5rem 0;"><?= $score ?> sur <?= $total ?></p>
                </div>
            </div>
        </div>

        <div style="background: white; padding: 2rem; border-radius: 10px; box-shadow: var(--shadow);">
            <h2><i class="fas fa-list-check"></i> Détails des Tests</h2>
            
            <div style="display: grid; gap: 0.5rem;">
                <?php foreach ($tests as $test): ?>
                    <div style="display: flex; align-items: center; padding: 0.75rem; background: <?= $test['status'] ? '#e8f5e8' : '#ffebee' ?>; border-radius: 5px; border-left: 4px solid <?= $test['status'] ? '#4caf50' : '#f44336' ?>;">
                        <span style="font-size: 1.2rem; margin-right: 1rem;">
                            <?= $test['status'] ? '✅' : '❌' ?>
                        </span>
                        <div style="flex: 1;">
                            <strong><?= htmlspecialchars($test['name']) ?></strong>
                            <span style="margin-left: 1rem; color: #666;">
                                <?= htmlspecialchars($test['message']) ?>
                            </span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <?php if ($percentage < 100): ?>
        <div style="background: white; padding: 2rem; border-radius: 10px; box-shadow: var(--shadow); margin-top: 2rem;">
            <h2><i class="fas fa-exclamation-triangle"></i> Actions Recommandées</h2>
            <div style="background: #fff3cd; padding: 1rem; border-radius: 8px; border-left: 4px solid #ff9800;">
                <ul>
                    <li>Vérifiez que tous les fichiers essentiels sont présents</li>
                    <li>Assurez-vous que les permissions d'écriture sont correctes</li>
                    <li>Testez la connexion à la base de données</li>
                    <li>Confirmez que les fichiers optionnels sont bien déplacés</li>
                </ul>
            </div>
        </div>
        <?php endif; ?>

        <div style="text-align: center; margin-top: 2rem;">
            <a href="dashboard.php" class="btn btn-primary">
                <i class="fas fa-arrow-left"></i> Retour au Tableau de Bord
            </a>
            <button onclick="location.reload()" class="btn btn-secondary" style="margin-left: 1rem;">
                <i class="fas fa-redo"></i> Relancer les Tests
            </button>
        </div>
    </div>
</main>

<?php include 'includes/footer.php'; ?>
