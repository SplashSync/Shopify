<?php

/*
 *  This file is part of SplashSync Project.
 *
 *  Copyright (C) Splash Sync  <www.splashsync.com>
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
     * @var array
     */
    private static array $deletedCustomers = array();

    /**
     * Load Request Object
     *
     * @param string $objectId Object id
     *
     * @return null|ArrayObject
     */
    public function load(string $objectId): ?ArrayObject
    {
        //====================================================================//
        // Stack Trace
        Splash::log()->trace();
        //====================================================================//
        // PHPUnit => Check if Id is Deleted Customers
        if (!empty(Splash::input("SPLASH_TRAVIS")) && in_array($objectId, self::$deletedCustomers, true)) {
            return Splash::log()->errNull("Loading Deleted Customer (".$objectId.").");
        }
        //====================================================================//
        // Get Customer from Api
        $object = API::get('customers', $objectId, array(), "customer");
        //====================================================================//
        // Fetch Object
        if (null === $object) {
            return Splash::log()->errNull("Unable to load Customer (".$objectId.").");
        }

        return new ArrayObject($object, ArrayObject::ARRAY_AS_PROPS);
    }

    /**
     * Create Request Object
     *
     * @return null|ArrayObject
     */
    public function create(): ?ArrayObject
    {
        //====================================================================//
        // Stack Trace
        Splash::log()->trace();
        //====================================================================//
        // Check Customer Name is given
        if (empty($this->in["email"]) || !is_string($this->in["email"])) {
            Splash::log()->err("ErrLocalFieldMissing", __CLASS__, __FUNCTION__, "Email");

            return null;
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
            return Splash::log()->errNull(" Unable to Create Customer (".$this->in["email"].").");
        }

        return new ArrayObject($newCustomer, ArrayObject::ARRAY_AS_PROPS);
    }

    /**
     * Update Request Object
     *
     * @param bool $needed Is This Update Needed
     *
     * @return null|string
     */
    public function update(bool $needed): ?string
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
            return Splash::log()->errNull(" Unable to Update Customer (".$this->object->id.").");
        }

        return $this->getObjectIdentifier();
    }

    /**
     * Delete requested Object
     *
     * @param string $objectId Object ID
     *
     * @return bool
     */
    public function delete(string $objectId): bool
    {
        //====================================================================//
        // Stack Trace
        Splash::log()->trace();
        //====================================================================//
        // Delete Customer from Api
        if (null === API::delete('customers/'.$objectId)) {
            return Splash::log()->errTrace(" Unable to Delete Customer (".$objectId.").");
        }
        //====================================================================//
        // PHPUnit => Store Id of Deleted Customers
        // Deleted Customers are Still Visible...
        if (!empty(Splash::input("SPLASH_TRAVIS"))) {
            self::$deletedCustomers[] = $objectId;
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function getObjectIdentifier(): ?string
    {
        if (!isset($this->object->id)) {
            return null;
        }

        return (string) $this->object->id;
    }
}
