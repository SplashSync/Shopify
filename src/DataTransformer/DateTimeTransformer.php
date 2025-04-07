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

use DateMalformedStringException;
use DateTime;
use Symfony\Component\Form\DataTransformerInterface;

class DateTimeTransformer implements DataTransformerInterface
{
    /**
     * @inheritDoc
     */
    public function transform($value): ?string
    {
        if (empty($value) || !is_string($value)) {
            return null;
        }

        try {
            $dateTime = new DateTime($value);
        } catch (DateMalformedStringException $e) {
            return null;
        }

        return $dateTime->format(SPL_T_DATETIMECAST);
    }

    /**
     * @inheritDoc
     */
    public function reverseTransform($value)
    {
        if (empty($value) || !is_string($value)) {
            return null;
        }

        try {
            $dateTime = new DateTime($value);
        } catch (DateMalformedStringException $e) {
            return null;
        }

        return $dateTime->format("Y-m-d\\TH:i:s");
    }
}
