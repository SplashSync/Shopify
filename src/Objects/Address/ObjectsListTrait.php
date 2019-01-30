<?php

/*
 *  This file is part of SplashSync Project.
 *
 *  Copyright (C) 2015-2019 Splash Sync  <www.splashsync.com>
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace   Splash\Connectors\Shopify\Objects\Address;

use Splash\Connectors\Shopify\Models\ShopifyHelper as API;

/**
 * @abstract    Shopify Customer List Functions
 */
trait ObjectsListTrait
{
    /**
     * {@inheritdoc}
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function objectsList($filter = null, $params = null)
    {
        //====================================================================//
        // Execute Customers List Request
        $rawData = API::list(
            'customers',
            (isset($params["max"]) ? $params["max"] : 0),
            (isset($params["offset"]) ? $params["offset"] : 0)
        );
        //====================================================================//
        // Request Failled
        if (null === $rawData) {
            return array( 'meta'    => array('current' => 0, 'total' => 0));
        }
        //====================================================================//
        // Parse Data in response
        $response = array();
        foreach ($rawData as $customer) {
            foreach ($customer['addresses'] as $address) {
                $response[]   = array(
                    'id'                        =>      $this->getObjectId($customer['id'], $address['id']),
                    'first_name'                =>      $address['first_name'],
                    'last_name'                 =>      $address['last_name'],
                );
            }
        }
        //====================================================================//
        // Compute Totals
        $response["meta"] = array('current' => count($response), 'total' => API::count('customers'));
        
        return $response;
    }
}
