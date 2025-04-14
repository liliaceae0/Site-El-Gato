<?php
session_start();
require_once '../includes/db.php';

// Ajout de la déconnexion
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    session_destroy();
    header('Location: connexion.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT id, password, email, username FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['username'] = $user['username'];
        header('Location: /ProjetWeb/finalprojectdev/accueil.html');
        exit;
    } else {
        $error = "Email ou mot de passe incorrect";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - El Gato</title>
    <link rel="stylesheet" href="/ProjetWeb/assets/css/style2.css">
</head>
<body>
    <div class="container">
        <div class="title">Connexion</div>
        <?php if (isset($error)): ?>
            <div class="error-message"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <form method="POST">
            <div class="user-details">
                <div class="input-box">
                    <span class="details">Email</span>
                    <input type="email" name="email" required placeholder="Votre email">
                </div>
                <div class="input-box">
                    <span class="details">Mot de passe</span>
                    <input type="password" name="password" required placeholder="Votre mot de passe">
                </div>
            </div>
            <div class="button">
                <input type="submit" value="Se connecter">
            </div>
            <div class="new-customer">
                Pas encore de compte ? <a href="inscription.php">Créer un compte</a>
            </div>
        </form>
    </div>
</body>
</html>
