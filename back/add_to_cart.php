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
    $_SESSION['cart_error'] = 'Erreur de connexion à la base de données';
    header('Location: ../vue/category.php');
    exit;
}

// Fonction pour ajouter un produit au panier
function addToCart($productId, $productName, $productPrice, $productImage, $quantity = 1) {
    // Initialiser le panier s'il n'existe pas
    if (!isset($_SESSION['panier'])) {
        $_SESSION['panier'] = [];
    }
    
    // Vérifier si le produit existe déjà dans le panier
    $productExists = false;
    foreach ($_SESSION['panier'] as &$item) {
        if ($item['id'] == $productId) {
            $item['quantity'] += $quantity;
            $productExists = true;
            break;
        }
    }
    
    // Si le produit n'existe pas, l'ajouter
    if (!$productExists) {
        $_SESSION['panier'][] = [
            'id' => $productId,
            'name' => $productName,
            'price' => $productPrice,
            'image' => $productImage,
            'quantity' => $quantity
        ];
    }
    
    return true;
}

// Traitement de l'ajout au panier
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_product'])) {
    $productId = intval($_POST['id_product']);
    $quantity = intval($_POST['quantity'] ?? 1);
    
    try {
        // Récupérer les vraies données du produit depuis la base de données
        $stmt = $pdo->prepare('SELECT * FROM product WHERE id_product = :id_product');
        $stmt->bindParam(':id_product', $productId, PDO::PARAM_INT);
        $stmt->execute();
        $product = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($product) {
            // Ajouter au panier avec les vraies données
            if (addToCart(
                $product['id_product'],
                $product['name_product'],
                $product['price_product'],
                $product['picture_product'],
                $quantity
            )) {
                $_SESSION['cart_message'] = 'Produit ajouté au panier avec succès !';
            }
        } else {
            $_SESSION['cart_error'] = 'Produit introuvable';
        }
    } catch (Exception $e) {
        $_SESSION['cart_error'] = 'Erreur lors de l\'ajout au panier : ' . $e->getMessage();
    }
    
    // Redirection vers la page category.php dans le dossier vue
    $redirectUrl = $_POST['redirect_url'] ?? '../vue/category.php';
    // Si l'URL ne commence pas par ../, c'est qu'elle est relative au dossier vue
    if (strpos($redirectUrl, '../') === false && strpos($redirectUrl, 'http') === false) {
        $redirectUrl = '../vue/' . $redirectUrl;
    }
    if (isset($_GET['category'])) {
        $separator = strpos($redirectUrl, '?') !== false ? '&' : '?';
        $redirectUrl .= $separator . 'category=' . intval($_GET['category']);
    }
    header('Location: ' . $redirectUrl);
    exit;
}

// Si accès direct sans POST, rediriger
header('Location: ../vue/category.php');
exit;
?>