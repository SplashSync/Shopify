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

namespace Splash\Connectors\Shopify\Objects;

use ArrayObject;
use Splash\Bundle\Models\AbstractStandaloneObject;
use Splash\Connectors\Shopify\Services\ShopifyConnector;
use Splash\Models\Objects\IntelParserTrait;
use Splash\Models\Objects\SimpleFieldsTrait;

/**
 * Shopify Implementation of Products
 *
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.CamelCasePropertyName)
 */
class Product extends AbstractStandaloneObject
{
    // Splash Php Core Traits
    use IntelParserTrait;
    use SimpleFieldsTrait;
    
    // Shopify Core Traits
    use Core\DatesTrait;
    use Core\UnitConverterTrait;
    
    // Shopify Products Traits
    use Product\CRUDTrait;
    use Product\ObjectsListTrait;
    use Product\CoreTrait;
    use Product\MainTrait;
    use Product\DescTrait;
    use Product\StockTrait;
    use Product\ImagesTrait;
    
    //====================================================================//
    // Object Definition Parameters
    //====================================================================//
    
    /**
     *  Object Disable Flag. Uncomment thius line to Override this flag and disable Object.
     */
//    protected static    $DISABLED        =  True;
    
    /**
     *  Object Name (Translated by Module)
     */
    protected static $NAME            =  "Product";
    
    /**
     *  Object Description (Translated by Module)
     */
    protected static $DESCRIPTION     =  "Shopify Product Object";
    
    /**
     *  Object Icon (FontAwesome or Glyph ico tag)
     */
    protected static $ICO     =  "fa fa-product-hunt";
    
    /**
     * Object Synchronistion Limitations
     *
     * This Flags are Used by Splash Server to Prevent Unexpected Operations on Remote Server
     *
     * @codingStandardsIgnoreStart
     */
    protected static $ALLOW_PUSH_CREATED         =  true;        // Allow Creation Of New Local Objects
    protected static $ALLOW_PUSH_UPDATED         =  true;        // Allow Update Of Existing Local Objects
    protected static $ALLOW_PUSH_DELETED         =  true;        // Allow Delete Of Existing Local Objects
    
    /**
     * Object Synchronistion Recommended Configuration
     */
    protected static $ENABLE_PUSH_CREATED       =  false;        // Enable Creation Of New Local Objects when Not Existing
    protected static $ENABLE_PUSH_UPDATED       =  true;         // Enable Update Of Existing Local Objects when Modified Remotly
    protected static $ENABLE_PUSH_DELETED       =  true;         // Enable Delete Of Existing Local Objects when Deleted Remotly

    protected static $ENABLE_PULL_CREATED       =  true;         // Enable Import Of New Local Objects
    protected static $ENABLE_PULL_UPDATED       =  true;         // Enable Import of Updates of Local Objects when Modified Localy
    protected static $ENABLE_PULL_DELETED       =  true;         // Enable Delete Of Remotes Objects when Deleted Localy
 
    /**
     * @codingStandardsIgnoreEnd
     *
     * @var ShopifyConnector
     */
    protected $connector;
    
    //====================================================================//
    // General Class Variables
    //====================================================================//
    protected $productId;       // Shopify Product Id
    protected $variantId;       // Shopify Product Variant Id
    
    /**
     * Shopify Product Variant Object
     *
     * @var ArrayObject
     */
    protected $variant;
    
    /**
     * Class Constructor
     *
     * @param ShopifyConnector $connector
     */
    public function __construct(ShopifyConnector $connector)
    {
        $this->connector  =   $connector;
    }
    
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
