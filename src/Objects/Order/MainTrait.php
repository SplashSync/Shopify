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

use DateTime;

/**
 * Shopify Orders Main Fields
 */
trait MainTrait
{
    /**
     * @var bool
     */
    private $updateBilled;

    /**
     * Build Address Fields using FieldFactory
     *
     * @return void
     */
    protected function buildMainFields(): void
    {
        //====================================================================//
        // Delivery Estimated Date
        $this->fieldsFactory()->create(SPL_T_DATE)
            ->Identifier("closed_at")
            ->Name("Closed Date")
            ->MicroData("http://schema.org/ParcelDelivery", "expectedArrivalUntil")
            ->isReadOnly()
        ;

        //====================================================================//
        // PRICES INFORMATIONS
        //====================================================================//

        //====================================================================//
        // Order Total Price HT
        $this->fieldsFactory()->create(SPL_T_DOUBLE)
            ->Identifier("total_price")
            ->Name("Total Price")
            ->MicroData("http://schema.org/Invoice", "totalPaymentDue")
            ->isReadOnly();

        //====================================================================//
        // Order Total Price TTC
        $this->fieldsFactory()->create(SPL_T_DOUBLE)
            ->Identifier("total_tax_excl")
            ->Name("Total Tax Excl.")
            ->MicroData("http://schema.org/Invoice", "totalPaymentDueTaxIncluded")
            ->isReadOnly();
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
        //====================================================================//
        // READ Fields
        switch ($fieldName) {
            //====================================================================//
            // Order Delivery Date
            case 'closed_at':
                if ($this->object->closed_at) {
                    $date = new DateTime($this->object->closed_at);
                    $this->out[$fieldName] = $date->format(SPL_T_DATECAST);

                    break;
                }
                $this->out[$fieldName] = null;

                break;
            //====================================================================//
            // PRICE INFORMATIONS
            //====================================================================//
            case 'total_price':
                $this->getSimple($fieldName);

                break;
            case 'total_tax_excl':
                $this->out[$fieldName] = (float) ($this->object->total_price - $this->object->total_tax);

                break;
            default:
                return;
        }

        unset($this->in[$key]);
    }
}
