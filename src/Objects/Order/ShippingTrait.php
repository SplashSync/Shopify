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

trait ShippingTrait
{
    /**
     * Build Fields using FieldFactory
     *
     * @return void
     */
    protected function buildShippingFields(): void
    {
        $groupName = "Main Shipping";

        //====================================================================//
        // Main Shipping Title
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->Identifier("carrier_title")
            ->Name("Name")
            ->MicroData("http://schema.org/ParcelDelivery", "name")
            ->Group($groupName)
            ->isReadOnly();

        //====================================================================//
        // Main Shipping Code
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->Identifier("carrier_code")
            ->Name("Code")
            ->MicroData("http://schema.org/ParcelDelivery", "provider")
            ->Group($groupName)
            ->isReadOnly();

        //====================================================================//
        // Main Shipping Source
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->Identifier("carrier_source")
            ->Name("Source")
            ->Group($groupName)
            ->isReadOnly();

        //====================================================================//
        // Main Shipping Source
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->Identifier("carrier_identifier")
            ->Name("Source")
            ->MicroData("http://schema.org/ParcelDelivery", "identifier")
            ->Group($groupName)
            ->isReadOnly();
    }

    /**
     * Read requested Field
     *
     * @param string $key       Input List Key
     * @param string $fieldName Field Identifier / Name
     *
     * @return void
     */
    protected function getShippingFields($key, $fieldName): void
    {
        //====================================================================//
        // READ Fields
        switch ($fieldName) {
            case 'carrier_title':
                $this->out[$fieldName] = $this->getMainShippingField("title");

                break;
            case 'carrier_code':
                $this->out[$fieldName] = $this->getMainShippingField("code");

                break;
            case 'carrier_source':
                $this->out[$fieldName] = $this->getMainShippingField("source");

                break;
            case 'carrier_identifier':
                $this->out[$fieldName] = $this->getMainShippingField("carrier_identifier");

                break;
            default:
                return;
        }

        unset($this->in[$key]);
    }

    /**
     * Get Order First Shipping Line Field
     *
     * @return null|string
     */
    private function getMainShippingField(string $fieldName): ?string
    {
        if (!isset($this->object->shipping_lines[0][$fieldName])) {
            return null;
        }

        return (string) $this->object->shipping_lines[0][$fieldName];
    }
}
