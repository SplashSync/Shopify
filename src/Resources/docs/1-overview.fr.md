---
lang: fr
permalink: overview
title: Connecteur Shopify
description: Synchronisez clients, produits, stocks et commandes entre Splash et votre boutique Shopify.
updated: 2026-09-28
---

### Présentation

Shopify est une plateforme e-commerce hébergée. Le connecteur Shopify relie votre boutique
Shopify à Splash au travers de l'API Shopify : vos données métier (clients, produits, stocks,
commandes) restent synchronisées avec les autres applications que vous utilisez.

Aucun module n'est à installer côté Shopify : la connexion est gérée par Splash à partir d'une
**application Shopify dédiée**. Vous fournissez seulement la **clé API** et la **clé secrète** de
l'application ; le connecteur récupère ensuite son jeton d'accès **automatiquement, par OAuth** —
plus aucun jeton à copier à la main.

### Objets synchronisés

| Objet | Rôle |
|---|---|
| **Client** | Clients de la boutique et leurs adresses de livraison / facturation |
| **Produit** | Catalogue, variantes, images, références et codes-barres |
| **Stock** | Niveaux de stock par entrepôt (localisation Shopify) |
| **Commande** | Commandes clients, transactions et avancement |
| **Livraison** | Préparation et suivi des expéditions (mode logistique) |

### Fonctionnement en bref

- La connexion s'appuie sur une **application Shopify** (identifiants API) — voir la section
  *Démarrage*. Aucun plug-in à installer sur la boutique.
- Shopify **notifie Splash en temps réel** (webhooks) à chaque changement : produit, stock,
  nouvelle commande, expédition.
- Le **mode logistique** (optionnel) active la gestion des préparations et du suivi colis
  (fulfillment). Voir la section *Configuration*.
- La synchronisation des **stocks** est rattachée à un **entrepôt par défaut** que vous
  choisissez dans la configuration du serveur.

### Pour commencer

1. **Installer le connecteur** : créer l'application Shopify et connecter votre boutique à Splash
   (section *Démarrage*).
2. Régler les **Options du connecteur** selon votre organisation (section *Configuration*).
3. Consulter le fonctionnement détaillé des objets synchronisés (section *Utilisation*).
