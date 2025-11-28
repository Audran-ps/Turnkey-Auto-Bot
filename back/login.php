<?php
session_start();

// Connexion à la base de données
$host = 'localhost';
$dbname = 'ecommerce';
$user = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    $_SESSION['login_error'] = 'Erreur de connexion à la base de données';
    header('Location: ../vue/conection.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['username']) && isset($_POST['password'])) {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    
    if (empty($username) || empty($password)) {
        $_SESSION['login_error'] = 'Veuillez remplir tous les champs';
        header('Location: ../vue/conection.php');
        exit;
    }
    
    try {
        // Vérifier si la table client existe
        try {
            $testStmt = $pdo->query("SELECT 1 FROM client LIMIT 1");
        } catch (PDOException $e) {
            $_SESSION['login_error'] = 'ERREUR : La table "client" n\'existe pas dans la base de données. Veuillez exécuter le fichier "creer_tables.sql" dans phpMyAdmin.';
            header('Location: ../vue/conection.php');
            exit;
        }
        
        // Vérifier si c'est un email ou un nom d'utilisateur
        $isEmail = filter_var($username, FILTER_VALIDATE_EMAIL);
        
        if ($isEmail) {
            // Recherche par email
            $stmt = $pdo->prepare("SELECT * FROM client WHERE email = :identifier");
        } else {
            // Recherche par nom ou prénom
            $stmt = $pdo->prepare("SELECT * FROM client WHERE nom = :identifier OR prenom = :identifier OR email = :identifier");
        }
        
        $stmt->bindParam(':identifier', $username, PDO::PARAM_STR);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user) {
            // Vérifier le mot de passe
            // Si le mot de passe est hashé avec password_hash
            if (password_verify($password, $user['mpd'])) {
                // Connexion réussie
                $_SESSION['user'] = [
                    'id' => $user['id_client'],
                    'nom' => $user['nom'],
                    'prenom' => $user['prenom'],
                    'email' => $user['email']
                ];
                $_SESSION['login_success'] = 'Connexion réussie !';
                header('Location: ../vue/category.php');
                exit;
            } 
            // Si le mot de passe n'est pas hashé (ancien système)
            elseif ($user['mpd'] === $password) {
                // Mettre à jour avec un hash
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                $updateStmt = $pdo->prepare("UPDATE client SET mpd = :password WHERE id_client = :id");
                $updateStmt->execute([':password' => $hashedPassword, ':id' => $user['id_client']]);
                
                $_SESSION['user'] = [
                    'id' => $user['id_client'],
                    'nom' => $user['nom'],
                    'prenom' => $user['prenom'],
                    'email' => $user['email']
                ];
                $_SESSION['login_success'] = 'Connexion réussie !';
                header('Location: ../vue/category.php');
                exit;
            } else {
                $_SESSION['login_error'] = 'Mot de passe incorrect';
                header('Location: ../vue/conection.php');
                exit;
            }
        } else {
            $_SESSION['login_error'] = 'Nom d\'utilisateur ou email incorrect';
            header('Location: ../vue/conection.php');
            exit;
        }
} catch (PDOException $e) {
    // Message d'erreur plus clair
    $errorMessage = 'Erreur lors de la connexion';
    if (strpos($e->getMessage(), "Base table or view not found") !== false) {
        $errorMessage = 'La table "client" n\'existe pas dans la base de données. Veuillez exécuter le script database_tables.sql';
    } else {
        $errorMessage = 'Erreur lors de la connexion : ' . $e->getMessage();
    }
    $_SESSION['login_error'] = $errorMessage;
    header('Location: ../vue/conection.php');
    exit;
}
} else {
    header('Location: ../vue/conection.php');
    exit;
}
?>

