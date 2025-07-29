<?php
require_once 'includes/auth_middleware.php';

// Vérification de l'authentification et des permissions
$user = checkAuth('access_dashboard', 'moderateur');

// Logger l'accès au dashboard
logAction($_SESSION['user_id'], 'access_dashboard', 'Accès au tableau de bord');

$page_title = "Tableau de bord";
include 'includes/header.php';
?>

<main class="main-content">
    <div class="container" style="padding: 2rem 0;">
        <!-- En-tête du dashboard -->
        <div style="background: linear-gradient(135deg, var(--primary-color) 0%, var(--accent-color) 100%); color: white; padding: 2rem; border-radius: 10px; margin-bottom: 2rem;">
            <div style="display: grid; grid-template-columns: 1fr auto; gap: 2rem; align-items: center;">
                <div>
                    <h1><i class="fas fa-tachometer-alt"></i> Tableau de bord - Mutuelle UDM</h1>
                    <p style="font-size: 1.1rem; margin: 0.5rem 0;">Bienvenue, <strong><?php echo htmlspecialchars($_SESSION['user_name']); ?></strong> !</p>
                    <p><i class="fas fa-graduation-cap"></i> <?php echo htmlspecialchars($_SESSION['user_filiere']); ?> - <?php echo htmlspecialchars($_SESSION['user_niveau']); ?></p>
                    <p style="font-size: 0.9rem; opacity: 0.9; margin-top: 0.5rem;">
                        <i class="fas fa-calendar"></i> Membre depuis :
                        <?php echo date('d/m/Y'); ?> |
                        <i class="fas fa-clock"></i> Dernière connexion : <?php echo date('d/m/Y H:i'); ?>
                    </p>
                </div>
                <div style="text-align: center;">
                    <div style="background: rgba(255,255,255,0.2); padding: 1rem; border-radius: 10px;">
                        <i class="fas fa-users" style="font-size: 2rem; margin-bottom: 0.5rem;"></i>
                        <div style="font-size: 1.2rem; font-weight: bold;">Membre Actif</div>
                        <div style="font-size: 0.9rem;">Statut vérifié</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Actualités et annonces de la mutuelle -->
        <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 2rem; margin-bottom: 2rem;">
            <div style="background: white; padding: 1.5rem; border-radius: 10px; box-shadow: var(--shadow);">
                <h2 style="color: var(--primary-color); margin-bottom: 1rem;">
                    <i class="fas fa-bullhorn"></i> Actualités de la Mutuelle
                </h2>
                <div class="news-item" style="border-left: 4px solid var(--primary-color); padding-left: 1rem; margin-bottom: 1rem;">
                    <h4 style="color: var(--text-dark); margin-bottom: 0.5rem;">📚 Nouvelle collection d'examens disponible</h4>
                    <p style="color: var(--text-light); font-size: 0.9rem; margin-bottom: 0.5rem;">
                        Plus de 150 nouveaux sujets d'examens ont été ajoutés à la banque d'épreuves pour les filières Informatique et Gestion.
                    </p>
                    <small style="color: var(--text-light);">
                        <i class="fas fa-clock"></i> Il y a 2 jours
                    </small>
                </div>
                <div class="news-item" style="border-left: 4px solid var(--accent-color); padding-left: 1rem; margin-bottom: 1rem;">
                    <h4 style="color: var(--text-dark); margin-bottom: 0.5rem;">🎓 Séance de tutorat collectif</h4>
                    <p style="color: var(--text-light); font-size: 0.9rem; margin-bottom: 0.5rem;">
                        Séance de révision en Mathématiques pour les L1/L2 - Samedi 27 juillet à 14h en amphithéâtre A.
                    </p>
                    <small style="color: var(--text-light);">
                        <i class="fas fa-clock"></i> Il y a 1 semaine
                    </small>
                </div>
                <div class="news-item" style="border-left: 4px solid #ff9800; padding-left: 1rem;">
                    <h4 style="color: var(--text-dark); margin-bottom: 0.5rem;">💡 Nouveau service : Mentorat étudiant</h4>
                    <p style="color: var(--text-light); font-size: 0.9rem; margin-bottom: 0.5rem;">
                        Programme de mentorat entre étudiants seniors et juniors. Inscriptions ouvertes !
                    </p>
                    <small style="color: var(--text-light);">
                        <i class="fas fa-clock"></i> Il y a 2 semaines
                    </small>
                </div>
            </div>

            <div>
                <!-- Statistiques de la mutuelle -->
                <div style="background: white; padding: 1.5rem; border-radius: 10px; box-shadow: var(--shadow); margin-bottom: 1.5rem;">
                    <h3 style="color: var(--primary-color); margin-bottom: 1rem;">
                        <i class="fas fa-chart-pie"></i> Statistiques Mutuelle
                    </h3>
                    <div style="display: flex; flex-direction: column; gap: 1rem;">
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <span style="color: var(--text-dark);">Membres actifs</span>
                            <strong style="color: var(--primary-color);">847</strong>
                        </div>
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <span style="color: var(--text-dark);">Documents partagés</span>
                            <strong style="color: var(--primary-color);">2,341</strong>
                        </div>
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <span style="color: var(--text-dark);">Téléchargements ce mois</span>
                            <strong style="color: var(--primary-color);">15,672</strong>
                        </div>
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <span style="color: var(--text-dark);">Messages échangés</span>
                            <strong style="color: var(--primary-color);">8,934</strong>
                        </div>
                    </div>
                </div>

                <!-- Événements à venir -->
                <div style="background: white; padding: 1.5rem; border-radius: 10px; box-shadow: var(--shadow);">
                    <h3 style="color: var(--primary-color); margin-bottom: 1rem;">
                        <i class="fas fa-calendar-alt"></i> Événements à venir
                    </h3>
                    <div style="display: flex; flex-direction: column; gap: 0.8rem;">
                        <div style="border-left: 3px solid var(--primary-color); padding-left: 0.8rem;">
                            <div style="font-weight: 600; color: var(--text-dark);">Assemblée Générale</div>
                            <div style="font-size: 0.8rem; color: var(--text-light);">30 juillet - 16h00</div>
                        </div>
                        <div style="border-left: 3px solid var(--accent-color); padding-left: 0.8rem;">
                            <div style="font-weight: 600; color: var(--text-dark);">Atelier CV/Lettre</div>
                            <div style="font-size: 0.8rem; color: var(--text-light);">5 août - 14h00</div>
                        </div>
                        <div style="border-left: 3px solid #ff9800; padding-left: 0.8rem;">
                            <div style="font-weight: 600; color: var(--text-dark);">Journée Portes Ouvertes</div>
                            <div style="font-size: 0.8rem; color: var(--text-light);">15 août - 9h00</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Mes statistiques personnelles -->
        <div style="background: white; padding: 1.5rem; border-radius: 10px; box-shadow: var(--shadow); margin-bottom: 2rem;">
            <h2 style="color: var(--primary-color); margin-bottom: 1.5rem;">
                <i class="fas fa-user-chart"></i> Mon activité dans la mutuelle
            </h2>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem;">
                <div class="stat-item" style="background: linear-gradient(135deg, #4caf50, #81c784);">
                    <div class="stat-number" style="color: white;">12</div>
                    <div class="stat-label" style="color: white;">Documents téléchargés</div>
                </div>
                <div class="stat-item" style="background: linear-gradient(135deg, #2196f3, #64b5f6);">
                    <div class="stat-number" style="color: white;">3</div>
                    <div class="stat-label" style="color: white;">Documents partagés</div>
                </div>
                <div class="stat-item" style="background: linear-gradient(135deg, #ff9800, #ffb74d);">
                    <div class="stat-number" style="color: white;">8</div>
                    <div class="stat-label" style="color: white;">Messages envoyés</div>
                </div>
                <div class="stat-item" style="background: linear-gradient(135deg, #9c27b0, #ba68c8);">
                    <div class="stat-number" style="color: white;">156</div>
                    <div class="stat-label" style="color: white;">Points de contribution</div>
                </div>
            </div>
        </div>

        <!-- Mission et valeurs de la mutuelle -->
        <div style="background: white; padding: 2rem; border-radius: 10px; box-shadow: var(--shadow); margin-bottom: 2rem;">
            <h2 style="color: var(--primary-color); margin-bottom: 1.5rem; text-align: center;">
                <i class="fas fa-heart"></i> Notre Mission - Mutuelle des Étudiants UDM
            </h2>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 2rem;">
                <div style="text-align: center;">
                    <div style="background: linear-gradient(135deg, var(--primary-color), var(--accent-color)); color: white; width: 80px; height: 80px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem; font-size: 2rem;">
                        <i class="fas fa-hands-helping"></i>
                    </div>
                    <h3 style="color: var(--primary-color); margin-bottom: 0.5rem;">Entraide Solidaire</h3>
                    <p style="color: var(--text-light);">Nous croyons en la force de l'entraide entre étudiants pour surmonter les défis académiques ensemble.</p>
                </div>
                <div style="text-align: center;">
                    <div style="background: linear-gradient(135deg, #2196f3, #64b5f6); color: white; width: 80px; height: 80px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem; font-size: 2rem;">
                        <i class="fas fa-share-alt"></i>
                    </div>
                    <h3 style="color: var(--primary-color); margin-bottom: 0.5rem;">Partage de Connaissances</h3>
                    <p style="color: var(--text-light);">Chaque étudiant peut contribuer et bénéficier des ressources partagées par la communauté.</p>
                </div>
                <div style="text-align: center;">
                    <div style="background: linear-gradient(135deg, #ff9800, #ffb74d); color: white; width: 80px; height: 80px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem; font-size: 2rem;">
                        <i class="fas fa-trophy"></i>
                    </div>
                    <h3 style="color: var(--primary-color); margin-bottom: 0.5rem;">Réussite Collective</h3>
                    <p style="color: var(--text-light);">Notre objectif est de contribuer à la réussite académique de tous les membres de l'UDM.</p>
                </div>
            </div>
        </div>

        <!-- Services disponibles -->
        <div style="margin-bottom: 2rem;">
            <h2 style="color: var(--primary-color); margin-bottom: 1.5rem; text-align: center;">
                <i class="fas fa-tools"></i> Services de la Mutuelle
            </h2>
            <div class="services-grid">
                <div class="service-card">
                    <div class="service-icon">
                        <i class="fas fa-book"></i>
                    </div>
                    <h3>Banque d'Épreuves</h3>
                    <p>Plus de <strong>2,341 documents</strong> : examens passés, cours, TD et TP partagés par nos membres.</p>
                    <div style="margin: 1rem 0; font-size: 0.9rem; color: var(--text-light);">
                        <i class="fas fa-download"></i> 15,672 téléchargements ce mois
                    </div>
                    <a href="bank.php" class="btn btn-primary">Accéder</a>
                </div>

                <div class="service-card">
                    <div class="service-icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <h3>Consultation Résultats</h3>
                    <p>Consultez vos résultats académiques de manière sécurisée et suivez votre progression.</p>
                    <div style="margin: 1rem 0; font-size: 0.9rem; color: var(--text-light);">
                        <i class="fas fa-shield-alt"></i> Accès sécurisé et confidentiel
                    </div>
                    <a href="results.php" class="btn btn-primary">Consulter</a>
                </div>

                <div class="service-card">
                    <div class="service-icon">
                        <i class="fas fa-comments"></i>
                    </div>
                    <h3>Messagerie Étudiante</h3>
                    <p>Communiquez avec vos collègues, créez des groupes d'étude et participez aux discussions.</p>
                    <div style="margin: 1rem 0; font-size: 0.9rem; color: var(--text-light);">
                        <i class="fas fa-users"></i> 8,934 messages échangés
                    </div>
                    <a href="messages.php" class="btn btn-primary">Messagerie</a>
                </div>

                <div class="service-card">
                    <div class="service-icon">
                        <i class="fas fa-envelope"></i>
                    </div>
                    <h3>Email Professionnel UDM</h3>
                    <p>Obtenez votre adresse email officielle @udm.ma pour vos communications académiques.</p>
                    <div style="margin: 1rem 0; font-size: 0.9rem; color: var(--text-light);">
                        <i class="fas fa-certificate"></i> Certification officielle UDM
                    </div>
                    <a href="#" class="btn btn-primary" onclick="generateEmail()">Générer</a>
                </div>

                <div class="service-card">
                    <div class="service-icon">
                        <i class="fas fa-upload"></i>
                    </div>
                    <h3>Contribuer à la Banque</h3>
                    <p>Partagez vos cours, examens et ressources pour aider la communauté étudiante.</p>
                    <div style="margin: 1rem 0; font-size: 0.9rem; color: var(--text-light);">
                        <i class="fas fa-gift"></i> Gagnez des points de contribution
                    </div>
                    <a href="#" class="btn btn-primary" onclick="showUploadModal()">Partager</a>
                </div>

                <div class="service-card">
                    <div class="service-icon">
                        <i class="fas fa-user-graduate"></i>
                    </div>
                    <h3>Mentorat Étudiant</h3>
                    <p>Bénéficiez de l'accompagnement d'étudiants seniors ou devenez mentor.</p>
                    <div style="margin: 1rem 0; font-size: 0.9rem; color: var(--text-light);">
                        <i class="fas fa-handshake"></i> Programme d'accompagnement
                    </div>
                    <a href="#" class="btn btn-primary" onclick="showMentorModal()">Participer</a>
                </div>
            </div>
        </div>

        <!-- Témoignages et impact -->
        <div style="background: linear-gradient(135deg, #f8f9fa, #e9ecef); padding: 2rem; border-radius: 10px; margin-bottom: 2rem;">
            <h2 style="color: var(--primary-color); margin-bottom: 1.5rem; text-align: center;">
                <i class="fas fa-quote-left"></i> Impact de la Mutuelle
            </h2>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 2rem;">
                <div style="background: white; padding: 1.5rem; border-radius: 10px; box-shadow: var(--shadow);">
                    <div style="color: var(--primary-color); font-size: 3rem; text-align: center; margin-bottom: 1rem;">92%</div>
                    <h4 style="text-align: center; color: var(--text-dark); margin-bottom: 0.5rem;">Taux de réussite</h4>
                    <p style="text-align: center; color: var(--text-light); font-size: 0.9rem;">
                        des membres actifs de la mutuelle réussissent leurs examens
                    </p>
                </div>
                <div style="background: white; padding: 1.5rem; border-radius: 10px; box-shadow: var(--shadow);">
                    <div style="color: var(--primary-color); font-size: 3rem; text-align: center; margin-bottom: 1rem;">4.8/5</div>
                    <h4 style="text-align: center; color: var(--text-dark); margin-bottom: 0.5rem;">Satisfaction</h4>
                    <p style="text-align: center; color: var(--text-light); font-size: 0.9rem;">
                        Note moyenne attribuée par nos membres
                    </p>
                </div>
                <div style="background: white; padding: 1.5rem; border-radius: 10px; box-shadow: var(--shadow);">
                    <div style="color: var(--primary-color); font-size: 3rem; text-align: center; margin-bottom: 1rem;">24h</div>
                    <h4 style="text-align: center; color: var(--text-dark); margin-bottom: 0.5rem;">Temps de réponse</h4>
                    <p style="text-align: center; color: var(--text-light); font-size: 0.9rem;">
                        moyen pour obtenir de l'aide via la messagerie
                    </p>
                </div>
            </div>
        </div>

        <!-- Activité récente et actions rapides -->
        <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 2rem; margin-bottom: 2rem;">
            <div style="background: white; padding: 1.5rem; border-radius: 10px; box-shadow: var(--shadow);">
                <h3 style="color: var(--primary-color); margin-bottom: 1rem;">
                    <i class="fas fa-history"></i> Activité récente dans la mutuelle
                </h3>
                <div style="display: flex; flex-direction: column; gap: 1rem;">
                    <div style="display: flex; align-items: center; gap: 1rem; padding: 0.8rem; background: #f8f9fa; border-radius: 5px;">
                        <div style="background: var(--primary-color); color: white; width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                            <i class="fas fa-download"></i>
                        </div>
                        <div style="flex: 1;">
                            <div style="font-weight: 600; color: var(--text-dark);">Nouveau document téléchargé</div>
                            <div style="font-size: 0.9rem; color: var(--text-light);">Examen Mathématiques L2 - 2023</div>
                            <div style="font-size: 0.8rem; color: var(--text-light);">Il y a 2 heures</div>
                        </div>
                    </div>
                    <div style="display: flex; align-items: center; gap: 1rem; padding: 0.8rem; background: #f8f9fa; border-radius: 5px;">
                        <div style="background: #2196f3; color: white; width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                            <i class="fas fa-message"></i>
                        </div>
                        <div style="flex: 1;">
                            <div style="font-weight: 600; color: var(--text-dark);">Message reçu de Ahmed M.</div>
                            <div style="font-size: 0.9rem; color: var(--text-light);">Question sur le cours de Programmation</div>
                            <div style="font-size: 0.8rem; color: var(--text-light);">Il y a 1 jour</div>
                        </div>
                    </div>
                    <div style="display: flex; align-items: center; gap: 1rem; padding: 0.8rem; background: #f8f9fa; border-radius: 5px;">
                        <div style="background: #4caf50; color: white; width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                            <i class="fas fa-upload"></i>
                        </div>
                        <div style="flex: 1;">
                            <div style="font-weight: 600; color: var(--text-dark);">Document partagé avec succès</div>
                            <div style="font-size: 0.9rem; color: var(--text-light);">TD Algorithmique - Série 3</div>
                            <div style="font-size: 0.8rem; color: var(--text-light);">Il y a 3 jours</div>
                        </div>
                    </div>
                </div>
            </div>

            <div style="background: white; padding: 1.5rem; border-radius: 10px; box-shadow: var(--shadow);">
                <h3 style="color: var(--primary-color); margin-bottom: 1rem;">
                    <i class="fas fa-bolt"></i> Actions rapides
                </h3>
                <div style="display: flex; flex-direction: column; gap: 1rem;">
                    <button class="btn btn-primary" onclick="showUploadModal()" style="width: 100%; justify-content: center;">
                        <i class="fas fa-plus"></i> Partager un document
                    </button>
                    <button class="btn btn-primary" onclick="window.location.href='messages.php'" style="width: 100%; justify-content: center; background-color: #2196f3;">
                        <i class="fas fa-envelope"></i> Envoyer un message
                    </button>
                    <button class="btn btn-primary" onclick="showProfileModal()" style="width: 100%; justify-content: center; background-color: #ff9800;">
                        <i class="fas fa-user-edit"></i> Modifier mon profil
                    </button>
                    <button class="btn btn-primary" onclick="showHelpModal()" style="width: 100%; justify-content: center; background-color: #9c27b0;">
                        <i class="fas fa-question-circle"></i> Aide & Support
                    </button>
                </div>

                <div style="margin-top: 1.5rem; padding-top: 1rem; border-top: 1px solid var(--border-color);">
                    <a href="?logout=1" class="btn btn-secondary" onclick="return confirm('Êtes-vous sûr de vouloir vous déconnecter ?')" style="width: 100%; justify-content: center;">
                        <i class="fas fa-sign-out-alt"></i> Se déconnecter
                    </a>
                </div>
            </div>
        </div>

        <!-- Contact et support -->
        <div style="background: linear-gradient(135deg, var(--primary-color), var(--accent-color)); color: white; padding: 2rem; border-radius: 10px; text-align: center;">
            <h3 style="margin-bottom: 1rem;"><i class="fas fa-headset"></i> Besoin d'aide ?</h3>
            <p style="margin-bottom: 1.5rem; opacity: 0.9;">
                Notre équipe de bénévoles est là pour vous accompagner dans votre parcours académique.
            </p>
            <div style="display: flex; justify-content: center; gap: 1rem; flex-wrap: wrap;">
                <a href="mailto:support@mutuelle-udm.ma" style="color: white; text-decoration: none; background: rgba(255,255,255,0.2); padding: 0.5rem 1rem; border-radius: 5px;">
                    <i class="fas fa-envelope"></i> support@mutuelle-udm.ma
                </a>
                <a href="tel:+212528123456" style="color: white; text-decoration: none; background: rgba(255,255,255,0.2); padding: 0.5rem 1rem; border-radius: 5px;">
                    <i class="fas fa-phone"></i> +212 528 12 34 56
                </a>
                <span style="color: white; background: rgba(255,255,255,0.2); padding: 0.5rem 1rem; border-radius: 5px;">
                    <i class="fas fa-clock"></i> Lun-Ven 8h-18h
                </span>
            </div>
        </div>
    </div>
