<?php
/**
 * Configuration de la base de données pour la Mutuelle UDM
 * Compatible PHP 5.4+ à 8.3+
 */

// Détection automatique de la version PHP et chargement du bon fichier de compatibilité
if (version_compare(PHP_VERSION, '7.4.0', '>=')) {
    // PHP moderne (7.4+)
    require_once __DIR__ . '/compatibility.php';
} else {
    // PHP legacy (5.4+)
    require_once __DIR__ . '/legacy_compatibility.php';
}

// Configuration de la base de données
define('DB_HOST', 'localhost');
define('DB_NAME', 'mutuelle_udm');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// Options PDO
$pdo_options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES " . DB_CHARSET
];

try {
    // Création de la connexion PDO
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET,
        DB_USER,
        DB_PASS,
        $pdo_options
    );
} catch (PDOException $e) {
    // En cas d'erreur de connexion
    error_log("Erreur de connexion à la base de données : " . $e->getMessage());
    die("Erreur de connexion à la base de données. Veuillez réessayer plus tard.");
}

/**
 * Fonction pour vérifier si la connexion à la base de données est active
 */
function isConnected() {
    global $pdo;
    try {
        // Vérifier si l'objet PDO existe
        if (!$pdo || !($pdo instanceof PDO)) {
            return false;
        }

        // Tester la connexion avec une requête simple
        $pdo->query("SELECT 1");
        return true;
    } catch (PDOException $e) {
        error_log("Vérification connexion DB échouée : " . $e->getMessage());
        return false;
    }
}

/**
 * Fonction pour exécuter une requête préparée
 */
function executeQuery($sql, $params = []) {
    global $pdo;
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    } catch (PDOException $e) {
        error_log("Erreur SQL : " . $e->getMessage());
        throw new Exception("Erreur lors de l'exécution de la requête");
    }
}

/**
 * Fonction pour récupérer un utilisateur par email
 */
function getUserByEmail($email) {
    $sql = "SELECT * FROM users WHERE email = ? AND active = 1";
    $stmt = executeQuery($sql, [$email]);
    return $stmt->fetch();
}

/**
 * Fonction pour récupérer un utilisateur par ID
 */
function getUserById($id) {
    $sql = "SELECT * FROM users WHERE id = ? AND active = 1";
    $stmt = executeQuery($sql, [$id]);
    return $stmt->fetch();
}

/**
 * Fonction pour créer un nouvel utilisateur
 */
function createUser($data) {
    $sql = "INSERT INTO users (nom, prenom, email, numero_etudiant, filiere, niveau, password_hash, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";

    $params = [
        $data['nom'],
        $data['prenom'],
        $data['email'],
        $data['numero_etudiant'],
        $data['filiere'],
        $data['niveau'],
        password_hash($data['password'], PASSWORD_DEFAULT)
    ];

    return executeQuery($sql, $params);
}

/**
 * Fonction pour vérifier si un email existe déjà
 */
function emailExists($email) {
    $sql = "SELECT COUNT(*) FROM users WHERE email = ?";
    $stmt = executeQuery($sql, [$email]);
    return $stmt->fetchColumn() > 0;
}

/**
 * Fonction pour vérifier si un numéro étudiant existe déjà
 */
function numeroEtudiantExists($numero) {
    $sql = "SELECT COUNT(*) FROM users WHERE numero_etudiant = ?";
    $stmt = executeQuery($sql, [$numero]);
    return $stmt->fetchColumn() > 0;
}

/**
 * Fonction pour vérifier un mot de passe
 */
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

/**
 * Fonction pour mettre à jour la dernière connexion
 */
function updateLastLogin($userId) {
    $sql = "UPDATE users SET last_login = NOW() WHERE id = ?";
    return executeQuery($sql, [$userId]);
}

/**
 * FONCTIONS POUR LA BANQUE D'ÉPREUVES
 */

/**
 * Fonction pour récupérer les documents avec filtres et pagination
 */
