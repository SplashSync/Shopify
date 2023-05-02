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

namespace Splash\Connectors\Shopify\OAuth2;

use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use KnpU\OAuth2ClientBundle\Client\OAuth2ClientInterface;
use Splash\Bundle\Interfaces\AuthenticatorInterface;
use Splash\Connectors\Shopify\Services\ShopifyConnector;
use Symfony\Component\HttpFoundation\Request;

/**
 * Splash Connector Authenticator
 *
 * Allow User to Log to profile from Remote Application
 */
class ShopifyAuthenticator implements AuthenticatorInterface
{
    /**
     * OAuth2 Clients Registry
     *
     * @var ClientRegistry
     */
    private ClientRegistry $clientRegistry;

    /**
     * Splash Shopify Connector
     *
     * @var ShopifyConnector
     */
    private ShopifyConnector $connector;

    /**
     * @param ClientRegistry   $clientRegistry
     * @param ShopifyConnector $connector
     */
    public function __construct(ClientRegistry $clientRegistry, ShopifyConnector $connector)
    {
        $this->clientRegistry = $clientRegistry;
        $this->connector = $connector;
    }

    /**
     * Check if Request is Supported & Valid
     *
     * @param Request $request
     *
     * @return bool
     */
    public function supports(Request $request): bool
    {
        //==============================================================================
        // Load Shopify OAuth2 Client
        $adapter = $this->getShopifyClient()->getOAuth2Provider();
        //==============================================================================
        // Safety Check
        if (!($adapter instanceof ShopifyAdapter)) {
            return false;
        }
        //==============================================================================
        // Verify Request HMAC
        if (!$adapter->validateQueryHmac($request)) {
            return false;
        }
        /** @var null|string $shop */
        $shop = $request->get('shop');
        //====================================================================//
        // Perform Identify Server by Host
        if (!$shop || !$this->connector->identifyByHost($shop)) {
            return false;
        }
        //==============================================================================
        // Configure Shopify OAuth2 Client
        $adapter->configure($this->connector);
        //==============================================================================
        // Allow Connection only if Server Already Connected
        return $this->connector->connect();
    }

    /**
     * Get Credentials => Connector Configuration
     *
     * @param Request $request
     *
     * @return null|array
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getCredentials(Request $request): ?array
    {
        if (empty($this->connector->getConfiguration())) {
            return null;
        }

        return $this->connector->getConfiguration();
    }

    /**
     * @return OAuth2ClientInterface
     */
    private function getShopifyClient(): OAuth2ClientInterface
    {
        return $this->clientRegistry->getClient('shopify');
    }
}
