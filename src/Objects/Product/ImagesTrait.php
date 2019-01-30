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

/**
 * Access to Product Images Fields
 */
trait ImagesTrait
{
    use SplashImagesTrait;
    
//    /**
//     *  @abstract     Write Given Fields
//     *
//     *  @param        string    $FieldName              Field Identifier / Name
//     *  @param        mixed     $Data                   Field Data
//     *
//     *  @return         none
//     */
//    private function setImagesFields($FieldName,$Data)
//    {
//        //====================================================================//
//        // WRITE Field
//        switch ($FieldName)
//        {
//            //====================================================================//
//            // PRODUCT IMAGES
//            //====================================================================//
//            case 'images':
//                if ( $this->object->id ) {
//                    $this->setImgArray($Data);
//                    $this->setImgArray($Data);
//                } else {
//                    $this->NewImagesArray = $Data;
//                }
//                break;
//
//            default:
//                return;
//        }
//        unset($this->in[$FieldName]);
//    }
    
    /**
     * Return Product Image Array from Product Object Class
     *
     * @return void
     */
    public function getImgArray()
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
            // Touche Image with Curl (Incase first reading)
            $this->images()->touchRemoteFile($shopifyImage['src']);
            //====================================================================//
            // Insert Image in Output List
            $image = $this->images()->EncodeFromUrl(
                ($shopifyImage['alt'] ? $shopifyImage['alt'] : basename(parse_url($shopifyImage['src'], PHP_URL_PATH))),
                $shopifyImage['src'],
                $shopifyImage['src']
            );
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
     * Build Fields using FieldFactory
     */
    private function buildImagesFields()
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
            ->MicroData("http://schema.org/Product", "image")
            ->isReadOnly();
        
        //====================================================================//
        // Product Images => Image Position In List
        $this->fieldsFactory()->create(SPL_T_INT)
            ->Identifier("position")
            ->InList("images")
            ->Name("Position")
            ->Description("Image Order for this Product Variant")
            ->MicroData("http://schema.org/Product", "positionImage")
            ->isNotTested();
        
        //====================================================================//
        // Product Images => Is Cover
        $this->fieldsFactory()->Create(SPL_T_BOOL)
            ->Identifier("cover")
            ->InList("images")
            ->Name("Is Cover")
            ->MicroData("http://schema.org/Product", "isCover")
            ->isNotTested()
            ->isReadOnly();
        
        //====================================================================//
        // Product Images => Is Visible Image
        $this->fieldsFactory()->create(SPL_T_BOOL)
            ->Identifier("visible")
            ->InList("images")
            ->Name("Visible")
            ->Description("Image is visible for this Product Variant")
            ->MicroData("http://schema.org/Product", "isVisibleImage")
            ->Group("Product gallery")
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
    private function getImagesFields($key, $fieldName)
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
         
