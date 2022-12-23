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

namespace Splash\Connectors\Shopify\Models;

use Exception;
use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Token\AccessToken;
use Psr\Http\Message\ResponseInterface;
use Splash\Bundle\Models\AbstractConnector;
use Symfony\Component\HttpFoundation\Request;

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
    protected string $shop;

    /**
     * @var string If set, this will be sent to shopify as the "per-user" parameter.
     *
     * @link https://help.shopify.com/api/guides/authentication/oauth#asking-for-permission
     */
    protected string $accessType;

    /**
     * @var array
     */
    private static array $config = array(
        // Shopify OAuth2 Provider
        "type" => "generic",
        "provider_class" => OAuth2Client::class,
        // Shopify Public App Options!
        "client_id" => "32324733c73b1ea6e98bd2266c1ec089",
        "client_secret" => "",
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
    public static function getConfig(): array
    {
        return self::$config;
    }

    /**
     * Init OAuth2 Client for All Connectors
     *
     * @param string $apiSecret
     *
     * @throws Exception
     *
     * @return void
     */
    public static function init(string $apiSecret): void
    {
        //==============================================================================
        // Safety Check
        if (empty($apiSecret)) {
            throw new Exception("SHOPIFY: No Api Secret Provided");
        }
        self::$config['client_secret'] = $apiSecret;
    }

    /**
     * Configure OAuth2 Client for User Connector
     *
     * @param AbstractConnector $connector
     *
     * @return $this
     */
    public function configure(AbstractConnector $connector, string $shop = null): self
    {
        //==============================================================================
        // Configure Shopify Shop Url
        /** @var string $shop */
        $shop = $shop ?? $connector->getParameter("WsHost");
        $this->shop = $shop;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getBaseAuthorizationUrl(): string
    {
        return 'https://'.$this->shop.'/admin/oauth/authorize';
    }

    /**
     * {@inheritdoc}
     */
    public function getBaseAccessTokenUrl(array $params): string
    {
        return 'https://'.$this->shop.'/admin/oauth/access_token';
    }

    /**
     * {@inheritdoc}
     */
    public function getResourceOwnerDetailsUrl(AccessToken $token): string
    {
        return 'https://'.$this->shop.'/admin/shop.json';
    }

    /**
     * {@inheritdoc}
     */
    public function getAuthorizationParameters(array $options): array
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
    public function getDefaultScopes(): array
    {
        return array(
            // Access to Customer and Saved Search.
            'read_customers', 'write_customers',
            // Access to Product, product variant, Product Image, Collect, Custom Collection, and Smart Collection.
            'read_products', 'write_products',
            // Access to Product Stocks Levels
            'read_inventory', 'write_inventory',
            // Access to Order, Transaction and Fulfillment.
            'read_orders', 'write_orders', 'read_all_orders',
            // Access to Fulfillment
            'read_fulfillments', 'write_fulfillments',
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getScopeSeparator(): string
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
     * Typically, this is "Bearer" or "MAC". For more information see:
     * http://tools.ietf.org/html/rfc6749#section-7.1
     *
     * No default is provided, providers must overload this method to activate
     * authorization headers.
     *
     * @param null|mixed $token Either a string or an access token instance
     *
     * @return array
     */
    public function getAuthorizationHeaders($token = null): array
    {
        return ($token instanceof AccessToken)
            ? array('X-Shopify-Access-Token' => $token->getToken())
            : array()
        ;
    }

    /**
     * Returns the authorization headers used by this provider.
     *
     * Each webhook request includes a base64-encoded X-Shopify-Hmac-SHA256 header,
     * which is generated using the app's client secret along with the data sent in the request.
     * If you're using PHP, or a Rack-based framework such as Ruby on Rails or Sinatra,
     * then the header is HTTP_X_SHOPIFY_HMAC_SHA256.
     *
     * @param Request $request Received Webhook Request
     *
     * @return bool
     */
    public static function validateWebhookHmac(Request $request): bool
    {
        //==============================================================================
        // Extract Request HMAC
        $headerHmac = $request->headers->get("X_SHOPIFY_HMAC_SHA256");
        if (empty($headerHmac) || !is_string($headerHmac)) {
            return false;
        }
        //==============================================================================
        // Extract Request RAW Data
        $rawContents = file_get_contents('php://input')
            ?: $request->getContent()
            ?: json_encode($request->request->all())
        ;
        //==============================================================================
        // Compute Request HMAC
        $requestHmac = self::getRequestHmac((string) $rawContents);
        if (empty($requestHmac)) {
            return false;
        }

        return hash_equals($requestHmac, $headerHmac);
    }

    /**
     * Generate request Security HMAC.
     *
     * @param string $contents Request Contents
     *
     * @return null|string
     */
    public static function getRequestHmac(string $contents): ?string
    {
        //==============================================================================
        // Safety Check
        if (empty(self::$config['client_secret'])) {
            return null;
        }
        //==============================================================================
        // Compute Request HMAC
        return base64_encode(hash_hmac(
            'sha256',
            $contents,
            self::$config['client_secret'],
            true
        )) ?: null;
    }

    /**
     * {@inheritdoc}
     */
    protected function createResourceOwner(array $response, AccessToken $token)
    {
        return new ShopifyStore($response);
    }
}
