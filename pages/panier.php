<?php
session_start();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon Panier - El Gato</title>
    <link href="https://fonts.googleapis.com/css2?family=Pacifico&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: "Pacifico", cursive;
            margin: 0;
            padding: 0;
            background: linear-gradient(135deg, #a1c4fd, #c2a5fd);
        }

        h1 {
            text-align: center;
            color: #333;
            margin: 20px 0;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.1);
        }

        nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background-color: #fffeff;
            padding: 1rem 2rem;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        nav img {
            width: 100px;
            height: auto;
        }

        nav ul {
            display: flex;
            list-style: none;
            margin: 0;
            padding: 0;
            gap: 2rem;
        }

        nav a {
            color: #b66e6e;
            text-decoration: none;
            font-size: 1.2rem;
            font-family: Optima, sans-serif;
            transition: color 0.3s ease;
        }

        nav a:hover {
            color: #8b4444;
            text-decoration: underline;
        }

        .cart-container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 20px;
            display: flex;
            gap: 2rem;
        }

        .cart-items {
            flex: 2;
            background: white;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .cart-summary {
            flex: 1;
            background: white;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            height: fit-content;
        }

        .cart-title {
            color: #b66e6e;
            border-bottom: 2px solid #b66e6e;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }

        .cart-item {
            display: flex;
            gap: 20px;
            padding: 15px 0;
            border-bottom: 1px solid #eee;
            transition: transform 0.3s ease;
        }

        .cart-item:hover {
            transform: translateY(-5px);
        }

        .item-image {
            width: 120px;
            height: 120px;
            object-fit: cover;
            border-radius: 8px;
        }

        .item-details {
            flex: 1;
        }

        .item-name {
            color: #333;
            font-size: 1.2rem;
            margin-bottom: 8px;
        }

        .item-price {
            color: #b66e6e;
            font-weight: bold;
            font-size: 1.1rem;
        }

        .quantity-control {
            display: flex;
            align-items: center;
            margin: 10px 0;
            gap: 10px;
        }

        .quantity-btn {
            background: #b66e6e;
            color: white;
            border: none;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            cursor: pointer;
            font-size: 1.2rem;
            transition: background-color 0.3s ease;
        }

        .quantity-btn:hover {
            background: #8b4444;
        }

        .quantity-input {
            width: 50px;
            text-align: center;
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 5px;
        }

        .remove-btn {
            background: none;
            border: none;
            color: #e74c3c;
            cursor: pointer;
            font-family: "Pacifico", cursive;
            font-size: 1rem;
            padding: 5px 10px;
            transition: color 0.3s ease;
        }

        .remove-btn:hover {
            color: #c0392b;
        }

        .thank-you {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-style: italic;
            color: #666;
        }

        .checkout-btn {
            background: #295ae0;
            color: white;
            border: none;
            padding: 12px;
            width: 100%;
            border-radius: 25px;
            font-family: "Pacifico", cursive;
            font-size: 1.1rem;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .checkout-btn:hover {
            background: #124992;
        }

        .empty-cart {
            text-align: center;
            padding: 50px 0;
            color: #666;
        }

        .continue-shopping {
            display: inline-block;
            margin-top: 20px;
            color: #295ae0;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s ease;
        }

        .continue-shopping:hover {
            color: #124992;
        }

        footer {
            background-color: #333;
            color: white;
            text-align: center;
            padding: 1rem;
            margin-top: 2rem;
        }

        @media (max-width: 768px) {
            .cart-container {
                flex-direction: column;
            }

            nav {
                flex-direction: column;
                padding: 1rem;
            }

            nav ul {
                flex-direction: column;
                align-items: center;
                gap: 1rem;
            }
        }
    </style>
</head>
<body>
    <h1><b>Bienvenue sur El Gato</b></h1>
    
    <header>
        <nav>
            <img src="/ProjetWeb/assets/images/Logo.png" alt="Logo">
            <ul>
                <a href="/ProjetWeb/finalprojectdev/accueil.html">Accueil</a>
                <a href="/ProjetWeb/finalprojectdev/nosproduits.html">Nos produits</a>
                <a href="/ProjetWeb/finalprojectdev/propos.html">À propos</a>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="/ProjetWeb/pages/connexion.php?action=logout">Se déconnecter</a>
                <?php else: ?>
                    <a href="/ProjetWeb/pages/connexion.php">Connexion</a>
                <?php endif; ?>
                <a href="/ProjetWeb/pages/panier.php">Panier</a>
            </ul>
        </nav>
    </header>

    <div class="cart-container">
        <div class="cart-items">
            <h2 class="cart-title">Mon Panier</h2>
            <div id="cart-items-container">
                <div class="empty-cart">
                    <p>Votre panier est vide</p>
                    <a href="/ProjetWeb/finalprojectdev/nosproduits.html" class="continue-shopping">Continuer vos achats</a>
                </div>
            </div>
        </div>

        <div class="cart-summary">
            <div class="thank-you">
                Merci d'avoir choisi notre boutique El Gato ! Votre satisfaction est notre priorité.
            </div>
            
            <h3>Résumé de la commande</h3>
            <div id="order-summary">
                <p>Sous-total: <span id="subtotal">0,00</span> €</p>
                <p>Livraison: Calculée à l'étape suivante</p>
            </div>
            
            <div class="checkout-section">
                <button class="checkout-btn" onclick="proceedToCheckout()">
                    Passer la commande
                </button>
                
                <?php if (!isset($_SESSION['user_id'])): ?>
                    <p class="login-reminder">
                        <a href="connexion.php">Connectez-vous</a> pour enregistrer votre panier
                    </p>
                <?php endif; ?>
            </div>
            
            <div class="auth-status" id="auth-status"></div>
        </div>
    </div>

    <footer>
        <p>&copy; 2025 El Gato - Tous droits réservés</p>
    </footer>

    <script>
// Charger le panier au démarrage
document.addEventListener('DOMContentLoaded', loadCart);

async function loadCart() {
    try {
        const response = await fetch('/ProjetWeb/api/cart.php');
        const data = await response.json();
        
        // Debugging
        console.log('Cart data received:', data);
        
        const container = document.getElementById('cart-items-container');
        const subtotalEl = document.getElementById('subtotal');
        const authStatus = document.getElementById('auth-status');
        
        // Mise à jour du statut d'authentification
        if (data.loggedIn && data.username) {
            authStatus.innerHTML = `Connecté en tant que ${data.username}`;
            document.querySelector('.login-reminder')?.remove(); // Supprime le message de connexion s'il existe
        } else {
            authStatus.innerHTML = 'Non connecté - <a href="connexion.php">Se connecter</a>';
        }
        
        // Affichage des articles
        if (data.items && data.items.length > 0) {
            let html = '';
            let total = 0;
            
            data.items.forEach(item => {
                const itemTotal = parseFloat(item.price) * parseInt(item.quantity);
                total += itemTotal;
                
                html += `
                <div class="cart-item" data-id="${item.produit_id}">
                    <img src="${item.image}" class="item-image" alt="${item.name}">
                    <div class="item-details">
                        <div class="item-name">${item.name}</div>
                        <div class="item-price">${parseFloat(item.price).toFixed(2)} €</div>
                        <div class="quantity-control">
                            <button class="quantity-btn" onclick="updateQuantity(${item.produit_id}, -1)">-</button>
                            <input type="text" class="quantity-input" value="${item.quantity}" readonly>
                            <button class="quantity-btn" onclick="updateQuantity(${item.produit_id}, 1)">+</button>
                        </div>
                        <button class="remove-btn" onclick="removeItem(${item.produit_id})">Supprimer</button>
                    </div>
                </div>`;
            });
            
            container.innerHTML = html;
            subtotalEl.textContent = total.toFixed(2);
        } else {
            container.innerHTML = `
                <div class="empty-cart">
                    <p>Votre panier est vide</p>
                    <a href="/ProjetWeb/finalprojectdev/nosproduits.html" class="continue-shopping">Continuer vos achats</a>
                </div>`;
            subtotalEl.textContent = '0.00';
        }
    } catch (error) {
        console.error('Erreur lors du chargement du panier:', error);
    }
}

// Fonctions pour gérer le panier
async function updateQuantity(itemId, change) {
    try {
        const response = await fetch('/ProjetWeb/api/cart.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                action: 'update',
                product_id: itemId,
                change: change // Envoi du changement de quantité (+1 ou -1)
            })
        });
        
        if (response.ok) {
            const data = await response.json();
            if (data.success) {
                await loadCart(); // Recharger le panier après la maj
            } else {
                alert(data.message || 'Erreur lors de la mise à jour');
            }
        } else {
            alert('Erreur lors de la mise à jour');
        }
    } catch (error) {
        console.error('Erreur:', error);
        alert('Erreur lors de la mise à jour');
    }
}

async function removeItem(itemId) {
    if (confirm('Voulez-vous vraiment supprimer cet article du panier ?')) {
        try {
            const response = await fetch('/ProjetWeb/api/cart.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    action: 'remove',
                    product_id: itemId
                })
            });
            
            if (response.ok) {
                const data = await response.json();
                if (data.success) {
                    await loadCart(); // Recharger le panier après la suppression
                } else {
                    alert(data.message || 'Erreur lors de la suppression');
                }
            } else {
                alert('Erreur lors de la suppression');
            }
        } catch (error) {
            console.error('Erreur:', error);
            alert('Erreur lors de la suppression');
        }
    }
}

function proceedToCheckout() {
    window.location.href = '/ProjetWeb/pages/commande.php';
}
</script>
</body>
</html>
