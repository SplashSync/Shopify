---
lang: fr
permalink: start/install
title: Installer le connecteur Shopify
description: Créer l'application Shopify, récupérer Client ID / Client secret, et laisser le connecteur récupérer son jeton par OAuth.
updated: 2026-09-28
---

> [!IMPORTANT]
> **Depuis janvier 2026**, Shopify ne permet plus de créer une application depuis l'administration
> de la boutique. Les applications se créent désormais dans le **Dev Dashboard / Partner Dashboard**,
> et **le jeton d'accès ne se copie plus à la main** : le connecteur le récupère automatiquement par
> **OAuth**. Vous n'avez qu'à fournir la **clé API** et la **clé secrète** de votre application.

### Prérequis

- Une boutique Shopify avec un accès **administrateur**.
- Un compte **Splash Sync** actif.

> [!NOTE]
> Si vous utilisez Splash en **SaaS** (cloud Splash, application publique), vous n'avez rien à créer :
> allez directement à l'étape **Connecter la boutique** et cliquez sur *Se connecter*. Les étapes de
> création d'application ci-dessous concernent l'usage de **votre propre application Shopify**
> (installation auto-hébergée).

### Étape 1 — Créer l'application (Dev Dashboard)

Depuis l'admin Shopify : **Réglages → Applications et canaux de vente → Développer des applications**,
puis **Créer des applications avec le Dev Dashboard** (vous pouvez aussi passer directement par
[partners.shopify.com](https://partners.shopify.com) → **Apps**).

Cliquez sur **Créer une application**, choisissez **Créer manuellement**, nommez-la (par exemple
`Splash Sync`) puis validez.

### Étape 2 — Configurer la redirection OAuth et les accès

Dans la configuration de l'application :

- **URL de redirection autorisée** — ajoutez l'URL de *callback* de Splash :
  `https://app.splashsync.com/ws/shopify`. Sans cette URL, l'OAuth échoue.
- **Accès de l'API Admin (scopes)** — cochez au minimum :

| Accès (scopes) | Objet |
|---|---|
| `read_customers`, `write_customers` | Clients |
| `read_products`, `write_products` | Produits |
| `read_inventory`, `write_inventory` | Stocks |
| `read_orders`, `write_orders` | Commandes |
| `read_fulfillments`, `write_fulfillments` | Livraisons |
| `read_locations` | Entrepôts |

Enregistrez / publiez la version.

### Étape 3 — Récupérer les identifiants

Dans les réglages de l'application, copiez les deux valeurs suivantes :

| Shopify | Champ Splash |
|---|---|
| **Client ID** (clé API) | Clé API privée |
| **Client secret** (clé secrète API) | Clé secrète API privée |

> [!CAUTION]
> Le **Client secret** est une donnée sensible : ne la partagez pas et ne l'exposez jamais côté
> client.

### Étape 4 — Distribution

Dans la section **Distribution**, choisissez **Distribution personnalisée** et renseignez le domaine
de votre boutique (`votre-boutique.myshopify.com`).

### Étape 5 — Connecter la boutique dans Splash

Côté Splash, ouvrez **Mes comptes → API Shopify**, modifiez la configuration du serveur et
renseignez :

| Champ Splash | Valeur |
|---|---|
| URL de la boutique | `votre-boutique.myshopify.com` |
| Application privée | activé |
| Clé API privée | *Client ID* de l'application |
| Clé secrète API privée | *Client secret* de l'application |

Enregistrez, puis cliquez sur **Se connecter**.

> [!TIP]
> Vous êtes redirigé vers Shopify pour approuver l'application, puis ramené à Splash. **Le jeton
> d'accès est récupéré automatiquement par OAuth** — il n'y a plus aucun jeton à copier à la main.

### Étape 6 — Webhooks et entrepôt

1. Lancez la **configuration des webhooks** : Shopify pourra notifier Splash en temps réel.
2. Sélectionnez l'**entrepôt par défaut** utilisé pour la synchronisation des stocks.

> [!TIP]
> Une fois ces étapes faites, le profil de serveur ne doit afficher que des indicateurs verts. Vous
> pouvez alors utiliser ce serveur comme n'importe quel autre pour synchroniser vos données.
