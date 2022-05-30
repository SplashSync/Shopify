<?php

/*
 *  This file is part of SplashSync Project.
 *
 *  Copyright (C) 2015-2021 Splash Sync  <www.splashsync.com>
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
use Splash\Connectors\Shopify\Helpers\HappyCommerceHelper;
use Splash\Connectors\Shopify\Helpers\MondialRelayHelper;
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
     * @return ArrayObject|null
     */
    public function load(string $objectId): ?ArrayObject
    {
        //====================================================================//
        // Stack Trace
        Splash::log()->trace();
        //====================================================================//
        // Get Customer Order from Api
        $object = API::get("orders", $objectId, array(), "order");
        //====================================================================//
        // Fetch Object from Shopify
        if (null === $object) {
            return Splash::log()->errNull("Unable to load Order/Invoice (".$objectId.").");
        }
        //====================================================================//
        // Override Order Infos with Apps Informations
        if ($this->connector->hasHappyColissimoPlugin()) {
            HappyCommerceHelper::apply($object, $this->getMetadataFromApi(
                "orders/".$objectId."/metafields",
                HappyCommerceHelper::NAMESPACE,
                HappyCommerceHelper::KEY,
            ));
        }
        if ($this->connector->hasMondialRelayPlugin()) {
            MondialRelayHelper::apply($object);
        }

        return new ArrayObject($object, ArrayObject::ARRAY_AS_PROPS);
    }

    /**
     * Create Request Object
     *
     * @return null
     */
    public function create()
    {
        //====================================================================//
        // Stack Trace
        Splash::log()->trace();

        return Splash::log()->errNull("Splash API Cannot Create Shopify Orders!");
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
        // Order Information Update is Forbidden
        if ($this->isToUpdate('fulfillments')) {
            if (method_exists($this, 'updateFulfillment')) {
                $this->updateFulfillment();
            }
        }

        //====================================================================//
        // Order Information Update is Forbidden
        if ($needed) {
            return Splash::log()->errNull("Splash API Cannot Update Shopify Orders!");
        }

        return $this->getObjectIdentifier();
    }

    /**
     * {@inheritdoc}
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function delete(string $objectId): bool
    {
        //====================================================================//
        // Stack Trace
        Splash::log()->trace();

        return Splash::log()->err("Splash API Cannot Delete Shopify Orders!");
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
