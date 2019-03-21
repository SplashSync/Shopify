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

namespace Splash\Connectors\Shopify\Objects\Product\Variants;

use ArrayObject;
use Splash\Connectors\Shopify\Models\ShopifyHelper as API;
use Splash\Core\SplashCore      as Splash;

/**
 * Shopify Product Variants CRUD Functions
 */
trait CRUDTrait
{
    /**
     * Load Product Variants Informations
     *
     * @param array $product Shopify Product Object
     *
     * @return bool
     */
    public function loadVariant($product)
    {
        //====================================================================//
        // Stack Trace
        Splash::log()->trace();
        //====================================================================//
        // Identify Variant
        foreach ($product['variants'] as $index => $variant) {
            if ($variant['id'] == $this->variantId) {
                $product['variants'][$index] = new ArrayObject($variant, ArrayObject::ARRAY_AS_PROPS);
                $this->variantIndex = $index;
                $this->variant = $product['variants'][$index];

                break;
            }
        }
        //====================================================================//
        // NO Variant found => Return False
        if (!isset($this->variant)) {
            return Splash::log()->errTrace(" Unable to load Product Variant (".$this->variantId.").");
        }

        return true;
    }

    /**
     * Create New Product Variant
     *
     * @return ArrayObject|false
     */
    public function createVariant()
    {
        //====================================================================//
        // Stack Trace
        Splash::log()->trace();
        //====================================================================//
        // Load Existing Parent Id
        $productId = $this->getParentProductId();

        if (null !== $productId) {
            return Splash::log()->errTrace(" Unable to Create Product Variant (".$productId.").");
        }
        //====================================================================//
        // Check Options are Given
        if (!isset($this->in["attributes"]) || empty($this->in["attributes"])) {
            return Splash::log()->err("ErrLocalFieldMissing", __CLASS__, __FUNCTION__, "Variant Options");
        }
        //====================================================================//
        // Create Variant With Options
        $variant = new ArrayObject();
        $variant["inventory_management"] = "shopify";
        $index = 0;
        foreach ($this->in["attributes"] as $item) {
            //====================================================================//
            // Check Product Attributes is Valid & Not More than 3 Options!
            if (!$this->isValidAttributeDefinition($item) && ($index < 3)) {
                continue;
            }
            //====================================================================//
            // Update Attribute Value
            $variant["option".($index + 1)] = $item["value"];
            //====================================================================//
            // Inc. Attribute Index
            $index++;
        }
        //====================================================================//
        // Create New Product from Api
        $response = API::post(
            "products/".$productId."/variants",
            array("variant" => $variant),
            "product"
            );
        if (null === $response) {
            return Splash::log()->errTrace(" Unable to Create Product Variant (".$productId.").");
        }

        return $this->load(self::getObjectId($response["product_id"], $response["id"]));
    }
}
