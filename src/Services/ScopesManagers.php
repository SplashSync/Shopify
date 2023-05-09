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

namespace Splash\Connectors\Shopify\Services;

use Splash\Connectors\Shopify\Models\ShopifyHelper as API;

class ScopesManagers
{
    /**
     * Default Scopes for Shopify Connector
     */
    const DEFAULT_SCOPES = array(
        // Access to Customer and Saved Search.
        'read_customers', 'write_customers',
        // Access to Product, product variant, Product Image, Collect, Custom Collection, and Smart Collection.
        'read_products', 'write_products',
        // Access to Product Stocks Levels
        'read_inventory', 'write_inventory',
        // Access to Order, Transaction and Fulfillment.
        'read_orders', 'write_orders',
        'read_all_orders',
        // Access to Fulfillment
        'read_fulfillments', 'write_fulfillments',
    );

    /**
     * Scopes for Logistics Mode
     */
    const LOGISTIC_SCOPES = array(
        // Access to Stock Locations
        'read_locations',
        // Access to Fulfillment
        'read_assigned_fulfillment_orders', 'write_assigned_fulfillment_orders',
    );

    /**
     * Get Shopify Access Scope from APi
     *
     * @return bool
     */
    public function fetchAccessScopes(ShopifyConnector $connector): bool
    {
        //====================================================================//
        // Get Lists of Available Scopes from Api
        $response = API::getRaw('oauth/access_scopes', array(), 'access_scopes');
        if (!is_array($response)) {
            return false;
        }
        //====================================================================//
        // Reformat results
        $response = array_map(function ($scopeItem) {
            return array_shift($scopeItem);
        }, $response);
        //====================================================================//
        // Store in Connector Settings
        $connector->setParameter("Scopes", $response);

        return true;
    }

    /**
     * Get Shopify Access Scope from Parameters
     *
     * @return string[]
     */
    public function getAccessScopes(ShopifyConnector $connector): array
    {
        //====================================================================//
        // Get from Connector Settings
        $scopes = $connector->getParameter("Scopes", null);
        //====================================================================//
        // From API if Empty
        if (null === $scopes) {
            if (!$this->fetchAccessScopes($connector)) {
                return array();
            }
            $scopes = $connector->getParameter("Scopes", null);
        }

        return is_array($scopes) ? $scopes : array();
    }

    /**
     * Get List of Missing Access Scopes.
     *
     * @return string[]
     */
    public function getMissingScopes(ShopifyConnector $connector) : array
    {
        //====================================================================//
        // Build Required Scope List
        $required = self::DEFAULT_SCOPES;
        if ($connector->hasLogisticMode()) {
            $required = array_merge($required, self::LOGISTIC_SCOPES);
        }

        return array_diff($required, $this->getAccessScopes($connector));
    }
}