function getDocuments($filters = [], $page = 1, $limit = 12) {
    $offset = ($page - 1) * $limit;
    $where_conditions = ["d.active = 1"];
    $params = [];

    // Construction des filtres
    if (!empty($filters['search'])) {
        $where_conditions[] = "(d.title LIKE ? OR d.description LIKE ? OR d.matiere LIKE ?)";
        $search_term = '%' . $filters['search'] . '%';
        $params[] = $search_term;
        $params[] = $search_term;
        $params[] = $search_term;
    }

    if (!empty($filters['filiere'])) {
        $where_conditions[] = "d.filiere = ?";
        $params[] = $filters['filiere'];
    }

    if (!empty($filters['niveau'])) {
        $where_conditions[] = "d.niveau = ?";
        $params[] = $filters['niveau'];
    }

    if (!empty($filters['matiere'])) {
        $where_conditions[] = "d.matiere = ?";
        $params[] = $filters['matiere'];
    }

    if (!empty($filters['type_document'])) {
        $where_conditions[] = "d.type_document = ?";
        $params[] = $filters['type_document'];
    }

    $where_clause = implode(' AND ', $where_conditions);

    $sql = "SELECT d.*, u.prenom, u.nom
            FROM documents d
            JOIN users u ON d.user_id = u.id
            WHERE $where_clause
            ORDER BY d.created_at DESC
            LIMIT ? OFFSET ?";

    $params[] = $limit;
    $params[] = $offset;

    $stmt = executeQuery($sql, $params);
    return $stmt->fetchAll();
}

/**
 * Fonction pour compter le nombre total de documents avec filtres
 */
function countDocuments($filters = []) {
    $where_conditions = ["d.active = 1"];
    $params = [];

    // Construction des filtres (même logique que getDocuments)
    if (!empty($filters['search'])) {
        $where_conditions[] = "(d.title LIKE ? OR d.description LIKE ? OR d.matiere LIKE ?)";
        $search_term = '%' . $filters['search'] . '%';
        $params[] = $search_term;
        $params[] = $search_term;
        $params[] = $search_term;
    }

    if (!empty($filters['filiere'])) {
        $where_conditions[] = "d.filiere = ?";
        $params[] = $filters['filiere'];
    }

    if (!empty($filters['niveau'])) {
        $where_conditions[] = "d.niveau = ?";
        $params[] = $filters['niveau'];
    }

    if (!empty($filters['matiere'])) {
        $where_conditions[] = "d.matiere = ?";
        $params[] = $filters['matiere'];
    }

    if (!empty($filters['type_document'])) {
        $where_conditions[] = "d.type_document = ?";
        $params[] = $filters['type_document'];
    }

    $where_clause = implode(' AND ', $where_conditions);

    $sql = "SELECT COUNT(*) FROM documents d WHERE $where_clause";
    $stmt = executeQuery($sql, $params);
    return $stmt->fetchColumn();
}

/**
 * Fonction pour récupérer un document par ID
 */
function getDocumentById($id) {
    $sql = "SELECT d.*, u.prenom, u.nom
            FROM documents d
            JOIN users u ON d.user_id = u.id
            WHERE d.id = ? AND d.active = 1";
    $stmt = executeQuery($sql, [$id]);
    return $stmt->fetch();
}

/**
 * Fonction pour incrémenter le compteur de téléchargements
 */
function incrementDownloadCount($documentId) {
    $sql = "UPDATE documents SET downloads = downloads + 1 WHERE id = ?";
    return executeQuery($sql, [$documentId]);
}

/**
 * Fonction pour récupérer les filières disponibles
 */
function getAvailableFilieres() {
    $sql = "SELECT DISTINCT filiere FROM documents WHERE active = 1 ORDER BY filiere";
    $stmt = executeQuery($sql);
    return $stmt->fetchAll(PDO::FETCH_COLUMN);
}

/**
 * Fonction pour récupérer les niveaux disponibles
 */
function getAvailableNiveaux() {
    $sql = "SELECT DISTINCT niveau FROM documents WHERE active = 1 ORDER BY niveau";
    $stmt = executeQuery($sql);
    return $stmt->fetchAll(PDO::FETCH_COLUMN);
}

