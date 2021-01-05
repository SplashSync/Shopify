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

namespace Splash\Connectors\Shopify\Objects\Invoice;

use DateTime;
use Exception;

/**
 * Access to Orders Payments Fields
 */
trait PaymentsTrait
{
    /**
     * @var array
     */
    private $knownPaymentMethods = array(
        "manual" => "ByBankTransferInAdvance",
        "Bank Deposit" => "ByBankTransferInAdvance",

        "Money Order" => "CheckInAdvance",

        "PayPal Express Checkout" => "PayPal",
        "PayPal Payflow Pro" => "PayPal",
        "Alipay Global" => "PayPal",
        "Amazon Pay" => "PayPal",

        "Cash on Delivery (COD)" => "COD",

        "Stripe" => "CreditCard",
        "Shopify Payments" => "CreditCard",
    );

    /**
     * Build Fields using FieldFactory
     *
     * @return void
     */
    protected function buildPaymentsFields(): void
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
     * @param string $key       Input List Key
     * @param string $fieldName Field Identifier / Name
     *
     * @throws Exception
     *
     * @return void
     */
    private function getPaymentsFields($key, $fieldName): void
    {
        //====================================================================//
        // Check if List field & Init List Array
        $fieldId = self::lists()->InitOutput($this->out, "payments", $fieldName);
        if (!$fieldId) {
            return;
        }
        //====================================================================//
        // Verify Order is Paid
        if ("paid" != $this->object->financial_status) {
            unset($this->in[$key]);

            return;
        }
        //====================================================================//
        // READ Fields
        switch ($fieldName) {
            //====================================================================//
            // Payment Line - Payment Mode
            case 'mode@payments':
                $value = $this->getPaymentMethod();

                break;
            //====================================================================//
            // Payment Line - Payment Date
            case 'date@payments':
                $date = new DateTime($this->object->created_at);
                $value = $date->format(SPL_T_DATECAST);

                break;
            //====================================================================//
            // Payment Line - Payment Identification Number
            case 'number@payments':
                $value = null;

                break;
            //====================================================================//
            // Payment Line - Payment Amount
            case 'amount@payments':
                $value = $this->object->total_price;

                break;
            default:
                return;
        }
        //====================================================================//
        // Insert Data in List
        self::lists()->Insert($this->out, "payments", $fieldName, 0, $value);

        unset($this->in[$key]);
    }

    /**
     * Try To Detect Payment method Standardized Name
     *
     * @return string
     */
    private function getPaymentMethod()
    {
        //====================================================================//
        // Detect Payment Method Type from Default Payment "known" methods
        if (array_key_exists($this->object->gateway, $this->knownPaymentMethods)) {
            return $this->knownPaymentMethods[$this->object->gateway];
        }

        return "CreditCard";
    }
}
