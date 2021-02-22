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

trait DeliveryTrait
{
    /**
     * Build Fields using FieldFactory
     *
     * @return void
     */
    protected function buildDeliveryPart1Fields(): void
    {
        $groupName = "Delivery";
        //====================================================================//
        // Company
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->Identifier("company")
            ->Name("Company")
            ->MicroData("http://schema.org/Organization", "legalName")
            ->Group($groupName)
            ->isReadOnly();
        //====================================================================//
        // Contact Full Name
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->Identifier("fullname")
            ->Name("Contact Name")
            ->MicroData("http://schema.org/PostalAddress", "alternateName")
            ->Group($groupName)
            ->isReadOnly();
        //====================================================================//
        // Address
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->Identifier("address1")
            ->Name("Street 1")
            ->Group($groupName)
            ->MicroData("http://schema.org/PostalAddress", "streetAddress")
            ->isReadOnly();
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->Identifier("address2")
            ->Name("Street 2")
            ->Group($groupName)
            ->MicroData("http://schema.org/PostalAddress", "postOfficeBoxNumber")
            ->isReadOnly();
        //====================================================================//
        // Zip Code
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->Identifier("zip")
            ->Name("Zip")
            ->Group($groupName)
            ->MicroData("http://schema.org/PostalAddress", "postalCode")
            ->isReadOnly();
        //====================================================================//
        // City Name
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->Identifier("city")
            ->Name("Town")
            ->Group($groupName)
            ->MicroData("http://schema.org/PostalAddress", "addressLocality")
            ->isReadOnly();
    }

    /**
     * Build Fields using FieldFactory
     *
     * @return void
     */
    protected function buildDeliveryPart2Fields(): void
    {
        $groupName = "Delivery";
        //====================================================================//
        // Country Name
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->Identifier("country")
            ->Name("Country")
            ->Group($groupName)
            ->isReadOnly();
        //====================================================================//
        // Country ISO Code
        $this->fieldsFactory()->create(SPL_T_COUNTRY)
            ->Identifier("country_code")
            ->Name("Country Code")
            ->Group($groupName)
            ->MicroData("http://schema.org/PostalAddress", "addressCountry")
            ->isReadOnly();
        //====================================================================//
        // State Name
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->Identifier("province")
            ->Group($groupName)
            ->Name("State")
            ->isReadOnly();
        //====================================================================//
        // State code
        $this->fieldsFactory()->create(SPL_T_STATE)
            ->Identifier("province_code")
            ->Name("State Code")
            ->Group($groupName)
            ->MicroData("http://schema.org/PostalAddress", "addressRegion")
            ->isReadOnly()
            ->isNotTested();
        //====================================================================//
        // Phone
        $this->fieldsFactory()->create(SPL_T_PHONE)
            ->Identifier("phone")
            ->Group($groupName)
            ->Name("Phone")
            ->MicroData("http://schema.org/PostalAddress", "telephone")
            ->isReadOnly();
        //====================================================================//
        // Other
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->identifier("note")
            ->name("Note")
            ->description("Other: Remarks, Relay Point Code, more...")
            ->MicroData("http://schema.org/PostalAddress", "description")
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
    protected function getDeliverySimpleFields($key, $fieldName): void
    {
        $simpleFields = array(
            'company', 'address1', 'address2', 'zip', 'city',
            'province', 'province_code', 'country', 'country_code', 'phone'
        );
        if (!in_array($fieldName, $simpleFields, true)) {
            return;
        }
        //====================================================================//
        // Direct Readings
        $this->out[$fieldName] = isset($this->object->shipping_address)
            ? $this->object->shipping_address[$fieldName]
            : null
        ;
        unset($this->in[$key]);
    }

    /**
     * Read requested Field
     *
     * @param string $key       Input List Key
     * @param string $fieldName Field Identifier / Name
     *
     * @return void
     */
    protected function getDeliveryFields($key, $fieldName): void
    {
        //====================================================================//
        // READ Fields
        switch ($fieldName) {
            //====================================================================//
            // Delivery Contact Full Name
            case 'fullname':
                $this->out[$fieldName] = "";
                if (isset($this->object->shipping_address)) {
                    $this->out[$fieldName] = $this->object->shipping_address["first_name"];
                    $this->out[$fieldName] .= " ";
                    $this->out[$fieldName] .= $this->object->shipping_address["last_name"];
                }

                break;
            case 'note':
                $this->getSimple($fieldName);

                break;
            default:
                return;
        }

        unset($this->in[$key]);
    }
}
