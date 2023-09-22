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
 * Splash Shopify Connector Actions Controller
 */
class OAuth2Connect extends AbstractController
{
    use ActionsTrait;

    //==============================================================================
    // OAUTH2 AUTHENTIFICATION
    //==============================================================================

    /**
     * Initiate Oauth2 Connection Process
     *
     * @param ClientRegistry    $registry
     * @param AbstractConnector $connector
     *
     * @return Response
     */
    public function __invoke(
        ClientRegistry $registry,
        AbstractConnector $connector
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
        $client->getOAuth2Provider()->configure($connector);

        //==============================================================================
        // Do Shopify OAuth2 Authentification
        return $client->redirect(array(), array());
        //==============================================================================
        // Do Shopify OAuth2 Authentification
        //        return $client->redirect(array(), array(
        //            'redirect_uri' => "https://xxx.ngrok-free.app/ws/shopify"
        //        ));
    }
}
