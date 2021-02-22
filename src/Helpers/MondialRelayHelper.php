<?php

/*
 *  This file is part of SplashSync Project.
 *
 *  Copyright (C) 2015-2021 Splash Sync  <www.splashsync.com>
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace Splash\Connectors\Shopify\Helpers;

/**
 * Mondial Relay Orders Helper
 */
class MondialRelayHelper
{
    /**
     * @var string
     */
    const KEY = "mondial_relay_";

    /**
     * @var array
     */
    const REQUIRED = array(
        "id", "name",
        "country", "address", "zip", "city"
    );

    /**
     * Analyze Order Metadata & Override Shipping Address when Mondial Relay Infos Detected
     *
     * @param array $order
     */
    public static function apply(array &$order): void
    {
        //====================================================================//
        // Identify & Extract Relay Metadata
        $mrData = self::extractData($order);
        if (!$mrData) {
            return;
        }
        //====================================================================//
        // Override Order Data with Relay Metadata
        self::overrideOrder($order, $mrData);
    }

    /**
     * Extract Mondial Relay Metadata on Note
     *
     * @param array $order
     *
     * @return null|array
     */
    private static function extractData(array &$order): ?array
    {
        //====================================================================//
        // Safety Check
        if (empty($order["note_attributes"]) || !is_array($order["note_attributes"])) {
            return null;
        }
        //====================================================================//
        // Walk on Order Note Attributes
        $mrData = array();
        foreach ($order["note_attributes"] as $attribute) {
            if (false === strpos($attribute["name"], self::KEY)) {
                continue;
            }
            $index = (string) str_replace(self::KEY, "", $attribute["name"]);
            if (empty($index) || !is_scalar($attribute["value"])) {
                continue;
            }
            $mrData[$index] = (string) $attribute["value"];
        }

        return self::validate($mrData);
    }

    /**
     * Validate Mondial Relay Metadata
     *
     * @param array $mrData
     *
     * @return null|array
     */
    private static function validate(array $mrData): ?array
    {
        //====================================================================//
        // Verify Required Fields
        foreach (self::REQUIRED as $index) {
            if (!isset($mrData[$index]) || empty($mrData[$index])) {
                return null;
            }
        }

        return $mrData;
    }

    /**
     * Override Order Shipping Address with Colissimo Metadata
     *
     * @param array $order
     * @param array $mrData
     *
     * @return void
     */
    private static function overrideOrder(array &$order, array $mrData): void
    {
        //====================================================================//
        // Delivery Company
        if (isset($mrData["name"])) {
            $order["shipping_address"]["company"] = (string) $mrData["name"];
        }
        //====================================================================//
        // Delivery Address 1
        if (isset($mrData["address"])) {
            $order["shipping_address"]["address1"] = (string) $mrData["address"];
            $order["shipping_address"]["address2"] = "";
        }
        //====================================================================//
        // Delivery Zip
        if (isset($mrData["zip"])) {
            $order["shipping_address"]["zip"] = (string) $mrData["zip"];
        }
        //====================================================================//
        // Delivery City
        if (isset($mrData["city"])) {
            $order["shipping_address"]["city"] = (string) $mrData["city"];
        }
        //====================================================================//
        // Delivery Country
        if (isset($mrData["country"])) {
            $order["shipping_address"]["country_code"] = (string) $mrData["country"];
        }
        //====================================================================//
        // Pickup ID
        if (isset($mrData["id"])) {
            $order["note"] = (string) $mrData["id"];
        }
    }
}
