<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Boutique AfribaPay</title>
  <meta name="description" content="Achetez facilement des gadgets, accessoires et objets connectés sur notre boutique en ligne et payez en toute sécurité avec AfribaPay." />
  <link rel="icon" href="favicon.png" type="image/png" />
  <meta property="og:title" content="Boutique AfribaPay" />
  <meta property="og:description" content="Achetez des articles tech et payez facilement avec AfribaPay." />
  <meta property="og:image" content="https://cdn-icons-png.flaticon.com/512/7479/7479736.png" />
  <meta property="og:url" content="https://ton-site.com" />
  <meta name="twitter:card" content="summary_large_image" />
  <script src="https://cdn.tailwindcss.com"></script>

  <style>
        * {
            box-sizing: border-box;
        }
        body {
            margin: 0;
            font-family: 'Inter', sans-serif;
            background-color: #f9fafb;
            padding: 20px;
        }
        .ecommerce-container {
            max-width: 1000px;
            margin: auto;
        }
        .ecommerce-header {
            text-align: center;
            margin-bottom: 40px;
        }
        .ecommerce-header h1 {
            font-size: 2rem;
            margin-bottom: 8px;
        }
        .ecommerce-products {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 20px;
        }
        .ecommerce-product {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.06);
            padding: 16px;
            text-align: center;
        }
        .ecommerce-product img {
            width: 100%;
            height: 150px;
            object-fit: cover;
            border-radius: 8px;
        }
        .ecommerce-product h2 {
            font-size: 16px;
            margin: 12px 0 6px;
        }
        .ecommerce-product p {
            margin: 0;
            color: #555;
        }
        .ecommerce-product button {
            margin-top: 10px;
            background-color: #202942;
            color: white;
            border: none;
            padding: 8px 12px;
            border-radius: 6px;
            cursor: pointer;
        }
        .ecommerce-cart {
            margin-top: 40px;
            background: #fff;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.06);
        }
        .ecommerce-cart h3 {
            margin-bottom: 16px;
        }
        .ecommerce-cart ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        .ecommerce-cart li {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }
        .ecommerce-cart .cart-item-controls {
            display: flex;
            gap: 5px;
            align-items: center;
        }
        .ecommerce-cart button {
            background: #ef4444;
            color: white;
            border: none;
            padding: 4px 8px;
            border-radius: 4px;
            cursor: pointer;
        }
        .ecommerce-cart .qty-button {
            background: #202942;
            color: white;
            border: none;
            padding: 4px 8px;
            border-radius: 4px;
            cursor: pointer;
        }
        .ecommerce-checkout {
            margin-top: 20px;
            text-align: right;
        }
        .ecommerce-checkout form button {
            background-color: #202942;
            color: white;
            border: none;
            padding: 10px 16px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 16px;
        }
  </style>

</head>
<body class="bg-white text-gray-800 font-sans">

  <div class="min-h-screen flex flex-col">
    <!-- HEADER -->
    <header class="bg-white shadow-md">
      <div class="max-w-7xl mx-auto px-4 py-4 flex items-center justify-between">
        <a href="#" class="text-xl font-bold text-indigo-600 flex items-center gap-2">
          <img src="https://cdn-icons-png.flaticon.com/512/7479/7479736.png" alt="Logo" class="w-8 h-8" />
          AfribaPay
        </a>
        <button id="menu-btn" class="md:hidden text-gray-700 focus:outline-none">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M4 8h16M4 16h16" />
          </svg>
        </button>
        <nav id="menu" class="hidden md:flex gap-6 text-gray-700 font-medium">
          <a href="#" class="hover:text-indigo-600">Accueil</a>
          <a href="#" class="bg-indigo-600 text-white px-3 py-1 rounded hover:bg-indigo-700 text-sm">Se connecter</a>
        </nav>
      </div>
      <div id="mobile-menu" class="md:hidden hidden px-4 pb-4">
        <a href="#" class="block py-2 text-gray-700 hover:text-indigo-600">Accueil</a>
        <a href="#" class="block mt-2 bg-indigo-600 text-white text-center py-2 rounded hover:bg-indigo-700">Se connecter</a>
      </div>
    </header>


    <!-- CONTENU -->
    <main class="flex-grow px-4 pt-8 pb-16">
      <!-- Accueil -->
      <section id="accueil" class="text-center mb-16">
        <h1 class="text-4xl font-bold mb-4">Bienvenue sur la boutique AfribaPay</h1>
        <p class="text-lg text-gray-600">Sélectionnez des articles et payez en toute sécurité avec AfribaPay</p>
      </section>


      <!-- Boutique -->
      <section id="boutique" class="mb-16">
        <div class="ecommerce-container">
            <div class="ecommerce-products" id="products"></div>

            <div class="ecommerce-cart">
                <h3>Votre panier</h3>
                <ul id="cart"></ul>
                <p><strong>Total :</strong> <span id="total">0</span> FCFA</p>
                <div class="ecommerce-checkout" id="checkout-button"></div>
            </div>

        </div>
      </section>



      <!-- À propos -->
      <section id="apropos" class="mb-16">
      </section>
      <!-- Contact -->
      <section id="contact">
      </section>
    </main>






    <!-- FOOTER -->
    <footer class="bg-gray-100 border-t mt-auto">
      <div class="max-w-7xl mx-auto px-4 py-10 grid md:grid-cols-3 gap-8 text-sm text-gray-600">
        <div>
          <h2 class="text-lg font-semibold text-indigo-600 mb-2">AfribaPay</h2>
          <p>Boutique en ligne d’objets tech et connectés avec paiement sécurisé.</p>
        </div>
        <div>
          <h3 class="font-semibold text-gray-800 mb-2">Liens utiles</h3>
          <ul class="space-y-1">
            <li><a href="#accueil" class="hover:underline">Accueil</a></li>
            <li><a href="#boutique" class="hover:underline">Boutique</a></li>
            <li><a href="#apropos" class="hover:underline">À propos</a></li>
            <li><a href="#contact" class="hover:underline">Contact</a></li>
          </ul>
        </div>
        <div>
          <h3 class="font-semibold text-gray-800 mb-2">Contact</h3>
          <p>Email : support@afribapay.com</p>
          <p>Téléphone : +226 70 00 00 00</p>
          <div class="mt-2 flex gap-4">
            <a href="#" class="hover:text-indigo-600">Facebook</a>
            <a href="#" class="hover:text-indigo-600">Instagram</a>
            <a href="#" class="hover:text-indigo-600">Twitter</a>
          </div>
        </div>
      </div>
      <div class="text-center text-xs text-gray-500 py-4 border-t">
        &copy; 2025 AfribaPay. Tous droits réservés.
      </div>
    </footer>

  </div>

  <!-- Script menu responsive -->
  <script>
    const menuBtn = document.getElementById("menu-btn");
    const mobileMenu = document.getElementById("mobile-menu");
    menuBtn.addEventListener("click", () => {
      mobileMenu.classList.toggle("hidden");
    });
  </script>

