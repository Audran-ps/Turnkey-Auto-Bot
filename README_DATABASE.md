# Instructions pour créer les tables de la base de données

## ⚠️ IMPORTANT : Exécuter ce script avant d'utiliser l'application

Pour que l'application fonctionne correctement, vous devez créer les tables nécessaires dans votre base de données.

## 📋 Étapes à suivre :

1. **Ouvrez phpMyAdmin** dans votre navigateur (généralement : http://localhost/phpmyadmin)

2. **Sélectionnez la base de données** `ecommerce` dans le menu de gauche

3. **Cliquez sur l'onglet "SQL"** en haut de la page

4. **Copiez-collez le contenu complet** du fichier `database_tables.sql` dans la zone de texte

5. **Cliquez sur "Exécuter"** (ou appuyez sur Ctrl+Entrée)

## ✅ Tables qui seront créées :

- **`client`** : Stocke les informations des utilisateurs (nom, prénom, email, mot de passe)
- **`commande`** : Stocke les commandes passées par les clients
- **`commande_details`** : Stocke les détails de chaque commande (produits, quantités, prix)
- **`product`** : Stocke les informations des produits (si elle n'existe pas déjà)
- **`category`** : Stocke les catégories de produits (si elle n'existe pas déjà)

## 🔍 Vérification :

Après l'exécution, vous devriez voir un message de succès. Vous pouvez vérifier que les tables ont été créées en cliquant sur le nom de la base de données `ecommerce` dans le menu de gauche.

## ❌ En cas d'erreur :

Si vous voyez une erreur indiquant qu'une table existe déjà, c'est normal. Le script utilise `CREATE TABLE IF NOT EXISTS`, donc les tables existantes ne seront pas modifiées.

Si vous avez d'autres erreurs, vérifiez que :
- Vous êtes bien connecté à MySQL/MariaDB
- La base de données `ecommerce` existe
- Vous avez les droits d'administration sur la base de données


