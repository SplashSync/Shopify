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

namespace Splash\Connectors\Shopify\Objects\Product;

/**
 * Access to Product Core Fields
 */
trait CoreTrait
{
    /**
     * Build Core Fields using FieldFactory
     *
     * @return void
     */
    private function buildCoreFields(): void
    {
        //====================================================================//
        // Name without Options
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->identifier("title")
            ->name("Title")
            ->isRequired()
            ->microData("http://schema.org/Product", "alternateName")
            ->isIndexed()
        ;
        //====================================================================//
        // Availability Date
        $this->fieldsFactory()->create(SPL_T_BOOL)
            ->identifier("published")
            ->name("Is Published")
            ->microData("http://schema.org/Product", "offered")
            ->isListed()
            ->isNotTested()
        ;
        //====================================================================//
        // Long Description
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->identifier("body_html")
            ->name("Description")
            ->description("A description of the product. Supports HTML formatting.")
            ->microData("http://schema.org/Product", "description")
        ;
        //====================================================================//
        // Meta Url
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->identifier("handle")
            ->name("Friendly URL")
            ->description(
                "A unique human-friendly string for the product. Automatically generated from the product's title."
            )
            ->addOption("isLowerCase")
            ->microData("http://schema.org/Product", "urlRewrite")
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
    private function getCoreFields(string $key, string $fieldName): void
    {
        //====================================================================//
        // READ Fields
        switch ($fieldName) {
            case 'title':
            case 'body_html':
            case 'handle':
                $this->getSimple($fieldName);

                break;
            case 'published':
                $this->getSimpleBool($fieldName);

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
    private function setCoreFields(string $fieldName, $fieldData): void
    {
        //====================================================================//
        // WRITE Field
        switch ($fieldName) {
            case 'title':
            case 'body_html':
            case 'handle':
                $this->setSimple($fieldName, $fieldData);

                break;
            case 'published':
                $this->setSimple($fieldName, (bool) $fieldData);

                break;
            default:
                return;
        }
        unset($this->in[$fieldName]);
    }
}
