<?php
require_once 'includes/auth_middleware.php';

// Vérification de l'authentification et des permissions
$user = checkAuth('upload_documents', 'moderateur');

// Logger l'accès à la page d'upload
logAction($_SESSION['user_id'], 'access_upload_page', 'Accès à la page d\'upload de documents');

$errors = [];
$success = false;

// Traitement du formulaire d'upload
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Vérification du token CSRF
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $errors['csrf'] = 'Token de sécurité invalide';
    }
    
    // Récupération des données
    $title = trim(isset($_POST['title']) ? $_POST['title'] : '');
    $description = trim(isset($_POST['description']) ? $_POST['description'] : '');
    $filiere = isset($_POST['filiere']) ? $_POST['filiere'] : '';
    $niveau = isset($_POST['niveau']) ? $_POST['niveau'] : '';
    $matiere = trim(isset($_POST['matiere']) ? $_POST['matiere'] : '');
    $type_document = isset($_POST['type_document']) ? $_POST['type_document'] : '';
    
    // Validation des champs
    if (empty($title)) {
        $errors['title'] = 'Le titre est requis';
    } elseif (strlen($title) > 255) {
        $errors['title'] = 'Le titre ne peut pas dépasser 255 caractères';
    }
    
    if (empty($filiere)) {
        $errors['filiere'] = 'La filière est requise';
    }
    
    if (empty($niveau)) {
        $errors['niveau'] = 'Le niveau est requis';
    }
    
    if (empty($type_document)) {
        $errors['type_document'] = 'Le type de document est requis';
    }
    
    // Validation du fichier
    if (!isset($_FILES['documents']) || $_FILES['documents']['error'] !== UPLOAD_ERR_OK) {
        $errors['file'] = 'Veuillez sélectionner un fichier valide';
    } else {
        $file = $_FILES['documents'];
        $allowed_types = ['pdf', 'doc', 'docx', 'ppt', 'pptx', 'txt'];
        $max_size = 10 * 1024 * 1024; // 10MB
        
        $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        
        if (!in_array($file_extension, $allowed_types)) {
            $errors['file'] = 'Type de fichier non autorisé. Formats acceptés : ' . implode(', ', $allowed_types);
        } elseif ($file['size'] > $max_size) {
            $errors['file'] = 'Le fichier est trop volumineux (maximum 10MB)';
        }
    }
    
    // Si pas d'erreurs, traitement de l'upload
    if (empty($errors)) {
        try {
            // Génération d'un nom de fichier unique
            $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $unique_filename = uniqid() . '_' . time() . '.' . $file_extension;
            $upload_path = 'uploads/' . $unique_filename;
            
            // Déplacement du fichier
            if (move_uploaded_file($file['tmp_name'], $upload_path)) {
                // Préparation des données pour la base
                $document_data = [
                    'user_id' => $_SESSION['user_id'],
                    'title' => $title,
                    'description' => $description,
                    'filename' => $unique_filename,
                    'original_filename' => $file['name'],
                    'file_size' => $file['size'],
                    'file_type' => $file_extension,
                    'filiere' => $filiere,
                    'niveau' => $niveau,
                    'matiere' => $matiere ?: null,
                    'type_document' => $type_document
                ];
                
                // Insertion en base de données
                addDocument($document_data);
                
                $success = true;
                $_SESSION['upload_success'] = true;
                
                // Redirection vers la banque d'épreuves
                header('Location: bank.php?uploaded=1');
                exit;
                
            } else {
                $errors['file'] = 'Erreur lors du téléchargement du fichier';
            }
        } catch (Exception $e) {
            $errors['database'] = 'Erreur lors de l\'enregistrement. Veuillez réessayer.';
            error_log("Erreur upload document : " . $e->getMessage());
        }
    }
}

// Protection CSRF
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$page_title = "Partager un Document";
include 'includes/header.php';
?>

