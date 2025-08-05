<?php
session_start();
require_once 'includes/db.php';

// Protection CSRF
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Gestion des messages de statut
$status_message = '';
$status_type = '';

if (isset($_GET['message'])) {
    switch ($_GET['message']) {
        case 'logged_out':
            $status_message = 'Vous avez été déconnecté avec succès.';
            $status_type = 'success';
            break;
        case 'already_logged_out':
            $status_message = 'Vous étiez déjà déconnecté.';
            $status_type = 'info';
            break;
        case 'session_expired':
            $status_message = 'Votre session a expiré. Veuillez vous reconnecter.';
            $status_type = 'warning';
            break;
        case 'access_denied':
            $status_message = 'Accès refusé. Veuillez vous connecter.';
            $status_type = 'error';
            break;
    }
}

// Redirection si déjà connecté
if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}

// Traitement du formulaire de connexion
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $errors = [];
    $success = false;

    // Vérification du token CSRF
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $errors['csrf'] = 'Token de sécurité invalide. Veuillez réessayer.';
    }

    // Récupération des données
    $email = trim(isset($_POST['email']) ? $_POST['email'] : '');
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    $remember = isset($_POST['remember']);

    // Validation des champs
    if (empty($email)) {
        $errors['email'] = 'L\'email est requis';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Format d\'email invalide';
    }

    if (empty($password)) {
        $errors['password'] = 'Le mot de passe est requis';
    }

    // Si pas d'erreurs, tentative de connexion
    if (empty($errors)) {
        try {
            // Récupération de l'utilisateur en base de données
            $user = getUserByEmail($email);

            if ($user && verifyPassword($password, $user['password_hash'])) {
                // Connexion réussie
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_name'] = $user['prenom'] . ' ' . $user['nom'];
                $_SESSION['user_filiere'] = $user['filiere'];
                $_SESSION['user_niveau'] = $user['niveau'];

                // Mise à jour de la dernière connexion
                updateLastLogin($user['id']);

                // Gestion du "Se souvenir de moi"
                if ($remember) {
                    // Création d'un token de session persistant
                    $token = bin2hex(random_bytes(32));
                    $expires = time() + (30 * 24 * 60 * 60); // 30 jours

                    // TODO: Sauvegarder le token en base de données
                    setcookie('remember_token', $token, $expires, '/', '', false, true);
                }

                // Redirection vers le dashboard
                header('Location: dashboard.php');
                exit;
            } else {
                $errors['login'] = 'Email ou mot de passe incorrect';
            }
        } catch (Exception $e) {
            $errors['login'] = 'Erreur lors de la connexion. Veuillez réessayer.';
            error_log("Erreur connexion : " . $e->getMessage());
        }
    }
}

// Messages de succès après inscription
$registration_success = isset($_GET['registered']) && $_GET['registered'] == '1';

$page_title = "Connexion";
include 'includes/header.php';
?>

<main class="main-content">
    <div class="form-page">
        <div class="container">
            <div class="form-container">
                <div class="form-header">
                    <h1><i class="fas fa-sign-in-alt"></i> Connexion</h1>
                    <p>Accédez à votre espace membre</p>
                </div>

                <?php if ($registration_success): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i>
                        <strong>Inscription réussie !</strong> Vous pouvez maintenant vous connecter avec vos identifiants.
                    </div>
                <?php endif; ?>

                <?php if ($status_message): ?>
                    <div class="alert alert-<?php echo $status_type; ?>">
                        <i class="fas fa-<?php echo $status_type === 'success' ? 'check-circle' : ($status_type === 'error' ? 'exclamation-triangle' : ($status_type === 'warning' ? 'exclamation-circle' : 'info-circle')); ?>"></i>
                        <?php echo htmlspecialchars($status_message); ?>
                    </div>
                <?php endif; ?>

                <?php if (isset($errors['login'])): ?>
                    <div class="alert alert-error">
                        <i class="fas fa-exclamation-triangle"></i>
                        <?php echo htmlspecialchars($errors['login']); ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="" id="loginForm" novalidate>
                    <!-- Token CSRF -->
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

                    <div class="form-group">
                        <label for="email" class="form-label">
                            <i class="fas fa-envelope"></i> Email
                        </label>
                        <input
                            type="email"
                            id="email"
                            name="email"
                            class="form-input <?php echo isset($errors['email']) ? 'error' : ''; ?>"
                            value="<?php echo htmlspecialchars(isset($email) ? $email : ''); ?>"
                            required
                            placeholder="votre.email@exemple.com"
                            autocomplete="email"
                        >
                        <div class="form-error <?php echo isset($errors['email']) ? 'show' : ''; ?>">
                            <?php echo isset($errors['email']) ? $errors['email'] : ''; ?>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="password" class="form-label">
                            <i class="fas fa-lock"></i> Mot de passe
                        </label>
                        <input
                            type="password"
                            id="password"
                            name="password"
                            class="form-input <?php echo isset($errors['password']) ? 'error' : ''; ?>"
                            required
                            placeholder="Votre mot de passe"
                            autocomplete="current-password"
                        >
                        <div class="form-error <?php echo isset($errors['password']) ? 'show' : ''; ?>">
                            <?php echo isset($errors['password']) ? $errors['password'] : ''; ?>
                        </div>
                    </div>

                    <div class="form-checkbox">
                        <input
                            type="checkbox"
                            id="remember"
                            name="remember"
                            <?php echo isset($_POST['remember']) ? 'checked' : ''; ?>
                        >
                        <label for="remember">
                            Se souvenir de moi
                        </label>
                    </div>

                    <button type="submit" class="form-submit">
                        <i class="fas fa-sign-in-alt"></i> Se connecter
                    </button>

                    <div class="form-links">
                        <p><a href="#" id="forgotPasswordLink">Mot de passe oublié ?</a></p>

                        <div class="form-divider">
                            <span>ou</span>
                        </div>

                        <p>Pas encore de compte ? <a href="register.php">S'inscrire</a></p>
                    </div>
                </form>

                <!-- Section d'aide pour les tests -->
                <div style="margin-top: 2rem; padding: 1rem; background-color: #f0f8ff; border-radius: 5px; border-left: 4px solid #2196f3;">
                    <h4 style="color: #1565c0; margin-bottom: 0.5rem;">
                        <i class="fas fa-info-circle"></i> Compte de test
                    </h4>
                    <p style="color: #1565c0; font-size: 0.9rem; margin: 0;">
                        <strong>Email :</strong> test@udm.ma<br>
                        <strong>Mot de passe :</strong> Test123!
                    </p>
                </div>
            </div>
        </div>
    </div>
