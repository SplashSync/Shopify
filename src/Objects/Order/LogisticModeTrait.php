<?php

/*
 *  This file is part of SplashSync Project.
 *
 *  Copyright (C) 2015-2021 Splash Sync  <www.splashsync.com>
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace Splash\Connectors\Shopify\Objects\Order;

use Splash\Connectors\Shopify\Models\ShopifyHelper as API;
use Splash\Core\SplashCore as Splash;

trait LogisticModeTrait
{
    /**
     * @var string[]
     */
    protected static array $fulfillmentFields = array(
        "status",
        "tracking_company", "tracking_number", "tracking_url",
        "location_id", "notify_customer",
    );

    /**
     * Build Fields using FieldFactory
     *
     * @return void
     */
    protected function buildLogisticFields(): void
    {
        //====================================================================//
        // Check if Logistic Mode is Active
        if (!$this->connector->hasLogisticMode()) {
            return;
        }
        //====================================================================//
        // Tracking Company
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->identifier("tracking_company")
            ->name("Carrier")
            ->MicroData("http://schema.org/ParcelDelivery", "identifier")
            ->isReadOnly()
        ;
        //====================================================================//
        // Tracking Company
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->identifier("shipment_status")
            ->name("Shipping Status")
            ->isReadOnly()
        ;
        //====================================================================//
        // Tracking Number
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->identifier("tracking_number")
            ->name("Tracking Number")
            ->microData("http://schema.org/ParcelDelivery", "trackingNumber")
        ;
        //====================================================================//
        // Tracking Url
        $this->fieldsFactory()->create(SPL_T_URL)
            ->identifier("tracking_url")
            ->name("Tracking Url")
            ->microData("http://schema.org/ParcelDelivery", "trackingUrl")
        ;
    }

    /**
     * Read requested Field
     *
     * @param string $key       Input List Key
     * @param string $fieldName Field Identifier / Name
     *
     * @return void
     */
    protected function getLogisticFields(string $key, string $fieldName): void
    {
        //====================================================================//
        // READ Fields
        switch ($fieldName) {
            case 'tracking_number':
            case 'tracking_url':
            case 'shipment_status':
                $this->out[$fieldName] = $this->getLogisticalField($fieldName);

                break;
            case 'tracking_company':
                $mainShippingCode = $this->getMainShippingCode();
                $this->out[$fieldName] = $mainShippingCode ?: "Default";

                break;
            default:
                return;
        }

        unset($this->in[$key]);
    }

    /**
     * Write Given Fields
     *
     * @param string $fieldName Field Identifier / Name
     * @param string $fieldData Field Data
     *
     * @return void
     */
    protected function setFulfillmentFields(string $fieldName, string $fieldData): void
    {
        //====================================================================//
        // WRITE Field
        switch ($fieldName) {
            case 'tracking_number':
            case 'tracking_url':
                $this->setMainFulfillmentField($fieldName, $fieldData);

                break;
            default:
                return;
        }
        unset($this->in[$fieldName]);
    }

    /**
     * Update Order Fulfillment Status
     *
     * @return bool
     */
    protected function updateFulfillment(): bool
    {
        //====================================================================//
        // Search for first Active Item
        $index = $this->getFirstFulfillmentIndex();
        if (is_null($index)) {
            return false;
        }
        //====================================================================//
        // Extract Fulfillment for Update
        $data = array(
            "fulfillment" => array_intersect_key(
                $this->object->fulfillments[$index],
                array_flip(self::$fulfillmentFields)
            )
        );
        //====================================================================//
        // Create/Update Fulfillment
        $result = isset($this->object->fulfillments[$index]["id"])
            ? API::put($this->getMainFulfillmentUri(), $data)
            : API::post($this->getMainFulfillmentUri(), $data)
        ;
        if (null === $result) {
            return Splash::log()->errTrace("Unable to create/update Order Fulfillment.");
        }
        //====================================================================//
        // Update Fulfillment Status
        if (isset($this->object->fulfillments[$index]["new_shipment_status"])) {
            $statusUpdate = API::post($this->getFulfillmentEventUri($result["fulfillment"]), array(
                "event" => array(
                    "status" => $this->object->fulfillments[$index]["new_shipment_status"]
                )
            ));
            if (null === $statusUpdate) {
                return Splash::log()->errTrace("Unable to Update Order Shipping Status.");
            }
        }

        return true;
    }

    /**
     * Get Order First Fulfillment Line Field
     *
     * @param string $fieldName
     *
     * @return null|string
     */
    private function getLogisticalField(string $fieldName): ?string
    {
        //====================================================================//
        // Search for first Active Item
        $index = $this->getFirstFulfillmentIndex();
        if (is_null($index) || !isset($this->object->fulfillments[$index][$fieldName])) {
            return null;
        }

        return (string) $this->object->fulfillments[$index][$fieldName];
    }

    /**
     * Set Order First Fulfillment Line Field
     *
     * @param string $fieldName
     * @param string $fieldData
     *
     * @return void
     */
    private function setMainFulfillmentField(string $fieldName, string $fieldData): void
    {
        //====================================================================//
        // Safety Check
        if (empty($fieldData)) {
            return;
        }
        //====================================================================//
        // Search for first Active Item
        $index = $this->getFirstFulfillmentIndex();
        //====================================================================//
        // Check if Fulfillment Exists
        if (is_null($index)) {
            //====================================================================//
            // Create a New Fulfillment
            if (!$this->addNewFulfillment()) {
                return;
            }
            $index = $this->getFirstFulfillmentIndex();
        }
        //====================================================================//
        // Check if Field Exists (To Compare)
        if (!isset($this->object->fulfillments[$index][$fieldName])
            || ($this->object->fulfillments[$index][$fieldName] != $fieldData)) {
            $this->object->fulfillments[$index][$fieldName] = $fieldData;
            $this->needUpdate('fulfillments');
        }
    }

    /**
     * Get Main Fulfillment CRUD Base Uri
     *
     * @return string
     */
    private function getMainFulfillmentUri() : string
    {
        $uri = 'orders/'.$this->getObjectIdentifier()."/fulfillments";
        //====================================================================//
        // Search for first Active Item
        $index = $this->getFirstFulfillmentIndex();
        if (isset($this->object->fulfillments[$index]["id"])) {
            $uri .= "/".$this->object->fulfillments[$index]["id"];
        }

        return $uri;
    }

    /**
     * Get Main Fulfillment CRUD Base Uri
     *
     * @param array $fulfillment
     *
     * @return string
     */
    private function getFulfillmentEventUri(array $fulfillment) : string
    {
        $uri = 'orders/'.$this->getObjectIdentifier()."/fulfillments";
        $uri .= "/".$fulfillment["id"]."/events";

        return $uri;
    }
    /**
     * Get First Fulfillment CRUD Base Uri
     *
     * @return null|int
     */
    private function getFirstFulfillmentIndex() : ?int
    {
        //====================================================================//
        // Safety Check
        if (!is_array($this->object->fulfillments)) {
            return null;
        }
        //====================================================================//
        // Search for first Active Item
        /** @var array $fulfillment */
        foreach ($this->object->fulfillments as $index => $fulfillment) {
            if (isset($fulfillment['status']) && "cancelled" == $fulfillment['status']) {
                continue;
            }

            return $index;
        }

        return null;
    }

    /**
     * Add a New Fulfillment Line
     *
     * @return bool
     */
    private function addNewFulfillment(): bool
    {
        //====================================================================//
        // Check if Product Default Stock Location is Selected
        $locationId = $this->getParameter("LocationId");
        if (empty($locationId)) {
            return Splash::log()->err(
                'Unable to Fulfill this Order: No Default Product Stock Location Selected'
            );
        }
        //====================================================================//
        // Check if Product Default Stock Location is Selected
        $trackingCompany = $this->getMainShippingCode();
        if (empty($trackingCompany)) {
            $trackingCompany = "default";
            Splash::log()->war(
                'No Shipping method detected. Set to Default.'
            );
        }
        //====================================================================//
        // Check if Product Default Stock Location is Selected
        $this->object->fulfillments[] = array(
            "location_id" => $locationId,
            "tracking_company" => $trackingCompany,
            "notify_customer" => (bool) $this->getParameter("LogisticNotify", false),
        );

        return true;
    }
}
