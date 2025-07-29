-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 25, 2025 at 10:28 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `mutuelle_udm`
--

-- --------------------------------------------------------

--
-- Table structure for table `documents`
--

CREATE TABLE `documents` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `filename` varchar(255) NOT NULL,
  `original_filename` varchar(255) NOT NULL,
  `file_size` int(11) NOT NULL,
  `file_type` varchar(100) NOT NULL,
  `filiere` varchar(100) NOT NULL,
  `niveau` varchar(10) NOT NULL,
  `matiere` varchar(100) DEFAULT NULL,
  `type_document` enum('examen','cours','td','tp','autre') DEFAULT 'autre',
  `downloads` int(11) DEFAULT 0,
  `active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `documents`
--

INSERT INTO `documents` (`id`, `user_id`, `title`, `description`, `filename`, `original_filename`, `file_size`, `file_type`, `filiere`, `niveau`, `matiere`, `type_document`, `downloads`, `active`, `created_at`, `updated_at`) VALUES
(1, 1, 'Examen Final Mathématiques L1 - Janvier 2024', 'Examen final de mathématiques pour les étudiants de première année. Couvre l\'algèbre linéaire, les fonctions et les dérivées.', 'math_l1_jan2024.pdf', 'Examen_Math_L1_Janvier_2024.pdf', 2048576, 'pdf', 'informatique', 'L1', 'Mathématiques', 'examen', 0, 1, '2025-07-25 11:40:17', '2025-07-25 11:40:17'),
(2, 1, 'Cours Complet - Algorithmique et Structures de Données', 'Cours complet sur l\'algorithmique couvrant les tris, les structures de données (listes, arbres, graphes) et la complexité.', 'cours_algo_l2.pdf', 'Cours_Algorithmique_L2.pdf', 5242880, 'pdf', 'informatique', 'L2', 'Algorithmique', 'cours', 0, 1, '2025-07-25 11:40:17', '2025-07-25 11:40:17'),
(3, 1, 'TD Programmation C - Pointeurs et Allocation Dynamique', 'Travaux dirigés sur la programmation en C, focus sur les pointeurs, l\'allocation dynamique de mémoire et la gestion des structures.', 'td_c_pointeurs.pdf', 'TD_Programmation_C_Pointeurs.pdf', 1536000, 'pdf', 'informatique', 'L2', 'Programmation C', 'td', 0, 1, '2025-07-25 11:40:17', '2025-07-25 11:40:17'),
(4, 1, 'TP Base de Données - Conception et Requêtes SQL', 'Travaux pratiques sur la conception de bases de données relationnelles et l\'écriture de requêtes SQL complexes.', 'tp_bdd_sql.pdf', 'TP_Base_Donnees_SQL.pdf', 3145728, 'pdf', 'informatique', 'L3', 'Base de Données', 'tp', 0, 1, '2025-07-25 11:40:17', '2025-07-25 11:40:17'),
(5, 1, 'Examen Comptabilité Générale - Session Juin 2024', 'Examen de comptabilité générale pour les étudiants en gestion. Inclut des exercices pratiques sur le bilan et le compte de résultat.', 'compta_generale_juin2024.pdf', 'Examen_Comptabilite_Juin_2024.pdf', 1843200, 'pdf', 'gestion', 'L1', 'Comptabilité', 'examen', 0, 1, '2025-07-25 11:40:17', '2025-07-25 11:40:17'),
(6, 1, 'Cours Marketing Digital et E-commerce', 'Cours sur les stratégies de marketing digital, les réseaux sociaux, le SEO/SEA et les plateformes e-commerce.', 'marketing_digital_m1.pdf', 'Cours_Marketing_Digital_M1.pdf', 4194304, 'pdf', 'gestion', 'M1', 'Marketing', 'cours', 0, 1, '2025-07-25 11:40:17', '2025-07-25 11:40:17'),
(7, 1, 'TD Microéconomie - Théorie du Consommateur', 'Exercices sur la théorie du consommateur, les courbes d\'indifférence, l\'optimisation sous contrainte budgétaire.', 'td_microeco_l2.pdf', 'TD_Microeconomie_L2.pdf', 2097152, 'pdf', 'economie', 'L2', 'Microéconomie', 'td', 0, 1, '2025-07-25 11:40:17', '2025-07-25 11:40:17'),
(8, 1, 'Examen Droit Constitutionnel - Janvier 2024', 'Examen portant sur les institutions de la Ve République, la séparation des pouvoirs et le contrôle de constitutionnalité.', 'droit_constit_jan2024.pdf', 'Examen_Droit_Constitutionnel_2024.pdf', 1572864, 'pdf', 'droit', 'L1', 'Droit Constitutionnel', 'examen', 1, 1, '2025-07-25 11:40:18', '2025-07-25 11:46:01'),
(9, 1, 'Cours Physique Quantique - Introduction', 'Introduction à la mécanique quantique : dualité onde-corpuscule, équation de Schrödinger, principe d\'incertitude.', 'physique_quantique_l3.pdf', 'Cours_Physique_Quantique_L3.pdf', 6291456, 'pdf', 'sciences', 'L3', 'Physique', 'cours', 0, 1, '2025-07-25 11:40:18', '2025-07-25 11:40:18'),
(10, 1, 'TP Chimie Organique - Synthèse et Purification', 'Travaux pratiques de chimie organique : techniques de synthèse, purification par recristallisation et chromatographie.', 'tp_chimie_orga_l2.pdf', 'TP_Chimie_Organique_L2.pdf', 2621440, 'pdf', 'sciences', 'L2', 'Chimie', 'tp', 1, 1, '2025-07-25 11:40:18', '2025-07-25 16:00:16'),
(11, 1, 'Résumé Littérature Française - XIXe Siècle', 'Résumé des grands mouvements littéraires du XIXe siècle : romantisme, réalisme, naturalisme. Auteurs et œuvres principales.', 'resume_litt_19e.pdf', 'Resume_Litterature_19e_Siecle.pdf', 1048576, 'pdf', 'lettres', 'L2', 'Littérature', 'autre', 1, 1, '2025-07-25 11:40:19', '2025-07-25 11:42:59'),
(12, 1, 'Examen Anatomie Humaine - Système Cardiovasculaire', 'Examen d\'anatomie sur le système cardiovasculaire : cœur, vaisseaux sanguins, circulation sanguine.', 'anatomie_cardio_l1.pdf', 'Examen_Anatomie_Cardiovasculaire.pdf', 3670016, 'pdf', 'medecine', 'L1', 'Anatomie', 'examen', 0, 1, '2025-07-25 11:40:19', '2025-07-25 11:40:19'),
(13, 1, 'test uploard', '', '68837e5b71639_1753448027.pdf', 'E_IMG_Female reproductive in young.pdf', 515730, 'pdf', 'medecine', 'L1', 'Reproduction', 'cours', 2, 1, '2025-07-25 12:53:47', '2025-07-25 15:59:36');

-- --------------------------------------------------------

--
-- Table structure for table `evaluations`
--

CREATE TABLE `evaluations` (
  `id` int(11) NOT NULL,
  `nom` varchar(100) NOT NULL,
  `type` enum('controle','partiel','final','projet','tp','oral') NOT NULL,
  `coefficient` decimal(3,1) DEFAULT 1.0,
  `description` text DEFAULT NULL,
  `active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `evaluations`
