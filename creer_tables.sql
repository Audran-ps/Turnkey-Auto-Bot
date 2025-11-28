-- ============================================
-- SCRIPT SQL POUR CRÉER LES TABLES NÉCESSAIRES
-- ============================================
-- Copiez TOUT ce fichier et exécutez-le dans phpMyAdmin
-- Menu : Base de données "ecommerce" > Onglet "SQL" > Collez > Exécutez

-- 1. Table CLIENT (pour les utilisateurs)
CREATE TABLE IF NOT EXISTS `client` (
  `id_client` int NOT NULL AUTO_INCREMENT,
  `nom` varchar(50) NOT NULL,
  `prenom` varchar(50) NOT NULL,
  `email` varchar(255) NOT NULL,
  `mpd` varchar(255) NOT NULL,
  `date_creation` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_client`),
  UNIQUE KEY `email` (`email`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- 2. Table COMMANDE (pour les commandes)
CREATE TABLE IF NOT EXISTS `commande` (
  `id_commande` int NOT NULL AUTO_INCREMENT,
  `id_client` int NOT NULL,
  `date_commande` datetime DEFAULT CURRENT_TIMESTAMP,
  `montant_total` decimal(10,2) NOT NULL,
  `statut` varchar(50) DEFAULT 'en_attente',
  `methode_paiement` varchar(50) DEFAULT NULL,
  `adresse_livraison` text,
  `ville_livraison` varchar(100),
  `code_postal_livraison` varchar(20),
  PRIMARY KEY (`id_commande`),
  KEY `id_client` (`id_client`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- 3. Table COMMANDE_DETAILS (détails des commandes)
CREATE TABLE IF NOT EXISTS `commande_details` (
  `id_details` int NOT NULL AUTO_INCREMENT,
  `id_commande` int NOT NULL,
  `id_product` int NOT NULL,
  `nom_product` varchar(255) NOT NULL,
  `prix_unitaire` decimal(10,2) NOT NULL,
  `quantite` int NOT NULL,
  `sous_total` decimal(10,2) NOT NULL,
  PRIMARY KEY (`id_details`),
  KEY `id_commande` (`id_commande`),
  KEY `id_product` (`id_product`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- 4. Table PRODUCT (si elle n'existe pas)
CREATE TABLE IF NOT EXISTS `product` (
  `id_product` int NOT NULL AUTO_INCREMENT,
  `name_product` varchar(255) NOT NULL,
  `description_product` text,
  `price_product` decimal(10,2) NOT NULL,
  `picture_product` varchar(255) DEFAULT NULL,
  `id_category` int DEFAULT NULL,
  `stock_quantity` int DEFAULT 0,
  PRIMARY KEY (`id_product`),
  KEY `id_category` (`id_category`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- 5. Table CATEGORY (si elle n'existe pas)
CREATE TABLE IF NOT EXISTS `category` (
  `Id_category` int NOT NULL AUTO_INCREMENT,
  `name_category` varchar(255) NOT NULL,
  PRIMARY KEY (`Id_category`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- ============================================
-- FIN DU SCRIPT
-- ============================================
-- Après exécution, vous devriez voir "5 requêtes exécutées avec succès"


