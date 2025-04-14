<?php
session_start();
require_once '../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Vérification que tous les champs requis sont présents
    $required_fields = ['nom', 'username', 'email', 'password', 'password_confirm', 'telephone', 'genre'];
    foreach ($required_fields as $field) {
        if (empty($_POST[$field])) {
            die("Le champ $field est requis");
        }
    }

    $password = $_POST['password']; // Définir la variable avant les vérifications

    // Vérification de la correspondance des mots de passe
    if ($_POST['password'] !== $_POST['password_confirm']) {
        die("Les mots de passe ne correspondent pas");
    }

    // Exigences minimales :
    if (strlen($password) < 8) {
        die("Le mot de passe doit contenir au moins 8 caractères");
    }
    if (!preg_match('/[A-Z]/', $password)) {
        die("Le mot de passe doit contenir au moins une majuscule");
    }
    if (!preg_match('/[a-z]/', $password)) {
        die("Le mot de passe doit contenir au moins une minuscule");
    }
    if (!preg_match('/[0-9]/', $password)) {
        die("Le mot de passe doit contenir au moins un chiffre");
    }
    if (!preg_match('/[\W]/', $password)) { // Correction ici
        die("Le mot de passe doit contenir au moins un caractère spécial");
    }
    // Récupération des données
    $nom = $_POST['nom'];
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $telephone = $_POST['telephone'];
    $genre = $_POST['genre'];
    // Insertion dans la BDD
    try {
        $stmt = $pdo->prepare("INSERT INTO users (nom, username, email, password, telephone, genre) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$nom, $username, $email, $password, $telephone, $genre]);
        // Création de la session
        $_SESSION['user_id'] = $pdo->lastInsertId();
        $_SESSION['user_email'] = $email;
        header('Location: /ProjetWeb/finalprojectdev/accueil.html');
        exit;
    } catch (PDOException $e) {
        die("Erreur: " . $e->getMessage());
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Formulaire d'inscription</title>
    <link rel="stylesheet" href="/ProjetWeb/assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>
    <div class="container">
        <div class="title">Inscription</div>
        <form method="POST" action="inscription.php">
            <div class="user-details">
                <div class="input-box">
                    <span class="details">Nom</span>
                    <input type="text" name="nom" placeholder="Entrez votre nom" required>
                </div>
                <div class="input-box">
                    <span class="details">Nom d'utilisateur</span>
                    <input type="text" name="username" placeholder="Choisissez un nom d'utilisateur" required>
                </div>
                <div class="input-box">
                    <span class="details">Email</span>
                    <input type="email" name="email" placeholder="Entrez votre email" required>
                </div>
                <div class="input-box">
                    <span class="details">Téléphone</span>
                    <input type="text" name="telephone" placeholder="Entrez votre numéro" required>
                </div>
                <div class="input-box">
                    <span class="details">Mot de passe</span>
                    <input type="password" name="password" id="password" placeholder="Entrez votre mot de passe" required>
                    <div class="password-strength">
                        <div class="password-strength-fill"></div>
                    </div>
                    <small class="password-rules">
                        Doit contenir : 8 caractères, majuscule, minuscule, chiffre et caractère spécial
                    </small>
                </div>
                <div class="input-box">
                    <span class="details">Confirmez le mot de passe</span>
                    <input type="password" name="password_confirm" id="password_confirm" placeholder="Confirmez votre mot de passe" required>
                </div>
            </div>
            <div class="gender-details">
                <span class="gender-title">Genre</span>
                <div class="category">
                    <label for="dot-1">
                        <span class="dot one"></span>
                        <input type="radio" name="genre" value="homme" id="dot-1" required>
                        <span class="gender">Homme</span>
                    </label> 
                    <label for="dot-2">
                        <span class="dot two"></span>
                        <input type="radio" name="genre" value="femme" id="dot-2" required>
                        <span class="gender">Femme</span>
                    </label>
                    <label for="dot-3">
                        <span class="dot three"></span>
                        <input type="radio" name="genre" value="non_precise" id="dot-3" required>
                        <span class="gender">Ne se prononce pas</span>
                    </label>        
                </div>
            </div>
            <div class="button">
                <input type="submit" value="Inscription">
            </div>
        </form>
    </div>
<script>
document.querySelector('form').addEventListener('submit', function(e) {
    const password = document.getElementById('password');
    const confirm = document.getElementById('password_confirm');
    
    if (password.value !== confirm.value) {
        e.preventDefault();
        showPopup('Les mots de passe ne correspondent pas !');
    }
});

document.getElementById('password').addEventListener('input', function() {
    const password = this.value;
    const strengthElement = document.querySelector('.password-strength-fill');
    
    // Calcul de la force (0-4)
    let strength = 0;
    if (password.length >= 8) strength++;
    if (/[A-Z]/.test(password)) strength++;
    if (/[a-z]/.test(password)) strength++;
    if (/[0-9]/.test(password)) strength++;
    if (/[\W]/.test(password)) strength++;
    
    // Mise à jour visuelle
    strengthElement.style.width = `${strength * 20}%`;
    
    // Changement de couleur
    const colors = ['red', 'orange', 'orange', 'yellowgreen', 'green'];
    strengthElement.style.backgroundColor = colors[strength - 1] || 'red';

    // Vérification des contraintes et affichage du popup
    let message = '';
    if (password.length > 0) {
        if (password.length < 8) message = 'Le mot de passe doit contenir au moins 8 caractères';
        else if (!/[A-Z]/.test(password)) message = 'Le mot de passe doit contenir au moins une majuscule';
        else if (!/[a-z]/.test(password)) message = 'Le mot de passe doit contenir au moins une minuscule';
        else if (!/[0-9]/.test(password)) message = 'Le mot de passe doit contenir au moins un chiffre';
        else if (!/[\W]/.test(password)) message = 'Le mot de passe doit contenir au moins un caractère spécial';
        
        if (message) {
            showPopup(message);
        }
    }
});

// Fonction pour gérer l'affichage du popup
function showPopup(message) {
    // Supprime l'ancien popup s'il existe
    const existingPopup = document.getElementById('passwordPopup');
    if (existingPopup) {
        existingPopup.remove();
    }

    // Crée le nouveau popup
    const popup = document.createElement('div');
    popup.id = 'passwordPopup';
    popup.style.cssText = `
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
        animation: fadeIn 0.3s ease-out;
    `;
    popup.textContent = message;

    document.body.appendChild(popup);

    // Supprime le popup après 3 secondes
    setTimeout(() => {
        popup.style.animation = 'fadeOut 0.3s ease-out';
        setTimeout(() => popup.remove(), 300);
    }, 3000);
}

// Ajoute les animations CSS nécessaires
const style = document.createElement('style');
style.textContent = `
    @keyframes fadeIn {
        from { opacity: 0; transform: translate(-50%, -20px); }
        to { opacity: 1; transform: translate(-50%, 0); }
    }
    @keyframes fadeOut {
        from { opacity: 1; transform: translate(-50%, 0); }
        to { opacity: 0; transform: translate(-50%, -20px); }
    }
`;
document.head.appendChild(style);
</script>
</html>
