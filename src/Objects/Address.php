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

use Splash\Connectors\Shopify\Objects\Core\DatesTrait;
use Splash\Connectors\Shopify\Objects\ThirdParty\ShopifyObjectTrait as ShopifyCustomerTrait;
use Splash\Connectors\Shopify\Services\ShopifyConnector;
use Splash\Models\AbstractObject;
use Splash\Models\Objects\IntelParserTrait;
use Splash\Models\Objects\ObjectsTrait;
use Splash\Models\Objects\SimpleFieldsTrait;

/**
 * @abstract    Shopify Implementation of ThirParty Address
 */
class Address extends AbstractObject
{
    // Splash Php Core Traits
    use IntelParserTrait;
    use SimpleFieldsTrait;
    use ObjectsTrait;
    
    // Shopify Customer Traits
    use Address\CRUDTrait;
    use Address\ObjectsListTrait;
    use Address\CoreTrait;
    use Address\MainTrait;
    
    /**
     * Object Disable Flag. Override this flag to disable Object.
     */
    protected static $DISABLED        =  false;
    /**
     * Object Name
     */
    protected static $NAME            =  "Customer Address";
    /**
     * Object Description
     */
    protected static $DESCRIPTION     =  "Shopify Customer Address";
    /**
     * Object Icon (FontAwesome or Glyph ico tag)
     */
    protected static $ICO     =  "fa fa-envelope-o";
    
    /**
     * @var ShopifyConnector
     */
    protected $connector;
    
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
     * Extract Customer Id from Splash Address Id
     *
     * @param string $objectId
     *
     * @return null|string
     */
    public static function getCustomerId(string $objectId) : ?string
    {
        $array = explode("-", $objectId);

        return isset($array[1]) ? $array[0] : null;
    }

    /**
     * Extract Address Id from Splash Address Id
     *
     * @param string $objectId
     *
     * @return null|string
     */
    public static function getAddressId(string $objectId) : ?string
    {
        $array = explode("-", $objectId);

        return isset($array[1]) ? $array[1] : null;
    }

    /**
     * Encode Splash Address Id from Shopify Customer && Address Id
     *
     * @param string $customerId
     * @param string $addressId
     *
     * @return string
     */
    public static function getObjectId(string $customerId, string $addressId) : string
    {
        return $customerId."-".$addressId;
    }
}
