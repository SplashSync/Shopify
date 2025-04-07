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

class DimensionTransformer extends AbstractJsonDataTransformer
{
    const VAL = "value";

    const UNIT = "unit";

    const KNOWN_UNITS = array(
        "mm" => UnitConverter::LENGTH_MILIMETER,
        "cm" => UnitConverter::LENGTH_CENTIMETER,
        "m" => UnitConverter::LENGTH_METER,
        "in" => UnitConverter::LENGTH_INCH,
        "ft" => UnitConverter::LENGTH_FOOT,
        "yd" => UnitConverter::LENGTH_YARD,
    );

    const ISO_UNITS = array(
        "m" => UnitConverter::LENGTH_METER,
        "cm" => UnitConverter::LENGTH_CENTIMETER,
        "mm" => UnitConverter::LENGTH_MILIMETER,
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
        $splFactor = self::KNOWN_UNITS[$arrayValue[self::UNIT]] ?? UnitConverter::LENGTH_METER;

        //====================================================================//
        // Convert Value to Generic Factor
        return UnitConverter::normalizeLength((float) ($arrayValue[self::VAL] ?? 0.0), $splFactor);
    }

    /**
     * @inheritDoc
     */
    public function reverseTransform($value): ?string
    {
        $result = array(self::VAL => "0", self::UNIT => "m");
        if (!is_numeric($value)) {
            return $this->reverseTransformJson($result);
        }
        $value = floatval($value);
        //====================================================================//
        // Detect Best Unit
        foreach (self::ISO_UNITS as $unit => $factor) {
            if ($value >= UnitConverter::normalizeLength(1.0, $factor)) {
                $result[self::VAL] = UnitConverter::convertLength(
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
