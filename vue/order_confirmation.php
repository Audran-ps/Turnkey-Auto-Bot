<?php
session_start();

if (!isset($_GET['id'])) {
    header('Location: category.php');
    exit;
}

$order_id = intval($_GET['id']);

// Connexion à la base de données
$host = 'localhost';
$dbname = 'ecommerce';
$user = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Récupérer les détails de la commande
    $stmt = $pdo->prepare("
        SELECT c.*, cl.nom, cl.prenom, cl.email 
        FROM commande c 
        JOIN client cl ON c.id_client = cl.id_client 
        WHERE c.id_commande = :id AND c.id_client = :client_id
    ");
    $stmt->execute([
        ':id' => $order_id,
        ':client_id' => $_SESSION['user']['id'] ?? 0
    ]);
    $commande = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$commande) {
        header('Location: category.php');
        exit;
    }
    
    // Récupérer les détails des produits
    $stmt = $pdo->prepare("SELECT * FROM commande_details WHERE id_commande = :id");
    $stmt->execute([':id' => $order_id]);
    $details = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    header('Location: category.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmation de commande - Nanos</title>
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
            --success-color: #2ed573;
        }

        body {
            font-family: 'Segoe UI', Roboto, 'Helvetica Neue', sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .confirmation-container {
            background: white;
            border-radius: 20px;
            padding: 40px;
            max-width: 600px;
            width: 100%;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
            text-align: center;
            animation: fadeInUp 0.6s ease;
        }

        .success-icon {
            font-size: 5rem;
            margin-bottom: 20px;
        }

        h1 {
            color: var(--success-color);
            margin-bottom: 15px;
        }

        .order-number {
            font-size: 1.5rem;
            color: var(--dark-bg);
            margin-bottom: 30px;
            font-weight: bold;
        }

        .order-details {
            text-align: left;
            margin: 30px 0;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 10px;
        }

        .detail-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #e0e0e0;
        }

        .detail-row:last-child {
            border-bottom: none;
        }

        .btn {
            display: inline-block;
            padding: 15px 30px;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            text-decoration: none;
            border-radius: 10px;
            font-weight: bold;
            margin-top: 20px;
            transition: all 0.3s ease;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(41, 217, 213, 0.4);
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
    </style>
</head>
<body>
    <div class="confirmation-container">
        <div class="success-icon">✅</div>
        <h1>Commande confirmée !</h1>
        <div class="order-number">Commande #<?= $order_id ?></div>
        
        <div class="order-details">
            <div class="detail-row">
                <span><strong>Montant total:</strong></span>
                <span><?= number_format($commande['montant_total'], 2, ',', ' ') ?> €</span>
            </div>
            <div class="detail-row">
                <span><strong>Méthode de paiement:</strong></span>
                <span><?= htmlspecialchars(ucfirst($commande['methode_paiement'] ?? 'Non spécifiée')) ?></span>
            </div>
            <div class="detail-row">
                <span><strong>Statut:</strong></span>
                <span><?= htmlspecialchars(ucfirst(str_replace('_', ' ', $commande['statut']))) ?></span>
            </div>
            <div class="detail-row">
                <span><strong>Date:</strong></span>
                <span><?= date('d/m/Y H:i', strtotime($commande['date_commande'])) ?></span>
            </div>
        </div>
        
        <p style="margin-top: 20px; color: #666;">
            Un email de confirmation a été envoyé à <?= htmlspecialchars($commande['email']) ?>
        </p>
        
        <a href="category.php" class="btn">Retour à l'accueil</a>
    </div>
</body>
</html>

