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
use Symfony\Component\HttpFoundation\Response;

/**
 * Shopify Oauth2 Connect Action.
 *
 * Secured action used to (re)connect an already provisioned connector: simply
 * redirects the user to the Shopify consent screen.
 */
class OAuth2Connect extends AbstractController
{
    use ActionsTrait;

    /**
     * Initiate the Oauth2 Connection Process.
     *
     * @param AbstractConnector $connector Target Connector
     * @param OAuth2Manager     $manager   Shopify Oauth2 Manager
     *
     * @return Response
     */
    public function __invoke(
        AbstractConnector $connector,
        OAuth2Manager $manager
    ): Response {
        //==============================================================================
        // Redirect User to the Shopify Consent Screen
        return $manager->connect($connector) ?? self::getDefaultResponse();
    }
}
