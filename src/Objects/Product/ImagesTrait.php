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

namespace Splash\Connectors\Shopify\Objects\Product;

use ArrayObject;
use Splash\Connectors\Shopify\Helpers\ShopifyImages;
use Splash\Core\SplashCore as Splash;
use Splash\Models\Objects\ImagesTrait as SplashImagesTrait;

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
     * @var int
     */
    private $imgPosition = 0;

    /**
     * @var array Shopify ID of Already Found Images
     */
    private $imgFound = array();

    /**
     * Build Fields using FieldFactory
     *
     * @return void
     */
    protected function buildImagesFields(): void
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
            ->MicroData("http://schema.org/Product", "image");

        //====================================================================//
        // Product Images => Image Position In List
        $this->fieldsFactory()->create(SPL_T_INT)
            ->Identifier("position")
            ->InList("images")
            ->Name("Position")
            ->Group("Images")
            ->Description("Image Order for this Product Variant")
            ->MicroData("http://schema.org/Product", "positionImage")
            ->isNotTested();

        //====================================================================//
        // Product Images => Is Cover
        $this->fieldsFactory()->Create(SPL_T_BOOL)
            ->Identifier("cover")
            ->InList("images")
            ->Name("Is Cover")
            ->Group("Images")
            ->MicroData("http://schema.org/Product", "isCover")
            ->isNotTested();

        //====================================================================//
        // Product Images => Is Visible Image
        $this->fieldsFactory()->create(SPL_T_BOOL)
            ->Identifier("visible")
            ->InList("images")
            ->Name("Visible")
            ->Group("Images")
            ->Description("Image is visible for this Product Variant")
            ->MicroData("http://schema.org/Product", "isVisibleImage")
            ->Group("Images")
