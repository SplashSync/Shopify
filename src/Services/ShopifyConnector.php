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

namespace Splash\Connectors\Shopify\Services;

use ArrayObject;
use Exception;
use Psr\Log\LoggerInterface;
use Splash\Bundle\Interfaces\Connectors\PrimaryKeysInterface;
use Splash\Bundle\Models\AbstractConnector;
use Splash\Bundle\Models\Connectors\GenericObjectMapperTrait;
use Splash\Bundle\Models\Connectors\GenericObjectPrimaryMapperTrait;
use Splash\Bundle\Models\Connectors\GenericWidgetMapperTrait;
use Splash\Connectors\Shopify\Controller as Actions;
use Splash\Connectors\Shopify\Form\ExtendedEditFormType;
use Splash\Connectors\Shopify\Models\ConnectorConfigurationsTrait;
use Splash\Connectors\Shopify\Models\ConnectorScopesTrait;
use Splash\Connectors\Shopify\Models\ShopifyHelper as API;
use Splash\Connectors\Shopify\OAuth2\ShopifyAdapter;
use Splash\Connectors\Shopify\Objects;
use Splash\Core\SplashCore as Splash;
use Splash\Models\Helpers\ImagesHelper;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Shopify REST API Connector for Splash
 *
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ShopifyConnector extends AbstractConnector implements PrimaryKeysInterface
{
    use GenericObjectMapperTrait;
    use GenericObjectPrimaryMapperTrait;
    use GenericWidgetMapperTrait;
    use ConnectorConfigurationsTrait;
    use ConnectorScopesTrait;

    /**
     * Objects Type Class Map
     *
     * @var array<string, class-string>
     */
    protected static array $objectsMap = array(
        "ThirdParty" => Objects\ThirdParty::class,
        "Address" => Objects\Address::class,
        "Product" => Objects\Product::class,
        "Order" => Objects\Order::class,
        "Invoice" => Objects\Invoice::class,
        "WebHooks" => Objects\WebHook::class,
    );

    /**
     * Widgets Type Class Map
     *
     * @var array<string, class-string>
     */
    protected static array $widgetsMap = array(
        "SelfTest" => "Splash\\Connectors\\Shopify\\Widgets\\SelfTest",
    );

    /**
     * @var string
     */
    private string $cacheDir;

    /**
     * Class Constructor
     *
     * @param string                   $cacheDir
     * @param EventDispatcherInterface $eventDispatcher
     * @param LoggerInterface          $logger
     *
     * @throws Exception
     */
    public function __construct(
        string $cacheDir,
        protected WebhooksManager $webhooksManager,
        protected ScopesManagers $scopesManagers,
        EventDispatcherInterface $eventDispatcher,
        LoggerInterface $logger
    ) {
        parent::__construct($eventDispatcher, $logger);
        $this->setSplashType("shopify");
        $this->cacheDir = $cacheDir;
    }

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
        // Get Scopes Informations
        if (!$this->fetchAccessScopes()) {
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
        if (empty($config["WsHost"]) || !is_string($config["WsHost"])) {
            return Splash::log()->err("Shop Url is Empty or Invalid");
        }

        //====================================================================//
        // Verify Token is Set
        //====================================================================//
        if (empty($config["Token"]) || !is_string($config["Token"])) {
            return Splash::log()->err("Shop Credential (App Token) is Invalid");
        }
        //====================================================================//
        // Verify Private API Key is Set
        //====================================================================//
        if ($this->hasPrivateAppCredentials()) {
            //====================================================================//
            // Configure PRIVATE API
            return API::configurePrivate(
                $config["WsHost"],
                $config["apiKey"] ?? "",
                $config["Token"],
                $config["apiSecret"] ?? "",
                $this->cacheDir
            );
        }
        //====================================================================//
        // Configure Rest API
        return API::configure($config["WsHost"], $config["Token"], $this->cacheDir);
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
        if (empty($config["WsHost"]) || !is_string($config["WsHost"])) {
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
    public function getFile(string $filePath, string $fileMd5): ?array
    {
        //====================================================================//
        // Safety Check => Verify Self test Pass
        if (!$this->selfTest()) {
            return null;
        }

        //====================================================================//
        // Encode Image Array (Without Raw)
        $response = ImagesHelper::encodeFromUrl("Shopify Image", $filePath, $filePath);
        if (!is_array($response) || ($response["md5"] != $fileMd5)) {
            Splash::log()->err("Unable to read Shopify Image: ".$filePath);

            return null;
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
     * @abstract   Get Connector Profile Information
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
            'uniqueHost' => true                                // Require Unique Host Constraint
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
    }

    /**
     * {@inheritdoc}
     */
    public function getMasterAction(): ?string
    {
        return Actions\OAuth2Master::class;
    }

    /**
     * {@inheritdoc}
     */
    public function getPublicActions() : array
    {
        return array(
            "index" => Actions\WebHooksController::class,
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getSecuredActions() : array
    {
        return array(
            "connect" => Actions\OAuth2Connect::class,
            "webhooks" => Actions\SetupWebhooks::class,
            "refresh" => Actions\OAuth2Refresh::class,
        );
    }

    //====================================================================//
    //  HIGH LEVEL WEBSERVICE CALLS
    //====================================================================//

    /**
     * Try Refresh of Shopify Access Token
     *
     * @param string $refreshToken
     *
     * @return bool
     */
    public function refreshAccessToken(string $refreshToken) : bool
    {
        //====================================================================//
        // Safety Check => Verify Self test Pass
        if (!$this->selfTest() || empty($refreshToken)) {
            return false;
        }
        //====================================================================//
        // Request a New Access Token
        $authConfig = ShopifyAdapter::getConfig();
        $clientConfig = $this->getConfiguration();
        $query = array(
            "client_id" => $authConfig["client_id"],
            "client_secret" => $authConfig["client_secret"],
            "refresh_token" => $refreshToken,
            "access_token" => $clientConfig["Token"],
        );
        $response = API::postRaw("oauth/access_token", array('json' => $query));
        //====================================================================//
        // Store New Access Token
        if ($response && isset($response['access_token'])) {
            $this->setParameter("Token", $response['access_token']);
            //====================================================================//
            // Update Connector Settings
            $this->updateConfiguration();

            return true;
        }

        return false;
    }

    /**
     * Check & Update Shopify Api Account WebHooks.
     *
     * @return bool
     */
    public function verifyWebHooks() : bool
    {
        return $this->webhooksManager->verifyWebHooks($this);
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
     * Get Shopify Shop Countries Information
     *
     * @return bool
     */
    public function fetchShopInformations(): bool
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
     * Get Shopify Shop Countries Information
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
     * Get Shopify Shop Locations Information
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
