<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' - ' : ''; ?>Mutuelle des Étudiants UDM</title>

    <!-- Fichiers de compatibilité -->
    <link rel="stylesheet" href="assets/css/compatibility.css">
    <link rel="stylesheet" href="assets/css/style.css">

    <!-- Font Awesome avec fallback -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet" onerror="this.onerror=null;this.href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css';">

    <!-- Meta tags pour la compatibilité -->
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="format-detection" content="telephone=no">

    <!-- Préchargement des polices critiques -->
    <link rel="preconnect" href="https://cdnjs.cloudflare.com" crossorigin>

    <!-- Script de détection des fonctionnalités -->
    <script>
        // Détection basique des fonctionnalités avant le chargement
        (function() {
            var html = document.documentElement;

            // Ajouter la classe no-js par défaut
            html.className = html.className.replace(/\bno-js\b/, 'js');

            // Détection de base
            if (!window.addEventListener) html.className += ' no-addeventlistener';
            if (!document.querySelector) html.className += ' no-queryselector';
            if (!window.localStorage) html.className += ' no-localstorage';
            if (!window.sessionStorage) html.className += ' no-sessionstorage';

            // Détection CSS
            var testEl = document.createElement('div');
            testEl.style.display = 'flex';
            if (testEl.style.display !== 'flex') html.className += ' no-flexbox';

            testEl.style.display = 'grid';
            if (testEl.style.display !== 'grid') html.className += ' no-grid';

            // Test des variables CSS
            if (!(window.CSS && CSS.supports && CSS.supports('color', 'var(--test)'))) {
                html.className += ' no-custom-properties';
            }
        })();
    </script>
