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

use Splash\Components\UnitConverter;

/**
 * Splash data transformer for Shopify Weight Fields
 */
class WeightTransformer extends AbstractJsonDataTransformer
{
    const VAL = "value";

    const UNIT = "unit";

    const KNOWN_UNITS = array(
        "g" => UnitConverter::MASS_GRAM,
        "kg" => UnitConverter::MASS_KILOGRAM,
        "oz" => UnitConverter::MASS_OUNCE,
        "lb" => UnitConverter::MASS_LIVRE,
    );

    const ISO_UNITS = array(
        "kg" => UnitConverter::MASS_KILOGRAM,
        "g" => UnitConverter::MASS_GRAM,
    );

    /**
     * @inheritDoc
     */
    public function transform($value): ?float
    {
        if (!$arrayValue = $this->transformJson($value)) {
            return null;
        }
        //====================================================================//
        // Detect Splash Generic Unit Factor
        $splFactor = self::KNOWN_UNITS[$arrayValue[self::UNIT]] ?? UnitConverter::MASS_KILOGRAM;

        //====================================================================//
        // Convert Value to Generic Factor
        return UnitConverter::normalizeWeight((float) ($arrayValue[self::VAL] ?? 0.0), $splFactor);
    }

    /**
     * @inheritDoc
     */
    public function reverseTransform($value): ?string
    {
        $result = array(self::VAL => "0", self::UNIT => "kg");
        if (!is_numeric($value)) {
            return $this->reverseTransformJson($result);
        }
        $value = floatval($value);
        //====================================================================//
        // Detect Best Unit
        foreach (self::ISO_UNITS as $unit => $factor) {
            if ($value >= UnitConverter::normalizeWeight(1.0, $factor)) {
                $result[self::VAL] = UnitConverter::convertWeight(
                    (float) $value,
                    (float) $factor
                );
                $result[self::UNIT] = $unit;

                break;
            }
        }

        return $this->reverseTransformJson($result);
    }
}
