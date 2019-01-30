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

namespace   Splash\Connectors\Shopify\Objects\Product;

use DateTime;
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
        // Prepare Parameters
        $query = array();
        if (isset($params["max"]) && ($params["max"] > 0) && isset($params["offset"]) && ($params["offset"] >= 0)) {
            $query = array(
                'limit'    =>   $params["max"],
                'page'     =>   (1 + (int) ($params["offset"] / $params["max"])),
            );
        }
        //====================================================================//
        // Execute Products List Request
        $rawData = API::get('products', null, $query, 'products');
        //====================================================================//
        // Request Failed
        if (false == $rawData) {
            return array( 'meta'    => array('current' => 0, 'total' => 0));
        }
        //====================================================================//
        // Parse Data in response
        $response = array();
        foreach ($rawData as $product) {
            foreach ($product['variants'] as $variant) {
                $response[]   = array(
                    'id'                        =>      self::getObjectId($product['id'], $variant['id']),
                    'title'                     =>      $product['title'],
                    'variant_title'             =>      $product['title']." - ".$variant['title'],
                    'sku'                       =>      $variant['sku'],
                    'price'                     =>      $variant['price'],
                    'inventory_quantity'        =>      $variant['inventory_quantity'],
                    'created_at'                =>      (new DateTime($product['created_at']))->format(SPL_T_DATETIMECAST),
                    'updated_at'                =>      (new DateTime($product['updated_at']))->format(SPL_T_DATETIMECAST),
                );
            }
        }
        //====================================================================//
        // Compute Totals
        $response["meta"] = array(
            'current' => count($response),
            'total' => API::count('products'),
        );
        
        return $response;
    }
}
