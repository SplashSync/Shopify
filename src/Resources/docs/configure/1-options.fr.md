---
lang: fr
permalink: configure/options
title: Options du connecteur
description: Régler l'entrepôt par défaut, le mode logistique, les notifications et les options avancées du connecteur Shopify.
updated: 2026-09-28
---

Les options se règlent dans **Mes comptes → API Shopify**, en modifiant la configuration du serveur.

### Options principales

| Option | Rôle | Détail |
|---|---|---|
| **URL de la boutique** | Domaine d'administration de la boutique | Doit être de la forme `votre-boutique.myshopify.com` (pas le domaine public). |
| **Stock par défaut** | Emplacement Shopify utilisé pour la synchronisation des stocks | À choisir parmi les emplacements de la boutique (chargés automatiquement à la connexion). |
| **Application privée** | Utiliser votre propre application Shopify | Active les champs *Clé API privée* et *Clé secrète API privée*. |
| **Clé API privée** | *Client ID* de votre application Shopify | Requis quand *Application privée* est activé. |
| **Clé secrète API privée** | *Client secret* de votre application Shopify | Requis quand *Application privée* est activé. |
| **Envoyer les notifications** | Prévenir le client Shopify au changement de statut d'expédition | Disponible uniquement en **mode logistique**. |

> [!NOTE]
> Le **jeton d'accès** n'est plus saisi à la main : il est récupéré et maintenu **automatiquement par
> OAuth** lors de la connexion (voir *Installer le connecteur*).

### Options avancées

Ces options ne figurent pas dans le formulaire standard : **contactez votre administrateur Splash**
pour les activer.

| Option | Rôle |
|---|---|
| **Mode logistique** | Gère les préparations, expéditions et le suivi colis (*fulfillment*) : rend le suivi modifiable, autorise le push des commandes mises à jour, ajoute les accès logistiques. |
| **MetaFields** | Synchronise les champs personnalisés Shopify (*metafields*) des produits et de leurs variantes. |
| **Détection Happy Commerce Colissimo** | Remplace l'adresse de livraison de la commande par le **point relais Colissimo** détecté. |
| **Détection Mondial Relay** | Remplace l'adresse de livraison de la commande par le **point relais Mondial Relay** détecté. |

> [!TIP]
> Vous pouvez ajuster les accès (scopes) à tout moment côté Shopify. Le connecteur signale les accès
> manquants et vous invite à les mettre à jour.
