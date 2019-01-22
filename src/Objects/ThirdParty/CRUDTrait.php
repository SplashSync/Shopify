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

namespace Splash\Connectors\Shopify\Objects\ThirdParty;

use ArrayObject;
use Splash\Connectors\Shopify\Models\ShopifyHelper as API;
use Splash\Core\SplashCore      as Splash;

/**
 * Shopify Customer CRUD Functions
 */
trait CRUDTrait
{
    /**
     * Load Request Object
     *
     * @param string $objectId Object id
     *
     * @return false|ArrayObject
     */
    public function load($objectId)
    {
        //====================================================================//
        // Stack Trace
        Splash::log()->trace(__CLASS__, __FUNCTION__);
        //====================================================================//
        // Get Customer from Api
        $object  =   API::get('customers', $objectId, array(), "customer");
        //====================================================================//
        // Fetch Object
        if (null === $object) {
            return Splash::log()->err("ErrLocalTpl", __CLASS__, __FUNCTION__, " Unable to load Customer (".$objectId.").");
        }

        return new ArrayObject($object, ArrayObject::ARRAY_AS_PROPS);
    }

    /**
     * Create Request Object
     *
     * @param array $List Given Object Data
     *
     * @return false|ArrayObject
     */
    public function create()
    {
        //====================================================================//
        // Stack Trace
        Splash::log()->trace(__CLASS__, __FUNCTION__);
        //====================================================================//
        // Check Customer Name is given
        if (empty($this->in["email"])) {
            return Splash::log()->err("ErrLocalFieldMissing", __CLASS__, __FUNCTION__, "Email");
        }
        //====================================================================//
        // Create New Customer
        $this->object   =   new ArrayObject(array( "id" => null ), ArrayObject::ARRAY_AS_PROPS);
        $this->setSimple("email", $this->in["email"]);
        //====================================================================//
        // PHPUnit => Add a Default Address
        if (!empty(Splash::input("SPLASH_TRAVIS"))) {
            $this->object->addresses = array(
                array(
                    "address1"      => "1 Rue des Carrieres",
                    "city"          => "Montreal",
                    "first_name"    => "John",
                    "last_name"     => "Doe",
                    "country_code"  =>  "CA",
                    "country_name"  =>  "Canada",
                ), );
        }
        //====================================================================//
        // Create Customer from Api
        $newCustomer   =   API::post('customers', array("customer" => $this->object), "customer");
        if (null === $newCustomer) {
            return Splash::log()->err("ErrLocalTpl", __CLASS__, __FUNCTION__, " Unable to Create Customer (".$this->in["email"].").");
        }

        return new ArrayObject($newCustomer, ArrayObject::ARRAY_AS_PROPS);
    }
    
    /**
     * Update Request Object
     *
     * @param bool $needed Is This Update Needed
     *
     * @return string Object Id
     */
    public function Update($needed)
    {
        //====================================================================//
        // Stack Trace
        Splash::log()->trace(__CLASS__, __FUNCTION__);
        if (!$needed) {
            return (string) $this->object->id;
        }
        //====================================================================//
        // Update Customer from Api
        if (null === API::put('customers/' . $this->object->id, array("customer" => $this->object))) {
            return Splash::log()->err("ErrLocalTpl", __CLASS__, __FUNCTION__, " Unable to Update Customer (".$this->object->id.").");
        }
        
        return (string) $this->object->id;
    }
    
    /**
     * Delete requested Object
     *
     * @param string $objectId Object Id
     *
     * @return bool
     */
    public function delete($objectId = null)
    {
        //====================================================================//
        // Stack Trace
        Splash::log()->trace(__CLASS__, __FUNCTION__);
        //====================================================================//
        // Delete Customer from Api
        if (null === API::delete('customers/' . $objectId)) {
            return Splash::log()->err("ErrLocalTpl", __CLASS__, __FUNCTION__, " Unable to Delete Customer (".$objectId.").");
        }
        
        return true;
    }
}
