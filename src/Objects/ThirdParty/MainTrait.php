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

namespace Splash\Connectors\Shopify\Objects\ThirdParty;

/**
 * Shopify ThirdParty Main Fields (Required)
 */
trait MainTrait
{
    /**
     * Build Core Fields using FieldFactory
     *
     * @return void
     */
    protected function buildMainFields(): void
    {
        //====================================================================//
        // Phone
        $this->fieldsFactory()->create(SPL_T_PHONE)
            ->Identifier("phone")
            ->Name("Phone")
            ->MicroData("http://schema.org/PostalAddress", "telephone")
            ->isLogged()
            ->isListed()
        ;
        //====================================================================//
        // Note
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->Identifier("note")
            ->Name("Note")
            ->MicroData("http://schema.org/Organization", "description")
        ;
        //====================================================================//
        // Active
        $this->fieldsFactory()->create(SPL_T_BOOL)
            ->Identifier("state")
            ->Name("Status")
            ->Group("Meta")
            ->MicroData("http://schema.org/Organization", "active")
            ->isListed()
            ->isReadOnly()
        ;
        //====================================================================//
        // isVAT
        $this->fieldsFactory()->create(SPL_T_BOOL)
            ->Identifier("tax_exempt")
            ->Name("Uses VAT")
            ->Group("Meta")
            ->MicroData("http://schema.org/Organization", "UseVAT")
        ;
        //====================================================================//
        // Is Opt In
        $this->fieldsFactory()->create(SPL_T_BOOL)
            ->Identifier("accepts_marketing")
            ->Name("Accepts Marketing")
            ->Group("Meta")
            ->MicroData("http://schema.org/Organization", "newsletter")
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
        switch ($fieldName) {
            case 'phone':
            case 'note':
                $this->getSimple($fieldName);

                break;
            case 'accepts_marketing':
                $this->getSimpleBool($fieldName);

                break;
            case 'tax_exempt':
                $this->out[$fieldName] = !$this->object->{$fieldName};

                break;
            case 'state':
                $this->out[$fieldName] = ("enabled" == $this->object->{$fieldName});

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
    protected function setMainFields($fieldName, $fieldData): void
    {
        switch ($fieldName) {
            case 'phone':
            case 'accepts_marketing':
            case 'note':
                $this->setSimple($fieldName, $fieldData);

                break;
            case 'tax_exempt':
                $this->setSimple($fieldName, !$fieldData);

                break;
            default:
                return;
        }
        unset($this->in[$fieldName]);
    }
}
