---
lang: en
permalink: configure/options
title: Connector options
description: Set the default warehouse, logistic mode, notifications and advanced options of the Shopify connector.
updated: 2026-09-28
---

Options are set in **My accounts → Shopify API**, by editing the server configuration.

### Main options

| Option | Role | Detail |
|---|---|---|
| **Shop URL** | Store admin domain | Must be of the form `your-store.myshopify.com` (not the public domain). |
| **Default Stock** | Shopify location used for stock synchronization | Chosen among the store locations (loaded automatically on connection). |
| **Private application** | Use your own Shopify application | Enables the *Private API key* and *Private API secret* fields. |
| **Private API key** | Your Shopify application *Client ID* | Required when *Private application* is enabled. |
| **Private API secret** | Your Shopify application *Client secret* | Required when *Private application* is enabled. |
| **Send notifications** | Notify the Shopify customer on shipping status change | Available only in **logistic mode**. |

> [!NOTE]
> The **access token** is no longer entered by hand: it is retrieved and maintained **automatically
> via OAuth** on connection (see *Install the connector*).

### Advanced options

These options are not shown in the standard form: **contact your Splash administrator** to enable
them.

| Option | Role |
|---|---|
| **Logistic mode** | Handles preparation, shipments and parcel tracking (*fulfillment*): makes tracking writable, allows pushing updated orders, adds the logistic scopes. |
| **MetaFields** | Synchronizes Shopify custom fields (*metafields*) of products and their variants. |
| **Happy Commerce Colissimo detection** | Replaces the order shipping address with the detected **Colissimo pickup point**. |
| **Mondial Relay detection** | Replaces the order shipping address with the detected **Mondial Relay pickup point**. |

> [!TIP]
> You can adjust the scopes at any time on the Shopify side. The connector reports missing scopes and
> invites you to update them.
