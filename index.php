<?php
$page_title = "Accueil";
include 'includes/header.php';
?>

<main class="main-content">
    <!-- Section Hero -->
    <section class="hero">
        <div class="hero-container">
            <div class="hero-content">
                <h1 class="hero-title">Bienvenue à la Mutuelle des Étudiants UDM</h1>
                <p class="hero-subtitle">Votre plateforme dédiée à la réussite académique et à l'entraide étudiante</p>
                <div class="hero-buttons">
                    <a href="register.php" class="btn btn-primary">Rejoindre la mutuelle</a>
                    <a href="bank.php" class="btn btn-secondary">Explorer les ressources</a>
                    <a href="guide_demarrage.php" class="btn btn-outline">
                        <i class="fas fa-rocket"></i> Guide de démarrage
                    </a>
                </div>
            </div>
            <div class="hero-image">
                <!-- Image d'étudiants collaborant -->
                <div style="position: relative; width: 100%; height: 300px; background: linear-gradient(135deg, rgba(46, 125, 50, 0.1), rgba(129, 199, 132, 0.1)); border-radius: 15px; display: flex; align-items: center; justify-content: center; overflow: hidden;">
                    <!-- Placeholder pour image d'étudiants -->
                    <div style="background: url('https://images.unsplash.com/photo-1523240795612-9a054b0db644?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80') center/cover; width: 100%; height: 100%; border-radius: 15px; position: relative;">
                        <div style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; background: linear-gradient(135deg, rgba(46, 125, 50, 0.3), rgba(129, 199, 132, 0.2)); border-radius: 15px;"></div>
                        <div style="position: absolute; bottom: 20px; left: 20px; color: white; font-weight: bold; text-shadow: 0 2px 4px rgba(0,0,0,0.5);">
                            <i class="fas fa-users"></i> Communauté UDM
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Section Services -->
    <section class="services">
        <div class="container">
            <h2 class="section-title">Nos Services</h2>
            <div class="services-grid">
                <div class="service-card">
                    <!-- Image de fond pour la banque d'épreuves -->
                    <div style="height: 150px; background: url('https://images.unsplash.com/photo-1481627834876-b7833e8f5570?ixlib=rb-4.0.3&auto=format&fit=crop&w=400&q=80') center/cover; border-radius: 10px 10px 0 0; position: relative; margin: -2rem -2rem 1rem -2rem;">
                        <div style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; background: linear-gradient(135deg, rgba(46, 125, 50, 0.8), rgba(129, 199, 132, 0.6)); border-radius: 10px 10px 0 0; display: flex; align-items: center; justify-content: center;">
                            <i class="fas fa-book" style="color: white; font-size: 3rem;"></i>
                        </div>
                    </div>
                    <h3>Banque d'Épreuves</h3>
                    <p>Accédez à une vaste collection d'examens passés et de cours partagés par la communauté étudiante.</p>
                    <a href="bank.php" class="service-link">Découvrir <i class="fas fa-arrow-right"></i></a>
                </div>

                <div class="service-card">
                    <!-- Image de fond pour les résultats -->
                    <div style="height: 150px; background: url('https://images.unsplash.com/photo-1551288049-bebda4e38f71?ixlib=rb-4.0.3&auto=format&fit=crop&w=400&q=80') center/cover; border-radius: 10px 10px 0 0; position: relative; margin: -2rem -2rem 1rem -2rem;">
                        <div style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; background: linear-gradient(135deg, rgba(33, 150, 243, 0.8), rgba(100, 181, 246, 0.6)); border-radius: 10px 10px 0 0; display: flex; align-items: center; justify-content: center;">
                            <i class="fas fa-chart-line" style="color: white; font-size: 3rem;"></i>
                        </div>
                    </div>
                    <h3>Consultation des Résultats</h3>
                    <p>Consultez vos résultats académiques de manière sécurisée et suivez votre progression.</p>
                    <a href="results.php" class="service-link">Consulter <i class="fas fa-arrow-right"></i></a>
                </div>

                <div class="service-card">
                    <!-- Image de fond pour la messagerie -->
                    <div style="height: 150px; background: url('https://images.unsplash.com/photo-1516321318423-f06f85e504b3?ixlib=rb-4.0.3&auto=format&fit=crop&w=400&q=80') center/cover; border-radius: 10px 10px 0 0; position: relative; margin: -2rem -2rem 1rem -2rem;">
                        <div style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; background: linear-gradient(135deg, rgba(255, 152, 0, 0.8), rgba(255, 183, 77, 0.6)); border-radius: 10px 10px 0 0; display: flex; align-items: center; justify-content: center;">
                            <i class="fas fa-comments" style="color: white; font-size: 3rem;"></i>
                        </div>
                    </div>
                    <h3>Messagerie Étudiante</h3>
                    <p>Communiquez avec vos collègues, partagez des informations et créez des groupes d'étude.</p>
                    <a href="messages.php" class="service-link">Messagerie <i class="fas fa-arrow-right"></i></a>
                </div>

                <div class="service-card">
                    <!-- Image de fond pour l'email -->
                    <div style="height: 150px; background: url('https://images.unsplash.com/photo-1596526131083-e8c633c948d2?ixlib=rb-4.0.3&auto=format&fit=crop&w=400&q=80') center/cover; border-radius: 10px 10px 0 0; position: relative; margin: -2rem -2rem 1rem -2rem;">
                        <div style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; background: linear-gradient(135deg, rgba(156, 39, 176, 0.8), rgba(186, 104, 200, 0.6)); border-radius: 10px 10px 0 0; display: flex; align-items: center; justify-content: center;">
                            <i class="fas fa-envelope" style="color: white; font-size: 3rem;"></i>
                        </div>
                    </div>
                    <h3>Email Professionnel</h3>
                    <p>Obtenez votre adresse email professionnelle UDM pour vos communications académiques.</p>
                    <a href="dashboard.php" class="service-link">Générer <i class="fas fa-arrow-right"></i></a>
                </div>
            </div>
        </div>
    </section>

    <!-- Section À propos -->
    <section class="about">
        <div class="container">
            <div class="about-content">
                <div class="about-text">
                    <h2>À propos de la Mutuelle UDM</h2>
                    <p>La Mutuelle des Étudiants de l'Université Dakhla Maroc est une initiative étudiante visant à créer une communauté solidaire et collaborative.</p>

                    <!-- Images illustratives de la vie étudiante -->
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin: 2rem 0;">
                        <div style="position: relative; height: 120px; border-radius: 10px; overflow: hidden;">
                            <div style="background: url('https://images.unsplash.com/photo-1522202176988-66273c2fd55f?ixlib=rb-4.0.3&auto=format&fit=crop&w=400&q=80') center/cover; width: 100%; height: 100%;"></div>
                            <div style="position: absolute; bottom: 0; left: 0; right: 0; background: linear-gradient(transparent, rgba(0,0,0,0.7)); color: white; padding: 0.5rem; font-size: 0.9rem; font-weight: bold;">
                                <i class="fas fa-users"></i> Travail en équipe
                            </div>
                        </div>
                        <div style="position: relative; height: 120px; border-radius: 10px; overflow: hidden;">
                            <div style="background: url('https://images.unsplash.com/photo-1434030216411-0b793f4b4173?ixlib=rb-4.0.3&auto=format&fit=crop&w=400&q=80') center/cover; width: 100%; height: 100%;"></div>
                            <div style="position: absolute; bottom: 0; left: 0; right: 0; background: linear-gradient(transparent, rgba(0,0,0,0.7)); color: white; padding: 0.5rem; font-size: 0.9rem; font-weight: bold;">
                                <i class="fas fa-book-open"></i> Études partagées
                            </div>
                        </div>
                    </div>

                    <ul class="about-features">
                        <li><i class="fas fa-check"></i> Partage de ressources académiques</li>
                        <li><i class="fas fa-check"></i> Entraide entre étudiants</li>
                        <li><i class="fas fa-check"></i> Suivi personnalisé des résultats</li>
                        <li><i class="fas fa-check"></i> Communication facilitée</li>
                    </ul>
                </div>
                <div class="about-stats">
                    <div class="stat-item">
                        <div class="stat-number">847</div>
                        <div class="stat-label">Étudiants membres actifs</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-number">2,341</div>
                        <div class="stat-label">Documents partagés</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-number">92%</div>
                        <div class="stat-label">Taux de réussite</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Section Témoignages -->
    <section class="testimonials" style="padding: 4rem 0; background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);">
        <div class="container">
            <h2 class="section-title">Ce que disent nos membres</h2>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 2rem;">
                <div style="background: white; padding: 2rem; border-radius: 15px; box-shadow: 0 5px 25px rgba(0,0,0,0.1); text-align: center; position: relative; overflow: hidden;">
                    <!-- Décoration de fond -->
                    <div style="position: absolute; top: -50px; right: -50px; width: 100px; height: 100px; background: linear-gradient(135deg, var(--primary-color), var(--accent-color)); border-radius: 50%; opacity: 0.1;"></div>

                    <!-- Avatar -->
                    <div style="width: 80px; height: 80px; border-radius: 50%; background: url('https://images.unsplash.com/photo-1494790108755-2616b612b786?ixlib=rb-4.0.3&auto=format&fit=crop&w=150&q=80') center/cover; margin: 0 auto 1rem; border: 4px solid var(--primary-color);"></div>

                    <div style="color: var(--primary-color); font-size: 2rem; margin-bottom: 1rem;">
                        <i class="fas fa-quote-left"></i>
                    </div>
                    <p style="color: var(--text-dark); font-style: italic; margin-bottom: 1.5rem; line-height: 1.6;">
                        "Grâce à la mutuelle, j'ai pu accéder à tous les anciens examens de ma filière.
                        Cela m'a énormément aidé à réussir mes partiels !"
                    </p>
                    <div style="color: var(--primary-color); font-weight: bold; font-size: 1.1rem;">Fatima Z.</div>
                    <div style="color: var(--text-light); font-size: 0.9rem;">Étudiante en Informatique L3</div>
                    <div style="margin-top: 1rem;">
                        <span style="color: #ffc107;">★★★★★</span>
                    </div>
                </div>

                <div style="background: white; padding: 2rem; border-radius: 15px; box-shadow: 0 5px 25px rgba(0,0,0,0.1); text-align: center; position: relative; overflow: hidden;">
                    <!-- Décoration de fond -->
                    <div style="position: absolute; top: -50px; right: -50px; width: 100px; height: 100px; background: linear-gradient(135deg, #2196f3, #64b5f6); border-radius: 50%; opacity: 0.1;"></div>

                    <!-- Avatar -->
                    <div style="width: 80px; height: 80px; border-radius: 50%; background: url('https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?ixlib=rb-4.0.3&auto=format&fit=crop&w=150&q=80') center/cover; margin: 0 auto 1rem; border: 4px solid #2196f3;"></div>

                    <div style="color: #2196f3; font-size: 2rem; margin-bottom: 1rem;">
                        <i class="fas fa-quote-left"></i>
                    </div>
                    <p style="color: var(--text-dark); font-style: italic; margin-bottom: 1.5rem; line-height: 1.6;">
                        "L'entraide entre étudiants est formidable. J'ai trouvé un groupe d'étude
                        grâce à la messagerie et nous nous soutenons mutuellement."
                    </p>
                    <div style="color: #2196f3; font-weight: bold; font-size: 1.1rem;">Ahmed M.</div>
                    <div style="color: var(--text-light); font-size: 0.9rem;">Étudiant en Gestion M1</div>
                    <div style="margin-top: 1rem;">
                        <span style="color: #ffc107;">★★★★★</span>
                    </div>
                </div>

                <div style="background: white; padding: 2rem; border-radius: 15px; box-shadow: 0 5px 25px rgba(0,0,0,0.1); text-align: center; position: relative; overflow: hidden;">
                    <!-- Décoration de fond -->
                    <div style="position: absolute; top: -50px; right: -50px; width: 100px; height: 100px; background: linear-gradient(135deg, #ff9800, #ffb74d); border-radius: 50%; opacity: 0.1;"></div>

                    <!-- Avatar -->
                    <div style="width: 80px; height: 80px; border-radius: 50%; background: url('https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?ixlib=rb-4.0.3&auto=format&fit=crop&w=150&q=80') center/cover; margin: 0 auto 1rem; border: 4px solid #ff9800;"></div>

                    <div style="color: #ff9800; font-size: 2rem; margin-bottom: 1rem;">
                        <i class="fas fa-quote-left"></i>
                    </div>
                    <p style="color: var(--text-dark); font-style: italic; margin-bottom: 1.5rem; line-height: 1.6;">
                        "Partager mes cours m'a permis d'aider d'autres étudiants tout en
                        renforçant mes propres connaissances. C'est un cercle vertueux !"
                    </p>
                    <div style="color: #ff9800; font-weight: bold; font-size: 1.1rem;">Youssef K.</div>
                    <div style="color: var(--text-light); font-size: 0.9rem;">Étudiant en Économie L2</div>
                    <div style="margin-top: 1rem;">
                        <span style="color: #ffc107;">★★★★★</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Section Galerie Vie Universitaire -->
    <section style="padding: 4rem 0; background: white;">
        <div class="container">
            <h2 class="section-title">La Vie à l'UDM</h2>
            <p style="text-align: center; color: var(--text-light); margin-bottom: 3rem; font-size: 1.1rem;">
                Découvrez l'ambiance chaleureuse et collaborative de notre université
            </p>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1.5rem;">
                <!-- Image 1: Bibliothèque -->
                <div style="position: relative; height: 200px; border-radius: 15px; overflow: hidden; box-shadow: 0 5px 20px rgba(0,0,0,0.1); transition: transform 0.3s ease;" onmouseover="this.style.transform='translateY(-5px)'" onmouseout="this.style.transform='translateY(0)'">
                    <div style="background: url('https://images.unsplash.com/photo-1481627834876-b7833e8f5570?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80') center/cover; width: 100%; height: 100%;"></div>
                    <div style="position: absolute; bottom: 0; left: 0; right: 0; background: linear-gradient(transparent, rgba(0,0,0,0.8)); color: white; padding: 1rem;">
                        <h4 style="margin: 0; font-size: 1.1rem;"><i class="fas fa-book"></i> Bibliothèque Moderne</h4>
                        <p style="margin: 0.5rem 0 0 0; font-size: 0.9rem; opacity: 0.9;">Espace d'étude calme et équipé</p>
                    </div>
                </div>

                <!-- Image 2: Amphithéâtre -->
                <div style="position: relative; height: 200px; border-radius: 15px; overflow: hidden; box-shadow: 0 5px 20px rgba(0,0,0,0.1); transition: transform 0.3s ease;" onmouseover="this.style.transform='translateY(-5px)'" onmouseout="this.style.transform='translateY(0)'">
                    <div style="background: url('https://images.unsplash.com/photo-1523050854058-8df90110c9f1?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80') center/cover; width: 100%; height: 100%;"></div>
                    <div style="position: absolute; bottom: 0; left: 0; right: 0; background: linear-gradient(transparent, rgba(0,0,0,0.8)); color: white; padding: 1rem;">
                        <h4 style="margin: 0; font-size: 1.1rem;"><i class="fas fa-chalkboard-teacher"></i> Amphithéâtres</h4>
                        <p style="margin: 0.5rem 0 0 0; font-size: 0.9rem; opacity: 0.9;">Cours magistraux interactifs</p>
                    </div>
                </div>

                <!-- Image 3: Laboratoire -->
                <div style="position: relative; height: 200px; border-radius: 15px; overflow: hidden; box-shadow: 0 5px 20px rgba(0,0,0,0.1); transition: transform 0.3s ease;" onmouseover="this.style.transform='translateY(-5px)'" onmouseout="this.style.transform='translateY(0)'">
                    <div style="background: url('https://images.unsplash.com/photo-1532094349884-543bc11b234d?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80') center/cover; width: 100%; height: 100%;"></div>
                    <div style="position: absolute; bottom: 0; left: 0; right: 0; background: linear-gradient(transparent, rgba(0,0,0,0.8)); color: white; padding: 1rem;">
                        <h4 style="margin: 0; font-size: 1.1rem;"><i class="fas fa-flask"></i> Laboratoires</h4>
                        <p style="margin: 0.5rem 0 0 0; font-size: 0.9rem; opacity: 0.9;">Équipements de pointe</p>
                    </div>
                </div>

                <!-- Image 4: Campus -->
                <div style="position: relative; height: 200px; border-radius: 15px; overflow: hidden; box-shadow: 0 5px 20px rgba(0,0,0,0.1); transition: transform 0.3s ease;" onmouseover="this.style.transform='translateY(-5px)'" onmouseout="this.style.transform='translateY(0)'">
                    <div style="background: url('https://images.unsplash.com/photo-1562774053-701939374585?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80') center/cover; width: 100%; height: 100%;"></div>
                    <div style="position: absolute; bottom: 0; left: 0; right: 0; background: linear-gradient(transparent, rgba(0,0,0,0.8)); color: white; padding: 1rem;">
                        <h4 style="margin: 0; font-size: 1.1rem;"><i class="fas fa-university"></i> Campus Verdoyant</h4>
                        <p style="margin: 0.5rem 0 0 0; font-size: 0.9rem; opacity: 0.9;">Environnement inspirant</p>
                    </div>
                </div>

                <!-- Image 5: Étudiants collaborant -->
                <div style="position: relative; height: 200px; border-radius: 15px; overflow: hidden; box-shadow: 0 5px 20px rgba(0,0,0,0.1); transition: transform 0.3s ease;" onmouseover="this.style.transform='translateY(-5px)'" onmouseout="this.style.transform='translateY(0)'">
                    <div style="background: url('https://images.unsplash.com/photo-1522202176988-66273c2fd55f?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80') center/cover; width: 100%; height: 100%;"></div>
                    <div style="position: absolute; bottom: 0; left: 0; right: 0; background: linear-gradient(transparent, rgba(0,0,0,0.8)); color: white; padding: 1rem;">
                        <h4 style="margin: 0; font-size: 1.1rem;"><i class="fas fa-users"></i> Travail Collaboratif</h4>
                        <p style="margin: 0.5rem 0 0 0; font-size: 0.9rem; opacity: 0.9;">Esprit d'équipe et entraide</p>
                    </div>
                </div>

                <!-- Image 6: Événements -->
                <div style="position: relative; height: 200px; border-radius: 15px; overflow: hidden; box-shadow: 0 5px 20px rgba(0,0,0,0.1); transition: transform 0.3s ease;" onmouseover="this.style.transform='translateY(-5px)'" onmouseout="this.style.transform='translateY(0)'">
                    <div style="background: url('https://images.unsplash.com/photo-1511632765486-a01980e01a18?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80') center/cover; width: 100%; height: 100%;"></div>
                    <div style="position: absolute; bottom: 0; left: 0; right: 0; background: linear-gradient(transparent, rgba(0,0,0,0.8)); color: white; padding: 1rem;">
                        <h4 style="margin: 0; font-size: 1.1rem;"><i class="fas fa-calendar-alt"></i> Événements</h4>
                        <p style="margin: 0.5rem 0 0 0; font-size: 0.9rem; opacity: 0.9;">Conférences et activités</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Section CTA -->
    <section class="cta">
        <div class="container">
            <div class="cta-content">
                <h2>Prêt à rejoindre notre communauté ?</h2>
                <p>Inscrivez-vous dès maintenant et bénéficiez de tous nos services</p>
                <a href="register.php" class="btn btn-primary btn-large">S'inscrire maintenant</a>
            </div>
        </div>
    </section>
</main>

<?php include 'includes/footer.php'; ?>