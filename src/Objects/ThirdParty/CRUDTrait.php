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
     * @return ArrayObject|false
     */
    public function load($objectId)
    {
        //====================================================================//
        // Stack Trace
        Splash::log()->trace();
        //====================================================================//
        // Get Customer from Api
        $object = API::get('customers', $objectId, array(), "customer");
        //====================================================================//
        // Fetch Object
        if (null === $object) {
            return Splash::log()->errTrace(" Unable to load Customer (".$objectId.").");
        }

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
        Splash::log()->trace();
        //====================================================================//
        // Check Customer Name is given
        if (empty($this->in["email"])) {
            return Splash::log()->err("ErrLocalFieldMissing", __CLASS__, __FUNCTION__, "Email");
        }
        //====================================================================//
        // Create New Customer
        $this->object = new ArrayObject(array( "id" => null ), ArrayObject::ARRAY_AS_PROPS);
        $this->setSimple("email", $this->in["email"]);
        //====================================================================//
        // PHPUnit => Add a Default Address
        if (!empty(Splash::input("SPLASH_TRAVIS"))) {
            $this->object->addresses = array(
                array(
                    "address1" => "1 Rue des Carrieres",
                    "city" => "Montreal",
                    "first_name" => "John",
                    "last_name" => "Doe",
                    "country_code" => "CA",
                    "country_name" => "Canada",
                ), );
        }
        //====================================================================//
        // Create Customer from Api
        $newCustomer = API::post('customers', array("customer" => $this->object), "customer");
        if (null === $newCustomer) {
            return Splash::log()->errTrace(" Unable to Create Customer (".$this->in["email"].").");
        }

        return new ArrayObject($newCustomer, ArrayObject::ARRAY_AS_PROPS);
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
        Splash::log()->trace();
        if (!$needed) {
            return $this->getObjectIdentifier();
        }
        //====================================================================//
        // Update Customer from Api
        if (null === API::put('customers/'.$this->object->id, array("customer" => $this->object))) {
            return Splash::log()->errTrace(" Unable to Update Customer (".$this->object->id.").");
        }

        return $this->getObjectIdentifier();
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
        Splash::log()->trace();
        //====================================================================//
        // Delete Customer from Api
        if (null === API::delete('customers/'.$objectId)) {
            return Splash::log()->errTrace(" Unable to Delete Customer (".$objectId.").");
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function getObjectIdentifier()
    {
        if (!isset($this->object->id)) {
            return false;
        }

        return (string) $this->object->id;
    }
}
