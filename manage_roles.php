<?php
require_once 'includes/auth_middleware.php';

// Vérification de l'authentification et des permissions - SEULS LES SUPER ADMINS
$user = checkAuth('modify_roles', 'super_admin');

// Logger l'accès à la gestion des rôles
logAction($_SESSION['user_id'], 'access_role_management', 'Accès à la gestion des rôles');

$message = '';
$messageType = '';

// Traitement des actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = isset($_POST['action']) ? $_POST['action'] : '';
    
    if ($action === 'update_role') {
        $targetUserId = intval(isset($_POST['user_id']) ? $_POST['user_id'] : 0);
        $newRole = isset($_POST['new_role']) ? $_POST['new_role'] : '';
        
        try {
            updateUserRole($targetUserId, $newRole, $_SESSION['user_id']);
            
            // Logger l'action
            $targetUser = getUserById($targetUserId);
            $targetName = $targetUser ? $targetUser['prenom'] . ' ' . $targetUser['nom'] : 'Utilisateur inconnu';
            logAction($_SESSION['user_id'], 'update_user_role', "Modification du rôle de $targetName vers $newRole");
            
            $message = "Rôle mis à jour avec succès !";
            $messageType = 'success';
        } catch (Exception $e) {
            $message = "Erreur : " . $e->getMessage();
            $messageType = 'error';
        }
    }
}

// Récupération des données
$users = getAllUsersWithRoles(100);
$roleStats = getRoleStatistics();

$page_title = "Gestion des Rôles";
include 'includes/header.php';
?>

<main class="main-content">
    <!-- En-tête -->
    <section style="background: linear-gradient(135deg, #4caf50 0%, #66bb6a 100%); color: white; padding: 3rem 0;">
        <div class="container">
            <div style="text-align: center;">
                <h1 style="font-size: 2.5rem; margin-bottom: 1rem;">
                    <i class="fas fa-users-cog"></i> Gestion des Rôles
                </h1>
                <p style="font-size: 1.2rem; opacity: 0.9;">
                    Administration des utilisateurs et de leurs permissions
                </p>
            </div>
        </div>
    </section>

    <!-- Messages -->
    <?php if ($message): ?>
        <section style="padding: 1rem 0;">
            <div class="container">
                <div class="alert alert-<?php echo $messageType; ?>" style="background: <?php echo $messageType === 'success' ? '#e8f5e8' : '#ffebee'; ?>; color: <?php echo $messageType === 'success' ? '#2e7d32' : '#d32f2f'; ?>; padding: 1rem; border-radius: 5px; border-left: 4px solid <?php echo $messageType === 'success' ? '#4caf50' : '#f44336'; ?>;">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            </div>
        </section>
    <?php endif; ?>

    <!-- Statistiques des rôles -->
    <section style="padding: 2rem 0; background: #f8f9fa;">
        <div class="container">
            <h2 style="color: var(--primary-color); margin-bottom: 2rem; text-align: center;">
                <i class="fas fa-chart-pie"></i> Répartition des Rôles
            </h2>
            
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem;">
                <?php 
                $roleConfig = [
                    'etudiant' => ['name' => 'Étudiants', 'color' => '#2196f3', 'icon' => 'fas fa-user-graduate'],
                    'moderateur' => ['name' => 'Modérateurs', 'color' => '#ff9800', 'icon' => 'fas fa-user-shield'],
                    'admin' => ['name' => 'Administrateurs', 'color' => '#4caf50', 'icon' => 'fas fa-user-cog'],
                    'super_admin' => ['name' => 'Super Admins', 'color' => '#f44336', 'icon' => 'fas fa-user-crown']
                ];
                
                foreach ($roleStats as $stat): 
                    $config = $roleConfig[$stat['role']] ?? ['name' => $stat['role'], 'color' => '#666', 'icon' => 'fas fa-user'];
                ?>
                    <div style="background: white; padding: 2rem; border-radius: 10px; text-align: center; box-shadow: var(--shadow); border-top: 4px solid <?php echo $config['color']; ?>;">
                        <div style="font-size: 2.5rem; color: <?php echo $config['color']; ?>; margin-bottom: 1rem;">
                            <i class="<?php echo $config['icon']; ?>"></i>
                        </div>
                        <div style="font-size: 2rem; font-weight: bold; color: <?php echo $config['color']; ?>; margin-bottom: 0.5rem;">
                            <?php echo $stat['count']; ?>
                        </div>
                        <div style="color: var(--text-dark); font-weight: 600; margin-bottom: 0.5rem;">
                            <?php echo $config['name']; ?>
                        </div>
                        <div style="font-size: 0.9rem; color: var(--text-light);">
                            <?php echo $stat['active_count']; ?> actifs
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Liste des utilisateurs -->
    <section style="padding: 2rem 0;">
        <div class="container">
            <h2 style="color: var(--primary-color); margin-bottom: 2rem;">
                <i class="fas fa-users"></i> Gestion des Utilisateurs
            </h2>
            
            <div style="background: white; border-radius: 10px; box-shadow: var(--shadow); overflow: hidden;">
                <div style="overflow-x: auto;">
                    <table style="width: 100%; border-collapse: collapse;">
                        <thead style="background: #f8f9fa;">
                            <tr>
                                <th style="padding: 1rem; text-align: left; border-bottom: 2px solid var(--border-color);">Utilisateur</th>
                                <th style="padding: 1rem; text-align: center; border-bottom: 2px solid var(--border-color);">Filière</th>
                                <th style="padding: 1rem; text-align: center; border-bottom: 2px solid var(--border-color);">Rôle Actuel</th>
                                <th style="padding: 1rem; text-align: center; border-bottom: 2px solid var(--border-color);">Statut</th>
                                <th style="padding: 1rem; text-align: center; border-bottom: 2px solid var(--border-color);">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $targetUser): ?>
                                <tr style="border-bottom: 1px solid var(--border-color);">
                                    <td style="padding: 1rem;">
                                        <div style="display: flex; align-items: center; gap: 1rem;">
                                            <div style="width: 40px; height: 40px; background: linear-gradient(135deg, #2196f3, #42a5f5); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: bold;">
                                                <?php echo strtoupper(substr($targetUser['prenom'], 0, 1) . substr($targetUser['nom'], 0, 1)); ?>
                                            </div>
                                            <div>
                                                <div style="font-weight: 600; color: var(--text-dark);">
                                                    <?php echo htmlspecialchars($targetUser['prenom'] . ' ' . $targetUser['nom']); ?>
                                                </div>
                                                <div style="font-size: 0.9rem; color: var(--text-light);">
                                                    <?php echo htmlspecialchars($targetUser['email']); ?>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td style="padding: 1rem; text-align: center;">
                                        <span style="background: #e3f2fd; color: #1565c0; padding: 0.25rem 0.5rem; border-radius: 10px; font-size: 0.8rem;">
                                            <?php echo htmlspecialchars($targetUser['filiere'] . ' ' . $targetUser['niveau']); ?>
                                        </span>
                                    </td>
                                    <td style="padding: 1rem; text-align: center;">
                                        <?php echo getRoleBadge($targetUser['role']); ?>
                                    </td>
                                    <td style="padding: 1rem; text-align: center;">
                                        <?php if ($targetUser['active']): ?>
                                            <span style="background: #e8f5e8; color: #2e7d32; padding: 0.25rem 0.5rem; border-radius: 10px; font-size: 0.8rem;">
                                                <i class="fas fa-check-circle"></i> Actif
                                            </span>
                                        <?php else: ?>
                                            <span style="background: #ffebee; color: #d32f2f; padding: 0.25rem 0.5rem; border-radius: 10px; font-size: 0.8rem;">
                                                <i class="fas fa-times-circle"></i> Inactif
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td style="padding: 1rem; text-align: center;">
                                        <?php if ($targetUser['id'] != $_SESSION['user_id']): ?>
                                            <button onclick="showRoleModal(<?php echo $targetUser['id']; ?>, '<?php echo htmlspecialchars($targetUser['prenom'] . ' ' . $targetUser['nom']); ?>', '<?php echo $targetUser['role']; ?>')" 
                                                    class="btn btn-primary" style="padding: 0.5rem 1rem; font-size: 0.9rem;">
                                                <i class="fas fa-edit"></i> Modifier
                                            </button>
                                        <?php else: ?>
                                            <span style="color: var(--text-light); font-style: italic;">Vous-même</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>
