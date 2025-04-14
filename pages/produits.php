<?php
session_start();
require_once '../includes/db.php';
?>

<!DOCTYPE html>
<html lang="fr">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>El Gato - Nos Produits</title>
    <link rel="stylesheet" href="/ProjetWeb/assets/css/style_nosproduits.css" />
    <link href="https://fonts.googleapis.com/css2?family=Pacifico&display=swap" rel="stylesheet">
  </head>
  <body>
    <div>
      <div class="banner"></div>
    </div>
       
    <h1><b>Bienvenue sur El Gato</b></h1>
  
    <header>
      <nav>
          <img src="/ProjetWeb/assets/images/Logo.png" alt="Logo">
          <ul>
              <a href="/ProjetWeb/finalprojectdev/accueil.html">Accueil</a>
              <a href="/ProjetWeb/finalprojectdev/nosproduits.html">Nos produits</a>
              <a href="/ProjetWeb/finalprojectdev/propos.html">À propos</a>
              <a href="/ProjetWeb/pages/commande.php">Commander</a>
              <a href="/ProjetWeb/pages/connexion.php">Connexion</a>
              <a href="/ProjetWeb/pages/panier.php">Panier</a>
          </ul>
      </nav>
    </header>

    <main class="container">
      <section id="products"></section>
    </main>

    <footer>
      <p>&copy; 2025 El Gato - Tous droits réservés</p>
    </footer>

    <div id="popup" class="popup-overlay">
      <div class="popup-content">
        <span class="close-btn">&times;</span>
        <h3>Ajouter au panier</h3>
        <label for="quantity">Quantité :</label>
        <input type="number" id="quantity" min="1" value="1">
        <p id="total-price"></p>
        <button id="confirm-btn">Confirmer</button>
      </div>
    </div>

    <script>
      // Garder ton tableau products original avec les bons chemins d'images
      const products = [
        { src: "/ProjetWeb/assets/images/sachetDEbonbon.jpg", alt: "Sachet de Sucrerie", name: "Sachet de Sucrerie", price: 4.99 },
        { src: "/ProjetWeb/assets/images/Bento1.jpeg", alt: "Bento", name: "Bento", price: 24.99 },
        { src: "/ProjetWeb/assets/images/Bento2.webp", alt: "Bento d'anniversaire", name: "Bento d'anniversaire", price: 24.99 },
        { src: "/ProjetWeb/assets/images/bento3.webp", alt: "Bento Printemps", name: "Bento Printemps", price: 24.99 },
        { src: "/ProjetWeb/assets/images/box1.jpg", alt: "Boite", name: "Boite Élégante", price: 34.99 },
        { src: "/ProjetWeb/assets/images/box2.jpg", alt: "Boîte fête", name: "Boîte fête", price: 19.99 },
        { src: "/ProjetWeb/assets/images/Framboisier.png", alt: "Framboisier", name: "Framboisier", price: 29.99 },
        { src: "/ProjetWeb/assets/images/GateauPM.jpg", alt: "Pièce Montée", name: "Pièce Montée", price: 119.99 },
        { src: "/ProjetWeb/assets/images/gateauliping.webp", alt: "Gâteau Pâques", name: "Gâteau Pâques", price: 29.99 },
        { src: "/ProjetWeb/assets/images/fraiseg.avif", alt: "Mini Fraisier", name: "Mini Fraisier", price: 6.99 },
        { src: "/ProjetWeb/assets/images/Bentolove.jpg", alt: "Bento Love", name: "Bento Love", price: 24.99 },
        { src: "/ProjetWeb/assets/images/bentofraise.jpg", alt: "Fraisier", name: "Fraisier", price: 29.99 },
        { src: "/ProjetWeb/assets/images/mini.jpg", alt: "Gâteau Multi-Fruit", name: "Gâteau Multi-Fruit", price: 14.99 },
        { src: "/ProjetWeb/assets/images/fraisei.jpg", alt: "Gâteau Fruits Rouges", name: "Gâteau Fruits Rouges", price: 29.99 },
        { src: "/ProjetWeb/assets/images/multi.webp", alt: "Gâteau de saison", name: "Gâteau de saison", price: 34.99 },
      ];
    
      async function addToCart(productName, quantity, totalPrice) {
        try {
          const productId = getProductId(productName);
          if (!productId) {
            throw new Error('Produit non trouvé');
          }

          const response = await fetch('/ProjetWeb/api/cart.php', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json'
            },
            body: JSON.stringify({
              action: 'add',
              product_id: productId,
              quantity: parseInt(quantity)
            })
          });

          const data = await response.json();
          
          if (response.ok) {
            const popup = document.getElementById('popup');
            popup.style.display = 'none';
            alert('Produit ajouté au panier avec succès !');
          } else {
            throw new Error(data.message || 'Erreur inconnue');
          }
        } catch (error) {
          console.error('Erreur détaillée:', error);
          alert('Erreur lors de l\'ajout au panier: ' + error.message);
        }
      }

      document.addEventListener('DOMContentLoaded', function () {
        const productsSection = document.getElementById('products');

        products.forEach(product => {
          const productDiv = document.createElement('div');
          productDiv.className = 'product';
          productDiv.innerHTML = `
            <img src="${product.src}" alt="${product.alt}" />
            <h3>${product.name}</h3>
            <p>Prix: ${product.price.toFixed(2)}€</p>
            <button class="add-to-cart">Ajouter au panier</button>
          `;
          productsSection.appendChild(productDiv);
        });

        productsSection.addEventListener('click', function (e) {
          if (e.target.classList.contains('add-to-cart')) {
            const productDiv = e.target.closest('.product');
            const priceText = productDiv.querySelector('p').innerText;
            const price = parseFloat(priceText.split(': ')[1]);
            const productName = productDiv.querySelector('h3').innerText;

            const popup = document.getElementById('popup');
            const quantityInput = document.getElementById('quantity');
            const totalPriceDisplay = document.getElementById('total-price');

            quantityInput.value = 1;
            totalPriceDisplay.innerText = `Total: ${(price * quantityInput.value).toFixed(2)}€`;

            popup.style.display = 'flex';

            quantityInput.addEventListener('input', function () {
              totalPriceDisplay.innerText = `Total: ${(price * quantityInput.value).toFixed(2)}€`;
            });

            document.getElementById('confirm-btn').onclick = function () {
              const quantity = parseInt(quantityInput.value);
              const totalPrice = (price * quantity).toFixed(2);
              addToCart(productName, quantity, totalPrice);
              popup.style.display = 'none';
            };

            document.querySelector('.close-btn').onclick = function () {
              popup.style.display = 'none';
            };
          }
        });
      });
    </script>
  </body>
</html>
