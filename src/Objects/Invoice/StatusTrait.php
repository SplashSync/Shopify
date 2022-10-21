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

use DateTimeInterface;
use Splash\Models\Objects\Invoice\Status;

/**
 * Shopify Customer Invoice Status Field
 */
trait StatusTrait
{
    /**
     * Build Customer Invoice Status Fields using FieldFactory
     *
     * @return void
     */
    protected function buildStatusFields(): void
    {
        //====================================================================//
        // Order Current Status
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->identifier("status")
            ->name("Order Status")
            ->microData("http://schema.org/Invoice", "paymentStatus")
            ->addChoice(Status::DRAFT, "Draft")
            ->addChoice(Status::PAYMENT_DUE, "Payment Due")
            ->addChoice(Status::COMPLETE, "Payment Completed")
            ->addChoice(Status::CANCELED, "Canceled")
            ->isListed()
        ;
    }

    /**
     * Read requested Field
     *
     * @param string $key       Input List Key
     * @param string $fieldName Field Identifier / Name
     *
     * @return void
     *
     * @SuppressWarnings(PHPMD.ElseExpression)
     */
    protected function getStatusFields($key, $fieldName): void
    {
        if ('status' != $fieldName) {
            return;
        }

        $this->out[$fieldName] = self::getSplashStatus(
            $this->object->confirmed,
            $this->object->cancelled_at,
            $this->object->financial_status
        );

        unset($this->in[$key]);
    }

    /**
     * Decode Splash Status from Order Informations
     *
     * @param bool                          $confirmed
     * @param null|DateTimeInterface|string $cancelledAt
     * @param null|string                   $paymentStatus
     *
     * @return string
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected static function getSplashStatus(bool $confirmed, $cancelledAt, ?string $paymentStatus): string
    {
        //====================================================================//
        // Unconfirmed Order => Draft
        if (empty($confirmed)) {
            return Status::DRAFT;
        }
        //====================================================================//
        // Status from Order Canceled At Status
        if ($cancelledAt) {
            return Status::CANCELED;
        }
        //====================================================================//
        // Status from Order Payment Status
        switch ($paymentStatus) {
            case 'pending':
            case 'authorized':
            case 'partially_paid':
                return Status::PAYMENT_DUE;
            case 'paid':
            case 'partially_refunded':
            case 'refunded':
                return Status::COMPLETE;
            case 'voided':
                return Status::CANCELED;
        }

        return Status::UNKNOWN;
    }
}
