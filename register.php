<?php
session_start();
require_once 'includes/db.php';

// Protection CSRF
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Traitement du formulaire d'inscription
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $errors = [];
    $success = false;

    // Vérification du token CSRF
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $errors['csrf'] = 'Token de sécurité invalide. Veuillez réessayer.';
    }

    // Récupération et validation des données
    $nom = trim(isset($_POST['nom']) ? $_POST['nom'] : '');
    $prenom = trim(isset($_POST['prenom']) ? $_POST['prenom'] : '');
    $email = trim(isset($_POST['email']) ? $_POST['email'] : '');
    $numero_etudiant = trim(isset($_POST['numero_etudiant']) ? $_POST['numero_etudiant'] : '');
    $filiere = trim(isset($_POST['filiere']) ? $_POST['filiere'] : '');
    $niveau = trim(isset($_POST['niveau']) ? $_POST['niveau'] : '');
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    $confirm_password = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : '';
    $terms = isset($_POST['terms']);

    // Validation des champs
    if (empty($nom)) {
        $errors['nom'] = 'Le nom est requis';
    } elseif (strlen($nom) > 100) {
        $errors['nom'] = 'Le nom ne peut pas dépasser 100 caractères';
    }

    if (empty($prenom)) {
        $errors['prenom'] = 'Le prénom est requis';
    } elseif (strlen($prenom) > 100) {
        $errors['prenom'] = 'Le prénom ne peut pas dépasser 100 caractères';
    }

    if (empty($email)) {
        $errors['email'] = 'L\'email est requis';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Format d\'email invalide';
    } elseif (strlen($email) > 255) {
        $errors['email'] = 'L\'email ne peut pas dépasser 255 caractères';
    } elseif (emailExists($email)) {
        $errors['email'] = 'Cet email est déjà utilisé';
    }

    if (empty($numero_etudiant)) {
        $errors['numero_etudiant'] = 'Le numéro étudiant est requis';
    } elseif (!preg_match('/^[0-9]{8,12}$/', $numero_etudiant)) {
        $errors['numero_etudiant'] = 'Le numéro étudiant doit contenir entre 8 et 12 chiffres';
    } elseif (numeroEtudiantExists($numero_etudiant)) {
        $errors['numero_etudiant'] = 'Ce numéro étudiant est déjà utilisé';
    }

    if (empty($filiere)) {
        $errors['filiere'] = 'La filière est requise';
    }

    if (empty($niveau)) {
        $errors['niveau'] = 'Le niveau est requis';
    }

    if (empty($password)) {
        $errors['password'] = 'Le mot de passe est requis';
    } elseif (strlen($password) < 8) {
        $errors['password'] = 'Le mot de passe doit contenir au moins 8 caractères';
    } elseif (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)/', $password)) {
        $errors['password'] = 'Le mot de passe doit contenir au moins une minuscule, une majuscule et un chiffre';
    }

    if (empty($confirm_password)) {
        $errors['confirm_password'] = 'La confirmation du mot de passe est requise';
    } elseif ($password !== $confirm_password) {
        $errors['confirm_password'] = 'Les mots de passe ne correspondent pas';
    }

    if (!$terms) {
        $errors['terms'] = 'Vous devez accepter les conditions d\'utilisation';
    }

    // Si pas d'erreurs, traitement de l'inscription
    if (empty($errors)) {
        try {
            // Création de l'utilisateur en base de données
            $userData = [
                'nom' => $nom,
                'prenom' => $prenom,
                'email' => $email,
                'numero_etudiant' => $numero_etudiant,
                'filiere' => $filiere,
                'niveau' => $niveau,
                'password' => $password
            ];

            createUser($userData);

            // Succès de l'inscription
            $_SESSION['registration_success'] = true;
            $_SESSION['user_email'] = $email;

            // Redirection vers la page de connexion
            header('Location: login.php?registered=1');
            exit;

        } catch (Exception $e) {
            $errors['database'] = 'Erreur lors de l\'inscription. Veuillez réessayer.';
            error_log("Erreur inscription : " . $e->getMessage());
        }
    }
}

$page_title = "Inscription";
include 'includes/header.php';
?>

