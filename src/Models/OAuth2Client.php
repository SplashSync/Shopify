<?php

/*
 *  This file is part of SplashSync Project.
 *
 *  Copyright (C) 2015-2019 Splash Sync  <www.splashsync.com>
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace Splash\Connectors\Shopify\Models;

use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Token\AccessToken;
use Psr\Http\Message\ResponseInterface;
use Splash\Bundle\Models\AbstractConnector;

/**
 * OAuth2 Shopify Client Provider
 */
class OAuth2Client extends AbstractProvider
{
    const ACCESS_TOKEN_RESOURCE_OWNER_ID = 'id';

    /**
     * @var string This will be prepended to the base uri.
     *
     * @link https://help.shopify.com/api/guides/authentication/oauth#asking-for-permission
     */
    protected $shop;

    /**
     * @var string If set, this will be sent to shopify as the "per-user" parameter.
     *
     * @link https://help.shopify.com/api/guides/authentication/oauth#asking-for-permission
     */
    protected $accessType;

    private static $config = array(
        // Shopify OAuth2 Provider
        "type" => "generic",
        "provider_class" => OAuth2Client::class,
        // Shopify Public App Options!
        "client_id" => "32324733c73b1ea6e98bd2266c1ec089",
        "client_secret" => "f4e625818548cf851d0ebbd6bf05fb53",
        // Shopify Redirect Route Definition
        "redirect_route" => "splash_connector_action_master",
        "redirect_params" => array(
            "connectorName" => "shopify",
        ),
    );

    /**
     * Get Shopify OAuth2 Client Configuration
     *
     * @return array
     */
    public static function getConfig()
    {
        return static::$config;
    }

    /**
     * Configure OAuth2 Client for User Connector
     *
     * @param AbstractConnector $connector
     *
     * @return $this
     */
    public function configure(AbstractConnector $connector)
    {
        //==============================================================================
        // Configure Shopify Shop Url
        $this->shop = $connector->getParameter("WsHost");

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getBaseAuthorizationUrl()
    {
        return 'https://'.$this->shop.'/admin/oauth/authorize';
    }

    /**
     * {@inheritdoc}
     */
    public function getBaseAccessTokenUrl(array $params)
    {
        return 'https://'.$this->shop.'/admin/oauth/access_token';
    }

    /**
     * {@inheritdoc}
     */
    public function getResourceOwnerDetailsUrl(AccessToken $token)
    {
        return 'https://'.$this->shop.'/admin/shop.json';
    }

    /**
     * {@inheritdoc}
     */
    public function getAuthorizationParameters(array $options)
    {
        $option = (!empty($this->accessType) && 'offline' != $this->accessType) ? 'per-user' : null;

        return array_merge(
            parent::getAuthorizationParameters($options),
            array_filter(array(
                'option' => $option,
            ))
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultScopes()
    {
        return array(
            // Access to Customer and Saved Search.
            'read_customers', 'write_customers',
            // Access to Product, product variant, Product Image, Collect, Custom Collection, and Smart Collection.
            'read_products', 'write_products',
            // Access to Product Stocks Levels
            'read_inventory', 'write_inventory',
            // Access to Order, Transaction and Fulfillment.
            'read_orders', 'write_orders',
            // 'read_all_orders',
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getScopeSeparator()
    {
        return ',';
    }

    /**
     * {@inheritdoc}
     */
    public function checkResponse(ResponseInterface $response, $data)
    {
        if (is_array($data) && !empty($data['errors'])) {
            throw new IdentityProviderException($data['errors'], 0, $data);
        }
    }

    /**
     * Returns the authorization headers used by this provider.
     *
     * Typically this is "Bearer" or "MAC". For more information see:
     * http://tools.ietf.org/html/rfc6749#section-7.1
     *
     * No default is provided, providers must overload this method to activate
     * authorization headers.
     *
     * @param null|mixed $token Either a string or an access token instance
     *
     * @return array
     */
    public function getAuthorizationHeaders($token = null)
    {
        return array('X-Shopify-Access-Token' => $token->getToken());
    }

    /**
     * {@inheritdoc}
     */
    protected function createResourceOwner(array $response, AccessToken $token)
    {
        return new ShopifyStore($response);
    }
}
