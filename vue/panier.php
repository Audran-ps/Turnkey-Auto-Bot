<?php
session_start();

// Initialiser le panier s'il n'existe pas
if (!isset($_SESSION['panier'])) {
    $_SESSION['panier'] = [];
}

// Connexion à la base de données pour récupérer les vraies données des produits
$host = 'localhost';
$dbname = 'ecommerce';
$user = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Récupérer les vraies données des produits depuis la BDD
    foreach ($_SESSION['panier'] as &$item) {
        if (isset($item['id'])) {
            $stmt = $pdo->prepare('SELECT * FROM product WHERE id_product = :id');
            $stmt->execute([':id' => $item['id']]);
            $product = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($product) {
                // Mettre à jour avec les vraies données
                $item['name'] = $product['name_product'] ?? $item['name'] ?? 'Produit sans nom';
                $item['price'] = floatval($product['price_product'] ?? $item['price'] ?? 0);
                $item['image'] = $product['picture_product'] ?? $item['image'] ?? 'default.png';
            }
        }
    }
    unset($item); // Important : libérer la référence
} catch (PDOException $e) {
    // En cas d'erreur, on continue avec les données de session
    error_log('Erreur BDD panier: ' . $e->getMessage());
}

// Traitement des actions AJAX
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    
    switch ($_POST['action']) {
        case 'update_quantity':
            $index = intval($_POST['index']);
            $quantity = intval($_POST['quantity']);
            if (isset($_SESSION['panier'][$index]) && $quantity > 0) {
                $_SESSION['panier'][$index]['quantity'] = $quantity;
                // Recalculer le total pour la réponse
                $itemTotal = $_SESSION['panier'][$index]['price'] * $quantity;
                $newTotal = 0;
                foreach ($_SESSION['panier'] as $item) {
                    $newTotal += $item['price'] * $item['quantity'];
                }
                echo json_encode([
                    'success' => true,
                    'itemTotal' => number_format($itemTotal, 2, ',', ' '),
                    'total' => number_format($newTotal, 2, ',', ' ')
                ]);
            } else {
                echo json_encode(['success' => false]);
            }
            exit;
            
        case 'remove_item':
            $index = intval($_POST['index']);
            if (isset($_SESSION['panier'][$index])) {
                array_splice($_SESSION['panier'], $index, 1);
                // Recalculer le total
                $newTotal = 0;
                foreach ($_SESSION['panier'] as $item) {
                    $newTotal += $item['price'] * $item['quantity'];
                }
                echo json_encode([
                    'success' => true,
                    'total' => number_format($newTotal, 2, ',', ' '),
                    'itemCount' => count($_SESSION['panier'])
                ]);
            } else {
                echo json_encode(['success' => false]);
            }
            exit;
            
        case 'clear_cart':
            $_SESSION['panier'] = [];
            echo json_encode(['success' => true]);
            exit;
    }
}

