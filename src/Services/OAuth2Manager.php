<?php

/*
 *  This file is part of SplashSync Project.
 *
 *  Copyright (C) Splash Sync  <www.splashsync.com>
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace Splash\Connectors\Shopify\Services;

use Exception;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use KnpU\OAuth2ClientBundle\Client\OAuth2ClientInterface;
use Splash\Bundle\Models\AbstractConnector;
use Splash\Connectors\Shopify\OAuth2\ShopifyAdapter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;

/**
 * Manage Shopify Connector Oauth2 Dialog.
 *
 * The connector is only responsible for the Oauth2 dialog with Shopify:
 *  - Authorize: redirect the user to the Shopify consent screen.
 *  - Callback:  exchange the returned code for a permanent offline access
 *               token and store it on the connector.
 *
 * Public and Private/Custom Apps use two distinct registered clients
 * (ShopifyAdapter::CLIENT_CODE / CLIENT_CODE_PRIVATE) so the public client
 * credentials are never overwritten by a private connector.
 *
 * Profile / user installation is handled application side: the callback only
 * stores the token on a shop that was already provisioned (identifyByHost).
 */
class OAuth2Manager
{
    public function __construct(
        private readonly ClientRegistry $registry,
        private readonly Environment $twig,
    ) {
    }

    //====================================================================//
    // OAUTH2 AUTHORIZE
    //====================================================================//

    /**
     * Build Redirect Response to the Shopify Authorization Screen.
     *
     * @param AbstractConnector $connector Target Connector
     * @param null|string       $shop      Force Shop Url (store initiated install)
     *
     * @return null|Response
     */
    public function connect(AbstractConnector $connector, ?string $shop = null): ?Response
    {
        //==============================================================================
        // Load & Configure the Shopify Oauth2 Client
        if (!$client = $this->getConfiguredClient($connector, $shop)) {
            return null;
        }
        //==============================================================================
        // Mark Client as Stateless (Shopify uses Hmac, not Oauth2 state).
        // Required in bridge mode (XML-RPC socket) where there is no HTTP session.
        $client->setAsStateless();

        //==============================================================================
        // Redirect User to the Shopify Consent Screen
        return $client->redirect(array(), array());
    }

    //====================================================================//
    // OAUTH2 CALLBACK
    //====================================================================//

    /**
     * Validate the Oauth2 Callback & Save the Access Token on the Connector.
     *
     * @param Request           $request   Inbound Shopify Callback Request
     * @param AbstractConnector $connector Target Connector
     *
     * @return Response
     */
    public function saveToken(Request $request, AbstractConnector $connector): Response
    {
        //==============================================================================
        // Identify Connector by Shop Host => must be provisioned application side
        $shop = $request->get("shop");
        if (!is_string($shop) || !$shop || (true !== $connector->identifyByHost($shop))) {
            return new Response(
                "This Shop is not registered yet. Please install the App from your Splash account.",
                Response::HTTP_NOT_FOUND
            );
        }
        //==============================================================================
        // Load & Configure the Oauth2 Client (selects public/private + credentials)
        if (!$client = $this->getConfiguredClient($connector, $shop)) {
            return new Response("Shopify Oauth2 Client not available", Response::HTTP_BAD_REQUEST);
        }
        //==============================================================================
        // Verify Request Hmac Signature using the now configured client secret
        $adapter = $client->getOAuth2Provider();
        if (!($adapter instanceof ShopifyAdapter) || !$adapter->validateQueryHmac($request)) {
            return new Response("Invalid Request Signature", Response::HTTP_FORBIDDEN);
        }
        //==============================================================================
        // Mark Client as Stateless (Shopify uses Hmac, not Oauth2 state)
        $client->setAsStateless();

        try {
            //==============================================================================
            // Exchange Authorization Code for a Permanent Access Token
            $accessToken = $client->getAccessToken();
            //==============================================================================
            // Store Access Token & Persist Connector Configuration
            $connector->setParameter("Token", $accessToken->getToken());
            $connector->updateConfiguration();
        } catch (Exception $e) {
            return new Response(
                sprintf("Connexion Refused: %s", $e->getMessage()),
                Response::HTTP_UNAUTHORIZED
            );
        }

        //==============================================================================
        // Render the Connection Confirmation Page
        return new Response($this->twig->render("@Shopify/OAuth2/connected.html.twig", array(
            "connector" => $connector,
            "shop" => $shop,
        )));
    }

    //====================================================================//
    // PRIVATE METHODS
    //====================================================================//

    /**
     * Load the right Shopify Oauth2 Client & Configure its Provider.
     *
     * @param AbstractConnector $connector Target Connector
     * @param null|string       $shop      Force Shop Url
     *
     * @return null|OAuth2ClientInterface
     */
    private function getConfiguredClient(AbstractConnector $connector, ?string $shop): ?OAuth2ClientInterface
    {
        //==============================================================================
        // Safety Check - This is a Shopify Connector
        if (!$connector instanceof ShopifyConnector) {
            return null;
        }
        //==============================================================================
        // Load Connector Oauth2 Client (public or private)
        $client = $this->registry->getClient($this->getClientCode($connector));
        //==============================================================================
        // Safety Check - Provider is a Shopify Adapter
        $adapter = $client->getOAuth2Provider();
        if (!($adapter instanceof ShopifyAdapter)) {
            return null;
        }
        //==============================================================================
        // Configure Shopify Oauth2 Provider for this Connector
        $adapter->configure($connector, $shop);

        return $client;
    }

    /**
     * Get the Oauth2 Client Code to use for this Connector.
     *
     * @param ShopifyConnector $connector Target Connector
     *
     * @return string
     */
    private function getClientCode(ShopifyConnector $connector): string
    {
        return $connector->hasPrivateAppCredentials()
            ? ShopifyAdapter::CLIENT_CODE_PRIVATE
            : ShopifyAdapter::CLIENT_CODE
        ;
    }
}
