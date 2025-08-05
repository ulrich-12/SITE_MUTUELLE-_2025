<?php
session_start();
require_once '../includes/db.php';

// Vérification de la connexion
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Non autorisé']);
    exit;
}

// Récupération de l'ID d'inscription
$inscription_id = intval(isset($_GET['inscription_id']) ? $_GET['inscription_id'] : 0);

if ($inscription_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'ID d\'inscription invalide']);
    exit;
}

try {
    // Vérifier que l'inscription appartient à l'utilisateur connecté
    $sql = "SELECT i.*, m.nom as matiere_nom, m.code as matiere_code 
            FROM inscriptions i 
            JOIN matieres m ON i.matiere_id = m.id 
            WHERE i.id = ? AND i.user_id = ?";
    $stmt = executeQuery($sql, [$inscription_id, $_SESSION['user_id']]);
    $inscription = $stmt->fetch();
    
    if (!$inscription) {
        echo json_encode(['success' => false, 'message' => 'Inscription non trouvée']);
        exit;
    }
    
    // Récupérer les notes détaillées
    $notes = getNotesForInscription($inscription_id);
    
    if (empty($notes)) {
        echo json_encode(['success' => false, 'message' => 'Aucune note disponible']);
        exit;
    }
    
    // Générer le HTML pour les notes
    $html = '<div style="padding: 1rem;">';
    $html .= '<h4 style="color: var(--primary-color); margin-bottom: 1rem;">';
    $html .= '<i class="fas fa-book"></i> ' . htmlspecialchars($inscription['matiere_nom']);
    $html .= ' <span style="color: var(--text-light); font-size: 0.9rem;">(' . htmlspecialchars($inscription['matiere_code']) . ')</span>';
    $html .= '</h4>';
    
    // Tableau des notes
    $html .= '<div style="overflow-x: auto;">';
    $html .= '<table style="width: 100%; border-collapse: collapse; margin-bottom: 1rem;">';
    $html .= '<thead>';
    $html .= '<tr style="background: #f8f9fa;">';
    $html .= '<th style="padding: 0.75rem; text-align: left; border-bottom: 2px solid var(--border-color);">Évaluation</th>';
    $html .= '<th style="padding: 0.75rem; text-align: center; border-bottom: 2px solid var(--border-color);">Note</th>';
    $html .= '<th style="padding: 0.75rem; text-align: center; border-bottom: 2px solid var(--border-color);">Coefficient</th>';
    $html .= '<th style="padding: 0.75rem; text-align: center; border-bottom: 2px solid var(--border-color);">Date</th>';
    $html .= '<th style="padding: 0.75rem; text-align: center; border-bottom: 2px solid var(--border-color);">Note/20</th>';
    $html .= '</tr>';
    $html .= '</thead>';
    $html .= '<tbody>';
    
    $total_points = 0;
    $total_coefficients = 0;
    
    foreach ($notes as $note) {
        $note_sur_20 = ($note['note'] / $note['note_sur']) * 20;
        $total_points += $note_sur_20 * $note['evaluation_coefficient'];
        $total_coefficients += $note['evaluation_coefficient'];
        
        $html .= '<tr style="border-bottom: 1px solid var(--border-color);">';
        
        // Évaluation
        $html .= '<td style="padding: 0.75rem;">';
        $html .= '<div style="font-weight: 600; color: var(--text-dark);">' . htmlspecialchars($note['evaluation_nom']) . '</div>';
        $html .= '<div style="font-size: 0.8rem; color: var(--text-light);">';
        
        // Badge du type d'évaluation
        $type_colors = [
            'controle' => '#ff9800',
            'partiel' => '#2196f3',
            'final' => '#f44336',
            'projet' => '#9c27b0',
            'tp' => '#4caf50',
            'oral' => '#607d8b'
        ];
        $color = $type_colors[$note['evaluation_type']] ?? '#616161';
        
        $html .= '<span style="background: ' . $color . '; color: white; padding: 0.2rem 0.5rem; border-radius: 10px; font-size: 0.7rem;">';
        $html .= strtoupper($note['evaluation_type']);
        $html .= '</span>';
        $html .= '</div>';
        $html .= '</td>';
        
        // Note
        $html .= '<td style="padding: 0.75rem; text-align: center;">';
        $html .= '<span style="font-weight: bold; font-size: 1.1rem; color: ' . ($note_sur_20 >= 10 ? '#4caf50' : '#f44336') . ';">';
        $html .= $note['note'] . '/' . $note['note_sur'];
        $html .= '</span>';
        $html .= '</td>';
        
        // Coefficient
        $html .= '<td style="padding: 0.75rem; text-align: center;">';
        $html .= '<span style="background: #e3f2fd; color: #1565c0; padding: 0.25rem 0.5rem; border-radius: 5px; font-size: 0.9rem;">';
        $html .= $note['evaluation_coefficient'];
        $html .= '</span>';
        $html .= '</td>';
        
        // Date
        $html .= '<td style="padding: 0.75rem; text-align: center; color: var(--text-light); font-size: 0.9rem;">';
        $html .= date('d/m/Y', strtotime($note['date_evaluation']));
        $html .= '</td>';
        
        // Note sur 20
        $html .= '<td style="padding: 0.75rem; text-align: center;">';
        $html .= '<span style="font-weight: bold; color: ' . ($note_sur_20 >= 10 ? '#4caf50' : '#f44336') . ';">';
        $html .= number_format($note_sur_20, 2) . '/20';
        $html .= '</span>';
        $html .= '</td>';
        
        $html .= '</tr>';
        
        // Commentaire s'il y en a un
        if (!empty($note['commentaire'])) {
            $html .= '<tr>';
            $html .= '<td colspan="5" style="padding: 0.5rem 0.75rem; background: #f8f9fa; font-style: italic; color: var(--text-light); border-bottom: 1px solid var(--border-color);">';
            $html .= '<i class="fas fa-comment"></i> ' . htmlspecialchars($note['commentaire']);
            $html .= '</td>';
            $html .= '</tr>';
        }
    }
    
    $html .= '</tbody>';
    $html .= '</table>';
    $html .= '</div>';
    
    // Calcul de la moyenne
    if ($total_coefficients > 0) {
        $moyenne = $total_points / $total_coefficients;
        
        $html .= '<div style="background: linear-gradient(135deg, var(--primary-color), var(--accent-color)); color: white; padding: 1.5rem; border-radius: 10px; text-align: center;">';
        $html .= '<h3 style="margin: 0 0 0.5rem 0; font-size: 1.2rem;">Moyenne de la matière</h3>';
        $html .= '<div style="font-size: 2.5rem; font-weight: bold; margin-bottom: 0.5rem;">';
        $html .= number_format($moyenne, 2) . '/20';
        $html .= '</div>';
        
        // Statut
        $html .= '<div style="font-size: 1rem; opacity: 0.9;">';
        if ($moyenne >= 10) {
            $html .= '<i class="fas fa-check-circle"></i> Matière validée';
        } else {
            $html .= '<i class="fas fa-times-circle"></i> Matière non validée';
        }
        $html .= '</div>';
        
        // Barre de progression
        $html .= '<div style="background: rgba(255,255,255,0.3); height: 8px; border-radius: 4px; margin-top: 1rem; overflow: hidden;">';
        $html .= '<div style="background: white; height: 100%; width: ' . min(100, ($moyenne / 20) * 100) . '%; transition: width 0.3s ease;"></div>';
        $html .= '</div>';
        
        $html .= '</div>';
    }
    
    $html .= '</div>';
    
    echo json_encode(['success' => true, 'html' => $html]);
    
} catch (Exception $e) {
    error_log("Erreur get_detailed_notes : " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Erreur interne']);
}
?>
