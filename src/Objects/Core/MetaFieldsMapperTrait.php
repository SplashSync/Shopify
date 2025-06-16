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

namespace Splash\Connectors\Shopify\Objects\Core;

use Splash\Connectors\Shopify\Helpers\MetaFieldTransformer;
use Webmozart\Assert\Assert;

/**
 * Core Mapper for Object MetaFields
 */
trait MetaFieldsMapperTrait
{
    /**
     * Generate Fields from Shopify MetaFields
     */
    protected function generateMetaFields(array $metaFields, string $owner, string $group = "Meta Fields"): void
    {
        //====================================================================//
        // Check if MetaFields feature is Active
        if (!$this->connector->hasMetaFieldsFeature()) {
            return;
        }
        //====================================================================//
        // Walk on Defined Object Meta Fields
        foreach ($metaFields as $metaField) {
            //====================================================================//
            // Transform to Splash Field Type
            if (!$splashType = MetaFieldTransformer::getSplashType($metaField)) {
                continue;
            }
            $fieldId = MetaFieldTransformer::getId($owner, $metaField);
            Assert::stringNotEmpty($name = MetaFieldTransformer::getName($metaField));
            Assert::stringNotEmpty($desc = $metaField['description'] ?? $metaField['description'] ?: $name);
            //====================================================================//
            // Transform to Splash Field Type
            $this->fieldsFactory()
                ->create($splashType, $fieldId)
                ->name($name)
                ->description($desc)
                ->microData(MetaFieldTransformer::getItemType($owner), $name)
                ->group($group)
            ;
        }
    }

    /**
     * Read Shopify Optional Fields
     */
    protected function readMetaFields(
        array $metaFields,
        string $ownerType,
        string $ownerId,
        string $key,
        string $fieldName
    ): void {
        //====================================================================//
        // Walk on Defined Meta Fields
        foreach ($metaFields as $metaField) {
            //====================================================================//
            // This is the Right Field ?
            if ($fieldName != MetaFieldTransformer::getId($ownerType, $metaField)) {
                continue;
            }
            //====================================================================//
            // Load Object Metadata
            $metaData = $this->connector->getMetaFieldsManager()->getMetadata(
                $ownerType,
                $ownerId,
                $metaField['namespace'],
                $metaField['key'],
            );
            //====================================================================//
            // Extract Field Data
            $this->out[$fieldName] = $metaData ? MetaFieldTransformer::getValue($metaData) : null;
            //====================================================================//
            // Make Field as Found
            unset($this->in[$key]);
        }
    }

    /**
     * Write Shopify Optional Fields
     */
    protected function writeMetaFields(
        array $metaFields,
        string $ownerType,
        string $ownerId,
        string $fieldName,
        null|bool|int|float|string|array $fieldData
    ): void {
        //====================================================================//
        // Walk on Defined Meta Fields
        foreach ($metaFields as $metaField) {
            //====================================================================//
            // This is the Right Field ?
            if ($fieldName != MetaFieldTransformer::getId($ownerType, $metaField)) {
                continue;
            }
            //====================================================================//
            // Load Object Metadata
            $metaData = $this->connector->getMetaFieldsManager()->getMetadata(
                $ownerType,
                $ownerId,
                $metaField['namespace'],
                $metaField['key'],
            );
            //====================================================================//
            // Create Object Metadata
            if (!$metaData) {
                $metaData = array(
                    "namespace" => $metaField['namespace'],
                    "key" => $metaField['key'],
                    "type" => $metaField["type"]['name'] ?? null,
                );
            }
            //====================================================================//
            // Update Field Data
            if (MetaFieldTransformer::setValue($metaData, $fieldData)) {
                $this->connector->getMetaFieldsManager()->setMetadata(
                    $ownerType,
                    $ownerId,
                    $metaData,
                );
            }
            //====================================================================//
            // Make Field as Updated
            unset($this->in[$fieldName]);
        }
    }
}
