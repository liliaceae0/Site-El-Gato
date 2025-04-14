<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
require_once '../includes/db.php'; 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupération des données du formulaire
    $nom = $_POST['nom'];
    $username = $_POST['username'];!
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT); 
    $telephone = $_POST['telephone'];
    $genre = $_POST['genre'];

    // Insertion dans la BDD
    try {
        $stmt = $pdo->prepare("
            INSERT INTO users (nom, username, email, password, telephone, genre) 
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([$nom, $username, $email, $password, $telephone, $genre]);

        // Création de la session utilisateur
        $_SESSION['user_id'] = $pdo->lastInsertId();
        $_SESSION['user_email'] = $email;

        // Redirection
        header('Location: ../commande.php');
        exit;
        
    } catch (PDOException $e) {
        die("Erreur DB : " . $e->getMessage());
    }
}

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Formulaire d'inscription</title>
    <link rel="stylesheet" href="/assets/css/style.css">
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
                    <input type="username" name="username" placeholder="Entrez votre nom d'utilisateur" required>
                </div>
                <div class="input-box">
                    <span class="details">Email</span>
                    <input type="email" name="email" placeholder="Entrez votre email" required>
                </div>
                <div class="input-box">
                    <span class="details">Téléphone</span>
                    <input type="tel" name="telephone" placeholder="Entrez votre numéro de téléphone" required>
                </div>
                <div class="input-box">
                    <span class="details">Mot de passe</span>
                    <input type="password" name="password" placeholder="Entrez votre mot de passe" required>
                </div>
                <div class="input-box">
                    <span class="details">Confirmez le mot de passe</span>
                    <input type="password" name="password" placeholder="Confirmez votre mot de passe" required>
                </div>
            </div>
            <div class="gender-details">
                <span class="gender-title">Genre</span>
                <div class="category">
                    <label for="dot-1">
                        <span class="dot one"></span>
                        <input type="radio" name="genre" id="dot-1">
                        <span class="gender">Homme</span>
                    </label> 
                    <label for="dot-2">
                        <span class="dot two"></span>
                        <input type="radio" name="genre" id="dot-2">
                        <span class="gender">Femme</span>
                    </label>
                    <label for="dot-3">
                        <span class="dot three"></span>
                        <input type="radio" name="genre" id="dot-3">
                        <span class="gender">Ne se prononce pas</span>
                    </label>        
                </div>
            </div>
            <div class="button">
                <input type="submit" value="Inscription">
            </div>
        </form>
    </div>
</body>
</html>