<main class="main-content">
    <div class="form-page">
        <div class="container">
            <div class="form-container">
                <div class="form-header">
                    <h1><i class="fas fa-user-plus"></i> Rejoindre la Mutuelle UDM</h1>
                    <p>Créez votre compte pour accéder à tous nos services</p>
                </div>

                <?php if (!empty($errors)): ?>
                    <div class="alert alert-error">
                        <i class="fas fa-exclamation-triangle"></i>
                        <strong>Erreurs détectées :</strong>
                        <ul>
                            <?php foreach ($errors as $error): ?>
                                <li><?php echo htmlspecialchars($error); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <form method="POST" action="" id="registerForm" novalidate>
                    <!-- Token CSRF -->
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

                    <!-- Informations personnelles -->
                    <div class="form-group">
                        <label for="nom" class="form-label">
                            <i class="fas fa-user"></i> Nom *
                        </label>
                        <input
                            type="text"
                            id="nom"
                            name="nom"
                            class="form-input <?php echo isset($errors['nom']) ? 'error' : ''; ?>"
                            value="<?php echo htmlspecialchars(isset($nom) ? $nom : ''); ?>"
                            required
                            placeholder="Votre nom de famille"
                        >
                        <div class="form-error <?php echo isset($errors['nom']) ? 'show' : ''; ?>">
                            <?php echo isset($errors['nom']) ? $errors['nom'] : ''; ?>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="prenom" class="form-label">
                            <i class="fas fa-user"></i> Prénom *
                        </label>
                        <input
                            type="text"
                            id="prenom"
                            name="prenom"
                            class="form-input <?php echo isset($errors['prenom']) ? 'error' : ''; ?>"
                            value="<?php echo htmlspecialchars(isset($prenom) ? $prenom : ''); ?>"
                            required
                            placeholder="Votre prénom"
                        >
                        <div class="form-error <?php echo isset($errors['prenom']) ? 'show' : ''; ?>">
                            <?php echo isset($errors['prenom']) ? $errors['prenom'] : ''; ?>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="email" class="form-label">
                            <i class="fas fa-envelope"></i> Email *
                        </label>
                        <input
                            type="email"
                            id="email"
                            name="email"
                            class="form-input <?php echo isset($errors['email']) ? 'error' : ''; ?>"
                            value="<?php echo htmlspecialchars(isset($email) ? $email : ''); ?>"
                            required
                            placeholder="votre.email@exemple.com"
                        >
                        <div class="form-error <?php echo isset($errors['email']) ? 'show' : ''; ?>">
                            <?php echo isset($errors['email']) ? $errors['email'] : ''; ?>
                        </div>
                    </div>

                    <!-- Informations académiques -->
                    <div class="form-group">
                        <label for="numero_etudiant" class="form-label">
                            <i class="fas fa-id-card"></i> Numéro étudiant *
                        </label>
                        <input
                            type="text"
                            id="numero_etudiant"
                            name="numero_etudiant"
                            class="form-input <?php echo isset($errors['numero_etudiant']) ? 'error' : ''; ?>"
                            value="<?php echo htmlspecialchars(isset($numero_etudiant) ? $numero_etudiant : ''); ?>"
                            required
                            placeholder="Ex: 20240001234"
                            pattern="[0-9]{8,12}"
                        >
                        <div class="form-error <?php echo isset($errors['numero_etudiant']) ? 'show' : ''; ?>">
                            <?php echo isset($errors['numero_etudiant']) ? $errors['numero_etudiant'] : ''; ?>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="filiere" class="form-label">
                            <i class="fas fa-graduation-cap"></i> Filière *
                        </label>
                        <select
                            id="filiere"
                            name="filiere"
                            class="form-select <?php echo isset($errors['filiere']) ? 'error' : ''; ?>"
                            required
                        >
                            <option value="">Choisissez votre filière</option>
                            <option value="informatique" <?php echo (isset($filiere) && $filiere === 'informatique') ? 'selected' : ''; ?>>Informatique</option>
                            <option value="gestion" <?php echo (isset($filiere) && $filiere === 'gestion') ? 'selected' : ''; ?>>Gestion</option>
                            <option value="economie" <?php echo (isset($filiere) && $filiere === 'economie') ? 'selected' : ''; ?>>Économie</option>
                            <option value="droit" <?php echo (isset($filiere) && $filiere === 'droit') ? 'selected' : ''; ?>>Droit</option>
                            <option value="medecine" <?php echo (isset($filiere) && $filiere === 'medecine') ? 'selected' : ''; ?>>Médecine</option>
                            <option value="ingenierie" <?php echo (isset($filiere) && $filiere === 'ingenierie') ? 'selected' : ''; ?>>Ingénierie</option>
                            <option value="lettres" <?php echo (isset($filiere) && $filiere === 'lettres') ? 'selected' : ''; ?>>Lettres et Sciences Humaines</option>
                            <option value="sciences" <?php echo (isset($filiere) && $filiere === 'sciences') ? 'selected' : ''; ?>>Sciences</option>
                            <option value="autre" <?php echo (isset($filiere) && $filiere === 'autre') ? 'selected' : ''; ?>>Autre</option>
                        </select>
                        <div class="form-error <?php echo isset($errors['filiere']) ? 'show' : ''; ?>">
                            <?php echo isset($errors['filiere']) ? $errors['filiere'] : ''; ?>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="niveau" class="form-label">
                            <i class="fas fa-layer-group"></i> Niveau d'études *
                        </label>
                        <select
                            id="niveau"
                            name="niveau"
                            class="form-select <?php echo isset($errors['niveau']) ? 'error' : ''; ?>"
                            required
                        >
                            <option value="">Choisissez votre niveau</option>
                            <option value="L1" <?php echo (isset($niveau) && $niveau === 'L1') ? 'selected' : ''; ?>>Licence 1ère année (L1)</option>
                            <option value="L2" <?php echo (isset($niveau) && $niveau === 'L2') ? 'selected' : ''; ?>>Licence 2ème année (L2)</option>
                            <option value="L3" <?php echo (isset($niveau) && $niveau === 'L3') ? 'selected' : ''; ?>>Licence 3ème année (L3)</option>
                            <option value="M1" <?php echo (isset($niveau) && $niveau === 'M1') ? 'selected' : ''; ?>>Master 1ère année (M1)</option>
                            <option value="M2" <?php echo (isset($niveau) && $niveau === 'M2') ? 'selected' : ''; ?>>Master 2ème année (M2)</option>
                            <option value="Doctorat" <?php echo (isset($niveau) && $niveau === 'Doctorat') ? 'selected' : ''; ?>>Doctorat</option>
                        </select>
                        <div class="form-error <?php echo isset($errors['niveau']) ? 'show' : ''; ?>">
                            <?php echo isset($errors['niveau']) ? $errors['niveau'] : ''; ?>
                        </div>
                    </div>

                    <!-- Mot de passe -->
                    <div class="form-group">
                        <label for="password" class="form-label">
                            <i class="fas fa-lock"></i> Mot de passe *
                        </label>
                        <input
                            type="password"
                            id="password"
                            name="password"
                            class="form-input <?php echo isset($errors['password']) ? 'error' : ''; ?>"
                            required
                            placeholder="Minimum 8 caractères"
                            minlength="8"
                        >
                        <div class="form-error <?php echo isset($errors['password']) ? 'show' : ''; ?>">
                            <?php echo isset($errors['password']) ? $errors['password'] : ''; ?>
                        </div>
                        <small style="color: var(--text-light); font-size: 0.8rem;">
                            Le mot de passe doit contenir au moins 8 caractères avec une minuscule, une majuscule et un chiffre.
                        </small>
                    </div>

                    <div class="form-group">
                        <label for="confirm_password" class="form-label">
                            <i class="fas fa-lock"></i> Confirmer le mot de passe *
                        </label>
                        <input
                            type="password"
                            id="confirm_password"
                            name="confirm_password"
                            class="form-input <?php echo isset($errors['confirm_password']) ? 'error' : ''; ?>"
                            required
                            placeholder="Répétez votre mot de passe"
                        >
                        <div class="form-error <?php echo isset($errors['confirm_password']) ? 'show' : ''; ?>">
                            <?php echo isset($errors['confirm_password']) ? $errors['confirm_password'] : ''; ?>
                        </div>
                    </div>

                    <!-- Conditions d'utilisation -->
                    <div class="form-checkbox">
                        <input
                            type="checkbox"
                            id="terms"
                            name="terms"
                            required
                            <?php echo isset($_POST['terms']) ? 'checked' : ''; ?>
                        >
                        <label for="terms">
                            J'accepte les <a href="#" target="_blank">conditions d'utilisation</a> et la
                            <a href="#" target="_blank">politique de confidentialité</a> de la Mutuelle UDM *
                        </label>
                    </div>
                    <div class="form-error <?php echo isset($errors['terms']) ? 'show' : ''; ?>">
                        <?php echo isset($errors['terms']) ? $errors['terms'] : ''; ?>
                    </div>

                    <!-- Bouton de soumission -->
                    <button type="submit" class="form-submit">
                        <i class="fas fa-user-plus"></i> Créer mon compte
                    </button>

                    <!-- Liens -->
                    <div class="form-links">
                        <p>Vous avez déjà un compte ? <a href="login.php">Se connecter</a></p>
                    </div>
                </form>
            </div>
        </div>
    </div>
