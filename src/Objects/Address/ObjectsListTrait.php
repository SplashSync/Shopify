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

namespace   Splash\Connectors\Shopify\Objects\Address;

use Slince\Shopify\Model\Customers\Customer;
use Splash\Connectors\Shopify\Models\ShopifyHelper as API;

/**
 * Shopify Customer Address List Functions
 */
trait ObjectsListTrait
{
    /**
     * {@inheritdoc}
     */
    public function objectsList(string $filter = null, array $params = array()): array
    {
        //====================================================================//
        // Execute List Request
        $rawData = API::list(
            'customers',
            ($params["max"] ?? 0),
            ($params["offset"] ?? 0),
            (!empty($filter) ? array("query" => $filter) : array())
        );
        //====================================================================//
        // Request Failed
        if (null === $rawData) {
            return array( 'meta' => array('current' => 0, 'total' => 0));
        }
        //====================================================================//
        // Parse Data in response
        $response = array();
        /** @var Customer $customer */
        foreach ($rawData as $customer) {
            foreach ($customer->getAddresses() as $address) {
                $response[] = array(
                    'id' => $this->getObjectId((string) $customer->getId(), (string) $address->getId()),
                    'first_name' => $address->getFirstName(),
                    'last_name' => $address->getLastName(),
                );
            }
        }
        //====================================================================//
        // Compute Totals
        $response["meta"] = array('current' => count($response), 'total' => API::count('customers'));

        return $response;
    }
}
