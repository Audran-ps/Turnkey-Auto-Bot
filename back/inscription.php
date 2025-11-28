<?php
session_start();

// Vérification CAPTCHA en premier
if (!isset($_POST['captcha']) || $_POST['captcha'] != $_SESSION['captcha_code']) {
    die('❌ Erreur : Le code CAPTCHA est incorrect.');
}
unset($_SESSION['captcha_code']);

// Connexion à la base de données
$host = 'localhost';
$dbname = 'ecommerce';
$user = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    $_SESSION['inscription_error'] = 'Erreur de connexion à la base de données. Veuillez vérifier que la table "client" existe.';
    header('Location: ../vue/inscription.php');
    exit;
}

// Récupération des données du formulaire
$name = $_POST['username'] ?? '';
$username = $_POST['name'] ?? '';
$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';
$confirm_password = $_POST['confirm-password'] ?? '';

// Vérification des mots de passe
if ($password !== $confirm_password) {
    die('❌ Les mots de passe ne correspondent pas.');
}

$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

// Vérifier si la table client existe
try {
    $testStmt = $pdo->query("SELECT 1 FROM client LIMIT 1");
} catch (PDOException $e) {
    $_SESSION['inscription_error'] = 'ERREUR : La table "client" n\'existe pas dans la base de données. Veuillez exécuter le fichier "creer_tables.sql" dans phpMyAdmin.';
    header('Location: ../vue/inscription.php');
    exit;
}

// Vérifier si l'email existe déjà
$checkStmt = $pdo->prepare("SELECT * FROM client WHERE email = :email");
$checkStmt->execute([':email' => $email]);
if ($checkStmt->fetch()) {
    $_SESSION['inscription_error'] = 'Cet email est déjà utilisé';
    header('Location: ../vue/inscription.php');
    exit;
}

// Insertion dans la base de données (table client)
try {
    $sql = "INSERT INTO client (nom, prenom, email, mpd) VALUES (:nom, :prenom, :email, :hashedPassword)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':nom' => $name,
        ':prenom' => $username,
        ':email' => $email,
        ':hashedPassword' => $hashedPassword,
    ]);
    
    $_SESSION['inscription_success'] = 'Inscription réussie ! Vous êtes maintenant connecté.';
    
    // Connecter automatiquement l'utilisateur après inscription
    $_SESSION['user'] = [
        'id' => $pdo->lastInsertId(),
        'nom' => $name,
        'prenom' => $username,
        'email' => $email
    ];
} catch (PDOException $e) {
    $_SESSION['inscription_error'] = 'Erreur lors de l\'inscription : ' . $e->getMessage();
    header('Location: ../vue/inscription.php');
    exit;
}

// ⚡ Envoi d'email avec PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require '../vendor/autoload.php';

$mail = new PHPMailer(true);

try {
    // Configuration du serveur SMTP
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'paysaudran@gmail.com';       // ➤ Ton adresse Gmail
    $mail->Password   = 'ibom vaod cibr mkvj';        // ➤ Mot de passe d'application
    $mail->SMTPSecure = 'tls';
    $mail->Port       = 587;

    // Expéditeur et destinataire
    $mail->setFrom('paysaudran@gmail.com', 'Nanos');
    $mail->addAddress($email, "$username $name");

    // Contenu du mail
    $mail->isHTML(true);
    $mail->Subject = "Bienvenue sur notre site Nanos, $username !";
    $mail->Body    = "
        <h1>Inscription réussie</h1>
        <p>Bonjour $username,</p>
        <p>Merci de vous être inscrit sur notre site. Vous pouvez maintenant vous connecter.</p>
        <p><a href='http://localhost/ecommerce/vue/conection.php'>Se connecter</a></p>
    ";

    $mail->send();
    // Redirection vers la page d'accueil après inscription réussie
    header('Location: ../vue/category.php');
    exit;
} catch (Exception $e) {
    // Même si l'email échoue, l'inscription est réussie
    header('Location: ../vue/category.php');
    exit;
}