--

INSERT INTO `evaluations` (`id`, `nom`, `type`, `coefficient`, `description`, `active`, `created_at`) VALUES
(1, 'Contrôle Continu 1', 'controle', 0.3, 'Premier contrôle continu', 1, '2025-07-25 12:02:41'),
(2, 'Contrôle Continu 2', 'controle', 0.3, 'Deuxième contrôle continu', 1, '2025-07-25 12:02:41'),
(3, 'Examen Partiel', 'partiel', 1.0, 'Examen de mi-semestre', 1, '2025-07-25 12:02:41'),
(4, 'Examen Final', 'final', 2.0, 'Examen final de fin de semestre', 1, '2025-07-25 12:02:41'),
(5, 'Projet', 'projet', 1.5, 'Projet de fin de module', 1, '2025-07-25 12:02:42'),
(6, 'Travaux Pratiques', 'tp', 0.5, 'Évaluation des TP', 1, '2025-07-25 12:02:42'),
(7, 'Examen Oral', 'oral', 1.0, 'Présentation orale', 1, '2025-07-25 12:02:42');

-- --------------------------------------------------------

--
-- Table structure for table `inscriptions`
--

CREATE TABLE `inscriptions` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `semestre_id` int(11) NOT NULL,
  `matiere_id` int(11) NOT NULL,
  `date_inscription` timestamp NOT NULL DEFAULT current_timestamp(),
  `active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `inscriptions`
--

INSERT INTO `inscriptions` (`id`, `user_id`, `semestre_id`, `matiere_id`, `date_inscription`, `active`) VALUES
(1, 1, 1, 1, '2025-07-25 12:03:06', 1),
(2, 1, 1, 2, '2025-07-25 12:03:06', 1),
(3, 1, 1, 3, '2025-07-25 12:03:06', 1),
(4, 1, 1, 4, '2025-07-25 12:03:06', 1),
(5, 1, 1, 5, '2025-07-25 12:03:06', 1),
(6, 1, 1, 6, '2025-07-25 12:03:06', 1),
(7, 1, 4, 7, '2025-07-25 12:03:06', 1),
(8, 1, 4, 8, '2025-07-25 12:03:06', 1),
(9, 1, 4, 9, '2025-07-25 12:03:06', 1),
(10, 1, 4, 10, '2025-07-25 12:03:06', 1),
(11, 1, 4, 11, '2025-07-25 12:03:06', 1),
(12, 1, 4, 12, '2025-07-25 12:03:06', 1);

-- --------------------------------------------------------

--
-- Table structure for table `matieres`
--

CREATE TABLE `matieres` (
  `id` int(11) NOT NULL,
  `nom` varchar(100) NOT NULL,
  `code` varchar(20) NOT NULL,
  `filiere` varchar(100) NOT NULL,
  `niveau` varchar(10) NOT NULL,
  `coefficient` decimal(3,1) DEFAULT 1.0,
  `credits` int(11) DEFAULT 3,
  `semestre_type` enum('S1','S2','S3','S4','S5','S6') NOT NULL,
  `active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `matieres`
