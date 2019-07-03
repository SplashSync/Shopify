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

namespace Splash\Connectors\Shopify\Objects\Product;

use Splash\Core\SplashCore      as Splash;
use Splash\Models\Objects\ImagesTrait as SplashImagesTrait;
use Symfony\Component\Cache\Simple\ApcuCache;

/**
 * Access to Product Images Fields
 */
trait ImagesTrait
{
    use SplashImagesTrait;

    /**
     * @var int Image Info Cache Lifetime
     */
    private static $imgCacheTtl = 3600;

    /**
     * Build Fields using FieldFactory
     */
    protected function buildImagesFields()
    {
        //====================================================================//
        // PRODUCT IMAGES
        //====================================================================//

        //====================================================================//
        // Product Images List
        $this->fieldsFactory()->Create(SPL_T_IMG)
            ->Identifier("image")
            ->InList("images")
            ->Name("Image")
            ->Group("Images")
            ->MicroData("http://schema.org/Product", "image")
            ->isReadOnly();

        //====================================================================//
        // Product Images => Image Position In List
        $this->fieldsFactory()->create(SPL_T_INT)
            ->Identifier("position")
            ->InList("images")
            ->Name("Position")
            ->Group("Images")
            ->Description("Image Order for this Product Variant")
            ->MicroData("http://schema.org/Product", "positionImage")
            ->isReadOnly()
            ->isNotTested();

        //====================================================================//
        // Product Images => Is Cover
        $this->fieldsFactory()->Create(SPL_T_BOOL)
            ->Identifier("cover")
            ->InList("images")
            ->Name("Is Cover")
            ->Group("Images")
            ->MicroData("http://schema.org/Product", "isCover")
            ->isNotTested()
            ->isReadOnly();

        //====================================================================//
        // Product Images => Is Visible Image
        $this->fieldsFactory()->create(SPL_T_BOOL)
            ->Identifier("visible")
            ->InList("images")
            ->Name("Visible")
            ->Group("Images")
            ->Description("Image is visible for this Product Variant")
            ->MicroData("http://schema.org/Product", "isVisibleImage")
            ->Group("Product gallery")
            ->isReadOnly()
            ->isNotTested();
    }

    /**
     * Read requested Field
     *
     * @param string $key       Input List Key
     * @param string $fieldName Field Identifier / Name
     */
    protected function getImagesFields($key, $fieldName)
    {
        //====================================================================//
        // READ Fields
        switch ($fieldName) {
            //====================================================================//
            // PRODUCT IMAGES
            //====================================================================//
            case 'image@images':
            case 'cover@images':
            case 'position@images':
            case 'visible@images':
                $this->getImgArray();

                break;
            default:
                return;
        }

        unset($this->in[$key]);
    }

    /**
     * Return Product Image Array from Product Object Class
     */
    private function getImgArray()
    {
        //====================================================================//
        // Images List Alraedy Loaded
        // Images List is Empty
        if (!empty($this->out["images"]) || !is_iterable($this->object->images)) {
            return;
        }
        //====================================================================//
        // Init Images List
        $this->out["images"] = array();
        //====================================================================//
        // Create Images List
        foreach ($this->object->images as $key => $shopifyImage) {
            //====================================================================//
            // Load Image Informations from cache or from API
            $image = $this->getImageInfoArray($shopifyImage['id'], $shopifyImage['src'], $shopifyImage['alt']);
            //====================================================================//
            // Init Image List Item
            if (!isset($this->out["images"][$key])) {
                $this->out["images"][$key] = array();
            }
            // Shopify Image Raw data
            $this->out["images"][$key]["image"] = $image;
            // Shopify Image Position
            $this->out["images"][$key]["position"] = $shopifyImage['position'];
            // Shopify Image Cover Flag
            $this->out["images"][$key]["cover"] = (1 == $shopifyImage['position']);
            // Shopify Image Visible Flag
            $this->out["images"][$key]["visible"] = empty($shopifyImage['variant_ids'])
                ? true
                : in_array($this->variantId, $shopifyImage['variant_ids'], true);
        }
    }

    /**
     * Load Product Image Informations Array from Cache or API
     *
     * @param int    $shopifyId
     * @param string $absoluteUrl
     * @param string $altImageNane
     *
     * @return null|array
     */
    private function getImageInfoArray(int $shopifyId, string $absoluteUrl, ?string $altImageNane): ?array
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
        for ($count = 0; $count < 3; $count++) {
            //====================================================================//
            // Touch Image with Curl (In Case first reading)
            $this->images()->touchRemoteFile($absoluteUrl);
            //====================================================================//
            // Encode Image Infos from Url
            $image = $this->images()->encodeFromUrl($filename, $absoluteUrl, $absoluteUrl);
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
}
