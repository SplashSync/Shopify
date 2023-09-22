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

use Symfony\Component\HttpFoundation\Request;

/**
 * Verify Oauth2 Request HMACS
 */
class RequestVerifier
{
    /**
     * Returns the authorization headers used by this provider.
     *
     * Each webhook request includes a base64-encoded X-Shopify-Hmac-SHA256 header,
     * which is generated using the app's client secret along with the data sent in the request.
     * If you're using PHP, or a Rack-based framework such as Ruby on Rails or Sinatra,
     * then the header is HTTP_X_SHOPIFY_HMAC_SHA256.
     *
     * @param string  $clientSecret
     * @param Request $request      Received Webhook Request
     *
     * @return bool
     */
    public static function validateWebhookHmac(string $clientSecret, Request $request): bool
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
        $requestHmac = self::getRequestHmac($clientSecret, (string) $rawContents);
        if (empty($requestHmac)) {
            return false;
        }

        return hash_equals($requestHmac, $headerHmac);
    }

    /**
     * Returns the authorization headers used by this provider.
     *
     * Each webhook request includes a base64-encoded X-Shopify-Hmac-SHA256 header,
     * which is generated using the app's client secret along with the data sent in the request.
     * If you're using PHP, or a Rack-based framework such as Ruby on Rails or Sinatra,
     * then the header is HTTP_X_SHOPIFY_HMAC_SHA256.
     *
     * @param string  $clientSecret
     * @param Request $request      Received Webhook Request
     *
     * @return bool
     */
    public static function validateQueryHmac(string $clientSecret, Request $request): bool
    {
        //==============================================================================
        // Extract Request Query HMAC
        $queryHmac = $request->query->get("hmac");
        if (empty($queryHmac) || !is_string($queryHmac)) {
            return false;
        }
        //==============================================================================
        // Extract Request Query Values
        $queryValues = array_diff_key($request->query->all(), array("hmac" => true));
        ksort($queryValues);
        //==============================================================================
        // Compute Request HMAC
        $requestHmac = hash_hmac(
            'sha256',
            http_build_query($queryValues),
            $clientSecret
        );
        //==============================================================================
        // Verify Request HMAC
        if (empty($requestHmac)) {
            return false;
        }

        return hash_equals($requestHmac, $queryHmac);
    }

    /**
     * Generate request Security HMAC.
     *
     * @param string $contents Request Contents
     *
     * @return null|string
     */
    public static function getRequestHmac(string $clientSecret, string $contents): ?string
    {
        //==============================================================================
        // Safety Check
        if (empty($clientSecret)) {
            return null;
        }

        //==============================================================================
        // Compute Request HMAC
        return base64_encode(hash_hmac(
            'sha256',
            $contents,
            $clientSecret,
            true
        )) ?: null;
    }
}
