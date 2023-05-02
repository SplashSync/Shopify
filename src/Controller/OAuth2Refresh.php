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
use Splash\Connectors\Shopify\Services\ShopifyConnector;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Translation\Translator;

/**
 * Refresh Shopify OAuth2 Access Token
 */
class OAuth2Refresh extends AbstractController
{
    use ActionsTrait;

    //==============================================================================
    // OAUTH2 ACCESS TOKEN REFRESH
    //==============================================================================

    /**
     * Refresh User Access Token
     *
     * @param Request           $request
     * @param AbstractConnector $connector
     *
     * @return Response
     */
    public function __invoke(Request $request, AbstractConnector $connector): Response
    {
        $result = false;
        //====================================================================//
        // Safety Check
        $refreshToken = $request->get("token");
        if (empty($refreshToken) || !is_string($refreshToken)) {
            return new Response("Please provide Refresh Token");
        }
        //====================================================================//
        // Connector SelfTest
        if (($connector instanceof ShopifyConnector) && $connector->selfTest()) {
            $result = $connector->refreshAccessToken($refreshToken);
        }
        //====================================================================//
        // Inform User
        /** @var Translator $translator */
        $translator = $this->get('translator');
        $this->addFlash(
            $result ? "success" : "danger",
            $translator->trans(
                $result ? "Access Token Updated" : "Access Token Update Fail",
                array(),
                "ShopifyBundle"
            )
        );
        //====================================================================//
        // Redirect Response
        /** @var string $referer */
        $referer = $request->headers->get('referer');
        if (empty($referer)) {
            return self::getDefaultResponse();
        }

        return new RedirectResponse($referer);
    }
}
