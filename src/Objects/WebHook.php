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

use Splash\Bundle\Models\AbstractStandaloneObject;
use Splash\Connectors\Shopify\Services\ShopifyConnector;
use Splash\Models\AbstractObject;
use Splash\Models\Objects\IntelParserTrait;
use Splash\Models\Objects\SimpleFieldsTrait;

/**
 * Shopify Implementation of WebHooks
 */
class WebHook extends AbstractStandaloneObject
{
    use IntelParserTrait;
    use SimpleFieldsTrait;
    use WebHook\CRUDTrait;
    use WebHook\CoreTrait;
    use WebHook\ObjectsListTrait;
    
    /**
     *  Object Disable Flag. Override this flag to disable Object.
     */
    protected static $DISABLED        =  true;
    /**
     *  Object Name
     */
    protected static $NAME            =  "WebHook";
    /**
     *  Object Description
     */
    protected static $DESCRIPTION     =  "Shopify WebHook";
    /**
     *  Object Icon (FontAwesome or Glyph ico tag)
     */
    protected static $ICO     =  "fa fa-cogs";
    
    /**
     * @var ShopifyConnector
     */
    protected $connector;
    
    /**
     * Class Constructor
     *
     * @param ShopifyConnector $parentConnector
     */
    public function __construct(ShopifyConnector $parentConnector)
    {
        $this->connector  =   $parentConnector;
    }
    
    /**
     * Check if WebHook Has Requested Parameters.
     *
     * @param   array $webHook Shopify WebHook Object
     * @param   string      $url     Splash WebHook Url
     * @param   string      $topic   WebHook Shopify Topic
     *
     * @return bool
     */
    public static function isValid(array $webHook, string $url, string $topic = null) : bool
    {
        //====================================================================//
        // This is a Splash WebHooks
        if (false === strpos(trim($webHook['address']), $url)) {
            return false;
        }        
        //====================================================================//
        // Check Topic
        if (!in_array($webHook['topic'], array_keys(static::$topics))) {
            return false;
        }
        if ((null !== $topic) && ($webHook['topic'] != $topic)) {
            return false;
        }

        return true;
    }    
}