</main>

<?php include 'includes/footer.php'; ?>

<script>
// Validation côté client
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('registerForm');
    const passwordInput = document.getElementById('password');
    const confirmPasswordInput = document.getElementById('confirm_password');

    // Validation en temps réel du mot de passe
    passwordInput.addEventListener('input', function() {
        const password = this.value;
        const isValid = password.length >= 8 &&
                       /[a-z]/.test(password) &&
                       /[A-Z]/.test(password) &&
                       /\d/.test(password);

        this.classList.toggle('error', !isValid && password.length > 0);
        this.classList.toggle('success', isValid);
    });

    // Validation de la confirmation du mot de passe
    confirmPasswordInput.addEventListener('input', function() {
        const password = passwordInput.value;
        const confirmPassword = this.value;
        const isValid = password === confirmPassword && confirmPassword.length > 0;

        this.classList.toggle('error', !isValid && confirmPassword.length > 0);
        this.classList.toggle('success', isValid);
    });

    // Validation du numéro étudiant
    document.getElementById('numero_etudiant').addEventListener('input', function() {
        const value = this.value;
        const isValid = /^[0-9]{8,12}$/.test(value);

        this.classList.toggle('error', !isValid && value.length > 0);
        this.classList.toggle('success', isValid);
    });
});
</script>