</main>

<!-- Modal de modification de rôle -->
<div id="roleModal" class="modal" style="display: none;">
    <div class="modal-content" style="max-width: 500px;">
        <div class="modal-header">
            <h3><i class="fas fa-user-edit"></i> Modifier le Rôle</h3>
            <span class="modal-close">&times;</span>
        </div>
        <div class="modal-body">
            <form method="POST">
                <input type="hidden" name="action" value="update_role">
                <input type="hidden" name="user_id" id="modalUserId">
                
                <div style="margin-bottom: 1.5rem;">
                    <label style="display: block; margin-bottom: 0.5rem; font-weight: 600;">Utilisateur :</label>
                    <div id="modalUserName" style="padding: 0.75rem; background: #f8f9fa; border-radius: 5px; color: var(--text-dark);"></div>
                </div>
                
                <div style="margin-bottom: 1.5rem;">
                    <label style="display: block; margin-bottom: 0.5rem; font-weight: 600;">Nouveau Rôle :</label>
                    <select name="new_role" id="modalNewRole" required style="width: 100%; padding: 0.75rem; border: 2px solid var(--border-color); border-radius: 5px;">
                        <option value="etudiant">Étudiant</option>
                        <option value="moderateur">Modérateur</option>
                        <option value="admin">Administrateur</option>
                        <?php if (hasRole($_SESSION['user_id'], 'super_admin')): ?>
                            <option value="super_admin">Super Administrateur</option>
                        <?php endif; ?>
                    </select>
                </div>
                
                <div style="background: #fff3e0; padding: 1rem; border-radius: 5px; margin-bottom: 1.5rem; border-left: 4px solid #ff9800;">
                    <strong>⚠️ Attention :</strong> Cette action modifiera les permissions de l'utilisateur de manière permanente.
                </div>
                
                <div style="display: flex; justify-content: flex-end; gap: 1rem;">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('roleModal')">Annuler</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Sauvegarder
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

<style>
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

@media (max-width: 768px) {
    table {
        font-size: 0.9rem;
    }
    
    .modal-content {
        width: 95%;
        margin: 1rem;
    }
}
</style>

<script>
function showRoleModal(userId, userName, currentRole) {
    document.getElementById('modalUserId').value = userId;
    document.getElementById('modalUserName').textContent = userName;
    document.getElementById('modalNewRole').value = currentRole;
    document.getElementById('roleModal').style.display = 'flex';
}

function closeModal(modalId) {
    document.getElementById(modalId).style.display = 'none';
}

// Gestion des modals
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('roleModal');
    const closeButton = document.querySelector('.modal-close');
    
    closeButton.addEventListener('click', function() {
        closeModal('roleModal');
    });
    
    window.addEventListener('click', function(e) {
        if (e.target === modal) {
            closeModal('roleModal');
        }
    });
});
</script>
