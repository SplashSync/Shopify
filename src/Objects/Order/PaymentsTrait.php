<?php

/*
 * This file is part of SplashSync Project.
 *
 * Copyright (C) Splash Sync <www.splashsync.com>
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Splash\Connectors\Shopify\Objects\Order;

use DateTime;

/**
 * Access to Orders Payments Fields
 */
trait PaymentsTrait
{
    

    private $KnownPaymentMethods = array(
        
            "manual"                        =>      "ByBankTransferInAdvance",
            "Bank Deposit"                  =>      "ByBankTransferInAdvance",
        
            "Money Order"                   =>      "CheckInAdvance",
        
            "PayPal Express Checkout"       =>      "PayPal",
            "PayPal Payflow Pro"            =>      "PayPal",
            "Alipay Global"                 =>      "PayPal",
            "Amazon Pay"                    =>      "PayPal",
        
            "Cash on Delivery (COD)"        =>      "COD",

            "Stripe"                        =>      "CreditCard",
            "Shopify Payments"              =>      "CreditCard",
        
    );
    
    
    /**
    * Build Fields using FieldFactory
    */
    protected function buildPaymentsFields()
    {
        
        //====================================================================//
        // Payment Line Payment Method
        $this->fieldsFactory()->Create(SPL_T_VARCHAR)
                ->Identifier("mode")
                ->InList("payments")
                ->Name("Payment method")
                ->MicroData("http://schema.org/Invoice", "PaymentMethod")
                ->Group("Payments")
                ->isReadOnly()
                ;

        //====================================================================//
        // Payment Line Date
        $this->fieldsFactory()->Create(SPL_T_DATE)
                ->Identifier("date")
                ->InList("payments")
                ->Name("Date")
                ->MicroData("http://schema.org/PaymentChargeSpecification", "validFrom")
                ->Group("Payments")
                ->isReadOnly()
                ;

        //====================================================================//
        // Payment Line Payment Identifier
        $this->fieldsFactory()->Create(SPL_T_VARCHAR)
                ->Identifier("number")
                ->InList("payments")
                ->Name("Transaction ID")
                ->MicroData("http://schema.org/Invoice", "paymentMethodId")
                ->Group("Payments")
                ->isReadOnly()
                ;

        //====================================================================//
        // Payment Line Amount
        $this->fieldsFactory()->Create(SPL_T_DOUBLE)
                ->Identifier("amount")
                ->InList("payments")
                ->Name("Amount")
                ->MicroData("http://schema.org/PaymentChargeSpecification", "price")
                ->Group("Payments")
                ->isReadOnly()
                ;
    }
    
    /**
     * Read requested Field
     *
     * @param        string $key       Input List Key
     * @param        string $fieldName Field Identifier / Name
     *
     * @return         none
     */
    private function getPaymentsFields($key, $fieldName)
    {
        //====================================================================//
        // Check if List field & Init List Array
        $FieldId = self::lists()->InitOutput($this->out, "payments", $fieldName);
        if (!$FieldId) {
            return;
        }
        //====================================================================//
        // Verify Order is Paid
        if ($this->object->financial_status != "paid") {
            unset($this->in[$key]);

            return true;
        }
        //====================================================================//
        // READ Fields
        switch ($fieldName) {
            //====================================================================//
            // Payment Line - Payment Mode
            case 'mode@payments':
                $Value  =   $this->getPaymentMethod();
                break;
            //====================================================================//
            // Payment Line - Payment Date
            case 'date@payments':
                $Date = new DateTime($this->object->created_at);
                $Value  =   $Date->format(SPL_T_DATECAST);
                break;
            //====================================================================//
            // Payment Line - Payment Identification Number
            case 'number@payments':
                $Value  =   null;
                break;
            //====================================================================//
            // Payment Line - Payment Amount
            case 'amount@payments':
                $Value  =   $this->object->total_price;
                break;
            default:
                return;
        }
        //====================================================================//
        // Insert Data in List
        self::lists()->Insert($this->out, "payments", $fieldName, 0, $Value);

        unset($this->in[$key]);
    }
    
    /**
     *  @abstract     Try To Detect Payment method Standardized Name
     *
     *  @return       string
     */
    private function getPaymentMethod()
    {
        //====================================================================//
        // Detect Payment Method Type from Default Payment "known" methods
        if (array_key_exists($this->object->gateway, $this->KnownPaymentMethods)) {
            return $this->KnownPaymentMethods[$this->object->gateway];
        }

        return "CreditCard";
    }
}