/**
 * Fonction pour récupérer les matières disponibles
 */
function getAvailableMatieres() {
    $sql = "SELECT DISTINCT matiere FROM documents WHERE active = 1 AND matiere IS NOT NULL ORDER BY matiere";
    $stmt = executeQuery($sql);
    return $stmt->fetchAll(PDO::FETCH_COLUMN);
}

/**
 * Fonction pour ajouter un nouveau document
 */
function addDocument($data) {
    $sql = "INSERT INTO documents (user_id, title, description, filename, original_filename, file_size, file_type, filiere, niveau, matiere, type_document, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";

    $params = [
        $data['user_id'],
        $data['title'],
        $data['description'],
        $data['filename'],
        $data['original_filename'],
        $data['file_size'],
        $data['file_type'],
        $data['filiere'],
        $data['niveau'],
        $data['matiere'],
        $data['type_document']
    ];

    return executeQuery($sql, $params);
}

/**
 * Fonction pour récupérer les statistiques de la banque
 */
function getBankStatistics() {
    $stats = [];

    // Nombre total de documents
    $sql = "SELECT COUNT(*) FROM documents WHERE active = 1";
    $stmt = executeQuery($sql);
    $stats['total_documents'] = $stmt->fetchColumn();

    // Nombre total de téléchargements
    $sql = "SELECT SUM(downloads) FROM documents WHERE active = 1";
    $stmt = executeQuery($sql);
    $stats['total_downloads'] = $stmt->fetchColumn() ?: 0;

    // Documents les plus téléchargés
    $sql = "SELECT d.title, d.downloads, d.type_document
            FROM documents d
            WHERE d.active = 1
            ORDER BY d.downloads DESC
            LIMIT 5";
    $stmt = executeQuery($sql);
    $stats['top_downloads'] = $stmt->fetchAll();

    // Répartition par type de document
    $sql = "SELECT type_document, COUNT(*) as count
            FROM documents
            WHERE active = 1
            GROUP BY type_document
            ORDER BY count DESC";
    $stmt = executeQuery($sql);
    $stats['by_type'] = $stmt->fetchAll();

    return $stats;
}

/**
 * FONCTIONS POUR LE SYSTÈME DE RÉSULTATS
 */

/**
 * Fonction pour récupérer les résultats d'un utilisateur
 */
function getUserResults($userId, $semestreId = null) {
    $where_clause = "i.user_id = ?";
    $params = [$userId];

    if ($semestreId) {
        $where_clause .= " AND i.semestre_id = ?";
        $params[] = $semestreId;
    }

    $sql = "SELECT
                i.id as inscription_id,
                m.nom as matiere_nom,
                m.code as matiere_code,
                m.coefficient as matiere_coefficient,
                m.credits,
                s.nom as semestre_nom,
                s.annee_universitaire,
                s.id as semestre_id,
                moy.moyenne_matiere,
                moy.statut
            FROM inscriptions i
            JOIN matieres m ON i.matiere_id = m.id
            JOIN semestres s ON i.semestre_id = s.id
            LEFT JOIN moyennes moy ON i.id = moy.inscription_id
            WHERE $where_clause AND i.active = 1
            ORDER BY s.annee_universitaire DESC, s.nom, m.nom";

    $stmt = executeQuery($sql, $params);
    return $stmt->fetchAll();
}

/**
 * Fonction pour récupérer les notes détaillées d'une inscription
 */
function getNotesForInscription($inscriptionId) {
    $sql = "SELECT
                n.note,
                n.note_sur,
                n.date_evaluation,
                n.commentaire,
                n.validee,
                e.nom as evaluation_nom,
                e.type as evaluation_type,
                e.coefficient as evaluation_coefficient
            FROM notes n
            JOIN evaluations e ON n.evaluation_id = e.id
            WHERE n.inscription_id = ? AND n.validee = 1
            ORDER BY n.date_evaluation DESC";

    $stmt = executeQuery($sql, [$inscriptionId]);
    return $stmt->fetchAll();
}

