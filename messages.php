<?php
require_once 'includes/auth_middleware.php';

// Vérification de l'authentification et des permissions
$user = checkAuth('use_messaging', 'etudiant');

// Logger l'accès à la messagerie
logAction($_SESSION['user_id'], 'access_messaging', 'Accès à la messagerie');

// Récupération des paramètres
$view = isset($_GET['view']) ? $_GET['view'] : 'inbox';
$message_id = isset($_GET['id']) ? $_GET['id'] : null;

// Récupération des données
$user_id = $_SESSION['user_id'];
$messaging_stats = getMessagingStats($user_id);
$recent_conversations = getRecentConversations($user_id);

// Récupération des messages selon la vue
switch ($view) {
    case 'sent':
        $messages = getUserMessages($user_id, 'sent');
        break;
    case 'public':
        $messages = getUserMessages($user_id, 'public');
        break;
    case 'inbox':
    default:
        $messages = getUserMessages($user_id, 'received');
        break;
}

// Si on affiche un message spécifique
$current_message = null;
if ($message_id) {
    $current_message = getMessageById($message_id, $user_id);
    if ($current_message && $current_message['receiver_id'] == $user_id && !$current_message['is_read']) {
        markMessageAsRead($message_id, $user_id);
    }
}

$page_title = "Messagerie";
include 'includes/header.php';
?>

