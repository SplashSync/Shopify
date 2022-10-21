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
            ->identifier("title")
            ->inList("lines")
            ->name("[L] Description")
            ->group("Items")
            ->microData("http://schema.org/partOfInvoice", "description")
            ->association("title@lines", "quantity@lines", "price@lines")
            ->isReadOnly()
        ;
        //====================================================================//
        // Order Line Product Identifier
        $this->fieldsFactory()->create((string) self::objects()->encode("Product", SPL_T_ID))
            ->identifier("product_id")
            ->inList("lines")
            ->name("[L] Product")
            ->group("Items")
            ->microData("http://schema.org/Product", "productID")
            ->association("title@lines", "quantity@lines", "price@lines")
            ->isReadOnly()
        ;
        //====================================================================//
        // Order Line Product SKU
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->identifier("sku")
            ->inList("lines")
            ->name("[L] SKU")
            ->group("Items")
            ->microData("http://schema.org/Product", "sku")
            ->association("title@lines", "quantity@lines", "price@lines")
            ->isReadOnly()
        ;
        //====================================================================//
        // Order Line Quantity
        $this->fieldsFactory()->create(SPL_T_INT)
            ->identifier("quantity")
            ->inList("lines")
            ->name("[L] Quantity")
            ->description("[L] Absolute Ordered Quantity")
            ->group("Items")
            ->microData(
                "http://schema.org/QuantitativeValue",
                $this->connector->hasLogisticMode() ? "toShip" : "value"
            )
            ->association("title@lines", "quantity@lines", "price@lines")
            ->isReadOnly()
        ;
        //====================================================================//
        // Order Line Quantity
        $this->fieldsFactory()->create(SPL_T_INT)
            ->identifier("quantity_with_refunds")
            ->inList("lines")
            ->name("[L] Qty with refunds")
            ->description("[L] Ordered Quantity minus Refunded Quantities")
            ->group("Items")
            ->microData(
                "http://schema.org/QuantitativeValue",
                $this->connector->hasLogisticMode() ? "value" : "toShip"
            )
            ->association("title@lines", "quantity@lines", "price@lines")
            ->isReadOnly()
        ;
        //====================================================================//
        // Order Line Discount
        $this->fieldsFactory()->create(SPL_T_DOUBLE)
            ->identifier("discount")
            ->inList("lines")
            ->name("Discount %")
            ->group("Items")
            ->microData("http://schema.org/Order", "discount")
            ->association("title@lines", "quantity@lines", "price@lines")
            ->isReadOnly()
        ;
        //====================================================================//
        // Order Line Unit Price
        $this->fieldsFactory()->create(SPL_T_PRICE)
            ->identifier("price")
            ->inList("lines")
            ->name("Unit Price")
            ->group("Items")
            ->microData("http://schema.org/PriceSpecification", "price")
            ->association("title@lines", "quantity@lines", "price@lines")
            ->isReadOnly()
        ;
        //====================================================================//
        // Order Line Tax Name
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->identifier("tax_name")
            ->inList("lines")
            ->name("VAT Tax Code")
            ->group("Items")
            ->microData("http://schema.org/PriceSpecification", "valueAddedTaxName")
            ->association("title@lines", "quantity@lines", "price@lines")
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
    protected function getItemsFields(string $key, string $fieldName): void
    {
        //====================================================================//
        // Check if List field & Init List Array
        $fieldId = self::lists()->initOutput($this->out, "lines", $fieldName);
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
                self::lists()->insert($this->out, "lines", $fieldName, $index, $value);
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
                    (string) (count($this->object->line_items) + $index),
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
     * @return null|array|float|int|string
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    private function getItemField(array $line, string $fieldName)
    {
        //====================================================================//
        // READ Fields
        switch ($fieldName) {
            //====================================================================//
            // Order Line Description
            case 'title@lines':
                return  $line['title']." - ".$line['variant_title'];
            case 'sku@lines':
                return  $line['sku'];
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
            case 'quantity_with_refunds@lines':
                return (int) ($line['quantity'] - $this->getItemRefundedQty($line));
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
     * @return null|array|float|int|string
     */
    private function getShippingField(array $line, string $fieldName)
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
            case 'sku@lines':
                return null;
            //====================================================================//
            // Order Line Quantity
            case 'quantity@lines':
            case 'quantity_with_refunds@lines':
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
    private function getItemVatRate(array $line)
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
    private function getItemVatName(array $line): ?string
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
     * Get Discount Percentile Applied
     *
     * @param array $line
     *
     * @return float|int
     */
    private function getItemDiscount(array $line)
    {
        //====================================================================//
        // Line has no Discounts
        if (empty($line['discount_allocations'])) {
            return 0;
        }
        //====================================================================//
        // Sum Discounts Amounts
        $amount = 0;
        foreach ($line['discount_allocations'] as $discount) {
            $amount += $discount['amount'];
        }
        //====================================================================//
        // If Quantity is defined => Divide
        if (isset($line['quantity'])) {
            $amount = $amount / $line['quantity'];
        }

        return 100 * ($amount / $line['price']);
    }

    /**
     * Get Item Refunded Quantity
     *
     * @param array $line
     *
     * @return int
     */
    private function getItemRefundedQty(array $line): int
    {
        //====================================================================//
        // Walk on Refunds
        foreach ($this->object->refunds ?? array() as $refund) {
            //====================================================================//
            // Walk on Refunds Items
            foreach ($refund["refund_line_items"] ?? array() as $refundLine) {
                if ($refundLine['line_item_id'] == $line['id']) {
                    return (int) $refundLine['quantity'];
                }
            }
        }

        return 0;
    }
}
