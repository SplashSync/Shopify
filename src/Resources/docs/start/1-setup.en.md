---
lang: en
permalink: start/setup
title: Splash Account Setup
---

### Account vs Server ? 

Accounts servers are different from standard servers because connection to your shop is managed by Splash.

To do so, we implemented communication with Sphopify API, you don't have any specific plugin to install on Shopify.

### Step 1 - Create a new Account Server Profile

To connect your Shopify Shop with Splash, first click on "Connect" button.

![Add Shopify Account]({{ "/assets/img/add-account.png" | relative_url }})

### Step 2 - Setup your Shop Admin Url

Setup the url of you new Shopify Account. 

Click on server <i class="far fa-edit"></i> Edit button and fill the form with the admin url of you shop. 

Generally, this url looks like: myawesomeshop.myshopify.com. 

**Do not use your public domain here, this is only for admin access to your Shopify Account.**

![Setup Shopify Admin Url]({{ "/assets/img/first-setup.png" | relative_url }})

### Step 3 - Connect to your Shop

Now that you have set the shop Url, click on Connect to Shopify API to start the authentication process.

This process will require your Shopify Account approval. 

### Step 4 - Refresh your server

Now Splash should have a valid Token to access your Shopify Shop API.

You can Refresh you connection so that Splash collect all informations about you shop and it's available objects.

![Server Refresh]({{ "/assets/img/refresh.png" | relative_url }})

### Step 5 - Setup of Webhooks

Shopify Webhooks are used to inform Splash of any changes you does on yoru Shopify Account.

This could an update of a Product decription or stock, but also a new Order received on your Shop.
  
To ensure Splash receive all those notifications, you need to trigger the webkook setup.

This is only done once.

### Step 6 - Select default Warehouse

Shopify now uses multi-location warehouses, so you have to select which warehouse Splash should use for stocks synchronization.

Click on server <i class="far fa-edit"></i> Edit button and fill select the default warehouse to use.

Now all should be ok, you can use your new server as any other servers to syncrhonize your Shopify data!