/**
 * Fonction pour calculer la moyenne d'une matière
 */
function calculateMoyenneMatiere($inscriptionId) {
    $notes = getNotesForInscription($inscriptionId);

    if (empty($notes)) {
        return null;
    }

    $total_points = 0;
    $total_coefficients = 0;

    foreach ($notes as $note) {
        $note_sur_20 = ($note['note'] / $note['note_sur']) * 20;
        $coefficient = $note['evaluation_coefficient'];

        $total_points += $note_sur_20 * $coefficient;
        $total_coefficients += $coefficient;
    }

    return $total_coefficients > 0 ? round($total_points / $total_coefficients, 2) : null;
}

/**
 * Fonction pour récupérer les semestres d'un utilisateur
 */
function getUserSemestres($userId) {
    $sql = "SELECT DISTINCT
                s.id,
                s.nom,
                s.annee_universitaire,
                s.date_debut,
                s.date_fin,
                COUNT(i.id) as nb_matieres
            FROM semestres s
            JOIN inscriptions i ON s.id = i.semestre_id
            WHERE i.user_id = ? AND i.active = 1
            GROUP BY s.id, s.nom, s.annee_universitaire, s.date_debut, s.date_fin
            ORDER BY s.annee_universitaire DESC, s.nom";

    $stmt = executeQuery($sql, [$userId]);
    return $stmt->fetchAll();
}

/**
 * Fonction pour récupérer les statistiques de résultats d'un utilisateur
 */
function getUserResultsStats($userId) {
    $stats = [];

    // Moyenne générale
    $sql = "SELECT AVG(moy.moyenne_matiere) as moyenne_generale
            FROM moyennes moy
            JOIN inscriptions i ON moy.inscription_id = i.id
            WHERE i.user_id = ? AND moy.moyenne_matiere IS NOT NULL";
    $stmt = executeQuery($sql, [$userId]);
    $stats['moyenne_generale'] = round($stmt->fetchColumn() ?: 0, 2);

    // Nombre de matières
    $sql = "SELECT COUNT(*) FROM inscriptions WHERE user_id = ? AND active = 1";
    $stmt = executeQuery($sql, [$userId]);
    $stats['nb_matieres'] = $stmt->fetchColumn();

    // Nombre de matières validées (moyenne >= 10)
    $sql = "SELECT COUNT(*)
            FROM moyennes moy
            JOIN inscriptions i ON moy.inscription_id = i.id
            WHERE i.user_id = ? AND moy.moyenne_matiere >= 10";
    $stmt = executeQuery($sql, [$userId]);
    $stats['nb_matieres_validees'] = $stmt->fetchColumn();

    // Crédits obtenus
    $sql = "SELECT SUM(mat.credits)
            FROM moyennes moy
            JOIN inscriptions i ON moy.inscription_id = i.id
            JOIN matieres mat ON i.matiere_id = mat.id
            WHERE i.user_id = ? AND moy.moyenne_matiere >= 10";
    $stmt = executeQuery($sql, [$userId]);
    $stats['credits_obtenus'] = $stmt->fetchColumn() ?: 0;

    // Crédits totaux
    $sql = "SELECT SUM(mat.credits)
            FROM inscriptions i
            JOIN matieres mat ON i.matiere_id = mat.id
            WHERE i.user_id = ? AND i.active = 1";
    $stmt = executeQuery($sql, [$userId]);
    $stats['credits_totaux'] = $stmt->fetchColumn() ?: 0;

    return $stats;
}

/**
 * Fonction pour récupérer les matières disponibles par filière et niveau
 */
function getMatieresByFiliereNiveau($filiere, $niveau) {
    $sql = "SELECT * FROM matieres
            WHERE filiere = ? AND niveau = ? AND active = 1
            ORDER BY semestre_type, nom";
    $stmt = executeQuery($sql, [$filiere, $niveau]);
    return $stmt->fetchAll();
}