</head>
<body>
    <header class="header">
        <nav class="navbar">
            <div class="nav-container">
                <div class="nav-logo">
                    <img src="assets/img/logo.png" alt="Logo UDM" class="logo">
                    <span class="logo-text">Mutuelle UDM</span>
                </div>
                <!-- Menu burger pour mobile -->
                <button class="mobile-menu-toggle" onclick="toggleMobileMenu()">
                    <i class="fas fa-bars"></i>
                </button>

                <ul class="nav-menu" id="navMenu">
                    <li class="nav-item">
                        <a href="index.php" class="nav-link">
                            <i class="fas fa-home"></i>
                            <span class="nav-text">Accueil</span>
                        </a>
                    </li>

                    <?php if (isset($_SESSION['user_id'])): ?>
                        <!-- Menu principal pour utilisateurs connectés -->
                        <li class="nav-item">
                            <a href="bank.php" class="nav-link">
                                <i class="fas fa-book"></i>
                                <span class="nav-text">Banque</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="results.php" class="nav-link">
                                <i class="fas fa-chart-bar"></i>
                                <span class="nav-text">Résultats</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="messages.php" class="nav-link">
                                <i class="fas fa-comments"></i>
                                <span class="nav-text">Messages</span>
                                <?php
                                if (function_exists('getUnreadMessagesCount')) {
                                    $unread = getUnreadMessagesCount($_SESSION['user_id']);
                                    if ($unread > 0) {
                                        echo "<span class='notification-badge'>$unread</span>";
                                    }
                                }
                                ?>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="manuel_utilisateur.php" class="nav-link">
                                <i class="fas fa-question-circle"></i>
                                <span class="nav-text">Aide</span>
                            </a>
                        </li>

                        <!-- Menu modérateur/admin groupé -->
                        <?php if (function_exists('canAccess') && (canAccess('dashboard') || canAccess('upload') || canAccess('role_management') || canAccess('system_logs'))): ?>
                            <li class="nav-item dropdown">
                                <a href="#" class="nav-link dropdown-toggle">
                                    <i class="fas fa-cog"></i>
                                    <span class="nav-text">Outils</span>
                                </a>
                                <ul class="dropdown-menu">
                                    <?php if (canAccess('dashboard')): ?>
                                        <li><a href="dashboard.php" class="dropdown-link">
                                            <i class="fas fa-tachometer-alt"></i> Tableau de bord
                                        </a></li>
                                    <?php endif; ?>

                                    <?php if (canAccess('upload')): ?>
                                        <li><a href="upload_document.php" class="dropdown-link">
                                            <i class="fas fa-upload"></i> Upload documents
                                        </a></li>
                                    <?php endif; ?>

                                    <?php if (canAccess('role_management') || canAccess('system_logs')): ?>
                                        <li class="dropdown-divider"></li>
                                        <?php if (canAccess('role_management')): ?>
                                            <li><a href="manage_roles.php" class="dropdown-link">
                                                <i class="fas fa-users-cog"></i> Gestion des rôles
                                            </a></li>
                                        <?php endif; ?>

                                        <?php if (canAccess('system_logs')): ?>
                                            <li><a href="system_logs.php" class="dropdown-link">
                                                <i class="fas fa-file-alt"></i> Logs système
                                            </a></li>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </ul>
                            </li>
                        <?php endif; ?>

                        <!-- Menu utilisateur -->
                        <li class="nav-item dropdown user-menu">
                            <a href="#" class="nav-link dropdown-toggle user-profile">
                                <div class="user-avatar">
                                    <?php
                                    $initials = '';
                                    if (isset($_SESSION['user_name'])) {
                                        $names = explode(' ', $_SESSION['user_name']);
                                        $initials = strtoupper(substr($names[0], 0, 1) . (isset($names[1]) ? substr($names[1], 0, 1) : ''));
                                    }
                                    echo $initials ?: 'U';
                                    ?>
                                </div>
                                <span class="nav-text user-info">
                                    <span class="user-name"><?php echo htmlspecialchars(isset($_SESSION['user_name']) ? $_SESSION['user_name'] : 'Utilisateur'); ?></span>
                                    <?php
                                    if (function_exists('getRoleBadge') && isset($_SESSION['user_role'])) {
                                        echo '<span class="user-role">' . getRoleBadge($_SESSION['user_role']) . '</span>';
                                    }
                                    ?>
                                </span>
                            </a>
                            <ul class="dropdown-menu user-dropdown">
                                <li class="user-info-header">
                                    <div class="user-avatar-large">
                                        <?php echo $initials ?: 'U'; ?>
                                    </div>
                                    <div class="user-details">
                                        <div class="user-name"><?php echo htmlspecialchars(isset($_SESSION['user_name']) ? $_SESSION['user_name'] : 'Utilisateur'); ?></div>
                                        <div class="user-email"><?php echo htmlspecialchars(isset($_SESSION['user_email']) ? $_SESSION['user_email'] : ''); ?></div>
                                        <?php
                                        if (function_exists('getRoleBadge') && isset($_SESSION['user_role'])) {
                                            echo '<div class="user-role-badge">' . getRoleBadge($_SESSION['user_role']) . '</div>';
                                        }
                                        ?>
                                    </div>
                                </li>
                                <li class="dropdown-divider"></li>
                                <li><a href="profile.php" class="dropdown-link">
                                    <i class="fas fa-user"></i> Mon profil
                                </a></li>
                                <li><a href="settings.php" class="dropdown-link">
                                    <i class="fas fa-cog"></i> Paramètres
                                </a></li>
                                <li class="dropdown-divider"></li>
                                <li><a href="logout.php" class="dropdown-link logout-link" onclick="return confirm('Êtes-vous sûr de vouloir vous déconnecter ?')">
                                    <i class="fas fa-sign-out-alt"></i> Déconnexion
                                </a></li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a href="login.php" class="nav-link login-btn">
                                <i class="fas fa-sign-in-alt"></i>
                                <span class="nav-text">Connexion</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="register.php" class="nav-link">
                                <i class="fas fa-user-plus"></i>
                                <span class="nav-text">Inscription</span>
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </nav>
    </header>

    <script>
    // Gestion du menu mobile
    function toggleMobileMenu() {
        const navMenu = document.getElementById('navMenu');
        navMenu.classList.toggle('active');
    }

    // Fermer le menu mobile quand on clique ailleurs
    document.addEventListener('click', function(event) {
        const navMenu = document.getElementById('navMenu');
        const toggleButton = document.querySelector('.mobile-menu-toggle');

        if (navMenu && toggleButton && !navMenu.contains(event.target) && !toggleButton.contains(event.target)) {
            navMenu.classList.remove('active');
        }
    });

    // Fermer le menu mobile quand on clique sur un lien
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.nav-link').forEach(link => {
            link.addEventListener('click', function() {
                const navMenu = document.getElementById('navMenu');
                if (navMenu) {
                    navMenu.classList.remove('active');
                }
            });
        });
    });

    // Gestion responsive du texte des liens
    function handleResponsiveNav() {
        const navTexts = document.querySelectorAll('.nav-text');
        const userInfo = document.querySelector('.user-info');

        if (window.innerWidth <= 1024) {
            navTexts.forEach(text => {
                if (!text.closest('.user-menu') && !text.closest('.dropdown-menu')) {
                    text.style.display = 'none';
                }
            });
            if (userInfo && !userInfo.closest('.dropdown-menu')) {
                userInfo.style.display = 'none';
            }
        } else {
            navTexts.forEach(text => {
                text.style.display = 'block';
            });
            if (userInfo) {
                userInfo.style.display = 'flex';
            }
        }
    }

    // Appliquer au chargement et au redimensionnement
    window.addEventListener('load', handleResponsiveNav);
    window.addEventListener('resize', handleResponsiveNav);
    </script>

    <!-- Scripts de compatibilité -->
    <script src="assets/js/compatibility.js"></script>
    <script src="assets/js/main.js"></script>