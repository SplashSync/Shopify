<?php

/*
 *  This file is part of SplashSync Project.
 *
 *  Copyright (C) 2015-2020 Splash Sync  <www.splashsync.com>
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace Splash\Connectors\Shopify\Objects\Order;

/**
 * Shopify Customer Order Status Field
 */
trait StatusTrait
{
    /**
     * Build Customer Order Status Fields using FieldFactory
     *
     * @return void
     */
    protected function buildStatusFields(): void
    {
        //====================================================================//
        // Order Current Status
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->Identifier("status")
            ->Name("Order Status")
            ->MicroData("http://schema.org/Order", "orderStatus")
            ->AddChoice("OrderCanceled", "Canceled")
            ->AddChoice("OrderDraft", "Draft")
            ->AddChoice("OrderInTransit", "Shipped")
            ->AddChoice("OrderProcessing", "Pending")
            ->AddChoice("OrderDelivered", "Delivered")
                ;
    }

    /**
     * Read requested Field
     *
     * @param string $key       Input List Key
     * @param string $fieldName Field Identifier / Name
     *
     * @return void
     *
     * @SuppressWarnings(PHPMD.ElseExpression)
     */
    protected function getStatusFields($key, $fieldName): void
    {
        if ('status' != $fieldName) {
            return;
        }

        if ("restocked" == $this->object->fulfillment_status) {
            $this->out[$fieldName] = "OrderCanceled";
        } elseif (!$this->object->confirmed) {
            $this->out[$fieldName] = "OrderDraft";
        } elseif (empty($this->object->fulfillment_status)) {
            $this->out[$fieldName] = "OrderProcessing";
        } elseif ("partial" == $this->object->fulfillment_status) {
            $this->out[$fieldName] = "OrderInTransit";
        } elseif ("fulfilled" == $this->object->fulfillment_status) {
            $this->out[$fieldName] = "OrderDelivered";
        } else {
            $this->out[$fieldName] = "Unknown";
        }

        unset($this->in[$key]);
    }
}
