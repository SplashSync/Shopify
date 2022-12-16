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
        "products/update" => "On Product Updated",
        "products/delete" => "On Product Deleted",

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
     * Shopify WebHooks Names used by Splash
     *
     * @var array
     */
    protected static array $gpdrTopics = array(
        // GPDR Events
        "customers/data_request" => "GPDR - Customers request their data from a store owner",
        "customers/redact" => "GPDR - Request that data is deleted on behalf of a customer",
        "shop/redact" => "GPDR - 48 hours after a store owner uninstalls your app"
    );

    /**
     * WebHook Request Data Format
     *
     * @var array
     */
    protected static array $format = array(
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
        return array_keys(array_merge(static::$topics, static::$gpdrTopics));
    }

    /**
     * Get List of GPDR WebHooks Topics
     *
     * @return array
     */
    public static function getGpdrTopics() : array
    {
        return array_keys(static::$gpdrTopics);
    }

    /**
     * Build Core Fields using FieldFactory
     *
     * @return void
     */
    protected function buildCoreFields(): void
    {
        //====================================================================//
        // WebHook Url
        $this->fieldsFactory()->create(SPL_T_URL)
            ->identifier("address")
            ->name("Address")
            ->isRequired()
            ->isListed()
        ;
        //====================================================================//
        // WebHook Topic (Event Type)
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->identifier("topic")
            ->name("Topic (Shopify Event Type)")
            ->addChoices(array_merge(static::$topics, static::$gpdrTopics))
            ->isRequired()
            ->isListed()
        ;
        //====================================================================//
        // WebHook Format
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->identifier("format")
            ->name("Data Format")
            ->addChoices(static::$format)
        ;
        //====================================================================//
        // WebHook Format
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->identifier("api_version")
            ->name("Api Version")
            ->isReadOnly()
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
    protected function getCoreFields(string $key, string $fieldName): void
    {
        switch ($fieldName) {
            case 'address':
            case 'topic':
            case 'format':
            case 'api_version':
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
     *
     * @return void
     */
    protected function setCoreFields(string $fieldName, $fieldData): void
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
