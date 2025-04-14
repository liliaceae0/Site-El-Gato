<?php
session_start();
require_once '../includes/db.php';

// Redirection si non connecté
if (!isset($_SESSION['user_id'])) {
    header('Location: /ProjetWeb/pages/connexion.php?redirect=commande');
    exit;
}

// Récupération des données utilisateur
$userStmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$userStmt->execute([$_SESSION['user_id']]);
$user = $userStmt->fetch(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Mise à jour des données utilisateur
    $updateStmt = $pdo->prepare("
        UPDATE users SET
            prenom = ?,
            telephone = ?,
            pays = ?,
            ville = ?,
            adresse = ?,
            entreprise = ?
        WHERE id = ?
    ");
    
    $updateStmt->execute([
        $_POST['prenom'],
        $_POST['telephone'],
        $_POST['pays'] ?? null,
        $_POST['ville'] ?? null,
        $_POST['adresse'] ?? null,
        $_POST['entreprise'] ?? null,
        $_SESSION['user_id']
    ]);

    // Création de la commande
    $commandeStmt = $pdo->prepare("
        INSERT INTO commandes (
            user_id,
            mode_commande,
            telephone,
            pays,
            adresse,
            ville,
            entreprise
        ) VALUES (?, ?, ?, ?, ?, ?, ?)
    ");
    
    $commandeStmt->execute([
        $_SESSION['user_id'],
        $_POST['commande'],
        $_POST['telephone'],
        $_POST['pays'] ?? null,
        $_POST['adresse'] ?? null,
        $_POST['ville'] ?? null,
        $_POST['entreprise'] ?? null
    ]);

    $success = "Commande validée et profil mis à jour !";
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Commande El Gato</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">

    <style>
    /* Reset et base */
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }
    
    body {
        font-family: 'Poppins', sans-serif;
        background: linear-gradient(135deg, #8dcdf8, #9b59b6);
        min-height: 100vh;
        display: flex;
        justify-content: center;
        align-items: center;
        background-attachment: fixed; /* Fixe le dégradé */
        padding: 20px;
    }

    .form-container {
        background: white;
        width: 100%;
        max-width: 500px;
        padding: 30px;
        border-radius: 15px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
    }

    h1 {
        color: #9b59b6;
        text-align: center;
        margin-bottom: 30px;
        font-size: 28px;
    }

    /* Styles des champs du formulaire */
    label {
        display: block;
        margin-bottom: 8px;
        color: #9b59b6;
        font-weight: 500;
    }

    .input-group {
        display: flex;
        gap : 15px;
    }

    .input-group .input-box {
    flex: 1;
    }
    
    input, select {
        width: 100%;
        padding: 12px 15px;
        margin-bottom: 20px;
        border: 2px solid #e0e0e0;
        border-radius: 8px;
        font-size: 16px;
        transition: all 0.3s;
    }

    /* Section expédition */
    #expedition-fields {
        margin-top: 25px;
        padding-top: 25px;
        border-top: 1px solid #f0f0f0;
    }

    /* Bouton submit */
    button[type="submit"] {
        background: #9b59b6;
        color: white;
        border: none;
        padding: 15px;
        width: 100%;
        border-radius: 8px;
        font-size: 16px;
        font-weight: 600;
        cursor: pointer;
        transition: background 0.3s;
        margin-top: 10px;
    }

    button[type="submit"]:hover {
        background: #8e44ad;
    }

    .error {
        border: 1px solid red !important;
    }

    .error-message {
        color: red;
        font-size: 0.8rem;
        margin-top: -0.5rem;
        margin-bottom: 0.5rem;
        display: block;
    }

    #popup {
        position: fixed;
        top: 20px;
        left: 50%;
        transform: translateX(-50%);
        background: #9b59b6;
        color: white;
        padding: 1rem 2rem;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        z-index: 1000;
        display: none;
        animation: fadeIn 0.3s ease-out;
    }

    .error-message {
    background: none !important; /* Supprime le fond gris */
    padding: 10px;
    color: red;
    }

    @keyframes fadeIn {
        from { opacity: 0; top: 0; }
        to { opacity: 1; top: 20px; }
    }

    /* Responsive */
    @media (max-width: 600px) {
        .form-container {
            padding: 1.5rem;
            margin: 1rem;
        }
        
        h1 {
            font-size: 1.5rem;
        }
    }

    </style>
    </style>
</head>
<body>

    <div class="form-container">
        <h1>Formulaire de Commande</h1>
        <?php if (isset($error)): ?>
            <div style="color: red;" class="error-message"><?= $error ?></div>
        <?php endif; ?>
        
        <form id="order-form" method="POST">
            
            <label for="commande">Mode de commande :</label>
            <select id="commande" name="commande" required>
                <option value="expedition">Expédition</option>
                <option value="retrait">Retrait en boutique</option>
            </select>
    
            <div id="common-fields">
                <label for="nom">Nom :</label>
                <input type="text" id="nom" name="nom" value="<?= htmlspecialchars($user['nom'] ?? '') ?>">
                
                <label for="prenom">Prénom :</label>
                <input type="text" id="prenom" name="prenom" value="<?= htmlspecialchars($user['prenom'] ?? '') ?>">
                
                <label for="telephone">Téléphone :</label>
                <input type="tel" id="telephone" name="telephone" value="<?= htmlspecialchars($user['telephone'] ?? '') ?>">
                
                <label for="email">Adresse e-mail :</label>
                <input type="email" id="email" name="email" value="<?= htmlspecialchars($user['email'] ?? '') ?>">
            </div>
    
            <div id="expedition-fields">
                <label for="pays">Pays :</label>
                <select id="pays" name="pays">
                    <option value="">-- Sélectionnez --</option>
                    <option value="France">France</option>
                    <option value="Belgique">Belgique</option>
                    <option value="Allemagne">Allemagne</option>
                    <option value="Luxembourg">Luxembourg</option>
                </select>
                
                <label for="adresse">Adresse :</label>
                <input type="text" name="adresse" value="<?= htmlspecialchars($user['adresse'] ?? '') ?>">
                <input type="text" name="ville" value="<?= htmlspecialchars($user['ville'] ?? '') ?>">
                
                <label for="ville">Ville :</label>
                <input type="text" id="ville" name="ville">
                
                <label for="entreprise">Entreprise (optionnel) :</label>
                <input type="text" id="entreprise" name="entreprise">
            </div>
    
            <button type="submit">Valider la commande</button>
        </form>
        
        <?php if (isset($success)): ?>
            <div id="popup" style="display: none; position: fixed; top: 20px; left: 50%; transform: translateX(-50%); background: green; color: white; padding: 15px; border-radius: 5px;">
                <?= $success ?>
            </div>
        <?php endif; ?>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('order-form');
            const commandeSelect = document.getElementById('commande');
            const expeditionFields = document.getElementById('expedition-fields');
            
            // Gestion de l'affichage des champs d'expédition
            commandeSelect.addEventListener('change', function() {
                expeditionFields.style.display = 
                    this.value === 'retrait' ? 'none' : 'block';
            });
            
            // Initialisation
            expeditionFields.style.display = 
                commandeSelect.value === 'retrait' ? 'none' : 'block';
            
            // Validation du formulaire
            form.addEventListener('submit', function(e) {
                let isValid = true;
                
                // Validation des champs requis
                if (commandeSelect.value === 'expedition') {
                    const requiredFields = ['pays', 'adresse', 'ville'];
                    requiredFields.forEach(fieldId => {
                        const field = document.getElementById(fieldId);
                        if (!field.value.trim()) {
                            alert(`Le champ ${fieldId} est obligatoire`);
                            isValid = false;
                        }
                    });
                }
                
                if (isValid && document.getElementById('popup')) {
                    document.getElementById('popup').style.display = 'block';
                    setTimeout(() => {
                        document.getElementById('popup').style.display = 'none';
                    }, 2000);
                }
                
                if (!isValid) {
                    e.preventDefault();
                }
            });
        });
    </script>
</body>
</html>