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
use Splash\Connectors\Shopify\Objects\Core\DatesTrait;
use Splash\Connectors\Shopify\Services\ShopifyConnector;
use Splash\Models\AbstractObject;
use Splash\Models\Objects\IntelParserTrait;
use Splash\Models\Objects\SimpleFieldsTrait;

/**
 * Shopify Implementation of ThirdParty
 */
class ThirdParty extends AbstractObject
{
    // Splash Php Core Traits
    use IntelParserTrait;
    use SimpleFieldsTrait;

    // Shopify Core Traits
    use DatesTrait;

    // Shopify Customer Traits
    use ThirdParty\CRUDTrait;
    use ThirdParty\ObjectsListTrait;
    use ThirdParty\CoreTrait;
    use ThirdParty\MainTrait;
    use ThirdParty\AddressTrait;

    /**
     * {@inheritdoc}
     */
    protected static bool $disabled = false;

    /**
     * {@inheritdoc}
     */
    protected static string $name = "Customer";

    /**
     * {@inheritdoc}
     */
    protected static string $description = "Shopify Customer";

    /**
     * {@inheritdoc}
     */
    protected static string $ico = "fa fa-user";

    //====================================================================//
    // Object Synchronization Recommended Configuration
    //
    // This Flags are Used by Splash Server to Setup Default Objects Configuration
    //====================================================================//

    /**
     * Enable Creation Of New Local Objects when Not Existing
     *
     * @var bool
     */
    protected static bool $enablePushCreated = false;

    /**
     * @phpstan-var  ArrayObject
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
