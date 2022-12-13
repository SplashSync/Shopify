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

namespace Splash\Connectors\Shopify\Objects\Order;

use Slince\Shopify\Model\Customers\Customer;
use Splash\Connectors\Shopify\Models\ShopifyHelper as API;

/**
 * Shopify Search Orders & Invoices by Primary Keys
 */
trait PrimaryTrait
{
    /**
     * @inheritDoc
     */
    public function getByPrimary(array $keys): ?string
    {
        //====================================================================//
        // Safety Checks
        $keys = array_filter($keys);
        if (empty($keys)) {
            return null;
        }
        //====================================================================//
        // Execute Customers Primary Request
        $rawData = API::list(
            'orders',
            5,
            0,
            array_merge(array("status" => "any"), $keys)
        );
        //====================================================================//
        // Ensure Only One Result
        if (!$rawData || (1 != count($rawData))) {
            return null;
        }
        //====================================================================//
        // Return ID of First Customer
        /** @var Customer[] $rawData */
        $customer = array_shift($rawData);

        return $customer ? (string) $customer->getId() : null;
    }
}
