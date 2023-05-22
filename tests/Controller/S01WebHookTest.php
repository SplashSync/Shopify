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

namespace Splash\Connectors\Shopify\Test\Controller;

use Splash\Connectors\Shopify\OAuth2\RequestVerifier;
use Splash\Connectors\Shopify\OAuth2\ShopifyAdapter;
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
    const METHOD = "JSON";

    /**
     * Test WebHook For Ping
     */
    public function testWebhookPing(): void
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
        $this->assertPublicActionFail($connector, null, array(), self::METHOD);
        //====================================================================//
        // Ping Action -> PUT -> KO
        $this->assertPublicActionFail($connector, null, array(), "PUT");
        //====================================================================//
        // Ping Action -> DELETE -> KO
        $this->assertPublicActionFail($connector, null, array(), "DELETE");
    }

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
     *
     * @return void
     */
    public function testWebhookRequest(
        string $topic,
        array $data,
        string $objectType,
        string $action,
        string $objectId
    ): void {
        //====================================================================//
        // Load Connector
        $connector = $this->getConnector("shopify");
        $this->assertInstanceOf(ShopifyConnector::class, $connector);
        //====================================================================//
        // Setup Client
        $this->configure($connector, $topic, $data);

        //====================================================================//
        // POST MODE
        $this->assertPublicActionWorks($connector, null, $data, "POST");
        $this->assertEquals(self::PING_RESPONSE, $this->getResponseContents());
        $this->assertIsLastCommitted($action, $objectType, $objectId);

        //====================================================================//
        // JSON POST MODE
        $this->assertPublicActionWorks($connector, null, $data, self::METHOD);
        $this->assertEquals(self::PING_RESPONSE, $this->getResponseContents());
        $this->assertIsLastCommitted($action, $objectType, $objectId);
    }

    /**
     * Test Mandatory WebHooks Updates
     *
     * @dataProvider webHooksMandatoryInputsProvider
     *
     * @param string $topic
     * @param array  $data
     *
     * @return void
     */
    public function testMandatoryWebhookRequest(
        string $topic,
        array $data
    ): void {
        //====================================================================//
        // Load Connector
        $connector = $this->getConnector("shopify");
        $this->assertInstanceOf(ShopifyConnector::class, $connector);

        //====================================================================//
        // Setup Client (Without Security HMAC)
        $this->configure($connector, $topic, null);
        //====================================================================//
        // POST MODE => 401 ERROR
        $this->assertRouteFail(
            "splash_connector_shopify_mandatory_webhooks",
            array(),
            $data,
            "POST"
        );
        $this->assertNotEmpty($this->getResponseContents());
        //====================================================================//
        // Setup Client (With Security HMAC)
        $this->configure($connector, $topic, $data);
        //====================================================================//
        // POST MODE
        $this->assertRouteWorks(
            "splash_connector_shopify_mandatory_webhooks",
            array(),
            $data,
            "POST"
        );
        $this->assertNotEmpty($this->getResponseContents());
        //====================================================================//
        // JSON POST MODE
        $this->assertRouteWorks(
            "splash_connector_shopify_mandatory_webhooks",
            array(),
            $data,
            "JSON"
        );
        $this->assertNotEmpty($this->getResponseContents());
        //====================================================================//
        // Setup Client (With Security HMAC)
        $this->configure($connector, $topic, array());
        //====================================================================//
        // EMPTY DATA => ERROR
        $this->assertRouteFail(
            "splash_connector_shopify_mandatory_webhooks",
            array(),
            array(),
            "JSON"
        );
    }

    /**
     * Generate Fake Inputs for WebHook Requests
     *
     * @return array
     */
    public function webHooksInputsProvider(): array
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
     * Generate Fake Inputs for Mandatory WebHook Requests
     *
     * @return array
     */
    public function webHooksMandatoryInputsProvider(): array
    {
        $hooks = array();

        //====================================================================//
        // GPDR Customer Data Request
        $hooks[] = array(
            "customers/data_request",
            array(
                "shop_id" => 954889,
                "shop_domain" => "{shop}.myshopify.com",
                "orders_requested" => array(299938, 280263, 220458),
                "customer" => array("id" => 191167, "email" => "john@example.com", "phone" => "555-625-1199"),
                "data_request" => array("id" => 9999)
            )
        );

        //====================================================================//
        // GPDR Customer Redact
        $hooks[] = array(
            "customers/redact",
            array(
                "shop_id" => 954889,
                "shop_domain" => "{shop}.myshopify.com",
                "orders_requested" => array(299938, 280263, 220458),
                "customer" => array("id" => 191167, "email" => "john@example.com", "phone" => "555-625-1199"),
                "data_request" => array("id" => 9999)
            )
        );

        //====================================================================//
        // GPDR Shop Redact
        $hooks[] = array(
            "shop/redact",
            array(
                "shop_id" => 954889,
                "shop_domain" => "{shop}.myshopify.com"
            )
        );

        return $hooks;
    }

    /**
     * Configure Client Headers for Shopify Requests
     *
     * @param ShopifyConnector $connector
     * @param string           $topic
     *
     * @return void
     */
    private function configure(ShopifyConnector $connector, string $topic, ?array $data = null): void
    {
        $wsHost = $connector->getParameter("WsHost");
        $this->assertIsString($wsHost);
        $this->getTestClient()->setServerParameter("HTTP_X-Shopify-Shop-Domain", $wsHost);
        $this->getTestClient()->setServerParameter("HTTP_X-Shopify-Topic", $topic);
        if (is_array($data)) {
            $this->getTestClient()->setServerParameter(
                "HTTP_X_SHOPIFY_HMAC_SHA256",
                (string) RequestVerifier::getRequestHmac(
                    ShopifyAdapter::getConfig()["client_secret"],
                    (string) json_encode($data)
                )
            );
        }
    }

    /**
     * Generate Fake ThirdParty Inputs for WebHook Request
     *
     * @param string      $action
     * @param string      $eventName
     * @param string      $thirdParty
     * @param null|string $address
     *
     * @return array
     */
    private static function getThirdPartyWebHook(
        string $action,
        string $eventName,
        string $thirdParty,
        string $address = null
    ) : array {
        return array(
            $eventName,
            array(
                "id" => $thirdParty,
                "addresses" => is_null($address) ? array() : array(array("id" => $address)),
            ),
            is_null($address) ? "ThirdParty" : "Address",
            $action,
            is_null($address) ? $thirdParty : Objects\Address::getObjectId($thirdParty, $address),
        );
    }

    /**
     * Generate Fake Product Inputs for WebHook Request
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
     * Generate Fake Order & Invoice Inputs for WebHook Request
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
