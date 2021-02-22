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

namespace Splash\Connectors\Shopify\Models;

use League\OAuth2\Client\Provider\ResourceOwnerInterface;
use League\OAuth2\Client\Tool\ArrayAccessorTrait;

/**
 * Shopify OAuth2 Store Class
 */
class ShopifyStore implements ResourceOwnerInterface
{
    use ArrayAccessorTrait;

    /**
     * @var array
     */
    protected $response;

    /**
     * @param array $response
     */
    public function __construct(array $response)
    {
        $this->response = $response;
    }

    /**
     * Get Shop Id
     *
     * @return string
     */
    public function getId()
    {
        return $this->getValueByKey($this->response, 'shop.id');
    }

    /**
     * Get shop name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->getValueByKey($this->response, 'shop.name');
    }

    /**
     * Get shop email.
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->getValueByKey($this->response, 'shop.email');
    }

    /**
     * Get shop domain name.
     *
     * @return string
     */
    public function getDomain()
    {
        return $this->getValueByKey($this->response, 'shop.domain');
    }

    /**
     * Get shop country.
     *
     * @return null|string
     */
    public function getCountry()
    {
        return $this->getValueByKey($this->response, 'shop.country_name');
    }

    /**
     * Get shop owner name.
     *
     * @return null|string
     */
    public function getShopOwner()
    {
        return $this->getValueByKey($this->response, 'shop.shop_owner');
    }

    /**
     * Get user data as an array.
     *
     * @return array
     */
    public function toArray()
    {
        return $this->getValueByKey($this->response, 'shop');
    }
}