<main class="main-content">
    <div class="form-page">
        <div class="container">
            <div class="form-container" style="max-width: 600px;">
                <div class="form-header">
                    <h1><i class="fas fa-upload"></i> Partager un Document</h1>
                    <p>Contribuez à la banque d'épreuves en partageant vos ressources</p>
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

                <form method="POST" action="" enctype="multipart/form-data" id="uploadForm">
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                    
                    <!-- Titre -->
                    <div class="form-group">
                        <label for="title" class="form-label">
                            <i class="fas fa-heading"></i> Titre du document *
                        </label>
                        <input 
                            type="text" 
                            id="title" 
                            name="title" 
                            class="form-input <?php echo isset($errors['title']) ? 'error' : ''; ?>"
                            value="<?php echo htmlspecialchars(isset($title) ? $title : ''); ?>"
                            required
                            placeholder="Ex: Examen Final Mathématiques L2 - Janvier 2024"
                        >
                        <div class="form-error <?php echo isset($errors['title']) ? 'show' : ''; ?>">
                            <?php echo isset($errors['title']) ? $errors['title'] : ''; ?>
                        </div>
                    </div>

                    <!-- Description -->
                    <div class="form-group">
                        <label for="description" class="form-label">
                            <i class="fas fa-align-left"></i> Description
                        </label>
                        <textarea 
                            id="description" 
                            name="description" 
                            class="form-input"
                            rows="4"
                            placeholder="Décrivez brièvement le contenu du document..."
                        ><?php echo htmlspecialchars(isset($description) ? $description : ''); ?></textarea>
                    </div>

                    <!-- Informations académiques -->
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
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
                                <option value="">Choisissez une filière</option>
                                <option value="informatique" <?php echo (isset($filiere) ? $filiere : '') === 'informatique' ? 'selected' : ''; ?>>Informatique</option>
                                <option value="gestion" <?php echo (isset($filiere) ? $filiere : '') === 'gestion' ? 'selected' : ''; ?>>Gestion</option>
                                <option value="economie" <?php echo (isset($filiere) ? $filiere : '') === 'economie' ? 'selected' : ''; ?>>Économie</option>
                                <option value="droit" <?php echo (isset($filiere) ? $filiere : '') === 'droit' ? 'selected' : ''; ?>>Droit</option>
                                <option value="medecine" <?php echo (isset($filiere) ? $filiere : '') === 'medecine' ? 'selected' : ''; ?>>Médecine</option>
                                <option value="ingenierie" <?php echo (isset($filiere) ? $filiere : '') === 'ingenierie' ? 'selected' : ''; ?>>Ingénierie</option>
                                <option value="lettres" <?php echo (isset($filiere) ? $filiere : '') === 'lettres' ? 'selected' : ''; ?>>Lettres et Sciences Humaines</option>
                                <option value="sciences" <?php echo (isset($filiere) ? $filiere : '') === 'sciences' ? 'selected' : ''; ?>>Sciences</option>
                            </select>
                            <div class="form-error <?php echo isset($errors['filiere']) ? 'show' : ''; ?>">
                                <?php echo isset($errors['filiere']) ? $errors['filiere'] : ''; ?>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="niveau" class="form-label">
                                <i class="fas fa-layer-group"></i> Niveau *
                            </label>
                            <select 
                                id="niveau" 
                                name="niveau" 
                                class="form-select <?php echo isset($errors['niveau']) ? 'error' : ''; ?>"
                                required
                            >
                                <option value="">Choisissez un niveau</option>
                                <option value="L1" <?php echo (isset($niveau) ? $niveau : '') === 'L1' ? 'selected' : ''; ?>>Licence 1ère année (L1)</option>
                                <option value="L2" <?php echo (isset($niveau) ? $niveau : '') === 'L2' ? 'selected' : ''; ?>>Licence 2ème année (L2)</option>
                                <option value="L3" <?php echo (isset($niveau) ? $niveau : '') === 'L3' ? 'selected' : ''; ?>>Licence 3ème année (L3)</option>
                                <option value="M1" <?php echo (isset($niveau) ? $niveau : '') === 'M1' ? 'selected' : ''; ?>>Master 1ère année (M1)</option>
                                <option value="M2" <?php echo (isset($niveau) ? $niveau : '') === 'M2' ? 'selected' : ''; ?>>Master 2ème année (M2)</option>
                                <option value="Doctorat" <?php echo (isset($niveau) ? $niveau : '') === 'Doctorat' ? 'selected' : ''; ?>>Doctorat</option>
                            </select>
                            <div class="form-error <?php echo isset($errors['niveau']) ? 'show' : ''; ?>">
                                <?php echo isset($errors['niveau']) ? $errors['niveau'] : ''; ?>
                            </div>
                        </div>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                        <div class="form-group">
                            <label for="matiere" class="form-label">
                                <i class="fas fa-book"></i> Matière
                            </label>
                            <input 
                                type="text" 
                                id="matiere" 
                                name="matiere" 
                                class="form-input"
                                value="<?php echo htmlspecialchars(isset($matiere) ? $matiere : ''); ?>"
                                placeholder="Ex: Mathématiques, Algorithmique..."
                            >
                        </div>

                        <div class="form-group">
                            <label for="type_document" class="form-label">
                                <i class="fas fa-file-alt"></i> Type de document *
                            </label>
                            <select 
                                id="type_document" 
                                name="type_document" 
                                class="form-select <?php echo isset($errors['type_document']) ? 'error' : ''; ?>"
                                required
                            >
                                <option value="">Choisissez un type</option>
                                <option value="examen" <?php echo (isset($type_document) ? $type_document : '') === 'examen' ? 'selected' : ''; ?>>Examen</option>
                                <option value="cours" <?php echo (isset($type_document) ? $type_document : '') === 'cours' ? 'selected' : ''; ?>>Cours</option>
                                <option value="td" <?php echo (isset($type_document) ? $type_document : '') === 'td' ? 'selected' : ''; ?>>TD (Travaux Dirigés)</option>
                                <option value="tp" <?php echo (isset($type_document) ? $type_document : '') === 'tp' ? 'selected' : ''; ?>>TP (Travaux Pratiques)</option>
                                <option value="autre" <?php echo (isset($type_document) ? $type_document : '') === 'autre' ? 'selected' : ''; ?>>Autre</option>
                            </select>
                            <div class="form-error <?php echo isset($errors['type_document']) ? 'show' : ''; ?>">
                                <?php echo isset($errors['type_document']) ? $errors['type_document'] : ''; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Upload de fichier -->
                    <div class="form-group">
                        <label for="documents" class="form-label">
                            <i class="fas fa-paperclip"></i> Fichier *
                        </label>
                        <div style="border: 2px dashed var(--border-color); border-radius: 10px; padding: 2rem; text-align: center; background: #f8f9fa;">
                            <input 
                                type="file" 
                                id="documents" 
                                name="documents" 
                                class="form-input"
                                accept=".pdf,.doc,.docx,.ppt,.pptx,.txt"
                                required
                                style="display: none;"
                                onchange="updateFileName(this)"
                            >
                            <div id="file-drop-zone" onclick="document.getElementById('documents').click()" style="cursor: pointer;">
                                <div style="font-size: 3rem; color: var(--primary-color); margin-bottom: 1rem;">
                                    <i class="fas fa-cloud-upload-alt"></i>
                                </div>
                                <p style="margin: 0; color: var(--text-dark); font-weight: 600;">
                                    Cliquez pour sélectionner un fichier
                                </p>
                                <p style="margin: 0.5rem 0 0 0; color: var(--text-light); font-size: 0.9rem;">
                                    Formats acceptés : PDF, DOC, DOCX, PPT, PPTX, TXT (max 10MB)
                                </p>
                            </div>
                            <div id="file-info" style="display: none; margin-top: 1rem; padding: 1rem; background: white; border-radius: 5px;">
                                <i class="fas fa-file"></i> <span id="file-name"></span>
                                <button type="button" onclick="clearFile()" style="float: right; background: none; border: none; color: #f44336; cursor: pointer;">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </div>
                        <div class="form-error <?php echo isset($errors['file']) ? 'show' : ''; ?>">
                            <?php echo isset($errors['file']) ? $errors['file'] : ''; ?>
                        </div>
                    </div>

                    <!-- Conditions -->
                    <div class="form-checkbox">
                        <input type="checkbox" id="terms" name="terms" required>
                        <label for="terms">
                            Je certifie que ce document ne viole aucun droit d'auteur et que je suis autorisé(e) à le partager *
                        </label>
                    </div>

                    <!-- Boutons -->
                    <button type="submit" class="form-submit">
                        <i class="fas fa-upload"></i> Partager le document
                    </button>

                    <div class="form-links">
                        <a href="bank.php">← Retour à la banque d'épreuves</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</main>

