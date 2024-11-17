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

namespace Splash\Connectors\Shopify\Objects\Product\Variants;

use Splash\Core\SplashCore as Splash;

/**
 * Prestashop Product Variant Core Data Access
 */
trait CoreTrait
{
    //====================================================================//
    // Fields Generation Functions
    //====================================================================//

    /**
     * Build Fields using FieldFactory
     *
     * @return void
     */
    protected function buildVariantsCoreFields(): void
    {
        //====================================================================//
        // Product Variation Parent Link
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->identifier("id")
            ->name("Parent Product")
            ->group("Meta")
            ->microData("http://schema.org/Product", "isVariationOf")
            ->isReadOnly()
        ;

        //====================================================================//
        // CHILD PRODUCTS INFORMATIONS
        //====================================================================//

        //====================================================================//
        // Product Variation List - Product Link
        $this->fieldsFactory()->create((string) self::objects()->Encode("Product", SPL_T_ID))
            ->identifier("id")
            ->name("Variants")
            ->inList("variants")
            ->microData("http://schema.org/Product", "Variants")
            ->isNotTested()
        ;

        //====================================================================//
        // Product Variation List - Product SKU
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->identifier("sku")
            ->name("SKU")
            ->inList("variants")
            ->microData("http://schema.org/Product", "VariationName")
            ->isReadOnly()
        ;

        //====================================================================//
        // Product Variation List - Variation Attribute
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->identifier("options")
            ->name("Options")
            ->inList("variants")
            ->microData("http://schema.org/Product", "VariationAttribute")
            ->isReadOnly()
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
    protected function getVariantsCoreFields(string $key, string $fieldName): void
    {
        //====================================================================//
        // READ Fields
        switch ($fieldName) {
            case 'id':
                $this->getSimple($fieldName);

                break;
            default:
                return;
        }

        unset($this->in[$key]);
    }

    /**
     * Read requested Field
     *
     * @param string $key       Input List Key
     * @param string $fieldName Field Identifier / Name
     *
     * @return void
     */
    protected function getVariantsListFields(string $key, string $fieldName): void
    {
        //====================================================================//
        // Check if List field & Init List Array
        $fieldId = self::lists()->initOutput($this->out, "variants", $fieldName);
        if (!$fieldId) {
            return;
        }

        //====================================================================//
        // READ Fields
        foreach ($this->object->variants as $index => $variant) {
            //====================================================================//
            // SKIP Current Variant When in PhpUnit/Travis Mode
            // Only Existing Variant will be Returned
            if (!empty(Splash::input('SPLASH_TRAVIS')) && ($this->variantId == $variant['id'])) {
                continue;
            }

            //====================================================================//
            // Get Variant Infos
            switch ($fieldId) {
                case 'id':
                    $value = self::objects()
                        ->encode(
                            "Product",
                            $this->getObjectId((string) $this->productId, $variant['id'])
                        );

                    break;
                case 'sku':
                    $value = $variant["sku"];

                    break;
                case 'options':
                    $variantOptions = array($variant["option1"], $variant["option2"], $variant["option3"]);
                    $value = implode(" | ", array_filter($variantOptions));

                    break;
                default:
                    return;
            }

            self::lists()->insert($this->out, "variants", $fieldId, $index, $value);
        }

        unset($this->in[$key]);
        //====================================================================//
        // Sort Attributes by Code
        if (is_array($this->out["variants"])) {
            ksort($this->out["variants"]);
        }
    }

    /**
     * Write Given Fields
     *
     * @param string $fieldName Field Identifier / Name
     * @param mixed  $fieldData Field Data
     *
     * @return void
     */
    protected function setVariantsListFields(string $fieldName, mixed $fieldData): void
    {
        //====================================================================//
        // Safety Check
        if ("variants" !== $fieldName) {
            return;
        }
        unset($this->in[$fieldName]);
    }

    /**
     * Identify Default Variant Product ID
     *
     * @return null|string
     */
    private function getParentProductId(): ?string
    {
        //====================================================================//
        // Not a Variable Product => No default
        if (!isset($this->in["variants"]) || !is_iterable($this->in["variants"])) {
            return null;
        }
        //====================================================================//
        // Identify Parent in Parent Products Ids
        foreach ($this->in["variants"] as $variant) {
            //====================================================================//
            // Safety Check => Id is Here
            if (!is_array($variant) || empty($variant['id']) || !is_scalar($variant['id'])) {
                continue;
            }
            //====================================================================//
            // Safety Check => Is Product Object Id
            if ("Product" !== self::objects()->type((string) $variant['id'])) {
                continue;
            }
            //====================================================================//
            // Extract Object Id
            $productId = self::getProductId((string) self::objects()->id((string) $variant['id']));
            //====================================================================//
            // Safety Check => Is Product Object Id is Here
            if (empty($productId) || !is_scalar($productId)) {
                continue;
            }

            return $productId;
        }

        return null;
    }
}
