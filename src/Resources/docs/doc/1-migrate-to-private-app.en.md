---
lang: en
permalink: docs/migrate-to-private-app
title: Private App Migration
---

### Why using a Private Application? 

Since June 15, 2023, using Shopify private app with Splash Sync become mandatory.

This change will improve reliability and efficiency of our interfaces with Shopify, also increase security.   

If you still have issues with Shopify Private Apps, please contact our support team.

### What will we do in this tutorial?

We will guide you for migration from Splash <> Shopify Public App connexion to Splash <> Shopify Private App.

This process is similar to our new installation process, but first, we will ask you to uninstall any existing public app.

### Step 1 - Remove Public App

To connect your Shopify store, then go to **Setting >> Apps and sales channels**.

Now uninstall our Public Application:

![Add Shopify Private App]({{ "/assets/img/uninstall-public-app.png" | relative_url }})

### Step 2 - Create a Shopify Private App

First step of installation process is creation of a dedicated Shopify Application Splash Sync will use to communication with your store.

To connect your Shopify store, then go to **Setting >> Apps and sales channels >> App development**.

Now create a new private application:

![Add Shopify Private App]({{ "/assets/img/private-app-create.png" | relative_url }})

### Step 3 - Admin API Integration

Now you have created an App, you have to configure application rights so that Splash could access required information.

Go to **Configuration >> AdminAPI Integration >> Edit** and configures required scopes.

You must select at least the following list:
- write_customers, read_customers
- write_fulfillments, read_fulfillments
- write_inventory, read_inventory
- write_orders, read_orders
- write_products, read_products
- read_locations

Your configuration should look like this.

![Admin API Configuration]({{ "/assets/img/private-app-config.png" | relative_url }})

<div class="callout-block callout-info py-3">
    <div class="icon-holder" style="top: 0px;">
        <i class="fas fa-exclamation-circle text-white"></i>
    </div>
    <div class="content">
        <p>Splash now verify scopes, you can adjust this configuration at anytime.</p>
    </div>
</div>

### Step 4 - Update your Server Configuration

Now go back to your Splash Sync account.

Go to **My Accounts >> My Shopify Store** and click on "Edit" button.

### Step 5 - Setup API credentials

This is the most critical step! You now have to cut and paste all your API credentials to your Splash Sync Server configuration.

Go to **API Credentials**  Tab and copy all credentials from Shopify to Splash.
![API credentials]({{ "/assets/img/private-app-credentials.png" | relative_url }})

<div class="callout-block callout-warning">
    <div class="icon-holder">
        <i class="fas fa-exclamation-circle"></i>
    </div>
    <div class="content">
        <h4 class="callout-title">Warning</h4>
        <p>Admin API access token is visible only once! So this one should be copied with care!</p>
    </div>
</div>

On Splash side, click on server <i class="far fa-edit"></i> Edit button and fill the form with the admin url of you shop.

First, enter your shop admin url, this url looks like: myawesomeshop.myshopify.com.
**Do not use your public domain here, this is only for admin access to your Shopify Account.**

Then, cut and paste all credentials. Your configuration look like this:

![API credentials]({{ "/assets/img/private-app-splash-setup.png" | relative_url }})

### Step 6 - Refresh your server

Now Splash should have a valid configuration to access your Shopify Shop API.

You can Refresh your connection so that Splash collect all information about you shop and available objects.

![Server Refresh]({{ "/assets/img/refresh.png" | relative_url }})

### Step 7 - Setup of Webhooks

Shopify Webhooks are used to inform Splash of any changes you does on your Shopify Account.

This could be an update of a Product description or stock, but also a new Order received on your Shop.

To ensure Splash receive all those notifications, you need to trigger the webhook setup.

Once done, your server profile should only show green flags!

![API credentials]({{ "/assets/img/configuration-ok.png" | relative_url }})

### Step 8 - Select default Warehouse

Shopify now uses multi-location warehouses, so you have to select which warehouse Splash should use for stocks synchronization.

Click on server <i class="far fa-edit"></i> Edit button and fill select the default warehouse to use.

<div class="callout-block callout-success">
    <div class="icon-holder">
        <i class="fas fa-check-double"></i>
    </div>
    <div class="content">
        <h4 class="callout-title">All done!</h4>
        <p>You can use your new server as any other servers to synchronize your Shopify data!</p>
    </div>
</div>
