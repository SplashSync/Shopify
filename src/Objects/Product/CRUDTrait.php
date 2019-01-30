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

namespace Splash\Connectors\Shopify\Objects\Product;

use ArrayObject;
use Splash\Connectors\Shopify\Models\ShopifyHelper as API;
use Splash\Core\SplashCore      as Splash;

/**
 * Shopify Product CRUD Functions
 */
trait CRUDTrait
{
    /**
     * Load Request Object
     *
     * @param string $objectId Object id
     *
     * @return ArrayObject|false
     */
    public function load($objectId)
    {
        //====================================================================//
        // Stack Trace
        Splash::log()->trace(__CLASS__, __FUNCTION__);
        //====================================================================//
        // Explode Storage Id
        $this->productId = $this->getProductId($objectId);
        $this->variantId = $this->getVariantId($objectId);
        //====================================================================//
        // Get Product from Api
        $object  =   API::get(self::getUri($this->productId), null, array(), "product");
        //====================================================================//
        // Fetch Object from Shopify
        if (null === $object) {
            return Splash::log()->err("ErrLocalTpl", __CLASS__, __FUNCTION__, " Unable to load Product (".$objectId.").");
        }
        //====================================================================//
        // Identify Variant
        foreach ($object['variants'] as $variant) {
            if ($variant['id'] == $this->variantId) {
                $this->variant = new ArrayObject($variant, ArrayObject::ARRAY_AS_PROPS);

                break;
            }
        }
        //====================================================================//
        // NO Variant found => Return False
        if (!isset($this->variant)) {
            return Splash::log()->err("ErrLocalTpl", __CLASS__, __FUNCTION__, " Unable to load Product Variant (".$objectId.").");
        }
        //====================================================================//
        // Unset Variants to Avoid Erazing Data
        unset($object['variants']);
        //====================================================================//
        // Return Product
        return new ArrayObject($object, ArrayObject::ARRAY_AS_PROPS);
    }

    /**
     * Create Request Object
     *
     * @return ArrayObject|false
     */
    public function create()
    {
        //====================================================================//
        // Stack Trace
        Splash::log()->trace(__CLASS__, __FUNCTION__);
        //====================================================================//
        // Check Title & Desc given
        if (empty($this->in["title"])) {
            return Splash::log()->err("ErrLocalFieldMissing", __CLASS__, __FUNCTION__, "Product Title");
        }
        //====================================================================//
        // Create New Product from Api
        $response  =   API::post("products", array("title" => $this->in["title"]), "product");
        if (null === $response) {
            return Splash::log()->err("ErrLocalTpl", __CLASS__, __FUNCTION__, " Unable to Create Product (".$this->in["title"].").");
        }
        
        $this->object =   new ArrayObject($response, ArrayObject::ARRAY_AS_PROPS);
                
        //====================================================================//
        // Store New Ids
        $this->productId = $this->object->id;
        $this->variant   = end($this->object->variants);
        $this->variantId = !empty($this->variant) ? $this->variant->id : null;
        
        //====================================================================//
        // Default Setup for New Product Variant
        $this->setSimple("inventory_management", "shopify", "Variant");

        return $this->object;
    }
    
    /**
     * Update Request Object
     *
     * @param bool $needed Is This Update Needed
     *
     * @return false|string
     */
    public function update($needed)
    {
        //====================================================================//
        // Stack Trace
        Splash::log()->trace(__CLASS__, __FUNCTION__);
        //====================================================================//
        // Encode Object Id
        $objectId = $this->getObjectId($this->productId, $this->variantId);
        
        //====================================================================//
        // Update Product Variant from Api
        if ($needed || $this->isToUpdate("variant")) {
            $this->object->variants = array($this->variant);
            if (null === API::put(self::getUri($this->productId), array("product" => $this->object))) {
                return Splash::log()->err("ErrLocalTpl", __CLASS__, __FUNCTION__, " Unable to Update Product Variant (".$objectId.").");
            }
        }

        //====================================================================//
        // Update Inventory Level
        if ($this->isToUpdate("inventory")) {
            $newInventorylevel = $this->getNewInventorylevel();
            if (is_null($newInventorylevel) || (null === API::post('inventory_levels/set', $newInventorylevel))) {
                return Splash::log()->err("ErrLocalTpl", __CLASS__, __FUNCTION__, " Unable to Update Product Variant Stock (".$objectId.").");
            }
        }
        
        return $objectId;
    }
    
    /**
     * Delete requested Object
     *
     * @param null|string $objectId Object Id
     *
     * @return bool
     */
    public function delete($objectId = null)
    {
        //====================================================================//
        // Stack Trace
        Splash::log()->trace(__CLASS__, __FUNCTION__);
        //====================================================================//
        // Explode Storage Id
        $this->productId = self::getProductId((string) $objectId);
        $this->variantId = self::getVariantId((string) $objectId);
        //====================================================================//
        // Delete Product Variant from Api
        if (null === API::delete(self::getUri($this->productId, $this->variantId))) {
            Splash::log()->err("ErrLocalTpl", __CLASS__, __FUNCTION__, " Unable to Delete Product (".$objectId.").");
        }

        return true;
    }
    
    /**
     * Get Object CRUD Base Uri
     *
     * @param string $productId
     * @param string $variantId
     *
     * @return string
     */
    private static function getUri(string $productId = null, string $variantId = null) : string
    {
        $baseUri = 'products/'.$productId;
        if (!is_null($variantId)) {
            $baseUri .= "/variants/".$variantId;
        }

        return $baseUri;
    }
}
