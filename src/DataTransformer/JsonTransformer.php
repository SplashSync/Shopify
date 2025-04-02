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

class JsonTransformer implements DataTransformerInterface
{
    /**
     * @inheritDoc
     */
    public function transform($value): ?array
    {
        if (!is_string($value)) {
            return null;
        }

        try {
            $value = json_decode($value, true, 512, JSON_THROW_ON_ERROR);

            return is_array($value) ? $value : null;
        } catch (\Exception) {
            return null;
        }
    }

    /**
     * @inheritDoc
     */
    public function reverseTransform($value): ?string
    {
        if (!is_array($value)) {
            return null;
        }

        try {
            return json_encode($value, JSON_THROW_ON_ERROR) ?: null;
        } catch (\Exception) {
            return null;
        }
    }
}