<main class="main-content">
    <!-- En-tête de la messagerie -->
    <section style="background: linear-gradient(135deg, #ff9800 0%, #ffb74d 100%); color: white; padding: 3rem 0;">
        <div class="container">
            <div style="text-align: center; margin-bottom: 2rem;">
                <h1 style="font-size: 2.5rem; margin-bottom: 1rem;">
                    <i class="fas fa-comments"></i> Messagerie Étudiante
                </h1>
                <p style="font-size: 1.2rem; opacity: 0.9;">
                    Communiquez avec vos collègues et participez aux discussions
                </p>
            </div>

            <!-- Statistiques rapides -->
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem;">
                <div style="background: rgba(255,255,255,0.2); padding: 1.5rem; border-radius: 10px; text-align: center;">
                    <div style="font-size: 2rem; font-weight: bold; margin-bottom: 0.5rem;">
                        <?php echo $messaging_stats['received']; ?>
                    </div>
                    <div style="opacity: 0.9;">Messages reçus</div>
                </div>
                <div style="background: rgba(255,255,255,0.2); padding: 1.5rem; border-radius: 10px; text-align: center;">
                    <div style="font-size: 2rem; font-weight: bold; margin-bottom: 0.5rem;">
                        <?php echo $messaging_stats['sent']; ?>
                    </div>
                    <div style="opacity: 0.9;">Messages envoyés</div>
                </div>
                <div style="background: rgba(255,255,255,0.2); padding: 1.5rem; border-radius: 10px; text-align: center;">
                    <div style="font-size: 2rem; font-weight: bold; margin-bottom: 0.5rem;">
                        <?php echo $messaging_stats['unread']; ?>
                    </div>
                    <div style="opacity: 0.9;">Non lus</div>
                </div>
                <div style="background: rgba(255,255,255,0.2); padding: 1.5rem; border-radius: 10px; text-align: center;">
                    <div style="font-size: 2rem; font-weight: bold; margin-bottom: 0.5rem;">
                        <?php echo $messaging_stats['public']; ?>
                    </div>
                    <div style="opacity: 0.9;">Annonces publiques</div>
                </div>
            </div>
        </div>
    </section>

    <!-- Interface de messagerie -->
    <section style="padding: 2rem 0;">
        <div class="container">
            <div style="display: grid; grid-template-columns: 300px 1fr; gap: 2rem; min-height: 600px;">

                <!-- Sidebar -->
                <div style="background: white; border-radius: 10px; box-shadow: var(--shadow); overflow: hidden;">
                    <!-- Actions -->
                    <div style="padding: 1.5rem; border-bottom: 1px solid var(--border-color);">
                        <button onclick="showComposeModal()" class="btn btn-primary" style="width: 100%; margin-bottom: 1rem;">
                            <i class="fas fa-plus"></i> Nouveau message
                        </button>
                        <button onclick="showPublicMessageModal()" class="btn btn-primary" style="width: 100%; background-color: #2196f3;">
                            <i class="fas fa-bullhorn"></i> Annonce publique
                        </button>
                    </div>

                    <!-- Navigation -->
                    <div style="padding: 0;">
                        <a href="?view=inbox" class="sidebar-link <?php echo $view === 'inbox' ? 'active' : ''; ?>">
                            <i class="fas fa-inbox"></i>
                            Boîte de réception
                            <?php if ($messaging_stats['unread'] > 0): ?>
                                <span class="badge"><?php echo $messaging_stats['unread']; ?></span>
                            <?php endif; ?>
                        </a>
                        <a href="?view=sent" class="sidebar-link <?php echo $view === 'sent' ? 'active' : ''; ?>">
                            <i class="fas fa-paper-plane"></i> Messages envoyés
                        </a>
                        <a href="?view=public" class="sidebar-link <?php echo $view === 'public' ? 'active' : ''; ?>">
                            <i class="fas fa-bullhorn"></i> Annonces publiques
                        </a>
                    </div>

                    <!-- Conversations récentes -->
                    <?php if (!empty($recent_conversations) && $view === 'inbox'): ?>
                        <div style="padding: 1.5rem; border-top: 1px solid var(--border-color);">
                            <h4 style="color: var(--primary-color); margin-bottom: 1rem; font-size: 0.9rem; text-transform: uppercase;">
                                Conversations récentes
                            </h4>
                            <?php foreach ($recent_conversations as $conv): ?>
                                <div style="padding: 0.75rem; border-radius: 5px; margin-bottom: 0.5rem; cursor: pointer; transition: background 0.2s;"
                                     onmouseover="this.style.background='#f8f9fa'"
                                     onmouseout="this.style.background='transparent'"
                                     onclick="filterByContact(<?php echo $conv['contact_id']; ?>)">
                                    <div style="font-weight: 600; font-size: 0.9rem; color: var(--text-dark);">
                                        <?php echo htmlspecialchars($conv['contact_name']); ?>
                                    </div>
                                    <div style="font-size: 0.8rem; color: var(--text-light);">
                                        <?php echo htmlspecialchars($conv['contact_filiere']); ?>
                                    </div>
                                    <?php if ($conv['unread_count'] > 0): ?>
                                        <span style="background: #ff9800; color: white; padding: 0.2rem 0.5rem; border-radius: 10px; font-size: 0.7rem;">
                                            <?php echo $conv['unread_count']; ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Zone principale -->
                <div style="background: white; border-radius: 10px; box-shadow: var(--shadow); overflow: hidden;">
                    <?php if ($current_message): ?>
                        <!-- Affichage d'un message spécifique -->
                        <div style="padding: 1.5rem; border-bottom: 1px solid var(--border-color);">
                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                <button onclick="window.location.href='messages.php?view=<?php echo $view; ?>'" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left"></i> Retour
                                </button>
                                <div style="display: flex; gap: 0.5rem;">
                                    <?php if ($current_message['sender_id'] == $user_id): ?>
                                        <button onclick="deleteMessage(<?php echo $current_message['id']; ?>)" class="btn btn-danger">
                                            <i class="fas fa-trash"></i> Supprimer
                                        </button>
                                    <?php endif; ?>
                                    <?php if (!$current_message['is_public']): ?>
                                        <button onclick="replyToMessage(<?php echo $current_message['sender_id']; ?>, '<?php echo htmlspecialchars($current_message['subject']); ?>')" class="btn btn-primary">
                                            <i class="fas fa-reply"></i> Répondre
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <div style="padding: 2rem;">
                            <!-- En-tête du message -->
                            <div style="margin-bottom: 2rem; padding-bottom: 1rem; border-bottom: 1px solid var(--border-color);">
                                <h2 style="color: var(--text-dark); margin-bottom: 1rem;">
                                    <?php echo htmlspecialchars($current_message['subject']); ?>
                                </h2>
                                <div style="display: flex; justify-content: space-between; align-items: center; font-size: 0.9rem; color: var(--text-light);">
                                    <div>
                                        <strong>De :</strong>
                                        <?php echo htmlspecialchars($current_message['sender_prenom'] . ' ' . $current_message['sender_nom']); ?>
                                        <span style="background: #e3f2fd; color: #1565c0; padding: 0.2rem 0.5rem; border-radius: 10px; margin-left: 0.5rem;">
                                            <?php echo htmlspecialchars($current_message['sender_filiere']); ?>
                                        </span>
                                    </div>
                                    <div>
                                        <i class="fas fa-clock"></i>
                                        <?php echo date('d/m/Y à H:i', strtotime($current_message['created_at'])); ?>
                                    </div>
                                </div>
                                <?php if (!$current_message['is_public'] && $current_message['receiver_prenom']): ?>
                                    <div style="margin-top: 0.5rem; font-size: 0.9rem; color: var(--text-light);">
                                        <strong>À :</strong>
                                        <?php echo htmlspecialchars($current_message['receiver_prenom'] . ' ' . $current_message['receiver_nom']); ?>
                                    </div>
                                <?php endif; ?>
                                <?php if ($current_message['is_public']): ?>
                                    <div style="margin-top: 0.5rem;">
                                        <span style="background: #ff9800; color: white; padding: 0.3rem 0.75rem; border-radius: 15px; font-size: 0.8rem;">
                                            <i class="fas fa-bullhorn"></i> Annonce publique
                                        </span>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <!-- Contenu du message -->
                            <div style="line-height: 1.6; color: var(--text-dark);">
                                <?php echo nl2br(htmlspecialchars($current_message['message'])); ?>
                            </div>
                        </div>
                    <?php else: ?>
                        <!-- Liste des messages -->
                        <div style="padding: 1.5rem; border-bottom: 1px solid var(--border-color);">
                            <h2 style="color: var(--primary-color); margin: 0;">
                                <?php
                                switch ($view) {
                                    case 'sent':
                                        echo '<i class="fas fa-paper-plane"></i> Messages envoyés';
                                        break;
                                    case 'public':
                                        echo '<i class="fas fa-bullhorn"></i> Annonces publiques';
                                        break;
                                    default:
                                        echo '<i class="fas fa-inbox"></i> Boîte de réception';
                                }
                                ?>
                            </h2>
                        </div>

                        <div style="max-height: 500px; overflow-y: auto;">
                            <?php if (empty($messages)): ?>
                                <div style="text-align: center; padding: 3rem; color: var(--text-light);">
                                    <div style="font-size: 3rem; margin-bottom: 1rem;">
                                        <i class="fas fa-envelope-open"></i>
                                    </div>
                                    <h3>Aucun message</h3>
                                    <p>
                                        <?php
                                        switch ($view) {
                                            case 'sent':
                                                echo 'Vous n\'avez encore envoyé aucun message.';
                                                break;
                                            case 'public':
                                                echo 'Aucune annonce publique pour le moment.';
                                                break;
                                            default:
                                                echo 'Votre boîte de réception est vide.';
                                        }
                                        ?>
                                    </p>
                                </div>
                            <?php else: ?>
                                <?php foreach ($messages as $message): ?>
                                    <div class="message-item <?php echo !$message['is_read'] && $message['receiver_id'] == $user_id ? 'unread' : ''; ?>"
                                         onclick="window.location.href='?view=<?php echo $view; ?>&id=<?php echo $message['id']; ?>'"
                                         style="padding: 1rem 1.5rem; border-bottom: 1px solid var(--border-color); cursor: pointer; transition: background 0.2s;"
                                         onmouseover="this.style.background='#f8f9fa'"
                                         onmouseout="this.style.background='white'">

                                        <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 1rem;">
                                            <div style="flex: 1;">
                                                <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.5rem;">
                                                    <h4 style="margin: 0; color: var(--text-dark); font-size: 1rem;">
                                                        <?php echo htmlspecialchars($message['subject']); ?>
                                                    </h4>
                                                    <?php if (!$message['is_read'] && $message['receiver_id'] == $user_id): ?>
                                                        <span style="background: #ff9800; color: white; padding: 0.2rem 0.5rem; border-radius: 10px; font-size: 0.7rem;">
                                                            NOUVEAU
                                                        </span>
                                                    <?php endif; ?>
                                                    <?php if ($message['is_public']): ?>
                                                        <span style="background: #2196f3; color: white; padding: 0.2rem 0.5rem; border-radius: 10px; font-size: 0.7rem;">
                                                            <i class="fas fa-bullhorn"></i> PUBLIC
                                                        </span>
                                                    <?php endif; ?>
                                                </div>

                                                <div style="font-size: 0.9rem; color: var(--text-light); margin-bottom: 0.5rem;">
                                                    <?php if ($view === 'sent'): ?>
                                                        <strong>À :</strong>
                                                        <?php if ($message['is_public']): ?>
                                                            Tous les étudiants
                                                        <?php else: ?>
                                                            <?php echo htmlspecialchars($message['receiver_prenom'] . ' ' . $message['receiver_nom']); ?>
                                                        <?php endif; ?>
                                                    <?php else: ?>
                                                        <strong>De :</strong>
                                                        <?php echo htmlspecialchars($message['sender_prenom'] . ' ' . $message['sender_nom']); ?>
                                                        <span style="background: #e8f5e8; color: #2e7d32; padding: 0.1rem 0.4rem; border-radius: 8px; margin-left: 0.5rem; font-size: 0.8rem;">
                                                            <?php echo htmlspecialchars($message['sender_filiere']); ?>
                                                        </span>
                                                    <?php endif; ?>
                                                </div>

                                                <div style="color: var(--text-light); font-size: 0.9rem;">
                                                    <?php echo substr(strip_tags($message['message']), 0, 100) . (strlen($message['message']) > 100 ? '...' : ''); ?>
                                                </div>
                                            </div>

                                            <div style="text-align: right; font-size: 0.8rem; color: var(--text-light);">
                                                <?php echo date('d/m/Y', strtotime($message['created_at'])); ?><br>
                                                <?php echo date('H:i', strtotime($message['created_at'])); ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>
