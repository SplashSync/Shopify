<?php

/*
 *  This file is part of SplashSync Project.
 *
 *  Copyright (C) 2015-2021 Splash Sync  <www.splashsync.com>
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
 * Access to Product Stock Fields
 */
trait StockTrait
{
    /**
     * @var null|array
     */
    private $newInventoryLevel;

    /**
     * Get Stock Updated data
     *
     * @return null|array
     */
    protected function getNewInventorylevel()
    {
        $response = $this->newInventoryLevel;
        $this->newInventoryLevel = null;

        return $response;
    }

    /**
     * Build Fields using FieldFactory
     *
     * @return void
     */
    protected function buildStockFields(): void
    {
        //====================================================================//
        // PRODUCT STOCKS
        //====================================================================//

        //====================================================================//
        // Stock Reel
        $this->fieldsFactory()->Create(SPL_T_INT)
            ->Identifier("inventory_quantity")
            ->Name("Stock")
            ->MicroData("http://schema.org/Offer", "inventoryLevel")
            ->isListed();

        //====================================================================//
        // Out of Stock Flag
        $this->fieldsFactory()->Create(SPL_T_BOOL)
            ->Identifier("outofstock")
            ->Name("Out of stock")
            ->MicroData("http://schema.org/ItemAvailability", "OutOfStock")
            ->isReadOnly();
    }

    /**
     * Read requested Field
     *
     * @param string $key       Input List Key
     * @param string $fieldName Field Identifier / Name
     *
     * @return void
     */
    protected function getStockFields($key, $fieldName): void
    {
        //====================================================================//
        // READ Fields
        switch ($fieldName) {
            //====================================================================//
            // PRODUCT STOCKS
            //====================================================================//
            // Stock Reel
            case 'inventory_quantity':
                $this->getSimple($fieldName, "variant");

                break;
            //====================================================================//
            // Out Of Stock
            case 'outofstock':
                $this->out[$fieldName] = ($this->variant->inventory_quantity > 0) ? false : true;

                break;
            default:
                return;
        }

        unset($this->in[$key]);
    }

    /**
     * Write Given Fields
     *
     * @param string $fieldName Field Identifier / Name
     * @param mixed  $fieldData Field Data
     *
     * @return void
     */
    protected function setStockFields($fieldName, $fieldData): void
    {
        //====================================================================//
        // WRITE Field
        switch ($fieldName) {
            //====================================================================//
            // PRODUCT STOCKS
            //====================================================================//

            case 'inventory_quantity':
                //====================================================================//
                //  Compare Data
                if ($this->variant->inventory_quantity == $fieldData) {
                    break;
                }
                //====================================================================//
                // Check if Product uses Stock Manager => Cancel Product Stock Update
                if (empty($this->variant->inventory_management)) {
                    Splash::log()->war('Stock not Managed for this Product : Stock Update Skipped!!');

                    break;
                }
                //====================================================================//
                // Check if Product Default Stock Location is Selected => Cancel Product Stock Update
                $locationId = $this->getParameter("LocationId");
                if (empty($locationId)) {
                    Splash::log()->war('No Default Product Stock Location Selected : Stock Update Skipped!!');

                    break;
                }
                //====================================================================//
                // Update Variant Product Inventory Level
                $inventoryLevel = array(
                    "location_id" => (int) $locationId,
                    "inventory_item_id" => (int) $this->variant->inventory_item_id,
                    "available" => (int) $fieldData,
                );
                //====================================================================//
                // Store Chantges on Inventory Level for Post Set Update
                $this->needUpdate("inventory");
                $this->newInventoryLevel = $inventoryLevel;

                break;
            default:
                return;
        }
        unset($this->in[$fieldName]);
    }
}