    //    /**
//    *   @abstract     Update Product Image Array from Server Data
//    *   @param        array   $Data             Input Image List for Update
//    */
//    public function setImgArray($Data)
//    {
//        //====================================================================//
//        // Safety Check
//        if (!is_array($Data) && !is_a($Data, "ArrayObject")) {
//            return False;
//        }
//
//        //====================================================================//
//        // Load Current Object Images List
//        //====================================================================//
//        // Load Object Images List
//        $ObjectImagesList   =   Image::getImages(
//                $this->LangId,
//                $this->object->id,
//                $this->AttributeId);
//        //====================================================================//
//        // UPDATE IMAGES LIST
//        //====================================================================//
//
//        $this->ImgPosition = 0;
//        //====================================================================//
//        // Given List Is Not Empty
//        foreach ($Data as $InValue) {
//            if ( !isset($InValue["image"]) || empty ($InValue["image"]) ) {
//                continue;
//            }
//            $this->ImgPosition++;
//            $InImage = $InValue["image"];
//            $IsCover = isset($InValue["cover"]) ? $InValue["cover"] : Null;
//
//            //====================================================================//
//            // Search For Image In Current List
//            $ImageFound = False;
//            foreach ($ObjectImagesList as $key => $ImageArray) {
//                //====================================================================//
//                // Fetch Images Object
//                $ObjectImage = new Image($ImageArray["id_image"],  $this->LangId);
//                //====================================================================//
//                // Compute Md5 CheckSum for this Image
//                $CheckSum = md5_file(
//                        _PS_PROD_IMG_DIR_
//                        . $ObjectImage->getImgFolder()
//                        . $ObjectImage->id . "."
//                        . $ObjectImage->image_format );
//                //====================================================================//
//                // If CheckSum are Different => Coninue
//                if ( $InImage["md5"] !== $CheckSum ) {
//                    continue;
//                }
//                //====================================================================//
//                // If Object Found, Unset from Current List
//                unset ($ObjectImagesList[$key]);
//                $ImageFound = True;
//                //====================================================================//
//                // Update Image Position in List
//                if ( !$this->AttributeId && ( $this->ImgPosition != $ObjectImage->position) ){
//                    $ObjectImage->updatePosition( $this->ImgPosition < $ObjectImage->position ,$this->ImgPosition);
//                }
//                //====================================================================//
//                // Update Image is Cover Flag
//                if ( !is_null($IsCover) && ((bool) $ObjectImage->cover) !==  ((bool) $IsCover) ){
//                    $ObjectImage->cover = $IsCover;
//                    $ObjectImage->update();
//                    $this->update = True;
//                }
//                break;
//            }
//            //====================================================================//
//            // If found, or on Product Attribute Update
//            if ( $ImageFound || $this->AttributeId) {
//                continue;
//            }
//            //====================================================================//
//            // If Not found, Add this object to list
//            $this->setImg($InImage,$IsCover);
//        }
//
//        //====================================================================//
//        // If Current Image List Is Empty => Clear Remaining Local Images
//        if ( !empty($ObjectImagesList) && !$this->AttributeId) {
//            foreach ($ObjectImagesList as $ImageArray) {
//                //====================================================================//
//                // Fetch Images Object
//                $ObjectImage = new Image($ImageArray["id_image"]);
//                $ObjectImage->deleteImage(True);
//                $ObjectImage->delete();
//                $this->needUpdate();
//            }
//        }
//
//        //====================================================================//
//        // Generate Images Thumbnail
//        //====================================================================//
//        // Load Object Images List
//        foreach (Image::getImages($this->LangId,$this->ProductId) as $image)  {
//            $imageObj   = new Image($image['id_image']);
//            $imagePath  = _PS_PROD_IMG_DIR_.$imageObj->getExistingImgPath();
//            if (!file_exists($imagePath.'.jpg')) {
//                continue;
//            }
//            foreach (ImageType::getImagesTypes("products") as $imageType)  {
//                $ImageThumb = _PS_PROD_IMG_DIR_.$imageObj->getExistingImgPath().'-'.Tools::stripslashes($imageType['name']).'.jpg';
//                if (!file_exists($ImageThumb)) {
//                    ImageManager::resize($imagePath.'.jpg', $ImageThumb, (int)($imageType['width']), (int)($imageType['height']));
//                }
//            }
//        }
//
//        return True;
//    }
//
//    /**
//    *   @abstract     Import a Product Image from Server Data
//    *   @param        array   $ImgArray             Splash Image Definition Array
//    */
//    public function setImg($ImgArray,$IsCover)
//    {
//        //====================================================================//
//        // Read File from Splash Server
//        $NewImageFile    =   Splash::File()->getFile($ImgArray["file"],$ImgArray["md5"]);
//
//        //====================================================================//
//        // File Imported => Write it Here
//        if ( $NewImageFile == False ) {
//            return False;
//        }
//        $this->update = True;
//
//        //====================================================================//
//        // Create New Image Object
//        $ObjectImage                = new Image();
//        $ObjectImage->label         = isset($NewImageFile["name"]) ? $NewImageFile["name"] : $NewImageFile["filename"];
//        $ObjectImage->id_product    = $this->ProductId;
//        $ObjectImage->position      = $this->ImgPosition;
//        $ObjectImage->cover         = $IsCover;
//
//        if ( !$ObjectImage->add() ) {
//            return False;
//        }
//
//        //====================================================================//
//        // Write Image On Folder
//        $Path       = dirname($ObjectImage->getPathForCreation());
//        $Filename   = "/" . $ObjectImage->id . "." . $ObjectImage->image_format;
//        Splash::File()->WriteFile($Path,$Filename,$NewImageFile["md5"],$NewImageFile["raw"]);
//    }
}
