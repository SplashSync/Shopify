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

namespace Splash\Connectors\Shopify\Helpers;

use DateTimeInterface;

/**
 * Execute Object Getter & Filter Type Errors
 */
class TypeErrorCatcher
{
    /**
     * Execute an Object Getter & Filter Type Errors
     *
     * @param object $object
     * @param string $method
     *
     * @return mixed
     */
    public static function get(object $object, string $method)
    {
        try {
            return $object->{ $method }();
        } catch (\TypeError $typeError) {
            return null;
        }
    }

    /**
     * Execute an Object String Getter & Filter Type Errors
     *
     * @param object $object
     * @param string $method
     *
     * @return null|string
     */
    public static function getString(object $object, string $method): ?string
    {
        $value = self::get($object, $method);

        return is_scalar($value) ? (string) $value : null;
    }

    /**
     * Execute an Object DateTime Getter & Filter Type Errors
     *
     * @param object $object
     * @param string $method
     *
     * @return null|DateTimeInterface
     */
    public static function getDateTime(object $object, string $method): ?DateTimeInterface
    {
        $value = self::get($object, $method);

        return ($value instanceof DateTimeInterface) ? $value : null;
    }
}
