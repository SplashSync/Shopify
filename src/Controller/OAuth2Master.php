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
use KnpU\OAuth2ClientBundle\Client\OAuth2ClientInterface;
use Splash\Bundle\Controller\OAuth\Install;
use Splash\Bundle\Models\AbstractConnector;
use Splash\Bundle\Models\Local\ActionsTrait;
use Splash\Connectors\Shopify\OAuth2\ShopifyAdapter;
use Splash\Connectors\Shopify\Services\ShopifyConnector;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;

/**
 * Splash Shopify Connector Master Action
 */
class OAuth2Master extends AbstractController
{
    use ActionsTrait;

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
    public function __invoke(
        Request $request,
        Session $session,
        ClientRegistry $registry,
        AbstractConnector $connector
    ) {
        //==============================================================================
        // Load Shopify OAuth2 Client
        $client = $registry->getClient("shopify");
        $adapter = $client->getOAuth2Provider();
        //==============================================================================
        // Safety Check
        if (!($adapter instanceof ShopifyAdapter)) {
            return self::getDefaultResponse();
        }
        //==============================================================================
        // Verify Request HMAC
        if (!$adapter->validateQueryHmac($request)) {
            throw $this->createAccessDeniedException();
        }
        //==============================================================================
        // CODE Defined => Request Access Token
        if ($request->query->has("code")) {
            //====================================================================//
            // Perform Identify Server by Host
            if (false === $connector->identifyByHost($request->get('shop'))) {
                //==============================================================================
                // NOTHING Defined => Request Install
                return $this->getRegisterToProfile($connector, $client, $session, $request);
            }

            return $this->getAccessToken($connector, $client);
        }
        //==============================================================================
        // SESSION Defined => Request Access Token
        if ($request->query->has("session")) {
            return $this->getConnectToProfile($request);
        }
        //==============================================================================
        // NOTHING Defined => Request Install
        return $this->forward(OAuth2Install::class, array(
            "connector" => $connector,
            "shop" => $request->get('shop')
        ));
    }

    /**
     * Request & Save Connector Access Token
     *
     * @param AbstractConnector     $connector
     * @param OAuth2ClientInterface $client
     *
     * @return Response
     */
    public function getAccessToken(AbstractConnector $connector, OAuth2ClientInterface $client): Response
    {
        $adapter = $client->getOAuth2Provider();
        //==============================================================================
        // Safety Check
        if (!($adapter instanceof ShopifyAdapter)) {
            return self::getDefaultResponse();
        }
        //==============================================================================
        // Configure Shopify OAuth2 Client
        $adapter->configure($connector);
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

        return $this->redirectToRoute("splash_connector_oauth2_profile");
    }

    /**
     * Request & Save Connector Access Token
     *
     * @param AbstractConnector     $connector
     * @param OAuth2ClientInterface $client
     *
     * @return Response
     */
    public function getConnectToProfile(Request $request): Response
    {
        return $this->redirectToRoute("splash_connector_oauth2_connect", $request->query->all());
    }

    /**
     * Request & Save Connector Access Token
     *
     * @param ShopifyConnector      $connector
     * @param OAuth2ClientInterface $client
     *
     * @return Response
     */
    public function getRegisterToProfile(
        AbstractConnector $connector,
        OAuth2ClientInterface $client,
        Session $session,
        Request $request
    ): Response {
        //====================================================================//
        // Perform Identify Server by Host
        if (false !== $connector->identifyByHost($request->get('shop'))) {
            return new Response('This Application is Already Connected.');
        }
        $adapter = $client->getOAuth2Provider();
        //==============================================================================
        // Safety Check
        if (!($adapter instanceof ShopifyAdapter)) {
            return self::getDefaultResponse();
        }
        //==============================================================================
        // Configure Shopify OAuth2 Client
        $adapter->configure($connector, $request->get('shop'));
        //==============================================================================
        // Set Client as StateLess
        $client->setAsStateless();

        try {
            //==============================================================================
            // Get Access Token
            $accessToken = $client->getAccessToken();
            //==============================================================================
            // Setup Connector Configuration
            $connector->configure("shopify", "", array(
                "WsHost" => $request->get('shop'),
                "Token" => $accessToken->getToken(),
            ));
        } catch (Exception $e) {
            return new Response($e->getMessage(), 400);
        }
        //====================================================================//
        // Get Shop Informations
        if (!$connector->selfTest() || !$connector->fetchShopInformations()) {
            return new Response("Too few informations to collect Shop Informations", 400);
        }
        $shop = $connector->getParameter("ShopInformations") ?? array();
        //====================================================================//
        // Setup Session for User Register
        $registerData = array(
            "username" => $shop['shop_owner'] ?? null,
            "email" => $shop['email'] ?? null,
            "phone" => $shop['phone'] ?? null,
            "connector" => $connector->getSplashType(),
            "configuration" => array(
                "WsHost" => $request->get('shop'),
                "Token" => $accessToken->getToken(),
            ),
            "extras" => array(
                "Shop" => $shop['name'] ?? null,
                "Domain" => $shop['domain'] ?? null,
            )
        );
        $session->set(md5(Install::class), $registerData);
        $session->save();

        return $this->redirectToRoute("splash_connector_oauth2_install", $request->query->all());
    }
}
