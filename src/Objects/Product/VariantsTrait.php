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

use ArrayObject;

/**
 * Shopify Product Variant Data Access
 */
trait VariantsTrait
{
    use Variants\CRUDTrait;
    use Variants\CoreTrait;
    use Variants\AttributesTrait;

    /**
     * Shopify Product ID
     *
     * @var null|string
     */
    protected ?string $productId;

    /**
     * Shopify Product Variant ID
     *
     * @var null|string
     */
    protected ?string $variantId;

    /**
     * Shopify Product Variant Object
     *
     * @var ArrayObject
     */
    protected ArrayObject $variant;

    /**
     * Shopify Product Variant Index
     *
     * @var null|int
     */
    protected ?int $variantIndex;

    //====================================================================//
    // Product Variants Id Management
    //====================================================================//

    /**
     * Extract Base Product Id from Splash Product Id
     *
     * @param string $objectId
     *
     * @return null|string
     */
    public static function getProductId(string $objectId) : ?string
    {
        $array = explode("-", $objectId);

        return isset($array[1]) ? $array[0] : null;
    }

    /**
     * Extract Product Variant Id from Splash Product Id
     *
     * @param string $objectId
     *
     * @return null|string
     */
    public static function getVariantId(string $objectId) : ?string
    {
        $array = explode("-", $objectId);

        return isset($array[1]) ? $array[1] : null;
    }

    /**
     * Encode Splash Address Id from Shopify Customer && Address Id
     *
     * @param string $productId
     * @param string $variantId
     *
     * @return string
     */
    public static function getObjectId(string $productId, string $variantId)
    {
        return $productId."-".$variantId;
    }
}
