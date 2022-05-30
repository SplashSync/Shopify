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

use DateTimeInterface;
use Splash\Core\SplashCore      as Splash;
use Splash\Models\Objects\Order\Status;

/**
 * Shopify Customer Order Status Field
 */
trait StatusTrait
{
    /**
     * Build Customer Order Status Fields using FieldFactory
     *
     * @return void
     */
    protected function buildStatusFields(): void
    {
        //====================================================================//
        // Order Current Status
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->Identifier("status")
            ->Name("Order Status")
            ->MicroData("http://schema.org/Order", "orderStatus")
            ->isListed()
            ->AddChoice(Status::CANCELED, "Canceled")
            ->AddChoice(Status::DRAFT, "Draft")
            ->AddChoice(Status::PROCESSING, "Processing")
            ->AddChoice(Status::IN_TRANSIT, "Shipped")
            ->AddChoice(Status::DELIVERED, "Delivered")
            ->AddChoice(Status::PROBLEM, "Problem")
            ->AddChoice(Status::RETURNED, "Returned")
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
            $this->object->financial_status,
            $this->object->fulfillment_status,
            $this->getLogisticalField('shipment_status')
        );

        unset($this->in[$key]);
    }

    /**
     * Write Given Fields
     *
     * @param string $fieldName Field Identifier / Name
     * @param mixed  $fieldData Field Data
     *
     * @return bool
     */
    protected function setStatusFields($fieldName, $fieldData): bool
    {
        if ('status' != $fieldName) {
            return true;
        }
        unset($this->in[$fieldName]);
        //====================================================================//
        // Decode Current Order Status
        $currentStatus = self::getSplashStatus(
            $this->object->confirmed,
            $this->object->cancelled_at,
            $this->object->financial_status,
            $this->object->fulfillment_status,
            $this->getLogisticalField('shipment_status')
        );
        //====================================================================//
        // Unchanged Status
        if ($currentStatus == $fieldData) {
            return true;
        }
        //====================================================================//
        // Try to Cancel Order from Splash => NO
        if (Status::CANCELED == $fieldData) {
            return Splash::log()->err("You can't cancel an Order from Splash API");
        }
        //====================================================================//
        // Try to mark Order as returned
        if (Status::RETURNED == $fieldData) {
            return Splash::log()->err("You cannot return an Order from Splash API");
        }
        //====================================================================//
        // Update Order Shipping Status
        switch ($fieldData) {
            case Status::PROCESSING:
                $this->setMainFulfillmentField("new_shipment_status", "confirmed");

                break;
            case Status::IN_TRANSIT:
                $this->setMainFulfillmentField("new_shipment_status", "in_transit");

                break;
            case Status::DELIVERED:
                $this->setMainFulfillmentField("new_shipment_status", "delivered");

                break;
            case Status::PROBLEM:
                $this->setMainFulfillmentField("new_shipment_status", "failure");

                break;
        }

        return true;
    }

    /**
     * Decode Splash Status from Order Informations
     *
     * @param bool                          $confirmed
     * @param null|DateTimeInterface|string $cancelledAt
     * @param null|string                   $paymentStatus
     * @param null|string                   $fulfillmentStatus
     * @param null|string                   $shipmentStatus
     *
     * @return string
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected static function getSplashStatus(
        bool $confirmed,
        $cancelledAt,
        ?string $paymentStatus,
        ?string $fulfillmentStatus,
        ?string $shipmentStatus
    ): string {
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
        // Status from Order Fulfillment Status
        if ("paid" != $paymentStatus) {
            return Status::PAYMENT_DUE;
        }

        //====================================================================//
        // Status from Order Fulfillment Status
        if ("restocked" == $fulfillmentStatus) {
            return Status::RETURNED;
        }
        if (empty($fulfillmentStatus)) {
            return Status::PROCESSING;
        }
        //====================================================================//
        // Status from Order Shipment Status
        switch ($shipmentStatus) {
            case 'label_printed':
            case 'label_purchased':
            case 'ready_for_pickup':
            case 'confirmed':
                return Status::PROCESSING;
            case 'attempted_delivery':
            case 'in_transit':
            case 'out_for_delivery':
                return Status::IN_TRANSIT;
            case 'delivered':
                return Status::DELIVERED;
            case 'failure':
                return Status::PROBLEM;
        }

        return Status::UNKNOWN;
    }
}
