<?php
    session_start();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Boutique - Catégories</title>
    <link rel="stylesheet" href="category.css">
    <style>
        * {
            box-sizing: border-box;
        }

        :root {
            --primary-color: #29d9d5;
            --secondary-color: #1fb3af;
            --dark-bg: #222;
            --white-text: #fff;
            --light-gray: #f8f9fa;
            --border-color: #e0e0e0;
            --transition-speed: 0.3s;
            --success-color: #2ed573;
        }

        body {
            font-family: 'Segoe UI', Roboto, 'Helvetica Neue', sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            padding-top: 80px;
        }

        .banner {
            background: linear-gradient(135deg, var(--dark-bg) 0%, #333 100%);
            color: white;
            text-align: center;
            padding: 80px 20px;
            margin-bottom: 50px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.2);
        }

        .banner h1 {
            color: var(--primary-color);
            font-size: 3.5rem;
            margin-bottom: 10px;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
            animation: fadeInDown 0.6s ease;
        }

        .banner p {
            font-size: 1.2rem;
            opacity: 0.9;
            animation: fadeInUp 0.6s ease;
        }

        .categories-container {
            max-width: 1200px;
            margin: 0 auto 50px;
            padding: 0 20px;
        }

        .categories-wrapper {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 15px;
        }

        .category-btn {
            display: inline-block;
            border: 2px solid var(--primary-color);
            padding: 12px 25px;
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 600;
            font-size: 1rem;
            border-radius: 30px;
            box-shadow: 0 4px 15px rgba(41, 217, 213, 0.2);
            transition: all var(--transition-speed) ease;
            position: relative;
            overflow: hidden;
        }

        .category-btn::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            border-radius: 50%;
            background: var(--primary-color);
            transform: translate(-50%, -50%);
            transition: width 0.6s, height 0.6s;
        }

        .category-btn:hover::before {
            width: 300px;
            height: 300px;
        }

        .category-btn:hover {
            color: var(--dark-bg);
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(41, 217, 213, 0.4);
        }

        .category-btn span {
            position: relative;
            z-index: 1;
        }

        .products-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px 50px;
        }

        .products-title {
            text-align: center;
            color: var(--dark-bg);
            font-size: 2rem;
            margin-bottom: 40px;
            position: relative;
            padding-bottom: 15px;
        }

        .products-title::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 100px;
            height: 3px;
            background: linear-gradient(90deg, var(--primary-color), var(--secondary-color));
            border-radius: 2px;
        }

        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 30px;
            justify-items: center;
        }

        .product-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
            padding: 25px;
            width: 100%;
            max-width: 300px;
            text-align: center;
            transition: all var(--transition-speed) ease;
            position: relative;
            overflow: hidden;
        }

        .product-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(41, 217, 213, 0.1), transparent);
            transition: left 0.5s;
        }

        .product-card:hover::before {
            left: 100%;
        }

        .product-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 10px 30px rgba(41, 217, 213, 0.3);
        }

        .product-image-wrapper {
            position: relative;
            width: 100%;
            height: 220px;
            border-radius: 12px;
            overflow: hidden;
            margin-bottom: 15px;
            background: var(--light-gray);
        }

        .product-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform var(--transition-speed) ease;
        }

        .product-card:hover .product-image {
            transform: scale(1.1);
        }

        .product-card h3 {
            font-size: 1.3rem;
            color: var(--dark-bg);
            margin-bottom: 10px;
            font-weight: 600;
        }

        .product-card .description {
            color: #666;
            font-size: 0.9rem;
            margin-bottom: 15px;
            line-height: 1.5;
            min-height: 40px;
        }

        .product-price {
            font-size: 1.5rem;
            font-weight: bold;
            color: var(--primary-color);
            margin-bottom: 10px;
        }

        .product-stock {
            font-size: 0.85rem;
            color: #888;
            margin-bottom: 20px;
        }

        .product-stock.in-stock {
            color: var(--success-color);
            font-weight: 600;
        }

        .add-to-cart-btn {
            width: 100%;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            border: none;
            padding: 12px 20px;
            color: var(--dark-bg);
            font-weight: bold;
            font-size: 1rem;
            border-radius: 8px;
            cursor: pointer;
            transition: all var(--transition-speed) ease;
            position: relative;
            overflow: hidden;
        }

        .add-to-cart-btn::before {
            content: '🛒';
            position: absolute;
            left: -30px;
            transition: left var(--transition-speed) ease;
        }

        .add-to-cart-btn:hover::before {
            left: 20px;
        }

        .add-to-cart-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(41, 217, 213, 0.4);
        }

        .add-to-cart-btn:active {
            transform: translateY(0);
        }

        .empty-message {
            text-align: center;
            color: #888;
            font-size: 1.2rem;
            padding: 60px 20px;
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
            max-width: 600px;
            margin: 0 auto;
        }

        .empty-message-icon {
            font-size: 4rem;
            margin-bottom: 20px;
            opacity: 0.3;
        }

        /* Toast Notification */
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
            background: #ff4757;
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

        /* Responsive */
        @media (max-width: 768px) {
            .banner h1 {
                font-size: 2.5rem;
            }

            .products-grid {
                grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
                gap: 20px;
            }

            .product-card {
                max-width: 100%;
            }
        }
    </style>
</head>
<body>

