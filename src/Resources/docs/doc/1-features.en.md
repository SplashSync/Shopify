---
lang: en
permalink: doc/features
title: Features
description: Synchronized objects, real-time sync, logistic mode, metafields and pickup-point detection.
updated: 2026-09-28
---

### Synchronized objects

| Object | Description |
|---|---|
| **Customer** | The Shopify customer: identity, contact details and primary address. |
| **Address** | Secondary addresses (shipping / billing) attached to a customer. |
| **Product** | Managed at the **variant** level: product core, images, tags, stock, attributes and *metafields*. |
| **Order** | Lines, addresses, totals, statuses, shipment and parcel tracking. |
| **Invoice** | Derived from the order (payments, status); synchronized together with it. |

### Real-time synchronization (webhooks)

Shopify **notifies Splash immediately** on every change, through webhooks configured automatically
(*Webhooks configuration* button). The following events trigger a synchronization:

| Shopify event | Effect in Splash |
|---|---|
| `customers/create · update · enable · disable · delete` | Updates the **Customer** (and its **Addresses**) |
| `products/create · update · delete` | Updates the **Product** (each variant) |
| `orders/create · updated · paid · fulfilled · cancelled · delete` | Updates the **Order** and its **Invoice** |

> [!NOTE]
> Each webhook is verified (store domain + HMAC signature) before processing. Shopify GDPR events are
> recognized but trigger no write.

### Logistic mode

When enabled, logistic mode handles shipments (*fulfillment*):

- the **parcel tracking** fields (tracking number and URL) become **writable**;
- **pushing updated orders** to Shopify is allowed;
- creating a shipment uses the **default warehouse** and can **notify the customer** (*Send
  notifications* option);
- the required logistic scopes are requested automatically.

### MetaFields

The **MetaFields** feature maps Shopify custom fields (*metafields*) of **products and their
variants** as Splash fields, synchronized like the others.

### Pickup-point detection

When reading an order, the connector can replace the shipping address with the pickup point chosen by
the customer:

- **Happy Commerce Colissimo** — reads the order's Colissimo *metafield* and fills in the pickup-point
  address.
- **Mondial Relay** — reads the order's Mondial Relay *note attributes* and fills in the pickup-point
  address.

### Scopes

The connector requests the scopes required for synchronization: customers, products, stocks, orders
and fulfillments. **Logistic mode** adds access to locations and fulfillment orders.

> [!TIP]
> Access to the full order history (`read_all_orders`, orders older than 60 days) is only available on
> the public application validated by Shopify — it is not granted to private applications.
