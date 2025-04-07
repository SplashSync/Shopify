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

use Symfony\Component\Form\DataTransformerInterface;

/**
 * Base class for Shopify Transformers using Json Data Inputs
 */
abstract class AbstractJsonDataTransformer implements DataTransformerInterface
{
    /**
     * Get Json Data Transformer
     */
    public function getJsonTransformer(): JsonTransformer
    {
        static $jsonTransformer;

        return $jsonTransformer ??= new JsonTransformer();
    }
    /**
     * Use Json Transformer to Parse Json Inputs
     */
    protected function transformJson(mixed $value): ?array
    {
        return  $this->getJsonTransformer()->transform($value);
    }

    /**
     * Use Json Transformer to Build Json Outputs
     */
    protected function reverseTransformJson(array $value): ?string
    {
        return  $this->getJsonTransformer()->reverseTransform($value);
    }
}
