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

namespace Splash\Connectors\Shopify\Objects\Invoice;

use DateTime;
use Exception;
use Splash\Connectors\Shopify\Models\ShopifyHelper as API;
use Splash\Connectors\Shopify\Objects\Invoice;

/**
 * Access to Orders Payments Fields
 */
trait PaymentsTrait
{
    /**
     * @var array[]
     */
    private ?array $transactions = null;

    /**
     * @var array
     */
    private static $knownPaymentMethods = array(
        "manual" => "ByBankTransferInAdvance",
        "Bank Deposit" => "ByBankTransferInAdvance",

        "Money Order" => "CheckInAdvance",

        "PayPal Express Checkout" => "PayPal",
        "PayPal Payflow Pro" => "PayPal",
        "Alipay Global" => "PayPal",
        "Amazon Pay" => "PayPal",

        "Cash on Delivery (COD)" => "COD",

        "Stripe" => "CreditCard",
        "PayPlug" => "CreditCard",
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
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->identifier("mode")
            ->inList("payments")
            ->name("Method")
            ->microData("http://schema.org/Invoice", "PaymentMethod")
            ->group("Payments")
            ->isReadOnly()
        ;
        //====================================================================//
        // Payment Line Date
        $this->fieldsFactory()->create(SPL_T_DATE)
            ->identifier("date")
            ->inList("payments")
            ->name("Date")
            ->microData("http://schema.org/PaymentChargeSpecification", "validFrom")
            ->group("Payments")
            ->isReadOnly()
        ;
        //====================================================================//
        // Payment Line Payment Identifier
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->identifier("number")
            ->inList("payments")
            ->name("Transaction")
            ->microData("http://schema.org/Invoice", "paymentMethodId")
            ->group("Payments")
            ->isReadOnly()
        ;
        //====================================================================//
        // Payment Line Amount
        $this->fieldsFactory()->create(SPL_T_DOUBLE)
            ->identifier("amount")
            ->inList("payments")
            ->name("Amount")
            ->microData("http://schema.org/PaymentChargeSpecification", "price")
            ->group("Payments")
            ->isReadOnly()
        ;
    }

    /**
     * Clear Potentially Loaded List of Transactions
     *
     * @return void
     */
    protected function clearLoadedTransactions(): void
    {
        $this->transactions = null;
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
    protected function getPaymentsFields(string $key, string $fieldName): void
    {
        //====================================================================//
        // Check if List field & Init List Array
        $fieldId = self::lists()->initOutput($this->out, "payments", $fieldName);
        if (!$fieldId) {
            return;
        }
        //====================================================================//
        // Ensure Order Transactions are Loaded
        if (!isset($this->transactions)) {
            //====================================================================//
            // Get Order Transactions from Api
            $transactions = API::get("orders/".$this->object->id."/transactions", null, array(), "transactions");
            $this->transactions = is_array($transactions) ? $transactions : array();
        }
        //====================================================================//
        // Walk on Order Transactions
        foreach ($this->transactions as $index => $transaction) {
            //====================================================================//
            // Filter Transactions
            if (!is_array($transaction) || self::isFilteredTransaction($transaction)) {
                continue;
            }
            //====================================================================//
            // Extract Field Value from Transaction
            $value = $this->getPaymentValue($fieldId, $transaction);
            //====================================================================//
            // Insert Data in List
            self::lists()->insert($this->out, "payments", $fieldName, $index, $value);
        }

        unset($this->in[$key]);
    }

    /**
     * Read requested Field from Transaction
     *
     * @param string $fieldId     Field Identifier / Name
     * @param array  $transaction Shopify Transaction Array
     *
     * @throws Exception
     *
     * @return null|int|string
     */
    private function getPaymentValue(string $fieldId, array $transaction)
    {
        //====================================================================//
        // READ Fields
        switch ($fieldId) {
            //====================================================================//
            // Payment Line - Payment Mode
            case 'mode':
                return self::toPaymentMethod($transaction["gateway"] ?? "Stripe");
            //====================================================================//
            // Payment Line - Payment Date
            case 'date':
                try {
                    $date = new DateTime($transaction["created_at"] ?? $this->object->created_at);
                } catch (Exception $ex) {
                    $date = new DateTime($this->object->created_at);
                }

                return $date->format(SPL_T_DATECAST);
            //====================================================================//
            // Payment Line - Payment Identification Number
            case 'number':
                return self::toPaymentReceipt($transaction["receipt"] ?? array());
            //====================================================================//
            // Payment Line - Payment Amount
            case 'amount':
                return $transaction["amount"] ?? 0.0;
            default:
                return null;
        }
    }

    /**
     * Check if this Transaction Should be Filtered
     *
     * @param array $transaction
     *
     * @return bool
     */
    private function isFilteredTransaction(array $transaction): bool
    {
        //====================================================================//
        // Filter on Success Status
        if (($transaction["status"] ?? "error") != "success") {
            return true;
        }
        //====================================================================//
        // Filter on Sales
        if (($this instanceof Invoice) && (($transaction["kind"] ?? "void") != "sale")) {
            return true;
        }

        return false;
    }

    /**
     * Try To Detect Payment method Standardized Name
     *
     * @param string $gateway
     *
     * @return string
     */
    private static function toPaymentMethod(string $gateway): string
    {
        //====================================================================//
        // Detect Payment Method Type from Default Payment "known" methods
        if (isset(self::$knownPaymentMethods[$gateway])) {
            return self::$knownPaymentMethods[$gateway];
        }

        return "CreditCard";
    }

    /**
     * Try To Detect Payment Receipt Reference
     *
     * @param array $receipt
     *
     * @return string
     */
    private static function toPaymentReceipt(array $receipt): string
    {
        //====================================================================//
        // Known Receipt Array keys
        static $knownKeys = array(
            "systempay" => "x_reference",
            "PayPlug" => "refund_id",
        );
        //====================================================================//
        // Walk on Allowed Receipt Number Keys
        foreach ($knownKeys as $key) {
            if (isset($receipt[$key]) && is_string($receipt[$key])) {
                return $receipt[$key];
            }
        }
        //====================================================================//
        // Default method
        return implode(", ", $receipt);
    }
}
