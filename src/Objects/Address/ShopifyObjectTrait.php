<?php
/**
 * This file is part of SplashSync Project.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 *  @author    Splash Sync <www.splashsync.com>
 *
 *  @copyright 2015-2017 Splash Sync
 *
 *  @license   GNU GENERAL PUBLIC LICENSE Version 3, 29 June 2007
 *
 **/

namespace Splash\Connectors\Shopify\Objects\Address;

use Splash\Connectors\Shopify\ShopifyObjects\ShopifyAddress;

/**
 * @abstract    Shopify ThirdParty Address Object
 */
trait ShopifyObjectTrait
{

    private $ShopifyAddress     = null;

    /**
     *    Build & Get Shopify Customer
     *
     * @param string $CustomerId Parent Customer Id
     *
     * @return     bool     $result
     */
    public function getShopifyAddress(string $CustomerId)
    {
        
        //====================================================================//
        // Check if Address Object Already Created
        if (!empty($this->ShopifyAddress)) {
            //==============================================================================
            // Setup Shopify Customer Address Prefix
            $this->ShopifyAddress->setCustomerId($CustomerId);

            return $this->ShopifyAddress;
        }
        //==============================================================================
        // Create Shopify Customer Address
        $this->ShopifyAddress = new ShopifyAddress($this->Connector->getShopifyClient());
        //==============================================================================
        // Setup Shopify Customer Address Prefix
        $this->ShopifyAddress->setCustomerId($CustomerId);
        
        return $this->ShopifyAddress;
    }
}