--

INSERT INTO `matieres` (`id`, `nom`, `code`, `filiere`, `niveau`, `coefficient`, `credits`, `semestre_type`, `active`, `created_at`) VALUES
(1, 'Algorithmique Avancée', 'ALG301', 'informatique', 'L3', 2.0, 6, 'S1', 1, '2025-07-25 12:03:05'),
(2, 'Base de Données', 'BDD301', 'informatique', 'L3', 2.0, 6, 'S1', 1, '2025-07-25 12:03:05'),
(3, 'Programmation Orientée Objet', 'POO301', 'informatique', 'L3', 2.0, 6, 'S1', 1, '2025-07-25 12:03:05'),
(4, 'Systèmes d\'Exploitation', 'SYS301', 'informatique', 'L3', 1.5, 4, 'S1', 1, '2025-07-25 12:03:05'),
(5, 'Mathématiques Discrètes', 'MAT301', 'informatique', 'L3', 1.5, 4, 'S1', 1, '2025-07-25 12:03:05'),
(6, 'Anglais Technique', 'ANG301', 'informatique', 'L3', 1.0, 2, 'S1', 1, '2025-07-25 12:03:05'),
(7, 'Génie Logiciel', 'GL302', 'informatique', 'L3', 2.0, 6, 'S2', 1, '2025-07-25 12:03:06'),
(8, 'Réseaux Informatiques', 'RES302', 'informatique', 'L3', 2.0, 6, 'S2', 1, '2025-07-25 12:03:06'),
(9, 'Intelligence Artificielle', 'IA302', 'informatique', 'L3', 1.5, 4, 'S2', 1, '2025-07-25 12:03:06'),
(10, 'Sécurité Informatique', 'SEC302', 'informatique', 'L3', 1.5, 4, 'S2', 1, '2025-07-25 12:03:06'),
(11, 'Projet de Fin d\'Études', 'PFE302', 'informatique', 'L3', 2.0, 8, 'S2', 1, '2025-07-25 12:03:06'),
(12, 'Communication', 'COM302', 'informatique', 'L3', 1.0, 2, 'S2', 1, '2025-07-25 12:03:06');

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `id` int(11) NOT NULL,
  `sender_id` int(11) NOT NULL,
  `receiver_id` int(11) DEFAULT NULL,
  `subject` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `is_public` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `messages`
--

