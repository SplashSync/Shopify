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
    private static int $imgCacheTtl = 3600;

    /**
     * @var int
     */
    private int $imgPosition = 0;

    /**
     * @var array Shopify ID of Already Found Images
     */
    private array $imgFound = array();

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
        $this->fieldsFactory()->create(SPL_T_IMG)
            ->identifier("image")
            ->inList("images")
            ->name("Image")
            ->group("Images")
            ->microData("http://schema.org/Product", "image")
        ;
        //====================================================================//
        // Product Images => Image Position In List
        $this->fieldsFactory()->create(SPL_T_INT)
            ->identifier("position")
            ->inList("images")
            ->name("Position")
            ->group("Images")
            ->description("Image Order for this Product Variant")
            ->microData("http://schema.org/Product", "positionImage")
            ->isNotTested()
        ;
        //====================================================================//
        // Product Images => Is Cover
        $this->fieldsFactory()->create(SPL_T_BOOL)
            ->identifier("cover")
            ->inList("images")
            ->name("Is Cover")
            ->group("Images")
            ->microData("http://schema.org/Product", "isCover")
            ->isNotTested()
        ;
        //====================================================================//
        // Product Images => Is Visible Image
        $this->fieldsFactory()->create(SPL_T_BOOL)
            ->identifier("visible")
            ->inList("images")
            ->name("Visible")
            ->group("Images")
            ->description("Image is visible for this Product Variant")
            ->microData("http://schema.org/Product", "isVisibleImage")
            ->group("Images")
            ->isNotTested()
        ;
    }

    /**
     * Read requested Field
     *
     * @param string $key       Input List Key
     * @param string $fieldName Field Identifier / Name
     *
     * @return void
     */
    protected function getImagesFields(string $key, string $fieldName): void
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
     * @param array  $fieldData Field Data
     *
     * @return void
     */
    protected function setImagesFields(string $fieldName, array $fieldData): void
    {
        if ('images' != $fieldName) {
            return;
        }
        $this->setImgArray($fieldData);
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
        // Images List Already Loaded
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
            $key = (string) $key;
            //====================================================================//
            // Load Image Information's from cache or from API
            $splashImage = ShopifyImages::getInfos($shopifyImage);
            //====================================================================//
            // Init Image List Item
            if (!isset($this->out["images"][$key])) {
                $this->out["images"][$key] = array();
            }
            // Shopify Image Raw data
            $this->out["images"][$key]["image"] = $splashImage;
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
    private function setImgArray(array $data): void
    {
        //====================================================================//
        // Init
        $this->imgPosition = 0;
        $this->imgFound = array();

        //====================================================================//
        // Given List Is Not Empty
        foreach ($data as $inValue) {
            //====================================================================//
            // Ensure Array
            $inValue = ($inValue instanceof ArrayObject) ? $inValue->getArrayCopy() : $inValue;
            //====================================================================//
            // Check Image Array is here
            if (empty($inValue["image"])) {
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
                Splash::log()->errTrace("An Error occurred while sending an image, please retry.");

                return;
            }
            //====================================================================//
            // Update Image Position in List
            $this->updateImagePosition($imgIndex, $inValue);
            // Update Image Cover Flag
            $this->updateImageCoverFlag($imgIndex, $inValue);

            $this->needUpdate();
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
    private function searchImage(string $md5): ?int
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
            // Load Image Metadata from Cache | API | Url
            $splashImage = ShopifyImages::getInfos($shopifyImage);
            if (null == $splashImage) {
                Splash::log()->errTrace("An Error Occurred while writing images, please retry");

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
     * @param array $splashImage
     *
     * @return null|int
     */
    private function addImage(array $splashImage): ?int
    {
        //==============================================================================
        // Try Reading of File on Local System
        $rawFile = $this->connector->file($splashImage["file"], $splashImage["md5"]);
        //==============================================================================
        // Verify File was Found
        if (!is_array($rawFile)) {
            return null;
        }
        //==============================================================================
        // Create Image Array from Local System
        $shopifyImage = ShopifyImages::buildShopifyImage($splashImage, $rawFile);
        //==============================================================================
        // Verify
        if (!is_array($shopifyImage)) {
            return null;
        }
        //==============================================================================
        // Add Image to Product
        $imgIndex = count($this->object->images);
        $this->object->images[] = $shopifyImage;
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
    private function updateImagePosition(int $imgIndex, array $imgArray): void
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
    private function updateImageCoverFlag(int $imgIndex, array $imgArray): void
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
