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

use Splash\Connectors\Shopify\DataTransformer as Transformers;
use Splash\Connectors\Shopify\Dictionary\MetaFieldType;
use Symfony\Component\Form\DataTransformerInterface;
use Webmozart\Assert\Assert;

/**
 * Generic Shopify Metadata Transformer
 *
 * Select Suitable Transformer for MetaField Type & Execute Transformations
 *
 * @SuppressWarnings(CouplingBetweenObjects)
 */
class MetaFieldTransformer
{
    /**
     * Convert MetaField to Splash Field Type
     */
    public static function getSplashType(array $metaField) : ?string
    {
        //====================================================================//
        // Extract Field Type
        $type = $metaField["type"]['name'] ?? null;
        if (!$type || !is_string($type)) {
            return null;
        }
        //====================================================================//
        // Detect List Types
        if ($listType = MetaFieldType::getListType($type)) {
            //====================================================================//
            // Convert Single fields to Splash
            return match ($listType) {
                MetaFieldType::FLOAT, MetaFieldType::INTEGER,
                MetaFieldType::DATE, MetaFieldType::DATE_TIME,
                MetaFieldType::DIMENSION, MetaFieldType::WEIGHT,
                MetaFieldType::ID, MetaFieldType::STRING, MetaFieldType::COLOR => SPL_T_INLINE,

                default => null,
            };
        }

        //====================================================================//
        // Convert Single fields to Splash
        return match ($type) {
            MetaFieldType::BOOLEAN => SPL_T_BOOL,
            MetaFieldType::FLOAT => SPL_T_DOUBLE,
            MetaFieldType::INTEGER => SPL_T_INT,
            MetaFieldType::ID, MetaFieldType::STRING, MetaFieldType::COLOR => SPL_T_VARCHAR,
            MetaFieldType::JSON, MetaFieldType::MULTILINE_TEXT => SPL_T_TEXT,
            MetaFieldType::DATE => SPL_T_DATE,
            MetaFieldType::DATE_TIME => SPL_T_DATETIME,
            MetaFieldType::PRICE => SPL_T_PRICE,
            MetaFieldType::DIMENSION => SPL_T_DOUBLE,
            MetaFieldType::WEIGHT => SPL_T_DOUBLE,

            default => null,
        };
    }

    /**
     * Convert MetaField ID to Field ID
     */
    public static function getId(string $prefix, array $metaField) : string
    {
        Assert::stringNotEmpty($prefix);
        Assert::stringNotEmpty($namespace = $metaField['namespace'] ?? null);
        Assert::stringNotEmpty($fieldId = $metaField['key'] ?? null);

        return sprintf("%s_%s_%s", $prefix, $namespace, $fieldId);
    }

    /**
     * Convert MetaField ID to Field Name
     */
    public static function getName(array $metaField) : string
    {
        Assert::stringNotEmpty($fieldId = $metaField['key'] ?? null);

        return ucwords(str_replace(array("_", "-"), " ", $fieldId));
    }

    /**
     * Extract MetaField Data Value
     */
    public static function getValue(array $metaData) : null|bool|int|float|string|array
    {
        //====================================================================//
        // Extract Field Type
        $type = $metaData["type"] ?? null;
        if (!$type || !is_string($type)) {
            return null;
        }
        //====================================================================//
        // Extract Field Value
        $value = $metaData["value"] ?? null;
        if (is_null($value)) {
            return null;
        }
        //====================================================================//
        // Extract Value using Data Transformer
        $result = self::getTransformer($type)?->transform($value);

        //====================================================================//
        // Return Value if Valid
        return (is_scalar($result) || is_array($result)) ? $result : null;
    }

    /**
     * Update MetaField Data Value
     */
    public static function setValue(array &$metaData, null|bool|int|float|string|array $value): bool
    {
        //====================================================================//
        // Compare Value
        $current = self::getValue($metaData);
        if ($current == $value) {
            return false;
        }
        //====================================================================//
        // Extract Field Type
        $type = $metaData["type"] ?? null;
        if (!$type || !is_string($type)) {
            return false;
        }
        //====================================================================//
        // Update Value using Data Transformer
        $metaData["value"] = self::getTransformer($type)?->reverseTransform($value);

        return true;
    }

    /**
     * Transforms a value from the transformed representation to its original
     * representation.
     */
    public static function getTransformer(string $type): ?DataTransformerInterface
    {
        //====================================================================//
        // Detect List Types
        if ($listType = MetaFieldType::getListType($type)) {
            //====================================================================//
            // Convert Single fields to Splash
            return new Transformers\ListInlineTransformer($listType);
        }

        return match ($type) {
            MetaFieldType::BOOLEAN => new Transformers\BooleanTransformer(),
            MetaFieldType::FLOAT => new Transformers\DecimalTransformer(),
            MetaFieldType::INTEGER => new Transformers\IntegerTransformer(),
            MetaFieldType::ID, MetaFieldType::STRING,
            MetaFieldType::COLOR, MetaFieldType::JSON,
            MetaFieldType::MULTILINE_TEXT => new Transformers\ScalarTransformer(),
            MetaFieldType::DATE => new Transformers\DateTransformer(),
            MetaFieldType::DATE_TIME => new Transformers\DateTimeTransformer(),
            MetaFieldType::PRICE => new Transformers\MoneyTransformer(),
            MetaFieldType::DIMENSION => new Transformers\DimensionTransformer(),
            MetaFieldType::WEIGHT => new Transformers\WeightTransformer(),

            default => null,
        };
    }
}