</main>

<!-- Modal pour composer un message -->
<div id="composeModal" class="modal" style="display: none;">
    <div class="modal-content" style="max-width: 600px;">
        <div class="modal-header">
            <h3><i class="fas fa-edit"></i> Nouveau message</h3>
            <span class="modal-close">&times;</span>
        </div>
        <div class="modal-body">
            <form id="composeForm">
                <div style="margin-bottom: 1rem;">
                    <label style="display: block; margin-bottom: 0.5rem; font-weight: 600;">Destinataire :</label>
                    <div style="position: relative;">
                        <input type="text" id="recipientSearch" placeholder="Rechercher un étudiant..."
                               style="width: 100%; padding: 0.75rem; border: 2px solid var(--border-color); border-radius: 5px;">
                        <div id="recipientSuggestions" style="position: absolute; top: 100%; left: 0; right: 0; background: white; border: 1px solid var(--border-color); border-top: none; border-radius: 0 0 5px 5px; max-height: 200px; overflow-y: auto; z-index: 1000; display: none;"></div>
                    </div>
                    <input type="hidden" id="recipientId" name="recipient_id">
                    <div id="selectedRecipient" style="margin-top: 0.5rem; display: none;"></div>
                </div>

                <div style="margin-bottom: 1rem;">
                    <label style="display: block; margin-bottom: 0.5rem; font-weight: 600;">Sujet :</label>
                    <input type="text" id="messageSubject" name="subject" required
                           style="width: 100%; padding: 0.75rem; border: 2px solid var(--border-color); border-radius: 5px;">
                </div>

                <div style="margin-bottom: 1rem;">
                    <label style="display: block; margin-bottom: 0.5rem; font-weight: 600;">Message :</label>
                    <textarea id="messageContent" name="message" rows="6" required
                              style="width: 100%; padding: 0.75rem; border: 2px solid var(--border-color); border-radius: 5px; resize: vertical;"></textarea>
                </div>

                <div style="display: flex; justify-content: flex-end; gap: 1rem;">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('composeModal')">Annuler</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-paper-plane"></i> Envoyer
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal pour message public -->
<div id="publicMessageModal" class="modal" style="display: none;">
    <div class="modal-content" style="max-width: 600px;">
        <div class="modal-header">
            <h3><i class="fas fa-bullhorn"></i> Nouvelle annonce publique</h3>
            <span class="modal-close">&times;</span>
        </div>
        <div class="modal-body">
            <div style="background: #fff3e0; padding: 1rem; border-radius: 5px; margin-bottom: 1rem; border-left: 4px solid #ff9800;">
                <strong>⚠️ Attention :</strong> Cette annonce sera visible par tous les étudiants de la plateforme.
            </div>

            <form id="publicMessageForm">
                <div style="margin-bottom: 1rem;">
                    <label style="display: block; margin-bottom: 0.5rem; font-weight: 600;">Sujet de l'annonce :</label>
                    <input type="text" id="publicSubject" name="subject" required
                           style="width: 100%; padding: 0.75rem; border: 2px solid var(--border-color); border-radius: 5px;">
                </div>

                <div style="margin-bottom: 1rem;">
                    <label style="display: block; margin-bottom: 0.5rem; font-weight: 600;">Contenu de l'annonce :</label>
                    <textarea id="publicContent" name="message" rows="6" required
                              style="width: 100%; padding: 0.75rem; border: 2px solid var(--border-color); border-radius: 5px; resize: vertical;"></textarea>
                </div>

                <div style="display: flex; justify-content: flex-end; gap: 1rem;">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('publicMessageModal')">Annuler</button>
                    <button type="submit" class="btn btn-primary" style="background-color: #ff9800;">
                        <i class="fas fa-bullhorn"></i> Publier l'annonce
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

