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

namespace Splash\Connectors\Shopify\Models;

use Splash\Connectors\Shopify\Services\MetaFieldsManager;

trait ConnectorMetaFieldsTrait
{
    /**
     * Get Shopify MetaFields Manager
     */
    public function getMetaFieldsManager(): MetaFieldsManager
    {
        return $this->metaFieldsManager;
    }

    /**
     * Get List of Shopify MetaFields
     *
     * @return bool
     */
    private function fetchMetaFieldsLists(): bool
    {
        //====================================================================//
        // Check if MetaFields feature is Active
        $isActive = $this->hasMetaFieldsFeature();
        //====================================================================//
        // Collect Products MetaFields
        $this->setParameter(
            "MetaFieldsProducts",
            $isActive ? $this->metaFieldsManager->getList("PRODUCT") : array()
        );
        $this->setParameter(
            "MetaFieldsProductsVariants",
            $isActive ? $this->metaFieldsManager->getList("PRODUCTVARIANT") : array()
        );

        return true;
    }
}
