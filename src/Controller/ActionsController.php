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

use Exception;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use Splash\Bundle\Models\AbstractConnector;
use Splash\Bundle\Models\Local\ActionsTrait;
use Splash\Connectors\Shopify\Models\OAuth2Client;
use Splash\Connectors\Shopify\Services\ShopifyConnector;
use Splash\Connectors\Shopify\Services\WebhooksManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Translation\Translator;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Splash Shopify Connector Actions Controller
 */
class ActionsController extends AbstractController
{
    use ActionsTrait;

    //==============================================================================
    // OAUTH2 AUTHENTIFICATION
    //==============================================================================

    /**
     * Perform Oauth for this Server from Splash Account Page
     *
     * @param Session           $session
     * @param ClientRegistry    $registry
     * @param AbstractConnector $connector
     *
     * @return Response
     */
    public function oauthAction(Session $session, ClientRegistry $registry, AbstractConnector $connector): Response
    {
        //==============================================================================
        // Load Shopify OAuth2 Client
        $client = $registry->getClient("shopify");
        //==============================================================================
        // Safety Check
        if (!($client->getOAuth2Provider() instanceof OAuth2Client)) {
            return self::getDefaultResponse();
        }
        //==============================================================================
        // Configure Shopify OAuth2 Client
        $client->getOAuth2Provider()->configure($connector);
        //==============================================================================
        // Store Connector WebService Id in Session
        $session->set("shopify_oauth2_wsid", $connector->getWebserviceId());
        //==============================================================================
        // Do Shopify OAuth2 Authentification
        return $client->redirect(array(), array());
    }

    /**
     * Register Connector App Token
     *
     * @param Request           $request
     * @param Session           $session
     * @param ClientRegistry    $registry
     * @param AbstractConnector $connector
     *
     * @return Response
     */
    public function registerAction(
        Request $request,
        Session $session,
        ClientRegistry $registry,
        AbstractConnector $connector
    ) {
        //==============================================================================
        // Get Connector WebService Id from Session
        /** @var null|string $webserviceId */
        $webserviceId = $session->get("shopify_oauth2_wsid");
        //====================================================================//
        // NO WebserviceId => Install from Shopify Listing
        if (!$webserviceId || ("new" == $webserviceId)) {
            return $this->forwardToConnector("ShopifyBundle:Install:init", $connector);
        }
        //====================================================================//
        // Perform Identify Pointed Server
        if (false === $connector->identify($webserviceId)) {
            return self::getDefaultResponse();
        }
        //==============================================================================
        // Load Shopify OAuth2 Client
        $client = $registry->getClient("shopify");
        //==============================================================================
        // Safety Check
        if (!($client->getOAuth2Provider() instanceof OAuth2Client)) {
            return self::getDefaultResponse();
        }
        //==============================================================================
        // Configure Shopify OAuth2 Client
        $client->getOAuth2Provider()->configure($connector);
        //==============================================================================
        // Set Client as StateLess
        $client->setAsStateless();

        try {
            //==============================================================================
            // Get Access Token
            $accessToken = $client->getAccessToken();
            //==============================================================================
            // Now update Connector Configuration
            $connector->setParameter("Token", $accessToken->getToken());
            $connector->updateConfiguration();
        } catch (Exception $e) {
            return new Response($e->getMessage(), 400);
        }

        //====================================================================//
        // Redirect Response
        /** @var string $referer */
        $referer = $request->headers->get('referer');
        if (empty($referer)) {
            return self::getDefaultResponse();
        }

        return new RedirectResponse($referer);
    }

    //==============================================================================
    // WEBHOOKS CONFIGURATION
    //==============================================================================

    /**
     * Update User Connector WebHooks List
     *
     * @param Request             $request
     * @param TranslatorInterface $translator
     * @param WebhooksManager     $whManager
     * @param AbstractConnector   $connector
     *
     * @return Response
     */
    public function webhooksAction(
        Request $request,
        TranslatorInterface $translator,
        WebhooksManager $whManager,
        AbstractConnector $connector
    ): Response {
        $result = false;
        //====================================================================//
        // Connector SelfTest
        if (($connector instanceof ShopifyConnector) && $connector->selfTest()) {
            //====================================================================//
            // Update WebHooks Config
            $result = $whManager->updateWebHooks($connector);
        }
        //====================================================================//
        // Inform User
        $this->addFlash(
            $result ? "success" : "danger",
            $translator->trans(
                $result ? "admin.webhooks.msg" : "admin.webhooks.err",
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

    //==============================================================================
    // TOKEN REFRESH
    //==============================================================================

    /**
     * Refresh User Access Token
     *
     * @param Request           $request
     * @param AbstractConnector $connector
     *
     * @return Response
     */
    public function refreshAction(Request $request, AbstractConnector $connector): Response
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
