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

namespace Splash\Connectors\Shopify\Objects\Product\Variants;

use Splash\Models\Objects\PricesTrait as SplashPricesTrait;

/**
 * Access to Product Variants Main Fields
 */
trait MainTrait
{
    use SplashPricesTrait;

    /**
     * Build Fields using FieldFactory
     */
    protected function buildMainFields()
    {
        //====================================================================//
        // PRODUCT DESCRIPTIONS
        //====================================================================//

        //====================================================================//
        // Reference | SKU
        $this->fieldsFactory()->Create(SPL_T_VARCHAR)
            ->Identifier("sku")
            ->Name("Product SKU")
            ->Description("A unique identifier for the product variant in the shop.")
            ->isListed()
            ->MicroData("http://schema.org/Product", "model");

        //====================================================================//
        // Name with Options
        $this->fieldsFactory()->Create(SPL_T_VARCHAR)
            ->Identifier("variant_title")
            ->Name("Title with Options")
            ->isListed()
            ->MicroData("http://schema.org/Product", "name")
            ->isReadOnly();

        //====================================================================//
        // PRICES INFORMATIONS
        //====================================================================//

        //====================================================================//
        // Product Selling Price
        $this->fieldsFactory()->Create(SPL_T_PRICE)
            ->Identifier("price")
            ->Name("Price (tax excl.)")
//                ->isListed()
            ->MicroData("http://schema.org/Product", "price");

        //====================================================================//
        // PRODUCT SPECIFICATIONS
        //====================================================================//

        //====================================================================//
        // Weight
        $this->fieldsFactory()->Create(SPL_T_DOUBLE)
            ->Identifier("grams")
            ->Name("Product weight (Kg)")
            ->MicroData("http://schema.org/Product", "weight");

        //====================================================================//
        // PRODUCT BARCODES
        //====================================================================//

        //====================================================================//
        // UPC
        $this->fieldsFactory()->Create(SPL_T_INT)
            ->Identifier("barcode")
            ->Name("UPC | ISBN BarCode")
            ->MicroData("http://schema.org/Product", "gtin12");
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
            // PRODUCT DESCRIPTIONS
            //====================================================================//
            case 'sku':
                $this->getSimple($fieldName, "variant");

                break;
            case 'variant_title':
                $this->out[$fieldName] = ($this->object->title." - ".$this->variant->title);

                break;
            //====================================================================//
            // PRICES INFORMATIONS
            //====================================================================//
            case 'price':
                //====================================================================//
                // Read Price
                $priceHT = (float) $this->variant->price;
                $tax = (float) ($this->variant->taxable ? $this->getLocalVatRate() : 0);
                //====================================================================//
                // Build Price Array
                $this->out[$fieldName] = self::prices()->Encode(
                    $priceHT,
                    $tax,
                    null,
                    $this->connector->getDefaultCurrency()
                );

                break;
            //====================================================================//
            // PRODUCT SPECIFICATIONS
            //====================================================================//
            case 'grams':
                $this->getVariantWheight($fieldName);

                break;
            //====================================================================//
            // PRODUCT BARCODES
            //====================================================================//
            case 'barcode':
                $this->getSimple($fieldName, "variant");

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
            //====================================================================//
            // PRODUCT DESCRIPTIONS
            //====================================================================//
            case 'sku':
                $this->setSimple($fieldName, $fieldData, "variant");

                break;
            //====================================================================//
            // PRICES INFORMATIONS
            //====================================================================//
            case 'price':
                $this->setSimple($fieldName, self::prices()->taxExcluded($fieldData), "variant");
                $this->setSimple('taxable', !empty(self::prices()->taxPercent($fieldData)), "variant");

                break;
            //====================================================================//
            // PRODUCT SPECIFICATIONS
            //====================================================================//
            case 'grams':
                $this->setVariantWheight($fieldData);

                break;
            //====================================================================//
            // PRODUCT BARCODES
            //====================================================================//
            case 'barcode':
                $this->setSimple($fieldName, $fieldData, "variant");

                break;
            default:
                return;
        }
        unset($this->in[$fieldName]);
    }

    /**
     * Get Local Vat Rate for a Product
     *
     * @return float
     */
    private function getLocalVatRate() : float
    {
        if (!$this->variant->taxable) {
            return 0;
        }
        //====================================================================//
        // Get Store Informations
        $storeCountry = $this->getParameter("country", null, "ShopInformations");
        $countries = $this->getParameter("Countries");
        //====================================================================//
        // Safety Check
        if (!is_iterable($countries) || is_null($storeCountry)) {
            return 0;
        }
        //====================================================================//
        // Search in Countries
        foreach ($countries as $country) {
            if ($country['code'] == $storeCountry) {
                return 100 * $country['tax'];
            }
        }

        return 0;
    }
}
