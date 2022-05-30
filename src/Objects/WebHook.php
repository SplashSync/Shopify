<?php

/*
 *  This file is part of SplashSync Project.
 *
 *  Copyright (C) 2015-2021 Splash Sync  <www.splashsync.com>
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace Splash\Connectors\Shopify\Objects;

use ArrayObject;
use Splash\Bundle\Models\AbstractStandaloneObject;
use Splash\Connectors\Shopify\Services\ShopifyConnector;
use Splash\Models\Objects\IntelParserTrait;
use Splash\Models\Objects\SimpleFieldsTrait;

/**
 * Shopify Implementation of WebHooks
 */
class WebHook extends AbstractStandaloneObject
{
    use IntelParserTrait;
    use SimpleFieldsTrait;
    use WebHook\CRUDTrait;
    use WebHook\CoreTrait;
    use WebHook\ObjectsListTrait;

    /**
     * {@inheritdoc}
     */
    protected static bool $disabled = true;

    /**
     * {@inheritdoc}
     */
    protected static string $name = "WebHook";

    /**
     * {@inheritdoc}
     */
    protected static string $description = "Shopify WebHook";

    /**
     * {@inheritdoc}
     */
    protected static string $ico = "fa fa-cogs";

    /**
     * @phpstan-var ArrayObject
     */
    protected object $object;

    /**
     * @var ShopifyConnector
     */
    protected ShopifyConnector $connector;

    /**
     * Class Constructor
     *
     * @param ShopifyConnector $parentConnector
     */
    public function __construct(ShopifyConnector $parentConnector)
    {
        $this->connector = $parentConnector;
    }

    /**
     * Check if WebHook Has Requested Parameters.
     *
     * @param array $webHook Shopify WebHook Object
     * @param string $url Splash WebHook Url
     * @param string|null $topic WebHook Shopify Topic
     *
     * @return bool
     */
    public static function isValid(array $webHook, string $url, string $topic = null) : bool
    {
        //====================================================================//
        // This is a Splash WebHooks
        if (false === strpos(trim($webHook['address']), $url)) {
            return false;
        }
        //====================================================================//
        // Check Topic
        if (!in_array($webHook['topic'], array_keys(static::$topics), true)) {
            return false;
        }
        if ((null !== $topic) && ($webHook['topic'] != $topic)) {
            return false;
        }

        return true;
    }
}
