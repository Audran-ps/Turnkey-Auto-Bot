<?php
session_start();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user'])) {
    $_SESSION['checkout_error'] = 'Vous devez être connecté pour passer une commande';
    header('Location: conection.php');
    exit;
}

// Vérifier si le panier n'est pas vide
if (empty($_SESSION['panier'])) {
    $_SESSION['checkout_error'] = 'Votre panier est vide';
    header('Location: panier.php');
    exit;
}

// Connexion à la base de données
$host = 'localhost';
$dbname = 'ecommerce';
$user = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Récupérer les vraies données des produits
    foreach ($_SESSION['panier'] as &$item) {
        if (isset($item['id'])) {
            $stmt = $pdo->prepare('SELECT * FROM product WHERE id_product = :id');
            $stmt->execute([':id' => $item['id']]);
            $product = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($product) {
                $item['name'] = $product['name_product'] ?? $item['name'] ?? 'Produit sans nom';
                $item['price'] = floatval($product['price_product'] ?? $item['price'] ?? 0);
                $item['image'] = $product['picture_product'] ?? $item['image'] ?? 'default.png';
            }
        }
    }
    unset($item);
} catch (PDOException $e) {
    error_log('Erreur BDD checkout: ' . $e->getMessage());
}

// Calcul du total
$total = 0;
$itemCount = 0;
foreach ($_SESSION['panier'] as $item) {
    $total += floatval($item['price'] ?? 0) * intval($item['quantity'] ?? 1);
    $itemCount += intval($item['quantity'] ?? 1);
}

