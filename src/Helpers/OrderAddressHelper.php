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

namespace Splash\Connectors\Shopify\Helpers;

use Splash\Models\Objects\ObjectsTrait;

/**
 * Manage Order Addresses as Generic Addresses
 */
class OrderAddressHelper
{
    use ObjectsTrait;

    const FORMAT_SHIPPING = "OS-%s";

    const FORMAT_BILLING = "OB-%s";

    /**
     * Check if Address ID is an Order Address ID
     */
    public static function isOrderAddress(string $objectId): bool
    {
        return !empty(self::toOrderShippingId($objectId))
            || !empty(self::toOrderBillingId($objectId))
        ;
    }

    /**
     * Convert Delivery Address ID to Order ID
     */
    public static function toOrderShippingId(string $objectId): ?string
    {
        /** @var null|string $shippingId */
        $shippingId = null;
        sscanf($objectId, self::FORMAT_SHIPPING, $shippingId);

        return (string) $shippingId ?: null;
    }

    /**
     * Convert Billing Address ID to Order ID
     */
    public static function toOrderBillingId(string $objectId): ?string
    {
        /** @var null|string $billingId */
        $billingId = null;
        sscanf($objectId, self::FORMAT_BILLING, $billingId);

        return (string) $billingId ?: null;
    }

    /**
     * Convert Order ID to Delivery Address ID
     */
    public static function toShippingId(string $orderId): ?string
    {
        return self::objects()->encode(
            "Address",
            sprintf(self::FORMAT_SHIPPING, $orderId)
        );
    }

    /**
     * Convert Order ID to Invoicing Address ID
     */
    public static function toBillingId(string $orderId): ?string
    {
        return self::objects()->encode(
            "Address",
            sprintf(self::FORMAT_BILLING, $orderId)
        );
    }
}
