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

namespace Splash\Connectors\Shopify\Objects;

use ArrayObject;
use Splash\Bundle\Models\AbstractStandaloneObject;
use Splash\Connectors\Shopify\Services\ShopifyConnector;
use Splash\Core\SplashCore as Splash;
use Splash\Models\Objects\IntelParserTrait;
use Splash\Models\Objects\ListsTrait;
use Splash\Models\Objects\ObjectsTrait;
use Splash\Models\Objects\PricesTrait;
use Splash\Models\Objects\PrimaryKeysAwareInterface;
use Splash\Models\Objects\SimpleFieldsTrait;

/**
 * Shopify Implementation of Customer Orders
 */
class Order extends AbstractStandaloneObject implements PrimaryKeysAwareInterface
{
    // Splash Php Core Traits
    use IntelParserTrait;
    use SimpleFieldsTrait;
    use PricesTrait;
    use ObjectsTrait;
    use ListsTrait;

    // Shopify Core Traits
    use Core\DatesTrait;
    use Core\MetadataTrait;

    // Shopify Orders Traits
    use Order\CRUDTrait;
    use Order\ObjectsListTrait;
    use Order\PrimaryTrait;
    use Order\CoreTrait;
    use Order\MainTrait;
    use Order\StatusTrait;
    use Order\StatusFlagsTrait;
    use Order\ItemsTrait;
    use Order\DeliveryTrait;
    use Order\ShippingTrait;
    use Order\FulfillmentTrait;
    use Order\LogisticModeTrait;

    //====================================================================//
    // Object Definition Parameters
    //====================================================================//

    /**
     * {@inheritdoc}
     */
    protected static string $name = "Customer Order";

    /**
     * {@inheritdoc}
     */
    protected static string $description = "Shopify Customers Order Object";

    /**
     * {@inheritdoc}
     */
    protected static string $ico = "fa fa-shopping-cart ";

    //====================================================================//
    // Object Synchronization Limitations
    // This Flags are Used by Splash Server to Prevent Unexpected Operations on Remote Server
    //====================================================================//

    /**
     * {@inheritdoc}
     */
    protected static bool $allowPushCreated = false;

    /**
     * {@inheritdoc}
     */
    protected static bool $allowPushUpdated = false;

    /**
     * {@inheritdoc}
     */
    protected static bool $allowPushDeleted = false;

    //====================================================================//
    // Object Synchronization Recommended Configuration
    //
    // This Flags are Used by Splash Server to Setup Default Objects Configuration
    //====================================================================//

    /**
     * {@inheritdoc}
     */
    protected static bool $enablePushCreated = false;

    /**
     * {@inheritdoc}
     */
    protected static bool $enablePushUpdated = false;

    /**
     * {@inheritdoc}
     */
    protected static bool $enablePushDeleted = false;

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
     * @param ShopifyConnector $connector
     */
    public function __construct(ShopifyConnector $connector)
    {
        $this->connector = $connector;
        //====================================================================//
        //  Load Translation File
        Splash::translator()->load('local');
        Splash::translator()->load('objects');
    }

    /**
     * {@inheritdoc}
     */
    public function description(): array
    {
        self::$allowPushUpdated = $this->connector->hasLogisticMode();
        self::$enablePushUpdated = self::$allowPushUpdated;

        return parent::description();
    }
}
