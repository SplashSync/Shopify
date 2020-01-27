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

namespace Splash\Connectors\Shopify\Helpers;

use Exception;
use Slince\Shopify\Client;
use Slince\Shopify\Common\CursorBasedPagination;
use Symfony\Component\Cache\Simple\ApcuCache;

/**
 * Override Base Slince Cursor Pagination to Add Links Caching
 *
 * Since API Version 2019-10 most part of API Object have to use
 * a Cursor based navigation for data lists. This just makes our API
 * becoming more and more complex...
 */
class CachedCursorPagination extends CursorBasedPagination
{
    /**
     * @var int Cache Lifetime
     */
    private static $cacheTtl = 3600;

    /**
     * @var string Cache Key
     */
    private $cacheKey = 3600;

    /**
     * @var ApcuCache
     */
    private $apcuCache;

    /**
     * @var array List of Known Pages Links
     */
    private $cache;

    /**
     * @param Client $client
     * @param string $resource
     * @param array  $query
     */
    public function __construct(Client $client, string $resource, array $query)
    {
        //====================================================================//
        // Build Parent Class
        parent::__construct(
            $client->{"get".ucwords($resource)."Manager"}(),
            isset($query['query']) ? $resource."/search" : $resource,
            $query
        );
        //====================================================================//
        // Build Unique Cache Key
        $this->cacheKey = $this->getCacheKey($client, $resource, $query);
        //====================================================================//
        // Connect to Apcu Cache
        $this->apcuCache = new ApcuCache();
        //====================================================================//
        // Load Cached Links
        $this->loadCache();
    }

    /**
     * Load Specified Objects Lists Page
     *
     * @param int $page
     *
     * @return array
     */
    public function getPage(int $page): array
    {
        //====================================================================//
        // Reading of First Page
        if (1 == $page) {
            $response = $this->current();
            $this->saveLinks($page);

            return $response;
        }
        //====================================================================//
        // Page Link is Known
        if ($this->hasLink($page)) {
            $response = $this->fetchResource($this->getLink($page));
            $this->saveLinks($page);

            return $response;
        }
        //====================================================================//
        // A Closest Page Link is Known
        $closest = $this->getClosestLink($page);
        if ($closest) {
            $response = $this->fetchResource($this->getLink($closest));
            $this->saveLinks($page);
            for ($i = ($closest + 1); $i <= $page; $i++) {
                if ($this->hasNext()) {
                    $response = $this->next();
                    $this->saveLinks($i);
                }
            }

            return $response;
        }
        //====================================================================//
        // BRUTAL => Walk from Page 1 to Page X
        $response = $this->current();
        $this->saveLinks(1);
        for ($i = 2; $i <= $page; $i++) {
            if ($this->hasNext()) {
                $response = $this->next();
                $this->saveLinks($i);
            }
        }

        return $response;
    }

    /**
     * Save Current Next & Previous Links to Cache
     *
     * @param int $page
     *
     * @return void
     */
    private function saveLinks(int $page): void
    {
        if (isset($this->links["previous"]) && !empty($this->links["previous"])) {
            $this->cache[$page - 1] = (string) $this->links["previous"];
        }
        if (isset($this->links["next"]) && !empty($this->links["next"])) {
            $this->cache[$page + 1] = (string) $this->links["next"];
        }

        ksort($this->cache);
        $this->saveCache();
    }

    /**
     * Check if Current Page Link is Cached
     *
     * @param int $page
     *
     * @return bool
     */
    private function hasLink(int $page): bool
    {
        if (isset($this->cache[$page]) && !empty($this->cache[$page])) {
            return true;
        }

        return false;
    }

    /**
     * Get Cached Link Url
     *
     * @param int $page
     *
     * @return null|string
     */
    private function getLink(int $page): string
    {
        if (!isset($this->cache[$page]) || empty($this->cache[$page])) {
            throw new Exception("Trying to get an uncached Link");
        }

        return $this->cache[$page];
    }

    /**
     * Identify Closest Cached Page Url
     *
     * @param int $page
     *
     * @return null|int
     */
    private function getClosestLink(int $page): ?int
    {
        $closestPage = null;
        /** @var int $cachedPage */
        foreach (array_keys($this->cache) as $cachedPage) {
            if ($cachedPage < $page) {
                $closestPage = $cachedPage;
            }
        }

        return $closestPage;
    }

    /**
     * Load Known Links from Cache
     *
     * @return array
     */
    private function loadCache(): array
    {
        //====================================================================//
        // Safety Check
        if (!is_string($this->cacheKey) || empty($this->cacheKey)) {
            return array();
        }
        //====================================================================//
        // Check if Links are In Cache
        if ($this->apcuCache->has($this->cacheKey)) {
            $this->cache = $this->apcuCache->get($this->cacheKey);
        }
        //====================================================================//
        // Load Empty Value
        if (!is_array($this->cache)) {
            $this->cache = array();
        }

        return $this->cache;
    }

    /**
     * Save Current Links to Cache
     *
     * @return void
     */
    private function saveCache(): void
    {
        //====================================================================//
        // Safety Check
        if (!is_string($this->cacheKey) || empty($this->cacheKey)) {
            return;
        }
        if (!is_array($this->cache) || empty($this->cache)) {
            return;
        }
        //====================================================================//
        // Save Links are In Cache
        $this->apcuCache->set($this->cacheKey, $this->cache, static::$cacheTtl);
    }

    /**
     * Build Cache Key from Inputs
     *
     * @param Client $client
     * @param type   $resource
     * @param type   $query
     *
     * @return string
     */
    private function getCacheKey(Client $client, $resource, $query): string
    {
        $params = array_merge_recursive(
            array(
                $client->getShop(),
                $resource,
            ),
            $query
        );
        //====================================================================//
        // Build Image Cache Key
        return implode(
            ".",
            array(
                "splash.shopify.connector.list",
                md5(serialize($params)))
        );
    }
}
