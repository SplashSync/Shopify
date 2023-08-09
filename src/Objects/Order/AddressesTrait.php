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

namespace Splash\Connectors\Shopify\Objects\Order;

use Splash\Connectors\Shopify\Helpers\OrderAddressHelper;

/**
 * Access to Shopify Order Adresses
 */
trait AddressesTrait
{
    /**
     * Build Fields using FieldFactory
     *
     * @return void
     */
    protected function buildAddressesFields(): void
    {
        //====================================================================//
        // Delivery Address
        $this->fieldsFactory()->create((string) self::objects()->encode("Address", SPL_T_ID))
            ->identifier("shipping_address")
            ->name("Shipping Address")
            ->isReadOnly()
        ;
        //====================================================================//
        // Invoicing Address
        $this->fieldsFactory()->create((string) self::objects()->encode("Address", SPL_T_ID))
            ->identifier("billing_address")
            ->name("Billing Address")
            ->isReadOnly()
        ;
    }

    /**
     * Read requested Field
     *
     * @param string $key       Input List Key
     * @param string $fieldName Field Identifier / Name
     *
     * @return void
     */
    protected function getAddressesFields(string $key, string $fieldName): void
    {
        //====================================================================//
        // READ Fields
        switch ($fieldName) {
            //====================================================================//
            // Delivery Address
            case 'shipping_address':
                $orderId = $this->getObjectIdentifier();
                $this->out[$fieldName] = $orderId ? OrderAddressHelper::toShippingId($orderId) : null;

                break;
            //====================================================================//
            // Billing Address
            case 'billing_address':
                $orderId = $this->getObjectIdentifier();
                $this->out[$fieldName] = $orderId ? OrderAddressHelper::toBillingId($orderId) : null;

                break;
            default:
                return;
        }

        unset($this->in[$key]);
    }
}
