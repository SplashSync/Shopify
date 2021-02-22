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

/**
 * Shopify Orders Status Flags Fields
 */
trait StatusFlagsTrait
{
    /**
     * @var bool
     */
    private $updateBilled;

    /**
     * Build Address Fields using FieldFactory
     *
     * @return void
     */
    protected function buildStatusFlagsFields(): void
    {
        //====================================================================//
        // Is Draft
        $this->fieldsFactory()->create(SPL_T_BOOL)
            ->identifier("isdraft")
            ->group("Meta")
            ->name("is Draft")
            ->microData("http://schema.org/OrderStatus", "OrderDraft")
            ->association("isdraft", "iscanceled", "isvalidated", "isclosed")
            ->isReadOnly();

        //====================================================================//
        // Is Canceled
        $this->fieldsFactory()->create(SPL_T_BOOL)
            ->identifier("iscanceled")
            ->group("Meta")
            ->name("is Canceled")
            ->microData("http://schema.org/OrderStatus", "OrderCancelled")
            ->association("isdraft", "iscanceled", "isvalidated", "isclosed")
            ->isReadOnly();

        //====================================================================//
        // Is Validated
        $this->fieldsFactory()->create(SPL_T_BOOL)
            ->identifier("isvalidated")
            ->group("Meta")
            ->name("is Validated")
            ->microData("http://schema.org/OrderStatus", "OrderValidated")
            ->association("isdraft", "iscanceled", "isvalidated", "isclosed")
            ->isReadOnly();

        //====================================================================//
        // Is Processing
        $this->fieldsFactory()->create(SPL_T_BOOL)
            ->identifier("isProcessing")
            ->group("Meta")
            ->name("is Processing")
            ->microData("http://schema.org/OrderStatus", "OrderProcessing")
            ->isReadOnly();

        //====================================================================//
        // Is Closed
        $this->fieldsFactory()->create(SPL_T_BOOL)
            ->identifier("isclosed")
            ->group("Meta")
            ->name("is Closed")
            ->microData("http://schema.org/OrderStatus", "OrderDelivered")
            ->association("isdraft", "iscanceled", "isvalidated", "isclosed")
            ->isReadOnly();

        //====================================================================//
        // Is Paid
        $this->fieldsFactory()->create(SPL_T_BOOL)
            ->identifier("financial_status")
            ->group("Meta")
            ->name("is Paid")
            ->microData("http://schema.org/OrderStatus", "OrderPaid")
            ->isReadOnly();
    }

    /**
     * Read requested Field
     *
     * @param string $key       Input List Key
     * @param string $fieldName Field Identifier / Name
     *
     * @return void
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function getStatusFlagsFields($key, $fieldName): void
    {
        //====================================================================//
        // READ Fields
        switch ($fieldName) {
            //====================================================================//
            // ORDER STATUS
            //====================================================================//

            case 'isdraft':
                // Draft Orders are not Visible from API
                $this->out[$fieldName] = !$this->object->confirmed;

                break;
            case 'iscanceled':
                $this->out[$fieldName] = !empty($this->object->cancelled_at);

                break;
            case 'isvalidated':
                $this->out[$fieldName] = ($this->object->confirmed)   ?   true:false;

                break;
            case 'isProcessing':
                $this->out[$fieldName] = $this->object->confirmed
                    && empty($this->object->cancelled_at)
                    && ("paid" == $this->object->financial_status)
                    && empty($this->object->fulfillment_status)
                ;

                break;
            case 'isclosed':
                $this->out[$fieldName] = ("delivered" == $this->getLogisticalField('shipment_status'));

                break;
            case 'financial_status':
                $this->out[$fieldName] = ("paid" == $this->object->financial_status);

                break;
            default:
                return;
        }

        unset($this->in[$key]);
    }
}
