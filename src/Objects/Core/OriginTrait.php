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

namespace Splash\Connectors\Shopify\Objects\Core;

/**
 * Access to Shopify Shop Metadata
 */
trait OriginTrait
{
    /**
     * Build Fields using FieldFactory
     */
    protected function buildOriginFields(): void
    {
        //====================================================================//
        // Prestashop Shop ID
        $this->fieldsFactory()->create(SPL_T_INT)
            ->identifier("id_shop")
            ->name("Shop ID")
            ->group("Meta")
            ->microData("http://schema.org/Author", "identifier")
            ->isReadOnly()
        ;
        //====================================================================//
        // Prestashop Shop Name
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->identifier("shop_name")
            ->name("Shop Name")
            ->group("Meta")
            ->microData("http://schema.org/Author", "name")
            ->isReadOnly()
        ;
        //====================================================================//
        // Prestashop Shop Url
        $this->fieldsFactory()->create(SPL_T_URL)
            ->identifier("shop_url")
            ->name("Shop Url")
            ->group("Meta")
            ->microData("http://schema.org/Author", "url")
            ->isReadOnly()
        ;
    }

    /**
     * Read requested Field
     */
    protected function getMultiShopFields(string $key, string $fieldName): void
    {
        //====================================================================//
        // READ Fields
        switch ($fieldName) {
            case 'id_shop':
                $value = $this->connector
                    ->getParameter("id", null, "ShopInformations")
                ;
                $this->out[$fieldName] = is_numeric($value) ? (int) $value : null;

                break;
            case 'shop_name':
                $value = $this->connector
                    ->getParameter("name", null, "ShopInformations")
                ;
                $this->out[$fieldName] = is_scalar($value) ? (string) $value : null;

                break;
            case 'shop_url':
                $value = $this->connector
                    ->getParameter("domain", null, "ShopInformations")
                ;
                $this->out[$fieldName] = is_scalar($value) ? (string) $value : null;

                break;
            default:
                return;
        }

        unset($this->in[$key]);
    }
}
