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

namespace Splash\Connectors\Shopify\Services;

use Splash\Connectors\Shopify\Models\ShopifyHelper as API;
use Webmozart\Assert\Assert;

/**
 * Manage Access to MetaFields
 */
class MetaFieldsManager
{
    /**
     * Local Storage for Current Object Metadata
     */
    private ?array $metadataCache = array();

    /**
     * Get List of Shopify Object Metadata
     */
    public function getList(string $ownerType) : array
    {
        $pageSize = 1;
        $metaFields = array();
        $endCursor = null;

        do {
            //====================================================================//
            // Build GraphQl Query
            $qlParams = implode(", ", array_filter(array(
                "owner_type" => "ownerType: ".$ownerType,
                "first" => "first: ".$pageSize,
                "after" => $endCursor ? 'after: "'.$endCursor.'"' : null,
            )));
            //====================================================================//
            // Build GraphQl Query
            $qlResponse = API::post("graphql", array(
                "query" => 'query { 
                metafieldDefinitions('.$qlParams.') { 
                    nodes {
                        id
                        key
                        namespace
                        description
                        type { name, valueType }
                        validations { type }
                    }
                    pageInfo {
                      endCursor
                      hasNextPage
                      hasPreviousPage
                      startCursor
                    }
                }
            }'
            ), "data");

            //====================================================================//
            // Get Data from Response
            if (is_array($nodes = $qlResponse['metafieldDefinitions']['nodes'] ?? array())) {
                $metaFields = array_merge_recursive($metaFields, $nodes);
            }
            //====================================================================//
            // Get Pagination from Response
            $hasNextPage = !empty($qlResponse['metafieldDefinitions']['pageInfo']['hasNextPage'] ?? false);
            $endCursor = $qlResponse['metafieldDefinitions']['pageInfo']['endCursor'] ?? null;
        } while ($hasNextPage);

        return $metaFields;
    }

    /**
     * Get a Single Object Metadata from API or Local Cache
     */
    public function getMetadata(string $ownerType, string $ownerId, string $namespace, string $key): ?array
    {
        $resourceUri = $this->getResourceUri($ownerType, $ownerId);
        //====================================================================//
        // Get Metadata from Local Cache or Api
        $metaDataCache = $this->metadataCache[$resourceUri] ?? $this->getMetadataFromApi($resourceUri);
        //====================================================================//
        // Extract Metadata from Local Cache
        $metaDatas = $this->filter($metaDataCache, $namespace, $key);
        if (1 != count($metaDatas)) {
            return null;
        }

        return array_shift($metaDatas);
    }

    /**
     * Get All Object Metadata from API or Local Cache
     *
     * @return array[]
     */
    public function getNamespace(string $ownerType, string $ownerId, string $namespace = null): array
    {
        $resourceUri = $this->getResourceUri($ownerType, $ownerId);
        //====================================================================//
        // Get Metadata from Local Cache or Api
        $metaDataCache = $this->metadataCache[$resourceUri] ?? $this->getMetadataFromApi($resourceUri);

        //====================================================================//
        // Extract Metadata from Local Cache
        return $this->filter($metaDataCache, $namespace);
    }

    /**
     * Update a Single Object Metadata from API
     */
    public function setMetadata(string $ownerType, string $ownerId, array $metaData): ?array
    {
        $resourceUri = $this->getResourceUri($ownerType, $ownerId);
        if ($metaDataId = $metaData["id"] ?? null) {
            //====================================================================//
            // Update Metadata
            $response = API::put(
                $resourceUri."/".$metaDataId,
                array("metafield" => $metaData)
            );
        } else {
            //====================================================================//
            // Create Metadata
            $response = API::post(
                $resourceUri,
                array("metafield" => $metaData)
            );
        }
        //====================================================================//
        // Creation Fails
        if (!$response || !is_array($metaData = $response["metafield"] ?? null)) {
            return null;
        }
        //====================================================================//
        // Update Cache
        Assert::scalar($id = $metaData["id"] ?? null);
        $this->metadataCache[$resourceUri][$id] = $metaData;

        return $metaData;
    }

    /**
     * Filter Object Metadata with Namespace and Key
     *
     * @return array[]
     */
    private function filter(array $metadataCache, string $namespace = null, string $key = null): array
    {
        //====================================================================//
        // No Metadata => Exit
        // No Namespace => Return All Metadata
        if (empty($metadataCache) || empty($namespace)) {
            return $metadataCache;
        }
        //====================================================================//
        // Search for Namespace Metadata
        $nsMetadata = array();
        foreach ($metadataCache as $metadata) {
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

    /**
     * Load All Object Metadata from API
     */
    private function getMetadataFromApi(string $resourceUri): array
    {
        //====================================================================//
        // Get All Object Metadata from Api
        $rawMetaData = API::get($resourceUri, null, array(), "metafields");
        //====================================================================//
        // Ensure Array
        $metaDatas = is_array($rawMetaData) ? $rawMetaData : array();
        //====================================================================//
        // Populate Cache
        $this->metadataCache[$resourceUri] = array();
        foreach ($metaDatas as $metaData) {
            Assert::scalar($id = $metaData["id"] ?? null);
            $this->metadataCache[$resourceUri][$id] = $metaData;
        }

        return $this->metadataCache[$resourceUri];
    }

    /**
     * Load Object Metadata API Resource Uri
     */
    private function getResourceUri(string $ownerType, string $ownerId): string
    {
        return sprintf("%s/%s/metafields", $ownerType, $ownerId);
    }
}
