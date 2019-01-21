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

/**
 * Shopify ThirdParty Core Fields (Required)
 */
trait CoreTrait
{
    /**
     * Build Core Fields using FieldFactory
     */
    protected function buildCoreFields()
    {
        //====================================================================//
        // Email
        $this->fieldsFactory()->create(SPL_T_EMAIL)
            ->Identifier("email")
            ->Name("Email")
            ->MicroData("http://schema.org/ContactPoint", "email")
            ->isRequired()
            ->AddOption("emailDomain", "exemple")
            ->AddOption("emailExtension", "com")
            ->isListed();
        
        //====================================================================//
        // Firstname
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->Identifier("first_name")
            ->Name("Firstname")
            ->MicroData("http://schema.org/Person", "familyName")
            ->isLogged();
        
        //====================================================================//
        // Lastname
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->Identifier("last_name")
            ->Name("Lastname")
            ->MicroData("http://schema.org/Person", "givenName")
            ->isLogged();
        
        //====================================================================//
        // Company
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->Identifier("company")
            ->Name("Company Name")
            ->MicroData("http://schema.org/Organization", "legalName")
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
    protected function getCoreFields($key, $fieldName)
    {
        switch ($fieldName) {
            case 'email':
            case 'first_name':
            case 'last_name':
                $this->getSimple($fieldName);

                break;
            case 'company':
                if (isset($this->object['default_address']['company']) && !empty($this->object['default_address']['company'])) {
                    $this->out[$fieldName]  =   $this->object['default_address']['company'];
                }
                $this->out[$fieldName]  =  "Shopify (" . $this->object->id . ")";

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
    protected function setCoreFields($fieldName, $fieldData)
    {
        switch ($fieldName) {
            case 'email':
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
