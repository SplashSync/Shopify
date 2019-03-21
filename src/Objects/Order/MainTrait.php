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

use DateTime;

/**
 * Shopify Orders Main Fields
 */
trait MainTrait
{
    private $updateBilled;

    /**
     * Build Address Fields using FieldFactory
     */
    protected function buildMainFields()
    {
        //====================================================================//
        // Delivry Estimated Date
        $this->fieldsFactory()->create(SPL_T_DATE)
            ->Identifier("closed_at")
            ->Name("Closed Date")
            ->MicroData("http://schema.org/ParcelDelivery", "expectedArrivalUntil");

        //====================================================================//
        // PRICES INFORMATIONS
        //====================================================================//

        //====================================================================//
        // Order Total Price HT
        $this->fieldsFactory()->create(SPL_T_DOUBLE)
            ->Identifier("total_price")
            ->Name("Total Price")
            ->MicroData("http://schema.org/Invoice", "totalPaymentDue")
            ->isReadOnly();

        //====================================================================//
        // Order Total Price TTC
        $this->fieldsFactory()->create(SPL_T_DOUBLE)
            ->Identifier("total_tax_excl")
            ->Name("Total Tax Excl.")
            ->MicroData("http://schema.org/Invoice", "totalPaymentDueTaxIncluded")
            ->isReadOnly();

        //====================================================================//
        // ORDER STATUS FLAGS
        //====================================================================//

        //====================================================================//
        // Is Draft
        $this->fieldsFactory()->create(SPL_T_BOOL)
            ->Identifier("isdraft")
            ->Group("Meta")
            ->Name("Order is Draft")
            ->MicroData("http://schema.org/OrderStatus", "OrderDraft")
            ->Association("isdraft", "iscanceled", "isvalidated", "isclosed")
            ->isReadOnly();

        //====================================================================//
        // Is Canceled
        $this->fieldsFactory()->create(SPL_T_BOOL)
            ->Identifier("iscanceled")
            ->Group("Meta")
            ->Name("Order is Canceled")
            ->MicroData("http://schema.org/OrderStatus", "OrderCancelled")
            ->Association("isdraft", "iscanceled", "isvalidated", "isclosed")
            ->isReadOnly();

        //====================================================================//
        // Is Validated
        $this->fieldsFactory()->create(SPL_T_BOOL)
            ->Identifier("isvalidated")
            ->Group("Meta")
            ->Name("Order is Validated")
            ->MicroData("http://schema.org/OrderStatus", "OrderProcessing")
            ->Association("isdraft", "iscanceled", "isvalidated", "isclosed")
            ->isReadOnly();

        //====================================================================//
        // Is Closed
        $this->fieldsFactory()->create(SPL_T_BOOL)
            ->Identifier("isclosed")
            ->Group("Meta")
            ->Name("Order is Closed")
            ->MicroData("http://schema.org/OrderStatus", "OrderDelivered")
            ->Association("isdraft", "iscanceled", "isvalidated", "isclosed")
            ->isReadOnly();

        //====================================================================//
        // Is Paid
        $this->fieldsFactory()->create(SPL_T_BOOL)
            ->Identifier("financial_status")
            ->Name("Order is Paid")
            ->MicroData("http://schema.org/OrderStatus", "OrderPaid")
            ->isReadOnly();
    }

    /**
     * Read requested Field
     *
     * @param string $key       Input List Key
     * @param string $fieldName Field Identifier / Name
     */
    protected function getMainFields($key, $fieldName)
    {
        //====================================================================//
        // READ Fields
        switch ($fieldName) {
            //====================================================================//
            // Order Delivery Date
            case 'closed_at':
                if ($this->object->closed_at) {
                    $date = new DateTime($this->object->closed_at);
                    $this->out[$fieldName] = $date->format(SPL_T_DATECAST);

                    break;
                }
                $this->out[$fieldName] = null;

                break;
            //====================================================================//
            // PRICE INFORMATIONS
            //====================================================================//
            case 'total_price':
                $this->getSimple($fieldName);

                break;
            case 'total_tax_excl':
                $this->out[$fieldName] = (float) ($this->object->total_price - $this->object->total_tax);

                break;
            default:
                return;
        }

        unset($this->in[$key]);
    }

    /**
     * Read requested Field
     *
     * @param string $key       Input List Key
     * @param string $fieldName Field Identifier / Name
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function getStatesFields($key, $fieldName)
    {
        //====================================================================//
        // READ Fields
        switch ($fieldName) {
            //====================================================================//
            // ORDER STATUS
            //====================================================================//

            case 'isdraft':
                // Darft Orders are not Visible from API
                $this->out[$fieldName] = !$this->object->confirmed;

                break;
            case 'iscanceled':
                $this->out[$fieldName] = ("restocked" == $this->object->fulfillment_status)   ?   true:false;

                break;
            case 'isvalidated':
                $this->out[$fieldName] = ($this->object->confirmed)   ?   true:false;

                break;
            case 'isclosed':
                $this->out[$fieldName] = ("fulfilled" == $this->object->fulfillment_status)   ?   true:false;

                break;
            case 'financial_status':
                $this->out[$fieldName] = ("paid" == $this->object->financial_status)    ?   true:false;

                break;
            default:
                return;
        }

        unset($this->in[$key]);
    }
}
