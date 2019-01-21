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

namespace Splash\Connectors\Shopify\Services;

use ArrayObject;
use Splash\Bundle\Models\AbstractConnector;
use Splash\Connectors\Shopify\Form\EditFormType;
use Splash\Connectors\Shopify\Models\ShopifyHelper as API;
use Splash\Connectors\Shopify\Objects\WebHook;
use Splash\Core\SplashCore as Splash;
use Symfony\Component\Routing\RouterInterface;

/**
 * Shopify REST API Connector for Splash
 *
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
class ShopifyConnector extends AbstractConnector
{
    use \Splash\Bundle\Models\Connectors\GenericObjectMapperTrait;
    use \Splash\Bundle\Models\Connectors\GenericWidgetMapperTrait;
    
    /**
     * Objects Type Class Map
     *
     * @var array
     */
    protected static $objectsMap = array(
//        "ThirdParty" => "Splash\\Connectors\\Shopify\\Objects\\ThirdParty",
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
        // Safety Check => Verify Selftest Pass
        if (!$this->selfTest()) {
            return false;
        }
//        //====================================================================//
//        // Perform Ping Test
//        return API::ping();
    }

    /**
     * {@inheritdoc}
     */
    public function connect() : bool
    {
        //====================================================================//
        // Safety Check => Verify Selftest Pass
        if (!$this->selfTest()) {
            return false;
        }
//        //====================================================================//
//        // Perform Connect Test
//        if (!API::connect()) {
//            return false;
//        }
//        //====================================================================//
//        // Get List of Available Lists
//        if (!$this->fetchMailingLists()) {
//            return false;
//        }
//        //====================================================================//
//        // Get List of Available Members Properties
//        if (!$this->fetchAttributesLists()) {
//            return false;
//        }
        
        return true;
    }
        
    /**
     * {@inheritdoc}
     */
    public function informations(ArrayObject  $informations) : ArrayObject
    {
        $config = $this->getConfiguration();
//        //====================================================================//
//        // Safety Check => Verify Selftest Pass
//        if (!$this->selfTest() || empty($config["ApiList"])) {
//            return $informations;
//        }
//        //====================================================================//
//        // Get List Detailed Informations
//        $details  =   API::get('account');
//        if (is_null($details)) {
            return $informations;
//        }

        //====================================================================//
        // Server General Description
        $informations->shortdesc        =   "Shopify";
        $informations->longdesc         =   "Splash Integration for Shopify's Api V3.0";
        //====================================================================//
        // Company Informations
        $informations->company          =   $details->companyName;
        $informations->address          =   $details->address->street;
        $informations->zip              =   $details->address->zipCode;
        $informations->town             =   $details->address->city;
        $informations->country          =   $details->address->country;
        $informations->www              =   "www.Shopify.com";
        $informations->email            =   $details->email;
        $informations->phone            =   "~";
        //====================================================================//
        // Server Logo & Ico
        $informations->icoraw           =   Splash::file()->readFileContents(dirname(dirname(__FILE__))."/Resources/public/img/Shopify-Logo.jpg");
        $informations->logourl          =   null;
        $informations->logoraw          =   Splash::file()->readFileContents(dirname(dirname(__FILE__))."/Resources/public/img/Shopify-Logo.jpg");
        //====================================================================//
        // Server Informations
        $informations->servertype       =   "Shopify REST Api V3";
        $informations->serverurl        =   API::ENDPOINT;
        //====================================================================//
        // Module Informations
        $informations->moduleauthor     =   SPLASH_AUTHOR;
        $informations->moduleversion    =   "master";

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
            Splash::log()->err("Shop Url is Invalid");

            return false;
        }

        //====================================================================//
        // Verify Token is Set
        //====================================================================//
        if (!isset($config["Token"]) || empty($config["Token"]) || !is_string($config["Token"])) {
            Splash::log()->err("Shop Creditial (App Token) is Invalid");

            return false;
        }
        
        //====================================================================//
        // Configure Rest API
        return API::configure(
            $config["ApiKey"],
            isset($config["ApiList"]) ? $config["ApiList"] : null
        );
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
        // Safety Check => Verify Selftest Pass
        if (!$this->selfTest()) {
            return false;
        }
        Splash::log()->err("There are No Files Reading for Shopify Up To Now!");

        return false;
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
            'enabled'   =>      true,                                   // is Connector Enabled
            'beta'      =>      false,                                  // is this a Beta release
            'type'      =>      self::TYPE_ACCOUNT,                     // Connector Type or Mode
            'name'      =>      'shopify',                           // Connector code (lowercase, no space allowed)
            'connector' =>      'splash.connectors.shopify',         // Connector Symfony Service
            'title'     =>      'profile.card.title',                   // Public short name
            'label'     =>      'profile.card.label',                   // Public long name
            'domain'    =>      'ShopifyBundle',                     // Translation domain for names
            'ico'       =>      'bundles/shopify/img/Shopify-Icon.png', // Public Icon path
            'www'       =>      'www.Shopify.com',                   // Website Url
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
        return EditFormType::class;
    }

    /**
     * {@inheritdoc}
     */
    public function getMasterAction()
    {
        return null;
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
        $webHookServer  =   filter_input(INPUT_SERVER, 'SERVER_NAME');
        //====================================================================//
        // When Running on a Local Server
        if (false !== strpos("localhost", $webHookServer)) {
            $webHookServer  =   "www.splashsync.com";
        }
        //====================================================================//
        // Create Object Class
        $webHookManager = new WebHook($this);
        $webHookManager->configure("webhook", $this->getWebserviceId(), $this->getConfiguration());
        //====================================================================//
        // Get List Of WebHooks for this List
        $webHooks       =   $webHookManager->objectsList();
        if (isset($webHooks["meta"])) {
            unset($webHooks["meta"]);
        }
        //====================================================================//
        // Filter & Clean List Of WebHooks
        foreach ($webHooks as $webHook) {
            //====================================================================//
            // This is a Splash WebHooks
            if (false !== strpos(trim($webHook['url']), $webHookServer)) {
                return true;
            }
        }
        //====================================================================//
        // Splash WebHooks was NOT Found
        return false;
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
        $webHookServer  =   filter_input(INPUT_SERVER, 'SERVER_NAME');
        $webHookUrl     =   $router->generate(
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
            $webHookServer  =   "www.splashsync.com";
            $webHookUrl     =   "https://www.splashsync.com/en/ws/Shopify/123456";
        }
        //====================================================================//
        // Create Object Class
        $webHookManager = new WebHook($this);
        $webHookManager->configure("webhook", $this->getWebserviceId(), $this->getConfiguration());
        //====================================================================//
        // Get List Of WebHooks for this List
        $webHooks       =   $webHookManager->objectsList();
        if (isset($webHooks["meta"])) {
            unset($webHooks["meta"]);
        }
        //====================================================================//
        // Filter & Clean List Of WebHooks
        $foundWebHook   =    false;
        foreach ($webHooks as $webHook) {
            //====================================================================//
            // This is Current Node WebHooks
            if (trim($webHook['url']) ==  $webHookUrl) {
                $foundWebHook   =   true;

                continue;
            }
            //====================================================================//
            // This is a Splash WebHooks
            if (false !== strpos(trim($webHook['url']), $webHookServer)) {
                $webHookManager->delete($webHook['id']);
            }
        }
        //====================================================================//
        // Splash WebHooks was Found
        if ($foundWebHook) {
            return true;
        }
        //====================================================================//
        // Add Splash WebHooks
        return (false !== $webHookManager->create($webHookUrl));
    }
    
    //====================================================================//
    //  LOW LEVEL PRIVATE FUNCTIONS
    //====================================================================//
    
    /**
     * Get Shopify User Lists
     *
     * @return bool
     */
    private function fetchMailingLists()
    {
        //====================================================================//
        // Get User Lists from Api
        $response  =   API::get('contacts/lists');
        if (is_null($response)) {
            return false;
        }
        if (!isset($response->lists)) {
            return false;
        }
        //====================================================================//
        // Parse Lists to Connector Settings
        $listIndex = array();
        foreach ($response->lists as $listDetails) {
            //====================================================================//
            // Add List Index
            $listIndex[$listDetails->id]  =   $listDetails->name;
        }
        //====================================================================//
        // Store in Connector Settings
        $this->setParameter("ApiListsIndex", $listIndex);
        $this->setParameter("ApiListsDetails", $response->lists);
        //====================================================================//
        // Update Connector Settings
        $this->updateConfiguration();
        
        return true;
    }
    
    /**
     * Get Shopify User Attributes Lists
     *
     * @return bool
     */
    private function fetchAttributesLists()
    {
        //====================================================================//
        // Get User Lists from Api
        $response  =   API::get('contacts/attributes');
        if (is_null($response)) {
            return false;
        }
        // @codingStandardsIgnoreStart
        if (!isset($response->attributes)) {
            return false;
        }
        //====================================================================//
        // Store in Connector Settings
        $this->setParameter("ContactAttributes", $response->attributes);
        // @codingStandardsIgnoreEnd
        //====================================================================//
        // Update Connector Settings
        $this->updateConfiguration();

        return true;
    }
}