INSERT INTO `messages` (`id`, `sender_id`, `receiver_id`, `subject`, `message`, `is_read`, `is_public`, `created_at`) VALUES
(1, 1, NULL, 'Bienvenue sur la plateforme Mutuelle UDM !', 'Chers étudiants,\n\nNous sommes ravis de vous accueillir sur la nouvelle plateforme de la Mutuelle UDM. Cette plateforme vous permettra de :\n\n• Consulter vos résultats académiques\n• Accéder à la banque d\'épreuves\n• Communiquer avec vos collègues\n• Partager des documents\n\nN\'hésitez pas à explorer toutes les fonctionnalités disponibles !\n\nCordialement,\nL\'équipe Mutuelle UDM', 1, 1, '2025-07-25 12:30:34'),
(2, 1, NULL, 'Nouvelle session d\'examens - Janvier 2025', 'Chers étudiants,\n\nLa session d\'examens de janvier 2025 approche. Voici les informations importantes :\n\n📅 Dates : Du 15 au 30 janvier 2025\n📍 Lieux : Amphithéâtres A, B et C\n⏰ Horaires : 8h00 - 12h00 et 14h00 - 18h00\n\nLes convocations individuelles seront disponibles dans vos espaces personnels dès le 10 janvier.\n\nBonne préparation à tous !\n\nService de la Scolarité', 1, 1, '2025-07-25 12:30:34'),
(3, 1, NULL, 'Mise à jour de la banque d\'épreuves', 'Bonjour à tous,\n\nNous avons le plaisir de vous informer que la banque d\'épreuves a été enrichie avec :\n\n• 50 nouveaux sujets d\'examens\n• Des corrigés détaillés\n• Des exercices pratiques\n\nCes ressources couvrent toutes les filières et niveaux. Nous encourageons également les étudiants à partager leurs propres documents pour enrichir cette base commune.\n\nBonne étude !\n\nÉquipe pédagogique', 1, 1, '2025-07-25 12:30:34'),
(4, 2, 1, 'Question sur le projet de base de données', 'Salut !\n\nJ\'espère que tu vas bien. Je travaille actuellement sur le projet de base de données et j\'ai quelques questions :\n\n1. Comment as-tu structuré tes tables pour la partie normalisation ?\n2. As-tu utilisé des triggers ou des procédures stockées ?\n\nSi tu as un moment, on pourrait se voir à la bibliothèque pour en discuter ?\n\nMerci d\'avance !\nSara', 1, 0, '2025-07-25 12:30:34'),
(5, 3, 1, 'Groupe d\'étude pour l\'examen de gestion', 'Bonjour,\n\nJe forme un groupe d\'étude pour l\'examen de gestion financière. Nous nous retrouvons tous les mardis et jeudis de 16h à 18h en salle B12.\n\nSi ça t\'intéresse, rejoins-nous ! Plus on est nombreux, mieux c\'est pour réviser ensemble.\n\nÀ bientôt j\'espère,\nAhmed', 1, 0, '2025-07-25 12:30:34'),
(6, 1, 3, 'Re: Groupe d\'étude pour l\'examen de gestion', 'Salut Ahmed,\n\nMerci pour ta proposition ! Je suis très intéressé par le groupe d\'étude. Les mardis et jeudis ça me va parfaitement.\n\nEst-ce que vous avez déjà défini un programme de révision ? J\'ai quelques anciens sujets qui pourraient être utiles.\n\nÀ mardi alors !\nUtilisateur Test', 0, 0, '2025-07-25 12:30:34'),
(7, 4, 1, 'Aide pour l\'installation de l\'environnement de dev', 'Bonjour,\n\nJe suis en L1 informatique et j\'ai des difficultés pour installer l\'environnement de développement. J\'ai entendu dire que tu étais très doué en informatique.\n\nPourrais-tu m\'aider ? Je peux me libérer quand tu veux cette semaine.\n\nMerci beaucoup !\nFatima', 1, 0, '2025-07-25 12:30:34'),
(8, 5, 1, 'Partage de notes de cours', 'Salut,\n\nJ\'ai vu que tu avais uploadé des documents très utiles sur la plateforme. J\'ai moi-même des notes de cours d\'économie qui pourraient intéresser d\'autres étudiants.\n\nComment fait-on pour les partager ? Y a-t-il une procédure particulière ?\n\nMerci pour ton aide !\nOmar', 0, 0, '2025-07-25 12:30:34'),
(9, 6, 1, 'Salutation', 'Yo, comment tu vas', 1, 0, '2025-07-25 13:14:00'),
(10, 1, 6, 'Re: Salutation', 'bien bien et toi', 1, 0, '2025-07-25 13:14:51');

-- --------------------------------------------------------

--
-- Table structure for table `moyennes`
--

