<?php

/*
 *  This file is part of SplashSync Project.
 *
 *  Copyright (C) 2015-2019 Splash Sync  <www.splashsync.com>
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace Splash\Connectors\Shopify\Objects\Core;

use Splash\Components\UnitConverter as Units;

/**
 * Shopify Units Converter Trait
 */
trait UnitConverterTrait
{
    use \Splash\Models\Objects\UnitsHelperTrait;

    /**
     * @var array
     */
    private static $wcWeights = array(
        "g" => Units::MASS_GRAM,
        "kg" => Units::MASS_KG,
        "lb" => Units::MASS_LIVRE,
        "oz" => Units::MASS_OUNCE,
    );

    /**
     * Reading of a Product Variant Weight Value
     *
     * @param string $fieldName Field Identifier / Name
     *
     * @return self
     */
    protected function getVariantWheight($fieldName)
    {
        //====================================================================//
        //  Read Current Weight Unit
        $unit = self::$wcWeights[$this->variant->weight_unit];
        //====================================================================//
        //  Normalize Weight
        $this->out[$fieldName] = self::units()->normalizeWeight((float) $this->variant->weight, $unit);

        return $this;
    }

    /**
     * Common Writing of a Product Variant Weight Value
     *
     * @param float|string $fieldData Field Data
     *
     * @return self
     */
    protected function setVariantWheight($fieldData)
    {
        //====================================================================//
        //  Normalize Weight
        $realData = self::units()->convertWeight((float) $fieldData, Units::MASS_GRAM);
        //====================================================================//
        //  Write Field Data
        if (abs((float) $this->variant->grams - $realData) > 1E-6) {
            $this->variant->grams = $realData;
            unset($this->variant->weight);
            $this->needUpdate("variant");
//            $this->needUpdate();
        }

        return $this;
    }
}
