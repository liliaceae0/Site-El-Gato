<?php
require_once 'includes/db.php';
echo "Connexion réussie ! User MySQL : " . $pdo->query("SELECT USER()")->fetchColumn();
?>