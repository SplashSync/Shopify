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

namespace Splash\Connectors\Shopify\Controller;

use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use Splash\Bundle\Models\AbstractConnector;
use Splash\Bundle\Models\Local\ActionsTrait;
use Splash\Connectors\Shopify\OAuth2\ShopifyAdapter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

/**
 * Shopify OAUth2 - Install Connector from Shopify App Store
 */
class OAuth2Install extends AbstractController
{
    use ActionsTrait;

    //==============================================================================
    // OAUTH2 INSTALL
    //==============================================================================

    /**
     * Initiate Oauth2 Connection Process
     *
     * @param string            $shop
     * @param AbstractConnector $connector
     * @param ClientRegistry    $registry
     *
     * @return Response
     */
    public function __invoke(
        string $shop,
        AbstractConnector $connector,
        ClientRegistry $registry
    ): Response {
        //==============================================================================
        // Load Shopify OAuth2 Client
        $client = $registry->getClient("shopify");
        //==============================================================================
        // Safety Check
        if (!($client->getOAuth2Provider() instanceof ShopifyAdapter)) {
            return self::getDefaultResponse();
        }
        //==============================================================================
        // Configure Shopify OAuth2 Client
        $client->getOAuth2Provider()->configure($connector, $shop);

        //==============================================================================
        // Do Shopify OAuth2 Authentification
        return $client->redirect(array(), array());
        //==============================================================================
        // Override Redirect url
        //        return $client->redirect(array(), array(
        //            'redirect_uri' => "https://xxx.ngrok-free.app/en/ws/shopify"
        //        ));
    }
}
