<?php
session_start();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Raleway', sans-serif;
        }

        body {
            background-color: #111;
            color: #fff;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 20px;
            animation: fadeIn 1s ease-out;
        }

        form {
            background-color: #222;
            padding: 30px;
            border-radius: 10px;
            width: 100%;
            max-width: 400px;
            box-shadow: 0 0 10px #29d9d5;
            margin-bottom: 20px;
            transform: translateY(20px);
            opacity: 0;
            animation: slideUp 0.6s ease-out 0.3s forwards;
        }

        h1 {
            text-align: center;
            color: #29d9d5;
            margin-bottom: 20px;
            font-size: 2.2rem;
            opacity: 0;
            animation: fadeIn 0.8s ease-out 0.2s forwards;
        }

        label {
            display: block;
            margin: 10px 0 5px;
            font-weight: 500;
            opacity: 0;
            transform: translateX(-10px);
            animation: slideIn 0.5s ease-out forwards;
        }

        input[type="text"],
        input[type="password"] {
            width: 100%;
            padding: 12px;
            background-color: transparent;
            border: 1px solid #29d9d5;
            color: #fff;
            border-radius: 5px;
            font-size: 1rem;
            transition: all 0.3s ease;
            opacity: 0;
            animation: fadeIn 0.6s ease-out 0.4s forwards;
            margin-bottom: 15px;
        }

        input[type="text"]:focus,
        input[type="password"]:focus {
            box-shadow: 0 0 8px rgba(41, 217, 213, 0.6);
            transform: scale(1.02);
            outline: none;
        }

        input[type="submit"] {
            margin-top: 20px;
            width: 100%;
            padding: 12px;
            background-color: #29d9d5;
            border: none;
            color: #111;
            font-weight: bold;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.3s ease;
            border-radius: 5px;
            opacity: 0;
            animation: fadeIn 0.6s ease-out 0.5s forwards;
            position: relative;
            overflow: hidden;
        }

        input[type="submit"]:hover {
            background-color: #1fb3b0;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(31, 179, 176, 0.4);
        }

        input[type="submit"]::after {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: 0.5s;
        }

        input[type="submit"]:hover::after {
            left: 100%;
        }

        .signup-link {
            text-align: center;
            margin-top: 20px;
            font-size: 14px;
            opacity: 0;
            animation: fadeIn 0.6s ease-out 0.6s forwards;
        }

        .signup-link a {
            color: #29d9d5;
            text-decoration: none;
            font-weight: bold;
            transition: all 0.3s ease;
            position: relative;
        }

        .signup-link a:hover {
            color: #1fb3b0;
        }

        .signup-link a::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            width: 0;
            height: 1px;
            background-color: #1fb3b0;
            transition: width 0.3s ease;
        }

        .signup-link a:hover::after {
            width: 100%;
        }

        form[action="../back/mdp_oublie.php"] {
            background-color: transparent;
            box-shadow: none;
            padding: 0;
            text-align: center;
            max-width: 400px;
            width: 100%;
            animation: fadeIn 0.8s ease-out 0.7s forwards;
        }

        form[action="../back/mdp_oublie.php"] input[type="submit"] {
            background-color: transparent;
            border: none;
            color: #29d9d5;
            font-size: 0.9rem;
            text-decoration: underline;
            cursor: pointer;
            padding: 0;
            margin-top: 0;
            width: auto;
            animation: pulse 2s infinite 1s;
        }

        form[action="../back/mdp_oublie.php"] input[type="submit"]:hover {
            color: #1fb3b0;
            text-decoration: none;
            transform: none;
            box-shadow: none;
            animation: none;
        }

        /* Animations */
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        @keyframes slideUp {
            from { 
                opacity: 0;
                transform: translateY(20px);
            }
            to { 
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes slideIn {
            from { 
                opacity: 0;
                transform: translateX(-10px);
            }
            to { 
                opacity: 1;
                transform: translateX(0);
            }
        }

        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }

        /* Animation séquentielle pour les labels */
        label[for="username"] { animation-delay: 0.3s; }
        label[for="password"] { animation-delay: 0.4s; }
    </style>
</head>
<body>
    <?php
        include 'navbar.php';
        
        // Afficher les messages d'erreur ou de succès
        if (isset($_SESSION['login_error'])) {
            echo '<div style="background: #ff4757; color: white; padding: 15px; border-radius: 5px; margin-bottom: 20px; text-align: center;">' . htmlspecialchars($_SESSION['login_error']) . '</div>';
            unset($_SESSION['login_error']);
        }
        if (isset($_SESSION['login_success'])) {
            echo '<div style="background: #2ed573; color: white; padding: 15px; border-radius: 5px; margin-bottom: 20px; text-align: center;">' . htmlspecialchars($_SESSION['login_success']) . '</div>';
            unset($_SESSION['login_success']);
        }
    ?>
    
    <!-- Formulaire de connexion -->
    <form action="../back/login.php" method="POST">
        <h1>Connexion</h1>
        
        <label for="username"><b>Nom d'utilisateur</b></label>
        <input type="text" id="username" name="username" placeholder="Entrer votre nom d'utilisateur" required>
        
        <label for="password"><b>Mot de passe</b></label>
        <input type="password" id="password" name="password" placeholder="Entrer votre mot de passe" required>
        
        <input type="submit" id="submit" value="LOGIN">
        <p class="signup-link">Pas encore inscrit ? <a href="inscription.php">Créez un compte ici</a>.</p>
    </form>
    
    <form action="../back/mdp_oublie.php" method="post">
        <input type="submit" value="mot de passe oublié">
    </form>

    <script>
        // Ajout d'un effet de vibration en cas de saisie incorrecte
        document.querySelector('form').addEventListener('submit', function(e) {
            const inputs = document.querySelectorAll('input[type="text"], input[type="password"]');
            let isValid = true;
            
            inputs.forEach(input => {
                if (!input.value.trim()) {
                    input.style.borderColor = '#ff5555';
                    input.style.animation = 'shake 0.5s';
                    isValid = false;
                    
                    // Reset animation after it completes
                    setTimeout(() => {
                        input.style.animation = '';
                    }, 500);
                }
            });
            
            if (!isValid) {
                e.preventDefault();
            }
        });

        // Reset border color when user starts typing
        document.querySelectorAll('input').forEach(input => {
            input.addEventListener('input', function() {
                this.style.borderColor = '#29d9d5';
            });
        });

        // Add shake animation to CSS
        const style = document.createElement('style');
        style.textContent = `
            @keyframes shake {
                0%, 100% { transform: translateX(0); }
                20%, 60% { transform: translateX(-5px); }
                40%, 80% { transform: translateX(5px); }
            }
        `;
        document.head.appendChild(style);
    </script>
</body>
</html>