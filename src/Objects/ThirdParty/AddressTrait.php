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

namespace Splash\Connectors\Shopify\Objects\ThirdParty;

use ArrayObject;
use Splash\Core\SplashCore      as Splash;

/**
 * Shopify ThirdParty Address Fields
 */
trait AddressTrait
{
    /**
     * @var ArrayObject
     */
    protected $address;

    /**
     * Build Address Fields using FieldFactory
     */
    protected function buildAddressFields()
    {
        $groupName = "Address";

        //====================================================================//
        // Addess
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->Identifier("address1")
            ->Name("Street 1")
            ->Group($groupName)
            ->isReadOnly()
            ->MicroData("http://schema.org/PostalAddress", "streetAddress");

        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->Identifier("address2")
            ->Name("Street 2")
            ->Group($groupName)
            ->isReadOnly()
            ->MicroData("http://schema.org/PostalAddress", "streetAddress2");

        //====================================================================//
        // Zip Code
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->Identifier("zip")
            ->Name("Zip")
            ->Group($groupName)
            ->MicroData("http://schema.org/PostalAddress", "postalCode")
            ->AddOption('maxLength', (string) 18)
            ->isReadOnly();

        //====================================================================//
        // City Name
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->Identifier("city")
            ->Name("Town")
            ->Group($groupName)
            ->isReadOnly()
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
            ->isReadOnly()
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
            ->isReadOnly();
    }

    /**
     * Read requested Field
     *
     * @param string $key       Input List Key
     * @param string $fieldName Field Identifier / Name
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function getAddressFields($key, $fieldName)
    {
        if (!isset($this->address)) {
            $this->address = new ArrayObject($this->object['default_address'], ArrayObject::ARRAY_AS_PROPS);
        }
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
                $this->getSimple($fieldName, "address");

                break;
            default:
                return;
        }

        unset($this->in[$key]);
    }
}
