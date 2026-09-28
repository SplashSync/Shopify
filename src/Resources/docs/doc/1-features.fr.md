---
lang: fr
permalink: doc/features
title: Fonctionnalités
description: Objets synchronisés, synchronisation temps réel, mode logistique, metafields et détection des points relais.
updated: 2026-09-28
---

### Objets synchronisés

| Objet | Description |
|---|---|
| **Client** | Le client Shopify (tiers) : identité, coordonnées et adresse principale. |
| **Adresse** | Les adresses secondaires (livraison / facturation) rattachées à un client. |
| **Produit** | Géré à la **maille variante** : cœur produit, images, tags, stock, attributs et *metafields*. |
| **Commande** | Lignes, adresses, totaux, statuts, livraison et suivi colis. |
| **Facture** | Dérivée de la commande (paiements, statut) ; synchronisée avec elle. |

### Synchronisation en temps réel (webhooks)

Shopify **notifie Splash immédiatement** à chaque changement, via des webhooks configurés
automatiquement (bouton *Configuration des webhooks*). Les évènements suivis déclenchent une
synchronisation :

| Évènement Shopify | Effet dans Splash |
|---|---|
| `customers/create · update · enable · disable · delete` | Met à jour le **Client** (et ses **Adresses**) |
| `products/create · update · delete` | Met à jour le **Produit** (chaque variante) |
| `orders/create · updated · paid · fulfilled · cancelled · delete` | Met à jour la **Commande** et sa **Facture** |

> [!NOTE]
> Chaque webhook est vérifié (domaine de la boutique + signature HMAC) avant traitement. Les
> évènements RGPD Shopify sont reconnus mais ne déclenchent aucune écriture.

### Mode logistique

Lorsqu'il est activé, le mode logistique gère les expéditions (*fulfillment*) :

- les champs de **suivi colis** (numéro et URL de tracking) deviennent **modifiables** ;
- le **push des commandes mises à jour** vers Shopify est autorisé ;
- la création d'une expédition utilise l'**entrepôt par défaut** et peut **notifier le client**
  (option *Envoyer les notifications*) ;
- les accès logistiques nécessaires sont automatiquement demandés.

### MetaFields

La fonctionnalité **MetaFields** mappe les champs personnalisés Shopify (*metafields*) des
**produits et de leurs variantes** comme des champs Splash, synchronisés comme les autres.

### Détection des points relais

À la lecture d'une commande, le connecteur peut remplacer l'adresse de livraison par le point relais
choisi par le client :

- **Happy Commerce Colissimo** — lit le *metafield* Colissimo de la commande et renseigne l'adresse
  du point relais.
- **Mondial Relay** — lit les *note attributes* Mondial Relay de la commande et renseigne l'adresse
  du point relais.

### Accès (scopes)

Le connecteur demande les accès nécessaires à la synchronisation : clients, produits, stocks,
commandes et expéditions. Le **mode logistique** ajoute les accès aux emplacements et aux ordres de
préparation.

> [!TIP]
> L'accès à l'historique complet des commandes (`read_all_orders`, commandes de plus de 60 jours)
> n'est disponible que sur l'application publique validée par Shopify — il n'est pas accordé aux
> applications privées.
