<?php
session_start();
require_once '../includes/db.php';

// Si c'est une requête POST, ça veut dire qu'on veut modifier le panier
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $data = json_decode(file_get_contents('php://input'), true);
        $response = ['success' => true];

        // Je vérifie quelle action on veut faire (changer quantité, supprimer, ajouter)
        switch ($data['action']) {
            case 'update':
                // Vérif si on a bien reçu ce qu'il faut
                if (!isset($data['product_id']) || !isset($data['change'])) {
                    throw new Exception('Oups, il manque des infos !');
                }

                // Si l'utilisateur est connecté, on modifie dans la base de données
                if (isset($_SESSION['user_id'])) {
                    $stmt = $pdo->prepare("
                        UPDATE panier 
                        SET quantite = quantite + ? 
                        WHERE user_id = ? AND produit_id = ?
                    ");
                    $result = $stmt->execute([$data['change'], $_SESSION['user_id'], $data['product_id']]);
                    
                    // Si la quantité devient 0 ou moins, on supprime l'article
                    $stmt = $pdo->prepare("
                        DELETE FROM panier 
                        WHERE user_id = ? AND produit_id = ? AND quantite <= 0
                    ");
                    $stmt->execute([$_SESSION['user_id'], $data['product_id']]);
                } else {
                    // Si pas connecté, on modifie dans la session
                    if (!isset($_SESSION['panier'])) {
                        throw new Exception('Pas de panier trouvé !');
                    }
                    
                    // On parcourt le panier pour trouver le bon produit
                    foreach ($_SESSION['panier'] as $key => &$item) {
                        if ($item['produit_id'] == $data['product_id']) {
                            $item['quantite'] += $data['change'];
                            // Si quantité à 0, on vire le produit
                            if ($item['quantite'] <= 0) {
                                unset($_SESSION['panier'][$key]);
                                $_SESSION['panier'] = array_values($_SESSION['panier']); // On réorganise les index
                            }
                            break;
                        }
                    }
                }
                break;

            case 'remove':
                // Vérif qu'on a bien l'ID du produit à supprimer
                if (!isset($data['product_id'])) {
                    throw new Exception('Il me faut l\'ID du produit !');
                }

                // Si connecté, on supprime de la BDD
                if (isset($_SESSION['user_id'])) {
                    $stmt = $pdo->prepare("
                        DELETE FROM panier 
                        WHERE user_id = ? AND produit_id = ?
                    ");
                    $stmt->execute([$_SESSION['user_id'], $data['product_id']]);
                } else {
                    // Sinon on supprime de la session
                    if (isset($_SESSION['panier'])) {
                        foreach ($_SESSION['panier'] as $key => $item) {
                            if ($item['produit_id'] == $data['product_id']) {
                                unset($_SESSION['panier'][$key]);
                                $_SESSION['panier'] = array_values($_SESSION['panier']);
                                break;
                            }
                        }
                    }
                }
                break;

            case 'add':
                // Pour ajouter, il me faut l'ID du produit et la quantité
                if (!isset($data['product_id']) || !isset($data['quantity'])) {
                    throw new Exception('Il manque des infos pour ajouter au panier !');
                }

                // Si connecté, on ajoute dans la BDD
                if (isset($_SESSION['user_id'])) {
                    // D'abord on regarde si le produit est déjà dans le panier
                    $stmt = $pdo->prepare("
                        SELECT id, quantite 
                        FROM panier 
                        WHERE user_id = ? AND produit_id = ?
                    ");
                    $stmt->execute([$_SESSION['user_id'], $data['product_id']]);
                    $existingItem = $stmt->fetch();

                    if ($existingItem) {
                        // Si oui, on augmente juste la quantité
                        $stmt = $pdo->prepare("
                            UPDATE panier 
                            SET quantite = quantite + ? 
                            WHERE id = ?
                        ");
                        $stmt->execute([$data['quantity'], $existingItem['id']]);
                    } else {
                        // Sinon on crée une nouvelle ligne
                        $stmt = $pdo->prepare("
                            INSERT INTO panier (user_id, produit_id, quantite) 
                            VALUES (?, ?, ?)
                        ");
                        $stmt->execute([$_SESSION['user_id'], $data['product_id'], $data['quantity']]);
                    }
                } else {
                    // Si pas connecté, on utilise la session
                    if (!isset($_SESSION['panier'])) {
                        $_SESSION['panier'] = [];
                    }
                    
                    // On cherche si le produit est déjà dans le panier
                    $found = false;
                    foreach ($_SESSION['panier'] as &$item) {
                        if ($item['produit_id'] == $data['product_id']) {
                            $item['quantite'] += $data['quantity'];
                            $found = true;
                            break;
                        }
                    }
                    
                    // Si pas trouvé, on l'ajoute
                    if (!$found) {
                        $_SESSION['panier'][] = [
                            'produit_id' => $data['product_id'],
                            'quantite' => $data['quantity']
                        ];
                    }
                }
                break;

            default:
                throw new Exception('Je ne connais pas cette action !');
        }

        echo json_encode($response);
    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
    exit;
}

// Si c'est une requête GET, on affiche juste le contenu du panier
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        $items = [];
        $response = [
            'loggedIn' => isset($_SESSION['user_id']),
            'username' => isset($_SESSION['username']) ? $_SESSION['username'] : null,
            'items' => []
        ];

        // Si l'utilisateur est connecté, on va chercher dans la BDD
        if (isset($_SESSION['user_id'])) {
            $stmt = $pdo->prepare("
                SELECT 
                    p.id as produit_id,
                    p.nom as name,
                    p.prix as price,
                    p.image_path as image,
                    c.quantite as quantity
                FROM panier c
                JOIN produits p ON c.produit_id = p.id
                WHERE c.user_id = ?
                ORDER BY p.id ASC
            ");
            $stmt->execute([$_SESSION['user_id']]);
            $response['items'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $response['username'] = $_SESSION['username'];
        } 
        // Sinon on prend ce qu'il y a dans la session
        else if (isset($_SESSION['panier'])) {
            foreach ($_SESSION['panier'] as $item) {
                $stmt = $pdo->prepare("
                    SELECT 
                        id as produit_id, 
                        nom as name, 
                        prix as price, 
                        image_path as image 
                    FROM produits 
                    WHERE id = ?
                ");
                $stmt->execute([$item['produit_id']]);
                $product = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($product) {
                    $product['quantity'] = $item['quantite'];
                    $response['items'][] = $product;
                }
            }
        }

        echo json_encode($response);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => $e->getMessage()]);
    }
    exit;
}
