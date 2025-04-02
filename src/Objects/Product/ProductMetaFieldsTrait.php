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

use Splash\Connectors\Shopify\Objects\Core\MetaFieldsMapperTrait;

trait ProductMetaFieldsTrait
{
    use MetaFieldsMapperTrait;

    /**
     * Generate Optional Product Fields
     */
    protected function buildProductMetaFields(): void
    {
        $this->generateMetaFields(
            $this->getProductMetaFieldsList(),
            "products",
            "Product Fields"
        );
    }

    /**
     * Read Product Optional Fields
     */
    protected function getProductMetaFields(string $key, string $fieldName): void
    {
        $this->readMetaFields(
            $this->getProductMetaFieldsList(),
            "products",
            (string) $this->getProductId($this->object->id),
            $key,
            $fieldName
        );
    }

    /**
     * Write Product Optional Fields
     */
    protected function setProductMetaFields(string $fieldName, null|bool|int|float|string|array $fieldData): void
    {
        $this->writeMetaFields(
            $this->getProductMetaFieldsList(),
            "products",
            (string) $this->getProductId($this->object->id),
            $fieldName,
            $fieldData
        );
    }

    /**
     * Get List of Product Optional Meta Fields
     *
     * @return array[]
     */
    private function getProductMetaFieldsList(): array
    {
        $productMetaFields = $this->connector->getParameter("MetaFieldsProducts", array());

        return is_array($productMetaFields) ? $productMetaFields : array();
    }
}
