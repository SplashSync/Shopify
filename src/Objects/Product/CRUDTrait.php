<?php
/**
 * This file is part of SplashSync Project.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 *  @author    Splash Sync <www.splashsync.com>
 *
 *  @copyright 2015-2017 Splash Sync
 *
 *  @license   GNU GENERAL PUBLIC LICENSE Version 3, 29 June 2007
 *
 **/

namespace Splash\Connectors\Shopify\Objects\Product;

use ArrayObject;
use Splash\Core\SplashCore      as Splash;
use Splash\Connectors\Shopify\Models\ShopifyHelper as API;

/**
 * Shopify Product CRUD Functions
 */
trait CRUDTrait
{
    
    /**
     * Load Request Object
     *
     * @param       string $objectId Object id
     *
     * @return      false|ArrayObject
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
     * @param       array $List Given Object Data
     *
     * @return      object     New Object
     */
    public function Create()
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
        // Create New Product
        $this->object   =   new ArrayObject([ "id" => null ], ArrayObject::ARRAY_AS_PROPS);
        $this->setSimple("title", $this->in["title"]);
        
        $Response   =   $this->Connector->createShopifyObject($this->getShopifyProduct(), $this->object);
        if (!$Response) {
            return false;
        }
        $NewProduct =   new ArrayObject($Response, ArrayObject::ARRAY_AS_PROPS);
                
        //====================================================================//
        // Store New Ids
        $this->ProductId = $NewProduct->id;
        $this->Variant   = end($NewProduct->variants);
        $this->VariantId = !empty($this->Variant) ? $this->Variant->id : null;
        
        //====================================================================//
        // Default Setup for New Product Variant
        $this->setSimple("inventory_management", "shopify", "Variant");

        return $NewProduct;
    }
    
    /**
     * Update Request Object
     *
     * @param       bool $needed Is This Update Needed
     *
     * @return      string      Object Id
     */
    public function Update($needed)
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
            if (null === API::post('inventory_levels/set', $this->getNewInventorylevel())) {
                return Splash::log()->err("ErrLocalTpl", __CLASS__, __FUNCTION__, " Unable to Update Product Variant Stock (".$objectId.").");
            }                
        }
        
        return $objectId;
    }
    
    /**
     * Delete requested Object
     *
     * @param       int $objectId Object Id
     *
     * @return      bool
     */
    public function Delete($objectId = null)
    {
        //====================================================================//
        // Stack Trace
        Splash::log()->trace(__CLASS__, __FUNCTION__);
        //====================================================================//
        // Explode Storage Id
        $this->productId = self::getProductId($objectId);
        $this->variantId = self::getVariantId($objectId);
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
        $baseUri = 'products/' . $productId;
        if(!is_null($variantId)) {
            $baseUri.=  "/variants/".$variantId;
        }

        return $baseUri;
    }    
}
