---
lang: fr
permalink: docs/migrate-to-private-app
title: Migration App Privée
---

### Pourquoi utiliser une application privée ?

Depuis le 15 juin 2023, l'utilisation de l'application privée Shopify avec Splash Sync devient obligatoire.

Ce changement améliorera la fiabilité et l'efficacité de nos interfaces avec Shopify, augmentera également la sécurité.

Si vous rencontrez toujours des problèmes avec les applications privées Shopify, veuillez contacter notre équipe d'assistance.

### Que va-t-on faire dans ce tutoriel ?

Nous vous guiderons pour la migration de votre connexion Splash <> Shopify Public App vers Splash <> Shopify Private App.

Ce processus est similaire à notre nouveau processus d'installation, mais nous vous demanderons d'abord de désinstaller toute application publique existante.

### Step 1 - Supprimer l'application publique

Pour connecter votre boutique Shopify, accédez à **Paramètres >> Applications et canaux de vente**.

Désinstallez maintenant notre application publique :

![Add Shopify Private App]({{ "/assets/img/uninstall-public-app.png" | relative_url }})

### Step 2 - Créer une application privée Shopify

La première étape du processus d'installation est la création d'une application Shopify dédiée que Splash Sync utilisera pour communiquer avec votre boutique.

Pour connecter votre boutique Shopify, accédez à **Paramètres >> Applications et canaux de vente >> Développer des applications**.

Créez maintenant une nouvelle application privée :

![Add Shopify Private App]({{ "/assets/img/private-app-create.png" | relative_url }})

### Step 3 - Configuration de l'API Admin

Maintenant que vous avez créé une application, vous devez configurer les droits de l'application afin que Splash puisse accéder aux informations requises.

Accédez à **Configuration >> Intégration de l'API de l'interface administrateur >> Modifier** et configurez les droits requis.

Vous devez sélectionner au moins la liste suivante :
- write_customers, read_customers
- write_fulfillments, read_fulfillments
- write_inventory, read_inventory
- write_orders, read_orders
- write_products, read_products
- read_locations

Votre configuration devrait ressembler à ceci.

![Admin API Configuration]({{ "/assets/img/private-app-config.png" | relative_url }})

<div class="callout-block callout-info py-3">
    <div class="icon-holder" style="top: 0px;">
        <i class="fas fa-exclamation-circle text-white"></i>
    </div>
    <div class="content">
        <p>Splash vérifie maintenant les droits, vous pouvez ajuster cette configuration à tout moment.</p>
    </div>
</div>

### Step 4 - Mettez à jour la configuration de votre serveur

Revenez maintenant à votre compte Splash Sync.

Accédez à **Mes comptes >> Ma boutique Shopify** et cliquez sur le bouton "Modifier".

### Step 5 - Configuration des Identifiants de l'API

C'est l'étape la plus critique ! Vous devez maintenant copier et coller toutes vos informations d'identification API dans votre configuration de serveur Splash Sync .

Accédez à l'onglet **Identifiants API** et copiez tous les identifiants de Shopify vers Splash.
![API credentials]({{ "/assets/img/private-app-credentials.png" | relative_url }})

<div class="callout-block callout-warning">
    <div class="icon-holder">
        <i class="fas fa-exclamation-circle"></i>
    </div>
    <div class="content">
        <h4 class="callout-title">Warning</h4>
        <p>Le jeton d'accès à l'API d'administration n'est visible qu'une seule fois ! Alors il est à copier avec précaution !</p>
    </div>
</div>

Côté Splash, cliquez sur le bouton Modifier du serveur <i class="far fa-edit"></i> et remplissez le formulaire.

Tout d'abord, entrez l'URL de l'administrateur de votre boutique, cette URL ressemble à : myawesomeshop.myshopify.com.
**N'utilisez pas votre domaine public ici, ceci est uniquement pour l'accès administrateur à votre compte Shopify.**

Ensuite, coupez et collez toutes les informations d'identification. Votre configuration ressemble à ceci :

![API credentials]({{ "/assets/img/private-app-splash-setup.png" | relative_url }})

### Step 6 - Rafraîchissez votre serveur

Maintenant, Splash devrait avoir un jeton valide pour accéder à votre API Shopify Shop.

Vous pouvez actualiser votre connexion afin que Splash collecte toutes les informations relatives à votre boutique et à ses objets disponibles.

![Server Refresh]({{ "/assets/img/refresh.png" | relative_url }})

### Step 7 - Configuration des Webhooks

Les Webhooks Shopify sont utilisés pour informer Splash de toute modification apportée à votre compte Shopify.

Cela peut être une mise à jour d'une description ou d'un stock de produit, mais également une nouvelle commande reçue sur votre boutique.

Pour que Splash reçoive toutes ces notifications, vous devez lancer la configuration des webhook.

Une fois cela fait, votre profil de serveur ne devrait afficher que des drapeaux verts !

![API credentials]({{ "/assets/img/configuration-ok.png" | relative_url }})

### Step 8 - Sélectionnez l'entrepôt par défaut

Shopify gère maintenant les entrepôts. Vous devez donc choisir l’entrepôt que Splash doit utiliser pour la synchronisation des stocks.

Cliquez sur le serveur <i class="far fa-edit"></i> bouton Modifier et sélectionnez l’entrepôt par défaut à utiliser.

<div class="callout-block callout-success">
    <div class="icon-holder">
        <i class="fas fa-check-double"></i>
    </div>
    <div class="content">
        <h4 class="callout-title">C'est bon !</h4>
        <p>Tout devrait bien se passer maintenant, vous pouvez utiliser votre nouveau serveur 
            comme n'importe quel autre serveur pour synchroniser vos données Shopify !
        </p>
    </div>
</div>
