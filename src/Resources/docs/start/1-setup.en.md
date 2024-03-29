---
lang: en
permalink: start/setup
title: Splash Account Setup
---

### Account vs Server ? 

Accounts servers are different from standard servers because connection to your shop is managed by Splash.

To do so, we implemented communication with Shopify API, you don't have any specific plugin to install on Shopify.

### Step 1 - Create a Shopify Private App

First step of installation process is creation of a dedicated Shopify Application Splash Sync will use to communication with your store.

To connect your Shopify store, then go to **Setting >> Apps and sales channels >> App development**.

Now create a new private application:

![Add Shopify Private App]({{ "/assets/img/private-app-create.png" | relative_url }})

### Step 2 - Admin API Integration

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

### Step 3 - Create a new Account Server Profile

Now go back to your Splash Sync account.

Go to **My Accounts >> Shopify API** and click on "Connect" button.

![Add Shopify Account]({{ "/assets/img/add-account.png" | relative_url }})

### Step 4 - Setup API credentials

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

On Splash side, click on server <i class="far fa-edit"></i> Edit button and fill the form.

First, enter your shop admin url, this url looks like: myawesomeshop.myshopify.com.
**Do not use your public domain here, this is only for admin access to your Shopify Account.**

Then, cut and paste all credentials. Your configuration look like this:

![API credentials]({{ "/assets/img/private-app-splash-setup.png" | relative_url }})

### Step 5 - Refresh your server

Now Splash should have a valid configuration to access your Shopify Shop API.

You can Refresh your connection so that Splash collect all information about you shop and available objects.

![Server Refresh]({{ "/assets/img/refresh.png" | relative_url }})

### Step 6 - Setup of Webhooks

Shopify Webhooks are used to inform Splash of any changes you does on your Shopify Account.

This could be an update of a Product description or stock, but also a new Order received on your Shop.
  
To ensure Splash receive all those notifications, you need to trigger the webhook setup.

Once done, your server profile should only show green flags!

![API credentials]({{ "/assets/img/configuration-ok.png" | relative_url }})

### Step 7 - Select default Warehouse

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
