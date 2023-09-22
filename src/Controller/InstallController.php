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
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;

class InstallController extends AbstractController
{
    use ActionsTrait;

    /**
     * Initiate Oauth when Installed from Shop
     *
     * @param Request           $request
     * @param Session           $session
     * @param AbstractConnector $connector
     * @param ClientRegistry    $registry
     *
     * @return Response
     */
    public function initAction(
        Request $request,
        Session $session,
        AbstractConnector $connector,
        ClientRegistry $registry
    ): Response {
        //==============================================================================
        // Get Shop Url from Request
        /** @var null|string $shopUrl */
        $shopUrl = $request->get("shop");
        //==============================================================================
        // Safety Check
        if (!$shopUrl) {
            return self::getDefaultResponse();
        }
        //==============================================================================
        // Load Shopify OAuth2 Client
        $client = $registry->getClient("shopify");
        //==============================================================================
        // Safety Check
        if (!($client->getOAuth2Provider() instanceof ShopifyAdapter)) {
            return self::getDefaultResponse();
        }
        //==============================================================================
        // Store Empty WebService Id in Session
        $session->set("shopify_oauth2_wsid", "new");
        //==============================================================================
        // Configure Shopify OAuth2 Client
        $client->getOAuth2Provider()->configure($connector, $shopUrl);

        //==============================================================================
        // Do Shopify OAuth2 Authentification
        return $client->redirect(array(), array());
    }
}
