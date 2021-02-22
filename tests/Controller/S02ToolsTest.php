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

namespace Splash\Connectors\Shopify\Test\Controller;

use Splash\Connectors\Shopify\Services\ShopifyConnector;
use Splash\Core\SplashCore as Splash;
use Splash\Tests\Tools\TestCase;

/**
 * Test of Shopify Various Tooling Functions
 */
class S02ToolsTest extends TestCase
{
    /**
     * Test Host url validation
     *
     * @dataProvider whHostsUrlProvider
     *
     * @param string $wsHost
     * @param bool   $isValid
     */
    public function testHostValidation(string $wsHost, bool $isValid): void
    {
        $this->assertNotEmpty($wsHost);
        $this->assertEquals(ShopifyConnector::isValidShopifyHost($wsHost), $isValid);

        if (!$isValid) {
            $this->assertNotEmpty(Splash::log()->err);
        }
        if ($isValid) {
            $this->assertEmpty(Splash::log()->err);
        }
    }

    /**
     * Generate Shopify Hosts Url for Validation
     *
     * @return array
     */
    public function whHostsUrlProvider(): array
    {
        $hooks = array();

        //====================================================================//
        // Ok Urls
        $hooks[] = array("exemple.myshopify.com", true);
        $hooks[] = array("sub-domain.myshopify.com", true);
        $hooks[] = array("subdomain33.myshopify.com", true);

        //====================================================================//
        // KO Urls - Extra Parameters
        $hooks[] = array("https://exemple.myshopify.com", false);
        $hooks[] = array("http://exemple.myshopify.com", false);
        $hooks[] = array("exemple.myshopify.com:666", false);
        $hooks[] = array("exemple.myshopify.com?test=false", false);

        //====================================================================//
        // KO Urls - Others
        $hooks[] = array("www.shopify.com", false);
        $hooks[] = array("exemple.shopify.com", false);

        return $hooks;
    }
}
