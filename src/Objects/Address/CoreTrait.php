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

use Splash\Core\SplashCore  as Splash;

/**
 * Shopify ThirdParty Address Core Fields (Required)
 */
trait CoreTrait
{
    /**
     * Build Core Fields using FieldFactory
     *
     * @return void
     */
    protected function buildCoreFields(): void
    {
        //====================================================================//
        // Customer
        $this->fieldsFactory()->create((string) self::objects()->encode("ThirdParty", SPL_T_ID))
            ->identifier("customer_id")
            ->name("Customer")
            ->microData("http://schema.org/Organization", "ID")
            ->isRequired()
            ->isNotTested()
        ;
        //====================================================================//
        // Firstname
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->identifier("first_name")
            ->name("Firstname")
            ->microData("http://schema.org/Person", "familyName")
            ->isRequired()
            ->isIndexed()
            ->isLogged()
            ->isListed()
        ;
        //====================================================================//
        // Lastname
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->identifier("last_name")
            ->name("Lastname")
            ->microData("http://schema.org/Person", "givenName")
            ->isRequired()
            ->isIndexed()
            ->isLogged()
            ->isListed()
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
    protected function getCoreFields($key, $fieldName): void
    {
        switch ($fieldName) {
            //====================================================================//
            // Address ThirdParty Id
            case 'customer_id':
                $this->out[$fieldName] = self::objects()->Encode("ThirdParty", $this->object->customer_id);

                break;
            case 'first_name':
            case 'last_name':
                $this->getSimple($fieldName);

                break;
            default:
                return;
        }
        //====================================================================//
        // Clear Key Flag
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
    protected function setCoreFields($fieldName, $fieldData): void
    {
        switch ($fieldName) {
            //====================================================================//
            // Address ThirdParty Id
            case 'customer_id':
                if ($this->object->customer_id != $fieldData) {
                    Splash::log()->war("You cannot update existing Shopify Address Customer Link. Change Skipped");
                }

                break;
            case 'first_name':
            case 'last_name':
                $this->setSimple($fieldName, $fieldData);

                break;
            default:
                return;
        }
        unset($this->in[$fieldName]);
    }
}
