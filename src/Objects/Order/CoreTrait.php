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
     * @SuppressWarnings(PHPMD.ElseExpression)
     */
    private function buildCoreFields()
    {
        //====================================================================//
        // Customer Object
        $this->fieldsFactory()->create((string) self::objects()->Encode("ThirdParty", SPL_T_ID))
            ->Identifier("customer")
            ->Name("Customer");
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
    private function getCoreFields($key, $fieldName)
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
