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

namespace Splash\Connectors\Shopify\Models;

trait ConnectorConfigurationsTrait
{
    /**
     * Get Shop Default Vat Rate
     *
     * @return float|int
     */
    public function getDefaultVatRate(): float|int
    {
        //====================================================================//
        // Get Shop Informations
        $storeInfos = $this->getParameter("ShopInformations");
        $countries = $this->getParameter("Countries");
        //====================================================================//
        // Safety Checks
        if (!is_array($storeInfos) || empty($storeInfos["country"]) || !is_array($countries)) {
            return 0;
        }
        //====================================================================//
        // Search for Shop Country Tax
        foreach ($countries as $country) {
            if ($country['code'] == $storeInfos["country"]) {
                return 100 * $country['tax'];
            }
        }

        //====================================================================//
        // Tax was not Found
        return 0;
    }

    /**
     * Get Shop Default Currency
     *
     * @return string
     */
    public function getDefaultCurrency() : string
    {
        //====================================================================//
        // Get Shop Informations
        /** @var null|string $currency */
        $currency = $this->getParameter("currency", "EUR", "ShopInformations");

        return (string) $currency;
    }

    /**
     * Get Shopify Host Domain.
     *
     * @return string
     */
    public function getShopifyDomain(): string
    {
        /** @var string $wsHost */
        $wsHost = $this->getParameter("WsHost");
        //====================================================================//
        // If Url Domain is found
        if (parse_url((string) $wsHost, PHP_URL_HOST)) {
            return (string) parse_url((string) $wsHost, PHP_URL_HOST);
        }

        //====================================================================//
        // Raw Domain was found
        return (string) $wsHost;
    }

    /**
     * Check if Shopify use Private App API Keys.
     *
     * @return bool
     */
    public function hasPrivateAppCredentials(): bool
    {
        return !empty($this->getParameter("apiKey", false))
            && !empty($this->getParameter("apiSecret", false))
        ;
    }

    /**
     * Check if Shopify Logistic is Enabled.
     *
     * @return bool
     */
    public function hasLogisticMode(): bool
    {
        return !empty($this->getParameter("LogisticMode", false));
    }

    /**
     * Check if Happy Commerce Colissimo Plugin is Enabled.
     *
     * @return bool
     */
    public function hasHappyColissimoPlugin(): bool
    {
        return !empty($this->getParameter("HappyColissimo", false));
    }

    /**
     * Check if Mondial Relay Plugin is Enabled.
     *
     * @return bool
     */
    public function hasMondialRelayPlugin(): bool
    {
        return !empty($this->getParameter("MondialRelay", false));
    }
}
