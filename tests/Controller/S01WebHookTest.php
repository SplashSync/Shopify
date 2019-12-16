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

namespace Splash\Connectors\Shopify\Test\Controller;

use Splash\Connectors\Shopify\Objects;
use Splash\Connectors\Shopify\Services\ShopifyConnector;
use Splash\Tests\Tools\TestCase;

/**
 * Test of Shopify Connector WebHook Controller
 */
class S01WebHookTest extends TestCase
{
    const PING_RESPONSE = '{"success":true}';
    const MEMBER = "ThirdParty";
    const FAKE_EMAIL = "fake@exemple.com";

    /**
     * Test WebHook For Ping
     */
    public function testWebhookPing()
    {
        //====================================================================//
        // Load Connector
        $connector = $this->getConnector("shopify");
        $this->assertInstanceOf(ShopifyConnector::class, $connector);

        //====================================================================//
        // Ping Action -> GET -> OK
        $this->assertPublicActionWorks($connector, null, array(), "GET");
        $this->assertEquals(self::PING_RESPONSE, $this->getResponseContents());

        //====================================================================//
        // Ping Action -> POST -> KO
        $this->assertPublicActionFail($connector, null, array(), "POST");
        //====================================================================//
        // Ping Action -> PUT -> KO
        $this->assertPublicActionFail($connector, null, array(), "PUT");
        //====================================================================//
        // Ping Action -> DELETE -> KO
        $this->assertPublicActionFail($connector, null, array(), "DELETE");
    }

//    /**
//     * Test WebHook with Errors
//     */
//    public function testWebhookErrors()
//    {
//        //====================================================================//
//        // Load Connector
//        $connector = $this->getConnector("Shopify");
//        $this->assertInstanceOf(ShopifyConnector::class, $connector);
//
//        //====================================================================//
//        // Empty Contents
//        //====================================================================//
//
//        $this->assertPublicActionFail($connector, null, array(), "POST");
//
//        //====================================================================//
//        // EVENT BUT NO EMAIL
//        //====================================================================//
//
//        $this->assertPublicActionFail($connector, null, array("event" => "unsubscribed"), "POST");
//
//        //====================================================================//
//        // EMAIOL BUT NO EVENT
//        //====================================================================//
//
//        $this->assertPublicActionFail($connector, null, array("email" => self::FAKE_EMAIL), "POST");
//    }
//
    /**
     * Test WebHook Member Updates
     *
     * @dataProvider webHooksInputsProvider
     *
     * @param string $topic
     * @param array  $data
     * @param string $objectType
     * @param string $action
     * @param string $objectId
     */
    public function testWebhookRequest(string $topic, array $data, string $objectType, string $action, string $objectId)
    {
        //====================================================================//
        // Load Connector
        $connector = $this->getConnector("shopify");
        $this->assertInstanceOf(ShopifyConnector::class, $connector);
        //====================================================================//
        // Setup Client
        $this->configure($connector, $topic);

        //====================================================================//
        // Prepare Request
//        $post  = array_replace_recursive(
//            array("mj_list_id" => $connector->getParameter("ApiList")),
//            $data
//        );
//        $post = $data;
        //dump($data);
        //dump($objectId);

        //====================================================================//
        // Touch Url
        $this->assertPublicActionWorks($connector, null, $data, "POST");
        $this->assertEquals(self::PING_RESPONSE, $this->getResponseContents());

        //====================================================================//
        // Verify Response
        $this->assertIsLastCommited($action, $objectType, $objectId);
    }

