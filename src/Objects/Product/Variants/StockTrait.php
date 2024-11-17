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

use Splash\Core\SplashCore as Splash;

/**
 * Access to Product Stock Fields
 */
trait StockTrait
{
    /**
     * @var null|array
     */
    private ?array $newInventoryLevel;

    /**
     * Get Stock Updated data
     *
     * @return null|array
     */
    protected function getNewInventorylevel(): ?array
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
            ->identifier("inventory_quantity")
            ->name("Stock")
            ->microData("http://schema.org/Offer", "inventoryLevel")
            ->isListed()
        ;
        //====================================================================//
        // Out of Stock Flag
        $this->fieldsFactory()->Create(SPL_T_BOOL)
            ->identifier("outofstock")
            ->name("Out of stock")
            ->microData("http://schema.org/ItemAvailability", "OutOfStock")
            ->isReadOnly()
        ;
    }

    /**
     * Read requested Field
     *
     * @param string $key       Input List Key
     * @param string $fieldName Field Identifier / Name
     *
     * @return void
     */
    protected function getStockFields(string $key, string $fieldName): void
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
                $this->out[$fieldName] = !($this->variant->inventory_quantity > 0);

                break;
            default:
                return;
        }

        unset($this->in[$key]);
    }

    /**
     * Write Given Fields
     *
     * @param string      $fieldName Field Identifier / Name
     * @param null|scalar $fieldData Field Data
     *
     * @return void
     */
    protected function setStockFields(string $fieldName, float|bool|int|string|null $fieldData): void
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
                /** @var null|int $locationId */
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
                // Store Changes on Inventory Level for Post Set Update
                $this->needUpdate("inventory");
                $this->newInventoryLevel = $inventoryLevel;

                break;
            default:
                return;
        }
        unset($this->in[$fieldName]);
    }
}
