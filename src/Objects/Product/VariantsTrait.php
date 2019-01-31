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

/**
 * Shopify Product Variant Data Access
 */
trait VariantsTrait
{
    use Variants\CRUDTrait;
    use Variants\CoreTrait;
    use Variants\AttributesTrait;
    
    /**
     * Shopify Product Id
     * 
     * @var string
     */
    protected $productId;

    /**
     * Shopify Product Variant Id
     * 
     * @var string
     */
    protected $variantId;       
    
    /**
     * Shopify Product Variant Object
     *
     * @var ArrayObject
     */
    protected $variant;

    /**
     * Shopify Product Variant Index
     * 
     * @var string
     */
    protected $variantIndex;       
    
    //====================================================================//
    // Fields Generation Functions
    //====================================================================//

    /**
     * Build Variation Fields using FieldFactory
     */
    private function buildVariationFields()
    {
        //====================================================================//
        // CHILD PRODUCTS INFORMATIONS
        //====================================================================//
        
//        //====================================================================//
//        // Product Variation List - Product Link
//        $this->fieldsFactory()->Create(self::objects()->Encode("Product", SPL_T_ID))
//            ->Identifier("id")
//            ->Name(__("Children"))
//            ->InList("children")
//            ->MicroData("http://schema.org/Product", "Variation")
//            ->isReadOnly();
//        
//        //====================================================================//
//        // Product Variation List - Product SKU
//        $this->fieldsFactory()->Create(SPL_T_VARCHAR)
//            ->Identifier("sku")
//            ->Name(__("SKU"))
//            ->InList("children")
//            ->MicroData("http://schema.org/Product", "VariationName")
//            ->isReadOnly();
//        
//        //====================================================================//
//        // Product Variation List - Variation Attribute
//        $this->fieldsFactory()->Create(SPL_T_VARCHAR)
//            ->Identifier("attribute")
//            ->Name(__("Attribute"))
//            ->InList("children")
//            ->MicroData("http://schema.org/Product", "VariationAttribute")
//            ->isReadOnly();
    }

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
    
    //====================================================================//
    // Fields Reading Functions
    //====================================================================//
    
//    /**
//     * Read requested Field
//     *
//     * @param string $key       Input List Key
//     * @param string $fieldName Field Identifier / Name
//     *
//     * @return void
//     */
//    private function getVariationsFields($key, $fieldName)
//    {
//        //====================================================================//
//        // Check if List field & Init List Array
//        $fieldId = self::lists()->InitOutput($this->out, "children", $fieldName);
//        if (!$fieldId) {
//            return;
//        }
//        //====================================================================//
//        // READ Fields
//        foreach ($this->product->get_children() as $index => $productId) {
//            switch ($fieldId) {
//                case 'id':
//                    self::lists()->Insert(
//                        $this->out,
//                        "children",
//                        $fieldId,
//                        $index,
//                        self::objects()->Encode("Product", $productId)
//                    );
//
//                    break;
//                case 'sku':
//                    self::lists()
//                        ->Insert($this->out, "children", $fieldId, $index, get_post_meta($productId, "_sku", true));
//
//                    break;
//                case 'attribute':
//                    self::lists()->Insert(
//                        $this->out,
//                        "children",
//                        $fieldId,
//                        $index,
//                        implode(" | ", wc_get_product($productId)->get_attributes())
//                    );
//
//                    break;
//                default:
//                    return;
//            }
//        }
//        unset($this->in[$key]);
//    }    
    
}
