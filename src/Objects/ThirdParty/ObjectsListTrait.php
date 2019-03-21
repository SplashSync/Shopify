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

namespace   Splash\Connectors\Shopify\Objects\ThirdParty;

use DateTime;
use Splash\Connectors\Shopify\Models\ShopifyHelper as API;

/**
 * Shopify Customer List Functions
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
        // Execute List Request
        $rawData = API::list(
            'customers',
            (isset($params["max"]) ? $params["max"] : 0),
            (isset($params["offset"]) ? $params["offset"] : 0)
        );
        //====================================================================//
        // Request Failled
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
        /** @var array $customer */
        foreach ($rawData as $customer) {
            //====================================================================//
            // Parse Meta Dates to Splash Format
            $customer['created_at'] = (new DateTime($customer['created_at']))->format(SPL_T_DATETIMECAST);
            $customer['updated_at'] = (new DateTime($customer['updated_at']))->format(SPL_T_DATETIMECAST);

            $response[] = $customer;
        }

        return $response;
    }
}
