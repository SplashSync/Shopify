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

namespace Splash\Connectors\Shopify\Objects\Address;

use ArrayObject;
use Splash\Connectors\Shopify\Helpers\OrderAddressHelper;
use Splash\Connectors\Shopify\Models\ShopifyHelper as API;
use Splash\Core\SplashCore      as Splash;

/**
 * Shopify Contacts Address CRUD Functions
 */
trait CRUDTrait
{
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
        // Get Address from Order Api
        $object = $this->loadFromOrder($objectId);
        //====================================================================//
        // Get Customer Address from Api
        $object = $object ?? API::get(self::getUri($objectId), null, array(), "customer_address");
        //====================================================================//
        // Fetch Object
        if (null === $object) {
            return Splash::log()->errNull("Unable to load Customer Address (".$objectId.").");
        }
        //====================================================================//
        // Unset Full Name to Avoid Data Duplicates
        unset($object['name']);

        return new ArrayObject($object, ArrayObject::ARRAY_AS_PROPS);
    }

    /**
     * Load Request Object from Order Object
     *
     * @param string $objectId Object ID
     *
     * @return null|array
     */
    public function loadFromOrder(string $objectId): ?array
    {
        $address = $order = null;
        //====================================================================//
        // Get Shipping Address from Order Api
        if ($shippingId = OrderAddressHelper::toOrderShippingId($objectId)) {
            $order = API::get("orders", $shippingId, array(), "order");

            $address = $order['shipping_address'] ?? null;
        }
        //====================================================================//
        // Get Billing Address from Order Api
        if ($billingId = OrderAddressHelper::toOrderBillingId($objectId)) {
            $order = API::get("orders", $billingId, array(), "order");

            $address = $order['billing_address'] ?? null;
        }
        //====================================================================//
        // Complete Address from Order Api
        if ($address && $order) {
            $address["id"] = $objectId;
            $address["customer_id"] = $order['customer']['id'] ?? null;
        }

        return $address;
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
        if (empty($this->in["customer_id"]) || !is_scalar($this->in["customer_id"])) {
            Splash::log()->err("ErrLocalFieldMissing", __CLASS__, __FUNCTION__, "customer_id");

            return null;
        }
        //====================================================================//
        // Explode Storage Id
        $customerId = self::objects()->id((string) $this->in["customer_id"]);
        //====================================================================//
        // Create New Customer Address
        $this->object = new ArrayObject(array( "id" => null ), ArrayObject::ARRAY_AS_PROPS);
        $this->setSimple("customer_id", $customerId);
        $this->setSimple("first_name", $this->in["first_name"]);
        $this->setSimple("last_name", $this->in["last_name"]);
        //====================================================================//
        // Create Customer from Api
        $newAddress = API::post(
            'customers/'.$customerId."/addresses",
            array("address" => $this->object),
            "customer_address"
        );
        if (null === $newAddress) {
            return Splash::log()->errNull("Unable to Create Customer Address.");
        }

        return new ArrayObject($newAddress, ArrayObject::ARRAY_AS_PROPS);
    }

    /**
     * Update Request Object
     *
     * @param bool $needed Is This Update Needed
     *
     * @return null|string Object ID
     */
    public function update(bool $needed): ?string
    {
        //====================================================================//
        // Stack Trace
        Splash::log()->trace();
        //====================================================================//
        // Safety Check
        if (OrderAddressHelper::isOrderAddress($this->object->id)) {
            Splash::log()->err("This is an Order Address, thus you cannot update it!");

            return $this->object->id;
        }
        //====================================================================//
        // Encode Object Id
        $objectId = $this->getObjectId($this->object->customer_id, $this->object->id);
        //====================================================================//
        // Check if Needed
        if (!$needed) {
            return $objectId;
        }
        //====================================================================//
        // Update Customer Address from Api
        if (null === API::put(self::getUri($objectId), array("customer_address" => $this->object))) {
            return Splash::log()->errNull("Unable to Update Customer Address (".$objectId.").");
        }

        return $objectId;
    }

    /**
     * {@inheritdoc}
     */
    public function delete(string $objectId): bool
    {
        //====================================================================//
        // Stack Trace
        Splash::log()->trace();
        //====================================================================//
        // Safety Check
        if (OrderAddressHelper::isOrderAddress($this->object->id)) {
            return Splash::log()->err("This is an Order Address, thus you cannot delete it!");
        }
        //====================================================================//
        // Delete Customer from Api
        if (null === API::delete(self::getUri($objectId))) {
            Splash::log()->errTrace("Unable to Delete Customer Address (".$objectId.").");
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function getObjectIdentifier(): ?string
    {
        if (!isset($this->object->customer_id) || !isset($this->object->id)) {
            return null;
        }

        //====================================================================//
        // Encode Object Id
        return $this->getObjectId($this->object->customer_id, $this->object->id);
    }

    /**
     * Get Object CRUD Base Uri
     *
     * @param null|string $objectId Splash Encoded ObjectId
     *
     * @return string
     */
    private static function getUri(string $objectId = null) : string
    {
        $baseUri = 'customers/'.self::getCustomerId((string) $objectId);
        $baseUri .= "/addresses/".self::getAddressId((string) $objectId);

        return $baseUri;
    }
}
