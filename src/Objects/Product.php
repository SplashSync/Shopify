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

use Splash\Bundle\Models\AbstractStandaloneObject;
use Splash\Connectors\Shopify\Services\ShopifyConnector;
use Splash\Core\SplashCore      as Splash;
use Splash\Models\Objects;

/**
 * Shopify Implementation of Products
 *
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.CamelCasePropertyName)
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
    use Product\VariantsTrait;
    use Product\Variants\MainTrait;
    use Product\Variants\StockTrait;

    //====================================================================//
    // Object Definition Parameters
    //====================================================================//

    /**
     * {@inheritdoc}
     */
//    protected static    $DISABLED        =  True;

    /**
     * {@inheritdoc}
     */
    protected static $NAME = "Product";

    /**
     * {@inheritdoc}
     */
    protected static $DESCRIPTION = "Shopify Product Object";

    /**
     * {@inheritdoc}
     */
    protected static $ICO = "fa fa-product-hunt";

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
        Splash::translator()->load('objects');
    }
}
