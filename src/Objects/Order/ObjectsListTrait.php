<?php

/*
 *  This file is part of SplashSync Project.
 *
 *  Copyright (C) 2015-2020 Splash Sync  <www.splashsync.com>
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace   Splash\Connectors\Shopify\Objects\Order;

use Slince\Shopify\Manager\Order\Order;
use Splash\Connectors\Shopify\Models\ShopifyHelper as API;

/**
 * Shopify Product List Functions
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
            'orders',
            (isset($params["max"]) ? $params["max"] : 0),
            (isset($params["offset"]) ? $params["offset"] : 0),
            array("status" => "any")
        );
        //====================================================================//
        // Request Failled
        if (null === $rawData) {
            return array( 'meta' => array('current' => 0, 'total' => 0));
        }
        //====================================================================//
        // Compute Totals
        $response = array(
            'meta' => array('current' => count($rawData), 'total' => API::count('orders')),
        );
        //====================================================================//
        // Parse Data in response
        /** @var Order $order */
        foreach ($rawData as $order) {
            $response[] = array(
                'id' => $order->getId(),
                'name' => $order->getName(),
                'created_at' => self::toDateTimeString($order->getProcessedAt()),
                'updated_at' => self::toDateTimeString($order->getUpdatedAt()),
                'processed_at' => self::toDateTimeString($order->getProcessedAt()),
            );
        }

        return $response;
    }
}
