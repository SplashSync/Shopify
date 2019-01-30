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

namespace Splash\Connectors\Shopify\Objects\Order;

use ArrayObject;
use Splash\Connectors\Shopify\Models\ShopifyHelper as API;
use Splash\Core\SplashCore      as Splash;

/**
 * Shopify Order CRUD Functions
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
        // Get Customer Order from Api
        $object  =   API::get("orders", $objectId, array(), "order");
        //====================================================================//
        // Fetch Object from Shopify
        if (null === $object) {
            return Splash::log()->err("ErrLocalTpl", __CLASS__, __FUNCTION__, " Unable to load Order/Invoice (".$objectId.").");
        }

        return new ArrayObject($object, ArrayObject::ARRAY_AS_PROPS);
    }

    /**
     * Create Request Object
     *
     * @return false
     */
    public function create()
    {
        //====================================================================//
        // Stack Trace
        Splash::log()->trace(__CLASS__, __FUNCTION__);

        return Splash::log()->err("Splash API Cannot Create Shopify Orders!");
    }
    
    /**
     * Update Request Object
     *
     * @param bool $needed Is This Update Needed
     *
     * @return bool Object Id
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function update($needed)
    {
        //====================================================================//
        // Stack Trace
        Splash::log()->trace(__CLASS__, __FUNCTION__);

        return Splash::log()->err("Splash API Cannot Update Shopify Orders!");
    }
    
    /**
     * Delete requested Object
     *
     * @param string $objectId Object Id.
     *
     * @return bool
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function delete($objectId = null)
    {
        //====================================================================//
        // Stack Trace
        Splash::log()->trace(__CLASS__, __FUNCTION__);

        return Splash::log()->err("Splash API Cannot Delete Shopify Orders!");
    }
}
