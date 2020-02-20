<?php

/*
 *  This file is part of SplashSync Project.
 *
 *  Copyright (C) 2015-2020 Splash Sync  <www.splashsync.com>
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace Splash\Connectors\Shopify\Helpers;

use Splash\Connectors\Shopify\Models\ShopifyHelper as API;
use Splash\Models\Objects\ImagesTrait as SplashImagesTrait;
use Symfony\Component\Cache\Simple\ApcuCache;

/**
 * Shopify Product Images Helper
 */
class ShopifyImages
{
    use SplashImagesTrait;

    /**
     * @var string
     */
    const META_NAMESPACE = "splashsync";

    /**
     * @var string
     */
    const META_KEY = "img_infos";

    /**
     * @var string
     */
    const META_TYPE = "json_string";

    /**
     * @var ApcuCache
     */
    private static $apcu;

    /**
     * @var int Image Info Cache Lifetime
     */
    private static $imgCacheTtl = 604800;

    /**
     * Load Product Image Informations Array from Cache or API
     *
     * @param array $shopifyImage
     *
     * @return null|array
     */
    public static function getInfos(array $shopifyImage): ?array
    {
        //====================================================================//
        // Check if Splash Image is In Cache
        $fromCache = self::getMetadataFromCache($shopifyImage);
        if ($fromCache) {
            return $fromCache;
        }
        //====================================================================//
        // Load Splash Image from Api
        $fromApi = self::getMetadataFromApi($shopifyImage);
        if ($fromApi) {
            //====================================================================//
            // Save Splash Image is In Cache
            self::setMetadataInCache($shopifyImage, $fromApi);

            return $fromApi;
        }
        //====================================================================//
        // Load Splash Image from Url
        $fromUrl = self::getMetadataFromUrl($shopifyImage['src'], $shopifyImage['alt']);
        if ($fromUrl) {
            //====================================================================//
            // Save Splash Image is In Cache & Api
            self::setMetadataInApi($shopifyImage, $fromUrl);
            self::setMetadataInCache($shopifyImage, $fromUrl);

            return $fromUrl;
        }
        //====================================================================//
        // Loading  Splash Image Fail
        return null;
    }

    /**
     * Create Raw Product Image Informations Array from Splash Raw File Array
     *
     * @param array $splashImage
     * @param array $rawFile
     *
     * @return null|array
     */
    public static function buildShopifyImage(array $splashImage, array $rawFile): ?array
    {
        //==============================================================================
        // Build Shopify Image Array
        return array(
            // Image File Name
            "alt" => !empty($rawFile["name"])
                ? $rawFile["name"]
                : $rawFile["filename"],
            // Raw Image File Contents
            "attachment" => $rawFile["raw"],
            // Position will be Setuped After
            "position" => null,
            // Variants IDs will be Setuped After
            "variant_ids" => array(),
            // Add Splash Image Metafields
            "metafields" => array(self::encodeMetadata($splashImage)),
        );
    }

    /**
     * Load Product Image Informations Array from Url
     *
     * @param string $absoluteUrl
     * @param string $altImageNane
     *
     * @return null|array
     */
    private static function getMetadataFromUrl(string $absoluteUrl, ?string $altImageNane): ?array
    {
        //====================================================================//
        // Build Image File Name
        $filename = !empty($altImageNane) ? $altImageNane : basename((string) parse_url($absoluteUrl, PHP_URL_PATH));
        //====================================================================//
        // Load Image from API
        $splashImage = false;
        for ($count = 0; $count < 3; $count++) {
            //====================================================================//
            // Touch Image with Curl (In Case first reading)
            self::images()->touchRemoteFile($absoluteUrl);
            //====================================================================//
            // Encode Image Infos from Url
            $splashImage = self::images()->encodeFromUrl($filename, $absoluteUrl, $absoluteUrl);
            //====================================================================//
            // Stop Loop if Succeeded
            if (is_array($splashImage)) {
                break;
            }
        }
        //====================================================================//
        // Ensure Informations Loaded
        if (!is_array($splashImage)) {
            return null;
        }

        return $splashImage;
    }

