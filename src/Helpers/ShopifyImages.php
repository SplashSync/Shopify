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

use Splash\Bundle\Models\AbstractConnector;
use Splash\Models\Objects\ImagesTrait as SplashImagesTrait;
use Symfony\Component\Cache\Simple\ApcuCache;

/**
 * Shopify Product Images Helper
 */
class ShopifyImages
{
    use SplashImagesTrait;

    /**
     * @var int Image Info Cache Lifetime
     */
    private static $imgCacheTtl = 3600;

    /**
     * Load Product Image Informations Array from Cache or API
     *
     * @param int    $shopifyId
     * @param string $absoluteUrl
     * @param string $altImageNane
     *
     * @return null|array
     */
    public static function getInfos(int $shopifyId, string $absoluteUrl, ?string $altImageNane): ?array
    {
        //====================================================================//
        // Build Image Cache Key
        $cacheKey = implode(".", array("splash.shopify.connector.image", $shopifyId, md5($absoluteUrl)));
        //====================================================================//
        // Check if Image is In Cache
        $apcuCache = new ApcuCache();
        if ($apcuCache->has($cacheKey)) {
            return $apcuCache->get($cacheKey);
        }
        //====================================================================//
        // Build Image File Name
        $filename = !empty($altImageNane) ? $altImageNane : basename((string) parse_url($absoluteUrl, PHP_URL_PATH));
        //====================================================================//
        // Load Image from API
        $image = false;
        for ($count = 0; $count < 3; $count++) {
            //====================================================================//
            // Touch Image with Curl (In Case first reading)
            self::images()->touchRemoteFile($absoluteUrl);
            //====================================================================//
            // Encode Image Infos from Url
            $image = self::images()->encodeFromUrl($filename, $absoluteUrl, $absoluteUrl);
            //====================================================================//
            // Stop Loop if Succeeded
            if (is_array($image)) {
                break;
            }
        }
        //====================================================================//
        // Ensure Informations Loaded
        if (!is_array($image)) {
            return null;
        }
        //====================================================================//
        // Store Image In Cache
        $apcuCache->set($cacheKey, $image, static::$imgCacheTtl);

        return $image;
    }

    /**
     * Create Raw Product Image Informations Array from Splash Database
     *
     * @param array             $splashImage
     * @param AbstractConnector $connector
     *
     * @return null|array
     */
    public static function buildImage(array $splashImage, AbstractConnector $connector): ?array
    {
        //==============================================================================
        // Try Reading of File on Local System
        $rawFile = $connector->file($splashImage["path"], $splashImage["md5"]);
        //==============================================================================
        // Verify File was Found
        if (!is_array($rawFile)) {
            return null;
        }
        //==============================================================================
        // Build Shopify Image Array
        return array(
            "alt" => $splashImage["filename"],      // Image File Name
            "attachment" => $rawFile["raw"],       // Raw Image File Contents
            "position" => null,                     // Position will be Setuped After
            "variant_ids" => array(),               // Variants IDs will be Setuped After
        );
    }
}
