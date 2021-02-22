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

namespace Splash\Connectors\Shopify\Objects\Core;

use Splash\Connectors\Shopify\Models\ShopifyHelper as API;
use Splash\Core\SplashCore as Splash;

/**
 * Trait MetadataTrait
 *
 * @package Splash\Connectors\Shopify\Objects\Core
 */
trait MetadataTrait
{
    /**
     * @var array
     */
    private $metadata;

    /**
     * Load Object Metadata from API
     *
     * @param string      $resourceUrl
     * @param null|string $namespace
     *
     * @return array
     */
    protected function getMetadataFromApi(string $resourceUrl, string $namespace = null, string $key = null): array
    {
        //====================================================================//
        // Get All Object Metadata from Api
        if (!isset($this->metadata)) {
            $rawMetadata = API::get($resourceUrl, null, array(), "metafields");
            $this->metadata = is_array($rawMetadata) ? $rawMetadata : array();
        }
        //====================================================================//
        // No Metadata => Exit
        // No Namespace => Return All Metadata
        if (empty($this->metadata) || empty($namespace)) {
            return $this->metadata;
        }
        //====================================================================//
        // Search for Namespace Metadata
        $nsMetadata = array();
        foreach ($this->metadata as $metadata) {
            if ($namespace != $metadata["namespace"]) {
                continue;
            }
            if (!empty($key) && ($key != $metadata["key"])) {
                continue;
            }
            $nsMetadata[] = $metadata;
        }

        return $nsMetadata;
    }
}
