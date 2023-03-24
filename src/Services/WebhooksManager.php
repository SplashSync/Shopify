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

use Splash\Connectors\Shopify\Objects\WebHook;
use Symfony\Component\Routing\RouterInterface;

/**
 * Manage Shopify Server Webhooks Configuration
 */
class WebhooksManager
{
    /**
     * @var RouterInterface
     */
    private RouterInterface $router;

    /**
     * @param RouterInterface $router
     */
    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }

    /**
     * Check & Update Shopify Api Account WebHooks.
     *
     * @return bool
     */
    public function verifyWebHooks(ShopifyConnector $connector) : bool
    {
        //====================================================================//
        // Connector SelfTest
        if (!$connector->selfTest()) {
            return false;
        }
        //====================================================================//
        // Get Hostname for WebHooks
        $webHookServer = $this->getHostname();
        //====================================================================//
        // Create Object Class
        $webHookManager = new WebHook($connector);
        $webHookManager->configure("webhook", $connector->getWebserviceId(), $connector->getConfiguration());
        //====================================================================//
        // Get List Of WebHooks for this List
        $webHooks = $webHookManager->objectsList();
        if (isset($webHooks["meta"])) {
            unset($webHooks["meta"]);
        }
        //====================================================================//
        // Walk on WebHooks Topics
        foreach (WebHook::getTopics() as $topic) {
            $found = false;

            //====================================================================//
            // Search in WebHooks List
            foreach ($webHooks as $webHook) {
                //====================================================================//
                // Check WebHook is Valid
                if (WebHook::isValid($webHook, $webHookServer, $topic)) {
                    $found = true;
                }
            }

            //====================================================================//
            // WebHooks is Ok
            if ($found) {
                continue;
            }

            return false;
        }

        //====================================================================//
        // All Splash WebHooks were Found
        return true;
    }

    /**
     * Check & Update Shopify Api Account WebHooks.
     *
     * @param ShopifyConnector $connector
     *
     * @return bool
     */
    public function updateWebHooks(ShopifyConnector $connector) : bool
    {
        //====================================================================//
        // Connector SelfTest
        if (!$connector->selfTest()) {
            return false;
        }
        //====================================================================//
        // Setup Hostname for WebHooks
        $this->router->getContext()
            ->setHost($this->getHostname())
            ->setScheme("https")
        ;
        //====================================================================//
        // Generate WebHook Url
        $webHookUrl = $this->router->generate(
            'splash_connector_action',
            array(
                'connectorName' => $connector->getProfile()["name"],
                'webserviceId' => $connector->getWebserviceId(),
            ),
            RouterInterface::ABSOLUTE_URL
        );
        //====================================================================//
        // Create Object Class
        $webHook = new WebHook($connector);
        $webHook->configure("webhook", $connector->getWebserviceId(), $connector->getConfiguration());
        //====================================================================//
        // Get List Of WebHooks for this List
        $webHooks = $webHook->objectsList();
        if (isset($webHooks["meta"])) {
            unset($webHooks["meta"]);
        }
        //====================================================================//
        // Walk on WebHooks Topics
        foreach (WebHook::getTopics() as $topic) {
            //====================================================================//
            // Update Splash WebHook Configuration
            if (false === $this->updateWebHookConfig($webHook, $webHooks, $webHookUrl, $topic)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Check & Update Shopify Api Account WebHook Configuration.
     *
     * @param WebHook $manager    Shopify WebHook Splash Manager
     * @param array   $webHooks   Shopify WebHooks List
     * @param string  $webhookUrl Splash WebHook Url
     * @param string  $topic      WebHook Shopify Topic
     *
     * @return bool
     */
    private function updateWebHookConfig(
        WebHook $manager,
        array $webHooks,
        string $webhookUrl,
        string $topic
    ) : bool {
        //====================================================================//
        // Filter & Clean List Of WebHooks
        $foundWebHook = false;
        foreach ($webHooks as $webHook) {
            //====================================================================//
            // Check WebHook is Valid
            if (WebHook::isValid($webHook, $webhookUrl, $topic)) {
                $foundWebHook = true;

                continue;
            }
            //====================================================================//
            // This is a Splash WebHooks
            if (str_contains(trim($webHook['address']), "splashsync.com")) {
                //====================================================================//
                // Same Topic but Wrong Address or Unexpected Topic
                if (($webHook['topic'] == $topic) || !in_array($topic, WebHook::getTopics(), true)) {
                    $manager->delete($webHook['id']);
                }
            }
        }
        //====================================================================//
        // Splash WebHooks was Found
        if ($foundWebHook) {
            return true;
        }
        //====================================================================//
        // Add Splash WebHooks
        return (false !== $manager->create($webhookUrl, $topic));
    }

    /**
     * Get HostName for Shopify Webhooks
     *
     * @return string
     */
    private function getHostname(): string
    {
        static $hostAliases = array(
            "localhost" => "eu-99.splashsync.com",
            "toolkit.shopify.local" => "eu-99.splashsync.com",
            "eu-99.splashsync.com" => "app-99.splashsync.com",
            "www.splashsync.com" => "app.splashsync.com",
            "admin.splashsync.com" => "app.splashsync.com"
        );
        //====================================================================//
        // Get Current Server Name
        $hostName = $this->router->getContext()->getHost();
        //====================================================================//
        // Detect Server Aliases
        foreach ($hostAliases as $source => $target) {
            if (str_contains($source, $hostName)) {
                $hostName = $target;
            }
        }

        return $hostName;
    }
}
