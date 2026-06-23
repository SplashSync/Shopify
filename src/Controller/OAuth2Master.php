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

use Splash\Bundle\Models\AbstractConnector;
use Splash\Bundle\Models\Local\ActionsTrait;
use Splash\Connectors\Shopify\Services\OAuth2Manager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Splash Shopify Connector Master Action.
 *
 * Single entry point reached by Shopify on /ws/shopify. Dispatches between the
 * two Oauth2 responsibilities of the connector:
 *  - Callback  (a "code" is present)  => exchange & save the access token.
 *  - Authorize (no "code")            => redirect to the Shopify consent screen.
 */
class OAuth2Master extends AbstractController
{
    use ActionsTrait;

    /**
     * Handle the Shopify Oauth2 Master Action.
     *
     * @param Request           $request   Inbound Request
     * @param AbstractConnector $connector Target Connector
     * @param OAuth2Manager     $manager   Shopify Oauth2 Manager
     *
     * @return Response
     */
    public function __invoke(
        Request $request,
        AbstractConnector $connector,
        OAuth2Manager $manager
    ): Response {
        //==============================================================================
        // CALLBACK => Code Returned by Shopify => Save Access Token
        if ($request->query->has("code")) {
            return $manager->saveToken($request, $connector);
        }
        //==============================================================================
        // AUTHORIZE => Redirect User to the Shopify Consent Screen
        $shop = $request->get("shop");

        return $manager->connect($connector, is_string($shop) ? $shop : null)
            ?? self::getDefaultResponse();
    }
}