// Récupérer les informations du client
$clientInfo = $_SESSION['user'];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Paiement - Nanos</title>
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
        }

        .logo a span {
            color: var(--white-text);
        }

        .container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 20px;
        }

        .checkout-wrapper {
            display: grid;
            grid-template-columns: 1fr 450px;
            gap: 30px;
            animation: fadeInUp 0.6s ease;
        }

        .checkout-section {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }

        .section-title {
            font-size: 1.5rem;
            color: var(--dark-bg);
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 2px solid var(--border-color);
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: var(--dark-bg);
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 2px solid var(--border-color);
            border-radius: 8px;
            font-size: 1rem;
            transition: all var(--transition-speed) ease;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(41, 217, 213, 0.1);
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }

        .payment-methods {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
            margin-top: 15px;
        }

        .payment-method {
            border: 2px solid var(--border-color);
            border-radius: 10px;
            padding: 15px;
            text-align: center;
            cursor: pointer;
            transition: all var(--transition-speed) ease;
        }

        .payment-method:hover {
            border-color: var(--primary-color);
            transform: translateY(-2px);
        }

        .payment-method input[type="radio"] {
            display: none;
        }

        .payment-method input[type="radio"]:checked + label {
            color: var(--primary-color);
            font-weight: bold;
        }

        .payment-method.selected {
            border-color: var(--primary-color);
            background: rgba(41, 217, 213, 0.1);
        }

        .order-summary {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            position: sticky;
            top: 100px;
            height: fit-content;
        }

        .order-item {
            display: flex;
            gap: 15px;
            padding: 15px 0;
            border-bottom: 1px solid var(--border-color);
        }

        .order-item:last-child {
            border-bottom: none;
        }

        .order-item-image {
            width: 80px;
            height: 80px;
            border-radius: 8px;
            overflow: hidden;
            background: var(--light-gray);
        }

        .order-item-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .order-item-details {
            flex: 1;
        }

        .order-item-name {
            font-weight: 600;
            margin-bottom: 5px;
        }

        .order-item-price {
            color: var(--primary-color);
            font-weight: bold;
        }

        .summary-total {
            margin-top: 20px;
            padding-top: 20px;
            border-top: 2px solid var(--border-color);
            display: flex;
            justify-content: space-between;
            font-size: 1.3rem;
            font-weight: bold;
            color: var(--primary-color);
        }

        .submit-btn {
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
            margin-top: 25px;
            box-shadow: 0 5px 15px rgba(41, 217, 213, 0.3);
        }

        .submit-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(41, 217, 213, 0.4);
        }

        .submit-btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }

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
            font-weight: 600;
        }

        .toast.show {
            transform: translateY(0);
            opacity: 1;
        }

        .toast.error {
            background: var(--danger-color);
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

        @media (max-width: 1024px) {
            .checkout-wrapper {
                grid-template-columns: 1fr;
            }

            .order-summary {
                position: static;
            }
        }
    </style>
</head>
<body>

<header>
    <div class="logo">
        <a href="category.php">N<span>anos</span></a>
    </div>
    <div style="color: white;">
        <a href="panier.php" style="color: var(--primary-color); text-decoration: none;">← Retour au panier</a>
    </div>
</header>

<div class="container">
    <h1 style="text-align: center; margin-bottom: 30px; color: var(--dark-bg);">💳 Paiement</h1>

    <?php if (isset($_SESSION['checkout_error'])): ?>
        <div class="toast error show" id="errorToast">
            <?= htmlspecialchars($_SESSION['checkout_error']) ?>
        </div>
        <?php unset($_SESSION['checkout_error']); ?>
    <?php endif; ?>

    <form action="../back/process_checkout.php" method="POST" id="checkoutForm">
        <div class="checkout-wrapper">
            <div>
                <!-- Informations de livraison -->
                <div class="checkout-section">
                    <h2 class="section-title">📍 Adresse de livraison</h2>
                    <div class="form-group">
                        <label>Nom complet</label>
                        <input type="text" name="nom_complet" value="<?= htmlspecialchars(($clientInfo['prenom'] ?? '') . ' ' . ($clientInfo['nom'] ?? '')) ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" name="email" value="<?= htmlspecialchars($clientInfo['email'] ?? '') ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Adresse</label>
                        <input type="text" name="adresse" placeholder="Numéro et nom de rue" required>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Code postal</label>
                            <input type="text" name="code_postal" pattern="[0-9]{5}" required>
                        </div>
                        <div class="form-group">
                            <label>Ville</label>
                            <input type="text" name="ville" required>
                        </div>
                    </div>
                </div>

                <!-- Méthode de paiement -->
                <div class="checkout-section">
                    <h2 class="section-title">💳 Méthode de paiement</h2>
                    <div class="payment-methods">
                        <div class="payment-method" onclick="selectPayment('carte')">
                            <input type="radio" name="methode_paiement" value="carte" id="carte" checked required>
                            <label for="carte">💳 Carte bancaire</label>
                        </div>
                        <div class="payment-method" onclick="selectPayment('paypal')">
                            <input type="radio" name="methode_paiement" value="paypal" id="paypal">
                            <label for="paypal">🅿️ PayPal</label>
                        </div>
                        <div class="payment-method" onclick="selectPayment('virement')">
                            <input type="radio" name="methode_paiement" value="virement" id="virement">
                            <label for="virement">🏦 Virement</label>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Résumé de la commande -->
            <div class="order-summary">
                <h2 class="section-title">📦 Résumé</h2>
                
                <?php foreach ($_SESSION['panier'] as $item): ?>
                    <div class="order-item">
                        <div class="order-item-image">
                            <?php 
                            $imagePath = 'uploads/' . ($item['image'] ?? 'default.png');
                            if (!file_exists($imagePath)) {
                                $imagePath = 'uploads/default.png';
                            }
                            ?>
                            <img src="<?= htmlspecialchars($imagePath) ?>" 
                                 alt="<?= htmlspecialchars($item['name'] ?? 'Produit') ?>" 
                                 onerror="this.src='uploads/default.png'">
                        </div>
                        <div class="order-item-details">
                            <div class="order-item-name"><?= htmlspecialchars($item['name'] ?? 'Produit sans nom') ?></div>
                            <div>Quantité: <?= intval($item['quantity'] ?? 0) ?></div>
                            <div class="order-item-price"><?= number_format(floatval($item['price'] ?? 0) * intval($item['quantity'] ?? 0), 2, ',', ' ') ?> €</div>
                        </div>
                    </div>
                <?php endforeach; ?>

                <div class="summary-total">
                    <span>Total</span>
                    <span><?= number_format($total, 2, ',', ' ') ?> €</span>
                </div>

                <button type="submit" class="submit-btn" id="submitBtn">
                    Confirmer et payer
                </button>
            </div>
        </div>
    </form>
</div>

<div class="toast" id="toast"></div>

<script>
function selectPayment(method) {
    document.querySelectorAll('.payment-method').forEach(el => el.classList.remove('selected'));
    event.currentTarget.classList.add('selected');
    document.getElementById(method).checked = true;
}

// Initialiser la sélection
document.querySelectorAll('.payment-method').forEach(el => {
    if (el.querySelector('input[type="radio"]:checked')) {
        el.classList.add('selected');
    }
});

// Gestion du formulaire
document.getElementById('checkoutForm').addEventListener('submit', function(e) {
    const btn = document.getElementById('submitBtn');
    btn.disabled = true;
    btn.textContent = 'Traitement en cours...';
});

// Gestion du toast d'erreur
const errorToast = document.getElementById('errorToast');
if (errorToast) {
    setTimeout(() => {
        errorToast.classList.remove('show');
        setTimeout(() => errorToast.remove(), 300);
    }, 5000);
}
</script>

</body>
</html>