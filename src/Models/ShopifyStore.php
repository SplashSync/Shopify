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
use function _PHPStan_c24aa5a16\RingCentral\Psr7\str;

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
     * Get Shop ID
     *
     * @return string
     */
    public function getId(): string
    {
        $shopId = $this->getValueByKey($this->response, 'shop.id');

        return is_scalar($shopId) ? (string) $shopId : "";
    }

    /**
     * Get shop name.
     *
     * @return string
     */
    public function getName(): string
    {
        $shopName = $this->getValueByKey($this->response, 'shop.name');

        return is_scalar($shopName) ? (string) $shopName : "";
    }

    /**
     * Get shop email.
     *
     * @return string
     */
    public function getEmail(): string
    {
        $shopEmail = $this->getValueByKey($this->response, 'shop.email');

        return is_scalar($shopEmail) ? (string) $shopEmail : "";
    }

    /**
     * Get shop domain name.
     *
     * @return string
     */
    public function getDomain(): string
    {
        $shopDomain = $this->getValueByKey($this->response, 'shop.domain');

        return is_scalar($shopDomain) ? (string) $shopDomain : "";
    }

    /**
     * Get shop country.
     *
     * @return null|string
     */
    public function getCountry(): ?string
    {
        $shopCountry = $this->getValueByKey($this->response, 'shop.country_name');

        return is_scalar($shopCountry) ? (string) $shopCountry : null;
    }

    /**
     * Get shop owner name.
     *
     * @return null|string
     */
    public function getShopOwner(): ?string
    {
        $shopOwner = $this->getValueByKey($this->response, 'shop.shop_owner');

        return is_scalar($shopOwner) ? (string) $shopOwner : null;
    }

    /**
     * Get user data as an array.
     *
     * @return array
     */
    public function toArray(): array
    {
        $shopInfo = $this->getValueByKey($this->response, 'shop');

        return is_array($shopInfo) ? $shopInfo : array();
    }
}
