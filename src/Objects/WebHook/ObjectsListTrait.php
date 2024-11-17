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

namespace Splash\Connectors\Shopify\Objects\WebHook;

use DateTime;
use Splash\Connectors\Shopify\Models\ShopifyHelper as API;

/**
 * Shopify WebHook Objects List Functions
 */
trait ObjectsListTrait
{
    /**
     * {@inheritdoc}
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function objectsList(string $filter = null, array $params = array()): array
    {
        //====================================================================//
        // Get WebHooks Lists from Api
        $rawData = API::get('webhooks', null, array(), "webhooks");

        //====================================================================//
        // Request Failed
        if (null == $rawData) {
            return array( 'meta' => array('current' => 0, 'total' => 0));
        }
        //====================================================================//
        // Compute Totals
        $response = array(
            'meta' => array('current' => count($rawData), 'total' => API::count('webhooks')),
        );
        //====================================================================//
        // Parse Data in response
        foreach ($rawData as $webhook) {
            $response[] = array(
                //====================================================================//
                // Parse Core Data
                'id' => $webhook['id'],
                'address' => $webhook['address'],
                'topic' => $webhook['topic'],
                //====================================================================//
                // Parse Meta Dates to Splash Format
                'created_at' => (new DateTime($webhook['created_at']))->format(SPL_T_DATETIMECAST),
            );
        }

        return $response;
    }
}