    /**
     * Generate Fake Inputs for WebHook Requets
     *
     * @return array
     */
    public function webHooksInputsProvider()
    {
        $hooks = array();

        for ($i = 0; $i < 5; $i++) {
            //====================================================================//
            // Add ThirdParty WebHook Test
            $hooks[] = self::getThirdPartyWebHook(SPL_A_CREATE, "customers/create", uniqid());
            $hooks[] = self::getThirdPartyWebHook(SPL_A_UPDATE, "customers/update", uniqid());
            $hooks[] = self::getThirdPartyWebHook(SPL_A_UPDATE, "customers/disable", uniqid());
            $hooks[] = self::getThirdPartyWebHook(SPL_A_UPDATE, "customers/enable", uniqid());

            //====================================================================//
            // Add Address WebHook Test
            $hooks[] = self::getThirdPartyWebHook(SPL_A_CREATE, "customers/create", uniqid(), uniqid());
            $hooks[] = self::getThirdPartyWebHook(SPL_A_UPDATE, "customers/update", uniqid(), uniqid());
            $hooks[] = self::getThirdPartyWebHook(SPL_A_UPDATE, "customers/disable", uniqid(), uniqid());
            $hooks[] = self::getThirdPartyWebHook(SPL_A_UPDATE, "customers/enable", uniqid(), uniqid());

            //====================================================================//
            // Add Product WebHook Test
            $hooks[] = self::getProductWebHook(SPL_A_CREATE, "products/create", uniqid());
            $hooks[] = self::getProductWebHook(SPL_A_UPDATE, "products/update", uniqid());
            $hooks[] = self::getProductWebHook(SPL_A_DELETE, "products/delete", uniqid());

            //====================================================================//
            // Add Order & Invoices WebHook Test
            $hooks[] = self::getInvoiceWebHook(SPL_A_CREATE, "orders/create", uniqid());
            $hooks[] = self::getInvoiceWebHook(SPL_A_UPDATE, "orders/cancelled", uniqid());
            $hooks[] = self::getInvoiceWebHook(SPL_A_UPDATE, "orders/fulfilled", uniqid());
            $hooks[] = self::getInvoiceWebHook(SPL_A_UPDATE, "orders/paid", uniqid());
            $hooks[] = self::getInvoiceWebHook(SPL_A_UPDATE, "orders/partially_fulfilled", uniqid());
            $hooks[] = self::getInvoiceWebHook(SPL_A_UPDATE, "orders/updated", uniqid());
            $hooks[] = self::getInvoiceWebHook(SPL_A_DELETE, "orders/delete", uniqid());
        }

        return $hooks;
    }

    /**
     * Configure Client Headers for Shopify Requests
     *
     * @param ShopifyConnector $connector
     * @param string           $topic
     */
    private function configure(ShopifyConnector $connector, string $topic)
    {
        $this->getClient()->setServerParameter("HTTP_X-Shopify-Shop-Domain", $connector->getParameter("WsHost"));
        $this->getClient()->setServerParameter("HTTP_X-Shopify-Topic", $topic);
    }

    /**
     * Generate Fake ThirdParty Inputs for WebHook Requets
     *
     * @param string $action
     * @param string $eventName
     * @param string $thirdparty
     * @param string $address
     *
     * @return array
     */
    private static function getThirdPartyWebHook(string $action, string $eventName, string $thirdparty, string $address = null) : array
    {
        return array(
            $eventName,
            array(
                "id" => $thirdparty,
                "addresses" => is_null($address) ? array() : array(array("id" => $address)),
            ),
            is_null($address) ? "ThirdParty" : "Address",
            $action,
            is_null($address) ? $thirdparty : Objects\Address::getObjectId($thirdparty, $address),
        );
    }

    /**
     * Generate Fake Product Inputs for WebHook Requets
     *
     * @param string $action
     * @param string $eventName
     * @param string $variant
     *
     * @return array
     */
    private static function getProductWebHook(string $action, string $eventName, string $variant) : array
    {
        $product = uniqid();

        return array(
            $eventName,
            array(
                "id" => $product,
                "variants" => array(array("id" => $variant)),
            ),
            "Product",
            $action,
            Objects\Product::getObjectId($product, $variant),
        );
    }

    /**
     * Generate Fake Order & Invoice Inputs for WebHook Requets
     *
     * @param string $action
     * @param string $eventName
     * @param string $invoice
     *
     * @return array
     */
    private static function getInvoiceWebHook(string $action, string $eventName, string $invoice) : array
    {
        return array(
            $eventName,
            array(
                "id" => $invoice,
            ),
            "Invoice",
            $action,
            $invoice,
        );
    }
}
