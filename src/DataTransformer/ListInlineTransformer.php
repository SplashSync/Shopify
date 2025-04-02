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

namespace Splash\Connectors\Shopify\DataTransformer;

use Splash\Connectors\Shopify\Helpers\MetaFieldTransformer;
use Splash\Models\Helpers\InlineHelper;

/**
 * Transform List Field to Splash Inline Value, using Simple Fields Transformers
 */
class ListInlineTransformer extends AbstractJsonDataTransformer
{
    public function __construct(
        private string $fieldType
    ) {
    }

    /**
     * @inheritDoc
     */
    public function transform($value): ?string
    {
        if (!$arrayValue = $this->transformJson($value)) {
            return null;
        }
        //====================================================================//
        // Apply Simple Field Transformer to all Items
        $arrayResult = array_map(
            function ($item): ?string {
                $value = MetaFieldTransformer::getTransformer($this->fieldType)?->transform($item);

                return is_scalar($value) ? (string) $value : null;
            },
            $arrayValue
        );

        //====================================================================//
        // Convert to Inline
        return InlineHelper::fromArray($arrayResult);
    }

    /**
     * @inheritDoc
     */
    public function reverseTransform($value): ?string
    {
        //====================================================================//
        // Empty Value Received
        if (!$value || !is_string($value)) {
            return null;
        }
        //====================================================================//
        // Convert Inline to Array
        if (!$arrayValue = InlineHelper::toArray($value)) {
            return null;
        }
        //====================================================================//
        // Apply Simple Field Transformer to all Items
        $arrayResult = array_map(
            function ($item): ?string {
                $value = MetaFieldTransformer::getTransformer($this->fieldType)?->reverseTransform($item);

                return is_scalar($value) ? (string) $value : null;
            },
            $arrayValue
        );

        return $this->reverseTransformJson($arrayResult);
    }
}
