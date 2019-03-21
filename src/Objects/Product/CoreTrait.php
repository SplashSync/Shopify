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

namespace Splash\Connectors\Shopify\Objects\Product;

use Splash\Core\SplashCore      as Splash;

/**
 * Access to Product Core Fields
 */
trait CoreTrait
{
    /**
     * Build Core Fields using FieldFactory
     */
    private function buildCoreFields()
    {
        //====================================================================//
        // Name without Options
        $this->fieldsFactory()->Create(SPL_T_VARCHAR)
            ->Identifier("title")
            ->Name("Title")
            ->isRequired()
            ->MicroData("http://schema.org/Product", "alternateName");

        //====================================================================//
        // Availability Date
        $this->fieldsFactory()->create(SPL_T_BOOL)
            ->Identifier("published")
            ->Name("Is Published")
            ->MicroData("http://schema.org/Product", "offered")
            ->isListed();

        //====================================================================//
        // Long Description
        $this->fieldsFactory()->Create(SPL_T_VARCHAR)
            ->Identifier("body_html")
            ->Name("Description")
            ->Description("A description of the product. Supports HTML formatting.")
            ->MicroData("http://schema.org/Product", "description");

        //====================================================================//
        // Meta Url
        $this->fieldsFactory()->Create(SPL_T_VARCHAR)
            ->Identifier("handle")
            ->Name("Friendly URL")
            ->Description("A unique human-friendly string for the product. Automatically generated from the product's title. Used by the Liquid templating language to refer to objects.")
            ->addOption("isLowerCase")
            ->MicroData("http://schema.org/Product", "urlRewrite");
    }

    /**
     * Read requested Field
     *
     * @param string $key       Input List Key
     * @param string $fieldName Field Identifier / Name
     */
    private function getCoreFields($key, $fieldName)
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
     */
    private function setCoreFields($fieldName, $fieldData)
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