// Calcul du total
$total = 0;
$itemCount = 0;
foreach ($_SESSION['panier'] as $item) {
    $total += $item['price'] * $item['quantity'];
    $itemCount += $item['quantity'];
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon Panier - Nanos</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        :root {
            --primary-color: #29d9d5;
            --secondary-color: #1fb3af;
            --dark-bg: #222;
            --white-text: #fff;
            --light-gray: #f8f9fa;
            --border-color: #e0e0e0;
            --transition-speed: 0.3s;
            --danger-color: #ff4757;
            --success-color: #2ed573;
        }

        body {
            font-family: 'Segoe UI', Roboto, 'Helvetica Neue', sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            padding-top: 80px;
        }

        /* Styles du header depuis navbar.php */
        header {
            background-color: var(--dark-bg);
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
            display: flex;
            align-items: center;
            justify-content: space-between;
            height: 60px;
            padding: 0 5%;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.3);
        }

        .logo a {
            font-size: 2.4rem;
            font-weight: bold;
            color: var(--primary-color);
            text-decoration: none;
            transition: color var(--transition-speed) ease;
        }

        .logo a:hover {
            color: var(--secondary-color);
        }

        .logo a span {
            color: var(--white-text);
        }

        .menu {
            display: flex;
            list-style: none;
            align-items: center;
            margin: 0;
        }

        .menu li {
            margin: 0 15px;
            position: relative;
        }

        .menu li a {
            color: var(--white-text);
            font-size: 1.4rem;
            text-decoration: none;
            position: relative;
            transition: color var(--transition-speed) ease;
            display: flex;
            align-items: center;
        }

        .menu li a:hover {
            color: var(--primary-color);
        }

        .menu li a::after {
            content: "";
            position: absolute;
            left: 0;
            bottom: -5px;
            height: 2px;
            width: 0%;
            background-color: var(--primary-color);
            transition: width var(--transition-speed) ease;
        }

        .menu li a:hover::after {
            width: 100%;
        }

        .cart-count {
            background-color: var(--primary-color);
            color: #000;
            border-radius: 50%;
            padding: 2px 6px;
            font-size: 0.8rem;
            margin-left: 5px;
            font-weight: bold;
        }

        .btn-reservation {
            color: var(--primary-color);
            font-size: 1.4rem;
            border: 2px solid var(--primary-color);
            padding: 5px 20px;
            transition: all var(--transition-speed) ease;
            font-weight: bold;
            text-decoration: none;
            border-radius: 4px;
        }

        .btn-reservation:hover {
            background-color: var(--primary-color);
            color: var(--dark-bg);
            transform: translateY(-2px);
        }

        /* Container principal */
        .container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 20px;
        }

        .page-header {
            text-align: center;
            margin-bottom: 40px;
            animation: fadeInDown 0.6s ease;
        }

        .page-header h1 {
            font-size: 2.5rem;
            color: var(--dark-bg);
            margin-bottom: 10px;
        }

        .page-header p {
            color: #666;
            font-size: 1.1rem;
        }

        .cart-wrapper {
            display: grid;
            grid-template-columns: 1fr 400px;
            gap: 30px;
            animation: fadeInUp 0.6s ease;
        }

        /* Section des produits */
        .cart-items {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }

        .empty-cart {
            text-align: center;
            padding: 60px 20px;
        }

        .empty-cart-icon {
            font-size: 5rem;
            margin-bottom: 20px;
            opacity: 0.3;
        }

        .empty-cart h2 {
            color: #666;
            margin-bottom: 10px;
        }

        .empty-cart p {
            color: #999;
            margin-bottom: 30px;
        }

        .cart-item {
            display: grid;
            grid-template-columns: 120px 1fr auto;
            gap: 20px;
            padding: 20px;
            border: 2px solid var(--border-color);
            border-radius: 12px;
            margin-bottom: 20px;
            transition: all var(--transition-speed) ease;
            animation: slideIn 0.4s ease;
        }

        .cart-item:hover {
            border-color: var(--primary-color);
            box-shadow: 0 5px 15px rgba(41, 217, 213, 0.2);
            transform: translateY(-2px);
        }

        .cart-item-image {
            width: 120px;
            height: 120px;
            border-radius: 10px;
            overflow: hidden;
            background: var(--light-gray);
        }

        .cart-item-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform var(--transition-speed) ease;
        }

        .cart-item:hover .cart-item-image img {
            transform: scale(1.1);
        }

        .cart-item-details {
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .cart-item-title {
            font-size: 1.3rem;
            font-weight: 600;
            color: var(--dark-bg);
            margin-bottom: 8px;
        }

        .cart-item-info {
            color: #666;
            font-size: 0.9rem;
            margin-bottom: 15px;
        }

        .cart-item-info span {
            display: inline-block;
            margin-right: 15px;
        }

        .quantity-control {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .quantity-btn {
            width: 35px;
            height: 35px;
            border: 2px solid var(--primary-color);
            background: white;
            color: var(--primary-color);
            border-radius: 50%;
            cursor: pointer;
            font-size: 1.2rem;
            font-weight: bold;
            transition: all var(--transition-speed) ease;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .quantity-btn:hover {
            background: var(--primary-color);
            color: white;
            transform: scale(1.1);
        }

        .quantity-input {
            width: 60px;
            text-align: center;
            border: 2px solid var(--border-color);
            border-radius: 8px;
            padding: 8px;
            font-size: 1rem;
            font-weight: 600;
        }

        .cart-item-actions {
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            align-items: flex-end;
        }

        .item-price {
            font-size: 1.5rem;
            font-weight: bold;
            color: var(--primary-color);
        }

        .remove-btn {
            background: none;
            border: none;
            color: var(--danger-color);
            cursor: pointer;
            font-size: 1.8rem;
            transition: all var(--transition-speed) ease;
            padding: 5px;
        }

        .remove-btn:hover {
            transform: rotate(90deg) scale(1.2);
        }

        /* Section résumé */
        .cart-summary {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            height: fit-content;
            position: sticky;
            top: 100px;
        }

        .cart-summary h2 {
            font-size: 1.5rem;
            color: var(--dark-bg);
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 2px solid var(--border-color);
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
            font-size: 1rem;
        }

        .summary-row.total {
            font-size: 1.3rem;
            font-weight: bold;
            color: var(--primary-color);
            padding-top: 20px;
            margin-top: 20px;
            border-top: 2px solid var(--border-color);
        }

        .promo-code {
            margin: 25px 0;
        }

        .promo-input {
            display: flex;
            gap: 10px;
            margin-top: 10px;
        }

        .promo-input input {
            flex: 1;
            padding: 12px;
            border: 2px solid var(--border-color);
            border-radius: 8px;
            font-size: 1rem;
        }

        .promo-input button {
            padding: 12px 20px;
            background: var(--dark-bg);
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            transition: all var(--transition-speed) ease;
        }

        .promo-input button:hover {
            background: var(--primary-color);
            color: var(--dark-bg);
        }

        .checkout-btn {
            width: 100%;
            padding: 15px;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 1.2rem;
            font-weight: bold;
            cursor: pointer;
            transition: all var(--transition-speed) ease;
            margin-top: 20px;
            box-shadow: 0 5px 15px rgba(41, 217, 213, 0.3);
        }

        .checkout-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(41, 217, 213, 0.4);
        }

        .continue-shopping {
            width: 100%;
            padding: 15px;
            background: white;
            color: var(--dark-bg);
            border: 2px solid var(--dark-bg);
            border-radius: 10px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all var(--transition-speed) ease;
            margin-top: 15px;
            text-decoration: none;
            display: block;
            text-align: center;
        }

        .continue-shopping:hover {
            background: var(--dark-bg);
            color: white;
        }

        .clear-cart-btn {
            width: 100%;
            padding: 12px;
            background: var(--danger-color);
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            margin-top: 15px;
            font-weight: 600;
            transition: all var(--transition-speed) ease;
        }

        .clear-cart-btn:hover {
            background: #ff3838;
            transform: translateY(-2px);
        }

        /* Animations */
        @keyframes fadeInDown {
            from {
                opacity: 0;
                transform: translateY(-30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateX(-30px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        /* Responsive */
        @media (max-width: 1024px) {
            .cart-wrapper {
                grid-template-columns: 1fr;
            }

            .cart-summary {
                position: static;
            }
        }

        @media (max-width: 768px) {
            .page-header h1 {
                font-size: 2rem;
            }

            .cart-item {
                grid-template-columns: 80px 1fr;
                gap: 15px;
            }

            .cart-item-image {
                width: 80px;
                height: 80px;
            }

            .cart-item-actions {
                grid-column: 1 / -1;
                flex-direction: row;
                justify-content: space-between;
                align-items: center;
                margin-top: 15px;
                padding-top: 15px;
                border-top: 1px solid var(--border-color);
            }

            .menu {
                display: none;
            }
        }

        /* Notification Toast */
        .toast {
            position: fixed;
            bottom: 30px;
            right: 30px;
            background: var(--success-color);
            color: white;
            padding: 15px 25px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
            transform: translateY(100px);
            opacity: 0;
            transition: all var(--transition-speed) ease;
            z-index: 2000;
        }

        .toast.show {
            transform: translateY(0);
            opacity: 1;
        }

        .toast.error {
            background: var(--danger-color);
        }
    </style>
</head>
<body>

<?php
    // Afficher les messages de session
    if (isset($_SESSION['cart_message'])) {
        echo '<div class="toast show" id="cartToast">' . htmlspecialchars($_SESSION['cart_message']) . '</div>';
        unset($_SESSION['cart_message']);
    }
    if (isset($_SESSION['cart_error'])) {
        echo '<div class="toast error show" id="cartToast">' . htmlspecialchars($_SESSION['cart_error']) . '</div>';
        unset($_SESSION['cart_error']);
    }
?>

<header>
    <div class="logo">
        <a href="category.php">N<span>anos</span></a>
    </div>

    <ul class="menu">
        <li><a href="category.php">Accueil</a></li>
        <li><a href="profil.php">Mon Profil</a></li>
        <li>
            <a href="panier.php">
                🛒 Panier 
                <span class="cart-count" id="cartCount"><?= $itemCount ?></span>
            </a>
        </li>
    </ul>

    <a href="contact.php" class="btn-reservation">Contact</a>
</header>

<div class="container">
    <div class="page-header">
        <h1>🛒 Mon Panier</h1>
        <p><?= $itemCount ?> article<?= $itemCount > 1 ? 's' : '' ?> dans votre panier</p>
    </div>

    <?php if (empty($_SESSION['panier'])): ?>
        <div class="cart-items">
            <div class="empty-cart">
                <div class="empty-cart-icon">🛒</div>
                <h2>Votre panier est vide</h2>
                <p>Découvrez nos produits et ajoutez-les à votre panier</p>
                <a href="category.php" class="checkout-btn">Découvrir nos produits</a>
            </div>
        </div>
    <?php else: ?>
        <div class="cart-wrapper">
            <div class="cart-items">
                <?php foreach ($_SESSION['panier'] as $index => $item): ?>
                    <div class="cart-item" data-index="<?= $index ?>">
                        <div class="cart-item-image">
                            <?php 
                            // Gestion intelligente du chemin d'image
                            $imageFile = $item['image'] ?? '';
                            $imagePath = '';
                            
                            // Essayer plusieurs chemins possibles
                            if (!empty($imageFile)) {
                                // Essayer directement avec le nom du fichier
                                $imagePath = 'uploads/' . $imageFile;
                                if (!file_exists($imagePath)) {
                                    // Essayer avec l'ID du produit
                                    $imagePath = 'uploads/' . ($item['id'] ?? '') . '.png';
                                    if (!file_exists($imagePath)) {
                                        // Essayer juste le numéro
                                        $imagePath = 'uploads/' . ($item['id'] ?? '') . '.jpg';
                                        if (!file_exists($imagePath)) {
                                            $imagePath = 'uploads/default.png';
                                        }
                                    }
                                }
                            } else {
                                // Si pas d'image, essayer avec l'ID
                                if (isset($item['id'])) {
                                    $imagePath = 'uploads/' . $item['id'] . '.png';
                                    if (!file_exists($imagePath)) {
                                        $imagePath = 'uploads/default.png';
                                    }
                                } else {
                                    $imagePath = 'uploads/default.png';
                                }
                            }
                            ?>
                            <img src="<?= $imagePath ?>" alt="<?= htmlspecialchars($item['name'] ?? 'Produit') ?>" onerror="this.src='uploads/default.png'">
                        </div>
                        
                        <div class="cart-item-details">
                            <div>
                                <div class="cart-item-title"><?= htmlspecialchars($item['name'] ?? 'Produit sans nom') ?></div>
                                <div class="cart-item-info">
                                    <span>Prix unitaire: <?= number_format(floatval($item['price'] ?? 0), 2, ',', ' ') ?> €</span>
                                </div>
                            </div>
                            
                            <div class="quantity-control">
                                <button class="quantity-btn decrease-btn" data-index="<?= $index ?>">−</button>
                                <input type="number" class="quantity-input" value="<?= $item['quantity'] ?>" min="1" data-index="<?= $index ?>">
                                <button class="quantity-btn increase-btn" data-index="<?= $index ?>">+</button>
                            </div>
                        </div>
                        
                        <div class="cart-item-actions">
                            <button class="remove-btn" data-index="<?= $index ?>" title="Supprimer">×</button>
                            <div class="item-price" data-unit-price="<?= floatval($item['price'] ?? 0) ?>"><?= number_format(floatval($item['price'] ?? 0) * intval($item['quantity'] ?? 1), 2, ',', ' ') ?> €</div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="cart-summary">
                <h2>Résumé</h2>
                
                <div class="summary-row">
                    <span>Sous-total</span>
                    <span id="subtotal"><?= number_format($total, 2, ',', ' ') ?> €</span>
                </div>
                
                <div class="summary-row">
                    <span>Livraison</span>
                    <span>Gratuite</span>
                </div>
                
                <div class="promo-code">
                    <label>Code promo</label>
                    <div class="promo-input">
                        <input type="text" placeholder="Entrez votre code">
                        <button>Appliquer</button>
                    </div>
                </div>
                
                <div class="summary-row total">
                    <span>Total</span>
                    <span id="total"><?= number_format($total, 2, ',', ' ') ?> €</span>
                </div>
                
                <button class="checkout-btn" onclick="window.location.href='checkout.php'">
                    Procéder au paiement
                </button>
                
                <a href="category.php" class="continue-shopping">
                    ← Continuer mes achats
                </a>
                
                <button class="clear-cart-btn" onclick="clearCart()">
                    Vider le panier
                </button>
            </div>
        </div>
    <?php endif; ?>
</div>

<div class="toast" id="toast"></div>

<script>
function showToast(message, isError = false) {
    const toast = document.getElementById('toast');
    toast.textContent = message;
    toast.classList.toggle('error', isError);
    toast.classList.add('show');
    
    setTimeout(() => {
        toast.classList.remove('show');
    }, 3000);
}

function updateCart(index, quantity) {
    // Désactiver les boutons pendant la mise à jour
    const item = document.querySelector(`.cart-item[data-index="${index}"]`);
    if (!item) return;
    
    const buttons = item.querySelectorAll('.quantity-btn, .quantity-input');
    buttons.forEach(btn => btn.disabled = true);
    
    fetch('panier.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `action=update_quantity&index=${index}&quantity=${quantity}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Mettre à jour le prix de l'article avec la valeur du serveur
            const priceElement = item.querySelector('.item-price');
            if (priceElement && data.itemTotal) {
                priceElement.textContent = data.itemTotal + ' €';
            } else {
                updateItemPrice(index, quantity);
            }
            // Mettre à jour le total avec la valeur du serveur
            if (data.total) {
                const subtotalElement = document.getElementById('subtotal');
                const totalElement = document.getElementById('total');
                if (subtotalElement) subtotalElement.textContent = data.total.replace('.', ',') + ' €';
                if (totalElement) totalElement.textContent = data.total.replace('.', ',') + ' €';
            } else {
                updateTotal();
            }
            // Mettre à jour le compteur du panier
            updateCartCount();
            showToast('Quantité mise à jour');
        } else {
            showToast('Erreur lors de la mise à jour', true);
        }
        // Réactiver les boutons
        buttons.forEach(btn => btn.disabled = false);
    })
    .catch(error => {
        showToast('Erreur lors de la mise à jour', true);
        buttons.forEach(btn => btn.disabled = false);
    });
}

function updateItemPrice(index, quantity) {
    const item = document.querySelector(`.cart-item[data-index="${index}"]`);
    if (item) {
        const priceElement = item.querySelector('.item-price');
        const unitPrice = parseFloat(priceElement.dataset.unitPrice || priceElement.textContent.replace(' €', '').replace(',', '.'));
        const totalPrice = (unitPrice * quantity).toFixed(2);
        priceElement.textContent = totalPrice.replace('.', ',') + ' €';
    }
}

function updateTotal() {
    let subtotal = 0;
    document.querySelectorAll('.cart-item').forEach(item => {
        const priceText = item.querySelector('.item-price').textContent;
        const price = parseFloat(priceText.replace(' €', '').replace(',', '.'));
        subtotal += price;
    });
    
    const subtotalElement = document.getElementById('subtotal');
    const totalElement = document.getElementById('total');
    
    if (subtotalElement) {
        subtotalElement.textContent = subtotal.toFixed(2).replace('.', ',') + ' €';
    }
    if (totalElement) {
        totalElement.textContent = subtotal.toFixed(2).replace('.', ',') + ' €';
    }
}

function removeItem(index) {
    if (confirm('Voulez-vous vraiment supprimer cet article ?')) {
        const item = document.querySelector(`.cart-item[data-index="${index}"]`);
        if (item) {
            // Animation de suppression
            item.style.transition = 'all 0.3s ease';
            item.style.opacity = '0';
            item.style.transform = 'translateX(-100px)';
        }
        
        fetch('panier.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `action=remove_item&index=${index}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast('Article supprimé avec succès');
                setTimeout(() => {
                    if (item) item.remove();
                    // Mettre à jour le total avec la valeur du serveur
                    if (data.total) {
                        const subtotalElement = document.getElementById('subtotal');
                        const totalElement = document.getElementById('total');
                        if (subtotalElement) subtotalElement.textContent = data.total.replace('.', ',') + ' €';
                        if (totalElement) totalElement.textContent = data.total.replace('.', ',') + ' €';
                    } else {
                        updateTotal();
                    }
                    updateCartCount();
                    // Vérifier si le panier est vide
                    if (document.querySelectorAll('.cart-item').length === 0) {
                        location.reload();
                    }
                }, 300);
            } else {
                showToast('Erreur lors de la suppression', true);
                if (item) {
                    item.style.opacity = '1';
                    item.style.transform = 'translateX(0)';
                }
            }
        })
        .catch(error => {
            showToast('Erreur lors de la suppression', true);
            if (item) {
                item.style.opacity = '1';
                item.style.transform = 'translateX(0)';
            }
        });
    }
}

function updateCartCount() {
    let totalQuantity = 0;
    document.querySelectorAll('.quantity-input').forEach(input => {
        totalQuantity += parseInt(input.value) || 0;
    });
    
    const cartCountElement = document.getElementById('cartCount');
    if (cartCountElement) {
        cartCountElement.textContent = totalQuantity;
    }
    
    // Mettre à jour aussi le header si présent
    const headerCartCount = document.querySelector('header .cart-count');
    if (headerCartCount) {
        headerCartCount.textContent = totalQuantity;
    }
}

function clearCart() {
    if (confirm('Voulez-vous vraiment vider votre panier ?')) {
        // Animation de suppression de tous les articles
        const items = document.querySelectorAll('.cart-item');
        items.forEach((item, index) => {
            setTimeout(() => {
                item.style.transition = 'all 0.3s ease';
                item.style.opacity = '0';
                item.style.transform = 'translateX(-100px)';
            }, index * 50);
        });
        
        fetch('panier.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'action=clear_cart'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast('Panier vidé avec succès');
                setTimeout(() => location.reload(), 500);
            }
        });
    }
}