<style>
.sidebar-link {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 1rem 1.5rem;
    color: var(--text-dark);
    text-decoration: none;
    border-bottom: 1px solid var(--border-color);
    transition: background 0.2s;
}

.sidebar-link:hover {
    background: #f8f9fa;
    color: var(--primary-color);
}

.sidebar-link.active {
    background: var(--primary-color);
    color: white;
}

.sidebar-link i {
    margin-right: 0.75rem;
    width: 20px;
}

.badge {
    background: #ff9800;
    color: white;
    padding: 0.2rem 0.5rem;
    border-radius: 10px;
    font-size: 0.7rem;
    font-weight: bold;
}

.message-item.unread {
    background: #f3f4f6 !important;
    border-left: 4px solid #ff9800;
}

.message-item.unread h4 {
    font-weight: bold;
}

.modal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 10000;
}

.modal-content {
    background: white;
    border-radius: 10px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
    width: 90%;
    max-height: 90vh;
    overflow-y: auto;
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1.5rem;
    border-bottom: 1px solid var(--border-color);
}

.modal-header h3 {
    margin: 0;
    color: var(--primary-color);
}

.modal-close {
    font-size: 1.5rem;
    cursor: pointer;
    color: var(--text-light);
    transition: color 0.2s;
}

.modal-close:hover {
    color: var(--text-dark);
}

