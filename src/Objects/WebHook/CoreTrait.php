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

namespace Splash\Connectors\Shopify\Objects\WebHook;

/**
 * Shopify WebHook Core Fields (Required)
 */
trait CoreTrait
{
    /**
     * Shopify WebHooks Names used by Splash
     *
     * @var array
     */
    protected static $topics = array(
        // Customers (ThirdParty) Events
        "customers/create" => "On Customer Created",
        "customers/delete" => "On Customer Deleted",
        "customers/disable" => "On Customer Disabled",
        "customers/enable" => "On Customer Enabled",
        "customers/update" => "On Customer Updated",

        // Products Events
        "products/create" => "On Product Created",
        "products/delete" => "On Product Updated",
        "products/update" => "On Product Deleted",

        // Orders Events
        "orders/cancelled" => "On Order Canceled",
        "orders/create" => "On Order Created",
        "orders/delete" => "On Order Deleted",
        "orders/fulfilled" => "On Order Shipped",
        "orders/paid" => "On Order Paid",
        "orders/partially_fulfilled" => "On Order Partially Shipped",
        "orders/updated" => "On Order Updated",
    );

    /**
     * WebHook Requets Dat Format
     *
     * @var array
     */
    protected static $format = array(
        "json" => "Json Data Format",
        "xml" => "Xml Data Format",
    );

    /**
     * Get List of Required WebHooks Topics
     *
     * @return array
     */
    public static function getTopics() : array
    {
        return array_keys(static::$topics);
    }

    /**
     * Build Core Fields using FieldFactory
     */
    protected function buildCoreFields()
    {
        //====================================================================//
        // WebHook Url
        $this->fieldsFactory()->create(SPL_T_URL)
            ->Identifier("address")
            ->Name("Address")
            ->isRequired()
            ->isListed();

        //====================================================================//
        // WebHook Topic (Event Type)
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->Identifier("topic")
            ->Name("Topic (Shopify Event Type)")
            ->addChoices(static::$topics)
            ->isRequired()
            ->isListed();

        //====================================================================//
        // WebHook Format
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->Identifier("format")
            ->Name("Data Format")
            ->addChoices(static::$format);
    }

    /**
     * Read requested Field
     *
     * @param string $key       Input List Key
     * @param string $fieldName Field Identifier / Name
     */
    protected function getCoreFields($key, $fieldName)
    {
        switch ($fieldName) {
            case 'address':
            case 'topic':
            case 'format':
                $this->getSimple($fieldName);

                break;
            default:
                return;
        }

        //====================================================================//
        // Clear Key Flag
        unset($this->in[$key]);
    }

    /**
     * Write Given Fields
     *
     * @param string $fieldName Field Identifier / Name
     * @param mixed  $fieldData Field Data
     */
    protected function setCoreFields($fieldName, $fieldData)
    {
        switch ($fieldName) {
            case 'address':
            case 'topic':
            case 'format':
                $this->setSimple($fieldName, $fieldData);

                break;
            default:
                return;
        }
        unset($this->in[$fieldName]);
    }
}
