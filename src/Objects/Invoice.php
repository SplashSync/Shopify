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
use Splash\Connectors\Shopify\Services\ShopifyConnector;
use Splash\Models\AbstractObject;
use Splash\Models\Objects\IntelParserTrait;
use Splash\Models\Objects\ListsTrait;
use Splash\Models\Objects\ObjectsTrait;
use Splash\Models\Objects\PricesTrait;
use Splash\Models\Objects\PrimaryKeysAwareInterface;
use Splash\Models\Objects\SimpleFieldsTrait;

/**
 * Shopify Implementation of Customer Invoice
 */
class Invoice extends AbstractObject implements PrimaryKeysAwareInterface
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
    use Order\CoreTrait;
    use Order\MainTrait;
    use Order\ItemsTrait;
    use Order\PrimaryTrait;

    // Shopify Invoices Traits
    use Invoice\StatusTrait;
    use Invoice\PaymentsTrait;
    use Invoice\ObjectsListTrait;

    //====================================================================//
    // Object Definition Parameters
    //====================================================================//

    /**
     * {@inheritdoc}
     */
    protected static string $name = "Customer Invoice";

    /**
     * {@inheritdoc}
     */
    protected static string $description = "Shopify Customers Invoice Object";

    /**
     * {@inheritdoc}
     */
    protected static string $ico = "fa fa-money";

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
    }
}