.modal-body {
    padding: 1.5rem;
}

.recipient-suggestion {
    padding: 0.75rem;
    cursor: pointer;
    border-bottom: 1px solid #f0f0f0;
    transition: background 0.2s;
}

.recipient-suggestion:hover {
    background: #f8f9fa;
}

.recipient-suggestion:last-child {
    border-bottom: none;
}

@media (max-width: 768px) {
    .container > div {
        grid-template-columns: 1fr !important;
    }

    .sidebar-link {
        padding: 0.75rem 1rem;
    }

    .modal-content {
        width: 95%;
        margin: 1rem;
    }
}
</style>

<script>
// Variables globales
let recipientSearchTimeout;
let selectedRecipientId = null;

// Initialisation
document.addEventListener('DOMContentLoaded', function() {
    initializeModals();
    initializeRecipientSearch();
    initializeForms();
});

// Gestion des modals
function initializeModals() {
    const modals = document.querySelectorAll('.modal');
    const closeButtons = document.querySelectorAll('.modal-close');

    closeButtons.forEach(button => {
        button.addEventListener('click', function() {
            const modal = this.closest('.modal');
            closeModal(modal.id);
        });
    });

    window.addEventListener('click', function(e) {
        if (e.target.classList.contains('modal')) {
            closeModal(e.target.id);
        }
    });
}

