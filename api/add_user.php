<?php
session_start();
require_once '../includes/db.php';

// Vérification de l'authentification et des permissions admin
if (!isset($_SESSION['user_id']) || !hasPermission($_SESSION['user_id'], 'manage_users')) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Accès refusé']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
    exit;
}

try {
    // Récupération et validation des données
    $name = trim(isset($_POST['name']) ? $_POST['name'] : '');
    $email = trim(isset($_POST['email']) ? $_POST['email'] : '');
    $role = trim(isset($_POST['role']) ? $_POST['role'] : 'etudiant');
    $filiere = trim(isset($_POST['filiere']) ? $_POST['filiere'] : '');
    $niveau = trim(isset($_POST['niveau']) ? $_POST['niveau'] : '');

    // Validation
    if (empty($name) || empty($email)) {
        throw new Exception('Le nom et l\'email sont obligatoires');
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception('Format d\'email invalide');
    }

    // Vérifier si l'email existe déjà
    $existing = executeQuery("SELECT id FROM users WHERE email = ?", [$email])->fetch();
    if ($existing) {
        throw new Exception('Cet email est déjà utilisé');
    }

    // Validation du rôle
    $allowed_roles = ['etudiant', 'moderateur'];
    if ($_SESSION['user_role'] === 'super_admin') {
        $allowed_roles[] = 'admin';
    }
    
    if (!in_array($role, $allowed_roles)) {
        throw new Exception('Rôle non autorisé');
    }

    // Séparer nom et prénom
    $name_parts = explode(' ', $name, 2);
    $nom = $name_parts[0];
    $prenom = isset($name_parts[1]) ? $name_parts[1] : '';

    // Générer un mot de passe temporaire
    $temp_password = bin2hex(random_bytes(8));
    $hashed_password = password_hash($temp_password, PASSWORD_DEFAULT);

    // Insérer l'utilisateur
    $query = "INSERT INTO users (nom, prenom, email, password, role, filiere, niveau, created_at) 
              VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";
    
    $params = [$nom, $prenom, $email, $hashed_password, $role, $filiere, $niveau];
    
    executeQuery($query, $params);
    $user_id = $pdo->lastInsertId();

    // Logger l'action
    logAction($_SESSION['user_id'], 'create_user', "Création de l'utilisateur {$email} avec le rôle {$role}");

    // Envoyer un email avec les identifiants (simulation)
    // Dans un vrai projet, vous enverriez un vrai email
    $email_content = "
    Bonjour {$nom} {$prenom},

    Votre compte a été créé sur la plateforme Mutuelle UDM.
    
    Email: {$email}
    Mot de passe temporaire: {$temp_password}
    Rôle: {$role}
    
    Veuillez vous connecter et changer votre mot de passe.
    
    Cordialement,
    L'équipe Mutuelle UDM
    ";

    // Log de l'email (pour simulation)
    error_log("Email envoyé à {$email}: " . $email_content);

    echo json_encode([
        'success' => true,
        'message' => 'Utilisateur créé avec succès',
        'user_id' => $user_id,
        'temp_password' => $temp_password, // À supprimer en production
        'email_sent' => true
    ]);

} catch (Exception $e) {
    error_log("Erreur création utilisateur : " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
