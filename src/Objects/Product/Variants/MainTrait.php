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
     *
     * @return void
     */
    protected function buildMainFields(): void
    {
        //====================================================================//
        // Read Price Tax Mode
        $taxIncluded = (bool) $this->getParameter("taxes_included", null, "ShopInformations");

        //====================================================================//
        // PRODUCT DESCRIPTIONS
        //====================================================================//

        //====================================================================//
        // Reference | SKU
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->identifier("sku")
            ->name("Product SKU")
            ->description("A unique identifier for the product variant in the shop.")
            ->isListed()
            ->microData("http://schema.org/Product", "model")
            ->isIndexed()
        ;
        //====================================================================//
        // Name with Options
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->identifier("variant_title")
            ->name("Title with Options")
            ->microData("http://schema.org/Product", "name")
            ->isListed()
            ->isIndexed()
            ->isReadOnly()
        ;

        //====================================================================//
        // PRICES INFORMATIONS
        //====================================================================//

        //====================================================================//
        // Product Selling Price
        $this->fieldsFactory()->create(SPL_T_PRICE)
            ->identifier("price")
            ->name("Price ".($taxIncluded ? "(tax incl.)" : "(tax excl.)"))
            ->microData("http://schema.org/Product", "price")
        ;

        //====================================================================//
        // PRODUCT SPECIFICATIONS
        //====================================================================//

        //====================================================================//
        // Weight
        $this->fieldsFactory()->create(SPL_T_DOUBLE)
            ->identifier("grams")
            ->name("Product weight (Kg)")
            ->microData("http://schema.org/Product", "weight")
        ;

        //====================================================================//
        // PRODUCT BARCODES
        //====================================================================//

        //====================================================================//
        // UPC
        $this->fieldsFactory()->Create(SPL_T_INT)
            ->identifier("barcode")
            ->name("UPC | ISBN BarCode")
            ->microData("http://schema.org/Product", "gtin12")
            ->isIndexed()
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
    protected function getMainFields(string $key, string $fieldName): void
    {
        //====================================================================//
        // READ Fields
        switch ($fieldName) {
            //====================================================================//
            // PRODUCT DESCRIPTIONS
            //====================================================================//
            case 'sku':
            case 'barcode':
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
                // Read Price Tax Mode
                $taxIncluded = (bool) $this->getParameter("taxes_included", null, "ShopInformations");
                //====================================================================//
                // Read Price
                $priceHT = $taxIncluded ? null : (float) $this->variant->price;
                $priceTTC = $taxIncluded ? (float) $this->variant->price : null;
                $tax = (float) ($this->variant->taxable ? $this->getLocalVatRate() : 0);
                //====================================================================//
                // Build Price Array
                $this->out[$fieldName] = self::prices()->encode(
                    $priceHT,
                    $tax,
                    $priceTTC,
                    $this->connector->getDefaultCurrency()
                );

                break;
                //====================================================================//
                // PRODUCT SPECIFICATIONS
                //====================================================================//
            case 'grams':
                $this->getVariantWeight($fieldName);

                break;
            default:
                return;
        }

        unset($this->in[$key]);
    }

    /**
     * Write Given Fields
     *
     * @param string      $fieldName Field Identifier / Name
     * @param null|scalar $fieldData Field Data
     *
     * @return void
     */
    protected function setMainFields(string $fieldName, float|bool|int|string|null $fieldData): void
    {
        //====================================================================//
        // WRITE Field
        switch ($fieldName) {
            //====================================================================//
            // PRODUCT DESCRIPTIONS
            //====================================================================//
            case 'sku':
            case 'barcode':
                $this->setSimple($fieldName, $fieldData, "variant");

                break;
                //====================================================================//
                // PRODUCT SPECIFICATIONS
                //====================================================================//
            case 'grams':
                $this->setVariantWeight((float) $fieldData);

                break;
            default:
                return;
        }
        unset($this->in[$fieldName]);
    }

    /**
     * Write Given Fields
     *
     * @param string     $fieldName Field Identifier / Name
     * @param null|array $fieldData Field Data
     *
     * @return void
     */
    protected function setMainPriceFields(string $fieldName, ?array $fieldData): void
    {
        //====================================================================//
        // WRITE Field
        switch ($fieldName) {
            //====================================================================//
            // PRICES INFORMATIONS
            //====================================================================//
            case 'price':
                $fieldData = $fieldData ?? array();
                //====================================================================//
                // Read Price Tax Mode
                $taxIncluded = (bool) $this->getParameter("taxes_included", null, "ShopInformations");
                //====================================================================//
                // Compute Price
                $price = $taxIncluded
                    ? self::prices()->taxIncluded($fieldData)
                    : self::prices()->taxExcluded($fieldData);
                $this->setSimple($fieldName, $price, "variant");
                $this->setSimple('taxable', !empty(self::prices()->taxPercent($fieldData)), "variant");

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
