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
use Httpful\Exception\ConnectionErrorException;
use Httpful\Request;
use Slince\Shopify\Client;
use Slince\Shopify\CredentialInterface;
use Slince\Shopify\Exception\ClientException;
use Slince\Shopify\PrivateAppCredential;
use Slince\Shopify\PublicAppCredential;
use Splash\Connectors\Shopify\Helpers\CachedCursorPagination;
use Splash\Core\SplashCore as Splash;

/**
 * Shopify Specific Helper
 *
 * Support for Managing ApiKey, ApiRequests, Hash, Etc...
 */
class ShopifyHelper
{
    /**
     * API Version to Use
     */
    const API_VERSION = "2022-01";

    /**
     * @var string
     */
    private static string $endpoint;

    /**
     * @var PrivateAppCredential|PublicAppCredential
     */
    private static CredentialInterface $credential;

    /**
     * @var Client
     */
    private static Client $client;

    /**
     * Configure Shopify REST Public APP API
     *
     * @param string $wsHost   Shopify Admin Url
     * @param string $apiToken Shopify Shop Token
     * @param string $cacheDir Symfony Cache Dir
     *
     * @return bool
     */
    public static function configure(string $wsHost, string $apiToken, string $cacheDir): bool
    {
        try {
            //====================================================================//
            // Store Current Shop Url
            self::$endpoint = self::validateShopUrl($wsHost);
            //====================================================================//
            // Store Current Shop Credentials
            self::$credential = new PublicAppCredential($apiToken);
            //====================================================================//
            // Build Metadata cache dir, required
            $metaCacheDir = (is_dir($cacheDir) ? $cacheDir : sys_get_temp_dir()).'/shopify';
            //====================================================================//
            // Configure Shopify API Client
            self::$client = new Client(self::$endpoint, self::$credential, array(
                'meta_cache_dir' => $metaCacheDir,
                'api_version' => self::API_VERSION,
            ));
        } catch (Exception $ex) {
            Splash::log()->err($ex->getMessage());

            return false;
        }

        return true;
    }

    /**
     * Configure Shopify REST Private APP API
     *
     * @param string $wsHost    Shopify Admin Url
     * @param string $apiKey    Private API Key
     * @param string $apiSecret Private API Secret
     * @param string $apiToken  Private Shop Token
     * @param string $cacheDir  Symfony Cache Dir
     *
     * @return bool
     */
    public static function configurePrivate(
        string $wsHost,
        string $apiKey,
        string $apiSecret,
        string $apiToken,
        string $cacheDir
    ): bool {
        try {
            //====================================================================//
            // Store Current Shop Url
            self::$endpoint = self::validateShopUrl($wsHost);
            //====================================================================//
            // Store Current Shop Credentials
            self::$credential = new PrivateAppCredential($apiKey, $apiSecret, $apiToken);
            //====================================================================//
            // Build Metadata cache dir, required
            $metaCacheDir = (is_dir($cacheDir) ? $cacheDir : sys_get_temp_dir()).'/shopify';
            //====================================================================//
            // Configure Shopify API Client
            self::$client = new Client(self::$endpoint, self::$credential, array(
                'meta_cache_dir' => $metaCacheDir,
                'api_version' => self::API_VERSION,
            ));
        } catch (Exception $ex) {
            Splash::log()->err($ex->getMessage());

            return false;
        }

        return true;
    }

    /**
     * Ping Shopify API Url as Anonymous User
     *
     * @return bool
     */
    public static function ping(): bool
    {
        //====================================================================//
        // Perform Ping Test
        try {
            $response = Request::get("https://".self::$endpoint)
                ->autoParse(false)
                ->send();
        } catch (ConnectionErrorException $ex) {
            Splash::log()->err($ex->getMessage());

            return false;
        }

        if ((103 == $response->code) || ($response->code >= 200) && ($response->code < 500)) {
            return true;
        }

        return false;
    }

    /**
     * Ping Shopify API Url with API Key (Logged User)
     *
     * @return bool
     */
    public static function connect(): bool
    {
        //====================================================================//
        // Perform Connect Test
        try {
            self::$client->__call("getShopManager", array())->get();
        } catch (ClientException $ex) {
            Splash::log()->err($ex->getMessage());

            return false;
        }

        //====================================================================//
        // Return Connect Result
        return true;
    }

    /**
     * Shopify API GET Request
     *
     * @param string      $path     API REST Path
     * @param null|string $objectId Shopify Object Id
     * @param array       $query    Request Query
     * @param null|string $resource Response Resource
     *
     * @return null|array
     */
    public static function get(
        string $path,
        string $objectId = null,
        array $query = array(),
        string $resource = null
    ): ?array {
        //====================================================================//
        // Complete Url
        if (!is_null($objectId)) {
            $path .= "/".$objectId;
        }
        //====================================================================//
        // Perform Request
        try {
            $response = self::$client->get($path, $query);
        } catch (ClientException $ex) {
            Splash::log()->err($ex->getMessage());

            return null;
        }
        //====================================================================//
        // Return Response
        if (!is_null($resource) && isset($response[$resource])) {
            return $response[$resource];
        }

        return  $response;
    }

