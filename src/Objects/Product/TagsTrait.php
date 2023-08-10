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

use Splash\Models\Helpers\InlineHelper;

trait TagsTrait
{
    /**
     * Build Fields using FieldFactory
     */
    protected function buildTagsFields(): void
    {
        $this->fieldsFactory()->create(SPL_T_INLINE)
            ->identifier("tags")
            ->name("Tags")
            ->description("Used for filtering and search. A product can have up to 250 tags.")
            ->microData("http://schema.org/Product", "tags")
            ->addOption("isOrdered")
            ->addOption("isLowerCase")
            ->setPreferNone()
        ;
    }

    /**
     * Read requested Field
     */
    protected function getTagsFields(string $key, string $fieldName): void
    {
        if ("tags" != $fieldName) {
            return;
        }

        $this->out[$fieldName] = InlineHelper::fromArray(
            explode(", ", $this->object->tags)
        );
        unset($this->in[$key]);
    }

    /**
     * Write Given Fields
     */
    protected function setTagsFields(string $fieldName, string|null $fieldData): void
    {
        if ("tags" != $fieldName) {
            return;
        }
        $this->setSimple($fieldName, implode(", ", InlineHelper::toArray($fieldData)));

        unset($this->in[$fieldName]);
    }
}
