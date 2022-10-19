---
lang: en
permalink: start/advanced-setup
title: Advanced Setup
---

### Why using a Private? 

Only uses Private apps if you face difficulties with the standard connexion process.
If you still have issues with Shopify, please contact our support team.

### Step 1 - Create a Shopify Private App

To connect your Shopify store, then go to **Setting >> Apps and sales channels >> App development**.

Now create a new private application:

![Add Shopify Private App]({{ "/assets/img/private-app-create.png" | relative_url }})

### Step 2 - Admin API Integration

On configuration tab, now configures required scopes.

You must select at least the following list:
- write_customers, read_customers
- write_fulfillments, read_fulfillments
- write_inventory, read_inventory
- write_orders, read_orders
- write_products, read_products
- read_locations

Your configuration should look like this.

![Admin API Configuration]({{ "/assets/img/private-app-config.png" | relative_url }})

### Step 3 - API credentials

One by One, cut and paste your API credentials to your Splash Server configuration

![API credentials]({{ "/assets/img/private-app-credentials.png" | relative_url }})

<div class="callout-block callout-warning">
    <div class="icon-holder">
        <i class="fas fa-exclamation-circle"></i>
    </div>
    <div class="content">
        <h4 class="callout-title">Warning</h4>
        <p>Admin API access token is visible only once!</p>
    </div>
</div>

### Step 4 - Refresh your server

Now Splash should have a valid Token to access your Shopify Shop API.

You can Refresh you connection so that Splash collect all informations about you shop and it's available objects.

![Server Refresh]({{ "/assets/img/refresh.png" | relative_url }})
