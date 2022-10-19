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

trait ShippingTrait
{
    /**
     * @var string
     */
    private static $shippingListName = "shipping";

    /**
     * Build Fields using FieldFactory
     *
     * @return void
     */
    protected function buildShippingFields(): void
    {
        //====================================================================//
        // Check if Logistic Mode is Active
        if (!$this->connector->hasLogisticMode()) {
            return;
        }

        //====================================================================//
        // Shipping Title
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->identifier("title")
            ->name("Name")
            ->inList(self::$shippingListName)
            ->group(ucfirst(self::$shippingListName))
            ->microData("http://schema.org/ParcelDelivery", "name")
            ->isReadOnly()
        ;
        //====================================================================//
        // Shipping Code
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->identifier("code")
            ->name("Code")
            ->inList(self::$shippingListName)
            ->group(ucfirst(self::$shippingListName))
            ->microData("http://schema.org/ParcelDelivery", "provider")
            ->isReadOnly()
        ;
        //====================================================================//
        // Shipping Source
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->identifier("source")
            ->name("Source")
            ->inList(self::$shippingListName)
            ->group(ucfirst(self::$shippingListName))
            ->isReadOnly()
        ;
        //====================================================================//
        // Shipping Price
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->identifier("price")
            ->name("Price")
            ->inList(self::$shippingListName)
            ->group(ucfirst(self::$shippingListName))
            ->isReadOnly()
        ;
        //====================================================================//
        // Shipping Carrier ID
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->identifier("carrier_identifier")
            ->name("Carrier ID")
            ->inList(self::$shippingListName)
            ->group(ucfirst(self::$shippingListName))
            ->microData("http://schema.org/ParcelDelivery", "identifier")
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
    protected function getShippingFields(string $key, string $fieldName): void
    {
        //====================================================================//
        // Check if List field & Init List Array
        $fieldId = self::lists()->InitOutput($this->out, self::$shippingListName, $fieldName);
        if (!$fieldId) {
            return;
        }

        //====================================================================//
        // Parse Order Shipping
        //====================================================================//
        if (!is_array($this->object->shipping_lines)) {
            unset($this->in[$key]);

            return;
        }
        //====================================================================//
        // Fill List with Data
        foreach ($this->object->shipping_lines as $index => $shippingLine) {
            //====================================================================//
            // READ Fields
            switch ($fieldId) {
                case 'title':
                case 'code':
                case 'source':
                case 'price':
                case 'carrier_identifier':
                    //====================================================================//
                    // Insert Data in List
                    self::lists()->insert(
                        $this->out,
                        self::$shippingListName,
                        $fieldName,
                        $index,
                        isset($shippingLine[$fieldId]) ? $shippingLine[$fieldId] : null
                    );

                    break;
                default:
                    return;
            }
        }

        unset($this->in[$key]);
    }

    /**
     * Get Order Main Shipping Code
     *
     * @return null|string
     */
    private function getMainShippingCode(): ?string
    {
        if (!isset($this->object->shipping_lines[0]["code"])) {
            return null;
        }
        if (empty($this->object->shipping_lines[0]["code"])) {
            return null;
        }

        return (string) $this->object->shipping_lines[0]["code"];
    }
}