function showComposeModal() {
    document.getElementById('composeModal').style.display = 'flex';
    document.getElementById('recipientSearch').focus();
}

function showPublicMessageModal() {
    document.getElementById('publicMessageModal').style.display = 'flex';
    document.getElementById('publicSubject').focus();
}

function closeModal(modalId) {
    document.getElementById(modalId).style.display = 'none';

    // Réinitialiser les formulaires
    if (modalId === 'composeModal') {
        document.getElementById('composeForm').reset();
        document.getElementById('recipientId').value = '';
        document.getElementById('selectedRecipient').style.display = 'none';
        document.getElementById('recipientSuggestions').style.display = 'none';
        selectedRecipientId = null;
    } else if (modalId === 'publicMessageModal') {
        document.getElementById('publicMessageForm').reset();
    }
}

// Recherche de destinataires
function initializeRecipientSearch() {
    const searchInput = document.getElementById('recipientSearch');
    const suggestionsDiv = document.getElementById('recipientSuggestions');

    searchInput.addEventListener('input', function() {
        const query = this.value.trim();

        clearTimeout(recipientSearchTimeout);

        if (query.length < 2) {
            suggestionsDiv.style.display = 'none';
            return;
        }

        recipientSearchTimeout = setTimeout(() => {
            searchUsers(query);
        }, 300);
    });

    // Cacher les suggestions quand on clique ailleurs
    document.addEventListener('click', function(e) {
        if (!e.target.closest('#recipientSearch') && !e.target.closest('#recipientSuggestions')) {
            suggestionsDiv.style.display = 'none';
        }
    });
}

function searchUsers(query) {
    fetch(`api/search_users.php?q=${encodeURIComponent(query)}`)
        .then(response => response.json())
        .then(data => {
            const suggestionsDiv = document.getElementById('recipientSuggestions');

            if (data.success && data.users.length > 0) {
                let html = '';
                data.users.forEach(user => {
                    html += `
                        <div class="recipient-suggestion" onclick="selectRecipient(${user.id}, '${user.prenom} ${user.nom}', '${user.filiere}')">
                            <div style="font-weight: 600;">${user.prenom} ${user.nom}</div>
                            <div style="font-size: 0.8rem; color: var(--text-light);">${user.filiere} - ${user.niveau}</div>
                        </div>
                    `;
                });
                suggestionsDiv.innerHTML = html;
                suggestionsDiv.style.display = 'block';
            } else {
                suggestionsDiv.innerHTML = '<div style="padding: 0.75rem; color: var(--text-light);">Aucun utilisateur trouvé</div>';
                suggestionsDiv.style.display = 'block';
            }
        })
        .catch(error => {
            console.error('Erreur lors de la recherche:', error);
        });
}