</main>

<!-- Modal pour génération d'email -->
<div id="emailModal" class="modal" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h3><i class="fas fa-envelope"></i> Générer Email UDM</h3>
            <span class="modal-close">&times;</span>
        </div>
        <div class="modal-body">
            <p>Votre adresse email UDM sera générée automatiquement :</p>
            <div style="background-color: #f8f9fa; padding: 1rem; border-radius: 5px; margin: 1rem 0;">
                <strong id="generatedEmail"></strong>
            </div>
            <p><small>Cette adresse sera activée dans les 24 heures.</small></p>
            <button class="btn btn-primary" onclick="confirmEmailGeneration()">
                <i class="fas fa-check"></i> Confirmer la génération
            </button>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

<script>
// Génération d'email
function generateEmail() {
    const userEmail = '<?php echo $_SESSION['user_email']; ?>';
    const userName = '<?php echo strtolower(str_replace(' ', '.', $_SESSION['user_name'])); ?>';
    const generatedEmail = userName + '@udm.ma';

    document.getElementById('generatedEmail').textContent = generatedEmail;
    document.getElementById('emailModal').style.display = 'flex';
}

function confirmEmailGeneration() {
    // TODO: Appel API pour générer l'email
    showNotification('Demande de génération d\'email envoyée !', 'success');
    document.getElementById('emailModal').style.display = 'none';
}