/**
 * Fonction pour ajouter une inscription
 */
function addInscription($userId, $semestreId, $matiereId) {
    $sql = "INSERT INTO inscriptions (user_id, semestre_id, matiere_id) VALUES (?, ?, ?)";
    return executeQuery($sql, [$userId, $semestreId, $matiereId]);
}

/**
 * Fonction pour ajouter une note
 */
function addNote($inscriptionId, $evaluationId, $note, $noteSur, $dateEvaluation, $commentaire = null) {
    $sql = "INSERT INTO notes (inscription_id, evaluation_id, note, note_sur, date_evaluation, commentaire, validee)
            VALUES (?, ?, ?, ?, ?, ?, 1)";
    return executeQuery($sql, [$inscriptionId, $evaluationId, $note, $noteSur, $dateEvaluation, $commentaire]);
}

/**
 * Fonction pour mettre à jour les moyennes
 */
function updateMoyennes($inscriptionId) {
    $moyenne = calculateMoyenneMatiere($inscriptionId);

    if ($moyenne !== null) {
        $statut = $moyenne >= 10 ? 'admis' : 'ajourne';

        $sql = "INSERT INTO moyennes (inscription_id, moyenne_matiere, statut)
                VALUES (?, ?, ?)
                ON DUPLICATE KEY UPDATE
                moyenne_matiere = VALUES(moyenne_matiere),
                statut = VALUES(statut)";

        return executeQuery($sql, [$inscriptionId, $moyenne, $statut]);
    }

    return false;
}

/**
 * FONCTIONS POUR LE SYSTÈME DE MESSAGERIE
 */

/**
 * Fonction pour récupérer les messages d'un utilisateur
 */
function getUserMessages($userId, $type = 'received', $limit = 20, $offset = 0) {
    $where_clause = "";
    $params = [$userId];

    switch ($type) {
        case 'received':
            $where_clause = "m.receiver_id = ? OR (m.receiver_id IS NULL AND m.is_public = 1)";
            break;
        case 'sent':
            $where_clause = "m.sender_id = ?";
            break;
        case 'public':
            $where_clause = "m.is_public = 1";
            $params = [];
            break;
        default:
            $where_clause = "(m.receiver_id = ? OR m.sender_id = ?) OR (m.receiver_id IS NULL AND m.is_public = 1)";
            $params = [$userId, $userId];
    }

    $sql = "SELECT
                m.*,
                sender.prenom as sender_prenom,
                sender.nom as sender_nom,
                sender.filiere as sender_filiere,
                sender.niveau as sender_niveau,
                receiver.prenom as receiver_prenom,
                receiver.nom as receiver_nom
            FROM messages m
            JOIN users sender ON m.sender_id = sender.id
            LEFT JOIN users receiver ON m.receiver_id = receiver.id
            WHERE $where_clause
            ORDER BY m.created_at DESC
            LIMIT ? OFFSET ?";

    $params[] = $limit;
    $params[] = $offset;

    $stmt = executeQuery($sql, $params);
    return $stmt->fetchAll();
}

/**
 * Fonction pour compter les messages non lus
 */
function getUnreadMessagesCount($userId) {
    $sql = "SELECT COUNT(*) FROM messages
            WHERE (receiver_id = ? OR (receiver_id IS NULL AND is_public = 1))
            AND is_read = 0";
    $stmt = executeQuery($sql, [$userId]);
    return $stmt->fetchColumn();
}

/**
 * Fonction pour envoyer un message
 */
function sendMessage($senderId, $receiverId, $subject, $message, $isPublic = false) {
    $sql = "INSERT INTO messages (sender_id, receiver_id, subject, message, is_public, created_at)
            VALUES (?, ?, ?, ?, ?, NOW())";

    $params = [
        $senderId,
        $isPublic ? null : $receiverId,
        $subject,
        $message,
        $isPublic ? 1 : 0
    ];

    return executeQuery($sql, $params);
}

/**
 * Fonction pour marquer un message comme lu
 */
