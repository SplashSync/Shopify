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

namespace Splash\Connectors\Shopify\Objects\Product;

use Exception;
use Slince\Shopify\Model\Products\Product;
use Slince\Shopify\Model\Products\Variant;
use Splash\Connectors\Shopify\Models\ShopifyHelper as API;

/**
 * Shopify Product List Functions
 */
trait ObjectsListTrait
{
    /**
     * {@inheritdoc}
     *
     * @throws Exception
     */
    public function objectsList(string $filter = null, array $params = array()): array
    {
        //====================================================================//
        // Execute Product List Request
        $rawData = API::list(
            'products',
            ($params["max"] ?? 0),
            ($params["offset"] ?? 0),
            (!empty($filter) ? array("title" => $filter) : array())
        );
        //====================================================================//
        // Request Failed
        if (!$rawData) {
            return array( 'meta' => array('current' => 0, 'total' => 0));
        }
        //====================================================================//
        // Parse Data in response
        $response = array();
        /** @var Product $product */
        foreach ($rawData as $product) {
            /** @var Variant $variant */
            foreach ($product->getVariants() as $variant) {
                $response[] = array(
                    'id' => self::getObjectId((string) $product->getId(), (string) $variant->getId()),
                    'title' => $product->getTitle(),
                    'variant_title' => $product->getTitle()." - ".$variant->getTitle(),
                    /** @phpstan-ignore-next-line  */
                    'published' => !empty($product->getPublishedAt()),
                    'sku' => $variant->getSku(),
                    'price' => $variant->getPrice(),
                    'inventory_quantity' => $variant->getInventoryQuantity(),
                    'created_at' => self::toDateTimeString($product->getCreatedAt()),
                    'updated_at' => self::toDateTimeString($product->getUpdatedAt()),
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
