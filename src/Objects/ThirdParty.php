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
use Splash\Connectors\Shopify\Services\ShopifyConnector;
use Splash\Models\AbstractObject;
use Splash\Models\Objects\IntelParserTrait;
use Splash\Models\Objects\SimpleFieldsTrait;

/**
 * Shopify Implementation of ThirdParty
 */
class ThirdParty extends AbstractObject
{
    // Splash Php Core Traits
    use IntelParserTrait;
    use SimpleFieldsTrait;
    
    // Shopify Core Traits
    use DatesTrait;
    
    // Shopify Customer Traits
    use ThirdParty\CRUDTrait;
    use ThirdParty\ObjectsListTrait;
    use ThirdParty\CoreTrait;
    use ThirdParty\MainTrait;
    use ThirdParty\AddressTrait;
    
    /**
     *  Object Disable Flag. Override this flag to disable Object.
     */
    protected static $DISABLED        =  false;
    /**
     *  Object Name
     */
    protected static $NAME            =  "Customer";
    /**
     *  Object Description
     */
    protected static $DESCRIPTION     =  "Shopify Customer";
    /**
     *  Object Icon (FontAwesome or Glyph ico tag)
     */
    protected static $ICO     =  "fa fa-user";
    
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
}
