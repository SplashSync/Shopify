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

namespace Splash\Connectors\Shopify\Objects\Address;

/**
 * Shopify ThirdParty Address Main Fields
 */
trait MainTrait
{
    /**
     * Build Fields using FieldFactory
     *
     * @return void
     */
    protected function buildMainFields(): void
    {
        $groupName = "Address";

        //====================================================================//
        // Addess
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->identifier("address1")
            ->name("Street 1")
            ->group($groupName)
            ->microData("http://schema.org/PostalAddress", "streetAddress")
            ->isIndexed()
        ;
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->identifier("address2")
            ->name("Street 2")
            ->group($groupName)
            ->microData("http://schema.org/PostalAddress", "streetAddress2")
        ;
        //====================================================================//
        // Zip Code
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->identifier("zip")
            ->name("Zip")
            ->group($groupName)
            ->microData("http://schema.org/PostalAddress", "postalCode")
            ->isIndexed()
            ->addOption('maxLength', (string) 18)
        ;
        //====================================================================//
        // City Name
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->identifier("city")
            ->name("Town")
            ->group($groupName)
            ->microData("http://schema.org/PostalAddress", "addressLocality")
        ;
        //====================================================================//
        // Country Name
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->identifier("country_name")
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
    }

    /**
     * Read requested Field
     *
     * @param string $key       Input List Key
     * @param string $fieldName Field Identifier / Name
     *
     * @return void
     */
    protected function getMainFields($key, $fieldName): void
    {
        //====================================================================//
        // READ Fields
        switch ($fieldName) {
            //====================================================================//
            // Direct Readings
            case 'address1':
            case 'address2':
            case 'zip':
            case 'city':
            case 'province':
            case 'province_code':
            case 'country_name':
            case 'country_code':
                $this->getSimple($fieldName);

                break;
            default:
                return;
        }

        unset($this->in[$key]);
    }

    /**
     * Write Given Fields
     *
     * @param string $fieldName Field Identifier / Name
     * @param mixed  $fieldData Field Data
     *
     * @return void
     */
    protected function setMainFields($fieldName, $fieldData): void
    {
        //====================================================================//
        // WRITE Field
        switch ($fieldName) {
            case 'address1':
            case 'address2':
            case 'zip':
            case 'city':
            case 'province':
            case 'province_code':
            case 'country_name':
            case 'country_code':
                $this->setSimple($fieldName, $fieldData);

                break;
            default:
                return;
        }
        unset($this->in[$fieldName]);
    }
}
