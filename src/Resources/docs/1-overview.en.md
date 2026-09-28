---
lang: en
permalink: overview
title: Shopify Connector
description: Sync customers, products, stocks and orders between Splash and your Shopify store.
updated: 2026-09-28
---

### Overview

Shopify is a hosted e-commerce platform. The Shopify connector links your Shopify store to Splash
through the Shopify API: your business data (customers, products, stocks, orders) stays in sync with
the other applications you use.

No module has to be installed on Shopify: the connection is managed by Splash through a **dedicated
Shopify application**. You only provide the application's **API key** and **API secret**; the
connector then retrieves its access token **automatically, via OAuth** — no token to copy by hand.

### Synchronized objects

| Object | Role |
|---|---|
| **Customer** | Store customers and their shipping / billing addresses |
| **Product** | Catalog, variants, images, references and barcodes |
| **Stock** | Stock levels per warehouse (Shopify location) |
| **Order** | Customer orders, transactions and progress |
| **Fulfillment** | Shipment preparation and tracking (logistic mode) |

### How it works

- The connection relies on a **Shopify application** (API credentials) — see the *Getting started*
  section. No plug-in to install on the store.
- Shopify **notifies Splash in real time** (webhooks) on every change: product, stock, new order,
  shipment.
- The optional **logistic mode** enables fulfillment handling and parcel tracking. See the
  *Configuration* section.
- **Stock** synchronization is tied to a **default warehouse** that you pick in the server
  configuration.

### Getting started

1. **Install the connector**: create the Shopify application and connect your store to Splash
   (*Getting started* section).
2. Set the **connector options** to match your organization (*Configuration* section).
3. Read the detailed behavior of the synchronized objects (*Usage* section).