function selectRecipient(id, name, filiere) {
    selectedRecipientId = id;
    document.getElementById('recipientId').value = id;
    document.getElementById('recipientSearch').value = name;
    document.getElementById('recipientSuggestions').style.display = 'none';

    document.getElementById('selectedRecipient').innerHTML = `
        <div style="background: #e8f5e8; padding: 0.5rem; border-radius: 5px; display: flex; justify-content: space-between; align-items: center;">
            <span><strong>${name}</strong> - ${filiere}</span>
            <button type="button" onclick="clearRecipient()" style="background: none; border: none; color: #666; cursor: pointer;">
                <i class="fas fa-times"></i>
            </button>
        </div>
    `;
    document.getElementById('selectedRecipient').style.display = 'block';
}

function clearRecipient() {
    selectedRecipientId = null;
    document.getElementById('recipientId').value = '';
    document.getElementById('recipientSearch').value = '';
    document.getElementById('selectedRecipient').style.display = 'none';
}

// Gestion des formulaires
function initializeForms() {
    // Formulaire de message privé
    document.getElementById('composeForm').addEventListener('submit', function(e) {
        e.preventDefault();

        if (!selectedRecipientId) {
            showNotification('Veuillez sélectionner un destinataire', 'error');
            return;
        }

        const formData = new FormData();
        formData.append('recipient_id', selectedRecipientId);
        formData.append('subject', document.getElementById('messageSubject').value);
        formData.append('message', document.getElementById('messageContent').value);

        sendMessage(formData, false);
    });

    // Formulaire de message public
    document.getElementById('publicMessageForm').addEventListener('submit', function(e) {
        e.preventDefault();

        const formData = new FormData();
        formData.append('subject', document.getElementById('publicSubject').value);
        formData.append('message', document.getElementById('publicContent').value);
        formData.append('is_public', '1');

        sendMessage(formData, true);
    });
}

function sendMessage(formData, isPublic) {
    fetch('api/send_message.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Message envoyé avec succès !', 'success');
            closeModal(isPublic ? 'publicMessageModal' : 'composeModal');

            // Recharger la page après un court délai
            setTimeout(() => {
                window.location.reload();
            }, 1000);
        } else {
            showNotification(data.message || 'Erreur lors de l\'envoi du message', 'error');
        }
    })
    .catch(error => {
        console.error('Erreur:', error);
        showNotification('Erreur lors de l\'envoi du message', 'error');
    });
}

// Fonctions utilitaires
function replyToMessage(senderId, originalSubject) {
    showComposeModal();

    // Pré-remplir le destinataire et le sujet
    setTimeout(() => {
        // Rechercher l'utilisateur par ID pour le sélectionner
        fetch(`api/get_user.php?id=${senderId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    selectRecipient(data.user.id, `${data.user.prenom} ${data.user.nom}`, data.user.filiere);
                    document.getElementById('messageSubject').value = `Re: ${originalSubject}`;
                }
            });
    }, 100);
}

function deleteMessage(messageId) {
    if (confirm('Êtes-vous sûr de vouloir supprimer ce message ?')) {
        fetch('api/delete_message.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ message_id: messageId })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification('Message supprimé avec succès', 'success');
                window.location.href = 'messages.php';
            } else {
                showNotification(data.message || 'Erreur lors de la suppression', 'error');
            }
        })
        .catch(error => {
            console.error('Erreur:', error);
            showNotification('Erreur lors de la suppression', 'error');
        });
    }
}

function filterByContact(contactId) {
    // TODO: Implémenter le filtrage par contact
    showNotification('Fonctionnalité en cours de développement', 'info');
}

// Fonction de notification
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
        z-index: 10001;
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