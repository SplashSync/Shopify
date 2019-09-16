---
lang: fr
permalink: start/setup
title: Configuration du Compte
---

### Comptes vs Serveurs ? 

Les serveurs de comptes sont différents des serveurs standard car la connexion à votre boutique est gérée par Splash.

Pour ce faire, nous avons mis en place une communication avec l'API Sphopify. Vous n'avez pas de plug-in à installer sur Shopify.

### Step 1 - Créer un nouveau profil Shopify

Pour connecter votre Shopify Shop avec Splash, cliquez d’abord sur le bouton "Connecter".

![Add Shopify Account]({{ "/assets/img/add-account.png" | relative_url }})

### Step 2 - Configurez votre URL d'administrateur de la boutique

Configurez l'URL de votre nouveau compte Shopify.

Click on server Edit button and fill the form with the admin url of you shop. 
Cliquez sur le bouton  <i class="far fa-edit"></i> pour modifier le serveur et remplissez le formulaire avec l'URL d'administrateur de votre boutique.

Généralement, cette URL ressemble à ceci: myawesomeshop.myshopify.com. 

**N'utilisez pas votre domaine public ici, c'est uniquement pour un accès administrateur à votre compte Shopify.**

![Setup Shopify Admin Url]({{ "/assets/img/first-setup.png" | relative_url }})

### Step 3 - Connectez-vous à votre boutique

Maintenant que vous avez défini l'URL de la boutique, cliquez sur Connect to Shopify API pour lancer le processus d'authentification.

Ce processus nécessitera l'approbation de votre compte Shopify.

### Step 4 - Rafraîchissez votre serveur

Maintenant, Splash devrait avoir un jeton valide pour accéder à votre API Shopify Shop.

Vous pouvez actualiser votre connexion afin que Splash collecte toutes les informations relatives à votre boutique et à ses objets disponibles.

![Server Refresh]({{ "/assets/img/refresh.png" | relative_url }})

### Step 5 - Configuration des Webhooks

Les Webhooks Shopify sont utilisés pour informer Splash de toute modification apportée à votre compte Shopify.

Cela peut être une mise à jour d'une description ou d'un stock de produit, mais également une nouvelle commande reçue sur votre boutique.
  
Pour que Splash reçoive toutes ces notifications, vous devez lancer l’installation de webkook.

Ceci n'est fait qu'une fois.

### Step 6 - Sélectionnez l'entrepôt par défaut

Shopify gère maintenant les entrepôts multiples. Vous devez donc choisir l’entrepôt que Splash doit utiliser pour la synchronisation des stocks.

Cliquez sur le serveur <i class="far fa-edit"></i> bouton Modifier et sélectionnez l’entrepôt par défaut à utiliser.

Tout devrait bien se passer maintenant, vous pouvez utiliser votre nouveau serveur comme n'importe quel autre serveur pour synchroniser vos données Shopify!