function markMessageAsRead($messageId, $userId) {
    $sql = "UPDATE messages SET is_read = 1
            WHERE id = ? AND (receiver_id = ? OR (receiver_id IS NULL AND is_public = 1))";
    return executeQuery($sql, [$messageId, $userId]);
}

/**
 * Fonction pour récupérer un message par ID
 */
function getMessageById($messageId, $userId) {
    $sql = "SELECT
                m.*,
                sender.prenom as sender_prenom,
                sender.nom as sender_nom,
                sender.filiere as sender_filiere,
                sender.niveau as sender_niveau,
                receiver.prenom as receiver_prenom,
                receiver.nom as receiver_nom
            FROM messages m
            JOIN users sender ON m.sender_id = sender.id
            LEFT JOIN users receiver ON m.receiver_id = receiver.id
            WHERE m.id = ? AND (m.receiver_id = ? OR m.sender_id = ? OR (m.receiver_id IS NULL AND m.is_public = 1))";

    $stmt = executeQuery($sql, [$messageId, $userId, $userId]);
    return $stmt->fetch();
}

/**
 * Fonction pour supprimer un message
 */
function deleteMessage($messageId, $userId) {
    // Seul l'expéditeur peut supprimer son message
    $sql = "DELETE FROM messages WHERE id = ? AND sender_id = ?";
    return executeQuery($sql, [$messageId, $userId]);
}

/**
 * Fonction pour rechercher des utilisateurs
 */
function searchUsers($query, $currentUserId, $limit = 10) {
    $sql = "SELECT id, prenom, nom, filiere, niveau, email
            FROM users
            WHERE (prenom LIKE ? OR nom LIKE ? OR email LIKE ?)
            AND id != ? AND active = 1
            ORDER BY prenom, nom
            LIMIT ?";

    $searchTerm = '%' . $query . '%';
    $params = [$searchTerm, $searchTerm, $searchTerm, $currentUserId, $limit];

    $stmt = executeQuery($sql, $params);
    return $stmt->fetchAll();
}

/**
 * Fonction pour récupérer les statistiques de messagerie
 */
function getMessagingStats($userId) {
    $stats = [];

    // Messages reçus
    $sql = "SELECT COUNT(*) FROM messages WHERE receiver_id = ?";
    $stmt = executeQuery($sql, [$userId]);
    $stats['received'] = $stmt->fetchColumn();

    // Messages envoyés
    $sql = "SELECT COUNT(*) FROM messages WHERE sender_id = ?";
    $stmt = executeQuery($sql, [$userId]);
    $stats['sent'] = $stmt->fetchColumn();

    // Messages non lus
    $stats['unread'] = getUnreadMessagesCount($userId);

    // Messages publics
    $sql = "SELECT COUNT(*) FROM messages WHERE is_public = 1";
    $stmt = executeQuery($sql);
    $stats['public'] = $stmt->fetchColumn();

    return $stats;
}

/**
 * Fonction pour récupérer les conversations récentes
 */
function getRecentConversations($userId, $limit = 5) {
    $sql = "SELECT DISTINCT
                CASE
                    WHEN m.sender_id = ? THEN m.receiver_id
                    ELSE m.sender_id
                END as contact_id,
                CASE
                    WHEN m.sender_id = ? THEN CONCAT(receiver.prenom, ' ', receiver.nom)
                    ELSE CONCAT(sender.prenom, ' ', sender.nom)
                END as contact_name,
                CASE
                    WHEN m.sender_id = ? THEN receiver.filiere
                    ELSE sender.filiere
                END as contact_filiere,
                MAX(m.created_at) as last_message_date,
                COUNT(CASE WHEN m.receiver_id = ? AND m.is_read = 0 THEN 1 END) as unread_count
            FROM messages m
            JOIN users sender ON m.sender_id = sender.id
            LEFT JOIN users receiver ON m.receiver_id = receiver.id
            WHERE (m.sender_id = ? OR m.receiver_id = ?) AND m.is_public = 0
            GROUP BY contact_id, contact_name, contact_filiere
            ORDER BY last_message_date DESC
            LIMIT ?";

    $params = [$userId, $userId, $userId, $userId, $userId, $userId, $limit];
    $stmt = executeQuery($sql, $params);
    return $stmt->fetchAll();
}

