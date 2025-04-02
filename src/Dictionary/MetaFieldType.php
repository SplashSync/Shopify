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

namespace Splash\Connectors\Shopify\Dictionary;

/**
 * Dictionary for Shopify Simple MetaFields Types
 */
class MetaFieldType
{
    /**
     * Boolean value (true or false)
     */
    public const BOOLEAN = 'boolean';

    /**
     * Représente une couleur au format hexadécimal (ex. : #RRGGBB)
     */
    public const COLOR = 'color';

    /**
     * Date au format ISO 8601 (AAAA-MM-JJ)
     */
    public const DATE = 'date';

    /**
     * Date et heure au format ISO 8601 (AAAA-MM-JJTHH:MM:SS)
     */
    public const DATE_TIME = 'date_time';

    /**
     * A unique single-line text field. You can add validations for min, max, and regex.
     */
    public const ID = 'id';

    /**
     * A single-line text field.
     */
    public const STRING = 'single_line_text_field';

    /**
     * A multi-line text field.
     */
    public const MULTILINE_TEXT = 'multi_line_text_field';

    /**
     * Données structurées au format JSON
     */
    public const JSON = 'json';

    /**
     * A numeric amount, with a currency code that matches the store's currency.
     */
    public const PRICE = 'money';

    /**
     * A whole number in the range of +/-9,007,199,254,740,991.
     */
    public const INTEGER = 'number_integer';

    /**
     * A number with decimal places in the range of +/-9999999999999.999999999.
     */
    public const FLOAT = 'number_decimal';

    /**
     * Représente une mesure de longueur avec unité (ex. : 5cm, 10in)
     */
    public const DIMENSION = 'dimension';

    /**
     * A value and a unit of weight.
     *
     * Valid unit values: oz, lb, g, kg
     */
    public const WEIGHT = 'weight';

    /**
     * Checks if the provided type is a valid list type
     */
    public static function isListType(string $type): bool
    {
        return (str_starts_with($type, "list."));
    }

    /**
     * Checks if the provided type is a valid list type and, if it is, removes the "list." prefix.
     *
     * @param string $type The type to check and potentially modify.
     *
     * @return null|string The modified type without the "list." prefix if valid, or null if not a list type.
     */
    public static function getListType(string $type): ?string
    {
        return self::isListType($type)
            ? str_replace("list.", "", $type)
            : null
        ;
    }
}