</main>

<!-- Modal pour mot de passe oublié -->
<div id="forgotPasswordModal" class="modal" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h3><i class="fas fa-key"></i> Mot de passe oublié</h3>
            <span class="modal-close">&times;</span>
        </div>
        <div class="modal-body">
            <p>Entrez votre adresse email pour recevoir un lien de réinitialisation :</p>
            <form id="forgotPasswordForm">
                <div class="form-group">
                    <label for="reset_email" class="form-label">Email</label>
                    <input
                        type="email"
                        id="reset_email"
                        name="reset_email"
                        class="form-input"
                        required
                        placeholder="votre.email@exemple.com"
                    >
                </div>
                <button type="submit" class="form-submit">
                    <i class="fas fa-paper-plane"></i> Envoyer le lien
                </button>
            </form>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('loginForm');
    const forgotPasswordLink = document.getElementById('forgotPasswordLink');
    const forgotPasswordModal = document.getElementById('forgotPasswordModal');
    const modalClose = document.querySelector('.modal-close');
    const forgotPasswordForm = document.getElementById('forgotPasswordForm');

    // Validation côté client
    form.addEventListener('submit', function(e) {
        const email = document.getElementById('email').value;
        const password = document.getElementById('password').value;

        if (!email || !password) {
            e.preventDefault();
            showNotification('Veuillez remplir tous les champs', 'error');
        }
    });

    // Modal mot de passe oublié
    forgotPasswordLink.addEventListener('click', function(e) {
        e.preventDefault();
        forgotPasswordModal.style.display = 'flex';
    });

    modalClose.addEventListener('click', function() {
        forgotPasswordModal.style.display = 'none';
    });

    window.addEventListener('click', function(e) {
        if (e.target === forgotPasswordModal) {
            forgotPasswordModal.style.display = 'none';
        }
    });

    // Traitement du formulaire de réinitialisation
    forgotPasswordForm.addEventListener('submit', function(e) {
        e.preventDefault();
        const email = document.getElementById('reset_email').value;

        if (email) {
            // TODO: Envoyer la demande de réinitialisation
            showNotification('Un email de réinitialisation a été envoyé à ' + email, 'success');
            forgotPasswordModal.style.display = 'none';
            document.getElementById('reset_email').value = '';
        }
    });
});

// Fonction de notification (déjà définie dans main.js mais redéfinie ici pour sécurité)
function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.textContent = message;

    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 1rem 1.5rem;
        background-color: ${type === 'success' ? '#4caf50' : type === 'error' ? '#f44336' : '#2196f3'};
        color: white;
        border-radius: 5px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.2);
        z-index: 10000;
        transform: translateX(100%);
        transition: transform 0.3s ease;
    `;

    document.body.appendChild(notification);

    setTimeout(() => {
        notification.style.transform = 'translateX(0)';
    }, 100);

    setTimeout(() => {
        notification.style.transform = 'translateX(100%)';
        setTimeout(() => {
            document.body.removeChild(notification);
        }, 300);
    }, 3000);
}
</script>