/**
 * FONCTIONS POUR LE SYSTÈME DE RÔLES ET PERMISSIONS
 */

/**
 * Fonction pour vérifier si un utilisateur a un rôle spécifique
 */
function hasRole($userId, $requiredRole) {
    $user = getUserById($userId);
    if (!$user) {
        return false;
    }

    $userRole = isset($user['role']) ? $user['role'] : 'etudiant';

    // Hiérarchie des rôles
    $roleHierarchy = [
        'etudiant' => 1,
        'moderateur' => 2,
        'admin' => 3,
        'super_admin' => 4
    ];

    $userLevel = isset($roleHierarchy[$userRole]) ? $roleHierarchy[$userRole] : 1;
    $requiredLevel = isset($roleHierarchy[$requiredRole]) ? $roleHierarchy[$requiredRole] : 1;

    return $userLevel >= $requiredLevel;
}

/**
 * Fonction pour vérifier si un utilisateur a une permission spécifique
 */
function hasPermission($userId, $permission) {
    $user = getUserById($userId);
    if (!$user) {
        return false;
    }

    $userRole = isset($user['role']) ? $user['role'] : 'etudiant';

    // Définition des permissions par rôle
    $permissions = [
        'etudiant' => [
            'view_bank',
            'download_documents',
            'view_results',
            'use_messaging',
            'view_public_announcements'
        ],
        'moderateur' => [
            'view_bank',
            'download_documents',
            'view_results',
            'use_messaging',
            'view_public_announcements',
            'upload_documents',
            'access_dashboard',
            'create_public_announcements',
            'moderate_messages'
        ],
        'admin' => [
            'view_bank',
            'download_documents',
            'view_results',
            'use_messaging',
            'view_public_announcements',
            'upload_documents',
            'access_dashboard',
            'create_public_announcements',
            'moderate_messages',
            'manage_users',
            'view_advanced_stats',
            'manage_database',
            'delete_content'
        ],
        'super_admin' => [
            'view_bank',
            'download_documents',
            'view_results',
            'use_messaging',
            'view_public_announcements',
            'upload_documents',
            'access_dashboard',
            'create_public_announcements',
            'moderate_messages',
            'manage_users',
            'modify_roles',
            'view_advanced_stats',
            'manage_database',
            'delete_content',
            'manage_admins',
            'view_system_logs',
            'modify_config',
            'backup_restore',
            'root_access'
        ]
    ];

    $userPermissions = isset($permissions[$userRole]) ? $permissions[$userRole] : [];
    return in_array($permission, $userPermissions);
}

/**
 * Fonction pour obtenir le rôle d'un utilisateur
 */
function getUserRole($userId) {
    $user = getUserById($userId);
    return $user ? (isset($user['role']) ? $user['role'] : 'etudiant') : null;
}

/**
 * Fonction pour modifier le rôle d'un utilisateur
 */
function updateUserRole($userId, $newRole, $adminId) {
    // Vérifier que l'admin a les permissions
    if (!hasPermission($adminId, 'modify_roles')) {
        throw new Exception("Permissions insuffisantes pour modifier les rôles");
    }

    // Vérifier que le nouveau rôle est valide
    $validRoles = ['etudiant', 'moderateur', 'admin', 'super_admin'];
    if (!in_array($newRole, $validRoles)) {
        throw new Exception("Rôle invalide");
    }

    // Un admin ne peut pas créer un super_admin
    $adminRole = getUserRole($adminId);
    if ($newRole === 'super_admin' && $adminRole !== 'super_admin') {
        throw new Exception("Seul un super administrateur peut créer un autre super administrateur");
    }

    $sql = "UPDATE users SET role = ? WHERE id = ?";
    return executeQuery($sql, [$newRole, $userId]);
}

/**
 * Fonction pour obtenir tous les utilisateurs avec leurs rôles
 */