CREATE TABLE `moyennes` (
  `id` int(11) NOT NULL,
  `inscription_id` int(11) NOT NULL,
  `moyenne_matiere` decimal(4,2) DEFAULT NULL,
  `moyenne_semestre` decimal(4,2) DEFAULT NULL,
  `moyenne_generale` decimal(4,2) DEFAULT NULL,
  `credits_obtenus` int(11) DEFAULT 0,
  `credits_totaux` int(11) DEFAULT 0,
  `statut` enum('admis','ajourne','redouble','en_cours') DEFAULT 'en_cours',
  `derniere_maj` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `moyennes`
--

INSERT INTO `moyennes` (`id`, `inscription_id`, `moyenne_matiere`, `moyenne_semestre`, `moyenne_generale`, `credits_obtenus`, `credits_totaux`, `statut`, `derniere_maj`) VALUES
(1, 1, 14.30, NULL, NULL, 0, 0, 'admis', '2025-07-25 12:03:07'),
(2, 2, 10.52, NULL, NULL, 0, 0, 'admis', '2025-07-25 12:03:07'),
(3, 3, 16.30, NULL, NULL, 0, 0, 'admis', '2025-07-25 12:03:07'),
(4, 4, 11.26, NULL, NULL, 0, 0, 'admis', '2025-07-25 12:03:07'),
(5, 5, 13.60, NULL, NULL, 0, 0, 'admis', '2025-07-25 12:03:07'),
(6, 6, 14.33, NULL, NULL, 0, 0, 'admis', '2025-07-25 12:03:08'),
(7, 7, 17.61, NULL, NULL, 0, 0, 'admis', '2025-07-25 12:03:08'),
(8, 8, 10.91, NULL, NULL, 0, 0, 'admis', '2025-07-25 12:03:10'),
(9, 9, 16.44, NULL, NULL, 0, 0, 'admis', '2025-07-25 12:03:10'),
(10, 10, 14.19, NULL, NULL, 0, 0, 'admis', '2025-07-25 12:03:10'),
(11, 11, 13.78, NULL, NULL, 0, 0, 'admis', '2025-07-25 12:03:11'),
(12, 12, 15.16, NULL, NULL, 0, 0, 'admis', '2025-07-25 12:03:11');

-- --------------------------------------------------------

--
-- Table structure for table `notes`
--

CREATE TABLE `notes` (
  `id` int(11) NOT NULL,
  `inscription_id` int(11) NOT NULL,
  `evaluation_id` int(11) NOT NULL,
  `note` decimal(4,2) NOT NULL,
  `note_sur` decimal(4,2) DEFAULT 20.00,
  `date_evaluation` date NOT NULL,
  `commentaire` text DEFAULT NULL,
  `validee` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ;

--
-- Dumping data for table `notes`
--

INSERT INTO `notes` (`id`, `inscription_id`, `evaluation_id`, `note`, `note_sur`, `date_evaluation`, `commentaire`, `validee`, `created_at`, `updated_at`) VALUES
(1, 1, 3, 14.46, 20.00, '2025-06-20', NULL, 1, '2025-07-25 12:03:06', '2025-07-25 12:03:06'),
(2, 1, 7, 17.76, 20.00, '2025-06-24', 'Bon travail, continuez ainsi !', 1, '2025-07-25 12:03:06', '2025-07-25 12:03:06'),
(3, 1, 7, 11.41, 20.00, '2025-06-12', 'Quelques lacunes à combler.', 1, '2025-07-25 12:03:06', '2025-07-25 12:03:06'),
(4, 1, 6, 12.85, 20.00, '2025-05-14', NULL, 1, '2025-07-25 12:03:07', '2025-07-25 12:03:07'),
(5, 2, 4, 10.00, 20.00, '2025-05-31', 'Quelques lacunes à combler.', 1, '2025-07-25 12:03:07', '2025-07-25 12:03:07'),
(6, 2, 7, 11.55, 20.00, '2025-05-20', NULL, 1, '2025-07-25 12:03:07', '2025-07-25 12:03:07'),
(7, 3, 4, 17.53, 20.00, '2025-05-31', 'Quelques lacunes à combler.', 1, '2025-07-25 12:03:07', '2025-07-25 12:03:07'),
(8, 3, 1, 8.09, 20.00, '2025-07-20', NULL, 1, '2025-07-25 12:03:07', '2025-07-25 12:03:07'),
(9, 4, 6, 11.25, 20.00, '2025-05-26', 'Excellent niveau de compréhension.', 1, '2025-07-25 12:03:07', '2025-07-25 12:03:07'),
(10, 4, 6, 8.71, 20.00, '2025-07-17', NULL, 1, '2025-07-25 12:03:07', '2025-07-25 12:03:07'),
(11, 4, 3, 14.00, 20.00, '2025-07-02', 'Peut mieux faire.', 1, '2025-07-25 12:03:07', '2025-07-25 12:03:07'),
(12, 4, 7, 9.80, 20.00, '2025-07-13', 'Peut mieux faire.', 1, '2025-07-25 12:03:07', '2025-07-25 12:03:07'),
(13, 5, 6, 10.19, 20.00, '2025-05-04', NULL, 1, '2025-07-25 12:03:07', '2025-07-25 12:03:07'),
(14, 5, 6, 13.62, 20.00, '2025-06-02', NULL, 1, '2025-07-25 12:03:07', '2025-07-25 12:03:07'),
(15, 5, 6, 17.00, 20.00, '2025-06-25', 'Quelques lacunes à combler.', 1, '2025-07-25 12:03:07', '2025-07-25 12:03:07'),
(16, 6, 1, 18.50, 20.00, '2025-07-01', 'Travail sérieux et appliqué.', 1, '2025-07-25 12:03:07', '2025-07-25 12:03:07'),
(17, 6, 7, 8.91, 20.00, '2025-06-25', 'Excellent niveau de compréhension.', 1, '2025-07-25 12:03:07', '2025-07-25 12:03:07'),
(18, 6, 2, 15.60, 20.00, '2025-06-17', 'Excellent niveau de compréhension.', 1, '2025-07-25 12:03:07', '2025-07-25 12:03:07'),
(19, 6, 5, 16.86, 20.00, '2025-07-04', NULL, 1, '2025-07-25 12:03:07', '2025-07-25 12:03:07'),
(20, 7, 2, 14.07, 20.00, '2025-05-24', 'Bon travail, continuez ainsi !', 1, '2025-07-25 12:03:08', '2025-07-25 12:03:08'),
(21, 7, 7, 18.67, 20.00, '2025-07-05', 'Quelques lacunes à combler.', 1, '2025-07-25 12:03:08', '2025-07-25 12:03:08'),
(22, 8, 6, 8.44, 20.00, '2025-06-22', 'Bon travail, continuez ainsi !', 1, '2025-07-25 12:03:09', '2025-07-25 12:03:09'),
(23, 8, 1, 15.06, 20.00, '2025-05-24', NULL, 1, '2025-07-25 12:03:09', '2025-07-25 12:03:09'),
(24, 8, 4, 10.90, 20.00, '2025-04-30', 'Bon travail, continuez ainsi !', 1, '2025-07-25 12:03:09', '2025-07-25 12:03:09'),
(25, 9, 2, 18.32, 20.00, '2025-05-14', 'Travail sérieux et appliqué.', 1, '2025-07-25 12:03:10', '2025-07-25 12:03:10'),
(26, 9, 7, 15.88, 20.00, '2025-05-29', 'Quelques lacunes à combler.', 1, '2025-07-25 12:03:10', '2025-07-25 12:03:10'),
(27, 10, 4, 16.00, 20.00, '2025-05-10', 'Travail sérieux et appliqué.', 1, '2025-07-25 12:03:10', '2025-07-25 12:03:10'),
(28, 10, 4, 12.37, 20.00, '2025-05-01', NULL, 1, '2025-07-25 12:03:10', '2025-07-25 12:03:10'),
(29, 11, 5, 10.54, 20.00, '2025-06-25', NULL, 1, '2025-07-25 12:03:11', '2025-07-25 12:03:11'),
(30, 11, 1, 18.10, 20.00, '2025-07-08', NULL, 1, '2025-07-25 12:03:11', '2025-07-25 12:03:11'),
(31, 11, 3, 13.14, 20.00, '2025-05-16', 'Excellent niveau de compréhension.', 1, '2025-07-25 12:03:11', '2025-07-25 12:03:11'),
(32, 11, 7, 18.00, 20.00, '2025-06-27', 'Excellent niveau de compréhension.', 1, '2025-07-25 12:03:11', '2025-07-25 12:03:11'),
(33, 12, 1, 9.08, 20.00, '2025-05-10', NULL, 1, '2025-07-25 12:03:11', '2025-07-25 12:03:11'),
(34, 12, 3, 16.99, 20.00, '2025-06-05', 'Quelques lacunes à combler.', 1, '2025-07-25 12:03:11', '2025-07-25 12:03:11');

-- --------------------------------------------------------

--
-- Table structure for table `semestres`
--

CREATE TABLE `semestres` (
  `id` int(11) NOT NULL,
  `nom` varchar(50) NOT NULL,
  `annee_universitaire` varchar(20) NOT NULL,
  `date_debut` date NOT NULL,
  `date_fin` date NOT NULL,
  `active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `semestres`
--

INSERT INTO `semestres` (`id`, `nom`, `annee_universitaire`, `date_debut`, `date_fin`, `active`, `created_at`) VALUES
(1, 'Semestre 1', '2024-2025', '2024-09-01', '2025-01-31', 1, '2025-07-25 12:02:42'),
(2, 'Semestre 2', '2024-2025', '2025-02-01', '2025-06-30', 1, '2025-07-25 12:02:42'),
(3, 'Semestre 1', '2023-2024', '2023-09-01', '2024-01-31', 1, '2025-07-25 12:02:42'),
(4, 'Semestre 2', '2023-2024', '2024-02-01', '2024-06-30', 1, '2025-07-25 12:02:42');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `nom` varchar(100) NOT NULL,
  `prenom` varchar(100) NOT NULL,
  `email` varchar(255) NOT NULL,
  `numero_etudiant` varchar(20) NOT NULL,
  `filiere` varchar(100) NOT NULL,
  `niveau` varchar(10) NOT NULL,
  `role` enum('etudiant','moderateur','admin','super_admin') DEFAULT 'etudiant',
  `password_hash` varchar(255) NOT NULL,
  `active` tinyint(1) DEFAULT 1,
  `email_verified` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `last_login` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `nom`, `prenom`, `email`, `numero_etudiant`, `filiere`, `niveau`, `role`, `password_hash`, `active`, `email_verified`, `created_at`, `updated_at`, `last_login`) VALUES
(1, 'Test', 'Utilisateur', 'test@udm.ma', '2024000001', 'informatique', 'L3', 'moderateur', '$2y$10$Vu3MszoBZH1.UYNxg.v/hOmud3ekf7gNt6yVILOfJN.HR0qUQJ8r2', 1, 1, '2025-07-25 11:39:50', '2025-07-25 15:00:34', '2025-07-25 12:30:12'),
(2, 'Alami', 'Sara', 'sara.alami@udm.ma', '2024000002', 'informatique', 'L3', 'etudiant', '$2y$10$aJTboLhgj6snz0a2Gq7z8OCfSPxAXCdGeLPNTsp/X5GrakNoSS9NC', 1, 0, '2025-07-25 12:30:31', '2025-07-25 12:30:31', NULL),
(3, 'Benali', 'Ahmed', 'ahmed.benali@udm.ma', '2024000003', 'gestion', 'L2', 'etudiant', '$2y$10$yBSa8oWeVrlfRkgcTOQyKO47ix7nU.ttXDWJqeeiu2FocbgYN6bNG', 1, 0, '2025-07-25 12:30:32', '2025-07-25 12:30:32', NULL),
(4, 'Chakir', 'Fatima', 'fatima.chakir@udm.ma', '2024000004', 'informatique', 'L1', 'etudiant', '$2y$10$LO36HMf3AoxraRilkkv8uOEymzn/ZxdqlAtJnCxEoniP32em6Nbli', 1, 0, '2025-07-25 12:30:33', '2025-07-25 12:30:33', NULL),
(5, 'Drissi', 'Omar', 'omar.drissi@udm.ma', '2024000005', 'economie', 'L3', 'etudiant', '$2y$10$uurLXush3iluyVYoUQn7AuafuY/POxYV8cQWIbMBzM8hFn4QnniAC', 1, 0, '2025-07-25 12:30:33', '2025-07-25 12:30:33', NULL),
(6, 'NONO', 'Boris', 'boris@gmail.com', '2024000006', 'gestion', 'L3', 'admin', '$2y$10$aaFqLsU3Zr.DEgj6DxukWeyC4EzhA4PaWThgQKdDQktm6ltfDF0xe', 1, 0, '2025-07-25 13:10:29', '2025-07-25 15:54:53', '2025-07-25 15:54:04'),
(7, 'Admin', 'Super', 'admin@udm.ma', 'ADMIN001', 'administration', 'ADMIN', 'super_admin', '$2y$10$GlkRqHXbCE04QjVmdH.bn.RSlSK2pogbY2x4sGugHONYAhFoJQHK.', 1, 0, '2025-07-25 14:29:20', '2025-07-25 15:33:53', '2025-07-25 15:33:53'),
(9, 'WABA KENNE', 'Mejest ULRICH', 'ulrich@gmail.com', '202212345', 'ingenierie', 'L1', 'etudiant', '$2y$10$yG754DdC//SkopWoUeYgW.PJJlOYQoYrW4wi4v1lql4v6FSuj/hrq', 1, 0, '2025-07-25 16:03:48', '2025-07-25 16:04:11', '2025-07-25 16:04:11');

-- --------------------------------------------------------

--
-- Table structure for table `user_sessions`
--

CREATE TABLE `user_sessions` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `token` varchar(255) NOT NULL,
  `expires_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `documents`
--
ALTER TABLE `documents`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `idx_filiere` (`filiere`),
  ADD KEY `idx_niveau` (`niveau`),
  ADD KEY `idx_matiere` (`matiere`),
  ADD KEY `idx_type` (`type_document`),
  ADD KEY `idx_active` (`active`);

--
-- Indexes for table `evaluations`
--
ALTER TABLE `evaluations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_type` (`type`),
  ADD KEY `idx_active` (`active`);

--
-- Indexes for table `inscriptions`
--
ALTER TABLE `inscriptions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_inscription` (`user_id`,`semestre_id`,`matiere_id`),
  ADD KEY `idx_user` (`user_id`),
  ADD KEY `idx_semestre` (`semestre_id`),
  ADD KEY `idx_matiere` (`matiere_id`);

--
-- Indexes for table `matieres`
--
ALTER TABLE `matieres`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`),
  ADD KEY `idx_filiere` (`filiere`),
  ADD KEY `idx_niveau` (`niveau`),
  ADD KEY `idx_semestre` (`semestre_type`),
  ADD KEY `idx_active` (`active`);

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_sender` (`sender_id`),
  ADD KEY `idx_receiver` (`receiver_id`),
  ADD KEY `idx_public` (`is_public`),
  ADD KEY `idx_read` (`is_read`);

--
-- Indexes for table `moyennes`
--
ALTER TABLE `moyennes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_moyenne` (`inscription_id`),
  ADD KEY `idx_statut` (`statut`),
  ADD KEY `idx_maj` (`derniere_maj`);

--
-- Indexes for table `notes`
--
ALTER TABLE `notes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_inscription` (`inscription_id`),
  ADD KEY `idx_evaluation` (`evaluation_id`),
  ADD KEY `idx_date` (`date_evaluation`),
  ADD KEY `idx_validee` (`validee`);

--
-- Indexes for table `semestres`
--
ALTER TABLE `semestres`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_annee` (`annee_universitaire`),
  ADD KEY `idx_active` (`active`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `numero_etudiant` (`numero_etudiant`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_numero_etudiant` (`numero_etudiant`),
  ADD KEY `idx_filiere` (`filiere`),
  ADD KEY `idx_niveau` (`niveau`),
  ADD KEY `idx_role` (`role`);

--
-- Indexes for table `user_sessions`
--
ALTER TABLE `user_sessions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `token` (`token`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `idx_token` (`token`),
  ADD KEY `idx_expires` (`expires_at`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `documents`
--
ALTER TABLE `documents`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `evaluations`
--
ALTER TABLE `evaluations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `inscriptions`
--
ALTER TABLE `inscriptions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `matieres`
--
ALTER TABLE `matieres`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `moyennes`
--
ALTER TABLE `moyennes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `notes`
--
ALTER TABLE `notes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `semestres`
--
ALTER TABLE `semestres`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `user_sessions`
--
ALTER TABLE `user_sessions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `documents`
--
ALTER TABLE `documents`
  ADD CONSTRAINT `documents_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `inscriptions`
--
ALTER TABLE `inscriptions`
  ADD CONSTRAINT `inscriptions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `inscriptions_ibfk_2` FOREIGN KEY (`semestre_id`) REFERENCES `semestres` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `inscriptions_ibfk_3` FOREIGN KEY (`matiere_id`) REFERENCES `matieres` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `messages`
--
ALTER TABLE `messages`
  ADD CONSTRAINT `messages_ibfk_1` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `messages_ibfk_2` FOREIGN KEY (`receiver_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `moyennes`
--
ALTER TABLE `moyennes`
  ADD CONSTRAINT `moyennes_ibfk_1` FOREIGN KEY (`inscription_id`) REFERENCES `inscriptions` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `notes`
--
ALTER TABLE `notes`
  ADD CONSTRAINT `notes_ibfk_1` FOREIGN KEY (`inscription_id`) REFERENCES `inscriptions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `notes_ibfk_2` FOREIGN KEY (`evaluation_id`) REFERENCES `evaluations` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_sessions`
--
ALTER TABLE `user_sessions`
  ADD CONSTRAINT `user_sessions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
