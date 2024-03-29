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

namespace Splash\Connectors\Shopify\OAuth2;

use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Token\AccessToken;
use Psr\Http\Message\ResponseInterface;
use Splash\Bundle\Models\AbstractConnector;
use Splash\Connectors\Shopify\Models\ShopifyStore;
use Splash\Connectors\Shopify\Services\ScopesManagers;
use Splash\Connectors\Shopify\Services\ShopifyConnector;
use Symfony\Component\HttpFoundation\Request;

/**
 * OAuth2 Shopify Client Provider
 */
class ShopifyAdapter extends AbstractProvider
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
        "provider_class" => ShopifyAdapter::class,
        // Shopify Public App Options!
        "client_id" => "%env(resolve:SHOPIFY_API_KEY)%",
        "client_secret" => "%env(resolve:SHOPIFY_API_SECRET)%",
        // Shopify Redirect Route Definition
        "redirect_route" => "splash_connector_action_master",
        "redirect_params" => array(
            "connectorName" => "shopify",
        ),
    );

    /**
     * @var string[]
     */
    private array $accessScopes = ScopesManagers::DEFAULT_SCOPES;

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
     * Configure OAuth2 Client for User Connector
     *
     * @param AbstractConnector $connector Shopify Connector
     * @param null|string       $shop      Force Shop Url
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
        //==============================================================================
        // Safety Check
        if (!$connector instanceof ShopifyConnector) {
            return $this;
        }
        //==============================================================================
        // Configure Access Scopes
        $this->accessScopes = ScopesManagers::DEFAULT_SCOPES;
        if ($connector->hasLogisticMode()) {
            $this->accessScopes = array_merge($this->accessScopes, ScopesManagers::LOGISTIC_SCOPES);
        }

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
        return $this->accessScopes;
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
    public function validateWebhookHmac(Request $request): bool
    {
        return RequestVerifier::validateWebhookHmac($this->clientSecret, $request);
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
    public function validateQueryHmac(Request $request): bool
    {
        return RequestVerifier::validateQueryHmac($this->clientSecret, $request);
    }

    /**
     * {@inheritdoc}
     */
    protected function createResourceOwner(array $response, AccessToken $token)
    {
        return new ShopifyStore($response);
    }
}
