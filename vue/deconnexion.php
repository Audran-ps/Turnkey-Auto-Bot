<?php
session_start();

// Détruire toutes les variables de session
$_SESSION = array();

// Détruire la session
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Détruire la session
session_destroy();

// Rediriger vers la page d'accueil
header('Location: category.php');
exit;
?>


