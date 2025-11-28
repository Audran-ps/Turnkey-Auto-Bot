<?php
session_start();
include 'navbar.php';

if (isset($_GET['refresh_captcha'])) {
    unset($_SESSION['captcha_code']);
}

if (!isset($_SESSION['captcha_code'])) {
    $_SESSION['captcha_code'] = rand(1000, 9999);
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Page d'Inscription</title>
    <link href="https://fonts.googleapis.com/css2?family=Raleway:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        
        body {
            background-color: #0f0f0f;
            font-family: 'Raleway', sans-serif;
            color: white;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
            animation: fadeIn 0.8s ease-out;
        }

        .signup-container {
            width: 100%;
            max-width: 480px;
            background-color: #1c1c1c;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 0 20px rgba(0, 230, 230, 0.3);
            transform: translateY(20px);
            opacity: 0;
            animation: slideUp 0.6s ease-out 0.3s forwards;
        }

        .signup-container h2 {
            color: #00e6e6;
            font-size: 28px;
            margin-bottom: 30px;
            text-align: center;
            font-weight: 600;
            opacity: 0;
            animation: fadeIn 0.8s ease-out 0.4s forwards;
        }

        .form-group {
            margin-bottom: 20px;
            opacity: 0;
            transform: translateX(-10px);
            animation: slideIn 0.5s ease-out forwards;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-size: 16px;
            color: #00e6e6;
            font-weight: 400;
        }

        .form-group input {
            width: 100%;
            padding: 14px 18px;
            background-color: #2a2a2a;
            border: 1px solid #00e6e6;
            border-radius: 6px;
            font-size: 15px;
            color: white;
            transition: all 0.3s ease;
        }

        .form-group input:focus {
            outline: none;
            border-color: #00cccc;
            box-shadow: 0 0 0 3px rgba(0, 204, 204, 0.2);
            transform: scale(1.02);
        }

        .captcha-section {
            background-color: #2a2a2a;
            padding: 20px;
            border-radius: 6px;
            margin: 30px 0;
            border: 1px solid #00e6e6;
            opacity: 0;
            animation: fadeIn 0.6s ease-out 0.6s forwards;
        }

        .captcha-display {
            font-size: 24px;
            letter-spacing: 3px;
            background: white;
            color: #1c1c1c;
            padding: 12px 20px;
            border-radius: 4px;
            display: inline-block;
            margin: 8px 0 15px;
            font-weight: 600;
        }

        .refresh-btn {
            background: none;
            border: none;
            color: #00e6e6;
            font-size: 16px;
            cursor: pointer;
            margin-left: 10px;
            transition: all 0.3s ease;
        }

        .refresh-btn:hover {
            transform: rotate(180deg);
            color: #00cccc;
        }

        .submit-btn {
            width: 100%;
            padding: 16px;
            background: linear-gradient(135deg, #00e6e6, #00b3b3);
            border: none;
            border-radius: 6px;
            color: #1c1c1c;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 15px;
            opacity: 0;
            animation: fadeIn 0.6s ease-out 0.7s forwards;
            position: relative;
            overflow: hidden;
        }

        .submit-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0, 230, 230, 0.3);
        }

        .submit-btn::after {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: 0.5s;
        }

        .submit-btn:hover::after {
            left: 100%;
        }

        .login-link {
            text-align: center;
            margin-top: 25px;
            font-size: 15px;
            opacity: 0;
            animation: fadeIn 0.6s ease-out 0.8s forwards;
        }

        .login-link a {
            color: #00e6e6;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            position: relative;
        }

        .login-link a:hover {
            color: #00cccc;
        }

        .login-link a::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            width: 0;
            height: 1px;
            background-color: #00cccc;
            transition: width 0.3s ease;
        }

        .login-link a:hover::after {
            width: 100%;
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

        /* Délais d'animation séquentiels */
        .form-group:nth-child(1) { animation-delay: 0.4s; }
        .form-group:nth-child(2) { animation-delay: 0.45s; }
        .form-group:nth-child(3) { animation-delay: 0.5s; }
        .form-group:nth-child(4) { animation-delay: 0.55s; }
        .form-group:nth-child(5) { animation-delay: 0.6s; }
    </style>
</head>
<body>
    <div class="signup-container">
        <h2>Inscription</h2>
        
        <?php
            // Afficher les messages d'erreur ou de succès
            if (isset($_SESSION['inscription_error'])) {
                echo '<div style="background: #ff4757; color: white; padding: 15px; border-radius: 5px; margin-bottom: 20px; text-align: center;">' . htmlspecialchars($_SESSION['inscription_error']) . '</div>';
                unset($_SESSION['inscription_error']);
            }
            if (isset($_SESSION['inscription_success'])) {
                echo '<div style="background: #2ed573; color: white; padding: 15px; border-radius: 5px; margin-bottom: 20px; text-align: center;">' . htmlspecialchars($_SESSION['inscription_success']) . '</div>';
                unset($_SESSION['inscription_success']);
            }
        ?>
        
        <form action="../back/inscription.php" method="post">
            <div class="form-group">
                <label for="username">Nom :</label>
                <input type="text" id="username" name="username" placeholder="Votre nom" required>
            </div>

            <div class="form-group">
                <label for="name">Prénom :</label>
                <input type="text" id="name" name="name" placeholder="Votre prénom" required>
            </div>

            <div class="form-group">
                <label for="email">Email :</label>
                <input type="email" id="email" name="email" placeholder="Votre email" required>
            </div>

            <div class="form-group">
                <label for="password">Mot de passe :</label>
                <input type="password" id="password" name="password" placeholder="Mot de passe" required>
            </div>

            <div class="form-group">
                <label for="confirm-password">Confirmer le mot de passe :</label>
                <input type="password" id="confirm-password" name="confirm-password" placeholder="Confirmer" required>
            </div>

            <div class="captcha-section">
                <label>Code de vérification :</label>
                <div class="captcha-display"><?php echo $_SESSION['captcha_code']; ?></div>
                <button type="button" class="refresh-btn" onclick="refreshCaptcha()">🔄 Regénérer</button>
                <input type="text" name="captcha" id="captcha" placeholder="Entrez le code ci-dessus" required>
            </div>

            <button type="submit" class="submit-btn">S'inscrire</button>
            
            <p class="login-link">Déjà inscrit ? <a href="conection.php">Connectez-vous ici</a>.</p>
        </form>
    </div>

    <script>
        function refreshCaptcha() {
            const btn = document.querySelector('.refresh-btn');
            btn.style.transform = 'rotate(360deg)';
            setTimeout(() => {
                window.location.href = window.location.pathname + '?refresh_captcha=true';
            }, 300);
        }

        // Validation des mots de passe
        document.querySelector('form').addEventListener('submit', function(e) {
            const password = document.getElementById('password');
            const confirmPassword = document.getElementById('confirm-password');
            
            if (password.value !== confirmPassword.value) {
                e.preventDefault();
                confirmPassword.style.borderColor = '#ff5555';
                confirmPassword.style.animation = 'shake 0.5s';
                
                setTimeout(() => {
                    confirmPassword.style.animation = '';
                }, 500);
            }
        });

        // Ajout de l'animation shake
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