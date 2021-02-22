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
            ->Identifier("title")
            ->Name("Name")
            ->InList(self::$shippingListName)
            ->Group(ucfirst(self::$shippingListName))
            ->MicroData("http://schema.org/ParcelDelivery", "name")
            ->isReadOnly()
        ;

        //====================================================================//
        // Shipping Code
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->Identifier("code")
            ->Name("Code")
            ->InList(self::$shippingListName)
            ->Group(ucfirst(self::$shippingListName))
            ->MicroData("http://schema.org/ParcelDelivery", "provider")
            ->isReadOnly()
        ;

        //====================================================================//
        // Shipping Source
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->Identifier("source")
            ->Name("Source")
            ->InList(self::$shippingListName)
            ->Group(ucfirst(self::$shippingListName))
            ->isReadOnly()
        ;

        //====================================================================//
        // Shipping Price
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->Identifier("price")
            ->Name("Price")
            ->InList(self::$shippingListName)
            ->Group(ucfirst(self::$shippingListName))
            ->isReadOnly()
        ;

        //====================================================================//
        // Shipping Carrier ID
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->Identifier("carrier_identifier")
            ->Name("Carrier ID")
            ->InList(self::$shippingListName)
            ->Group(ucfirst(self::$shippingListName))
            ->MicroData("http://schema.org/ParcelDelivery", "identifier")
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
    protected function getShippingFields($key, $fieldName): void
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
