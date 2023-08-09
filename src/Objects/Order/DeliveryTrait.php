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
            ->identifier("company")
            ->name("Company")
            ->microData("http://schema.org/Organization", "legalName")
            ->group($groupName)
            ->isReadOnly()
        ;
        //====================================================================//
        // Contact Full Name
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->identifier("fullname")
            ->name("Contact Name")
            ->microData("http://schema.org/PostalAddress", "alternateName")
            ->group($groupName)
            ->isReadOnly()
        ;
        //====================================================================//
        // Address
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->identifier("address1")
            ->name("Street 1")
            ->group($groupName)
            ->microData("http://schema.org/PostalAddress", "streetAddress")
            ->isReadOnly()
        ;
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->identifier("address2")
            ->name("Street 2")
            ->group($groupName)
            ->microData("http://schema.org/PostalAddress", "postOfficeBoxNumber")
            ->isReadOnly()
        ;
        //====================================================================//
        // Zip Code
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->identifier("zip")
            ->name("Zip")
            ->group($groupName)
            ->microData("http://schema.org/PostalAddress", "postalCode")
            ->isReadOnly()
        ;
        //====================================================================//
        // City Name
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->identifier("city")
            ->name("Town")
            ->group($groupName)
            ->microData("http://schema.org/PostalAddress", "addressLocality")
            ->isReadOnly()
        ;
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
            ->identifier("country")
            ->name("Country")
            ->group($groupName)
            ->isReadOnly()
        ;
        //====================================================================//
        // Country ISO Code
        $this->fieldsFactory()->create(SPL_T_COUNTRY)
            ->identifier("country_code")
            ->name("Country Code")
            ->group($groupName)
            ->microData("http://schema.org/PostalAddress", "addressCountry")
            ->isReadOnly()
        ;
        //====================================================================//
        // State Name
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->identifier("province")
            ->group($groupName)
            ->name("State")
            ->isReadOnly()
        ;
        //====================================================================//
        // State code
        $this->fieldsFactory()->create(SPL_T_STATE)
            ->identifier("province_code")
            ->name("State Code")
            ->group($groupName)
            ->microData("http://schema.org/PostalAddress", "addressRegion")
            ->isReadOnly()
            ->isNotTested()
        ;
        //====================================================================//
        // Phone
        $this->fieldsFactory()->create(SPL_T_PHONE)
            ->identifier("phone")
            ->group($groupName)
            ->name("Phone")
            ->microData("http://schema.org/PostalAddress", "telephone")
            ->isReadOnly()
        ;
        //====================================================================//
        // Other
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->identifier("note")
            ->name("Note")
            ->description("Other: Remarks, Relay Point Code, more...")
            ->microData("http://schema.org/PostalAddress", "description")
            ->group($groupName)
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
