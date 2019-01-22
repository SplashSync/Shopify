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

namespace Splash\Connectors\Shopify\Objects\Order;

use ArrayObject;
use Splash\Core\SplashCore      as Splash;
use Splash\Connectors\Shopify\Models\ShopifyHelper as API;

/**
 * Shopify Order CRUD Functions
 */
trait CRUDTrait
{
    
    /**
     * Load Request Object
     *
     * @param       string $objectId Object id
     *
     * @return     false|ArrayObject
     */
    public function load($objectId)
    {
        //====================================================================//
        // Stack Trace
        Splash::log()->trace(__CLASS__, __FUNCTION__);
        //====================================================================//
        // Get Customer Order from Api
        $object  =   API::get("orders", $objectId, array(), "order");        
        //====================================================================//
        // Fetch Object from Shopify
        if (null === $object) {
            return Splash::log()->err("ErrLocalTpl", __CLASS__, __FUNCTION__, " Unable to load Order/Invoice (".$objectId.").");
        }
dump($object);
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

        return Splash::log()->err("Splash API Cannot Create Shopify Orders!");
    }
    
    /**
     * Update Request Object
     *
     * @param       array $Needed Is This Update Needed
     *
     * @return      string      Object Id
     */
    public function Update($Needed)
    {
        //====================================================================//
        // Stack Trace
        Splash::log()->trace(__CLASS__, __FUNCTION__);

        return Splash::log()->err("Splash API Cannot Update Shopify Orders!");
    }
    
    /**
     * Delete requested Object
     *
     * @param       int $Id Object Id.  If NULL, Object needs to be created.
     *
     * @return      bool
     */
    public function Delete($Id = null)
    {
        //====================================================================//
        // Stack Trace
        Splash::log()->trace(__CLASS__, __FUNCTION__);

        return Splash::log()->err("Splash API Cannot Delete Shopify Orders!");
    }

}
