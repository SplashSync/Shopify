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

use Splash\Connectors\Shopify\Models\ShopifyHelper as API;
use Splash\Core\SplashCore      as Splash;
use ArrayObject;

/**
 * Shopify WebHook CRUD Functions
 */
trait CRUDTrait
{
    /**
     * Load Request Object
     *
     * @param string $objectId Object id
     *
     * @return false|ArrayObject
     */
    public function load($objectId)
    {
        //====================================================================//
        // Stack Trace
        Splash::log()->trace(__CLASS__, __FUNCTION__);
        //====================================================================//
        // Execute Read Request
        $shWebHook = API::get("webhooks", $objectId, array(), "webhook");

        //====================================================================//
        // Fetch Object
        if (null == $shWebHook) {
            return Splash::log()->err("ErrLocalTpl", __CLASS__, __FUNCTION__, " Unable to load WebHook (".$objectId.").");
        }
        
        return new ArrayObject($shWebHook, ArrayObject::ARRAY_AS_PROPS);
    }

    /**
     * Create Request Object
     *
     * @param string $url
     * @param string $topic
     *
     * @return false|stdClass New Object
     */
    public function create(string $url = null, string $topic = null)
    {
        //====================================================================//
        // Stack Trace
        Splash::log()->trace(__CLASS__, __FUNCTION__);
        
        //====================================================================//
        // Check WebHook Url is given
        if (empty($url) && empty($this->in["address"])) {
            return Splash::log()->err("ErrLocalFieldMissing", __CLASS__, __FUNCTION__, "address");
        }
        $webhookUrl = empty($url) ? $this->in["address"] : $url;
        //====================================================================//
        // Check WebHook Topic is given
        if (empty($topic) && empty($this->in["topic"])) {
            return Splash::log()->err("ErrLocalFieldMissing", __CLASS__, __FUNCTION__, "topic");
        }
        $webhookTopic = empty($topic) ? $this->in["topic"] : $topic;
        
        //====================================================================//
        // Create Object
        $newWebhook = API::post('webhooks', self::getWebHooksConfiguration($webhookUrl, $webhookTopic), "webhook");
        if (is_null($newWebhook)) {
            return Splash::log()->err("ErrLocalTpl", __CLASS__, __FUNCTION__, " Unable to Create WebHook");
        }
        
        return new ArrayObject($newWebhook, ArrayObject::ARRAY_AS_PROPS);
    }
    
    /**
     * Update Request Object
     *
     * @param bool $needed Is This Update Needed
     *
     * @return false|string Object Id of False if Failed to Update
     */
    public function update(bool $needed)
    {
        //====================================================================//
        // Stack Trace
        Splash::log()->trace(__CLASS__, __FUNCTION__);
        if (!$needed) {
            return (string) $this->object->id;
        }
        
        //====================================================================//
        // Update WebHook
        if (true == SPLASH_DEBUG) {
            $response = API::put(self::getUri($this->object->id), array("webhook" => $this->object));
            if (null === $response) {
                return Splash::log()->err("ErrLocalTpl", __CLASS__, __FUNCTION__, " Unable to Update WebHook (".$this->object->id.").");
            }
        }
        
        //====================================================================//
        // Update Not Allowed
        Splash::log()->war("ErrLocalTpl", __CLASS__, __FUNCTION__, " WebHook Update is disabled.");
        
        return (string) $this->object->id;
    }
    
    /**
     * Delete requested Object
     *
     * @param string $objectId Object Id
     *
     * @return bool
     */
    public function delete($objectId = null)
    {
        //====================================================================//
        // Stack Trace
        Splash::log()->trace(__CLASS__, __FUNCTION__);
        //====================================================================//
        // Delete Object
        $response = API::delete(self::getUri($objectId));
        if (null === $response) {
            return Splash::log()->err("ErrLocalTpl", __CLASS__, __FUNCTION__, " Unable to Delete WebHook (".$objectId.").");
        }

        return true;
    }
    
    /**
     * Get Object CRUD Uri
     *
     * @param string $objectId
     *
     * @return string
     */
    private static function getUri(string $objectId = null) : string
    {
        $baseUri = 'webhooks';
        if (!is_null($objectId)) {
            return $baseUri."/".$objectId;
        }

        return $baseUri;
    }
    
    /**
     * Get New WebHooks Configuration
     *
     * @param string $webhookUrl
     * @param string $webhookTopic
     *
     * @return array
     */
    private static function getWebHooksConfiguration(string $webhookUrl, string $webhookTopic) : array
    {
        return array(
            "webhook" => array(
                'address' => $webhookUrl,
                'topic' => $webhookTopic,
                'format' => 'json'
            )
        );
    }
}