<script>
        const products = [
            { id: 1, name: 'Casque Bluetooth', price: 15000, image: 'https://cdn-icons-png.flaticon.com/512/787/787535.png' },
            { id: 2, name: 'Montre connectée', price: 23000, image: 'https://cdn-icons-png.flaticon.com/512/7479/7479736.png' },
            { id: 3, name: 'Chargeur USB', price: 5000, image: 'https://cdn-icons-png.flaticon.com/512/3343/3343848.png' },
            { id: 4, name: 'Sac à dos', price: 12000, image: 'https://cdn-icons-png.flaticon.com/512/2920/2920122.png' },
            { id: 5, name: 'Lunettes de soleil', price: 8000, image: 'https://cdn-icons-png.flaticon.com/512/2965/2965567.png' },
            { id: 6, name: 'Écouteurs', price: 7000, image: 'https://cdn-icons-png.flaticon.com/512/4366/4366867.png' },
        ];

        const cart = [];

        function renderProducts() {
            const productContainer = document.getElementById('products');
            productContainer.innerHTML = '';
            products.forEach(product => {
                const div = document.createElement('div');
                div.className = 'ecommerce-product';
                div.innerHTML = `
                    <img src="${product.image}" alt="${product.name}">
                    <h2>${product.name}</h2>
                    <p>${product.price} FCFA</p>
                    <button onclick="addToCart(${product.id})">Ajouter</button>
                `;
                productContainer.appendChild(div);
            });
        }

        function addToCart(id) {
            const product = products.find(p => p.id === id);
            const cartItem = cart.find(item => item.id === id);
            if (cartItem) {
                cartItem.quantity += 1;
            } else {
                cart.push({ ...product, quantity: 1 });
            }
            updateCart();
        }

        function decreaseQuantity(id) {
            const cartItem = cart.find(item => item.id === id);
            if (cartItem) {
                if (cartItem.quantity > 1) {
                    cartItem.quantity -= 1;
                } else {
                    cart.splice(cart.indexOf(cartItem), 1);
                }
            }
            updateCart();
        }

        function removeFromCart(id) {
            const index = cart.findIndex(item => item.id === id);
            if (index > -1) {
                cart.splice(index, 1);
            }
            updateCart();
        }

        function updateCart() {
            const cartList = document.getElementById('cart');
            cartList.innerHTML = '';
            let total = 0;
            cart.forEach(item => {
                total += item.price * item.quantity;
                const li = document.createElement('li');
                li.innerHTML = `
                    ${item.name} x${item.quantity} - ${item.price * item.quantity} FCFA
                    <span class="cart-item-controls">
                        <button class="qty-button" onclick="decreaseQuantity(${item.id})">-</button>
                        <button onclick="removeFromCart(${item.id})">Retirer</button>
                        <button class="qty-button" onclick="addToCart(${item.id})">+</button>
                    </span>
                `;
                cartList.appendChild(li);
            });
            document.getElementById('total').textContent = total;
            renderCheckout(total);
        }

        function renderCheckout(amount) {
            console.log(cart);
            const total = cart.reduce((acc, item) => acc + (item.price * item.quantity), 0);
            console.log("Montant total : " + total + " FCFA");
            const checkoutDiv = document.getElementById('checkout-button');
            checkoutDiv.innerHTML = '';
            if (amount > 0) {
                localStorage.setItem('cart', JSON.stringify(cart));

                // Convertir en JSON puis échapper pour éviter les caractères spéciaux
                document.cookie = "cart=" + encodeURIComponent(JSON.stringify(cart)) + "; path=/; max-age=86400"; // expire dans 1 jour

                const form = document.createElement('form');
                form.action = 'checkout.php';
                  form.method = 'GET';
                  form.innerHTML = `
                      <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded shadow transition">
                          💳 Valider mon panier
                      </button>
                  `;
                checkoutDiv.appendChild(form);
            }
        }


        renderProducts();
    </script>

</body>
</html>
