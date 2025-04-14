<?php
session_start();
require_once 'db.php';

// Fonction pour ajouter un produit au panier
function ajouterAuPanier($produit_id, $quantite = 1) {
    global $pdo;
    
    // Si l'utilisateur est connecté
    if (isset($_SESSION['user_id'])) {
        $user_id = $_SESSION['user_id'];
        
        // Vérifier si le produit est déjà dans le panier
        $stmt = $pdo->prepare("SELECT * FROM panier WHERE user_id = ? AND produit_id = ?");
        $stmt->execute([$user_id, $produit_id]);
        $panier_item = $stmt->fetch();
        
        if ($panier_item) {
            // Mettre à jour la quantité
            $nouvelle_quantite = $panier_item['quantite'] + $quantite;
            $stmt = $pdo->prepare("UPDATE panier SET quantite = ? WHERE id = ?");
            $stmt->execute([$nouvelle_quantite, $panier_item['id']]);
        } else {
            // Ajouter un nouveau produit
            $stmt = $pdo->prepare("INSERT INTO panier (user_id, produit_id, quantite) VALUES (?, ?, ?)");
            $stmt->execute([$user_id, $produit_id, $quantite]);
        }
    } else {
        // Si l'utilisateur n'est pas connecté, utiliser la session
        if (!isset($_SESSION['panier'])) {
            $_SESSION['panier'] = [];
        }
        
        $produit_existe = false;
        
        // Vérifier si le produit existe déjà dans le panier
        foreach ($_SESSION['panier'] as &$item) {
            if ($item['produit_id'] == $produit_id) {
                $item['quantite'] += $quantite;
                $produit_existe = true;
                break;
            }
        }
        
        // Si le produit n'existe pas, l'ajouter
        if (!$produit_existe) {
            $_SESSION['panier'][] = [
                'produit_id' => $produit_id,
                'quantite' => $quantite
            ];
        }
    }
    
    return true;
}

// Fonction pour mettre à jour la quantité
function modifierQuantite($panier_id, $quantite) {
    global $pdo;
    
    if (isset($_SESSION['user_id'])) {
        $user_id = $_SESSION['user_id'];
        
        $stmt = $pdo->prepare("UPDATE panier SET quantite = ? WHERE id = ? AND user_id = ?");
        $stmt->execute([$quantite, $panier_id, $user_id]);
    } else {
        foreach ($_SESSION['panier'] as &$item) {
            if ($item['id'] == $panier_id) {
                $item['quantite'] = $quantite;
                break;
            }
        }
    }
    
    return true;
}

// Fonction pour supprimer un produit du panier
function supprimerDuPanier($panier_id) {
    global $pdo;
    
    if (isset($_SESSION['user_id'])) {
        $user_id = $_SESSION['user_id'];
        
        $stmt = $pdo->prepare("DELETE FROM panier WHERE id = ? AND user_id = ?");
        $stmt->execute([$panier_id, $user_id]);
    } else {
        foreach ($_SESSION['panier'] as $key => $item) {
            if ($item['id'] == $panier_id) {
                unset($_SESSION['panier'][$key]);
                $_SESSION['panier'] = array_values($_SESSION['panier']); // Réindexer le tableau
                break;
            }
        }
    }
    
    return true;
}

// Fonction pour obtenir les produits du panier
function getPanierProduits() {
    global $pdo;
    
    if (isset($_SESSION['user_id'])) {
        $user_id = $_SESSION['user_id'];
        
        $stmt = $pdo->prepare("
            SELECT p.id as panier_id, pr.id as produit_id, pr.nom, pr.prix, pr.image_path, p.quantite 
            FROM panier p 
            JOIN produits pr ON p.produit_id = pr.id 
            WHERE p.user_id = ?
        ");
        $stmt->execute([$user_id]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } else {
        if (!isset($_SESSION['panier']) || empty($_SESSION['panier'])) {
            return [];
        }
        
        $produits = [];
        
        foreach ($_SESSION['panier'] as $key => $item) {
            $stmt = $pdo->prepare("SELECT * FROM produits WHERE id = ?");
            $stmt->execute([$item['produit_id']]);
            $produit = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($produit) {
                $produits[] = [
                    'panier_id' => $key, // Use array key as unique identifier
                    'produit_id' => $produit['id'],
                    'nom' => $produit['nom'],
                    'prix' => $produit['prix'],
                    'image_path' => $produit['image_path'],
                    'quantite' => $item['quantite']
                ];
            }
        }
        
        return $produits;
    }
}

// Gestion des requêtes AJAX ou formulaires
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Ajouter au panier
    if (isset($_POST['action']) && $_POST['action'] === 'ajouter') {
        $produit_id = $_POST['produit_id'];
        $quantite = isset($_POST['quantite']) ? (int)$_POST['quantite'] : 1;
        
        if (ajouterAuPanier($produit_id, $quantite)) {
            header('Location: ../pages/panier.php?success=added');
        } else {
            header('Location: ../pages/panier.php?error=failed');
        }
        exit;
    }
    
    // Mettre à jour la quantité
    if (isset($_POST['action']) && $_POST['action'] === 'modifier') {
        $panier_id = $_POST['panier_id'];
        $quantite = (int)$_POST['quantite'];
        
        if ($quantite > 0) {
            if (modifierQuantite($panier_id, $quantite)) {
                header('Location: ../pages/panier.php?success=updated');
            } else {
                header('Location: ../pages/panier.php?error=failed');
            }
        } else {
            supprimerDuPanier($panier_id);
            header('Location: ../pages/panier.php?success=removed');
        }
        exit;
    }
    
    // Supprimer du panier
    if (isset($_POST['action']) && $_POST['action'] === 'supprimer') {
        $panier_id = $_POST['panier_id'];
        
        if (supprimerDuPanier($panier_id)) {
            header('Location: ../pages/panier.php?success=removed');
        } else {
            header('Location: ../pages/panier.php?error=failed');
        }
        exit;
    }
}
?>