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
use Splash\Core\SplashCore      as Splash;
use Splash\Models\Objects;

/**
 * Shopify Implementation of Products
 *
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
class Product extends AbstractStandaloneObject
{
    // Splash Php Core Traits
    use Objects\IntelParserTrait;
    use Objects\SimpleFieldsTrait;
    use Objects\ListsTrait;
    use Objects\ObjectsTrait;

    // Shopify Core Traits
    use Core\DatesTrait;
    use Core\UnitConverterTrait;

    // Shopify Products Traits
    use Product\CRUDTrait;
    use Product\ObjectsListTrait;
    use Product\CoreTrait;
    use Product\ImagesTrait;
    use Product\TagsTrait;
    use Product\VariantsTrait;
    use Product\Variants\MainTrait;
    use Product\Variants\StockTrait;

    //====================================================================//
    // Object Definition Parameters
    //====================================================================//

    /**
     * {@inheritdoc}
     */
    protected static string $name = "Product";

    /**
     * {@inheritdoc}
     */
    protected static string $description = "Shopify Product Object";

    /**
     * {@inheritdoc}
     */
    protected static string $ico = "fa fa-product-hunt";

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
     * @phpstan-var ArrayObject
     */
    protected object $object;

    /**
     * @var ShopifyConnector
     */
    protected ShopifyConnector $connector;

    //====================================================================//
    // General Class Variables
    //====================================================================//

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
}
