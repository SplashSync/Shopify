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

namespace Splash\Connectors\Shopify\Helpers;

use Psr\Cache\InvalidArgumentException;
use Splash\Connectors\Shopify\Models\ShopifyHelper as API;
use Splash\Models\Objects\ImagesTrait as SplashImagesTrait;
use Symfony\Component\Cache\Adapter\ApcuAdapter;
use Symfony\Contracts\Cache\ItemInterface;

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
    const META_TYPE = "json";

    /**
     * @var ApcuAdapter
     */
    private static ApcuAdapter $apcu;

    /**
     * @var int Image Info Cache Lifetime
     */
    private static int $imgCacheTtl = 604800;

    /**
     * @var array Image Info for Cache Building
     */
    private static array $shopifyImage;

    /**
     * Load Product Image Information Array from Cache or API
     *
     * @param array $shopifyImage
     *
     * @return null|array
     */
    public static function getInfos(array $shopifyImage): ?array
    {
        self::$shopifyImage = $shopifyImage;
        //====================================================================//
        // Build Image Cache Key
        $cacheKey = implode(
            ".",
            array("splash.shopify.connector.image.meta", $shopifyImage['id'], md5($shopifyImage['src']))
        );
        //====================================================================//
        // Ensure Cache Exists
        if (!isset(self::$apcu)) {
            self::$apcu = new ApcuAdapter();
        }

        try {
            $fromCache = self::$apcu->get($cacheKey, function (ItemInterface $item) {
                //====================================================================//
                // Setup Cache Item
                $item->expiresAfter(self::$imgCacheTtl);
                //====================================================================//
                // Load Splash Image from Api
                $fromApi = self::getMetadataFromApi(self::$shopifyImage);
                if ($fromApi) {
                    return $fromApi;
                }
                //====================================================================//
                // Load Splash Image from Url
                $fromUrl = self::getMetadataFromUrl(self::$shopifyImage['src'], self::$shopifyImage['alt']);
                if ($fromUrl) {
                    //====================================================================//
                    // Save Splash Image is In Cache & Api
                    self::setMetadataInApi(self::$shopifyImage, $fromUrl);

                    return $fromUrl;
                }

                return null;
            });
        } catch (InvalidArgumentException $ex) {
            return null;
        }
        //====================================================================//
        // Check if Splash Image is In Cache
        if (is_array($fromCache)) {
            return $fromCache;
        }

        //====================================================================//
        // Loading Splash Image Fail
        try {
            self::$apcu->delete($cacheKey);
        } catch (InvalidArgumentException $e) {
            return null;
        }

        return null;
    }

    /**
     * Create Raw Product Image Information Array from Splash Raw File Array
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
            // Position will be Setup After
            "position" => null,
            // Variants IDs will be Setup After
            "variant_ids" => array(),
            // Add Splash Image Meta fields
            "metafields" => array(self::encodeMetadata($splashImage)),
        );
    }

    /**
     * Load Product Image Information Array from Url
     *
     * @param string      $absoluteUrl
     * @param null|string $altImageName
     *
     * @return null|array
     */
    private static function getMetadataFromUrl(string $absoluteUrl, ?string $altImageName): ?array
    {
        //====================================================================//
        // Build Image File Name
        $filename = !empty($altImageName) ? $altImageName : basename((string) parse_url($absoluteUrl, PHP_URL_PATH));
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
     * Load Product Image Metadata Array from Cache or API
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
        $metaDatas = API::get("metafields", null, $query, "metafields");
        //====================================================================//
        // No Metadata => Exit
        if (!is_array($metaDatas)) {
            return null;
        }
        //====================================================================//
        // Search for Splash Metadata
        $splashMeta = null;
        foreach ($metaDatas as $metadata) {
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
     * Save Product Image Information in Api
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
            "type" => self::META_TYPE,
            "namespace" => self::META_NAMESPACE,
        );
    }
}
