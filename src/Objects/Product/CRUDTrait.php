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
use Exception;
use Splash\Connectors\Shopify\Models\ShopifyHelper as API;
use Splash\Core\SplashCore      as Splash;

/**
 * Shopify Product CRUD Functions
 */
trait CRUDTrait
{
    /**
     * Load Request Object
     *
     * @param string $objectId Object id
     *
     * @return null|ArrayObject
     */
    public function load(string $objectId): ?ArrayObject
    {
        //====================================================================//
        // Stack Trace
        Splash::log()->trace();
        //====================================================================//
        // Explode Storage Id
        $this->productId = $this->getProductId($objectId);
        $this->variantId = $this->getVariantId($objectId);
        //====================================================================//
        // Get Product from Api
        $product = API::get(self::getUri($this->productId), null, array(), "product");
        //====================================================================//
        // Fetch Object from Shopify
        if (null === $product) {
            return Splash::log()->errNull(" Unable to load Product (".$objectId.").");
        }
        //====================================================================//
        // Detect Published Flag
        $product['published'] = !empty($product['published_at']);
        unset($product['published_at']);

        //====================================================================//
        // Identify & Load Variant Infos
        if (!$this->loadVariant($product)) {
            return null;
        }
        //====================================================================//
        // Return Product
        $product["id"] = $objectId;

        return new ArrayObject($product, ArrayObject::ARRAY_AS_PROPS);
    }

    /**
     * Create Request Object
     *
     * @return null|ArrayObject
     */
    public function create(): ?ArrayObject
    {
        //====================================================================//
        // Stack Trace
        Splash::log()->trace();
        //====================================================================//
        // Check if Existing Variants Ids are Given
        if (null !== $this->getParentProductId()) {
            return $this->createVariant();
        }
        //====================================================================//
        // Check Title & Desc given
        if (empty($this->in["title"])) {
            Splash::log()->err("ErrLocalFieldMissing", __CLASS__, __FUNCTION__, "Product Title");

            return null;
        }

        //====================================================================//
        // Create New Product from Api
        try {
            $response = API::post(
                "products",
                array( "product" => array(
                    "title" => $this->in["title"],
                    "body_html" => $this->in["body_html"] ?? "",
                ),
                ),
                "product"
            );
        } catch (Exception $exception) {
            return Splash::log()->errNull($exception->getMessage());
        }

        if (null === $response) {
            return Splash::log()->errNull(sprintf(
                "Unable to Create Product (%s)",
                is_scalar($this->in["title"]) ? $this->in["title"] : ""
            ));
        }
        $this->object = new ArrayObject($response, ArrayObject::ARRAY_AS_PROPS);

        //====================================================================//
        // Store New Ids
        $this->productId = $this->object->id;
        $this->variant = new ArrayObject(end($this->object->variants), ArrayObject::ARRAY_AS_PROPS);
        $this->variantIndex = 0;
        $this->variantId = !empty($this->variant->id) ? $this->variant->id : null;

        //====================================================================//
        // Default Setup for New Product Variant
        $this->setSimple("inventory_management", "shopify", "variant");

        return $this->object;
    }

    /**
     * Update Request Object
     *
     * @param bool $needed Is This Update Needed
     *
     * @return null|string
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function update(bool $needed): ?string
    {
        //====================================================================//
        // Stack Trace
        Splash::log()->trace();
        //====================================================================//
        // Encode Object Id
        if ((null === $this->productId) || (null === $this->variantId)) {
            return Splash::log()->errNull(" Unable to Update Product Variant (Wrong Ids).");
        }
        $objectId = $this->getObjectIdentifier();

        try {
            //====================================================================//
            // Update Product Variant from Api
            if ($needed || $this->isToUpdate("variant")) {
                $this->object->variants[$this->variantIndex] = $this->variant;
                if (null === API::put(self::getUri($this->productId), array("product" => $this->object))) {
                    return Splash::log()->errNull(" Unable to Update Product Variant (".$objectId.").");
                }
            }
            //====================================================================//
            // Update Inventory Level
            if ($this->isToUpdate("inventory")) {
                $newInventoryLevel = $this->getNewInventorylevel();
                if (is_null($newInventoryLevel) || (null === API::post('inventory_levels/set', $newInventoryLevel))) {
                    return Splash::log()->errNull(" Unable to Update Product Variant Stock (".$objectId.").");
                }
            }
        } catch (Exception $exception) {
            return Splash::log()->errNull($exception->getMessage());
        }
        //====================================================================//
        // Take Time in Tests Phases
        if (Splash::isTravisMode()) {
            usleep(250000);
        }

        return $objectId;
    }

    /**
     * {@inheritdoc}
     */
    public function delete(string $objectId): bool
    {
        //====================================================================//
        // Stack Trace
        Splash::log()->trace();
        //====================================================================//
        // Explode Storage Id
        $this->productId = self::getProductId($objectId);
        $this->variantId = self::getVariantId($objectId);
        //====================================================================//
        // Count Product Variants from Api
        $variantsCount = API::count(self::getUri($this->productId)."/variants");
        if (null === $variantsCount) {
            Splash::log()->errTrace("Unable to Count Product Variants (".$objectId.").");
        }
        //====================================================================//
        // Delete Product Variant from Api
        if (!API::delete(self::getUri($this->productId, $this->variantId))) {
            Splash::log()->errTrace("Unable to Delete Product (".$objectId.").");

            return true;
        }
        //====================================================================//
        // Last Product Variant ? Delete Whole Product from Api
        if (1 == $variantsCount) {
            if (null === API::delete(self::getUri($this->productId))) {
                Splash::log()->errTrace(" Unable to Delete Product (".$objectId.").");
            }
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function getObjectIdentifier(): ?string
    {
        if (!isset($this->productId) || !isset($this->variantId)) {
            return null;
        }

        //====================================================================//
        // Encode Object Id
        return $this->getObjectId($this->productId, $this->variantId);
    }

    /**
     * Get Object CRUD Base Uri
     *
     * @param null|string $productId
     * @param null|string $variantId
     *
     * @return string
     */
    private static function getUri(string $productId = null, string $variantId = null) : string
    {
        $baseUri = 'products/'.$productId;
        if (!is_null($variantId)) {
            $baseUri .= "/variants/".$variantId;
        }

        return $baseUri;
    }
}
