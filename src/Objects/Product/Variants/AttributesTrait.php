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

namespace Splash\Connectors\Shopify\Objects\Product\Variants;

use Splash\Core\SplashCore      as Splash;

/**
 * WooCommerce Product Variants Attributes Data Access
 */
trait AttributesTrait
{
    /**
     * List of Available Options
     *
     * @var array
     */
    private static array $optionsList = array(
        0 => 'option1',
        1 => 'option2',
        2 => 'option3'
    );

    //====================================================================//
    // Fields Generation Functions
    //====================================================================//

    /**
     * Build Attributes Fields using FieldFactory
     *
     * @return void
     */
    protected function buildVariantsAttributesFields(): void
    {
        $groupName = "Options";

        //====================================================================//
        // Product Variation List - Variation Attribute Code
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->identifier("code")
            ->name("Code")
            ->inList("attributes")
            ->group($groupName)
            ->microData("http://schema.org/Product", "VariantAttributeCode")
            ->isReadOnly(empty(Splash::input('SPLASH_TRAVIS')))
            ->isNotTested()
        ;
        //====================================================================//
        // Product Variation List - Variation Attribute Name
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->identifier("name")
            ->name("Name")
            ->inList("attributes")
            ->group($groupName)
            ->microData("http://schema.org/Product", "VariantAttributeName")
            ->isNotTested()
        ;
        //====================================================================//
        // Product Variation List - Variation Attribute Value
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->identifier("value")
            ->name("Value")
            ->inList("attributes")
            ->group($groupName)
            ->microData("http://schema.org/Product", "VariantAttributeValue")
            ->isNotTested()
        ;
    }

    //====================================================================//
    // Fields Reading Functions
    //====================================================================//

    /**
     * Read requested Field
     *
     * @param string $key       Input List Key
     * @param string $fieldName Field Identifier / Name
     *
     * @return void
     */
    protected function getVariantsAttributesFields(string $key, string $fieldName): void
    {
        //====================================================================//
        // Check if List field & Init List Array
        $fieldId = self::lists()->initOutput($this->out, "attributes", $fieldName);
        if (!$fieldId) {
            return;
        }

        //====================================================================//
        // READ Fields
        foreach (self::$optionsList as $code => $name) {
            //====================================================================//
            // Ensure Variant Option Exists
            if (!isset($this->variant->{$name})) {
                continue;
            }
            //====================================================================//
            // Get Variant Infos
            $value = $this->getVariantsAttributesFieldValue($fieldId, $code, $name);

            //====================================================================//
            // Add Info to Attributes List
            self::lists()->insert($this->out, "attributes", $fieldId, $code, $value);
        }
        unset($this->in[$key]);
        //====================================================================//
        // Sort Attributes by Code
        if (is_array($this->out["attributes"])) {
            ksort($this->out["attributes"]);
        }
    }

    //====================================================================//
    // Fields Writing Functions
    //====================================================================//

    /**
     * Write Given Fields
     *
     * @param string $fieldName Field Identifier / Name
     * @param array  $fieldData Field Data
     *
     * @return void
     */
    protected function setVariantsAttributesFields(string $fieldName, array $fieldData): void
    {
        //====================================================================//
        // Safety Check
        if ("attributes" !== $fieldName) {
            return;
        }

        //====================================================================//
        // Update Products Attributes Ids
        $index = 0;
        foreach ($fieldData as $item) {
            //====================================================================//
            // Check Product Attributes is Valid & Not More than 3 Options!
            if (!is_array($item) || !$this->isValidAttributeDefinition($item) && ($index < 3)) {
                continue;
            }
            //====================================================================//
            // Build Attribute Name
            // Travis Mode => Encode Code & Name
            $attrName = !empty(Splash::input('SPLASH_TRAVIS'))
                    ? $item["code"]."@".$item["name"]
                    : $item["name"]
            ;
            //====================================================================//
            // Update Attribute Name
            if (!isset($this->object->options[$index])) {
                $this->object->options[$index] = array(
                    'name' => $attrName,
                    'position' => $index + 1,
                );
            }
            $this->object->options[$index]["name"] = $attrName;
            //====================================================================//
            // Update Attribute Value
            $this->setSimple("option".($index + 1), $item["value"], "variant");
            //====================================================================//
            // Inc. Attribute Index
            $index++;
        }

        unset($this->in[$fieldName]);
    }

    //====================================================================//
    // CRUD Functions
    //====================================================================//

    /**
     * Check if Attribute Array is Valid for Writing
     *
     * @param array $attrData Attribute Array
     *
     * @return bool
     */
    private function isValidAttributeDefinition(array $attrData): bool
    {
        //====================================================================//
        // Check Attributes Names are Given
        if (!is_scalar($attrData["name"]) || empty($attrData["name"])) {
            return Splash::log()->errTrace("Product Attribute Public Name is Not Valid.");
        }
        //====================================================================//
        // Check Attributes Values are Given
        if (!is_scalar($attrData["value"]) || empty($attrData["value"])) {
            return Splash::log()->errTrace("Product Attribute Value Name is Not Valid.");
        }

        return true;
    }

    /**
     * Read requested Field
     *
     * @param string $fieldId
     * @param string $code
     * @param string $name
     *
     * @return null|string
     */
    private function getVariantsAttributesFieldValue(string $fieldId, string $code, string $name): ?string
    {
        $attrName = $this->object->options[$code]["name"];

        //====================================================================//
        // Get Variant Infos
        switch ($fieldId) {
            case 'code':
                //====================================================================//
                // Normal Mode => Direct Reading
                if (empty(Splash::input('SPLASH_TRAVIS'))) {
                    return $attrName;
                }
                //====================================================================//
                // Travis Mode => If Encoded Attribute Name
                if (false !== strpos($attrName, "@")) {
                    return explode("@", $this->object->options[$code]["name"])[0];
                }
                //====================================================================//
                // Travis Mode => If NOT Encoded Attribute Name
                return $attrName;
            case 'name':
                //====================================================================//
                // Normal Mode => Direct Reading
                if (empty(Splash::input('SPLASH_TRAVIS'))) {
                    return $attrName;
                }
                //====================================================================//
                // Travis Mode => If Encoded Attribute Name
                if (false !== strpos($attrName, "@")) {
                    return explode("@", $this->object->options[$code]["name"])[1];
                }
                //====================================================================//
                // Travis Mode => If NOT Encoded Attribute Name
                return $attrName;
            case 'value':
                return $this->variant->{$name};
        }

        return null;
    }
}
