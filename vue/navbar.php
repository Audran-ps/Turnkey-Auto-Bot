<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Nanos</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
            --transition-speed: 0.3s;
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

        .responsive-menu {
            display: none;
            width: 40px;
            height: 40px;
            position: relative;
            cursor: pointer;
            background: none;
            border: none;
        }

        .responsive-menu span {
            position: absolute;
            width: 100%;
            height: 3px;
            background-color: var(--primary-color);
            left: 0;
            transition: all 0.4s ease;
        }

        .responsive-menu span:nth-child(1) {
            top: 10px;
        }

        .responsive-menu span:nth-child(2) {
            top: 20px;
        }

        .responsive-menu span:nth-child(3) {
            top: 30px;
        }

        .responsive-menu.active span:nth-child(1) {
            transform: rotate(45deg);
            top: 18px;
        }

        .responsive-menu.active span:nth-child(2) {
            opacity: 0;
        }

        .responsive-menu.active span:nth-child(3) {
            transform: rotate(-45deg);
            top: 18px;
        }

        .user-info {
            display: flex;
            align-items: center;
            color: var(--white-text);
            margin-right: 15px;
        }

        .user-info img {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            margin-right: 10px;
        }

        @media (max-width: 768px) {
            .menu {
                display: none;
                flex-direction: column;
                position: absolute;
                top: 60px;
                right: 0;
                background-color: var(--dark-bg);
                width: 250px;
                padding: 15px 0;
                box-shadow: 0 5px 10px rgba(0, 0, 0, 0.5);
                animation: slideFadeIn 0.4s ease forwards;
            }

            .menu.responsive {
                display: flex;
            }

            .menu li {
                margin: 10px 0;
                padding: 0 20px;
                width: 100%;
            }

            .menu li a {
                padding: 8px 0;
            }

            .responsive-menu {
                display: block;
            }

            .btn-reservation {
                display: none;
            }

            .user-info {
                margin-right: 0;
                margin-bottom: 10px;
                padding-left: 20px;
            }
        }

        @keyframes slideFadeIn {
            0% {
                opacity: 0;
                transform: translateY(-10px);
            }
            100% {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>
<body>

<header>
    <div class="logo">
        <a href="category.php">N<span>anos</span></a>
    </div>

    <ul class="menu">
        <?php if(isset($_SESSION['user'])): ?>
            <div class="user-info">
                <img src="<?= htmlspecialchars($_SESSION['user']['avatar'] ?? 'uploads/default.png') ?>" alt="Avatar" onerror="this.src='uploads/default.png'">
                <span><?= htmlspecialchars($_SESSION['user']['prenom'] ?? 'Utilisateur') ?></span>
            </div>
            <li><a href="category.php">Accueil</a></li>
            <li><a href="profil.php">Mon Profil</a></li>
            <li><a href="deconnexion.php">Déconnexion</a></li>
        <?php else: ?>
            <li><a href="category.php">Accueil</a></li>
            <li><a href="inscription.php">Inscription</a></li>
            <li><a href="conection.php">Connexion</a></li>
        <?php endif; ?>
        
        <li>
            <a href="panier.php">
                🛒 Panier 
                <span class="cart-count">
                    <?= isset($_SESSION['panier']) ? array_sum(array_column($_SESSION['panier'], 'quantity')) : 0 ?>
                </span>
            </a>
        </li>
    </ul>

    <a href="contact.php" class="btn-reservation">Contact</a>
    <button class="responsive-menu">
        <span></span>
        <span></span>
        <span></span>
    </button>
</header>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const burger = document.querySelector('.responsive-menu');
        const menu = document.querySelector('.menu');
        const links = document.querySelectorAll('.menu li:not(.user-info)');

        burger.addEventListener('click', () => {
            burger.classList.toggle('active');
            menu.classList.toggle('responsive');

            if (menu.classList.contains('responsive')) {
                links.forEach((link, index) => {
                    link.style.opacity = "0";
                    link.style.transform = "translateX(20px)";
                    setTimeout(() => {
                        link.style.opacity = "1";
                        link.style.transform = "translateX(0)";
                        link.style.transition = `all 0.3s ease ${index * 100}ms`;
                    }, 50);
                });
            } else {
                links.forEach((link) => {
                    link.style.opacity = "1";
                    link.style.transform = "none";
                    link.style.transition = "none";
                });
            }
        });

        // Fermer le menu quand on clique à l'extérieur
        document.addEventListener('click', function(event) {
            if (!menu.contains(event.target) && !burger.contains(event.target)) {
                burger.classList.remove('active');
                menu.classList.remove('responsive');
            }
        });
    });
</script>

</body>
</html>