// Modal upload
function showUploadModal() {
    // TODO: Implémenter modal d'upload
    showNotification('Fonctionnalité en cours de développement', 'info');
}

// Modal profil
function showProfileModal() {
    showNotification('Redirection vers la page de profil...', 'info');
    // TODO: Rediriger vers profile.php
}

// Modal mentorat
function showMentorModal() {
    showNotification('Programme de mentorat - Inscriptions bientôt ouvertes !', 'info');
}

// Modal aide
function showHelpModal() {
    const helpContent = `
        <div style="text-align: left;">
            <h4 style="color: var(--primary-color); margin-bottom: 1rem;">Comment utiliser la mutuelle ?</h4>
            <ul style="margin-left: 1rem; color: var(--text-dark);">
                <li style="margin-bottom: 0.5rem;"><strong>Banque d'épreuves :</strong> Recherchez et téléchargez des documents par filière</li>
                <li style="margin-bottom: 0.5rem;"><strong>Partage :</strong> Contribuez en uploadant vos propres ressources</li>
                <li style="margin-bottom: 0.5rem;"><strong>Messagerie :</strong> Communiquez avec d'autres étudiants</li>
                <li style="margin-bottom: 0.5rem;"><strong>Points :</strong> Gagnez des points en partageant du contenu</li>
            </ul>
            <div style="margin-top: 1rem; padding: 1rem; background: #f8f9fa; border-radius: 5px;">
                <strong>Besoin d'aide personnalisée ?</strong><br>
                Contactez-nous à support@mutuelle-udm.ma
            </div>
        </div>
    `;

    // Créer une modal temporaire pour l'aide
    const modal = document.createElement('div');
    modal.className = 'modal';
    modal.style.display = 'flex';
    modal.innerHTML = `
        <div class="modal-content">
            <div class="modal-header">
                <h3><i class="fas fa-question-circle"></i> Aide & Support</h3>
                <span class="modal-close">&times;</span>
            </div>
            <div class="modal-body">
                ${helpContent}
            </div>
        </div>
    `;

    document.body.appendChild(modal);

    // Gestion de la fermeture
    modal.querySelector('.modal-close').addEventListener('click', () => {
        document.body.removeChild(modal);
    });

    modal.addEventListener('click', (e) => {
        if (e.target === modal) {
            document.body.removeChild(modal);
        }
    });
}

// Gestion des modals
document.addEventListener('DOMContentLoaded', function() {
    const modals = document.querySelectorAll('.modal');
    const closeButtons = document.querySelectorAll('.modal-close');

    closeButtons.forEach(button => {
        button.addEventListener('click', function() {
            this.closest('.modal').style.display = 'none';
        });
    });

    window.addEventListener('click', function(e) {
        modals.forEach(modal => {
            if (e.target === modal) {
                modal.style.display = 'none';
            }
        });
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