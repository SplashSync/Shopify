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

namespace   Splash\Connectors\Shopify\Objects\Invoice;

use Exception;
use Slince\Shopify\Model\Orders\Order;
use Splash\Connectors\Shopify\Helpers\TypeErrorCatcher;
use Splash\Connectors\Shopify\Models\ShopifyHelper as API;

/**
 * Shopify Invoice List Functions
 */
trait ObjectsListTrait
{
    /**
     * {@inheritdoc}
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     *
     * @throws Exception
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
        // Request Failed
        if (null === $rawData) {
            return array( 'meta' => array('total' => 0, 'current' => 0));
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
                'processed_at' => self::toDateTimeString($order->getProcessedAt()),
                'updated_at' => self::toDateTimeString($order->getUpdatedAt()),
                'status' => self::getSplashStatus(
                    $order->isConfirmed(),
                    TypeErrorCatcher::getDateTime($order, "getCancelledAt"),
                    $order->getFinancialStatus()
                ),
            );
        }

        return $response;
    }
}
