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

namespace Splash\Connectors\Shopify\Objects\Address;

use Splash\Core\SplashCore      as Splash;

/**
 * @abstract    Shopify ThirdParty Address Main Fields
 */
trait MainTrait
{
    /**
     *  @abstract     Build Fields using FieldFactory
     */
    protected function buildMAinFields()
    {
        $groupName = "Address";

        //====================================================================//
        // Addess
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->Identifier("address1")
            ->Name("Street 1")
            ->Group($groupName)
            ->MicroData("http://schema.org/PostalAddress", "streetAddress");

        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->Identifier("address2")
            ->Name("Street 2")
            ->Group($groupName)
            ->MicroData("http://schema.org/PostalAddress", "streetAddress2");

        //====================================================================//
        // Zip Code
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->Identifier("zip")
            ->Name("Zip")
            ->Group($groupName)
            ->MicroData("http://schema.org/PostalAddress", "postalCode")
            ->AddOption('maxLength', (string) 18);

        //====================================================================//
        // City Name
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->Identifier("city")
            ->Name("Town")
            ->Group($groupName)
            ->MicroData("http://schema.org/PostalAddress", "addressLocality");

        //====================================================================//
        // Country Name
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->Identifier("country_name")
            ->Name("Country")
            ->Group($groupName)
            ->isReadOnly();

        //====================================================================//
        // Country ISO Code
        $this->fieldsFactory()->create(SPL_T_COUNTRY)
            ->Identifier("country_code")
            ->Name("Country Code")
            ->Group($groupName)
            ->MicroData("http://schema.org/PostalAddress", "addressCountry");

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
     */
    protected function setMainFields($fieldName, $fieldData)
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
