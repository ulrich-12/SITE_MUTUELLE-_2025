<?php
require_once 'includes/auth_middleware.php';

// Vérification de l'authentification et des permissions
$user = checkAuth('view_results', 'etudiant');

// Logger l'accès aux résultats
logAction($_SESSION['user_id'], 'access_results', 'Accès aux résultats');

// Récupération du semestre sélectionné
$selected_semestre = isset($_GET['semestre']) ? $_GET['semestre'] : null;

// Récupération des données de l'utilisateur
$user_id = $_SESSION['user_id'];
$user_semestres = getUserSemestres($user_id);
$user_results = getUserResults($user_id, $selected_semestre);
$user_stats = getUserResultsStats($user_id);

$page_title = "Mes Résultats";
include 'includes/header.php';
?>

<main class="main-content">
    <!-- En-tête des résultats -->
    <section style="background: linear-gradient(135deg, #1976d2 0%, #42a5f5 100%); color: white; padding: 3rem 0;">
        <div class="container">
            <div style="text-align: center; margin-bottom: 2rem;">
                <h1 style="font-size: 2.5rem; margin-bottom: 1rem;">
                    <i class="fas fa-chart-line"></i> Mes Résultats Académiques
                </h1>
                <p style="font-size: 1.2rem; opacity: 0.9;">
                    Consultez vos notes et suivez votre progression académique
                </p>
            </div>

            <!-- Statistiques rapides -->
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem;">
                <div style="background: rgba(255,255,255,0.2); padding: 1.5rem; border-radius: 10px; text-align: center;">
                    <div style="font-size: 2rem; font-weight: bold; margin-bottom: 0.5rem;">
                        <?php echo $user_stats['moyenne_generale']; ?>/20
                    </div>
                    <div style="opacity: 0.9;">Moyenne Générale</div>
                </div>
                <div style="background: rgba(255,255,255,0.2); padding: 1.5rem; border-radius: 10px; text-align: center;">
                    <div style="font-size: 2rem; font-weight: bold; margin-bottom: 0.5rem;">
                        <?php echo $user_stats['nb_matieres_validees']; ?>/<?php echo $user_stats['nb_matieres']; ?>
                    </div>
                    <div style="opacity: 0.9;">Matières Validées</div>
                </div>
                <div style="background: rgba(255,255,255,0.2); padding: 1.5rem; border-radius: 10px; text-align: center;">
                    <div style="font-size: 2rem; font-weight: bold; margin-bottom: 0.5rem;">
                        <?php echo $user_stats['credits_obtenus']; ?>/<?php echo $user_stats['credits_totaux']; ?>
                    </div>
                    <div style="opacity: 0.9;">Crédits Obtenus</div>
                </div>
                <div style="background: rgba(255,255,255,0.2); padding: 1.5rem; border-radius: 10px; text-align: center;">
                    <div style="font-size: 2rem; font-weight: bold; margin-bottom: 0.5rem;">
                        <?php echo round(($user_stats['credits_obtenus'] / max($user_stats['credits_totaux'], 1)) * 100); ?>%
                    </div>
                    <div style="opacity: 0.9;">Progression</div>
                </div>
            </div>
        </div>
    </section>

    <!-- Filtres et navigation -->
    <section style="background: #f8f9fa; padding: 2rem 0;">
        <div class="container">
            <div style="background: white; padding: 2rem; border-radius: 10px; box-shadow: var(--shadow);">
                <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
                    <div>
                        <h3 style="color: var(--primary-color); margin: 0 0 0.5rem 0;">
                            <i class="fas fa-filter"></i> Filtrer par semestre
                        </h3>
                        <p style="color: var(--text-light); margin: 0; font-size: 0.9rem;">
                            Sélectionnez un semestre pour voir les résultats détaillés
                        </p>
                    </div>

                    <div style="display: flex; gap: 1rem; align-items: center;">
                        <select onchange="filterBySemestre(this.value)" style="padding: 0.75rem; border: 2px solid var(--border-color); border-radius: 5px; font-size: 1rem;">
                            <option value="">Tous les semestres</option>
                            <?php foreach ($user_semestres as $semestre): ?>
                                <option value="<?php echo $semestre['id']; ?>"
                                        <?php echo $selected_semestre == $semestre['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($semestre['nom'] . ' - ' . $semestre['annee_universitaire']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>

                        <button onclick="exportResults()" class="btn btn-primary" style="background-color: #ff9800;">
                            <i class="fas fa-download"></i> Exporter PDF
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Résultats détaillés -->
    <section style="padding: 2rem 0;">
        <div class="container">
            <?php if (empty($user_results)): ?>
                <!-- Aucun résultat -->
                <div style="text-align: center; padding: 3rem; background: white; border-radius: 10px; box-shadow: var(--shadow);">
                    <div style="font-size: 4rem; color: var(--text-light); margin-bottom: 1rem;">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <h3 style="color: var(--text-dark); margin-bottom: 1rem;">Aucun résultat disponible</h3>
                    <p style="color: var(--text-light); margin-bottom: 2rem;">
                        <?php if ($selected_semestre): ?>
                            Aucun résultat trouvé pour le semestre sélectionné.
                        <?php else: ?>
                            Vos résultats ne sont pas encore disponibles ou vous n'êtes inscrit à aucune matière.
                        <?php endif; ?>
                    </p>
                    <a href="?semestre=" class="btn btn-primary">Voir tous les semestres</a>
                </div>
            <?php else: ?>
                <!-- Groupement par semestre -->
                <?php
                $results_by_semestre = [];
                foreach ($user_results as $result) {
                    $sem_key = $result['semestre_id'];
                    if (!isset($results_by_semestre[$sem_key])) {
                        $results_by_semestre[$sem_key] = [
                            'info' => $result,
                            'matieres' => []
                        ];
                    }
                    $results_by_semestre[$sem_key]['matieres'][] = $result;
                }
                ?>

                <?php foreach ($results_by_semestre as $semestre_data): ?>
                    <div style="background: white; border-radius: 10px; box-shadow: var(--shadow); margin-bottom: 2rem; overflow: hidden;">
                        <!-- En-tête du semestre -->
                        <div style="background: linear-gradient(135deg, var(--primary-color), var(--accent-color)); color: white; padding: 1.5rem;">
                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                <div>
                                    <h3 style="margin: 0; font-size: 1.3rem;">
                                        <i class="fas fa-calendar-alt"></i>
                                        <?php echo htmlspecialchars($semestre_data['info']['semestre_nom']); ?>
                                    </h3>
                                    <p style="margin: 0.5rem 0 0 0; opacity: 0.9;">
                                        Année universitaire : <?php echo htmlspecialchars($semestre_data['info']['annee_universitaire']); ?>
                                    </p>
                                </div>
                                <div style="text-align: right;">
                                    <?php
                                    $matieres_semestre = $semestre_data['matieres'];
                                    $moyennes_semestre = array_filter(array_column($matieres_semestre, 'moyenne_matiere'));
                                    $moyenne_semestre = !empty($moyennes_semestre) ? round(array_sum($moyennes_semestre) / count($moyennes_semestre), 2) : 0;
                                    ?>
                                    <div style="font-size: 1.5rem; font-weight: bold;">
                                        <?php echo $moyenne_semestre; ?>/20
                                    </div>
                                    <div style="opacity: 0.9; font-size: 0.9rem;">Moyenne du semestre</div>
                                </div>
                            </div>
                        </div>

                        <!-- Matières du semestre -->
                        <div style="padding: 0;">
                            <?php foreach ($semestre_data['matieres'] as $index => $matiere): ?>
                                <div style="padding: 1.5rem; border-bottom: 1px solid var(--border-color); <?php echo $index % 2 === 0 ? 'background: #f8f9fa;' : ''; ?>">
                                    <div style="display: grid; grid-template-columns: 2fr 1fr 1fr 1fr auto; gap: 1rem; align-items: center;">
                                        <!-- Informations matière -->
                                        <div>
                                            <h4 style="margin: 0 0 0.5rem 0; color: var(--text-dark);">
                                                <?php echo htmlspecialchars($matiere['matiere_nom']); ?>
                                            </h4>
                                            <div style="display: flex; gap: 1rem; font-size: 0.9rem; color: var(--text-light);">
                                                <span><i class="fas fa-code"></i> <?php echo htmlspecialchars($matiere['matiere_code']); ?></span>
                                                <span><i class="fas fa-weight-hanging"></i> Coeff. <?php echo $matiere['matiere_coefficient']; ?></span>
                                                <span><i class="fas fa-coins"></i> <?php echo $matiere['credits']; ?> crédits</span>
                                            </div>
                                        </div>

                                        <!-- Moyenne -->
                                        <div style="text-align: center;">
                                            <?php if ($matiere['moyenne_matiere']): ?>
                                                <div style="font-size: 1.5rem; font-weight: bold; color: <?php echo $matiere['moyenne_matiere'] >= 10 ? '#4caf50' : '#f44336'; ?>;">
                                                    <?php echo $matiere['moyenne_matiere']; ?>/20
                                                </div>
                                                <div style="font-size: 0.8rem; color: var(--text-light);">Moyenne</div>
                                            <?php else: ?>
                                                <div style="color: var(--text-light); font-style: italic;">
                                                    Pas de notes
                                                </div>
                                            <?php endif; ?>
                                        </div>

                                        <!-- Statut -->
                                        <div style="text-align: center;">
                                            <?php if ($matiere['statut']): ?>
                                                <span style="padding: 0.25rem 0.75rem; border-radius: 15px; font-size: 0.8rem; font-weight: bold;
                                                    <?php
                                                    switch($matiere['statut']) {
                                                        case 'admis':
                                                            echo 'background: #e8f5e8; color: #2e7d32;';
                                                            break;
                                                        case 'ajourne':
                                                            echo 'background: #ffebee; color: #d32f2f;';
                                                            break;
                                                        default:
                                                            echo 'background: #e3f2fd; color: #1565c0;';
                                                    }
                                                    ?>">
                                                    <?php
                                                    echo $matiere['statut'] === 'admis' ? 'VALIDÉ' :
                                                         ($matiere['statut'] === 'ajourne' ? 'AJOURNÉ' : 'EN COURS');
                                                    ?>
                                                </span>
                                            <?php else: ?>
                                                <span style="padding: 0.25rem 0.75rem; border-radius: 15px; font-size: 0.8rem; background: #f5f5f5; color: var(--text-light);">
                                                    EN COURS
                                                </span>
                                            <?php endif; ?>
                                        </div>

                                        <!-- Progression visuelle -->
                                        <div style="width: 60px;">
                                            <?php if ($matiere['moyenne_matiere']): ?>
                                                <div style="background: #e0e0e0; height: 8px; border-radius: 4px; overflow: hidden;">
                                                    <div style="background: <?php echo $matiere['moyenne_matiere'] >= 10 ? '#4caf50' : '#f44336'; ?>;
                                                                height: 100%; width: <?php echo min(100, ($matiere['moyenne_matiere'] / 20) * 100); ?>%;
                                                                transition: width 0.3s ease;"></div>
                                                </div>
                                            <?php endif; ?>
                                        </div>

                                        <!-- Actions -->
                                        <div>
                                            <button onclick="showDetailedNotes(<?php echo $matiere['inscription_id']; ?>)"
                                                    class="btn btn-secondary" style="padding: 0.5rem;">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </section>
</main>

<!-- Modal pour les notes détaillées -->
<div id="notesModal" class="modal" style="display: none;">
    <div class="modal-content" style="max-width: 800px;">
        <div class="modal-header">
            <h3><i class="fas fa-list-alt"></i> Notes détaillées</h3>
            <span class="modal-close">&times;</span>
        </div>
        <div class="modal-body" id="notesModalBody">
            <!-- Contenu chargé dynamiquement -->
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

<script>
// Fonction pour filtrer par semestre
function filterBySemestre(semestreId) {
    const url = new URL(window.location);
    if (semestreId) {
        url.searchParams.set('semestre', semestreId);
    } else {
        url.searchParams.delete('semestre');
    }
    window.location.href = url.toString();
}

// Fonction pour exporter en PDF
function exportResults() {
    // TODO: Implémenter l'export PDF
    showNotification('Fonctionnalité d\'export PDF en cours de développement', 'info');
}

// Fonction pour afficher les notes détaillées
function showDetailedNotes(inscriptionId) {
    const modal = document.getElementById('notesModal');
    const modalBody = document.getElementById('notesModalBody');

    // Afficher un loader
    modalBody.innerHTML = '<div style="text-align: center; padding: 2rem;"><i class="fas fa-spinner fa-spin" style="font-size: 2rem; color: var(--primary-color);"></i><br><br>Chargement des notes...</div>';
    modal.style.display = 'flex';

    // Charger les notes via AJAX
    fetch(`api/get_detailed_notes.php?inscription_id=${inscriptionId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                modalBody.innerHTML = data.html;
            } else {
                modalBody.innerHTML = '<div style="text-align: center; padding: 2rem; color: var(--text-light);">Aucune note disponible pour cette matière.</div>';
            }
        })
        .catch(error => {
            modalBody.innerHTML = '<div style="text-align: center; padding: 2rem; color: #f44336;">Erreur lors du chargement des notes.</div>';
        });
}

// Gestion des modals
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('notesModal');
    const closeButton = document.querySelector('.modal-close');

    closeButton.addEventListener('click', function() {
        modal.style.display = 'none';
    });

    window.addEventListener('click', function(e) {
        if (e.target === modal) {
            modal.style.display = 'none';
        }
    });
});

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