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

trait FulfillmentTrait
{
    /**
     * @var string
     */
    private static $fulfillmentListName = "fulfillments";

    /**
     * Build Fields using FieldFactory
     *
     * @return void
     */
    protected function buildFulfillmentFields(): void
    {
        //====================================================================//
        // Check if Logistic Mode is Active
        if (!$this->connector->hasLogisticMode()) {
            return;
        }
        //====================================================================//
        // Tracking Company
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->identifier("status")
            ->name("Status")
            ->inList(self::$fulfillmentListName)
            ->group(ucfirst(self::$fulfillmentListName))
            ->isReadOnly()
        ;
        //====================================================================//
        // Tracking Company
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->identifier("shipment_status")
            ->name("Shipping Status")
            ->inList(self::$fulfillmentListName)
            ->group(ucfirst(self::$fulfillmentListName))
            ->isReadOnly()
        ;
        //====================================================================//
        // Tracking Company
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->identifier("tracking_company")
            ->name("Company")
            ->inList(self::$fulfillmentListName)
            ->group(ucfirst(self::$fulfillmentListName))
            ->isReadOnly()
        ;
        //====================================================================//
        // Tracking Number
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->identifier("tracking_number")
            ->name("Tracking Number")
            ->inList(self::$fulfillmentListName)
            ->group(ucfirst(self::$fulfillmentListName))
            ->isReadOnly()
        ;
        //====================================================================//
        // Tracking Url
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->identifier("tracking_url")
            ->name("Tracking Url")
            ->inList(self::$fulfillmentListName)
            ->group(ucfirst(self::$fulfillmentListName))
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
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function getFulfillmentFields($key, $fieldName): void
    {
        //====================================================================//
        // Check if List field & Init List Array
        $fieldId = self::lists()->initOutput($this->out, self::$fulfillmentListName, $fieldName);
        if (!$fieldId) {
            return;
        }

        //====================================================================//
        // Parse Order Shipping
        //====================================================================//
        if (!is_array($this->object->fulfillments)) {
            unset($this->in[$key]);

            return;
        }
        //====================================================================//
        // Fill List with Data
        foreach ($this->object->fulfillments as $index => $fulfillment) {
            //====================================================================//
            // READ Fields
            switch ($fieldId) {
                case 'status':
                case 'tracking_company':
                case 'tracking_number':
                case 'tracking_url':
                case 'shipment_status':
                    //====================================================================//
                    // Insert Data in List
                    self::lists()->insert(
                        $this->out,
                        self::$fulfillmentListName,
                        $fieldName,
                        $index,
                        isset($fulfillment[$fieldId]) ? $fulfillment[$fieldId] : null
                    );

                    break;
                default:
                    return;
            }
        }

        unset($this->in[$key]);
    }
}