<?php include 'includes/footer.php'; ?>

<script>
function updateFileName(input) {
    const fileInfo = document.getElementById('file-info');
    const fileName = document.getElementById('file-name');
    const dropZone = document.getElementById('file-drop-zone');
    
    if (input.files.length > 0) {
        const file = input.files[0];
        fileName.textContent = file.name + ' (' + formatFileSize(file.size) + ')';
        fileInfo.style.display = 'block';
        dropZone.style.display = 'none';
    }
}

function clearFile() {
    const input = document.getElementById('documents');
    const fileInfo = document.getElementById('file-info');
    const dropZone = document.getElementById('file-drop-zone');
    
    input.value = '';
    fileInfo.style.display = 'none';
    dropZone.style.display = 'block';
}

function formatFileSize(bytes) {
    if (bytes >= 1048576) {
        return Math.round(bytes / 1048576 * 100) / 100 + ' MB';
    } else if (bytes >= 1024) {
        return Math.round(bytes / 1024 * 100) / 100 + ' KB';
    } else {
        return bytes + ' B';
    }
}

// Drag and drop
document.addEventListener('DOMContentLoaded', function() {
    const dropZone = document.getElementById('file-drop-zone');
    const fileInput = document.getElementById('documents');
    
    ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
        dropZone.addEventListener(eventName, preventDefaults, false);
    });
    
    function preventDefaults(e) {
        e.preventDefault();
        e.stopPropagation();
    }
    
    ['dragenter', 'dragover'].forEach(eventName => {
        dropZone.addEventListener(eventName, highlight, false);
    });
    
    ['dragleave', 'drop'].forEach(eventName => {
        dropZone.addEventListener(eventName, unhighlight, false);
    });
    
    function highlight(e) {
        dropZone.style.borderColor = 'var(--primary-color)';
        dropZone.style.backgroundColor = '#f0f8f0';
    }
    
    function unhighlight(e) {
        dropZone.style.borderColor = 'var(--border-color)';
        dropZone.style.backgroundColor = '#f8f9fa';
    }
    
    dropZone.addEventListener('drop', handleDrop, false);
    
    function handleDrop(e) {
        const dt = e.dataTransfer;
        const files = dt.files;
        
        if (files.length > 0) {
            fileInput.files = files;
            updateFileName(fileInput);
        }
    }
});
</script>
