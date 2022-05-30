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

use ArrayObject;
use Splash\Connectors\Shopify\Models\ShopifyHelper as API;
use Splash\Core\SplashCore      as Splash;

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
     * @return null|ArrayObject
     */
    public function load(string $objectId): ?ArrayObject
    {
        //====================================================================//
        // Stack Trace
        Splash::log()->trace();
        //====================================================================//
        // Execute Read Request
        $shWebHook = API::get("webhooks", $objectId, array(), "webhook");

        //====================================================================//
        // Fetch Object
        if (null == $shWebHook) {
            return Splash::log()->errNull(" Unable to load WebHook (".$objectId.").");
        }

        return new ArrayObject($shWebHook, ArrayObject::ARRAY_AS_PROPS);
    }

    /**
     * Create Request Object
     *
     * @param null|string $url
     * @param null|string $topic
     *
     * @return null|ArrayObject
     */
    public function create(string $url = null, string $topic = null): ?ArrayObject
    {
        //====================================================================//
        // Stack Trace
        Splash::log()->trace();

        //====================================================================//
        // Check WebHook Url is given
        if (empty($url) && empty($this->in["address"])) {
            Splash::log()->err("ErrLocalFieldMissing", __CLASS__, __FUNCTION__, "address");

            return null;
        }
        /** @var string $webhookUrl */
        $webhookUrl = empty($url) ? $this->in["address"] : $url;
        //====================================================================//
        // Check WebHook Topic is given
        if (empty($topic) && empty($this->in["topic"])) {
            Splash::log()->err("ErrLocalFieldMissing", __CLASS__, __FUNCTION__, "topic");

            return null;
        }
        /** @var string $webhookTopic */
        $webhookTopic = empty($topic) ? $this->in["topic"] : $topic;

        //====================================================================//
        // Create Object
        $newWebhook = API::post('webhooks', self::getWebHooksConfiguration($webhookUrl, $webhookTopic), "webhook");
        if (is_null($newWebhook)) {
            return Splash::log()->errNull(" Unable to Create WebHook");
        }

        return new ArrayObject($newWebhook, ArrayObject::ARRAY_AS_PROPS);
    }

    /**
     * Update Request Object
     *
     * @param bool $needed Is This Update Needed
     *
     * @return null|string Object ID of NULL if Failed to Update
     */
    public function update(bool $needed): ?string
    {
        //====================================================================//
        // Stack Trace
        Splash::log()->trace();
        if (!$needed) {
            return $this->getObjectIdentifier();
        }

        //====================================================================//
        // Update WebHook
        if (Splash::isDebugMode()) {
            $response = API::put(self::getUri($this->object->id), array("webhook" => $this->object));
            if (null === $response) {
                return Splash::log()->errNull(" Unable to Update WebHook (".$this->object->id.").");
            }
        }

        //====================================================================//
        // Update Not Allowed
        Splash::log()->war("ErrLocalTpl", __CLASS__, __FUNCTION__, " WebHook Update is disabled.");

        return $this->getObjectIdentifier();
    }

    /**
     * Delete requested Object
     *
     * @param string $objectId Object ID
     *
     * @return bool
     */
    public function delete(string $objectId): bool
    {
        //====================================================================//
        // Stack Trace
        Splash::log()->trace();
        //====================================================================//
        // Delete Object
        $response = API::delete(self::getUri($objectId));
        if (null === $response) {
            return Splash::log()->errTrace(" Unable to Delete WebHook (".$objectId.").");
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function getObjectIdentifier(): ?string
    {
        if (!isset($this->object->id)) {
            return null;
        }

        return (string) $this->object->id;
    }

    /**
     * Get Object CRUD Uri
     *
     * @param null|string $objectId
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
                'format' => 'json',
            ),
        );
    }
}
