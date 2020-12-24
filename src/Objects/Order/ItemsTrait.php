<?php

/*
 *  This file is part of SplashSync Project.
 *
 *  Copyright (C) 2015-2020 Splash Sync  <www.splashsync.com>
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace Splash\Connectors\Shopify\Objects\Order;

use Splash\Connectors\Shopify\Objects\Product;

/**
 * Shopify Customer Orders Items Fields
 */
trait ItemsTrait
{
    /**
     * Build Address Fields using FieldFactory
     *
     * @return void
     */
    protected function buildItemsFields(): void
    {
        //====================================================================//
        // Order Line Description
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->Identifier("title")
            ->InList("lines")
            ->Name("Description")
            ->Group("Items")
            ->MicroData("http://schema.org/partOfInvoice", "description")
            ->Association("title@lines", "quantity@lines", "price@lines")
            ->isReadOnly()
        ;

        //====================================================================//
        // Order Line Product Identifier
        $this->fieldsFactory()->create((string) self::objects()->Encode("Product", SPL_T_ID))
            ->Identifier("product_id")
            ->InList("lines")
            ->Name("Product")
            ->Group("Items")
            ->MicroData("http://schema.org/Product", "productID")
            ->Association("title@lines", "quantity@lines", "price@lines")
            ->isReadOnly()
        ;

        //====================================================================//
        // Order Line Quantity
        $this->fieldsFactory()->create(SPL_T_INT)
            ->Identifier("quantity")
            ->InList("lines")
            ->Name("Quantity")
            ->Group("Items")
            ->MicroData("http://schema.org/QuantitativeValue", "value")
            ->Association("title@lines", "quantity@lines", "price@lines")
            ->isReadOnly()
        ;

        //====================================================================//
        // Order Line Discount
        $this->fieldsFactory()->create(SPL_T_DOUBLE)
            ->Identifier("discount")
            ->InList("lines")
            ->Name("Discount %")
            ->Group("Items")
            ->MicroData("http://schema.org/Order", "discount")
            ->Association("title@lines", "quantity@lines", "price@lines")
            ->isReadOnly()
        ;

        //====================================================================//
        // Order Line Unit Price
        $this->fieldsFactory()->create(SPL_T_PRICE)
            ->Identifier("price")
            ->InList("lines")
            ->Name("Unit Price")
            ->Group("Items")
            ->MicroData("http://schema.org/PriceSpecification", "price")
            ->Association("title@lines", "quantity@lines", "price@lines")
            ->isReadOnly()
        ;

        //====================================================================//
        // Order Line Tax Name
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->Identifier("tax_name")
            ->InList("lines")
            ->Name("VAT Tax Code")
            ->Group("Items")
            ->MicroData("http://schema.org/PriceSpecification", "valueAddedTaxName")
            ->Association("title@lines", "quantity@lines", "price@lines")
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
    protected function getItemsFields($key, $fieldName): void
    {
        //====================================================================//
        // Check if List field & Init List Array
        $fieldId = self::lists()->InitOutput($this->out, "lines", $fieldName);
        if (!$fieldId) {
            return;
        }

        //====================================================================//
        // Parse Order Items
        //====================================================================//
        if (is_array($this->object->line_items)) {
            //====================================================================//
            // Fill List with Data
            foreach ($this->object->line_items as $index => $orderLine) {
                //====================================================================//
                // Read Data from Line Item
                $value = $this->getItemField($orderLine, $fieldName);
                //====================================================================//
                // Insert Data in List
                self::lists()->Insert($this->out, "lines", $fieldName, $index, $value);
            }
        }

        //====================================================================//
        // Parse Order Shipping
        //====================================================================//
        if (is_array($this->object->shipping_lines)) {
            //====================================================================//
            // Fill List with Data
            foreach ($this->object->shipping_lines as $index => $orderLine) {
                //====================================================================//
                // Read Data from Line Item
                $value = $this->getShippingField($orderLine, $fieldName);
                //====================================================================//
                // Insert Data in List
                self::lists()->insert(
                    $this->out,
                    "lines",
                    $fieldName,
                    count($this->object->line_items) + $index,
                    $value
                );
            }
        }

        unset($this->in[$key]);
    }

    /**
     * Read requested Field
     *
     * @param array  $line      Line Data Object
     * @param string $fieldName Field Identifier / Name
     *
     * @return null|array|bool|float|int|string
     */
    private function getItemField($line, $fieldName)
    {
        //====================================================================//
        // READ Fields
        switch ($fieldName) {
            //====================================================================//
            // Order Line Description
            case 'title@lines':
                return  $line['title']." - ".$line['variant_title'];
            //====================================================================//
            // Order Line Product Id
            case 'product_id@lines':
                if (empty($line['product_id'])) {
                    return null;
                }

                return self::objects()->encode(
                    "Product",
                    Product::getObjectId($line['product_id'], $line['variant_id'])
                );
            //====================================================================//
            // Order Line Quantity
            case 'quantity@lines':
                return (int) $line['quantity'];
            //====================================================================//
            // Order Line Price
            case 'price@lines':
                return $this->getItemPrice($line);
            //====================================================================//
            // Order Line Discount Percentile
            case "discount@lines":
                return  (float) $this->getItemDiscount($line);
            //====================================================================//
            // Order Line Tax Name
            case 'tax_name@lines':
                return  $this->getItemVatName($line);
            default:
                return null;
        }
    }

    /**
     * Read requested Field
     *
     * @param array  $line      Line Data Object
     * @param string $fieldName Field Identifier / Name
     *
     * @return null|array|float|float|int|string
     */
    private function getShippingField($line, $fieldName)
    {
        //====================================================================//
        // READ Fields
        switch ($fieldName) {
            //====================================================================//
            // Order Line Description
            case 'title@lines':
                return  $line['title'];
            //====================================================================//
            // Order Line Product Id
            case 'product_id@lines':
                return null;
            //====================================================================//
            // Order Line Quantity
            case 'quantity@lines':
                return 1;
            //====================================================================//
            // Order Line Price
            case 'price@lines':
                return $this->getItemPrice($line);
            //====================================================================//
            // Order Line Discount Percentile
            case "discount@lines":
                return  (float) $this->getItemDiscount($line);
            //====================================================================//
            // Order Line Tax Name
            case 'tax_name@lines':
                return  $this->getItemVatName($line);
            default:
                return null;
        }
    }

    /**
     * Compute Item Total Tax Rate
     *
     * @param array $line
     *
     * @return float|int
     */
    private function getItemVatRate($line)
    {
        //====================================================================//
        // Line not Taxable
        if (isset($line['taxable']) && !$line['taxable']) {
            return 0;
        }
        //====================================================================//
        // No Taxes Lines
        if (empty($line['tax_lines'])) {
            return 0;
        }
        //====================================================================//
        // Sum Applied VAT Rates
        $vatRate = 0;
        foreach ($line['tax_lines'] as $tax) {
            $vatRate += (100 * $tax['rate']);
        }

        return $vatRate;
    }

    /**
     * Compute Item Price Array
     *
     * @param array $line
     *
     * @return array|string
     */
    private function getItemPrice($line)
    {
        //====================================================================//
        // Read Price Tax Mode
        $taxIncluded = (bool) $this->object->taxes_included;
        //====================================================================//
        // Read Price
        $priceHT = $taxIncluded ? null : (float) $line['price'];
        $priceTTC = $taxIncluded ? (float) $line['price'] : null;
        $tax = (float) $this->getItemVatRate($line);
        //====================================================================//
        // Build Price Array
        return self::prices()->Encode(
            $priceHT,
            $tax,
            $priceTTC,
            $this->connector->getDefaultCurrency()
        );
    }

    /**
     * Get Name of First Applied Tax Rate
     *
     * @param array $line
     *
     * @return null|string
     */
    private function getItemVatName($line)
    {
        //====================================================================//
        // Line not Taxable
        if (isset($line['taxable']) && !$line['taxable']) {
            return null;
        }
        //====================================================================//
        // No Taxes Lines
        if (empty($line['tax_lines'])) {
            return null;
        }
        //====================================================================//
        // Extract First Rate Title
        foreach ($line['tax_lines'] as $tax) {
            return $tax['title'];
        }

        return null;
    }

    /**
     * Get Discount Percetile Applied
     *
     * @param array $line
     *
     * @return float|int
     */
    private function getItemDiscount($line)
    {
        //====================================================================//
        // Line has no Discounts
        if (!isset($line['discount_amounts']) || empty($line['discount_amounts'])) {
            return 0;
        }
        //====================================================================//
        // Sum Discounts Ammounts
        $amount = 0;
        foreach ($line['discount_amounts'] as $discount) {
            $amount += $discount['amount'];
        }
        //====================================================================//
        // If Quantity is defined => Devide
        if (isset($line['quantity'])) {
            $amount = $amount / $line['quantity'];
        }

        return 100 * ($amount / $line['price']);
    }
}
