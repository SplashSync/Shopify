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

namespace Splash\Connectors\Shopify\Services;

use ArrayObject;
use Splash\Bundle\Models\AbstractConnector;
use Splash\Bundle\Models\Connectors\GenericObjectMapperTrait;
use Splash\Bundle\Models\Connectors\GenericWidgetMapperTrait;
use Splash\Connectors\Shopify\Form\ExtendedEditFormType;
use Splash\Connectors\Shopify\Models\ShopifyHelper as API;
use Splash\Connectors\Shopify\Objects;
use Splash\Connectors\Shopify\Objects\WebHook;
use Splash\Core\SplashCore as Splash;
use Splash\Models\Helpers\ImagesHelper;
use Symfony\Component\Routing\RouterInterface;

/**
 * Shopify REST API Connector for Splash
 *
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
class ShopifyConnector extends AbstractConnector
{
    use GenericObjectMapperTrait;
    use GenericWidgetMapperTrait;

    /**
     * Objects Type Class Map
     *
     * @var array
     */
    protected static $objectsMap = array(
        "ThirdParty" => Objects\ThirdParty::class,
        "Address" => Objects\Address::class,
        "Product" => Objects\Product::class,
        "Order" => Objects\Order::class,
        "Invoice" => Objects\Invoice::class,
        //        "WebHooks" => Objects\WebHook::class,
    );

    /**
     * Widgets Type Class Map
     *
     * @var array
     */
    protected static $widgetsMap = array(
        "SelfTest" => "Splash\\Connectors\\Shopify\\Widgets\\SelfTest",
    );

    /**
     * {@inheritdoc}
     */
    public function ping() : bool
    {
        //====================================================================//
        // Safety Check => Verify Self test Pass
        if (!$this->selfTest()) {
            return false;
        }
        //====================================================================//
        // Perform Ping Test
        return API::ping();
    }

    /**
     * {@inheritdoc}
     */
    public function connect() : bool
    {
        //====================================================================//
        // Safety Check => Verify Self test Pass
        if (!$this->selfTest()) {
            return false;
        }
        //====================================================================//
        // Perform Connect Test
        if (!API::connect()) {
            return false;
        }
        //====================================================================//
        // Get Shop Informations
        if (!$this->fetchShopInformations()) {
            return false;
        }
        //====================================================================//
        // Get List of Available Countries
        if (!$this->fetchCountriesLists()) {
            return false;
        }
        //====================================================================//
        // Get List of Available Stock Locations
        if (!$this->fetchLocationsLists()) {
            return false;
        }

        //====================================================================//
        // Update Connector Settings
        $this->updateConfiguration();

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function informations(ArrayObject  $informations) : ArrayObject
    {
        //====================================================================//
        // Safety Check => Verify Self test Pass
        if (!$this->selfTest()) {
            return $informations;
        }
        //====================================================================//
        // Get List Detailed Informations
        $details = API::get('shop', null, array(), 'shop');
        if (is_null($details)) {
            return $informations;
        }
        //====================================================================//
        // Server General Description
        $informations->shortdesc = "Shopify";
        $informations->longdesc = "Splash Integration for Shopify's Rest Api";
        //====================================================================//
        // Company Informations
        $informations->company = $details["shop_owner"];
        $informations->address = $details["address1"];
        $informations->zip = $details["zip"];
        $informations->town = $details["city"];
        $informations->country = $details["country_name"];
        $informations->www = $details["domain"];
        $informations->email = $details["email"];
        $informations->phone = $details["phone"];
        //====================================================================//
        // Server Logo & Ico
        $informations->icoraw = Splash::file()->readFileContents(
            dirname(dirname(__FILE__))."/Resources/public/img/Shopify-Icon.png"
        );
        $informations->logourl = null;
        $informations->logoraw = Splash::file()->readFileContents(
            dirname(dirname(__FILE__))."/Resources/public/img/Shopify-Logo.png"
        );
        //====================================================================//
        // Server Informations
        $informations->servertype = "Shopify REST Api";
        $informations->serverurl = $details["myshopify_domain"];
        //====================================================================//
        // Module Informations
        $informations->moduleauthor = SPLASH_AUTHOR;
        $informations->moduleversion = "master";
        $informations->github = "https://github.com/SplashSync/Shopify";
        $informations->documentation = "https://splashsync.github.io/Shopify/";

        return $informations;
    }

    /**
     * {@inheritdoc}
     */
    public function selfTest() : bool
    {
        $config = $this->getConfiguration();

        //====================================================================//
        // Verify Host is Set
        //====================================================================//
        if (!isset($config["WsHost"]) || empty($config["WsHost"]) || !is_string($config["WsHost"])) {
            return Splash::log()->err("Shop Url is Empty or Invalid");
        }

        //====================================================================//
        // Verify Token is Set
        //====================================================================//
        if (!isset($config["Token"]) || empty($config["Token"]) || !is_string($config["Token"])) {
            return Splash::log()->err("Shop Credential (App Token) is Invalid");
        }

        //====================================================================//
        // Configure Rest API
        return API::configure($config["WsHost"], $config["Token"]);
    }

    /**
     * @return bool
     */
    public function hasValidShopifyHost() : bool
    {
        $config = $this->getConfiguration();

        //====================================================================//
        // Verify Host is Set
        //====================================================================//
        if (!isset($config["WsHost"]) || empty($config["WsHost"]) || !is_string($config["WsHost"])) {
            return false;
        }
        if (!self::isValidShopifyHost($config["WsHost"])) {
            return false;
        }

        return true;
    }

    //====================================================================//
    // Objects Interfaces
    //====================================================================//

    //====================================================================//
    // Files Interfaces
    //====================================================================//

    /**
     * {@inheritdoc}
     */
    public function getFile(string $filePath, string $fileMd5)
    {
        //====================================================================//
        // Safety Check => Verify Self test Pass
        if (!$this->selfTest()) {
            return false;
        }

        //====================================================================//
        // Encode Image Array (Without Raw)
        $response = ImagesHelper::encodeFromUrl("Shopify Image", $filePath, $filePath);
        if (!is_array($response) || ($response["md5"] != $fileMd5)) {
            Splash::log()->err("Unable to read Shopify Image: ".$filePath);

            return false;
        }

        //====================================================================//
        // Load Image Raw Contents form Url
        $response["raw"] = base64_encode((string) file_get_contents($filePath));

        return $response;
    }

    //====================================================================//
    // Profile Interfaces
    //====================================================================//

    /**
     * @abstract   Get Connector Profile Informations
     *
     * @return array
     */
    public function getProfile() : array
    {
        return array(
            'enabled' => true,                                  // is Connector Enabled
            'beta' => false,                                    // is this a Beta release
            'type' => self::TYPE_ACCOUNT,                       // Connector Type or Mode
            'name' => 'shopify',                                // Connector code (lowercase, no space allowed)
            'connector' => 'splash.connectors.shopify',         // Connector Symfony Service
            'title' => 'profile.card.title',                    // Public short name
            'label' => 'profile.card.label',                    // Public long name
            'domain' => 'ShopifyBundle',                        // Translation domain for names
            'ico' => '/bundles/shopify/img/Shopify-Icon.png',   // Public Icon path
            'www' => 'www.Shopify.com',                         // Website Url
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getConnectedTemplate() : string
    {
        return "@Shopify/Profile/connected.html.twig";
    }

    /**
     * {@inheritdoc}
     */
    public function getOfflineTemplate() : string
    {
        return "@Shopify/Profile/offline.html.twig";
    }

    /**
     * {@inheritdoc}
     */
    public function getNewTemplate() : string
    {
        return "@Shopify/Profile/new.html.twig";
    }

    /**
     * {@inheritdoc}
     */
    public function getFormBuilderName() : string
    {
        return ExtendedEditFormType::class;
//        return $this->getParameter("Extended", false) ? ExtendedEditFormType::class : EditFormType::class;
    }

    /**
     * {@inheritdoc}
     */
    public function getMasterAction(): ?string
    {
        return "ShopifyBundle:Actions:register";
    }

    /**
     * {@inheritdoc}
     */
    public function getPublicActions() : array
    {
        return array(
            "index" => "ShopifyBundle:WebHooks:index",
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getSecuredActions() : array
    {
        return array(
            "oauth" => "ShopifyBundle:Actions:oauth",
            "webhooks" => "ShopifyBundle:Actions:webhooks",
        );
    }

    //====================================================================//
    //  HIGH LEVEL WEBSERVICE CALLS
    //====================================================================//

    /**
     * Check & Update Shopify Api Account WebHooks.
     *
     * @return bool
     */
    public function verifyWebHooks() : bool
    {
        //====================================================================//
        // Connector SelfTest
        if (!$this->selfTest()) {
            return false;
        }
        //====================================================================//
        // Generate WebHook Url
        $webHookServer = filter_input(INPUT_SERVER, 'SERVER_NAME');
        //====================================================================//
        // When Running on a Local Server
        if (false !== strpos("localhost", $webHookServer)) {
            $webHookServer = "www.splashsync.com";
        }
        //====================================================================//
        // When Running on a Splash Cloud
        if (false !== strpos("admin.splashsync.com", $webHookServer)) {
            $webHookServer = "www.splashsync.com";
        }
        //====================================================================//
        // Create Object Class
        $webHookManager = new WebHook($this);
        $webHookManager->configure("webhook", $this->getWebserviceId(), $this->getConfiguration());
        //====================================================================//
        // Get List Of WebHooks for this List
        $webHooks = $webHookManager->objectsList();
        if (isset($webHooks["meta"])) {
            unset($webHooks["meta"]);
        }
        //====================================================================//
        // Walk on WebHooks Topics
        foreach (WebHook::getTopics() as $topic) {
            $found = false;

            //====================================================================//
            // Search in WebHooks List
            foreach ($webHooks as $webHook) {
                //====================================================================//
                // Check WebHook is Valid
                if (WebHook::isValid($webHook, $webHookServer, $topic)) {
                    $found = true;
                }
            }

            //====================================================================//
            // WebHooks is Ok
            if ($found) {
                continue;
            }

            return false;
        }

        //====================================================================//
        // All Splash WebHooks were Found
        return true;
    }

    /**
     * Check & Update Shopify Api Account WebHooks.
     *
     * @param RouterInterface $router
     *
     * @return bool
     */
    public function updateWebHooks(RouterInterface $router) : bool
    {
        //====================================================================//
        // Connector SelfTest
        if (!$this->selfTest()) {
            return false;
        }
        //====================================================================//
        // Generate WebHook Url
        $webHookServer = filter_input(INPUT_SERVER, 'SERVER_NAME');
        $webHookUrl = (string) $router->generate(
            'splash_connector_action',
            array(
                'connectorName' => $this->getProfile()["name"],
                'webserviceId' => $this->getWebserviceId(),
            ),
            RouterInterface::ABSOLUTE_URL
        );
        //====================================================================//
        // When Running on a Local Server
        if (false !== strpos("localhost", $webHookServer)) {
            $webHookServer = "www.splashsync.com";
            $webHookUrl = "https://www.splashsync.com/en/ws/Shopify/123456";
        }
        //====================================================================//
        // Create Object Class
        $webHookManager = new WebHook($this);
        $webHookManager->configure("webhook", $this->getWebserviceId(), $this->getConfiguration());
        //====================================================================//
        // Get List Of WebHooks for this List
        $webHooks = $webHookManager->objectsList();
        if (isset($webHooks["meta"])) {
            unset($webHooks["meta"]);
        }
        //====================================================================//
        // Walk on WebHooks Topics
        foreach (WebHook::getTopics() as $topic) {
            //====================================================================//
            // Update Splash WebHook Configuration
            if (false === $this->updateWebHookConfig($webHookManager, $webHooks, $webHookServer, $webHookUrl, $topic)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get Shop Default Vat Rate
     *
     * @return int
     */
    public function getDefaultVatRate()
    {
        //====================================================================//
        // Get Shop Informations
        $storeInfos = $this->getParameter("ShopInformations");
        $countries = $this->getParameter("Countries");
        //====================================================================//
        // Safety Checks
        if (!isset($storeInfos["country"]) || empty($storeInfos["country"]) || !is_array($countries)) {
            return 0;
        }
        //====================================================================//
        // Search for Shop Country Tax
        foreach ($countries as $country) {
            if ($country['code'] == $storeInfos["country"]) {
                return 100 * $country['tax'];
            }
        }
        //====================================================================//
        // Tax was not Found
        return 0;
    }

    /**
     * Get Shop Default Currency
     *
     * @return string
     */
    public function getDefaultCurrency() : string
    {
        //====================================================================//
        // Get Shop Informations
        return (string) $this->getParameter("currency", "EUR", "ShopInformations");
    }

    /**
     * Get Shopify Host Domain.
     *
     * @return string
     */
    public function getShopifyDomain(): string
    {
        $wsHost = (string) $this->getParameter("WsHost");
        //====================================================================//
        // If Url Domain is found
        if (parse_url($wsHost, PHP_URL_HOST)) {
            return (string) parse_url($wsHost, PHP_URL_HOST);
        }
        //====================================================================//
        // Raw Domain was found
        return $wsHost;
    }

    /**
     * Check if Shopify Logistic is Enabled.
     *
     * @return bool
     */
    public function hasLogisticMode(): bool
    {
        return !empty($this->getParameter("LogisticMode"));
    }

    //====================================================================//
    //  LOW LEVEL PRIVATE FUNCTIONS
    //====================================================================//

    /**
     * Validate Shop Url
     *
     * @param string $wsHost
     *
     * @return bool
     */
    public static function isValidShopifyHost(string $wsHost) : bool
    {
        //====================================================================//
        // Url can't be empty
        if (empty($wsHost)) {
            return Splash::log()->err("Shop Url is Empty. Please open configuration and fill your shop Url");
        }
        //====================================================================//
        // Explode Url
        $urlParts = parse_url($wsHost);
        if (!is_array($urlParts)) {
            return Splash::log()->err("Unable to decode Shop Url.");
        }
        //====================================================================//
        // Validate Extra Parts
        if (count(array_keys($urlParts)) > 1) {
            Splash::log()->err("Shop Url should only include shopify admin domain.");

            return Splash::log()->err("Please remove schema (https://), ports (:80), or extra parameters.");
        }
        //====================================================================//
        // Validate Subdomain
        if (!isset($urlParts["path"])) {
            return Splash::log()->err("Unable to detect Shop Url.");
        }
        $urlParts["path"] = strtolower($urlParts["path"]);
        if (false === strpos($urlParts["path"], ".myshopify.com")) {
            return Splash::log()->err("Shop Url must alike your-shop.myshopify.com.");
        }

        return true;
    }

    /**
     * Check & Update Shopify Api Account WebHook Configuration.
     *
     * @param WebHook $manager    Shopify WebHook Splash Manager
     * @param array   $webHooks   Shopify WebHooks List
     * @param string  $serverUrl  Splash Server Url
     * @param string  $webhookUrl Splash WebHook Url
     * @param string  $topic      WebHook Shopify Topic
     *
     * @return bool
     */
    private function updateWebHookConfig(
        WebHook $manager,
        array $webHooks,
        string $serverUrl,
        string $webhookUrl,
        string $topic
    ) : bool {
        //====================================================================//
        // Filter & Clean List Of WebHooks
        $foundWebHook = false;
        foreach ($webHooks as $webHook) {
            //====================================================================//
            // Check WebHook is Valid
            if (WebHook::isValid($webHook, $webhookUrl, $topic)) {
                $foundWebHook = true;

                continue;
            }
            //====================================================================//
            // This is a Splash WebHooks
            if (false !== strpos(trim($webHook['address']), $serverUrl)) {
                $manager->delete($webHook['id']);
            }
        }
        //====================================================================//
        // Splash WebHooks was Found
        if ($foundWebHook) {
            return true;
        }
        //====================================================================//
        // Add Splash WebHooks
        return (false !== $manager->create($webhookUrl, $topic));
    }

    /**
     * Get Shopify Shop Countries Informations
     *
     * @return bool
     */
    private function fetchShopInformations(): bool
    {
        //====================================================================//
        // Get User Lists from Api
        $response = API::get('shop', null, array(), 'shop');
        if (!is_array($response)) {
            return false;
        }
        //====================================================================//
        // Store in Connector Settings
        $this->setParameter("ShopInformations", $response);

        return true;
    }

    /**
     * Get Shopify Shop Countries Informations
     *
     * @return bool
     */
    private function fetchCountriesLists(): bool
    {
        //====================================================================//
        // Get User Lists from Api
        $response = API::get('countries', null, array(), 'countries');
        if (!is_array($response)) {
            return false;
        }
        //====================================================================//
        // Store in Connector Settings
        $this->setParameter("Countries", $response);

        return true;
    }

    /**
     * Get Shopify Shop Locations Informations
     *
     * @return bool
     */
    private function fetchLocationsLists(): bool
    {
        //====================================================================//
        // Get User Lists from Api
        $response = API::get('locations', null, array(), 'locations');
        if (!is_array($response)) {
            return false;
        }
        //====================================================================//
        // Store in Connector Settings
        $locationsMap = array();
        foreach ($response as $location) {
            $locationsMap[$location['id']] = $location['name'];
        }
        //====================================================================//
        // Store in Connector Settings
        $this->setParameter("Locations", $response);
        $this->setParameter("LocationsMap", $locationsMap);

        return true;
    }
}