<?php
    $data = include '../back/category.php';
    $categorie_recuperer = $data['categories'];
    $produits = $data['produits'];
    include 'navbar.php';
    
    // Afficher les messages de session
    if (isset($_SESSION['cart_message'])) {
        echo '<div class="toast show" id="cartToast">' . htmlspecialchars($_SESSION['cart_message']) . '</div>';
        unset($_SESSION['cart_message']);
    }
    if (isset($_SESSION['cart_error'])) {
        echo '<div class="toast error show" id="cartToast">' . htmlspecialchars($_SESSION['cart_error']) . '</div>';
        unset($_SESSION['cart_error']);
    }
    // Afficher les messages de connexion/inscription
    if (isset($_SESSION['login_success'])) {
        echo '<div class="toast show" id="loginToast" style="background: #2ed573;">' . htmlspecialchars($_SESSION['login_success']) . '</div>';
        unset($_SESSION['login_success']);
    }
    if (isset($_SESSION['inscription_success'])) {
        echo '<div class="toast show" id="inscriptionToast" style="background: #2ed573;">' . htmlspecialchars($_SESSION['inscription_success']) . '</div>';
        unset($_SESSION['inscription_success']);
    }
?>

<!-- BANNIÈRE -->
<div class="banner">
    <h1>🛍️ Nos Catégories</h1>
    <p>Découvrez notre sélection de produits exceptionnels</p>
</div>

<!-- BOUTONS CATÉGORIES -->
<div class="categories-container">
    <div class="categories-wrapper">
        <?php foreach ($categorie_recuperer as $categorie): ?>
            <a href="?category=<?= urlencode($categorie['Id_category']) ?>" class="category-btn">
                <span><?= htmlspecialchars($categorie['name_category']) ?></span>
            </a>
        <?php endforeach; ?>
    </div>
</div>

<!-- AFFICHAGE DES PRODUITS -->
<?php if (!empty($produits)): ?>
    <div class="products-container">
        <h2 class="products-title">Produits de la catégorie sélectionnée</h2>
        <div class="products-grid">
            <?php foreach ($produits as $produit): ?>
                <div class="product-card">
                    <div class="product-image-wrapper">
                        <?php 
                            // Utiliser picture_product si disponible, sinon générer le nom
                            if (isset($produit['picture_product']) && !empty($produit['picture_product'])) {
                                $imagePath = 'uploads/' . $produit['picture_product'];
                            } else {
                                $imageFile = $produit['Id_product'] . '.png';
                                $imagePath = 'uploads/' . $imageFile;
                            }
                            
                            if (!file_exists($imagePath)) {
                                $imagePath = 'uploads/default.png';
                            }
                        ?>
                        <img src="<?= $imagePath ?>" class="product-image" alt="<?= htmlspecialchars($produit['name_product'] ?? $produit['name'] ?? 'Produit') ?>">
                    </div>
                    <h3><?= htmlspecialchars($produit['name_product'] ?? $produit['name'] ?? 'Produit sans nom') ?></h3>
                    <p class="description"><?= htmlspecialchars($produit['description_product'] ?? $produit['description'] ?? 'Aucune description') ?></p>
                    <div class="product-price"><?= number_format($produit['price_product'] ?? $produit['price'] ?? 0, 2) ?> €</div>
                    <p class="product-stock <?= ($produit['stock_quantity'] ?? 0) > 0 ? 'in-stock' : '' ?>">
                        <?= ($produit['stock_quantity'] ?? 0) > 0 ? '✓ En stock (' . ($produit['stock_quantity']) . ')' : '✗ Rupture de stock' ?>
                    </p>

                    <form action="../back/add_to_cart.php" method="post" class="add-to-cart-form">
                        <input type="hidden" name="id_product" value="<?= $produit['Id_product'] ?>">
                        <input type="hidden" name="redirect_url" value="category.php<?= isset($_GET['category']) ? '?category=' . intval($_GET['category']) : '' ?>">
                        <button type="submit" class="add-to-cart-btn" <?= ($produit['stock_quantity'] ?? 0) <= 0 ? 'disabled style="opacity: 0.5; cursor: not-allowed;"' : '' ?>>
                            <?= ($produit['stock_quantity'] ?? 0) > 0 ? 'Ajouter au panier' : 'Rupture de stock' ?>
                        </button>
                    </form>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
<?php elseif (isset($_GET['category'])): ?>
    <div class="products-container">
        <div class="empty-message">
            <div class="empty-message-icon">📦</div>
            <h2>Aucun produit trouvé</h2>
            <p>Aucun produit n'est disponible pour cette catégorie pour le moment.</p>
        </div>
    </div>
<?php else: ?>
    <div class="products-container">
        <div class="empty-message">
            <div class="empty-message-icon">👆</div>
            <h2>Sélectionnez une catégorie</h2>
            <p>Choisissez une catégorie ci-dessus pour voir les produits disponibles.</p>
        </div>
    </div>
<?php endif; ?>

<div class="toast" id="toast"></div>

<script>
// Gestion du toast
document.addEventListener('DOMContentLoaded', function() {
    const toast = document.getElementById('cartToast');
    if (toast) {
        setTimeout(() => {
            toast.classList.remove('show');
            setTimeout(() => toast.remove(), 300);
        }, 3000);
    }

    // Animation des formulaires
    document.querySelectorAll('.add-to-cart-form').forEach(form => {
        form.addEventListener('submit', function(e) {
            const btn = this.querySelector('.add-to-cart-btn');
            if (btn.disabled) {
                e.preventDefault();
                return false;
            }
            btn.textContent = 'Ajout en cours...';
            btn.disabled = true;
        });
    });
});
</script>

</body>
</html>