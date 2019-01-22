<?php

/*
 * This file is part of SplashSync Project.
 *
 * Copyright (C) Splash Sync <www.splashsync.com>
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Splash\Connectors\Shopify\Objects\Product;

use Splash\Core\SplashCore      as Splash;

/**
 * Access to Product Descriptions Fields
 *
 * @author      B. Paquier <contact@splashsync.com>
 */
trait DescTrait
{
    
    /**
    * Build Description Fields using FieldFactory
    */
    private function buildDescFields()
    {
        
        $groupSEO  = "SEO";
        
        //====================================================================//
        // PRODUCT DESCRIPTIONS
        //====================================================================//

        //====================================================================//
        // Name with Options
        $this->fieldsFactory()->Create(SPL_T_VARCHAR)
                ->Identifier("variant_title")
                ->Name("Title with Options")
                ->isListed()
                ->MicroData("http://schema.org/Product", "name")
                ->isReadOnly()
                ;
        
//        //====================================================================//
//        // Short Description
//        $this->fieldsFactory()->Create(SPL_T_MVARCHAR)
//                ->Identifier("description_short")
//                ->Name(Translate::getAdminTranslation("Short Description", "AdminProducts"))
//                ->Group($GroupName)
//                ->MicroData("http://schema.org/Product","description");
//
//        //====================================================================//
//        // Meta Description
//        $this->fieldsFactory()->Create(SPL_T_MVARCHAR)
//                ->Identifier("meta_description")
//                ->Name(Translate::getAdminTranslation("Meta description", "AdminProducts"))
//                ->Description($GroupName2 . " " . Translate::getAdminTranslation("Meta description", "AdminProducts"))
//                ->Group($GroupName2)
//                ->MicroData("http://schema.org/Article","headline");

//        //====================================================================//
//        // Meta Title
//        $this->fieldsFactory()->Create(SPL_T_MVARCHAR)
//                ->Identifier("meta_title")
//                ->Name(Translate::getAdminTranslation("Meta title", "AdminProducts"))
//                ->Description($GroupName2 . " " . Translate::getAdminTranslation("Meta title", "AdminProducts"))
//                ->Group($GroupName2)
//                ->MicroData("http://schema.org/Article","name");
//
//        //====================================================================//
//        // Meta KeyWords
//        $this->fieldsFactory()->Create(SPL_T_MVARCHAR)
//                ->Identifier("meta_keywords")
//                ->Name(Translate::getAdminTranslation("Meta keywords", "AdminProducts"))
//                ->Description($GroupName2 . " " . Translate::getAdminTranslation("Meta keywords", "AdminProducts"))
//                ->MicroData("http://schema.org/Article","keywords")
//                ->Group($GroupName2)
//                ->isReadOnly();
//
        //====================================================================//
        // Meta Url
        $this->fieldsFactory()->Create(SPL_T_VARCHAR)
                ->Identifier("handle")
                ->Name("Friendly URL")
                ->Description("A unique human-friendly string for the product. Automatically generated from the product's title. Used by the Liquid templating language to refer to objects.")
                ->Group($groupSEO)
                ->addOption("isLowerCase")
                ->MicroData("http://schema.org/Product", "urlRewrite");
    }
    
    /**
     * Read requested Field
     *
     * @param        string $key       Input List Key
     * @param        string $fieldName Field Identifier / Name
     *
     * @return         void
     */
    protected function getDescFields($key, $fieldName)
    {
        switch ($fieldName) {
            case 'variant_title':
                $this->out[$fieldName] = ($this->object->title." - ".$this->variant->title);
                break;

            case 'handle':
                $this->getSimple($fieldName);
                break;
            
            default:
                return;
        }
        //====================================================================//
        // Clear Key Flag
        unset($this->in[$key]);
    }
    
    /**
     * Write Given Fields
     *
     * @param        string $fieldName Field Identifier / Name
     * @param        mixed  $fieldData      Field Data
     *
     * @return         void
     */
    protected function setDescFields($fieldName, $fieldData)
    {
        switch ($fieldName) {
            case 'handle':
                $this->setSimple($fieldName, $fieldData);
                break;
            
            default:
                return;
        }
        unset($this->in[$fieldName]);
    }
}
