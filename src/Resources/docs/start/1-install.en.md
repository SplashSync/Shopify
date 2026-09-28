---
lang: en
permalink: start/install
title: Install the Shopify connector
description: Create the Shopify application, get the Client ID / Client secret, and let the connector retrieve its token via OAuth.
updated: 2026-09-28
---

> [!IMPORTANT]
> **Since January 2026**, Shopify no longer lets you create an application from the store admin. Apps
> are now created in the **Dev Dashboard / Partner Dashboard**, and **the access token is no longer
> copied by hand**: the connector retrieves it automatically through **OAuth**. You only provide your
> application's **API key** and **API secret**.

### Requirements

- A Shopify store with **admin** access.
- An active **Splash Sync** account.

> [!NOTE]
> If you use Splash as **SaaS** (Splash cloud, public application), you have nothing to create: go
> straight to the **Connect the store** step and click *Connect*. The app-creation steps below are for
> using **your own Shopify application** (self-hosted installation).

### Step 1 — Create the app (Dev Dashboard)

From the Shopify admin: **Settings → Apps and sales channels → Develop apps**, then **Build apps
using the Dev Dashboard** (you can also go directly to [partners.shopify.com](https://partners.shopify.com)
→ **Apps**).

Click **Create app**, choose **Create manually**, name it (for example `Splash Sync`) and confirm.

### Step 2 — Configure the OAuth redirect and scopes

In the app configuration:

- **Allowed redirection URL(s)** — add the Splash *callback* URL:
  `https://app.splashsync.com/ws/shopify`. Without it, OAuth fails.
- **Admin API scopes** — select at least:

| Scope | Object |
|---|---|
| `read_customers`, `write_customers` | Customers |
| `read_products`, `write_products` | Products |
| `read_inventory`, `write_inventory` | Stocks |
| `read_orders`, `write_orders` | Orders |
| `read_fulfillments`, `write_fulfillments` | Fulfillments |
| `read_locations` | Warehouses |

Save / release the version.

### Step 3 — Get the credentials

In the app settings, copy the two following values:

| Shopify | Splash field |
|---|---|
| **Client ID** (API key) | Private API key |
| **Client secret** (API secret) | Private API secret |

> [!CAUTION]
> The **Client secret** is sensitive: never share it or expose it on the client side.

### Step 4 — Distribution

Under **Distribution**, choose **Custom distribution** and enter your store domain
(`your-store.myshopify.com`).

### Step 5 — Connect the store in Splash

On the Splash side, open **My accounts → Shopify API**, edit the server configuration and fill in:

| Splash field | Value |
|---|---|
| Shop URL | `your-store.myshopify.com` |
| Private application | enabled |
| Private API key | Application *Client ID* |
| Private API secret | Application *Client secret* |

Save, then click **Connect**.

> [!TIP]
> You are redirected to Shopify to approve the application, then back to Splash. **The access token is
> retrieved automatically through OAuth** — there is no token to copy by hand anymore.

### Step 6 — Webhooks and warehouse

1. Run the **webhooks configuration**: Shopify will then notify Splash in real time.
2. Select the **default warehouse** used for stock synchronization.

> [!TIP]
> Once these steps are done, the server profile should only show green flags. You can then use this
> server like any other to synchronize your data.