    /**
     * Load Product Image Metadatas Array from Cache or API
     *
     * @param array $shopifyImage
     *
     * @return null|array
     */
    private static function getMetadataFromApi(array $shopifyImage): ?array
    {
        //====================================================================//
        // Get Product Image Metadata from Api
        $query = array(
            "metafield[owner_id]" => (string) $shopifyImage["id"],
            "metafield[owner_resource]" => "product_image",
        );
        $metadatas = API::get("metafields", null, $query, "metafields");
        //====================================================================//
        // No Metadata => Exit
        if (!is_array($metadatas)) {
            return null;
        }
        //====================================================================//
        // Search for Splash Metadata
        $splashMeta = null;
        foreach ($metadatas as $metadata) {
            if (self::META_NAMESPACE != $metadata["namespace"]) {
                continue;
            }
            if (self::META_KEY != $metadata["key"]) {
                continue;
            }
            $splashMeta = json_decode($metadata["value"], true);

            break;
        }
        //====================================================================//
        // No Metadata => Exit
        if (!is_array($splashMeta)) {
            return null;
        }
        //====================================================================//
        // Complete with Shopify Infos
        $splashMeta["path"] = $shopifyImage["src"];
        $splashMeta["url"] = $shopifyImage["src"];
        unset($splashMeta["file"]);

        return $splashMeta;
    }

    /**
     * Save Product Image Informations in Api
     *
     * @param array $shopifyImage
     * @param array $splashImage
     *
     * @return void
     */
    private static function setMetadataInApi(array $shopifyImage, array $splashImage): void
    {
        //====================================================================//
        // Build Image Post Url
        $url = "products/".$shopifyImage["product_id"];
        $url .= "/images/".$shopifyImage["id"];
        //====================================================================//
        // Build Image Post Query
        $image = array(
            "id" => $shopifyImage["id"],
            "metafields" => array(self::encodeMetadata($splashImage)),
        );
        //====================================================================//
        // Update Image Metadata
        API::put($url, array("image" => $image));
    }

    /**
     * Encode Product Image Metadata
     *
     * @param array $splashImage
     *
     * @return array
     */
    private static function encodeMetadata(array $splashImage): array
    {
        return array(
            "key" => self::META_KEY,
            "value" => json_encode($splashImage),
            //                "value" => $splashImage,
            "value_type" => self::META_TYPE,
            "namespace" => self::META_NAMESPACE,
        );
    }

    /**
     * Load Product Image Informations from Cache
     *
     * @param array $shopifyImage
     *
     * @return null|array
     */
    private static function getMetadataFromCache(array  $shopifyImage): ?array
    {
        //====================================================================//
        // Build Image Cache Key
        $cacheKey = implode(".", array("splash.shopify.connector.image.meta", $shopifyImage['id'], md5($shopifyImage['src'])));
        //====================================================================//
        // Ensure Cache Exists
        if (!isset(static::$apcu)) {
            static::$apcu = new ApcuCache();
        }
        //====================================================================//
        // Check if Image is In Cache
        if (static::$apcu->has($cacheKey)) {
            return static::$apcu->get($cacheKey);
        }

        return null;
    }

    /**
     * Save Product Image Informations in Cache
     *
     * @param array $shopifyImage
     * @param array $splashImage
     *
     * @return void
     */
    private static function setMetadataInCache(array $shopifyImage, array $splashImage): void
    {
        //====================================================================//
        // Build Image Cache Key
        $cacheKey = implode(".", array("splash.shopify.connector.image.meta", $shopifyImage['id'], md5($shopifyImage['src'])));
        //====================================================================//
        // Ensure Cache Exists
        if (!isset(static::$apcu)) {
            static::$apcu = new ApcuCache();
        }
        //====================================================================//
        // Store Image In Cache
        static::$apcu->set($cacheKey, $splashImage, static::$imgCacheTtl);
    }
}