function getAllUsersWithRoles($limit = 50, $offset = 0) {
    $sql = "SELECT id, prenom, nom, email, filiere, niveau, role, active, created_at, last_login
            FROM users
            ORDER BY role DESC, prenom, nom
            LIMIT ? OFFSET ?";
    $stmt = executeQuery($sql, [$limit, $offset]);
    return $stmt->fetchAll();
}

/**
 * Fonction pour obtenir les statistiques des rôles
 */
function getRoleStatistics() {
    $sql = "SELECT
                role,
                COUNT(*) as count,
                COUNT(CASE WHEN active = 1 THEN 1 END) as active_count,
                COUNT(CASE WHEN last_login IS NOT NULL THEN 1 END) as logged_in_count
            FROM users
            GROUP BY role
            ORDER BY FIELD(role, 'etudiant', 'moderateur', 'admin', 'super_admin')";
    $stmt = executeQuery($sql);
    return $stmt->fetchAll();
}

/**
 * Fonction pour vérifier l'accès à une page
 */
function checkPageAccess($userId, $page) {
    $pagePermissions = [
        'dashboard.php' => 'access_dashboard',
        'upload_document.php' => 'upload_documents',
        'manage_roles.php' => 'modify_roles',
        'system_logs.php' => 'view_system_logs'
    ];

    $requiredPermission = isset($pagePermissions[$page]) ? $pagePermissions[$page] : null;

    if ($requiredPermission) {
        return hasPermission($userId, $requiredPermission);
    }

    // Si aucune permission spécifique n'est requise, autoriser l'accès
    return true;
}

/**
 * Fonction pour rediriger si l'accès est refusé
 */
function requirePermission($userId, $permission, $redirectUrl = 'index.php') {
    if (!hasPermission($userId, $permission)) {
        header("Location: $redirectUrl?error=access_denied");
        exit;
    }
}

/**
 * Fonction pour rediriger si le rôle est insuffisant
 */
function requireRole($userId, $requiredRole, $redirectUrl = 'index.php') {
    if (!hasRole($userId, $requiredRole)) {
        header("Location: $redirectUrl?error=insufficient_role");
        exit;
    }
}

/**
 * Script de création des tables (à exécuter une seule fois)
 * Décommentez et exécutez ce code pour créer les tables nécessaires
 */
/*
function createTables() {
    global $pdo;

    // Table des utilisateurs
    $sql_users = "
    CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nom VARCHAR(100) NOT NULL,
        prenom VARCHAR(100) NOT NULL,
        email VARCHAR(255) UNIQUE NOT NULL,
        numero_etudiant VARCHAR(20) UNIQUE NOT NULL,
        filiere VARCHAR(100) NOT NULL,
        niveau VARCHAR(10) NOT NULL,
        password_hash VARCHAR(255) NOT NULL,
        active TINYINT(1) DEFAULT 1,
        email_verified TINYINT(1) DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        last_login TIMESTAMP NULL,
        INDEX idx_email (email),
        INDEX idx_numero_etudiant (numero_etudiant),
        INDEX idx_filiere (filiere),
        INDEX idx_niveau (niveau)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ";

    // Table des sessions (pour "se souvenir de moi")
    $sql_sessions = "
    CREATE TABLE IF NOT EXISTS user_sessions (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        token VARCHAR(255) UNIQUE NOT NULL,
        expires_at TIMESTAMP NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        INDEX idx_token (token),
        INDEX idx_expires (expires_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ";

    // Table des documents (pour la banque d'épreuves)
    $sql_documents = "
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
        matiere VARCHAR(100),
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
        INDEX idx_active (active)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ";

    try {
        $pdo->exec($sql_users);
        $pdo->exec($sql_sessions);
        $pdo->exec($sql_documents);
        echo "Tables créées avec succès !";
    } catch (PDOException $e) {
        echo "Erreur lors de la création des tables : " . $e->getMessage();
    }
}

// Décommentez la ligne suivante pour créer les tables
// createTables();
*/
?>