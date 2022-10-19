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

/**
 * Shopify ThirdParty Core Fields (Required)
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
        // Email
        $this->fieldsFactory()->create(SPL_T_EMAIL)
            ->identifier("email")
            ->name("Email")
            ->microData("http://schema.org/ContactPoint", "email")
            ->isRequired()
            ->isPrimary()
            ->addOption("emailDomain", "exemple")
            ->addOption("emailExtension", "com")
            ->isListed()
        ;
        //====================================================================//
        // Firstname
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->identifier("first_name")
            ->name("Firstname")
            ->microData("http://schema.org/Person", "familyName")
            ->isLogged()
        ;
        //====================================================================//
        // Lastname
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->identifier("last_name")
            ->name("Lastname")
            ->microData("http://schema.org/Person", "givenName")
            ->isLogged()
        ;
        //====================================================================//
        // Company
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->identifier("company")
            ->name("Company Name")
            ->microData("http://schema.org/Organization", "legalName")
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
    protected function getCoreFields(string $key, string $fieldName): void
    {
        switch ($fieldName) {
            case 'email':
            case 'first_name':
            case 'last_name':
                $this->getSimple($fieldName);

                break;
            case 'company':
                if (isset($this->object['default_address']) && is_array($this->object['default_address'])) {
                    /** @var null|string $company */
                    $company = $this->object['default_address']['company'] ?? null;
                } else {
                    $company = null;
                }
                $this->out[$fieldName] = $company ?: "Shopify (".$this->object->id.")";

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
    protected function setCoreFields(string $fieldName, $fieldData): void
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