    /**
     * Shopify API PUT Request
     *
     * @param string $resource API REST Path
     * @param array  $data     Request Data
     *
     * @return null|array
     */
    public static function put(string $resource, array $data): ?array
    {
        //====================================================================//
        // Perform Request
        try {
            $response = self::$client->put($resource, $data);
        } catch (ClientException $ex) {
            Splash::log()->err($ex->getMessage());

            return null;
        }
        //====================================================================//
        // Return Response
        return isset($response[$resource]) ? $response[$resource] : $response;
    }

    /**
     * Shopify API POST Request
     *
     * @param string      $path     API REST Path
     * @param array       $data     Request Data
     * @param null|string $resource Response Resource
     *
     * @return null|array
     */
    public static function post(string $path, array $data, string $resource = null): ?array
    {
        //====================================================================//
        // Perform Request
        try {
            $response = self::$client->post($path, $data);
        } catch (ClientException $ex) {
            Splash::log()->err($ex->getMessage());

            return null;
        }
        //====================================================================//
        // Return Response
        if (!is_null($resource) && isset($response[$resource])) {
            return $response[$resource];
        }

        return  $response;
    }

    /**
     * Shopify API POST Request
     *
     * @param string      $path     API REST Path
     * @param array       $data     Request Data
     * @param null|string $resource Response Resource
     *
     * @return null|array
     */
    public static function postRaw(string $path, array $data, string $resource = null): ?array
    {
        //====================================================================//
        // Perform Request
        try {
            $response = self::$client->createRequest(
                "POST",
                sprintf('https://%s/admin/%s.json', self::$endpoint, $path),
                $data
            );
        } catch (ClientException $ex) {
            Splash::log()->err($ex->getMessage());

            return null;
        }
        //====================================================================//
        // Return Response
        if (!is_null($resource) && isset($response[$resource])) {
            return $response[$resource];
        }

        return  $response;
    }

    /**
     * Shopify API DELETE Request
     *
     * @param string $resource API REST Path
     *
     * @return null|bool
     */
    public static function delete(string $resource): ?bool
    {
        //====================================================================//
        // Perform Request
        try {
            self::$client->delete($resource);
        } catch (ClientException $ex) {
            Splash::log()->err($ex->getMessage());

            return false;
        }

        //====================================================================//
        // Catch Errors in Response
        return true;
    }

    /**
     * Shopify API List Request
     *
     * @param string   $resource API REST Path
     * @param null|int $limit    Number of results
     * @param null|int $offset   Results Offset
     * @param array    $query    Query Parameters
     *
     * @return null|array
     */
    public static function list(string $resource, int $limit = null, int $offset = null, array $query = array()): ?array
    {
        //====================================================================//
        // Prepare Parameters
        if (!is_null($limit) && ($limit > 0)) {
            $query['limit'] = $limit;
        }
        $page = 1;
        if (!is_null($limit) && ($limit > 0) && !is_null($offset) && ($offset >= 0)) {
            $page = (1 + (int) ($offset / $limit));
        }
        //====================================================================//
        // Perform Request
        try {
            //====================================================================//
            // Create Cached Cursor Pagination
            $cursor = new CachedCursorPagination(self::$client, $resource, $query);
            //====================================================================//
            // Load Requested Page
            $response = $cursor->getPage($page);
        } catch (Exception $ex) {
            Splash::log()->err($ex->getMessage());

            return null;
        }
        //====================================================================//
        // Return Response
        return isset($response[$resource]) ? $response[$resource] : $response;
    }

    /**
     * Shopify API Count Request
     *
     * @param string $resource API REST Path
     *
     * @return null|int
     */
    public static function count(string $resource, array $query = array()): ?int
    {
        //====================================================================//
        // Perform Request
        try {
            $response = self::$client->get($resource."/count", $query);
        } catch (ClientException $ex) {
            Splash::log()->err($ex->getMessage());

            return null;
        }
        //====================================================================//
        // Return Response
        return isset($response["count"]) ? $response["count"] : null;
    }

    /**
     * Check if Shop Url is Ok.
     *
     * @param string $wsHost
     *
     * @return string
     */
    private static function validateShopUrl(string $wsHost) : string
    {
        //====================================================================//
        // Remove Http
        if (0 === strpos($wsHost, "http://")) {
            $wsHost = substr($wsHost, strlen("http://"));
        }
        //====================================================================//
        // Remove Https
        if (0 === strpos($wsHost, "https://")) {
            $wsHost = substr($wsHost, strlen("https://"));
        }
        //====================================================================//
        // Remove End Slash
        if (strpos($wsHost, "/") === (strlen($wsHost) - 1)) {
            $wsHost = substr($wsHost, 0, strlen($wsHost) - 1);
        }

        return $wsHost;
    }
}
