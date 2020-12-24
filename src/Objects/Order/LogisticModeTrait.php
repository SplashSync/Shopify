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

use Splash\Connectors\Shopify\Models\ShopifyHelper as API;
use Splash\Core\SplashCore as Splash;

trait LogisticModeTrait
{
    /**
     * @var string[]
     */
    protected static $fulfillmentFields = array(
        "status",
//        "shipment_status",
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
            ->identifier("shipment_status")
            ->name("Status")
            ->isReadOnly()
        ;
        //====================================================================//
        // Tracking Company
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->identifier("tracking_company")
            ->name("Company")
            ->isReadOnly()
        ;
        //====================================================================//
        // Tracking Number
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->Identifier("tracking_number")
            ->Name("Tracking Number")
            ->MicroData("http://schema.org/ParcelDelivery", "trackingNumber")
        ;
        //====================================================================//
        // Tracking Url
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->Identifier("tracking_url")
            ->Name("Tracking Url")
            ->MicroData("http://schema.org/ParcelDelivery", "trackingUrl")
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
    protected function getLogisticFields($key, $fieldName): void
    {
        //====================================================================//
        // READ Fields
        switch ($fieldName) {
            case 'tracking_company':
            case 'tracking_number':
            case 'tracking_url':
            case 'shipment_status':
                $this->out[$fieldName] = $this->getLogisticalField($fieldName);

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
     * @param mixed  $fieldData Field Data
     *
     * @return void
     */
    protected function setFulfillmentFields($fieldName, $fieldData): void
    {
        //====================================================================//
        // WRITE Field
        switch ($fieldName) {
            case 'tracking_company':
            case 'tracking_number':
            case 'tracking_url':
                $this->setMainFulfillmentField($fieldName, $fieldData);

                break;
            case 'shipment_status':


                $this->setMainFulfillmentField("status", "pending");
//                $this->setMainFulfillmentField("shipment_status", "confirmed");

                break;
            default:
                return;
        }
        unset($this->in[$fieldName]);
    }

    /**
     * Update Request Object
     *
     * @param bool $needed Is This Update Needed
     *
     * @return bool Object Id
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function updateFulfillment()
    {
        //====================================================================//
        // Search for first Active Item
        $index = $this->getFirstFulfillmentIndex();

//        dump($this->getMainFulfillmentUri());
//        dump($this->object->fulfillments[$index]);
//        exit;

//        API::post($this->getMainFulfillmentUri()."/complete", array());

//        unset($this->object->fulfillments[0]["status"]);
//        unset($this->object->fulfillments[0]["tracking_numbers"]);
//        unset($this->object->fulfillments[0]["tracking_urls"]);
//        unset($this->object->fulfillments[0]["line_items"]);
//        unset($this->object->fulfillments[0]["shipment_status"]);
//
//
//        $this->object->fulfillments[0]["status"] = "pending";

        //====================================================================//
        // Extract Fulfillment for Update
        $data = array(
            "fulfillment" => array_intersect_key(
                $this->object->fulfillments[$index],
                array_flip(self::$fulfillmentFields)
            )
        );
        $data["fulfillment"]["status"] = "pending";
//        $data["fulfillment"]["shipment_status"] = "confirmed";

        //====================================================================//
        // Update Fulfillment
dump(isset($this->object->fulfillments[$index]["id"]));

        $result = isset($this->object->fulfillments[$index]["id"])
            ? API::put($this->getMainFulfillmentUri(), $data)
            : API::post($this->getMainFulfillmentUri(), $data)
        ;

        API::post($this->getMainFulfillmentUri()."/events", array(
            "event" => array(
                "status" => "attempted_delivery"
            )
        ));

//        dump($this->object->fulfillments[0]);



        dump($data);
        dump($result["fulfillment"]);
        echo Splash::log()->getHtmlLog();
        exit;

        //====================================================================//
        // Detect Errors
        if (null === $result) {
            return Splash::log()->errTrace(
                "Unable to Update Customer Order (".$this->getObjectIdentifier().")."
            );
        }
    }

    /**
     * Get Order First Fulfillment Line Field
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
     * @return bool
     */
    private function setMainFulfillmentField(string $fieldName, $fieldData): void
    {
        //====================================================================//
        // Search for first Active Item
        $index = $this->getFirstFulfillmentIndex();
        //====================================================================//
        // Check if Fulfillment Exists
        if (is_null($index) ) {
            //====================================================================//
            // Check if Product Default Stock Location is Selected
            $locationId = $this->getParameter("LocationId");
            if (empty($locationId)) {
                Splash::log()->err('No Default Product Stock Location Selected : Order Update Skipped!!');
            }
            $this->object->fulfillments[] = array(
                "location_id" => $locationId,
                "notify_customer" => false,
            );
            $index = $this->getFirstFulfillmentIndex();
        }
        //====================================================================//
        // Check if Field Exists
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
        foreach($this->object->fulfillments as $index => $fulfillment) {
            if (isset($fulfillment['status']) && $fulfillment['status'] == "cancelled") {
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
            return Splash::log()->err(
                'Unable to Fulfill this Order: No Shipping method detected'
            );
        }
        //====================================================================//
        // Check if Product Default Stock Location is Selected
        $this->object->fulfillments[] = array(
            "location_id" => $locationId,
            "tracking_company" => false,
            "notify_customer" => (bool) $this->getParameter("LogisticNotify", false),
        );

        return true;
    }


}
