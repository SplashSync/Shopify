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

use DateTime;
use Splash\Connectors\Shopify\Objects\Invoice;

/**
 * Access to Orders Core Fields
 */
trait CoreTrait
{
    /**
     * Build Core Fields using FieldFactory
     *
     * @return void
     *
     * @SuppressWarnings(PHPMD.ElseExpression)
     */
    private function buildCoreFields(): void
    {
        //====================================================================//
        // Customer Object
        $this->fieldsFactory()->create((string) self::objects()->Encode("ThirdParty", SPL_T_ID))
            ->Identifier("customer")
            ->Name("Customer")
            ->isReadOnly()
        ;
        if ($this instanceof Invoice) {
            $this->fieldsFactory()->MicroData("http://schema.org/Invoice", "customer");
        } else {
            $this->fieldsFactory()->MicroData("http://schema.org/Organization", "ID");
        }

        //====================================================================//
        // Customer Email
        $this->fieldsFactory()->create(SPL_T_EMAIL)
            ->Identifier("email")
            ->Name("Customer Email")
            ->MicroData("http://schema.org/ContactPoint", "email")
            ->isReadOnly();

        //====================================================================//
        // Reference
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->Identifier("name")
            ->Name("Reference")
            ->MicroData("http://schema.org/Order", "orderNumber")
            ->isReadOnly()
            ->isListed();

        //====================================================================//
        // UUID
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->Identifier("uuid")
            ->Name("UUID")
            ->description("Order Unique Reference")
            ->MicroData("http://schema.org/Order", "orderNumberID")
            ->isReadOnly();

        //====================================================================//
        // Order Date
        $this->fieldsFactory()->create(SPL_T_DATE)
            ->Identifier("processed_at")
            ->Name("Order Date")
            ->MicroData("http://schema.org/Order", "orderDate")
            ->isReadOnly()
            ->isListed();
    }

    /**
     * Read requested Field
     *
     * @param string $key       Input List Key
     * @param string $fieldName Field Identifier / Name
     *
     * @return void
     */
    private function getCoreFields($key, $fieldName): void
    {
        //====================================================================//
        // READ Fields
        switch ($fieldName) {
            //====================================================================//
            // Direct Readings
            case 'name':
            case 'email':
                $this->getSimple($fieldName);

                break;
            //====================================================================//
            // Order UUID
            case 'uuid':
                $this->out[$fieldName] = $this->object->id.$this->object->name;

                break;
            //====================================================================//
            // Customer Object Id Readings
            case 'customer':
                if (isset($this->object->customer)) {
                    $this->out[$fieldName] = self::objects()->Encode("ThirdParty", $this->object->customer['id']);

                    break;
                }
                $this->out[$fieldName] = null;

                break;
            //====================================================================//
            // Order Official Date
            case 'processed_at':
                $date = new DateTime($this->object->{$fieldName});
                $this->out[$fieldName] = $date->format(SPL_T_DATECAST);

                break;
            default:
                return;
        }

        unset($this->in[$key]);
    }
}
