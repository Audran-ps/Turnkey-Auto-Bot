<?php
session_start();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user'])) {
    $_SESSION['checkout_error'] = 'Vous devez être connecté pour passer une commande';
    header('Location: ../vue/conection.php');
    exit;
}

// Vérifier si le panier n'est pas vide
if (empty($_SESSION['panier'])) {
    $_SESSION['checkout_error'] = 'Votre panier est vide';
    header('Location: ../vue/panier.php');
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
} catch (PDOException $e) {
    $_SESSION['checkout_error'] = 'Erreur de connexion à la base de données';
    header('Location: ../vue/checkout.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupérer les données du formulaire
    $nom_complet = trim($_POST['nom_complet'] ?? '');
    $email = filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL);
    $adresse = trim($_POST['adresse'] ?? '');
    $code_postal = trim($_POST['code_postal'] ?? '');
    $ville = trim($_POST['ville'] ?? '');
    $methode_paiement = $_POST['methode_paiement'] ?? 'carte';
    
    // Validation
    if (empty($nom_complet) || empty($email) || empty($adresse) || empty($code_postal) || empty($ville)) {
        $_SESSION['checkout_error'] = 'Veuillez remplir tous les champs';
        header('Location: ../vue/checkout.php');
        exit;
    }
    
    // Calculer le total
    $montant_total = 0;
    foreach ($_SESSION['panier'] as $item) {
        $montant_total += floatval($item['price'] ?? 0) * intval($item['quantity'] ?? 1);
    }
    
    try {
        // Démarrer une transaction
        $pdo->beginTransaction();
        
        // Créer la commande
        $stmt = $pdo->prepare("
            INSERT INTO commande (id_client, montant_total, statut, methode_paiement, adresse_livraison, ville_livraison, code_postal_livraison)
            VALUES (:id_client, :montant_total, 'en_attente', :methode_paiement, :adresse, :ville, :code_postal)
        ");
        
        $stmt->execute([
            ':id_client' => $_SESSION['user']['id'],
            ':montant_total' => $montant_total,
            ':methode_paiement' => $methode_paiement,
            ':adresse' => $adresse,
            ':ville' => $ville,
            ':code_postal' => $code_postal
        ]);
        
        $id_commande = $pdo->lastInsertId();
        
        // Ajouter les détails de la commande
        foreach ($_SESSION['panier'] as $item) {
            $prix_unitaire = floatval($item['price'] ?? 0);
            $quantite = intval($item['quantity'] ?? 1);
            $sous_total = $prix_unitaire * $quantite;
            
            $stmt = $pdo->prepare("
                INSERT INTO commande_details (id_commande, id_product, nom_product, prix_unitaire, quantite, sous_total)
                VALUES (:id_commande, :id_product, :nom_product, :prix_unitaire, :quantite, :sous_total)
            ");
            
            $stmt->execute([
                ':id_commande' => $id_commande,
                ':id_product' => $item['id'] ?? 0,
                ':nom_product' => $item['name'] ?? 'Produit sans nom',
                ':prix_unitaire' => $prix_unitaire,
                ':quantite' => $quantite,
                ':sous_total' => $sous_total
            ]);
        }
        
        // Valider la transaction
        $pdo->commit();
        
        // Vider le panier
        $_SESSION['panier'] = [];
        
        // Message de succès
        $_SESSION['order_success'] = 'Votre commande a été passée avec succès ! Numéro de commande: #' . $id_commande;
        
        // Rediriger vers une page de confirmation
        header('Location: ../vue/order_confirmation.php?id=' . $id_commande);
        exit;
        
    } catch (PDOException $e) {
        // Annuler la transaction en cas d'erreur
        $pdo->rollBack();
        $_SESSION['checkout_error'] = 'Erreur lors du traitement de la commande : ' . $e->getMessage();
        header('Location: ../vue/checkout.php');
        exit;
    }
} else {
    header('Location: ../vue/checkout.php');
    exit;
}
?>


