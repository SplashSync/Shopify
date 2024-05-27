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

/**
 * Compute Order Generic Totals Prices
 */
trait TotalsTrait
{
    /**
     * Build Fields using FieldFactory
     *
     * @return void
     */
    private function buildTotalsFields(): void
    {
        //====================================================================//
        // PRICES INFORMATIONS
        //====================================================================//

        //====================================================================//
        // Order Total Price
        $this->fieldsFactory()->create(SPL_T_PRICE)
            ->identifier("price_total")
            ->name("Order Total")
            ->microData("http://schema.org/Invoice", "total")
            ->group("Totals")
            ->isReadOnly()
        ;
        //====================================================================//
        // Order Total Shipping
        $this->fieldsFactory()->create(SPL_T_PRICE)
            ->identifier("price_shipping")
            ->name("Order Shipping")
            ->microData("http://schema.org/Invoice", "totalShipping")
            ->group("Totals")
            ->isReadOnly()
        ;
        //====================================================================//
        // Order Total Shipping
        $this->fieldsFactory()->create(SPL_T_PRICE)
            ->identifier("price_discount")
            ->name("Order Discounts")
            ->microData("http://schema.org/Invoice", "totalDiscount")
            ->group("Totals")
            ->isReadOnly()
        ;
    }

    /**
     * Read requested Field
     */
    private function getTotalsFields(string $key, string $fieldName): void
    {
        //====================================================================//
        // READ Fields
        switch ($fieldName) {
            case 'price_total':
                //====================================================================//
                // Build Price Array
                $this->out[$fieldName] = self::prices()->encode(
                    null,
                    self::toVatPercents(
                        $this->object->total_price - $this->object->total_tax,
                        $this->object->total_price,
                    ),
                    (double)    $this->object->total_price,
                    $this->connector->getDefaultCurrency(),
                );

                break;
            case 'price_shipping':
                //====================================================================//
                // Build Price Array
                $this->out[$fieldName] = $this->getShippingTotal();

                break;
            case 'price_discount':
                //====================================================================//
                // Build Price Array
                $this->out[$fieldName] = self::prices()->encode(
                    (double)    $this->object->total_discounts,
                    0.0,
                    null,
                    $this->connector->getDefaultCurrency(),
                );

                break;
            default:
                return;
        }

        unset($this->in[$key]);
    }

    /**
     * Compute Vat Percentile from Both Price Values
     *
     * @param float $priceTaxExcl
     * @param float $priceTaxIncl
     *
     * @return float
     */
    private static function toVatPercents(float $priceTaxExcl, float $priceTaxIncl): float
    {
        return (($priceTaxExcl > 0) && ($priceTaxIncl > 0) && ($priceTaxExcl <= $priceTaxIncl))
            ? 100 * ($priceTaxIncl - $priceTaxExcl) / $priceTaxExcl
            : 0.0
        ;
    }

    /**
     * Compute Shipping Total Price
     */
    private function getShippingTotal(): ?array
    {
        $total = $tax = 0.0;
        if (is_array($this->object->shipping_lines)) {
            //====================================================================//
            // Fill List with Data
            foreach ($this->object->shipping_lines as $shippingLine) {
                //====================================================================//
                // Read Data from Line Item
                $price = $this->getItemPrice($shippingLine);
                $total += $price['ht'] ?? 0.0;
                $tax += $price['tax'] ?? 0.0;
            }
        }

        return self::prices()->encode(
            (double)    $total,
            self::toVatPercents(
                $total - $tax,
                $total,
            ),
            null,
            $this->connector->getDefaultCurrency(),
        );
    }
}
