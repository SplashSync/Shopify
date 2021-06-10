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

namespace   Splash\Connectors\Shopify\Objects\ThirdParty;

use Slince\Shopify\Model\Customers\Customer;
use Splash\Client\Splash;
use Splash\Connectors\Shopify\Helpers\TypeErrorCatcher;
use Splash\Connectors\Shopify\Models\ShopifyHelper as API;

/**
 * Shopify Customer List Functions
 */
trait ObjectsListTrait
{
    /**
     * {@inheritdoc}
     */
    public function objectsList($filter = null, $params = null)
    {
        //====================================================================//
        // Execute List Request
        $rawData = API::list(
            'customers',
            (isset($params["max"]) ? $params["max"] : 0),
            (isset($params["offset"]) ? $params["offset"] : 0),
            (!empty($filter) ? array("query" => $filter) : array())
        );
        //====================================================================//
        // Request Failed
        if (null === $rawData) {
            return array( 'meta' => array('current' => 0, 'total' => 0));
        }
        //====================================================================//
        // Compute Totals
        $response = array(
            'meta' => array('current' => count($rawData), 'total' => API::count('customers') ),
        );
        //====================================================================//
        // Parse Data in response
        /** @var Customer $customer */
        foreach ($rawData as $customer) {
            //====================================================================//
            // Parse Meta Dates to Splash Format
            try {
                $response[] = array(
                    'id' => $customer->getId(),
                    'created_at' => self::toDateTimeString($customer->getCreatedAt()),
                    'updated_at' => self::toDateTimeString($customer->getUpdatedAt()),
                    'state' => ("enabled" == $customer->getState()),
                    'email' => $customer->getEmail(),
                    'phone' => TypeErrorCatcher::get($customer, "getPhone")
                );
            } catch (\Throwable $exception) {
                Splash::log()->err($exception->getMessage());
            }
        }

        return $response;
    }
}
