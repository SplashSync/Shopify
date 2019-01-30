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

use Splash\Models\Objects\PricesTrait as SplashPricesTrait;

/**
 * Access to Product Main Fields
 */
trait MainTrait
{
    use SplashPricesTrait;
        
    /**
     * Build Fields using FieldFactory
     */
    private function buildMainFields()
    {
        //====================================================================//
        // Reference | SKU
        $this->fieldsFactory()->Create(SPL_T_VARCHAR)
            ->Identifier("sku")
            ->Name("Product SKU")
            ->Description("A unique identifier for the product variant in the shop.")
            ->isListed()
            ->MicroData("http://schema.org/Product", "model");
        
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
     *
     * @return void
     */
    private function getMainFields($key, $fieldName)
    {
        //====================================================================//
        // READ Fields
        switch ($fieldName) {
            case 'sku':
                $this->getSimple($fieldName, "variant");

                break;
            //====================================================================//
            // PRICES INFORMATIONS
            //====================================================================//
            case 'price':
                //====================================================================//
                // Read Price
                $priceHT    = (float) $this->variant->price;
                $tax        = (float) ($this->variant->taxable ? $this->getLocalVatRate() : 0);
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
     *
     * @return void
     */
    private function setMainFields($fieldName, $fieldData)
    {
        //====================================================================//
        // WRITE Field
        switch ($fieldName) {
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
//                $this->setSimple($fieldName, ($fieldData * 1E3), "variant");
//                unset($this->Variant->weight);
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
        $countries  = $this->getParameter("Countries");
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