//            ->isReadOnly()
            ->isNotTested();
    }

    /**
     * Read requested Field
     *
     * @param string $key       Input List Key
     * @param string $fieldName Field Identifier / Name
     *
     * @return void
     */
    protected function getImagesFields($key, $fieldName): void
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
     * Write Given Fields
     *
     * @param string $fieldName Field Identifier / Name
     * @param mixed  $fieldData Field Data
     *
     * @return void
     */
    private function setImagesFields($fieldName, $fieldData)
    {
        //====================================================================//
        // WRITE Field
        switch ($fieldName) {
            //====================================================================//
            // PRODUCT IMAGES
            //====================================================================//
            case 'images':
                //==============================================================================
                // Detect ArrayObjects
                if ($fieldData instanceof ArrayObject) {
                    $fieldData = $fieldData->getArrayCopy();
                }
                $this->setImgArray($fieldData);

                break;
            default:
                return;
        }
        unset($this->in[$fieldName]);
    }

    /**
     * Return Product Image Array from Product Object Class
     *
     * @return void
     */
    private function getImgArray(): void
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
            $image = ShopifyImages::getInfos($shopifyImage['id'], $shopifyImage['src'], $shopifyImage['alt']);
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
            $this->out["images"][$key]["cover"] = self::isCoverImage($shopifyImage);
            // Shopify Image Visible Flag
            // Shopify Do Not Manage This Feature
            $this->out["images"][$key]["visible"] = true;
        }
    }

    /**
     * Update Product Image Array from Server Data
     *
     * @param array $data Input Image List for Update
     *
     * @return void
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    private function setImgArray(array $data)
    {
        //====================================================================//
        // Init
        $this->imgPosition = 0;
        $this->imgFound = array();

        //====================================================================//
        // Given List Is Not Empty
        foreach ($data as $inValue) {
            //====================================================================//
            // Check Image Array is here
            if (!isset($inValue["image"]) || empty($inValue["image"])) {
                continue;
            }
            //====================================================================//
            // Check Image is Visible
            if (isset($inValue["visible"]) && empty($inValue["visible"])) {
                continue;
            }
            $this->imgPosition++;
            //====================================================================//
            // Search For Image In Current List
            $imgIndex = $this->searchImage($inValue["image"]["md5"]);
            //====================================================================//
            // If Not found, Add this object to list
            if (is_null($imgIndex)) {
                $imgIndex = $this->addImage($inValue["image"]);
            }
            //====================================================================//
            // If STILL Not found, Error
            if (is_null($imgIndex)) {
                Splash::log()->errTrace("An Error occured while sending an image, please retry.");

                return;
            }
            //====================================================================//
            // Update Image Position in List
            $this->updateImagePosition($imgIndex, $inValue);
            $this->updateImageCoverFlag($imgIndex, $inValue);
        }
        //====================================================================//
        // Clear Remaining Local Images
        $this->cleanImages();
        //====================================================================//
        // Re-Index Images
        $this->object->images = array_values($this->object->images);
    }

    /**
     * Check if Image is Cover for THIS Variant
     *
     * @param array $shopifyImage Shopify Image Definition Array
     *
     * @return bool
     */
    private function isCoverImage(array $shopifyImage): bool
    {
        //====================================================================//
        // Present in Variants Ids
        return in_array((int) $this->variantId, $shopifyImage['variant_ids'], true);
    }

    /**
     * Search Image on Product Images List
     *
     * @param string $md5 Expected Image Md5
     *
     * @return null|int
     */
    private function searchImage($md5): ?int
    {
        if (!is_array($this->object->images)) {
            return null;
        }
        foreach ($this->object->images as $index => $shopifyImage) {
            //====================================================================//
            // If Image has no ID => NEW Image => Skip
            // If Image Already Found => Skip
            if (!isset($shopifyImage['id']) || in_array($shopifyImage['id'], $this->imgFound, true)) {
                continue;
            }
            //====================================================================//
            // Load Image Informations from cache or from API
            $splashImage = ShopifyImages::getInfos($shopifyImage['id'], $shopifyImage['src'], $shopifyImage['alt']);
            if (null == $splashImage) {
                Splash::log()->errTrace("An Error Occured while writting images, please retry");

                return null;
            }
            //====================================================================//
            // If CheckSum are Different => Continue
            if ($splashImage["md5"] != $md5) {
                continue;
            }
            //====================================================================//
            // Add Image ID to Found List
            $this->imgFound[] = $shopifyImage['id'];

            return $index;
        }

        return null;
    }

    /**
     * Add Product Image
     *
     * @param array|ArrayObject $splashImage
     *
     * @return null|int
     */
    private function addImage($splashImage): ?int
    {
        //==============================================================================
        // Detect ArrayObjects
        if ($splashImage instanceof ArrayObject) {
            $splashImage = $splashImage->getArrayCopy();
        }
        //==============================================================================
        // Create Image Array from Local System
        $newImage = ShopifyImages::buildImage($splashImage, $this->connector);
        //==============================================================================
        // Verify
        if (!is_array($newImage)) {
            return null;
        }
        //==============================================================================
        // Add Image to Product
        $imgIndex = count($this->object->images);
        $this->object->images[] = $newImage;
        $this->needUpdate();

        return $imgIndex;
    }

    /**
     * Remove Deleted Product Images
     *
     * @return void
     */
    private function cleanImages(): void
    {
        if (!is_array($this->object->images)) {
            return;
        }
        foreach ($this->object->images as $index => $shopifyImage) {
            //====================================================================//
            // If Image has no ID => NEW Image => Skip
            // If Image Already Found, Skip
            if (!isset($shopifyImage['id']) || in_array($shopifyImage['id'], $this->imgFound, true)) {
                continue;
            }
            //====================================================================//
            // Remove Image from Product
            unset($this->object->images[$index]);
            $this->needUpdate();
        }
    }

    /**
     * Update Image Position
     *
     * @param int   $imgIndex Image Index in Product Images
     * @param array $imgArray Splash Image Value Definition Array
     *
     * @retrurn     void
     */
    private function updateImagePosition($imgIndex, $imgArray): void
    {
        //====================================================================//
        // Safety Checks
        if (!isset($this->object->images[$imgIndex])) {
            return;
        }
        $newPosition = (int) isset($imgArray["position"]) ? $imgArray["position"]: $this->imgPosition;
        //====================================================================//
        // Needed ?
        if ($this->object->images[$imgIndex]["position"] == $newPosition) {
            return;
        }
        //====================================================================//
        // Update Image Position in List
        $this->object->images[$imgIndex]["position"] = $newPosition;
        $this->needUpdate();
    }

    /**
     * Update Image Cover Flag
     *
     * @param int   $imgIndex Image Index in Product Images
     * @param array $imgArray Splash Image Value Definition Array
     *
     * @retrurn     void
     */
    private function updateImageCoverFlag($imgIndex, $imgArray): void
    {
        //====================================================================//
        // Safety Checks
        if (!isset($this->object->images[$imgIndex]) || !isset($imgArray["cover"])) {
            return;
        }
        $isCover = self::isCoverImage($this->object->images[$imgIndex]);
        //====================================================================//
        // Needed ?
        if ($isCover == $imgArray["cover"]) {
            return;
        }
        $this->needUpdate();
        //====================================================================//
        // Set Image Cover Flag
        if ($imgArray["cover"]) {
            $this->object->images[$imgIndex]['variant_ids'][] = (int) $this->variantId;

            return;
        }
        //====================================================================//
        // Unset Image Cover Flag
        $this->object->images[$imgIndex]["variant_ids"] = array_diff(
            $this->object->images[$imgIndex]["variant_ids"],
            array((int) $this->variantId)
        );
    }
}
