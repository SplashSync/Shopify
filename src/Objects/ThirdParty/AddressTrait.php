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

namespace Splash\Connectors\Shopify\Objects\ThirdParty;

use ArrayObject;

/**
 * Shopify ThirdParty Address Fields
 */
trait AddressTrait
{
    /**
     * @var null|ArrayObject
     */
    protected ?ArrayObject $address;

    /**
     * Build Address Fields using FieldFactory
     *
     * @return void
     */
    protected function buildAddressFields(): void
    {
        $groupName = "Address";

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
            ->microData("http://schema.org/PostalAddress", "streetAddress2")
            ->isReadOnly()
        ;
        //====================================================================//
        // Zip Code
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->identifier("zip")
            ->name("Zip")
            ->group($groupName)
            ->microData("http://schema.org/PostalAddress", "postalCode")
            ->addOption('maxLength', "18")
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
    protected function getAddressFields(string $key, string $fieldName): void
    {
        if (!isset($this->address)) {
            $this->address = new ArrayObject(
                (isset($this->object['default_address']) && is_array($this->object['default_address']))
                    ? $this->object['default_address']
                    : array(),
                ArrayObject::ARRAY_AS_PROPS
            );
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