// Gestion des boutons de quantité
document.querySelectorAll('.increase-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        const index = this.dataset.index;
        const input = document.querySelector(`.quantity-input[data-index="${index}"]`);
        const newQuantity = parseInt(input.value) + 1;
        input.value = newQuantity;
        updateCart(index, newQuantity);
    });
});

document.querySelectorAll('.decrease-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        const index = this.dataset.index;
        const input = document.querySelector(`.quantity-input[data-index="${index}"]`);
        const newQuantity = Math.max(1, parseInt(input.value) - 1);
        input.value = newQuantity;
        updateCart(index, newQuantity);
    });
});

document.querySelectorAll('.quantity-input').forEach(input => {
    input.addEventListener('change', function() {
        const index = this.dataset.index;
        const newQuantity = Math.max(1, parseInt(this.value));
        this.value = newQuantity;
        updateCart(index, newQuantity);
    });
});

document.querySelectorAll('.remove-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        const index = this.dataset.index;
        removeItem(index);
    });
});

// Gestion du toast initial
document.addEventListener('DOMContentLoaded', function() {
    const toast = document.getElementById('cartToast');
    if (toast) {
        setTimeout(() => {
            toast.classList.remove('show');
            setTimeout(() => toast.remove(), 300);
        }, 3000);
    }
});
</script>

</body>
</html>