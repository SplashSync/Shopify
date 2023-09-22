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
use Exception;
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
        $this->fieldsFactory()->create((string) self::objects()->encode("ThirdParty", SPL_T_ID))
            ->identifier("customer")
            ->name("Customer")
            ->isReadOnly()
        ;
        if ($this instanceof Invoice) {
            $this->fieldsFactory()->microData("http://schema.org/Invoice", "customer");
        } else {
            $this->fieldsFactory()->microData("http://schema.org/Organization", "ID");
        }

        //====================================================================//
        // Customer Email
        $this->fieldsFactory()->create(SPL_T_EMAIL)
            ->identifier("email")
            ->name("Customer Email")
            ->microData("http://schema.org/ContactPoint", "email")
            ->isIndexed()
            ->isReadOnly()
        ;
        //====================================================================//
        // Reference
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->identifier("name")
            ->name("Reference")
            ->microData("http://schema.org/Order", "orderNumber")
            ->isReadOnly()
            ->isRequired()
            ->isPrimary()
            ->isIndexed()
            ->isListed()
        ;
        //====================================================================//
        // UUID
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->identifier("uuid")
            ->name("UUID")
            ->description("Order Unique Reference")
            ->microData("http://schema.org/Order", "orderNumberID")
            ->isReadOnly()
        ;
        //====================================================================//
        // Order Date
        $this->fieldsFactory()->create(SPL_T_DATE)
            ->Identifier("processed_at")
            ->Name("Order Date")
            ->MicroData("http://schema.org/Order", "orderDate")
            ->isReadOnly()
            ->isListed()
        ;
    }

    /**
     * Read requested Field
     *
     * @param string $key       Input List Key
     * @param string $fieldName Field Identifier / Name
     *
     * @throws Exception
     *
     * @return void
     */
    private function getCoreFields(string $key, string $fieldName): void
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
