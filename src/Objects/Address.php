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

use Splash\Connectors\Shopify\Services\ShopifyConnector;
use Splash\Models\AbstractObject;
use Splash\Models\Objects\IntelParserTrait;
use Splash\Models\Objects\ObjectsTrait;
use Splash\Models\Objects\SimpleFieldsTrait;

/**
 * Shopify Implementation of ThirdParty Address
 *
 * @SuppressWarnings(PHPMD.CamelCasePropertyName)
 */
class Address extends AbstractObject
{
    // Splash Php Core Traits
    use IntelParserTrait;
    use SimpleFieldsTrait;
    use ObjectsTrait;

    // Shopify Customer Traits
    use Address\CRUDTrait;
    use Address\ObjectsListTrait;
    use Address\CoreTrait;
    use Address\MainTrait;

    /**
     * {@inheritdoc}
     */
    protected static $DISABLED = false;

    /**
     * {@inheritdoc}
     */
    protected static $NAME = "Customer Address";

    /**
     * {@inheritdoc}
     */
    protected static $DESCRIPTION = "Shopify Customer Address";

    /**
     * {@inheritdoc}
     */
    protected static $ICO = "fa fa-envelope-o";

    //====================================================================//
    // Object Synchronization Recommended Configuration
    //
    // This Flags are Used by Splash Server to Setup Default Objects Configuration
    //====================================================================//

    /**
     * Enable Creation Of New Local Objects when Not Existing
     *
     * @var bool
     *
     * @codingStandardsIgnoreStart
     */
    protected static $ENABLE_PUSH_CREATED = false;

    /**
     * @codingStandardsIgnoreEnd
     *
     * @var ShopifyConnector
     */
    protected $connector;

    /**
     * Class Constructor
     *
     * @param ShopifyConnector $connector
     */
    public function __construct(ShopifyConnector $connector)
    {
        $this->connector = $connector;
    }

    /**
     * Extract Customer Id from Splash Address Id
     *
     * @param string $objectId
     *
     * @return null|string
     */
    public static function getCustomerId(string $objectId) : ?string
    {
        $array = explode("-", $objectId);

        return isset($array[1]) ? $array[0] : null;
    }

    /**
     * Extract Address Id from Splash Address Id
     *
     * @param string $objectId
     *
     * @return null|string
     */
    public static function getAddressId(string $objectId) : ?string
    {
        $array = explode("-", $objectId);

        return isset($array[1]) ? $array[1] : null;
    }

    /**
     * Encode Splash Address Id from Shopify Customer && Address Id
     *
     * @param string $customerId
     * @param string $addressId
     *
     * @return string
     */
    public static function getObjectId(string $customerId, string $addressId) : string
    {
        return $customerId."-".$addressId;
    }
}
