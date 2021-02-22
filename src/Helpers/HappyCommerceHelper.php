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
 * HappyCommerce Colissimo Orders Helper
 */
class HappyCommerceHelper
{
    /**
     * @var string
     */
    const NAMESPACE = "Colissimo";

    /**
     * @var string
     */
    const KEY = "service";

    /**
     * @var array
     */
    const REQUIRED = array(
        "pickup_id", "company",
        "address_1", "zip", "city"
    );

    /**
     * Analyze Order Metadata & Override Shipping Address when Colissimo Infos Detected
     *
     * @param array $order
     * @param array $metadatas
     */
    public static function apply(array &$order, array $metadatas): void
    {
        //====================================================================//
        // Identify & Extract Colissimo Metadata
        $colissimoData = self::extractData($metadatas);
        if (!$colissimoData) {
            return;
        }
        //====================================================================//
        // Override Order Data with Colissimo Metadata
        self::overrideOrder($order, $colissimoData);
    }

    /**
     * Extract Colissimo Metadata by Namespace & Key
     *
     * @param array $metadatas
     *
     * @return null|array
     */
    private static function extractData(array $metadatas): ?array
    {
        if (!empty($metadatas)) {
            foreach ($metadatas as $metadata) {
                if (self::NAMESPACE != $metadata["namespace"]) {
                    continue;
                }
                if (self::KEY != $metadata["key"]) {
                    continue;
                }

                return self::validate($metadata["value"] ?: "");
            }
        }

        return null;
    }

    /**
     * Decode & Validate Colissimo Metadata
     *
     * @param string $metaValue
     *
     * @return null|array
     */
    private static function validate(string $metaValue): ?array
    {
        //====================================================================//
        // Decode Json Data to Array
        $metadata = json_decode($metaValue, true);
        //====================================================================//
        // Safety Check
        if (!is_array($metadata)) {
            return null;
        }
        //====================================================================//
        // Verify Required Fields
        foreach (self::REQUIRED as $index) {
            if (!isset($metadata[$index]) || empty($metadata[$index])) {
                return null;
            }
        }

        return $metadata;
    }

    /**
     * Override Order Shipping Address with Colissimo Metadata
     *
     * @param array $order
     * @param array $colissimoData
     *
     * @return void
     */
    private static function overrideOrder(array &$order, array $colissimoData): void
    {
        //====================================================================//
        // Delivery Company
        if (isset($colissimoData["company"])) {
            $order["shipping_address"]["company"] = (string) $colissimoData["company"];
        }
        //====================================================================//
        // Delivery Address 1
        if (isset($colissimoData["address_1"])) {
            $order["shipping_address"]["address1"] = (string) $colissimoData["address_1"];
        }
        //====================================================================//
        // Delivery Address 2
        if (isset($colissimoData["address_2"])) {
            $order["shipping_address"]["address2"] = (string) $colissimoData["address_2"];
        }
        //====================================================================//
        // Delivery Zip
        if (isset($colissimoData["zip"])) {
            $order["shipping_address"]["zip"] = (string) $colissimoData["zip"];
        }
        //====================================================================//
        // Delivery City
        if (isset($colissimoData["city"])) {
            $order["shipping_address"]["city"] = (string) $colissimoData["city"];
        }
        //====================================================================//
        // Pickup ID
        if (isset($colissimoData["pickup_id"])) {
            $order["note"] = (string) $colissimoData["pickup_id"];
        }
    }
}
